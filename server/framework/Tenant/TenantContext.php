<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: TenantContext.php
 * @Date: 2026-03-19
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Tenant;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * 租户上下文管理器（Symfony Request 版本）
 *
 * 该类提供基于 Symfony Request 的租户隔离上下文管理，
 * 使用 Request 属性存储租户信息，避免 Workerman 常驻进程下的静态变量污染问题。
 *
 * 主要功能：
 * - 设置和获取当前租户 ID
 * - 控制是否启用租户隔离
 * - 提供忽略租户隔离的临时作用域执行
 * - 支持从 JWT Token 或 Session 中解析租户ID
 *
 * 使用示例：
 * ```php
 * // 在中间件中设置租户
 * TenantContext::setTenantIdToRequest($request, 1001);
 *
 * // 在模型中自动应用租户隔离
 * TenantContext::shouldApplyTenant(); // 返回 true/false
 *
 * // 超管模式临时忽略隔离
 * TenantContext::withIgnore(function() {
 *     return User::all(); // 返回所有租户数据
 * });
 * ```
 *
 * @package Framework\Tenant
 */
final class TenantContext
{
    /**
     * Request 属性键名 - 租户ID
     */
    private const TENANT_ID_KEY = '_tenant_id';

    /**
     * Request 属性键名 - 忽略租户隔离
     */
    private const IGNORE_TENANT_KEY = '_ignore_tenant';

    /**
     * Request 属性键名 - 用户ID（用于JWT场景）
     */
    private const USER_ID_KEY = '_user_id';

    /**
     * RequestStack 实例
     */
    private static ?RequestStack $requestStack = null;

    /**
     * 设置 RequestStack（在容器启动时调用）
     *
     * @param RequestStack $requestStack Symfony RequestStack
     */
    public static function setRequestStack(RequestStack $requestStack): void
    {
        self::$requestStack = $requestStack;
    }

    /**
     * 获取当前 Request
     *
     * @return Request|null 当前请求实例
     */
    public static function getCurrentRequest(): ?Request
    {
        if (self::$requestStack !== null) {
            return self::$requestStack->getCurrentRequest();
        }
        return null;
    }

    /**
     * 设置租户ID到当前 Request
     *
     * @param int|null $tenantId 租户ID
     * @return void
     */
    public static function setTenantId(?int $tenantId): void
    {
        $request = self::getCurrentRequest();
        if ($request !== null) {
            $request->attributes->set(self::TENANT_ID_KEY, $tenantId);
        }
    }

    /**
     * 设置租户ID到指定 Request（用于中间件）
     *
     * @param Request $request Request 实例
     * @param int|null $tenantId 租户ID
     * @return void
     */
    public static function setTenantIdToRequest(Request $request, ?int $tenantId): void
    {
        $request->attributes->set(self::TENANT_ID_KEY, $tenantId);
    }

    /**
     * 获取当前租户ID
     *
     * @return int|null 租户ID，未设置时返回 null
     */
    public static function getTenantId(): ?int
    {
        $request = self::getCurrentRequest();

        //error_log('request: ' . json_encode($request));
        if ($request === null) {
            return null;
        }
        return $request->attributes->get(self::TENANT_ID_KEY);
    }

    /**
     * 从指定 Request 获取租户ID
     *
     * @param Request $request Request 实例
     * @return int|null 租户ID
     */
    public static function getTenantIdFromRequest(Request $request): ?int
    {
        return $request->attributes->get(self::TENANT_ID_KEY);
    }

    /**
     * 设置用户ID（用于JWT场景）
     *
     * @param int|null $userId 用户ID
     * @return void
     */
    public static function setUserId(?int $userId): void
    {
        $request = self::getCurrentRequest();
        if ($request !== null) {
            $request->attributes->set(self::USER_ID_KEY, $userId);
        }
    }

    /**
     * 获取当前用户ID
     *
     * @return int|null 用户ID
     */
    public static function getUserId(): ?int
    {
        $request = self::getCurrentRequest();
        if ($request === null) {
            return null;
        }
        return $request->attributes->get(self::USER_ID_KEY);
    }

    /**
     * 忽略租户隔离（超管模式）
     *
     * @return void
     */
    public static function ignore(): void
    {
        $request = self::getCurrentRequest();
        if ($request !== null) {
            $request->attributes->set(self::IGNORE_TENANT_KEY, true);
        }
    }

    /**
     * 在指定 Request 上忽略租户隔离
     *
     * @param Request $request Request 实例
     * @return void
     */
    public static function ignoreOnRequest(Request $request): void
    {
        $request->attributes->set(self::IGNORE_TENANT_KEY, true);
    }

    /**
     * 恢复租户隔离
     *
     * @return void
     */
    public static function restore(): void
    {
        $request = self::getCurrentRequest();
        if ($request !== null) {
            $request->attributes->set(self::IGNORE_TENANT_KEY, false);
        }
    }

    /**
     * 检查当前是否处于忽略租户隔离状态
     *
     * @return bool 正在忽略返回 true
     */
    public static function isIgnoring(): bool
    {
        $request = self::getCurrentRequest();
        if ($request === null) {
            return false;
        }
        return $request->attributes->get(self::IGNORE_TENANT_KEY, false);
    }

    /**
     * 判断是否应启用租户隔离
     *
     * 当租户 ID 不为空且未设置忽略标志时返回 true
     *
     * @return bool 需要启用租户隔离返回 true
     */
    public static function shouldApplyTenant(): bool
    {
        $request = self::getCurrentRequest();
        if ($request === null) {
            return false;
        }

        $ignore = $request->attributes->get(self::IGNORE_TENANT_KEY, false);
        $tenantId = $request->attributes->get(self::TENANT_ID_KEY);

        return !$ignore && $tenantId !== null;
    }

    /**
     * 在忽略租户隔离的作用域内安全执行回调函数
     *
     * 执行期间临时忽略租户隔离，执行完成后自动恢复原状态
     * 使用 try-finally 确保状态始终被恢复
     *
     * @param callable $fn 要执行的回调函数
     * @return mixed 回调函数的返回值
     */
    public static function withIgnore(callable $fn)
    {
        $request = self::getCurrentRequest();
        if ($request === null) {
            return $fn();
        }

        $prev = $request->attributes->get(self::IGNORE_TENANT_KEY, false);
        $request->attributes->set(self::IGNORE_TENANT_KEY, true);

        try {
            return $fn();
        } finally {
            $request->attributes->set(self::IGNORE_TENANT_KEY, $prev);
        }
    }

    /**
     * 获取最大影响行数限制
     *
     * 用于限制批量删除或更新操作的最大影响行数，防止误操作
     *
     * @return int 最大影响行数
     */
    public static function maxAffectRows(): int
    {
        return 100;
    }

    /**
     * 清理当前请求的租户上下文
     *
     * 在请求结束时调用，清理 Request 中的租户相关属性
     *
     * @return void
     */
    public static function clear(): void
    {
        $request = self::getCurrentRequest();
        if ($request !== null) {
            $request->attributes->remove(self::TENANT_ID_KEY);
            $request->attributes->remove(self::IGNORE_TENANT_KEY);
            $request->attributes->remove(self::USER_ID_KEY);
        }
    }

    /**
     * 检查当前请求是否有租户上下文
     *
     * @return bool 有租户上下文返回 true
     */
    public static function hasContext(): bool
    {
        return self::getTenantId() !== null;
    }
}
