<?php

declare(strict_types=1);

define('BASE_PATH', realpath(dirname(__DIR__)));

require BASE_PATH . '/vendor/autoload.php';

use App\Middlewares\PermissionMiddleware;
use App\Models\SysUser;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$dbConfig = require BASE_PATH . '/config/database.php';
$connectionConfig = $dbConfig['connections']['mysql'];

if (isset($connectionConfig['hostname']) && !isset($connectionConfig['host'])) {
    $connectionConfig['host'] = $connectionConfig['hostname'];
}

if (isset($connectionConfig['type']) && !isset($connectionConfig['driver'])) {
    $connectionConfig['driver'] = $connectionConfig['type'];
}

$capsule = new Capsule();
$capsule->addConnection($connectionConfig);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$user = SysUser::with(['dept', 'roles', 'roles.menus'])->find(100);

if (!$user) {
    throw new RuntimeException('user 100 not found');
}

$middleware = new PermissionMiddleware();
$cases = [
    [
        'path' => '/api/system/dept/list',
        'route' => 'dept.list',
        'expected' => 403,
    ],
    [
        'path' => '/api/system/user/list',
        'route' => 'user.list',
        'expected' => 200,
    ],
    [
        'path' => '/api/core/system/getUserList',
        'route' => 'system.getUserList',
        'expected' => 200,
    ],
];

foreach ($cases as $case) {
    $request = Request::create($case['path'], 'GET');
    $request->attributes->set('current_user', $user);
    $request->attributes->set('user', [
        'id' => (int) $user->id,
        'username' => (string) ($user->user_name ?? ''),
        'tenant_id' => 1,
    ]);
    $request->attributes->set('_route', [
        'params' => [
            '_route_name' => $case['route'],
        ],
    ]);

    $response = $middleware->handle($request, static fn () => new Response('ok', 200));
    $statusCode = $response->getStatusCode();

    echo $case['path'] . ' => ' . $statusCode . PHP_EOL;

    if ($statusCode !== $case['expected']) {
        throw new RuntimeException($case['path'] . ' expected ' . $case['expected'] . ', got ' . $statusCode);
    }
}

echo 'manual permission guard verified' . PHP_EOL;
