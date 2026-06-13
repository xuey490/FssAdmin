<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: MiddlewareDispatcher.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use App\Middlewares\AuthMiddleware;
use App\Middlewares\PermissionMiddleware;
use Framework\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MiddlewareDispatcher.
 *
 * 自动调度中间件，包括：
 * - 全局中间件（框架层）
 * - 应用层中间件（config配置）
 * - 路由中间件（路由注解/属性）
 * - 自动扫描控制器 @auth true / #[Auth] 动态添加 AuthMiddleware
 */
class MiddlewareDispatcher
{
    private Container $container;

    // 框架全局中间件（所有请求都会执行，框架底层）
    private array $globalMiddleware = [
        ContextInitMiddleware::class,
        SecurityHeadersMiddleware::class,   // 统一注入安全响应头（对所有响应生效）
        IpBlockMiddleware::class,           // 尽早拒绝黑名单 IP，节省后续开销
        MethodOverrideMiddleware::class,
        CorsMiddleware::class,
        CsrfTokenGenerateMiddleware::class,
        LoginRateLimitMiddleware::class,    // 敏感认证接口限流（登录/刷新/验证码）
		RateLimitMiddleware::class,
        // CircuitBreakerMiddleware::class, // ⚠️ 不作为全局中间件：单一全局熔断器会因少量 500/异常
        //                                  // 触发后让整站返回 503，且会吞掉真实错误。
        //                                  // 如需熔断请按下游依赖（支付/外部API）单独挂载并分 serviceName。
        XssFilterMiddleware::class,
        CsrfProtectionMiddleware::class,
        RefererCheckMiddleware::class,
        CookieConsentMiddleware::class,
        DebugMiddleware::class,
        // 顺序：上下文 → 安全头 → IP封禁 → 方法重写 → CORS → CSRF生成 → 登录限流 → 全局限流 → XSS → CSRF校验 → Referer → CookieConsent → Debug
    ];

    // 应用层中间件（从config/middlewares.php加载）
    private array $appMiddleware = [];


    public function __construct(Container $container)
    {
        $this->container = $container;
        // 初始化加载应用层中间件（项目启动时仅加载一次，提升性能）
        $this->loadAppMiddleware();
    }

    /**
     * 从config/middlewares.php加载应用层中间件
     * 做基础校验，避免配置文件错误
     */
    private function loadAppMiddleware(): void
    {
        // 加载配置文件，兼容配置不存在的情况
        $appMiddlewareConfig = config('middlewares', []);
        if (!is_array($appMiddlewareConfig)) {
            throw new \RuntimeException("config/middlewares.php must return an array of middleware class names");
        }

        // 拍平配置中的数组（支持嵌套）+ 基础校验
        $flattenedConfig = $this->flattenArray($appMiddlewareConfig);
        foreach ($flattenedConfig as $middlewareClass) {
            if (empty($middlewareClass)) {
                continue;
            }
            if (!class_exists($middlewareClass)) {
                throw new \RuntimeException(sprintf(
                    "App Middleware class '%s' not found in config/middlewares.php",
                    $middlewareClass
                ));
            }
            $this->appMiddleware[] = $middlewareClass;
        }

        // 去重应用层中间件
        $this->appMiddleware = array_values(array_unique($this->appMiddleware));
    }

