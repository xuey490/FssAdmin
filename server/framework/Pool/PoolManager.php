<?php

declare(strict_types=1);

/**
 * @Filename: PoolManager.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Pool;

use RuntimeException;

/**
 * 连接池管理器
 *
 * 统一管理 Redis 与 MySQL 连接池的生命周期：
 * - 注册命名池
 * - 从指定池借出/归还连接
 * - 进程退出时统一关闭所有池
 *
 * 用法示例：
 * ```php
 * // 注册（在 Worker onWorkerStart 中调用）
 * PoolManager::register('redis.default', new RedisPool($config));
 * PoolManager::register('mysql.default', new MysqlPool($config));
 *
 * // 借用连接
 * $redis = PoolManager::borrow('redis.default');
 * $pdo   = PoolManager::borrow('mysql.default');
 *
 * // 归还连接
 * PoolManager::release('redis.default', $redis);
 * PoolManager::release('mysql.default', $pdo);
 * ```
 */
class PoolManager
{
    /** @var array<string, PoolInterface> 已注册的连接池 */
    private static array $pools = [];

    /**
     * 注册一个命名连接池
     *
     * @param string        $name 池名称（如 'redis.default', 'mysql.write'）
     * @param PoolInterface $pool 连接池实例
     * @return void
     */
    public static function register(string $name, PoolInterface $pool): void
    {
        self::$pools[$name] = $pool;
    }

    /**
     * 检查指定名称的池是否已注册
     *
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return isset(self::$pools[$name]);
    }

    /**
     * 获取池实例
     *
     * @param string $name
     * @return PoolInterface
     * @throws RuntimeException 池未注册时抛出
     */
    public static function get(string $name): PoolInterface
    {
        if (!isset(self::$pools[$name])) {
            throw new RuntimeException(sprintf('连接池 [%s] 未注册。', $name));
        }
        return self::$pools[$name];
    }

    /**
     * 从指定池借出连接
     *
     * @param string $name 池名称
     * @return object 连接对象
     * @throws RuntimeException
     */
    public static function borrow(string $name): object
    {
        return self::get($name)->borrow();
    }

    /**
     * 归还连接到指定池
     *
     * @param string $name       池名称
     * @param object $connection 连接对象
     * @return void
     */
    public static function release(string $name, object $connection): void
    {
        if (isset(self::$pools[$name])) {
            self::$pools[$name]->release($connection);
        }
    }

    /**
     * 获取所有池的统计信息
     *
     * @return array<string, array{idle: int, active: int, total: int, max: int}>
     */
    public static function stats(): array
    {
        $result = [];
        foreach (self::$pools as $name => $pool) {
            $result[$name] = $pool->stats();
        }
        return $result;
    }

    /**
     * 关闭所有连接池（在 Worker onWorkerStop 或进程退出时调用）
     *
     * @return void
     */
    public static function closeAll(): void
    {
        foreach (self::$pools as $name => $pool) {
            try {
                $pool->close();
            } catch (\Throwable $e) {
                if (function_exists('log_info')) {
                    log_info(sprintf('[PoolManager] 关闭池 [%s] 异常：%s', $name, $e->getMessage()));
                }
            }
        }
        self::$pools = [];
    }
}
