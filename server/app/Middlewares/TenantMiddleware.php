<?php

declare(strict_types=1);

/**
 * 租户上下文中间件
 *
 * @package App\Middlewares
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Tenant\TenantContext;
use Framework\Tenant\JwtTenantContext;
use Framework\Tenant\SessionTenantContext;
use Framework\Middleware\MiddlewareInterface;

/**
 * TenantMiddleware - 租户上下文中间件
 *
 * 负责从请求中解析租户信息并设置租户上下文。
 * 支持 JWT Token 和 Session 两种模式。
 *
 * 配置来源：config/jwt.php
 * - auth_mode: 认证模式 (jwt|session|auto)
 * - tenant_header: 自定义租户ID请求头名
 * - tenant_query_param: 调试用的租户ID查询参数名
 */
class TenantMiddleware implements MiddlewareInterface
{
    /**
     * JWT 配置
     */
    private array $config;

    /**
     * 构造函数
     *
     * 从 config/jwt.php 读取配置
     */
    public function __construct()
    {
        $this->config = config('jwt', []);
    }

    /**
     * 获取认证模式
     *
     * @return string jwt|session|auto
     */
    protected function getAuthMode(): string
    {
        return $this->config['auth_mode'] ?? 'auto';
    }

    /**
     * 是否启用调试模式（允许从 Query 参数获取租户ID）
     *
     * @return bool
     */
    protected function isDebugMode(): bool
    {
        return $this->config['tenant_debug'] ?? false;
    }

    /**
     * 处理请求
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // 解析租户ID
        $tenantId = $this->resolveTenantId($request);
        $userId = $this->resolveUserId($request);

        // 设置租户上下文
        if ($tenantId !== null) {
            TenantContext::setTenantIdToRequest($request, $tenantId);
        }

        if ($userId !== null) {
            $request->attributes->set('_user_id', $userId);
        }

        // 执行后续中间件
        $response = $next($request);

        // 清理租户上下文（可选）
        // TenantContext::clear();

        return $response;
    }

    /**
     * 解析租户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveTenantId(Request $request): ?int
    {
        $authMode = $this->getAuthMode();

        // 方式1：从 JWT Token 解析（jwt 或 auto 模式）
        if (in_array($authMode, ['jwt', 'auto'])) {
            $tenantId = $this->resolveTenantIdFromJwt($request);
            if ($tenantId !== null) {
                return $tenantId;
            }
        }

        // 方式2：从 Session 获取（session 或 auto 模式）
        if (in_array($authMode, ['session', 'auto'])) {
            $tenantId = $this->resolveTenantIdFromSession($request);
            if ($tenantId !== null) {
                return $tenantId;
            }
        }

        // 方式3：从自定义 Header 获取
        $headerName = $this->config['tenant_header'] ?? 'X-Tenant-ID';
        $tenantHeader = $request->headers->get($headerName);
        if ($tenantHeader && is_numeric($tenantHeader)) {
            return (int) $tenantHeader;
        }

        // 方式4：从 Query 参数获取（仅用于开发调试）
        if ($this->isDebugMode()) {
            $paramName = $this->config['tenant_query_param'] ?? 'tenant_id';
            $tenantParam = $request->query->get($paramName);
            if ($tenantParam && is_numeric($tenantParam)) {
                return (int) $tenantParam;
            }
        }

        return null;
    }

    /**
     * 从 JWT Token 解析租户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveTenantIdFromJwt(Request $request): ?int
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return null;
        }

        $token = JwtTenantContext::extractTokenFromHeader($authHeader);
        if (!$token) {
            return null;
        }

        return JwtTenantContext::getTenantIdFromToken($token);
    }

    /**
     * 从 Session 解析租户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveTenantIdFromSession(Request $request): ?int
    {
        if (!$request->hasSession()) {
            return null;
        }

        return SessionTenantContext::getTenantId();
    }

    /**
     * 解析用户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveUserId(Request $request): ?int
    {
        $authMode = $this->getAuthMode();

        // 方式1：从 JWT Token 解析（jwt 或 auto 模式）
        if (in_array($authMode, ['jwt', 'auto'])) {
            $userId = $this->resolveUserIdFromJwt($request);
            if ($userId !== null) {
                return $userId;
            }
        }

        // 方式2：从 Session 获取（session 或 auto 模式）
        if (in_array($authMode, ['session', 'auto'])) {
            $userId = $this->resolveUserIdFromSession($request);
            if ($userId !== null) {
                return $userId;
            }
        }

        return null;
    }

    /**
     * 从 JWT Token 解析用户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveUserIdFromJwt(Request $request): ?int
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return null;
        }

        $token = JwtTenantContext::extractTokenFromHeader($authHeader);
        if (!$token) {
            return null;
        }

        return JwtTenantContext::getUserIdFromToken($token);
    }

    /**
     * 从 Session 解析用户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveUserIdFromSession(Request $request): ?int
    {
        if (!$request->hasSession()) {
            return null;
        }

        return SessionTenantContext::getUserId();
    }
}
