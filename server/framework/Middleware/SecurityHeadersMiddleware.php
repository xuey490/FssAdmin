<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 */

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeadersMiddleware - 统一注入安全响应头
 *
 * 对用户访问完全无感，但能显著提升对 XSS、点击劫持、MIME 嗅探、
 * 信息泄露等攻击的防护：
 * - X-Content-Type-Options: nosniff      防 MIME 嗅探
 * - X-Frame-Options / frame-ancestors    防点击劫持
 * - Referrer-Policy                      限制 Referer 泄露
 * - Permissions-Policy                   限制危险特性
 * - Content-Security-Policy              限制资源加载来源
 * - Strict-Transport-Security            仅 HTTPS 下强制 HTTPS
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function __construct(private array $config = [])
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        if (($this->config['enabled'] ?? true) === false) {
            return $response;
        }

        $headers = $response->headers;

        // 防 MIME 嗅探
        if (! $headers->has('X-Content-Type-Options')) {
            $headers->set('X-Content-Type-Options', 'nosniff');
        }

        // 防点击劫持
        $frameOptions = $this->config['frame_options'] ?? 'DENY';
        if ($frameOptions !== '' && ! $headers->has('X-Frame-Options')) {
            $headers->set('X-Frame-Options', $frameOptions);
        }

        // Referrer 策略
        $referrerPolicy = $this->config['referrer_policy'] ?? 'strict-origin-when-cross-origin';
        if ($referrerPolicy !== '' && ! $headers->has('Referrer-Policy')) {
            $headers->set('Referrer-Policy', $referrerPolicy);
        }

        // Permissions-Policy（限制地理位置/摄像头/麦克风等）
        $permissionsPolicy = $this->config['permissions_policy'] ?? '';
        if ($permissionsPolicy !== '' && ! $headers->has('Permissions-Policy')) {
            $headers->set('Permissions-Policy', $permissionsPolicy);
        }

        // Content-Security-Policy
        $csp = $this->config['csp'] ?? '';
        if ($csp !== '' && ! $headers->has('Content-Security-Policy')) {
            $headers->set('Content-Security-Policy', $csp);
        }

        // HSTS：仅在 HTTPS 下下发，避免影响本地 http 调试
        $hsts = $this->config['hsts'] ?? '';
        if ($hsts !== '' && $request->isSecure() && ! $headers->has('Strict-Transport-Security')) {
            $headers->set('Strict-Transport-Security', $hsts);
        }

        return $response;
    }
}
