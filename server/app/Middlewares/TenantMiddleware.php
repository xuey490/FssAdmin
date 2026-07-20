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

use App\Models\SysUserTenant;
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
     * @return mixed
     * @var array<array-key, mixed>
     */
    private array $config;

    /**
     * 构造函数
     *
     * 从 config/jwt.php 读取配置
     * @return mixed
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
        // 解析租户ID（含来源追踪）和用户ID
        $tenantResult = $this->resolveTenantId($request);
        $tenantId = $tenantResult['tenant_id'];
        $tenantSource = $tenantResult['source'];
        $userId = $this->resolveUserId($request);

        // 从 Header/Query 参数获取的租户ID需要进行用户归属校验
        if ($tenantId !== null && in_array($tenantSource, ['header', 'query'], true) && $userId !== null) {
            if (!SysUserTenant::isUserInTenant($userId, $tenantId)) {
                // 用户不属于该租户，拒绝使用该租户ID
                $tenantId = null;
            }
        }

        // 设置租户上下文
        if ($tenantId !== null) {
            TenantContext::setTenantIdToRequest($request, $tenantId);
        }

        if ($userId !== null) {
            $request->attributes->set('_user_id', $userId);
        }

        // 执行后续中间件
        $response = $next($request);

        return $response;
    }

    /**
     * 解析租户ID
     *
     * 返回值包含 tenant_id（int|null）和 source（'jwt'|'session'|'header'|'query'|null）
     *
     * @param Request $request
     * @return array{tenant_id: int|null, source: string|null}
     */
    protected function resolveTenantId(Request $request): array
    {
        $authMode = $this->getAuthMode();

        // 方式1：从 JWT Token 解析（jwt 或 auto 模式）— 已签名可信
        if (in_array($authMode, ['jwt', 'auto'])) {
            $tenantId = $this->resolveTenantIdFromJwt($request);
            if ($tenantId !== null) {
                return ['tenant_id' => $tenantId, 'source' => 'jwt'];
            }
        }

        // 方式2：从 Session 获取（session 或 auto 模式）— 服务端可信
        if (in_array($authMode, ['session', 'auto'])) {
            $tenantId = $this->resolveTenantIdFromSession($request);
            if ($tenantId !== null) {
                return ['tenant_id' => $tenantId, 'source' => 'session'];
            }
        }

        // 方式3：从自定义 Header 获取 — 不可信，需校验用户归属
        $headerName = $this->config['tenant_header'] ?? 'X-Tenant-ID';
        $tenantHeader = $request->headers->get($headerName);
        if ($tenantHeader !== null && $tenantHeader !== '' && is_numeric($tenantHeader)) {
            return ['tenant_id' => (int) $tenantHeader, 'source' => 'header'];
        }

        // 方式4：从 Query 参数获取（仅用于开发调试）— 不可信，需校验用户归属
        if ($this->isDebugMode()) {
            $paramName = $this->config['tenant_query_param'] ?? 'tenant_id';
            $tenantParam = $request->query->get($paramName);
            if ($tenantParam !== null && $tenantParam !== '' && is_numeric($tenantParam)) {
                return ['tenant_id' => (int) $tenantParam, 'source' => 'query'];
            }
        }

        return ['tenant_id' => null, 'source' => null];
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