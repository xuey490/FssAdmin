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
#use Framework\Security\AuthGuard;
use Framework\Utils\JwtFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class JwtServiceProvider implements ServiceProviderInterface
{
    // public function __invoke(ContainerConfigurator $configurator): void
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 注册jwt服务
        $services->set('jwt', JwtFactory::class)
            ->autowire()
            ->public();
		
		/*
        $services->set(AuthGuard::class)
            ->args([
                service('jwt'),
            ])
            ->autowire()
            ->public();

        $services->set('AuthGuard', AuthGuard::class)
            ->args([
                service('jwt'),
            ])
            ->autowire()
            ->public();
		*/
    }

    public function boot(ContainerInterface $container): void
    # public function boot(ContainerConfigurator $container): void
    {}
}
