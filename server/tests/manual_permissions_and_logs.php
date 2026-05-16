<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SysUser;
use App\Models\SysArticle;
use App\Models\SysAccessLog;
use App\Models\SysLoginLog;
use App\Middlewares\AccessLogDbMiddleware;

$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections']['mysql'];

$dsn = "mysql:host={$dbConfig['hostname']};port={$dbConfig['hostport']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
$username = $dbConfig['username'];
$password = $dbConfig['password'];

$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$initSql = file_get_contents(__DIR__ . '/../database/sql/init.sql');
$demoSql = file_get_contents(__DIR__ . '/../database/sql/demo_data.sql');
$pdo->exec($initSql);
$pdo->exec($demoSql);

$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => $dbConfig['hostname'],
    'port' => $dbConfig['hostport'],
    'database' => $dbConfig['database'],
    'username' => $dbConfig['username'],
    'password' => $dbConfig['password'],
    'charset' => $dbConfig['charset'],
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$users = SysUser::with(['dept', 'roles', 'roles.depts'])->orderBy('user_id')->get()->keyBy('user_name');

$expectations = [
    'super' => 6,
    'tech_admin' => 5,
    'backend_1' => 2,
    'frontend_1' => 2,
    'hr_1' => 1,
];

echo "== DataScope article counts ==\n";
foreach ($expectations as $username => $expectedCount) {
    $u = $users->get($username);
    $count = SysArticle::query()->dataScope($u)->count();
    echo "{$username}: {$count} (expected {$expectedCount})\n";
}

echo "\n== Access log insert test ==\n";
$before = SysAccessLog::count();
$req = Request::create('/test/access', 'POST', ['q' => '1', 'password' => '123456']);
$req->attributes->set('current_user', $users->get('tech_admin'));
$mw = new AccessLogDbMiddleware();
$mw->handle($req, fn () => new Response('ok', 200));
$after = SysAccessLog::count();
echo "sys_access_log count: {$before} -> {$after}\n";

echo "\n== Login log insert test ==\n";
$before = SysLoginLog::count();
SysLoginLog::create([
    'user_id' => $users->get('tech_admin')->user_id,
    'user_name' => 'tech_admin',
    'ip' => '127.0.0.1',
    'user_agent' => 'manual-test',
    'status' => 1,
    'message' => null,
    'login_time' => date('Y-m-d H:i:s'),
]);
$after = SysLoginLog::count();
echo "sys_login_log count: {$before} -> {$after}\n";

