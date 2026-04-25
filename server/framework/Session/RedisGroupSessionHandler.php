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

namespace Framework\Session;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

/**
 * 支持分组前缀和分布式锁的 Redis Session 处理器.
 *
 * 继承 Symfony 的 RedisSessionHandler，扩展以下功能：
 * - group_prefix（分组前缀）：支持多应用共享 Redis 时的命名空间隔离
 * - 分布式锁：使用 Redis SET NX PX 命令和 Lua 脚本实现高并发安全的写入
 * - 自旋锁等待：可配置锁获取的重试策略
 *
 * 配置选项（Options）：
 * - prefix (string)         : 键前缀（父类支持）
 * - ttl (int)               : Session TTL（秒）
 * - locking (bool)          : 是否启用显式锁，默认 true
 * - spin_lock_wait (int)    : 自旋每次等待时间，单位微秒，默认 150000
 * - lock_ttl (int)          : 锁过期时间，单位毫秒，默认 30000
 * - group_prefix (string)   : 自定义分组前缀，会拼接到 prefix 前面
 *
 * @package Framework\Session
 * @extends RedisSessionHandler
 */
class RedisGroupSessionHandler extends RedisSessionHandler
{
    /**
     * Redis 客户端实例.
     *
     * @var object
     */
    protected object $redis;

    /**
     * Session 本地 TTL（秒）.
     *
     * @var int
     */
    protected int $ttlLocal;

    /**
     * 是否启用分布式锁.
     *
     * @var bool
     */
    protected bool $locking = true;

    /**
     * 自旋锁等待时间（微秒）.
     *
     * @var int
     */
    protected int $spinLockWait = 150000; // 微秒

    /**
     * 锁过期时间（毫秒）.
     *
     * @var int
     */
    protected int $lockTtl = 30000;       // 毫秒

    /**
     * Session 键前缀.
     *
     * @var string
     */
    protected string $prefix = 'session:';

    /**
     * 分组前缀，用于多应用隔离.
     *
     * @var string
     */
    protected string $groupPrefix = '';

    /**
     * 构造函数，初始化 Redis Session 处理器.
     *
     * 处理自定义配置选项，设置分组前缀和分布式锁参数，
     * 然后调用父类构造函数完成基础初始化。
     *
     * @param object $redis   Redis 客户端实例（支持 setex、set、get、del、eval 等方法）
     * @param array  $options 配置选项数组
     */
    public function __construct(object $redis, array $options = [])
    {
        // 处理自定义 option（不要把 group_prefix 传给父类）
        if (isset($options['group_prefix'])) {
            $this->groupPrefix = rtrim((string) $options['group_prefix'], ':') . ':';
            unset($options['group_prefix']);
        }

        // 读取并移除我们自定义的 lock_ttl/spin_wait/locking（父类不认识）
        if (isset($options['lock_ttl'])) {
            $this->lockTtl = (int) $options['lock_ttl'];
            unset($options['lock_ttl']);
        }
        if (isset($options['spin_lock_wait'])) {
            // NOTE: Symfony uses same key name; we keep it if present (microseconds)
            $this->spinLockWait = (int) $options['spin_lock_wait'];
            unset($options['spin_lock_wait']);
            // don't unset: parent allows spin_lock_wait
        }
        if (isset($options['locking'])) {
            $this->locking = (bool) $options['locking'];
            unset($options['locking']);
            // parent allows locking option too; keep it
        }

        // 确定 TTL（秒），优先从 options['ttl']，否则用 php.ini
        $this->ttlLocal = isset($options['ttl']) ? (int) $options['ttl'] : (int) (ini_get('session.gc_maxlifetime') ?: 0);

        // 拼接 groupPrefix 到真正的 prefix 参数，交给父类
        $options['prefix'] = ($this->groupPrefix . ($options['prefix'] ?? $this->prefix));

        // 调用父类构造（传入 options），以保持兼容性与内部锁/机制
        parent::__construct($redis, $options);

        // 保存 redis 实例与最终前缀（父类也持有，但我们用自己的更明确）
        $this->redis  = $redis;
        $this->prefix = $options['prefix'];
    }

    /**
     * 写入 Session 数据（使用分布式锁保护高并发安全）.
     *
     * 如果启用锁机制，在写入前会获取分布式锁，写入完成后释放。
     * 不启用锁则直接使用 setex 写入。
     *
     * @param string $sessionId Session ID
     * @param string $data      序列化的 Session 数据
     *
     * @return bool 写入成功返回 true，失败返回 false
     */
    public function write(string $sessionId, string $data): bool
    {
        // 如果不启用显式锁，直接写（setex）
        if (! $this->locking) {
            try {
                if ($this->ttlLocal > 0) {
                    return (bool) $this->redis->setex($this->sessionKey($sessionId), $this->ttlLocal, $data);
                }
                return (bool) $this->redis->set($this->sessionKey($sessionId), $data);
            } catch (\Throwable $e) {
                error_log('[RedisGroupSessionHandler] write error: ' . $e->getMessage());
                return false;
            }
        }

        // 启用锁：获取 token
        $token = $this->acquireLock($sessionId);
        if ($token === false) {
            // 失败则返回 false（session_write_close 会报错），但我们尽量减少这种情况
            error_log('[RedisGroupSessionHandler] acquireLock failed for session ' . $sessionId);
            return false;
        }

        // 拿到锁后写入
        try {
            if ($this->ttlLocal > 0) {
                $ok = $this->redis->setex($this->sessionKey($sessionId), $this->ttlLocal, $data);
            } else {
                $ok = $this->redis->set($this->sessionKey($sessionId), $data);
            }
        } catch (\Throwable $e) {
            error_log('[RedisGroupSessionHandler] write error after lock: ' . $e->getMessage());
            $ok = false;
        }

        // 释放锁（尽量释放，即使写入失败也要释放）
        try {
            $this->releaseLock($sessionId, $token);
        } catch (\Throwable $e) {
            // 忽略释放异常
        }

        return (bool) $ok;
    }

