<?php

declare(strict_types=1);

define('BASE_PATH', realpath(__DIR__ . '/..'));
define('APP_DEBUG', true);

require BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/framework/helpers.php';
require_once BASE_PATH . '/app/function.php';

use Framework\Core\Framework;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$config = require BASE_PATH . '/config/database.php';
$dbConfig = $config['connections']['mysql'];

$dsn = "mysql:host={$dbConfig['hostname']};port={$dbConfig['hostport']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$initSql = file_get_contents(BASE_PATH . '/database/sql/init.sql');
$demoSql = file_get_contents(BASE_PATH . '/database/sql/demo_data.sql');
$pdo->exec($initSql);
$pdo->exec($demoSql);

$app = Framework::getInstance();

function json(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

function decode(Response|string $resp): array
{
    $content = $resp instanceof Response ? $resp->getContent() : $resp;
    $decoded = json_decode((string) $content, true);
    return is_array($decoded) ? $decoded : [];
}

echo "== Auth whitelist / login ==\n";
$loginReq = Request::create(
    '/api/admin/login',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
    json(['username' => 'tech_admin', 'password' => '123456'])
);
$loginResp = $app->handleRequest($loginReq);
$loginData = decode($loginResp);
echo "login status: {$loginResp->getStatusCode()}\n";

$token = (string) (($loginData['data']['tokenValue'] ?? '') ?: '');
if ($token === '') {
    throw new RuntimeException('login failed, tokenValue missing');
}

echo "\n== Role Allocate Users ==\n";
// 1. 获取 tech_admin 的 role_id
$roleId = 2; // tech_admin role

// 2. 查询已分配用户 (tech_admin 用户本身应该在列表里)
$allocatedReq = Request::create('/Admin/SysRole/allocatedUserList', 'GET', ['role_id' => $roleId]);
$allocatedReq->headers->set('Authorization', 'Bearer ' . $token);
$allocatedResp = $app->handleRequest($allocatedReq);
echo "allocated list status: {$allocatedResp->getStatusCode()}\n";
$allocatedData = decode($allocatedResp);
// var_dump($allocatedData);

// 3. 查询未分配用户 (backend_1 是 role 3, frontend_1 是 role 4)
$unallocatedReq = Request::create('/Admin/SysRole/unallocatedUserList', 'GET', ['role_id' => $roleId]);
$unallocatedReq->headers->set('Authorization', 'Bearer ' . $token);
$unallocatedResp = $app->handleRequest($unallocatedReq);
echo "unallocated list status: {$unallocatedResp->getStatusCode()}\n";

// 4. 授权 backend_1 (user_id=3) 到 role 2
$authReq = Request::create(
    '/Admin/SysRole/authUser',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
    json(['role_id' => $roleId, 'user_ids' => [3]])
);
$authReq->headers->set('Authorization', 'Bearer ' . $token);
$authResp = $app->handleRequest($authReq);
echo "auth user status: {$authResp->getStatusCode()}\n";

// 5. 取消授权
$cancelAuthReq = Request::create(
    '/Admin/SysRole/cancelAuthUser',
    'POST',
    [],
    [],
    [],
    ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
    json(['role_id' => $roleId, 'user_ids' => [3]])
);
$cancelAuthReq->headers->set('Authorization', 'Bearer ' . $token);
$cancelAuthResp = $app->handleRequest($cancelAuthReq);
echo "cancel auth user status: {$cancelAuthResp->getStatusCode()}\n";


echo "\n== Logs Management ==\n";
// Login Logs
$loginLogReq = Request::create('/Admin/SysLoginLog', 'GET', ['limit' => 1]);
$loginLogReq->headers->set('Authorization', 'Bearer ' . $token);
$loginLogResp = $app->handleRequest($loginLogReq);
echo "login log list status: {$loginLogResp->getStatusCode()}\n";

// Access Logs
$accessLogReq = Request::create('/Admin/SysAccessLog', 'GET', ['limit' => 1]);
$accessLogReq->headers->set('Authorization', 'Bearer ' . $token);
$accessLogResp = $app->handleRequest($accessLogReq);
echo "access log list status: {$accessLogResp->getStatusCode()}\n";

echo "\nOK\n";
