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

namespace Framework\Cache;

/**
 * 缓存接口
 * 
 * 定义缓存组件的基本操作接口，提供缓存的增删改查功能。
 * 遵循 PSR-16 简单缓存标准的设计理念，适用于 ThinkCache 适配。
 * 
 * 实现此接口的类应提供以下能力：
 * - 存储和获取缓存数据
 * - 检查缓存键是否存在
 * - 删除指定缓存项
 * - 清空所有缓存
 *
 * @package Framework\Cache
 * @author  xuey863toy
 */
interface CacheInterface
{
    /**
     * 获取缓存值
     * 
     * 从缓存中获取指定键对应的值，如果键不存在则返回默认值。
     *
     * @param string $key     缓存键名
     * @param mixed  $default 当键不存在时返回的默认值，默认为 null
     * 
     * @return mixed 缓存值，如果键不存在则返回默认值
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * 设置缓存值
     * 
     * 将数据存储到缓存中，可指定过期时间。
     *
     * @param string $key   缓存键名
     * @param mixed  $value 要缓存的值
     * @param int    $ttl   过期时间（秒），0 表示使用默认过期时间，默认为 0
     * 
     * @return bool 成功返回 true，失败返回 false
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool;

    /**
     * 检查缓存键是否存在
     * 
     * 判断指定的缓存键是否存在于缓存中。
     *
     * @param string $key 缓存键名
     * 
     * @return bool 存在返回 true，不存在返回 false
     */
    public function has(string $key): bool;

    /**
     * 删除缓存项
     * 
     * 从缓存中删除指定键对应的缓存项。
     *
     * @param string $key 缓存键名
     * 
     * @return bool 成功返回 true，失败返回 false
     */
    public function delete(string $key): bool;

    /**
     * 清空所有缓存
     * 
     * 清除缓存中的所有数据项。
     *
     * @return bool 成功返回 true，失败返回 false
     */
    public function clear(): bool;
}