    /**
     * 调度中间件：全局中间件 → 应用层中间件 → 路由中间件.
     * @param callable $destination 最终的控制器/处理方法
     */
    public function dispatch(Request $request, callable $destination): Response
    {
        // 1. 获取路由中间件（UrlMatcher已填充_request属性）
        $rawRouteMiddleware = $request->attributes->get('_middleware', []);
        $routeInfo = $request->attributes->get('_route');
        $currentRouteName = is_string($routeInfo) ? $routeInfo : (is_array($routeInfo) ? json_encode($routeInfo) : 'unknown_route');

        // 2. 拍平路由中间件 + 严格校验类是否存在
        $flattenedRouteMiddleware = $this->flattenArray($rawRouteMiddleware);
        foreach ($flattenedRouteMiddleware as $middlewareClass) {
            if (empty($middlewareClass)) {
                continue;
            }
            if (!class_exists($middlewareClass)) {
                throw new \RuntimeException(sprintf(
                    "Middleware class '%s' not found. Defined in route: '%s'. Please check your Route Attributes or Annotations.",
                    $middlewareClass,
                    $currentRouteName
                ));
            }
        }

        // 3. 路由中间件去重：排除全局/应用层已存在的，避免重复执行
        $excludeMiddleware = array_merge($this->globalMiddleware, $this->appMiddleware);
        $uniqueRouteMiddleware = array_values(array_diff($flattenedRouteMiddleware, $excludeMiddleware));

        // 3.1 权限中间件依赖登录上下文：仅有 Permission 时自动补 Auth
        $hasAuthInGlobal = in_array(AuthMiddleware::class, $this->globalMiddleware, true);
        $hasAuthInApp = in_array(AuthMiddleware::class, $this->appMiddleware, true);
        $hasAuthInRoute = in_array(AuthMiddleware::class, $uniqueRouteMiddleware, true);
        $hasPermissionInRoute = in_array(PermissionMiddleware::class, $uniqueRouteMiddleware, true);

        if (
            $hasPermissionInRoute
            && !$hasAuthInGlobal
            && !$hasAuthInApp
            && !$hasAuthInRoute
        ) {
            array_unshift($uniqueRouteMiddleware, AuthMiddleware::class);
            $hasAuthInRoute = true;
            $request->attributes->set('_auth', true);
        }

        $uniqueRouteMiddleware = $this->ensureAuthBeforePermission($uniqueRouteMiddleware);

        // 4. AuthMiddleware 特殊处理：标记请求需要认证
        if ($hasAuthInGlobal || $hasAuthInApp || $hasAuthInRoute) {
            $request->attributes->set('_auth', true);
        }

        // 5. 自动注入AuthMiddleware：如果请求需要认证但未配置，则加到路由中间件最前
        $needsAuth = $request->attributes->get('_auth', false);
        if ($needsAuth && !$hasAuthInGlobal && !$hasAuthInApp && !$hasAuthInRoute) {
            array_unshift($uniqueRouteMiddleware, AuthMiddleware::class);
        }

        // 6. 核心：合并所有中间件【全局→应用层→路由】，保证执行顺序
        $allMiddleware = array_merge(
            $this->globalMiddleware,
            $this->appMiddleware,
            $uniqueRouteMiddleware
        );
		
		#dump($allMiddleware);

        // 7. 构建洋葱模型（反向包装，保证执行顺序为数组正序）
        $middlewareChain = $destination;
        foreach (array_reverse($allMiddleware) as $middlewareClass) {
            if (empty($middlewareClass)) {
                continue;
            }
            // 容器解析中间件（支持构造函数依赖注入）
            $middleware = $this->container->get($middlewareClass);
            if (!method_exists($middleware, 'handle')) {
                throw new \RuntimeException(sprintf(
                    "Middleware class '%s' must implement a public 'handle' method",
                    $middlewareClass
                ));
            }
            // 包装洋葱链条
            $middlewareChain = function ($req) use ($middleware, $middlewareChain) {
                return $middleware->handle($req, $middlewareChain);
            };
        }

        // 8. 启动中间件链条
        return $middlewareChain($request);
    }

    /**
     * 保证 AuthMiddleware 在 PermissionMiddleware 之前执行。
     */
    private function ensureAuthBeforePermission(array $middleware): array
    {
        $authIndex = array_search(AuthMiddleware::class, $middleware, true);
        $permissionIndex = array_search(PermissionMiddleware::class, $middleware, true);

        if ($authIndex === false || $permissionIndex === false || $authIndex < $permissionIndex) {
            return $middleware;
        }

        unset($middleware[$authIndex]);
        array_unshift($middleware, AuthMiddleware::class);

        return array_values($middleware);
    }

    /**
     * 将多维数组递归“拍平”成一维数组.
     */
    private function flattenArray(array $array): array
    {
        $result = [];
        array_walk_recursive($array, function ($value) use (&$result) {
            $result[] = $value;
        });
        return $result;
    }
}
