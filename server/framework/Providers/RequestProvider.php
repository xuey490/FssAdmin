<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/*
* 注册全局的request 服务
*/
final class RequestProvider implements ServiceProviderInterface
{
    // public function __invoke(ContainerConfigurator $configurator): void
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 注册 RequestStack（用于在工厂中获取当前请求）
        $services->set(RequestStack::class);


        // 1. 注册 Request 服务（确保全局使用同一个请求实例）
        $services->set(\Symfony\Component\HttpFoundation\Request::class)
            ->factory([\Symfony\Component\HttpFoundation\Request::class, 'createFromGlobals'])->public(); // 通过工厂方法创建请求实例

        // 1. 注册 Request 服务（确保全局使用同一个请求实例）
        $services->set('request', Request::class)
            ->factory([Request::class, 'createFromGlobals'])->public(); // 通过工厂方法创建请求实例
    }

    public function boot(ContainerInterface $container): void
    # public function boot(ContainerConfigurator $container): void
    {}
}
