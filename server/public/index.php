<?php

// 框架入口文件
define('BASE_PATH', realpath(dirname(__DIR__)));
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));

define('FRAMEWORK_VERSION', '0.2.2');

// 引入 Composer 自动加载（确保 vendor 目录在 BASE_PATH 下）
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
} else {
    die('Composer autoload file not found. Please run "composer install".');
}
use Framework\Core\Framework;

// 检查并引入框架辅助函数
$helpersFile = BASE_PATH . '/framework/helpers.php';
if (file_exists($helpersFile)) {
    require_once $helpersFile;
} else {
    die("Framework helpers file not found: $helpersFile");
}

// 检查并引入应用函数
$appFunctionsFile = BASE_PATH . '/app/function.php';
if (file_exists($appFunctionsFile)) {
    require_once $appFunctionsFile;
} else {
    die("App functions file not found: $appFunctionsFile");
}

// 启动框架 （通过单例方法获取实例）
$app = Framework::getInstance();
$app->run();
