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

/**
 * 自定义监听器接口（仅用于标记和约定）.
 */
interface ListenerInterface
{
    /**
     * 返回订阅的事件列表
     * 示例: [UserLoginEvent::class => 'handle'].
     */
    public function subscribedEvents(): array;
}
