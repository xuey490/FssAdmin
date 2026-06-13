<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 */

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LoginRateLimitMiddleware - 敏感认证接口限流
 *
 * 仅作用于登录/验证码/刷新等敏感端点，弥补全局限流豁免 /api/* 后
 * 「登录可被无限暴力破解」的缺口。采用双维度计数：
 * - 按 IP：拦截单一来源的暴力破解 / 撞库
 * - 按 IP + 用户名：拦截针对单一账号的密码爆破
 *
 * 阈值默认对正常用户足够宽松（极少有人 5 分钟内失败几十次），
 * 但能有效阻断自动化攻击，不影响正常登录体验。
 */
class LoginRateLimitMiddleware implements MiddlewareInterface
{
    private bool $enabled;
    private array $paths;
    private int $ipMaxAttempts;
    private int $identityMaxAttempts;
    private int $period;

    public function __construct(private object $redis, array $config = [])
    {
        $this->enabled             = (bool) ($config['enabled'] ?? true);
        $this->paths               = $config['paths'] ?? ['/api/core/login'];
        $this->ipMaxAttempts       = (int) ($config['ip_max_attempts'] ?? 50);
        $this->identityMaxAttempts = (int) ($config['identity_max_attempts'] ?? 10);
        $this->period              = (int) ($config['period'] ?? 300);
    }

    public function handle(Request $request, callable $next): Response
    {
        // 未启用，或非敏感路径/非写方法，直接放行
        if (! $this->enabled
            || ! in_array(strtoupper($request->getMethod()), ['POST', 'PUT'], true)
            || ! $this->isThrottledPath($request->getPathInfo())) {
            return $next($request);
        }

        $ip = $request->getClientIp() ?: 'unknown';

        // 1) 按 IP 维度
        $ipKey = 'login_throttle:ip:' . md5($ip);
        if ($this->hitLimit($ipKey, $this->ipMaxAttempts)) {
            return $this->buildResponse($ipKey);
        }

        // 2) 按 IP + 用户名 维度（仅当请求里带了用户名）
        $username = $this->extractUsername($request);
        if ($username !== '') {
            $idKey = 'login_throttle:id:' . md5($ip . '|' . strtolower($username));
            if ($this->hitLimit($idKey, $this->identityMaxAttempts)) {
                return $this->buildResponse($idKey);
            }
        }

        return $next($request);
    }

    /**
     * 原子自增并判断是否超限；首次计数时设置过期时间。
     */
    private function hitLimit(string $key, int $max): bool
    {
        $count = (int) $this->redis->incr($key);
        if ($count === 1) {
            $this->redis->expire($key, $this->period);
        }

        return $count > $max;
    }

    private function isThrottledPath(string $path): bool
    {
        foreach ($this->paths as $pattern) {
            if (! str_contains($pattern, '*')) {
                if ($path === $pattern) {
                    return true;
                }
                continue;
            }
            $regex = str_replace('\*', '.*', preg_quote($pattern, '#'));
            if (preg_match('#^' . $regex . '$#', $path)) {
                return true;
            }
        }

        return false;
    }

    private function extractUsername(Request $request): string
    {
        $username = $request->request->get('username');
        if (! is_string($username) || $username === '') {
            $content = $request->getContent();
            if ($content !== '') {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['username']) && is_string($decoded['username'])) {
                    $username = $decoded['username'];
                }
            }
        }

        return is_string($username) ? trim($username) : '';
    }

    private function buildResponse(string $key): Response
    {
        $retryAfter = (int) $this->redis->ttl($key);
        if ($retryAfter < 0) {
            $retryAfter = $this->period;
        }

        $message = "尝试过于频繁，请 {$retryAfter} 秒后再试。";

        $response = new JsonResponse([
            'code'        => 429,
            'success'     => false,
            'error'       => 'too_many_attempts',
            'msg'         => $message,
            'message'     => $message,
            'retry_after' => $retryAfter,
            'data'        => null,
        ], 429);

        $response->headers->set('Retry-After', (string) $retryAfter);

        return $response;
    }
}
