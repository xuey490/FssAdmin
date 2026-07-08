<?php

declare(strict_types=1);

/**
 * @Filename: RedisPool.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Pool;

use Predis\Client as PredisClient;
use RuntimeException;
use SplStack;

/**
 * Redis 连接池
 *
 * 基于 predis/predis 实现，支持：
 * - 最小/最大连接数管理
 * - 借出/归还语义
 * - 健康检查与断线重连
 * - 借出超时（阻塞等待）
 * - 进程隔离（每个 Workerman Worker 进程独立池）
 */
class RedisPool implements PoolInterface
{
    /** @var SplStack<PredisClient> 空闲连接栈（O(1) push/pop）*/
    private SplStack $idle;

    /** @var int 当前活跃（已借出）连接数 */
    private int $active = 0;

    /** @var array<string, mixed> Predis 连接参数 */
    private array $connectionParams;

    /** @var array<string, mixed> Predis 客户端选项 */
    private array $clientOptions;

    /** @var int 池中最大连接总数 */
    private int $maxConnections;

    /** @var int 池中最小（预热）连接数 */
    private int $minConnections;

    /** @var float 借出等待超时（秒），-1 表示不等待直接抛出 */
    private float $borrowTimeout;

    /** @var int 健康检查失败后最大重试次数 */
    private int $retryAttempts;

    /** @var bool 池是否已关闭 */
    private bool $closed = false;

    /**
     * 构造函数
     *
     * @param array<string, mixed> $config 连接池配置，示例：
     *   [
     *     'host'           => '127.0.0.1',
     *     'port'           => 6379,
     *     'password'       => null,
     *     'database'       => 0,
     *     'timeout'        => 2.0,
     *     'min_connections'=> 2,
     *     'max_connections'=> 10,
     *     'borrow_timeout' => 3.0,
     *     'retry_attempts' => 3,
     *   ]
     */
    public function __construct(private readonly array $config = [])
    {
        $this->maxConnections  = (int) ($config['max_connections'] ?? 10);
        $this->minConnections  = (int) ($config['min_connections'] ?? 2);
        $this->borrowTimeout   = (float) ($config['borrow_timeout'] ?? 3.0);
        $this->retryAttempts   = (int) ($config['retry_attempts'] ?? 3);

        $this->connectionParams = [
            'scheme'   => 'tcp',
            'host'     => $config['host']     ?? '127.0.0.1',
            'port'     => (int) ($config['port'] ?? 6379),
            'password' => $config['password'] ?? null,
            'database' => (int) ($config['database'] ?? 0),
            'timeout'  => (float) ($config['timeout'] ?? 2.0),
        ];

        $this->clientOptions = [
            'exceptions' => true,
        ];

        $this->idle = new SplStack();
        $this->warmup();
    }

    /**
     * 预热：初始化最小连接数
     */
    private function warmup(): void
    {
        for ($i = 0; $i < $this->minConnections; $i++) {
            try {
                $this->idle->push($this->createConnection());
            } catch (\Throwable $e) {
                // 预热失败记录日志但不阻塞启动
                $this->log(sprintf(
                    '[RedisPool] 预热连接 #%d 失败：%s',
                    $i + 1,
                    $e->getMessage()
                ));
            }
        }

        $this->log(sprintf(
            '[RedisPool] 预热完成，空闲连接数：%d / %d',
            $this->idle->count(),
            $this->minConnections
        ));
    }

    /**
     * {@inheritDoc}
 */
    public function borrow(): object
    {
        if ($this->closed) {
            throw new RuntimeException('Redis 连接池已关闭，无法借出连接。');
        }

        $deadline = microtime(true) + $this->borrowTimeout;

        while (true) {
            // 1. 优先从空闲栈取
            if (!$this->idle->isEmpty()) {
                $conn = $this->idle->pop();
                if ($this->healthCheck($conn)) {
                    $this->active++;
                    return $conn;
                }
                // 不健康则丢弃，继续循环
                $this->log('[RedisPool] 丢弃不健康连接，尝试获取新连接');
                continue;
            }

            // 2. 池未达上限，新建连接
            $total = $this->active + $this->idle->count();
            if ($total < $this->maxConnections) {
                $conn = $this->createConnectionWithRetry();
                $this->active++;
                return $conn;
            }

            // 3. 池已满，等待或超时
            if (microtime(true) >= $deadline) {
                throw new RuntimeException(sprintf(
                    'Redis 连接池已耗尽（最大 %d），等待超时（%.1f 秒）。',
                    $this->maxConnections,
                    $this->borrowTimeout
                ));
            }

            // 短暂休眠后重试（非阻塞进程友好）
            usleep(10_000); // 10ms
        }
    }

    /**
     * {@inheritDoc}
     */
    public function release(object $connection): void
    {
        if (!$connection instanceof PredisClient) {
            return;
        }

        $this->active = max(0, $this->active - 1);

        if ($this->closed) {
            // 池已关闭，直接断开
            try {
                $connection->disconnect();
            } catch (\Throwable) {
                // 忽略
            }
            return;
        }

        // 健康则放回池，不健康则丢弃
        if ($this->healthCheck($connection)) {
            $this->idle->push($connection);
        } else {
            $this->log('[RedisPool] 归还连接不健康，已丢弃');
            // 补充一个新连接保持池水位
            try {
                $this->idle->push($this->createConnection());
            } catch (\Throwable $e) {
                $this->log('[RedisPool] 补充连接失败：' . $e->getMessage());
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function healthCheck(object $connection): bool
    {
        if (!$connection instanceof PredisClient) {
            return false;
        }

        try {
            return $connection->ping() !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     * @return array<mixed>
 */
    public function stats(): array
    {
        return [
            'idle'   => $this->idle->count(),
            'active' => $this->active,
            'total'  => $this->idle->count() + $this->active,
            'max'    => $this->maxConnections,
        ];
    }

    /**
     * {@inheritDoc}
 */
    public function close(): void
    {
        $this->closed = true;

        while (!$this->idle->isEmpty()) {
            $conn = $this->idle->pop();
            try {
                $conn->disconnect();
            } catch (\Throwable) {
                // 忽略
            }
        }

        $this->log('[RedisPool] 连接池已关闭');
    }

    /**
     * 创建一个新的 Predis 连接（带重试）
     *
     * @return PredisClient
     * @throws RuntimeException
     */
    private function createConnectionWithRetry(): PredisClient
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                return $this->createConnection();
            } catch (\Throwable $e) {
                $lastException = $e;
                $this->log(sprintf(
                    '[RedisPool] 创建连接失败（第 %d/%d 次）：%s',
                    $attempt,
                    $this->retryAttempts,
                    $e->getMessage()
                ));
                if ($attempt < $this->retryAttempts) {
                    usleep(100_000 * $attempt); // 100ms, 200ms, ...
                }
            }
        }

        throw new RuntimeException(
            sprintf('Redis 连接创建失败，已重试 %d 次：%s', $this->retryAttempts, $lastException?->getMessage()),
            0,
            $lastException
        );
    }

    /**
     * 创建一个原始 Predis 连接
     *
     * @return PredisClient
     * @throws \Throwable
     */
    private function createConnection(): PredisClient
    {
        $client = new PredisClient($this->connectionParams, $this->clientOptions);
        // 触发实际连接（Predis 默认懒连接，ping 强制建连）
        $client->ping();
        return $client;
    }

    /**
     * 写日志（复用全局 log_info 或回退 error_log）
     */
    private function log(string $message): void
    {
        if (function_exists('log_info')) {
            log_info($message);
        } else {
            error_log($message);
        }
    }
}
