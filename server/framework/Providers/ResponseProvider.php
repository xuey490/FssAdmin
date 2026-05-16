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
use Framework\Utils\ResponseFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Response;

/*
* 注册全局的response 服务
*/
final class ResponseProvider implements ServiceProviderInterface
{
    // public function __invoke(ContainerConfigurator $configurator): void
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // === 注册 Response 为服务 ===
        // 定义一个工厂服务
        $services->set('response', Response::class)
            ->public()
            ->factory([ResponseFactory::class, 'create']);

        // 定义工厂类
        $services->set(ResponseFactory::class)
            ->public();
        /*
        $services
            ->set('response1' , Response::class)
            ->args(['', Response::HTTP_OK, []])
            ->public();
		
        $services
            ->set('response2' , Response::class)
            ->class(Response::class)
            ->public()
            ->synthetic(false) // 表示容器自己管理
            ->args(['', Response::HTTP_OK, []]);
        */
    }

    public function boot(ContainerInterface $container): void
    # public function boot(ContainerConfigurator $container): void
    {}
}
