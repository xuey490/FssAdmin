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
use Framework\View\ThinkTemplateFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use think\Template;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
* 注册ThinkTemp 全局服务
*/
final class ThinkTempServiceProvider implements ServiceProviderInterface
{
    // public function __invoke(ContainerConfigurator $configurator): void
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // ThinkPHP View配置加载
        $TempConfig       = require \dirname(__DIR__) . '/../config/view.php';
        $tpTemplateConfig = $TempConfig['Think'];

        // 0 注册参数类
        $parameters = $configurator->parameters();

        // 1 注册模板工厂类 ，可以这样注册
        $services->set(ThinkTemplateFactory::class)
            ->args([$tpTemplateConfig])
            ->public();

        // 1. 将 ThinkPHP 模板配置定义为一个容器参数
        // 这是一种更 Symfony 的做法，便于管理
        $parameters->set('think_template.config', $tpTemplateConfig);

        // 2. 注册 'thinkTemp' 服务 ，也可以这样注册
        $services->set('thinkTemp', Template::class)
            // 使用 factory() 方法，并指向我们的工厂类
            // ->factory(service(\Framework\View\ThinkTemplateFactory::class))
            ->factory([service(ThinkTemplateFactory::class), 'create'])
            // 为工厂方法注入配置参数
            ->args([
                // 使用 param() 来引用上面定义的参数
                param('think_template.config'),
            ])
            ->public(); // 允许从容器外部获取
    }

    public function boot(ContainerInterface $container): void
    # public function boot(ContainerConfigurator $container): void
    {}
}
