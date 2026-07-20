<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: RoleMiddleware.php
 * @Date: 2025-12-17
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Middlewares;

use Framework\Attributes\Role;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * 处理请求角色权限校验
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // 1. 获取当前路由配置的 Role 注解信息
        /** @var array<string, object> $attributes */
        $attributes = $request->attributes->get('_attributes', []);
        /** @var Role|null $roleAttribute */
        $roleAttribute = $attributes[Role::class] ?? null;

        // 如果该路由没有配置 Role 注解，默认放行
        if ($roleAttribute === null || empty($roleAttribute->roles)) {
            return $next($request);
        }

        // 2. 获取当前登录用户的角色（由 AuthMiddleware 注入）
        /** @var array{id?: int, username?: string, role?: string, roles?: array<string>} $user */
        $user = $request->attributes->get('user', []);
        $userRoles = $user['roles'] ?? [];

        // 3. 超管不受角色门禁限制
        /** @var object|null $currentUser */
        $currentUser = $request->attributes->get('current_user');
        if ($currentUser !== null && method_exists($currentUser, 'isSuperAdmin') && $currentUser->isSuperAdmin()) {
            return $next($request);
        }

        // 4. 角色权限检查：用户角色是否在允许的列表中
        if (empty(array_intersect($userRoles, $roleAttribute->roles))) {
            return $this->forbiddenResponse();
        }

        // 5. 权限验证通过，继续执行
        return $next($request);
    }

    /**
     * 生成 403 Forbidden 响应
     *
     * @return Response
     */
    private function forbiddenResponse(): Response
    {
        return BaseJsonResponse::error('无权限访问！', 403);
    }
}
