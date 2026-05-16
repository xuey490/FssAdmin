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

namespace Framework\Middleware;

use Redis;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request; // 引入 JsonResponse
use Symfony\Component\HttpFoundation\Response; // 引入 Redis 类

class RateLimitMiddleware
{
    private int $maxRequests = 10;

    private int $period = 60; // seconds

    private array $except = [];

    /** @var \Redis [MODIFIED] 声明 Redis 属性 */
    private object $redis;

    /**
     * [MODIFIED] 构造函数现在接收 \Redis 实例，而不是 $cacheDir.
     */
    public function __construct(private array $config, object $redis)
    {
        $this->maxRequests = $config['maxRequests'] ?? $this->maxRequests;
        $this->period      = $config['period']      ?? $this->period;
        $this->except      = $config['except']      ?? $this->except;
        $this->redis       = $redis; // 存储 Redis 实例
    }

    /**
     * 处理请求并应用限流
     *
     * @param callable $next 接收 Request 并返回 Response 的下一个处理器
     */
    public function handle(Request $request, callable $next): Response
    {
        foreach ($this->except as $pattern) {
            if ($this->matchPath($request->getPathInfo(), $pattern)) {
                return $next($request); // 匹配成功，跳过限流
            }
        }

        $ip  = $request->getClientIp() ?: 'unknown';

        // [MODIFIED] 使用 Redis key prefix，例如 "rate_limit:md5_of_ip"
        $key = 'rate_limit:' . md5($ip);

        // [MODIFIED] 核心逻辑：使用 Redis 的 INCR 和 EXPIRE
        // 1. 原子性地增加计数器
        $currentCount = $this->redis->incr($key);

        // 2. 如果是窗口内的第一个请求 (incr 返回 1)，则设置过期时间
        if ($currentCount === 1) {
            $this->redis->expire($key, $this->period);
        }

        // 3. 检查是否超过限制
        if ($currentCount > $this->maxRequests) {
            // === 限流触发 ===

            // [MODIFIED] 从 Redis 获取剩余的 TTL 作为 Retry-After
            $retryAfter = $this->redis->ttl($key);
            // 兜底处理，-1 (永不过期) 或 -2 (已删除) 都应视为一个完整周期
            if ($retryAfter < 0) {
                $retryAfter = $this->period;
            }

            return $this->buildRateLimitResponse($request, $retryAfter);
        }

        // === 未触发限流, 执行请求 ===
        $response = $next($request);

        // === [MODIFIED] 在 *成功* 的响应头中也添加当前的限流状态 (最佳实践) ===
        // 这让客户端可以知道自己还剩多少次请求
        $remaining = max(0, $this->maxRequests - $currentCount);

        // 获取当前 key 的剩余时间
        $ttl = $this->redis->ttl($key);
        if ($ttl < 0) {
            $ttl = $this->period;
        } // 兜底
        $resetTime = time() + $ttl;

        $response->headers->set('X-RateLimit-Limit', (string) $this->maxRequests);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
        $response->headers->set('X-RateLimit-Reset', (string) $resetTime);

        return $response;
    }

    /**
     * [MODIFIED] 签名保持不变，内部逻辑也无需大改.
     */
    private function buildRateLimitResponse(Request $request, int $retryAfter): Response
    {
        $message = "请求过于频繁，请 {$retryAfter} 秒后再试。";

        // 判断是否为 API 请求
        if ($request->isXmlHttpRequest()
            || str_contains($request->headers->get('Accept', ''), 'application/json')) {
            $response = new JsonResponse([
                'success'     => false,
                'error'       => 'rate_limit_exceeded',
                'message'     => $message,
                'retry_after' => $retryAfter,
                'limit'       => $this->maxRequests,
                'period'      => $this->period,
            ], 429);
        } else {
            // Web 页面
            $html     = "<h2>⚠️ {$message}</h2><p>系统限制：每 {$this->period} 秒最多 {$this->maxRequests} 次请求。</p>";
            $response = new Response($html, 429, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        // 设置标准的限流头
        $response->headers->set('Retry-After', (string) $retryAfter);
        $response->headers->set('X-RateLimit-Limit', (string) $this->maxRequests);
        $response->headers->set('X-RateLimit-Remaining', '0'); // 触发了限流，剩余次数为 0
        $response->headers->set('X-RateLimit-Reset', (string) (time() + $retryAfter));

        return $response;
    }

    /**
     * (保持不变).
     */
    private function matchPath(string $path, string $pattern): bool
    {
        $regex = str_replace('\*', '.*', preg_quote($pattern, '#'));
        return (bool) preg_match('#^' . $regex . '$#', $path);
    }
}
