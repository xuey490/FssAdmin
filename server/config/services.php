<?php
// config/services.php
// 这个是个核心的配置文件，如果不懂，请参考symfony服务注册器的语法或下面的例子

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;


return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    // 默认配置
    $services
        ->defaults()
        ->autowire()      // 所有服务默认自动装配
        ->autoconfigure() // 所有服务默认自动配置
		->public();
		
	#(new SessionServiceProvider())($configurator);
    //$parameters = $configurator->parameters();
    //$parameters->set('database.engine', env('ORM_DRIVER')?? 'thinkORM'); // 可以在 .env 中被覆盖	

    $services->set('test', \stdClass::class)->public();



	//$services->load('App\\Dao\\', '../app/Dao/');

	$services->load('App\\Dao\\', BASE_PATH. '/app/Dao/**/*.php')
		->autowire()
		->autoconfigure()
		->public();	
	
	/*orm 别名切换*/
	$databseConfig  = require BASE_PATH . '/config/database.php';
	$engine = $databseConfig['engine'];

	// === 再进行 class_alias 切换 ORM Model ===
	if ($engine === 'laravelORM') {
		class_alias(
			//\Illuminate\Database\Eloquent\Model::class, #原始基类
			\Framework\Basic\BaseLaORMModel::class,	//封装类
			\Framework\Utils\BaseModel::class,
			true
		);
	}

	if ($engine === 'thinkORM') {
		class_alias(
			//\think\Model::class, #原始基类
			\Framework\Basic\BaseTpORMModel::class,	//封装类
			\Framework\Utils\BaseModel::class,
			true
		);
	}	 


	//$databseConfig  = require BASE_PATH . '/config/database.php';

    // === 注册 ThinkORM 模型工厂服务 ===
	/*
    $services
        ->set(\Framework\Utils\ThinkORMFactory::class)
		->args([$databseConfig])
        ->public(); // 可选：设为 public 以便直接从容器获取

    // 可以额外 alias
    $services->alias('thinkorm', \Framework\Utils\ThinkORMFactory::class);
	*/
	
	/** 双orm同时注册
	$databaseConfig  = require BASE_PATH . '/config/database.php';
	$ormType = 'eloquent'; // eloquent 或 'think'，可从配置读取

	$services
		->set(\Framework\Utils\ORMFactory::class)
		->args([$databaseConfig, $ormType])
		->public();
	**/
	

    // ✅ 1. 自动加载应用 Provider
    $providerManager = new \Framework\Container\ContainerProviders();

	// ✅ 2. 自动加载核心 + 应用 Provider
	$providerManager->loadAll(
		$configurator,
		'App\\Providers\\',
		BASE_PATH . '/app/Providers'
		
	);


    // ✅ 3. 启动所有 Provider（boot）
	\Framework\Container\Container::setProviderManager($providerManager);

	
	

};
