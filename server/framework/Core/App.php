<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: App.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use Framework\Container\Container;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 应用容器静态包装类
 *
 * 该类提供了对依赖注入容器(DI Container)的静态访问接口。
 * 通过静态方法封装，使得在应用的任何位置都可以方便地获取服务实例，
 * 而无需在每次使用时传递容器对象。
 *
 * 主要功能：
 * - 容器的全局设置和获取
 * - 服务的注册和解析（singleton、bind、factory、instance等）
 * - 参数和标签的管理
 * - 延迟加载服务的注册
 *
 * @package Framework\Core
 */
class App
{
    /**
     * 全局容器实例
     *
     * @var ContainerInterface|null
     */
    protected static ?ContainerInterface $container = null;

    /**
     * 设置全局应用容器
     *
     * 此方法用于初始化全局容器实例。容器必须实现 get() 和 has() 方法。
     * 通常在应用启动时调用此方法进行容器初始化。
     *
     * @param ContainerInterface $container 容器实例，必须实现 ContainerInterface 接口
     *
     * @return void
     *
     * @throws InvalidArgumentException 当容器未实现必要的方法时抛出
     */
    public static function setContainer(ContainerInterface $container): void
    {
        if (! method_exists($container, 'get') || ! method_exists($container, 'has')) {
            throw new InvalidArgumentException(
                sprintf('容器必须实现 get() 和 has() 方法，当前类型: %s', get_class($container))
            );
        }

        self::$container = $container;
    }

