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

use Psr\SimpleCache\CacheInterface;
use think\cache\Driver; // ThinkCache 的统一接口
use think\contract\CacheHandlerInterface; // 或者直接用基类

/**
 * ThinkCache 适配器类
 * 
 * 将 ThinkPHP 的缓存驱动适配到框架的 CacheInterface 接口，
 * 同时实现了 PSR-16 CacheInterface 接口，提供标准的缓存操作方法。
 * 
 * 该适配器封装了 ThinkPHP 的缓存驱动（Driver 或 CacheHandlerInterface），
 * 使得框架可以使用统一的接口操作 ThinkCache 缓存。
 * 
 * 支持的功能：
 * - 单键存取、删除、检查
 * - 批量存取、删除
 * - 过期时间设置（支持秒数和 DateInterval）
 * - 缓存键前缀
 *
 * @package Framework\Cache
 * @author  xuey863toy
 */
class ThinkAdapter extends AbstractCache implements CacheInterface
{
    /**
     * ThinkCache 缓存驱动实例
     * 
     * 可以是 CacheHandlerInterface 或 Driver 类型，
     * 来自 ThinkPHP 框架的缓存组件。
     *
     * @var CacheHandlerInterface|Driver
     */
    protected CacheHandlerInterface|Driver $driver;

    /**
     * 构造函数
     * 
     * 接收 ThinkCache 驱动实例进行初始化。
     *
     * @param CacheHandlerInterface|Driver $cache ThinkCache 缓存驱动实例
     */
    public function __construct(CacheHandlerInterface|Driver $cache)
    {
        $this->driver = $cache;
    }

    /**
     * 获取缓存值
     * 
     * 从缓存中获取指定键对应的值，如果键不存在则返回默认值。
     * 会自动应用配置的键前缀。
     *
     * @param string $key     缓存键名
     * @param mixed  $default 当键不存在时返回的默认值，默认为 null
     * 
     * @return mixed 缓存值，如果键不存在则返回默认值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver->get($this->formatKey($key), $default);
    }

    /**
     * 设置缓存值
     * 
     * 将数据存储到缓存中，可指定过期时间。
     * 支持整数秒数或 DateInterval 对象作为过期时间。
     *
     * @param string                  $key   缓存键名
     * @param mixed                   $value 要缓存的值
     * @param \DateInterval|int|null $ttl   过期时间，支持秒数或 DateInterval 对象，null 表示使用默认值
     * 
     * @return bool 成功返回 true，失败返回 false
     */
    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $ttlSeconds = is_int($ttl) ? $ttl : ($ttl ? (int) $ttl->format('%s') : $this->defaultTtl);
        return $this->driver->set($this->formatKey($key), $value, $ttl ?: $this->defaultTtl);
    }

    /**
     * 检查缓存键是否存在
     * 
     * 判断指定的缓存键是否存在于缓存中。
     *
     * @param string $key 缓存键名
     * 
     * @return bool 存在返回 true，不存在返回 false
     */
    public function has(string $key): bool
    {
        return $this->driver->has($this->formatKey($key));
    }

    /**
     * 删除缓存项
     * 
     * 从缓存中删除指定键对应的缓存项。
     *
     * @param string $key 缓存键名
     * 
     * @return bool 成功返回 true，失败返回 false
     */
    public function delete(string $key): bool
    {
        return $this->driver->delete($this->formatKey($key));
    }

    /**
     * 清空所有缓存
     * 
     * 清除当前驱动中的所有缓存数据。
     *
     * @return bool 成功返回 true，失败返回 false
     */
    public function clear(): bool
    {
        return $this->driver->clear();
    }

    /**
     * 批量获取缓存值
     * 
     * 根据给定的键列表批量获取缓存值，返回键值对数组。
     * 对于不存在的键，使用默认值填充。
     *
     * @param iterable $keys    缓存键名列表
     * @param mixed    $default 当键不存在时返回的默认值，默认为 null
     * 
     * @return iterable 键值对数组，键为缓存键名，值为对应的缓存值
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    /**
     * 批量设置缓存值
     * 
     * 批量设置多个缓存项的值，可统一指定过期时间。
     *
     * @param iterable                $values 键值对数组，键为缓存键名，值为要缓存的数据
     * @param \DateInterval|int|null $ttl    过期时间，支持秒数或 DateInterval 对象，null 表示使用默认值
     * 
     * @return bool 成功返回 true，失败返回 false
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    /**
     * 批量删除缓存项
     * 
     * 根据给定的键列表批量删除缓存项。
     *
     * @param iterable $keys 缓存键名列表
     * 
     * @return bool 成功返回 true，失败返回 false
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }
}
