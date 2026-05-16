<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: CorsMiddleware.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CorsMiddleware - 跨域资源共享中间件
 *
 * 处理跨域请求，支持预检请求和实际请求的 CORS 头设置。
 * 支持配置化的域名白名单和请求头控制。
 *
 * @package Framework\Middleware
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * 允许的来源域名
     * 支持配置为数组形式的白名单，或 '*' 表示允许所有来源
     * @var array|string
     */
    protected array|string $allowOrigin = '*';

    /**
     * 允许的请求方法
     * @var array
     */
    protected array $allowMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
        'OPTIONS',
    ];

    /**
     * 允许的请求头
     * @var array
     */
    protected array $allowHeaders = [
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Origin',
        'Authorization',
        'X-Tenant-Id',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
        'X-TOKEN-REFRESH',
    ];

    /**
     * 允许携带凭证（如 Cookie）
     * @var bool
     */
    protected bool $allowCredentials = true;

    /**
     * 预检请求缓存时间（秒）
     * @var int
     */
    protected int $maxAge = 86400;

    /**
     * 暴露给客户端的响应头
     * @var array
     */
    protected array $exposeHeaders = [];

    /**
     * 处理请求
     *
     * 对预检请求返回 CORS 头，对实际请求在响应中添加 CORS 头。
     *
     * @param Request $request HTTP 请求对象
     * @param callable $next 下一个中间件或处理器
     * @return Response HTTP 响应对象
     */
    public function handle(Request $request, callable $next): Response
    {
        // 1. 处理预检请求 (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            return $this->handlePreflightRequest($request);
        }

        // 2. 对其他请求，先执行后续逻辑，获取响应
        $response = $next($request);

        // 3. 在响应中添加 CORS 头
        $this->addCorsHeaders($request, $response);

        // 4. 返回最终的响应
        return $response;
    }

    /**
     * 处理预检请求
     *
     * 对 OPTIONS 请求返回预检响应，包含 CORS 相关头信息。
     *
     * @param Request $request HTTP 请求对象
     * @return Response 预检响应
     */
    protected function handlePreflightRequest(Request $request): Response
    {
        $response = new Response();
        $response->setStatusCode(204); // No Content

        // 设置允许的来源
        $origin = $this->getAllowedOrigin($request);
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        // 设置允许的方法
        $response->headers->set(
            'Access-Control-Allow-Methods',
            implode(', ', $this->allowMethods)
        );

        // 设置允许的请求头
        $response->headers->set(
            'Access-Control-Allow-Headers',
            implode(', ', $this->allowHeaders)
        );

        // 设置是否允许凭证
        if ($this->allowCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        // 设置预检缓存时间
        $response->headers->set('Access-Control-Max-Age', (string)$this->maxAge);

        return $response;
    }

    /**
     * 添加 CORS 响应头
     *
     * 为实际请求的响应添加 CORS 相关头信息。
     *
     * @param Request $request HTTP 请求对象
     * @param Response $response HTTP 响应对象
     * @return void
     */
    protected function addCorsHeaders(Request $request, Response $response): void
    {
        // 设置允许的来源
        $origin = $this->getAllowedOrigin($request);
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        // 设置允许的请求头（供客户端了解可用的请求头）
        $response->headers->set(
            'Access-Control-Allow-Headers',
            implode(', ', $this->allowHeaders)
        );

        // 设置允许的方法
        $response->headers->set(
            'Access-Control-Allow-Methods',
            implode(', ', $this->allowMethods)
        );

        // 设置是否允许凭证
        if ($this->allowCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        // 设置暴露的响应头
        if (!empty($this->exposeHeaders)) {
            $response->headers->set(
                'Access-Control-Expose-Headers',
                implode(', ', $this->exposeHeaders)
            );
        }
    }

    /**
     * 获取允许的来源
     *
     * 根据请求的 Origin 头和白名单配置，返回允许的来源值。
     *
     * @param Request $request HTTP 请求对象
     * @return string|null 允许的来源，或 null 表示不允许
     */
    protected function getAllowedOrigin(Request $request): ?string
    {
        $requestOrigin = $request->headers->get('Origin');

        // 如果没有 Origin 头，返回默认值
        if (!$requestOrigin) {
            return $this->allowOrigin === '*' ? '*' : null;
        }

        // 如果配置为 '*'，直接返回
        if ($this->allowOrigin === '*') {
            return '*';
        }

        // 如果是数组形式的白名单，检查是否匹配
        if (is_array($this->allowOrigin)) {
            // 检查是否在白名单中
            foreach ($this->allowOrigin as $allowed) {
                if ($this->matchOrigin($requestOrigin, $allowed)) {
                    return $requestOrigin; // 返回实际请求的来源
                }
            }
            return null; // 不在白名单中
        }

        // 单个字符串配置
        return $this->matchOrigin($requestOrigin, $this->allowOrigin) ? $requestOrigin : null;
    }

    /**
     * 匹配来源
     *
     * 检查请求来源是否匹配允许的来源模式。
     * 支持通配符匹配，如 '*.example.com'。
     *
     * @param string $requestOrigin 请求来源
     * @param string $allowed 允许的来源模式
     * @return bool 是否匹配
     */
    protected function matchOrigin(string $requestOrigin, string $allowed): bool
    {
        // 精确匹配
        if ($requestOrigin === $allowed) {
            return true;
        }

        // 通配符匹配（如 *.example.com）
        if (str_starts_with($allowed, '*.')) {
            $domain = substr($allowed, 2); // 去掉 '*.'
            $parsed = parse_url($requestOrigin);
            $host = $parsed['host'] ?? '';

            // 检查是否匹配主域名或子域名
            return $host === $domain || str_ends_with($host, '.' . $domain);
        }

        return false;
    }

    /**
     * 设置允许的来源
     *
     * @param array|string $allowOrigin 允许的来源
     * @return static 当前实例
     */
    public function setAllowOrigin(array|string $allowOrigin): static
    {
        $this->allowOrigin = $allowOrigin;
        return $this;
    }

    /**
     * 设置允许的方法
     *
     * @param array $allowMethods 允许的方法列表
     * @return static 当前实例
     */
    public function setAllowMethods(array $allowMethods): static
    {
        $this->allowMethods = $allowMethods;
        return $this;
    }

    /**
     * 设置允许的请求头
     *
     * @param array $allowHeaders 允许的请求头列表
     * @return static 当前实例
     */
    public function setAllowHeaders(array $allowHeaders): static
    {
        $this->allowHeaders = $allowHeaders;
        return $this;
    }

    /**
     * 设置是否允许凭证
     *
     * @param bool $allowCredentials 是否允许凭证
     * @return static 当前实例
     */
    public function setAllowCredentials(bool $allowCredentials): static
    {
        $this->allowCredentials = $allowCredentials;
        return $this;
    }

    /**
     * 设置预检缓存时间
     *
     * @param int $maxAge 缓存时间（秒）
     * @return static 当前实例
     */
    public function setMaxAge(int $maxAge): static
    {
        $this->maxAge = $maxAge;
        return $this;
    }

    /**
     * 设置暴露的响应头
     *
     * @param array $exposeHeaders 暴露的响应头列表
     * @return static 当前实例
     */
    public function setExposeHeaders(array $exposeHeaders): static
    {
        $this->exposeHeaders = $exposeHeaders;
        return $this;
    }
}
