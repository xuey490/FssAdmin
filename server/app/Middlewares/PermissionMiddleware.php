<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Models\SysUser;
use Framework\Attributes\Permission;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // 1. Allow OPTIONS requests (CORS preflight)
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            return $next($request);
        }

        // 2. Route protection check (prevent auto-route bypass)
        if (!$this->validateRouteAccess($request)) {
            return $this->forbidden('非法访问：此接口受显式路由保护，禁止通过约定路径绕过权限。');
        }

        // 3. Resolve and validate user
        $sysUser = $this->resolveUser($request);
        if (!$sysUser) {
            return $this->unauthorized('请先登录');
        }

        if ($sysUser->isDisabled()) {
            return $this->forbidden('用户已被禁用');
        }

        // 4. Super admin bypass
        if ($sysUser->isSuperAdmin()) {
            return $next($request);
        }

        // 5. Check permission annotation
        $permissionAttr = $this->getPermissionsFromAttribute($request);
        if ($permissionAttr === null) {
            // No annotation = no permission requirement
            return $next($request);
        }

        // 6. Validate user permissions against annotation
        if ($this->checkPermission($sysUser, $permissionAttr)) {
            return $next($request);
        }

        return $this->forbidden('无权限访问');
    }

    protected function validateRouteAccess(Request $request): bool
    {
        $routeInfo = $request->attributes->get('_route');
        if (!is_array($routeInfo)) {
            return true;
        }

        $controller = $routeInfo['controller'] ?? null;
        $method = $routeInfo['method'] ?? null;

        // Check if this is an auto-route (convention-based routing)
        $isAutoRoute = !isset($routeInfo['params']['_route']);

        if ($isAutoRoute && $controller && $method) {
            try {
                $rm = new \ReflectionMethod($controller, $method);

                // Check if method has explicit route annotations
                $hasRouteAttr = !empty($rm->getAttributes(\Framework\Attributes\Route::class, \ReflectionAttribute::IS_INSTANCEOF)) ||
                               !empty($rm->getAttributes(\Framework\Attributes\Routes\BaseMapping::class, \ReflectionAttribute::IS_INSTANCEOF));

                if ($hasRouteAttr) {
                    return false; // Deny: explicit route accessed via auto-route
                }
            } catch (\Throwable $e) {
                // Ignore reflection errors
            }
        }

        return true;
    }

    protected function resolveUser(Request $request): ?SysUser
    {
        $currentUser = $request->attributes->get('current_user');
        if ($currentUser instanceof SysUser) {
            return $currentUser;
        }

        $user = $request->attributes->get('user');
        $userId = (int) ($user['id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        return SysUser::find($userId);
    }

    protected function getPermissionsFromAttribute(Request $request): ?Permission
    {
        $routeInfo = $request->attributes->get('_route');
        if (!is_array($routeInfo)) {
            return null;
        }

        $controllerClass = $routeInfo['controller'] ?? null;
        $method = $routeInfo['method'] ?? null;

        if (!$controllerClass || !$method) {
            return null;
        }

        try {
            // Check method-level annotation first
            $reflectionMethod = new \ReflectionMethod($controllerClass, $method);
            $attributes = $reflectionMethod->getAttributes(Permission::class);
            if (!empty($attributes)) {
                return $attributes[0]->newInstance();
            }

            // Fallback to class-level annotation
            $reflectionClass = new \ReflectionClass($controllerClass);
            $attributes = $reflectionClass->getAttributes(Permission::class);
            if (!empty($attributes)) {
                return $attributes[0]->newInstance();
            }
        } catch (\ReflectionException $e) {
            return null;
        }

        return null;
    }

    protected function checkPermission(SysUser $user, Permission $permission): bool
    {
        $userSlugs = $user->getPermissions();
        $requiredSlugs = $permission->slugs;

        // Empty slugs array = no permission requirement
        if (empty($requiredSlugs)) {
            return true;
        }

        if ($permission->mode === 'AND') {
            // AND mode: user must have ALL required permissions
            foreach ($requiredSlugs as $slug) {
                if (!in_array($slug, $userSlugs, true)) {
                    return false;
                }
            }
            return true;
        } else {
            // OR mode: user must have ANY required permission
            foreach ($requiredSlugs as $slug) {
                if (in_array($slug, $userSlugs, true)) {
                    return true;
                }
            }
            return false;
        }
    }

    protected function unauthorized(string $message): Response
    {
        return new Response(
            json_encode([
                'code' => 401,
                'message' => $message,
                'data' => null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            401,
            ['Content-Type' => 'application/json']
        );
    }

    protected function forbidden(string $message): Response
    {
        return new Response(
            json_encode([
                'code' => 403,
                'message' => $message,
                'data' => null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            403,
            ['Content-Type' => 'application/json']
        );
    }
}
