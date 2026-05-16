<?php
define('BASE_PATH', realpath(__DIR__));
echo BASE_PATH;
require __DIR__ . '/vendor/autoload.php';

use Framework\Core\App;
use Framework\Container\Container;
use Framework\Casbin\Permission;

// 启动订阅监听（阻塞式）
try {
	Container::init();
	$container =Container::getInstance();
	
	App::setContainer($container);
	$db = app('db');
    echo "Casbin Redis Watcher 已启动，监听策略更新...\n";
    Permission::startWatcherListening();
} catch (Exception $e) {
    echo "监听失败: " . $e->getMessage() . "\n";
}