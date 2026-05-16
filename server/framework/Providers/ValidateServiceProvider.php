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
use Framework\Validation\ThinkValidatorFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use think\Validate;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
* 注册Validate全局服务
*/
final class ValidateServiceProvider implements ServiceProviderInterface
{
    // public function __invoke(ContainerConfigurator $configurator): void
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 注册ThinkValidator工厂类
        $services->set(ThinkValidatorFactory::class)
            ->public();

        // 注册thinkphp validate
        $services->set('validate', Validate::class)
            // 使用 factory() 方法，并指向工厂类
            ->factory([service(ThinkValidatorFactory::class), 'create'])
            ->public(); // 允许从容器外部获取
    }

    public function boot(ContainerInterface $container): void
    # public function boot(ContainerConfigurator $container): void
    {}
}
