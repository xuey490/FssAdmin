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
        MethodOverrideMiddleware::class,
        #CorsMiddleware::class,
        #CsrfTokenGenerateMiddleware::class,
		RateLimitMiddleware::class,
        #CircuitBreakerMiddleware::class, //熔断中间件，正式环境使用，开发环境直接溢出错误堆栈
        IpBlockMiddleware::class,
        XssFilterMiddleware::class,
        #CsrfProtectionMiddleware::class,
        #RefererCheckMiddleware::class,
        CookieConsentMiddleware::class,
        DebugMiddleware::class,
        // 添加日志、CORS、熔断器、限流器，xss、 ip block、Debug等全局中间件
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

        // 4. AuthMiddleware 特殊处理：标记请求需要认证
        $hasAuthInGlobal = in_array(AuthMiddleware::class, $this->globalMiddleware);
        $hasAuthInApp = in_array(AuthMiddleware::class, $this->appMiddleware);
        $hasAuthInRoute = in_array(AuthMiddleware::class, $uniqueRouteMiddleware);
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
