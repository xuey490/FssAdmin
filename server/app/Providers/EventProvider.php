<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: EventProvider.php
 * @Date: 2025-11-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Framework\Container\ServiceProviderInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Psr\EventDispatcher\EventDispatcherInterface;

use Framework\Event\Dispatcher;
use Framework\Event\ListenerScanner;
use Framework\Event\ListenerInterface;

/*
* 批量注册事件
*/
final class EventProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->load('App\\Listeners\\', \dirname(__DIR__) . '/Listeners/**/*.php')
            ->autowire()
            ->autoconfigure()
            ->public();
			
        // 2. 注册核心服务
        $services->set(ListenerScanner::class)->autowire()->public();
		// 3. 注册事件分发
		$services->set(\Framework\Event\Dispatcher::class)
			->arg('$container', service('service_container'))->public(); // ✅ 显式注入容器自身 注意arg，跟args差异

		// 4. 【关键】绑定接口别名
		// 当控制器请求 EventDispatcherInterface 时，容器会给它 Dispatcher 实例
		$services->alias(EventDispatcherInterface::class, Dispatcher::class);
    }
	

    /**
     * 在 Boot 阶段将扫描到的监听器真正绑定到 Dispatcher
     */
    /**
     * 在 Boot 阶段将扫描到的监听器真正绑定到 Dispatcher
     */
    public function boot(ContainerInterface $container): void
    {
		//dump($container->get('log'));
        /** @var Dispatcher $dispatcher */
        $dispatcher = $container->get(Dispatcher::class);
        
        /** @var ListenerScanner $scanner */
        $scanner = $container->get(ListenerScanner::class);

        $scanResult = $scanner->getSubscribers();

        // 1. 处理传统的 Interface 监听器
        if (!empty($scanResult['interface'])) {
            foreach ($scanResult['interface'] as $className) {
                // 从容器获取实例
                $subscriber = $container->get($className);
                if ($subscriber instanceof ListenerInterface) {
                    $dispatcher->addSubscriber($subscriber);
                }
            }
        }

        // 2. 处理注解监听器
        if (!empty($scanResult['attribute'])) {
            foreach ($scanResult['attribute'] as $conf) {
                // $conf = ['class' => '...', 'method' => '...', 'event' => '...', 'priority' => 0]
                
                // 延迟获取：这里传类名字符串给 addListener，
                // Dispatcher::resolveListener 会在触发时才去容器 get($class)
                $listenerCallback = [$conf['class'], $conf['method']];
                
                $dispatcher->addListener(
                    $conf['event'], 
                    $listenerCallback, 
                    $conf['priority']
                );
            }
        }
    }   
	
}