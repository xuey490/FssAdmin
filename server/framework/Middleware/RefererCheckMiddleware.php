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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RefererCheckMiddleware
{
    public function __construct(
        private array $allowedHosts,
        private array $allowedSchemes = ['https'],
        private array $except = [],
        // 建议默认为 false，否则用户直接在地址栏输入网址会报错
        private bool $strict = false, 
        private string $errorMessage = 'Invalid request origin.'
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        // 1. 那些不需要验证来源的方法（通常是只读且不涉及敏感数据的）
        // 如果你需要防盗链（图片等），请从这里移除 'GET'
        if (in_array($request->getMethod(), ['HEAD', 'OPTIONS', 'TRACE'])) {
            return $next($request);
        }

        // 2. 检查排除路径
        foreach ($this->except as $pattern) {
            if ($this->matchPath($request->getPathInfo(), $pattern)) {
                return $next($request);
            }
        }

        // 3. 获取来源：优先取 Origin，其次取 Referer
        // Origin 通常在 POST/PUT/DELETE 或 CORS 请求中出现，比 Referer 更可靠且隐私性更好
        $origin = $request->headers->get('Origin') ?? $request->headers->get('Referer');

        // 4. 处理无来源的情况
        if (! $origin) {
            // 即使是严格模式，通常也建议对 GET 请求放行（允许直接访问），除非是纯 API 接口
            if ($this->strict && ! $request->isMethod('GET')) {
                throw new AccessDeniedHttpException($this->errorMessage . ' (No Origin/Referer)');
            }
            // 如果是严格模式且是 GET 请求，或者是非严格模式，直接放行
            return $next($request);
        }

        // 5. 解析来源 URL
        $parsed = parse_url($origin);
        if (! $parsed || ! isset($parsed['host'])) {
            throw new AccessDeniedHttpException($this->errorMessage . ' (Malformed URL)');
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        $host   = strtolower($parsed['host']);

        // 6. 验证协议
        if (! in_array($scheme, $this->allowedSchemes, true)) {
            throw new AccessDeniedHttpException($this->errorMessage . ' (Invalid Scheme)');
        }

        // 7. 验证主机名
        if (! $this->isHostAllowed($host)) {
            throw new AccessDeniedHttpException($this->errorMessage . ' (Invalid Host)');
        }

        return $next($request);
    }

    private function isHostAllowed(string $host): bool
    {
        foreach ($this->allowedHosts as $allowed) {
            // 精确匹配
            if ($host === $allowed) {
                return true;
            }

            // 通配符匹配 (*.example.com)
            if (str_starts_with($allowed, '*.')) {
                $domain = substr($allowed, 2);
                // 检查是否是子域名 (abc.example.com) 或 根域名本身 (example.com)
                if (str_ends_with($host, '.' . $domain) || $host === $domain) {
                    return true;
                }
            }
        }
        return false;
    }

    private function matchPath(string $path, string $pattern): bool
    {
        // 简单优化：如果没有通配符，直接全等比较，性能更高
        if (! str_contains($pattern, '*')) {
            return $path === $pattern;
        }

        $regex = str_replace('\*', '.*', preg_quote($pattern, '#'));
        return (bool) preg_match('#^' . $regex . '$#', $path);
    }
}