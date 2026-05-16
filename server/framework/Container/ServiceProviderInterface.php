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

namespace Framework\Container;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * 服务提供者接口
 *
 * 所有服务提供者必须实现此接口。
 * 服务提供者用于封装服务注册和引导逻辑。
 *
 * 生命周期：
 * 1. register() - 在容器编译前调用，用于注册服务定义
 * 2. boot() - 在容器编译后调用，用于执行初始化逻辑
 *
 * @package Framework\Container
 */
interface ServiceProviderInterface
{
    /**
     * 注册服务定义
     *
     * 在此阶段注册服务到容器中。此时容器尚未编译，
     * 可以自由添加、修改服务定义。
     *
     * @param ContainerConfigurator $container 容器配置器，用于注册服务
     *
     * @return void
     */
    public function register(ContainerConfigurator $container): void;

    /**
     * 引导服务提供者
     *
     * 在容器编译后执行。此时所有服务已定义完成，
     * 可以访问已解析的服务实例执行初始化逻辑。
     *
     * @param ContainerInterface $container 已编译的容器实例
     *
     * @return void
     */
    public function boot(ContainerInterface $container): void;
}
