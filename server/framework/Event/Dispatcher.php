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

namespace Framework\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Dispatcher implements EventDispatcherInterface
{
    /**
     * 存储监听器：[事件类][优先级][] = 监听器.
     * @var array<string, array<int, array<array|callable|string>>>
     */
    private array $listeners = [];

    public function __construct(private ContainerInterface $container) {}

    /**
     * 添加监听器.
     *
     * @param string                $eventClass 事件类名
     * @param array|callable|string $listener   回调、[对象, 方法]、类名
     * @param int                   $priority   优先级，数值越大越先执行
     */
    public function addListener(string $eventClass, array|callable|string $listener, int $priority = 0): void
    {
        $this->listeners[$eventClass][$priority][] = $listener;

        // 按优先级降序排列（高优先级在前）
        krsort($this->listeners[$eventClass]);
    }

    /**
     * 批量注册实现了 ListenerInterface 的监听器类
     * 支持两种格式：
     *  1. [['methodName', 100], ...]           - 主流框架风格
     *  2. ['method'=>'m','priority'=>100]      - 新手友好风格
     */
    public function addSubscriber(ListenerInterface $subscriber): void
    {
        foreach ($subscriber->subscribedEvents() as $event => $config) {
            if (! is_array($config)) {
                throw new \InvalidArgumentException("Subscription for {$event} must be an array.");
            }

            // 包装成统一的二维数组格式
            $subscriptions = $this->normalizeSubscriptions($config);

            foreach ($subscriptions as $subscription) {
                $method   = $subscription['method'];
                $priority = $subscription['priority'];

                $this->addListener($event, [$subscriber, $method], $priority);
            }
        }
    }

    /**
     * 分发事件，执行所有匹配的监听器.
     */
    public function dispatch(object $event): object
    {
        $eventClass = get_class($event);

        // 获取该事件的所有监听器（已按优先级排序）
        $listeners = $this->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            // 解析监听器（支持字符串类名、DI 容器注入等）
            $callable = $this->resolveListener($listener);

            if (! $callable) {
                continue;
            }

            // 执行监听器
            $callable($event);

            // 如果事件实现了可停止接口，且已停止，则中断后续执行
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                //  "🛑 Event propagation stopped by listener.\n";
                break;
            }
        }

        return $event;
    }

    /**
     * 获取某个事件的所有监听器（按优先级合并后返回）.
     */
    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = get_class($event);

        if (! isset($this->listeners[$eventClass])) {
            return [];
        }

        $flattened = [];
        foreach ($this->listeners[$eventClass] as $priorityGroup) {
            foreach ($priorityGroup as $listener) {
                $flattened[] = $listener;
            }
        }

        return $flattened;
    }

    /**
     * 检查是否有监听器监听该事件.
     */
    public function hasListeners(object $event): bool
    {
        return count($this->getListenersForEvent($event)) > 0;
    }

    /**
     * 将不同格式的订阅配置标准化为统一结构.
     *
     * @param  array                                            $config 原始配置
     * @return array<int, array{method: string, priority: int}>
     */
    private function normalizeSubscriptions(array $config): array
    {
        $result = [];

        // 判断是否是 "['method'=>'xxx', 'priority'=>100]" 风格
        if (isset($config['method'])) {
            $result[] = [
                'method'   => $config['method'],
                'priority' => $config['priority'] ?? 0,
            ];
        }
        // 判断是否是 [['handle', 100], ...] 风格
        elseif (! empty($config) && is_array($config[0])) {
            foreach ($config as $item) {
                if (is_string($item)) {
                    $result[] = [
                        'method'   => (string) $item ?? 'handle',
                        'priority' => 0,
                    ];
                    // continue;
                }
                if (! is_array($item)) {
                    continue;
                }
                if (is_array($item)) {
                    $result[] = [
                        'method'   => $item['method']   ?? ($item[0] ?? 'handle'),  // $item['method'] ?? 'handle',
                        'priority' => $item['priority'] ?? ($item[1] ?? 0),
                    ];
                }
            }
        }
        // 简写形式：['handleLogin', 100]
        elseif (isset($config[0]) && is_string($config[0])) {
            $result[] = [
                'method'   => $config[0] ?? 'handle',
                'priority' => $config[1] ?? 0,
            ];
        }

        return $result;
    }

    private function normalizeSubscriptions1(array $config): array
    {
        $result = [];

        foreach ($config as $item) {
            if (is_string($item)) {
                $result[] = ['method' => $item, 'priority' => 0];
            } elseif (is_array($item)) {
                if (isset($item['method'])) {
                    $result[] = [
                        'method'   => $item['method'],
                        'priority' => $item['priority'] ?? 0,
                    ];
                } else {
                    // 假设是 [method, priority]
                    $result[] = [
                        'method'   => $item[0] ?? 'handle',
                        'priority' => $item[1] ?? 0,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * 解析监听器，支持：
     *  - [obj, 'method']
     *  - 'ClassName::method'
     *  - 'ServiceName'（自动从容器获取）
     *  - 闭包/匿名函数.
     */
    private function resolveListener(array|callable|string $listener): ?callable
    {
        if (is_callable($listener)) {
            return $listener;
        }

        if (is_string($listener)) {
            if (str_contains($listener, '::')) {
                [$class, $method] = explode('::', $listener, 2);
                return [$this->container->get($class), $method];
            }

            return $this->container->get($listener); // 返回对象（需实现 __invoke）
        }

        if (is_array($listener) && isset($listener[0], $listener[1])) {
            $target = $listener[0];
            $method = $listener[1];

            if (is_string($target)) {
                $resolved = $this->container->get($target);
                return [$resolved, $method];
            }

            return $listener; // 已是 [object, method]
        }

        return null;
    }
}
