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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Framework\Attributes\Role;
use Framework\Basic\BaseJsonResponse;

class RoleMiddleware
{
    /**
     * 处理请求权限
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request, callable $next): Response
    {
        // 1. 获取当前路由配置的 Role 注解信息
        // 假设框架 Router 已经将解析到的注解实例放入了 request attributes 中
        // key 为类名: Framework\Attributes\Role
        /** @var Role|null $roleAttribute */
        $roleAttribute = $request->attributes->get('_attributes', []); // $request->attributes->get(Role::class);
		
		$attr = $roleAttribute[Role::class] ?? null;


		// 假设 Router 往 request 存了当前调用的控制器类名和方法名
		/* 相当于：$request->attributes->get('_attributes', []);
		$controller = $request->attributes->get('_controller'); // e.g., "App\Controllers\AdminController"
		$action = $request->attributes->get('_action');         // e.g., "dashboard"
		
		if ($controller) {
			$reflectionMethod = new \ReflectionMethod($controller);
			$attributes = $reflectionMethod->getAttributes(Role::class);
			
			if (!empty($attributes)) {
				$roleAttribute = $attributes[0]->newInstance();
			}
		}
		*/	

		

        // 如果该路由没有配置 Role 注解，或者配置的角色列表为空，默认放行（或根据业务需求改为拒绝）
        if (!$attr || empty($attr->roles)) {
            return $next($request);
        }

        // 2. 获取当前登录用户的角色
        // ⚠️ TODO: 这里需要对接你实际的 Auth 系统/Session/JWT
        $currentUserRole = $this->getCurrentUserRole($request);

        // 3. 权限检查：判断用户角色是否在允许的列表中
        if (!in_array($currentUserRole, $attr->roles, true)) {
            // 权限不足，抛出 403
            return $this->forbiddenResponse();
        }

        // 4. 权限验证通过，继续执行
        return $next($request);
    }

    /**
     * 获取当前用户角色 (模拟实现，请替换为真实逻辑)
     */
    private function getCurrentUserRole(Request $request): string
    {
        // 示例：从 Session 或 app('auth') 获取
        // return app('auth')->user()->role ?? 'guest';
        
        // 示例：从 Request 属性获取（如果是 API Token 认证，通常会在 AuthMiddleware 中注入 user）
        // $user = $request->attributes->get('current_user');
        // return $user['role'] ?? 'guest';

        // 临时模拟：假设所有访问者都是 'guest'，你可以改为 'admin' 测试通过情况
        return 'guest'; 
    }

    /**
     * 生成 403 Forbidden 响应
     */
    private function forbiddenResponse(): Response
    {
        // 方式 A: 抛出异常 (如果你的框架有全局异常处理器，推荐用这个)
        // throw new \Exception('You do not have permission to access this resource.', 403);

        // 方式 B: 直接返回 JSON 响应
        return BaseJsonResponse::error('Forbidden: Access denied.', 403);
    }
}