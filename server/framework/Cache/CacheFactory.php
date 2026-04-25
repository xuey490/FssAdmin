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

/**
 * Symfony Cache 组件工厂类
 * 
 * 根据配置创建不同类型的缓存适配器实例，支持多种缓存驱动：
 * - file: 文件缓存
 * - redis: Redis 缓存
 * - apcu: APCu 内存缓存
 * - array: 数组缓存（内存）
 * - memcached: Memcached 缓存
 * 
 * 所有缓存适配器均通过 TagAwareAdapter 包装，支持缓存标签功能。
 *
 * @package Framework\Cache
 * @author  xuey863toy
 */

namespace Framework\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

/**
 * Symfony Cache 组件工厂类
 * 
 * 根据配置动态创建不同驱动的缓存适配器实例。
 * 
 * @package Framework\Cache
 * @author  xuey863toy
 */
class CacheFactory
{
    /**
     * 缓存配置数组
     * 
     * 包含 default（默认驱动）、stores（各驱动详细配置）等配置项。
     *
     * @var array
     */
    private array $config;

    /**
     * 构造函数
     * 
     * 初始化缓存工厂实例，接收缓存配置数组。
     *
     * @param array $config 缓存配置数组，包含 default 和 stores 配置
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 创建默认缓存适配器实例
     * 
     * 根据配置中的 default 选项创建对应的缓存适配器，
     * 返回支持标签功能的 TagAwareAdapter 实例。
     *
     * @return TagAwareAdapter 支持标签功能的缓存适配器实例
     */
    public function create(): TagAwareAdapter
    {
        $store = $this->config['default'];
        return $this->createStore($store);
    }

    /**
     * 创建指定存储驱动的缓存适配器
     * 
     * 根据存储名称从配置中读取对应驱动配置，创建相应的缓存适配器实例。
     * 支持 file、redis、apcu、array、memcached 五种驱动类型。
     *
     * @param string $name 存储驱动名称（如 'redis'、'file' 等）
     * 
     * @return TagAwareAdapter 支持标签功能的缓存适配器实例
     * 
     * @throws \InvalidArgumentException 当指定的存储驱动不存在或不支持时抛出
     */
    private function createStore(string $name): TagAwareAdapter
    {
        $config = $this->config['stores'][$name] ?? throw new \InvalidArgumentException("Cache store [{$name}] not found.");

        $driver = $config['driver'];
        $prefix = $config['prefix'] ?? '';
        // 注意：即使 enable_tags=false，我们也返回 TagAwareAdapter（它仍可工作）
        // 但你可以选择只在需要时包装，这里为简化统一包装

        $innerAdapter = match ($driver) {
            'file'      => new FilesystemAdapter($prefix, 0, $config['path']),
            'redis'     => $this->createRedisAdapter($prefix, $config),
            'apcu'      => new ApcuAdapter($prefix, 0),
            'array'     => new ArrayAdapter(),
            'memcached' => $this->createMemcachedAdapter($prefix, $config),
            default     => throw new \InvalidArgumentException("Unsupported driver: {$driver}"),
        };

        return new TagAwareAdapter($innerAdapter);
    }

    /**
     * 创建 Redis 缓存适配器
     * 
     * 根据 Redis 配置创建 RedisAdapter 实例，支持密码认证和数据库选择。
     * 使用 DSN 字符串方式建立 Redis 连接。
     *
     * @param string $prefix 缓存键前缀
     * @param array  $config Redis 配置数组，包含 host、port、password、database 等字段
     * 
     * @return AdapterInterface Redis 缓存适配器实例
     */
    private function createRedisAdapter(string $prefix, array $config): AdapterInterface
    {
        # $conn     = $config['connection'];
        $host     = $config['host'];
        $port     = $config['port'];
        $password = $config['password']   ?? null;
        $database = $config['database']   ?? 0;

        // 构造标准 Redis DSN: redis://[password@]host:port
        if ($password) {
            $dsn = sprintf('redis://:%s@%s:%d/%d', urlencode($password), $host, $port, $database);
        } else {
            $dsn = sprintf('redis://%s:%d/%d', $host, $port, $database);
        }

        // 直接交给 Symfony 创建连接
        $redisConnection = RedisAdapter::createConnection($dsn);

        return new RedisAdapter($redisConnection, $prefix);
    }

    /**
     * 创建 Memcached 缓存适配器
     * 
     * 根据配置创建 MemcachedAdapter 实例，支持多服务器配置。
     * 需要安装并启用 memcached PHP 扩展。
     *
     * @param string $prefix 缓存键前缀
     * @param array  $config Memcached 配置数组，包含 servers 字段（服务器列表）
     * 
     * @return AdapterInterface Memcached 缓存适配器实例
     * 
     * @throws \RuntimeException 当 memcached 扩展未加载时抛出
     */
    private function createMemcachedAdapter(string $prefix, array $config): AdapterInterface
    {
        if (! extension_loaded('memcached')) {
            throw new \RuntimeException('Memcached extension is required for "memcached" driver.');
        }

        $client  = new \Memcached();
        $servers = array_map(fn ($s) => [$s['host'], $s['port']], $config['servers']);
        $client->addServers($servers);

        return new MemcachedAdapter($client, $prefix);
    }
}