    /**
     * 获取全局容器实例
     *
     * 返回已设置的全局容器实例。如果容器尚未初始化，则抛出异常。
     *
     * @return ContainerInterface 全局容器实例
     *
     * @throws RuntimeException 当容器尚未初始化时抛出
     */
    public static function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            throw new RuntimeException('应用容器尚未初始化，请先调用 App::setContainer()。');
        }

        return self::$container;
    }

    /**
     * 从容器中获取一个已注册的服务
     *
     * 根据服务ID从容器中获取对应的服务实例。
     * 如果服务不存在，返回 null。
     *
     * @param string $id 服务的唯一标识符（服务ID或类名）
     *
     * @return object|null 服务实例，如果服务不存在则返回 null
     */
    public static function get(string $id): ?object
    {
        return self::getContainer()->get($id);
    }

    /**
     * 从容器解析服务，或在容器外创建实例
     *
     * 该方法提供了灵活的服务解析方式：
     * 1. 如果服务已在容器中注册且不需要额外参数，直接从容器获取
     * 2. 如果容器是自定义 Container 类，使用其 make 方法进行实例化
     *
     * @param string $id     服务名或类名
     * @param array  $params 可选的构造参数，传递给服务构造函数
     *
     * @return object 服务实例
     *
     * @throws RuntimeException 当无法解析服务时抛出
     */
    public static function make(string $id, array $params = []): object
    {
        $container = self::getContainer();

        // 1. 优先从容器获取（工厂、单例、服务等）
        if (empty($params) && $container->has($id)) {
            $service = $container->get($id);

            if (! is_object($service)) {
                throw new RuntimeException("服务 {$id} 返回不是对象类型");
            }

            return $service;
        }

        // 2. 如果容器是我们自定义的 Container 类，使用其 make 方法
        if ($container instanceof Container) {
            return $container->make($id, $params);
        }

        throw new RuntimeException("无法解析服务 '{$id}'。");
    }

    /**
     * 检查容器是否存在指定服务
     *
     * 判断容器中是否已注册指定的服务。
     *
     * @param string $id 服务的唯一标识符
     *
     * @return bool 如果服务存在返回 true，否则返回 false
     */
    public static function has(string $id): bool
    {
        $container = self::$container;
        return $container !== null && $container->has($id);
    }

    // =================================================================
    // 以下是新增的、转发到 Container 实例的静态方法
    // =================================================================

    /**
     * 注册一个单例服务
     *
     * 单例服务在整个应用生命周期中只会被实例化一次，
     * 后续所有获取该服务的请求都会返回同一个实例。
     *
     * @param string   $id      服务ID，用于后续获取服务
     * @param callable $factory 工厂闭包，返回服务实例
     *
     * @return void
     *
     * @throws RuntimeException 当当前容器不支持 singleton 方法时抛出
     */
    public static function singleton(string $id, callable $factory): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->singleton($id, $factory);
        } else {
            throw new RuntimeException('当前容器不支持 singleton 方法。');
        }
    }

    /**
     * 绑定一个抽象（接口）到具体实现
     *
     * 用于将接口或抽象类绑定到具体的实现类，
     * 使得在依赖注入时可以自动解析到正确的实现。
     *
     * @param string $abstract 接口名或抽象类名
     * @param string $concrete 具体实现类名
     * @param bool   $shared   是否为单例模式，默认 false（每次获取都创建新实例）
     *
     * @return void
     *
     * @throws RuntimeException 当当前容器不支持 bind 方法时抛出
     */
    public static function bind(string $abstract, string $concrete, bool $shared = false): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->bind($abstract, $concrete, $shared);
        } else {
            throw new RuntimeException('当前容器不支持 bind 方法。');
        }
    }

    /**
     * 通过工厂函数注册一个服务
     *
     * 使用工厂闭包来创建服务实例。
     * 可以选择将服务注册为单例（shared=true）或每次创建新实例。
     *
     * @param string   $id      服务ID
     * @param callable $factory 工厂闭包，接收容器作为参数，返回服务实例
     * @param bool     $shared  是否为单例，默认 false
     *
     * @return void
     *
     * @throws RuntimeException 当当前容器不支持 factory 方法时抛出
     */
    public static function factory(string $id, callable $factory, bool $shared = false): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->factory($id, $factory, $shared);
        } else {
            throw new RuntimeException('当前容器不支持 factory 方法。');
        }
    }

    /**
     * 注册一个已存在的对象实例
     *
     * 将已经创建好的对象实例注册到容器中，
     * 后续通过该ID获取时将直接返回此实例。
     *
     * @param string $id       服务ID
     * @param object $instance 要注册的对象实例
     *
     * @return void
     *
     * @throws RuntimeException 当当前容器不支持 instance 方法时抛出
     */
    public static function instance(string $id, object $instance): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->instance($id, $instance);
        } else {
            throw new RuntimeException('当前容器不支持 instance 方法。');
        }
    }

    /**
     * 注册一个容器参数
     *
     * 用于存储配置值或其他需要在应用中共享的简单值。
     * 参数可以是任意类型的值（字符串、数字、数组等）。
     *
     * @param string $name  参数名
     * @param mixed  $value 参数值
     *
     * @return void
     *
     * @throws RuntimeException 当当前容器不支持 parameter 方法时抛出
     */
    public static function parameter(string $name, mixed $value): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->parameter($name, $value);
        } else {
            // Symfony ContainerInterface 原生支持参数设置
            if (method_exists($container, 'setParameter')) {
                $container->setParameter($name, $value);
            } else {
                throw new RuntimeException('当前容器不支持 parameter 方法。');
            }
        }
    }

    /**
     * 为一个已注册的服务添加标签
     *
     * 标签用于对服务进行分组，便于批量获取或处理特定类别的服务。
     * 例如，可以将所有事件监听器标记为 "event_listener"。
     *
     * @param string $id         服务ID
     * @param string $tag        标签名
     * @param array  $attributes 标签属性，用于存储额外的元数据
     *
     * @return void
     *
     * @throws RuntimeException 当当前容器不支持 tag 方法时抛出
     */
    public static function tag(string $id, string $tag, array $attributes = []): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->tag($id, $tag, $attributes);
        } else {
            throw new RuntimeException('当前容器不支持 tag 方法。');
        }
    }

    /**
     * 注册一个延迟初始化的服务
     *
     * 延迟服务只有在真正被使用时才会被实例化，
     * 这可以提高应用的启动性能，特别是对于不常用的服务。
     *
     * @param string $id       服务ID
     * @param string $concrete 具体实现类名
     * @param bool   $shared   是否为单例，默认 true
     *
     * @return void
     *
     * @throws RuntimeException 当当前容器不支持 lazy 方法时抛出
     */
    public static function lazy(string $id, string $concrete, bool $shared = true): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->lazy($id, $concrete, $shared);
        } else {
            throw new RuntimeException('当前容器不支持 lazy 方法。');
        }
    }
}