    /**
     * 读取 Session 数据.
     *
     * 从 Redis 中获取指定 Session ID 的数据，异常时返回空字符串。
     *
     * @param string $sessionId Session ID
     *
     * @return string Session 数据内容，不存在或异常时返回空字符串
     */
    public function read(string $sessionId): string
    {
        try {
            $v = $this->redis->get($this->sessionKey($sessionId));
            return $v ?: '';
        } catch (\Throwable $e) {
            error_log('[RedisGroupSessionHandler] read error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * 销毁指定 Session.
     *
     * 从 Redis 中删除指定 Session ID 对应的键。
     *
     * @param string $sessionId Session ID
     *
     * @return bool 始终返回 true
     */
    public function destroy(string $sessionId): bool
    {
        try {
            $this->redis->del($this->sessionKey($sessionId));
        } catch (\Throwable $e) {
            error_log('[RedisGroupSessionHandler] destroy error: ' . $e->getMessage());
        }
        return true;
    }

    /**
     * 垃圾回收.
     *
     * Redis 使用 TTL 自动过期机制，无需手动清理。
     *
     * @param int $maxlifetime Session 最大生命周期（秒），此参数被忽略
     *
     * @return false|int 始终返回 0，表示由 Redis TTL 管理
     */
    public function gc(int $maxlifetime): false|int
    {
        return 0;
    }

    /**
     * 生成 Session 存储键名.
     *
     * 格式：{prefix}{sessionId}
     *
     * @param string $sessionId Session ID
     *
     * @return string 完整的 Redis 键名
     */
    protected function sessionKey(string $sessionId): string
    {
        // Symfony 的 parent 默认把 prefix 放在 session id 前
        return $this->prefix . $sessionId;
    }

    /**
     * 生成分布式锁键名.
     *
     * 格式：{prefix}lock:{sessionId}
     * 与 Session 数据键区分，添加 'lock:' 前缀。
     *
     * @param string $sessionId Session ID
     *
     * @return string 锁的 Redis 键名
     */
    protected function lockKey(string $sessionId): string
    {
        // 保持与其他实现区分，使用 prefix + 'lock:' + id
        return $this->prefix . 'lock:' . $sessionId;
    }

    /**
     * 获取分布式锁.
     *
     * 使用 Redis SET NX PX 命令实现原子性锁获取。
     * 支持自旋重试，最大等待时间约 3 秒。
     *
     * @param string $sessionId Session ID
     *
     * @return false|string 成功返回锁令牌（token），失败返回 false
     */
    protected function acquireLock(string $sessionId): false|string
    {
        $lockKey = $this->lockKey($sessionId);
        $token   = uniqid('', true);

        // 计算最大自旋次数：我们等待一次 spinLockWait，每次尝试一次
        $maxAttempts = 1; // at least 1
        // We'll try until we acquire; but to avoid infinite loop, limit attempts:
        $maxAttempts = (int) max(1, ceil(3000000 / max(1, $this->spinLockWait))); // cap ~3s total wait

        $attempt = 0;
        do {
            ++$attempt;
            // 使用 Redis::set with options ['nx','px' => ms]
            // ext-redis supports $redis->set($k,$v, ['nx','px' => $ms])
            try {
                $ok = $this->redis->set($lockKey, $token, ['nx', 'px' => $this->lockTtl]);
            } catch (\Throwable $e) {
                // 若底层出错，短暂休眠再试一把
                usleep((int) $this->spinLockWait);
                $ok = false;
            }

            if ($ok) {
                return $token;
            }

            // 没拿到锁 -> 自旋等待
            usleep((int) $this->spinLockWait);
        } while ($attempt < $maxAttempts);

        // 最后再尝试一次，若失败则返回 false
        try {
            $ok = $this->redis->set($lockKey, $token, ['nx', 'px' => $this->lockTtl]);
            if ($ok) {
                return $token;
            }
        } catch (\Throwable) {
            // ignore
        }

        return false;
    }

    /**
     * 释放分布式锁.
     *
     * 使用 Lua 脚本实现原子性锁释放，确保只有持有令牌的客户端才能释放锁。
     * 如果 Lua 不可用，则降级为非原子的比较删除方式。
     *
     * @param string $sessionId Session ID
     * @param string $token     获取锁时返回的令牌
     *
     * @return bool 释放成功返回 true，失败返回 false
     */
    protected function releaseLock(string $sessionId, string $token): bool
    {
        $lockKey = $this->lockKey($sessionId);
        $lua     = <<<'LUA'
if redis.call("get", KEYS[1]) == ARGV[1] then
    return redis.call("del", KEYS[1])
end
return 0
LUA;
        try {
            $res = $this->redis->eval($lua, [$lockKey, $token], 1);
            return (bool) $res;
        } catch (\Throwable $e) {
            // 如果 eval 不可用，做最简单的 fallback（非原子）
            try {
                $current = $this->redis->get($lockKey);
                if ($current === $token) {
                    $this->redis->del($lockKey);
                    return true;
                }
            } catch (\Throwable) {
                // ignore
            }
            return false;
        }
    }
}
