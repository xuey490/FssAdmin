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
use Framework\Database\DatabaseFactory;
//use Psr\Log\LoggerInterface;
use Framework\Log\LoggerService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $dbConfig = require BASE_PATH . '/config/database.php';
        $ormType  = $dbConfig['engine'] ?? 'thinkORM';
		
        // 注册 DatabaseFactory ，引入log服务
        $services->set(DatabaseFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service('log') //->nullOnInvalid(),
            ])
            ->public();

        // 别名 "db" ，引入log服务
        $services->set('db', DatabaseFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service('log'), //service(LoggerInterface::class)->nullOnInvalid(),
            ])
            ->public();
    }

    /*
    模型基类的别名，暂时不可用
    */
    public function boot(ContainerInterface $container): void {}
}
