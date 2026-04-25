<?php

declare(strict_types=1);

namespace Framework\Core;

use Framework\Attributes\Action;
use Framework\Attributes\Auth;
use Framework\Attributes\Role;
use Framework\Attributes\MiddlewareProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * Router (Enhanced Version)
 *
 * 功能特性：
 * - 混合路由：支持 Symfony 定义路由 + 自动推断路由
 * - 路由命中缓存：基于 PSR-16 缓存 URL 匹配结果，跳过解析过程
 * - 元数据编译：支持 Attribute 扫描结果的导出与预加载，生产环境零反射
 * - 安全策略：黑白名单机制、强制显式 Action 声明
 * - 统一元数据：手动路由与自动路由均支持 Auth/Role/Middleware 注解扫描
 */
class Router
{
    private const AUTO_ROUTE_PREFIX = 'auto_route_';
    private const DEFAULT_CONTROLLER_NAMESPACE = 'App\Controllers';
    private const CACHE_KEY_PREFIX = ':route_match_v1_';//redis 分组
    private const CACHE_TTL = 3600; // 缓存 1 小时

    // 定义参数处理常量
    private const PARAM_SINGLE_KEY = 'id';
    private const PARAM_MULTI_PREFIX = 'param';

    // 核心依赖
    private RouteCollection $routes;
    private ContainerInterface $container;
    private ?CacheInterface $cache = null; // PSR-16 缓存实例
    private string $controllerNamespace;

    // 编译后的元数据缓存 (Controller::Method => Metadata Array)
    // 生产环境可通过 loadMetadata 注入，避免运行时反射
    private array $compiledMetadata = [];

    // 运行时方法存在性缓存 (防止重复反射检查)
    private array $methodExistenceCache = [];

    // --- 安全策略配置 ---

    // 是否强制要求控制器方法必须包含 #[Action] 属性才能被自动路由匹配
    private bool $requireExplicitAction = false;

    // 允许自动路由的控制器命名空间前缀白名单 (为空代表不限制)
    private array $whitelist = [];

    // 禁止自动路由的控制器命名空间前缀黑名单
    private array $blacklist = [];

    public function __construct(
        RouteCollection $routes,
        ContainerInterface $container,
        string $controllerNamespace = self::DEFAULT_CONTROLLER_NAMESPACE
    ) {
        $this->routes = $routes;
        $this->container = $container;
        $this->controllerNamespace = rtrim($controllerNamespace, '\\');
    }

    /**
     * 设置缓存实例 (PSR-16)
     * 建议注入 Redis 或 ArrayCache
     */
    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * 设置安全策略
     *
     * @param bool $requireExplicitAction 是否开启显式 Action 模式
     * @param array $whitelist 允许的命名空间前缀，例如 ['App\Controllers\Api']
     * @param array $blacklist 禁止的命名空间前缀，例如 ['App\Controllers\Internal']
     */
    public function setSecurityPolicy(
        bool $requireExplicitAction = false,
        array $whitelist = [],
        array $blacklist = []
    ): self {
        $this->requireExplicitAction = $requireExplicitAction;
        $this->whitelist = $whitelist;
        $this->blacklist = $blacklist;
        return $this;
    }

    /**
     * 加载预编译的元数据
     * 生产环境应在引导阶段调用此方法，传入由 dumpMetadata 生成的数组
     */
    public function loadMetadata(array $metadata): void
    {
        $this->compiledMetadata = $metadata;
    }

    /**
     * 获取当前收集到的所有元数据
     * 用于构建脚本导出并缓存到文件
     */
    public function dumpMetadata(): array
    {
        return $this->compiledMetadata;
    }

    /**
     * 执行路由匹配
     */
    public function match(Request $request): ?array
    {
        // 1. URL 预处理 (去除 .html 后缀等)
        $this->preprocessRequest($request);

        // 2. 检查路由命中缓存 (Route Hit Cache)
        // 如果命中缓存，直接恢复环境并返回，跳过后续所有逻辑
		//dump($this->cache);
        $cacheKey = $this->getCacheKey($request);
        if ($this->cache && $cachedResult = $this->cache->get($cacheKey)) {
            return $this->restoreFromCache($request, $cachedResult);
        }

        // 3. 彩蛋逻辑 (保持原版)
        if (EasterEgg::isTriggeredVersion($request)) {
            return EasterEgg::getRouteMarker();
        }
        if (EasterEgg::isTriggeredTeam($request)) {
            return EasterEgg::getTeamRouteMarker();
        }

        // 准备路由上下文
        $context = (new RequestContext())->fromRequest($request);
        $path = $request->getPathInfo();
        $matchedRoute = null;

        // 4. 尝试匹配定义路由 (Symfony Routes)
        if ($route = $this->matchDefinedRoutes($path, $context, $request)) {
            $matchedRoute = $route;
        }

        // 5. 如果未匹配，尝试自动路由 (Auto Route)
        if (!$matchedRoute) {
            $matchedRoute = $this->matchAutoRoute($path, $request);
        }

        // 6. 如果匹配成功，写入缓存并返回
        if ($matchedRoute) {
            $this->saveToCache($cacheKey, $matchedRoute);
            return $matchedRoute;
        }

        return null;
    }

    /**
     * 匹配 Symfony 定义的静态路由
     */
    private function matchDefinedRoutes(
        string $path,
        RequestContext $context,
        Request $request
    ): ?array {
        try {
            $matcher = new UrlMatcher($this->routes, $context);
            $params = $matcher->match($path);

            if (!isset($params['_controller'])) {
                return null;
            }

            // 解析控制器和方法
            [$controller, $method] = str_contains($params['_controller'], '::')
                ? explode('::', $params['_controller'], 2)
                : [$params['_controller'], '__invoke'];

            // 验证控制器方法是否存在
            if (!$this->isControllerMethodValid($controller, $method)) {
                return null;
            }

            // 获取元数据 (Attributes) 并合并到请求参数
            return $this->finalizeRoute(
                $request,
                $controller,
                $method,
                $params,
                $params['_route'] ?? 'defined_route'
            );

        } catch (MethodNotAllowedException|ResourceNotFoundException $e) {
            return null;
        }
    }

    /**
     * 匹配自动推断路由 /Controller/Action/Params
     */
    private function matchAutoRoute(string $path, Request $request): ?array
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $method = $request->getMethod();

        // 根路径尝试 Home 控制器
        if (empty($segments)) {
            return $this->tryHomeController($request);
        }

        // 倒序匹配，支持多级命名空间
        // 例如 /Admin/User/List -> 尝试 Admin\User\ListController, Admin\User\List, Admin\UserController::list
        for ($i = count($segments); $i >= 1; --$i) {
            // 构建潜在的控制器类名
            $controller = $this->buildControllerClass(array_slice($segments, 0, $i));
            
            // 如果类不存在，或者被安全策略拦截，则跳过
            if (!$controller || !$this->isControllerAllowed($controller)) {
                continue;
            }

            // 匹配方法和剩余参数
            $route = $this->matchActionAndParams(
                $controller,
                array_slice($segments, $i),
                $method
            );

            if ($route) {
                // 自动生成路由名称
                $routeName = self::AUTO_ROUTE_PREFIX . md5($controller . $route['method']);
                
                return $this->finalizeRoute(
                    $request,
                    $controller,
                    $route['method'],
                    $route['params'],
                    $routeName
                );
            }
        }

        return null;
    }

    /**
     * 最终化路由：提取 Attribute，注入 Request，构建返回数组
     */
    private function finalizeRoute(
        Request $request,
        string $controller,
        string $method,
        array $params,
        string $routeName
    ): array {
        // 核心：获取元数据 (优先查 compiledMetadata，否则反射扫描)
        $meta = $this->getMetadata($controller, $method);

        // 合并中间件：Defined路由参数中的中间件 + Attribute中间件
        $mergedMiddleware = array_unique(
            array_merge($params['_middleware'] ?? [], $meta['middleware'])
        );

        // 构造注入到 Request 的属性
        $attributes = $params + [
            '_controller' => "{$controller}::{$method}",
            '_route'      => $routeName,
            '_middleware' => array_values($mergedMiddleware),
            '_auth'       => $meta['auth'],   // 注入 Auth 数据
            '_roles'      => $meta['roles'],  // 注入 Roles 数组
            '_attributes' => $meta['attributes_instances'], // 原始 Attribute 实例
        ];

        $request->attributes->add($attributes);
        #dump($meta);
        // 返回结果数组 (部分数据用于缓存)
        return [
            'controller' => $controller,
            'method'     => $method,
            'params'     => $params,
            'middleware' => array_values($mergedMiddleware),
            // 将计算好的扁平化 Attribute 数据附带在结果中，便于缓存恢复
            '__meta_flat' => [
                '_auth'  => $meta['auth'],
                '_roles' => $meta['roles'],
                // 注意：attributes_instances 包含对象，序列化可能较重，
                // 如果缓存驱动不支持对象序列化，这里需要剔除或特殊处理。
                // 假设 PSR-16 驱动支持 serialize。
            ]
        ];
    }

    /**
     * 安全策略检查：控制器是否允许访问
     */
    private function isControllerAllowed(string $controller): bool
    {
        // 1. 黑名单检查 (优先)
        foreach ($this->blacklist as $blocked) {
            if (str_starts_with($controller, $blocked)) {
                return false;
            }
        }

        // 2. 白名单检查 (如果有配置)
        if (!empty($this->whitelist)) {
            foreach ($this->whitelist as $allowed) {
                if (str_starts_with($controller, $allowed)) {
                    return true;
                }
            }
            // 配置了白名单但未匹配中
            return false;
        }

        // 默认允许
        return true;
    }

    /**
     * 获取元数据：从预编译数组读取 或 实时扫描
     */
    private function getMetadata(string $controller, string $method): array
    {
        $key = "{$controller}::{$method}";

        if (isset($this->compiledMetadata[$key])) {
            return $this->compiledMetadata[$key];
        }

        // 实时扫描并写入内存缓存 (以便 dumpMetadata 可以获取)
        return $this->compiledMetadata[$key] = $this->scanAttributes($controller, $method);
    }

    /**
     * 扫描 Attributes (Reflection)
     */
    private function scanAttributes(string $controller, string $method): array
    {
        $middleware = [];
        $auth = null;
        $roles = [];
        $attributeInstances = []; // 存储原始 Attribute 实例 (Role, Auth 对象)

        try {
            $rc = new ReflectionClass($controller);
            $rm = $rc->getMethod($method);

            // 合并类级别和方法级别的 Attributes
            $attributes = array_merge($rc->getAttributes(), $rm->getAttributes());

            foreach ($attributes as $attr) {
                try {
                    $instance = $attr->newInstance();

                    // 收集 Middleware
                    if ($instance instanceof MiddlewareProviderInterface) {
                        foreach ((array) $instance->getMiddleware() as $m) {
                            if (is_string($m) && $m !== '') {
                                $middleware[] = $m;
                            }
                        }
                    }

                    // 收集 Auth
                    if ($instance instanceof Auth) {
                        $auth = $instance->required; // 假设 Auth 有 required 属性
                        $attributeInstances[Auth::class] = $instance;
                    }

                    // 收集 Role
                    if ($instance instanceof Role) {
                        $roles = array_merge($roles, $instance->roles); // 假设 Role 有 roles 数组
                        $attributeInstances[Role::class] = $instance;
                    }

                } catch (Throwable $e) {
                    // 忽略无法实例化的 Attribute
                    continue;
                }
            }
        } catch (Throwable $e) {
            $this->logException($e, "Attribute scan failed for {$controller}::{$method}");
        }

        return [
            'middleware'           => array_values(array_unique($middleware)),
            'auth'                 => $auth,
            'roles'                => array_values(array_unique($roles)),
            'attributes_instances' => $attributeInstances,
        ];
    }

    /**
     * 从缓存恢复请求状态
     */
    private function restoreFromCache(Request $request, array $cachedRoute): array
    {
        // 恢复基本的 Request Attributes
        $attributes = $cachedRoute['params'] ?? [];
        $attributes['_controller'] = $cachedRoute['controller'] . '::' . $cachedRoute['method'];
        $cachedMiddleware = is_array($cachedRoute['middleware'] ?? null) ? $cachedRoute['middleware'] : [];

        // 即使命中缓存，也重新读取最新 Attribute 元数据，避免注解变更后缓存导致鉴权失效
        $controller = (string) ($cachedRoute['controller'] ?? '');
        $method = (string) ($cachedRoute['method'] ?? '');
        if ($controller !== '' && $method !== '') {
            $meta = $this->getMetadata($controller, $method);
            $attributes['_middleware'] = array_values(array_unique(array_merge(
                $cachedMiddleware,
                (array) ($meta['middleware'] ?? [])
            )));
            $attributes['_auth'] = $meta['auth'] ?? null;
            $attributes['_roles'] = array_values(array_unique((array) ($meta['roles'] ?? [])));
            $attributes['_attributes'] = $meta['attributes_instances'] ?? [];

            // 同步更新缓存结果中的扁平元数据，保证后续逻辑一致
            $cachedRoute['middleware'] = $attributes['_middleware'];
            $cachedRoute['__meta_flat'] = [
                '_auth' => $attributes['_auth'],
                '_roles' => $attributes['_roles'],
            ];
        } else {
            $attributes['_middleware'] = $cachedMiddleware;
            if (isset($cachedRoute['__meta_flat'])) {
                $attributes = array_merge($attributes, $cachedRoute['__meta_flat']);
            }
        }

        $request->attributes->add($attributes);
        return $cachedRoute;
    }

    /**
     * 写入缓存
     */
    private function saveToCache(string $key, array $route): void
    {
        if (!$this->cache) {
            return;
        }

        // 我们直接存储 route 数组，包含 __meta_flat
        // 确保没有不可序列化的资源 (Resources)
        $this->cache->set($key, $route, self::CACHE_TTL);
    }

    private function getCacheKey(Request $request): string
    {
        // Key 必须包含 Method 和 Path
        return self::CACHE_KEY_PREFIX . md5($request->getMethod() . $request->getPathInfo());
    }

    private function buildControllerClass(array $segments): ?string
    {
        $segments = array_map(
            fn($s) => preg_replace('/[^a-zA-Z0-9_]/', '', $s),
            $segments
        );

        if (!$segments) {
            return null;
        }

        $segments = array_map('ucfirst', $segments);
        
        // 尝试1: 完整命名空间类 (App\Controllers\Admin\User)
        $base = $this->controllerNamespace . '\\' . implode('\\', $segments);
        if (class_exists($base)) {
            return $base;
        }

        // 尝试2: 带有 Controller 后缀 (App\Controllers\Admin\UserController)
        $segments[count($segments) - 1] .= 'Controller';
        $fallback = $this->controllerNamespace . '\\' . implode('\\', $segments);

        return class_exists($fallback) ? $fallback : null;
    }

    private function matchActionAndParams(
        string $controller,
        array $segments,
        string $httpMethod
    ): ?array {
        // 获取有效方法列表 (已应用 Action 过滤策略)
        $methods = $this->getValidControllerMethods($controller);

        // 如果没有 URL 片段，尝试 RESTful 默认动作 (index, store...)
        if (!$segments) {
            $action = $this->getRestAction($httpMethod);
            return in_array($action, $methods, true)
                ? ['method' => $action, 'params' => []]
                : null;
        }

        // 尝试匹配 Action 和参数
        // 贪婪匹配：优先匹配更长的 Action 名称
        // 例如 /User/Get/Info -> 优先匹配 getInfo(), 参数无
        // 其次匹配 get(), 参数 Info
        for ($i = count($segments); $i >= 1; --$i) {
             // 修正逻辑：原版是从 1 递增，这里建议倒序或正序根据业务习惯
             // 这里采用标准逻辑：尝试把 segments[0...i] 组合成方法名
             $actionName = $this->buildActionName(array_slice($segments, 0, $i));
             
             if (in_array($actionName, $methods, true)) {
                 return [
                     'method' => $actionName,
                     'params' => $this->extractParams(array_slice($segments, $i)),
                 ];
             }
        }

        // 最后尝试：RESTful 默认动作，剩余全部作为参数
        $fallback = $this->getRestAction($httpMethod);
        if (in_array($fallback, $methods, true)) {
            return [
                'method' => $fallback,
                'params' => $this->extractParams($segments)
            ];
        }

        return null;
    }

    /**
     * 获取控制器中有效的 public 方法列表
     * 应用 requireExplicitAction 策略
     */
    private function getValidControllerMethods(string $class): array
    {
        if (isset($this->methodExistenceCache[$class])) {
            return $this->methodExistenceCache[$class];
        }

        try {
            $rc = new ReflectionClass($class);
            $validMethods = [];

            foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
                // 忽略构造函数和魔术方法
                if ($m->isConstructor() || str_starts_with($m->getName(), '__')) {
                    continue;
                }

                // 策略检查：如果开启显式模式，必须有 #[Action]
                if ($this->requireExplicitAction) {
                    if (empty($m->getAttributes(Action::class))) {
                        continue;
                    }
                }

                $validMethods[] = $m->getName();
            }

            return $this->methodExistenceCache[$class] = $validMethods;
        } catch (Throwable $e) {
            return $this->methodExistenceCache[$class] = [];
        }
    }

    private function buildActionName(array $segments): string
    {
        // convert ['user', 'profile'] to 'userProfile'
        return lcfirst(implode('', array_map('ucfirst', $segments)));
    }

    private function extractParams(array $segments): array
    {
        if (empty($segments)) {
            return [];
        }
        if (count($segments) === 1) {
            return [self::PARAM_SINGLE_KEY => $segments[0]];
        }

        $params = [];
        foreach ($segments as $i => $v) {
            $params[self::PARAM_MULTI_PREFIX . ($i + 1)] = $v;
        }
        return $params;
    }

    private function getRestAction(string $method): string
    {
        return match (strtoupper($method)) {
            'POST'   => 'store',
            'PUT',
            'PATCH'  => 'update',
            'DELETE' => 'destroy',
            default  => 'index',
        };
    }

    private function tryHomeController(Request $request): ?array
    {
        foreach (['Home', 'HomeController'] as $name) {
            $class = "{$this->controllerNamespace}\\{$name}";
            if ($this->isControllerMethodValid($class, 'index')) {
                // 使用 finalizeRoute 确保走统一的元数据加载逻辑
                return $this->finalizeRoute(
                    $request, 
                    $class, 
                    'index', 
                    [], 
                    self::AUTO_ROUTE_PREFIX . 'home'
                );
            }
        }
        return null;
    }

    private function isControllerMethodValid(string $class, string $method): bool
    {
        return class_exists($class)
            && in_array($method, $this->getValidControllerMethods($class), true);
    }

    private function preprocessRequest(Request $request): void
    {
        if (str_ends_with($request->getPathInfo(), '.html')) {
            $clean = substr($request->getPathInfo(), 0, -5);
            if (preg_match('#^[a-zA-Z0-9/_-]+$#', $clean)) {
                $request->server->set(
                    'REQUEST_URI',
                    str_replace($request->getPathInfo(), $clean, $request->getUri())
                );
            }
        }
    }

    private function logException(Throwable $e, string $context): void
    {
        // 建议替换为 Psr\Log\LoggerInterface
        error_log("[Router] {$context}: {$e->getMessage()}");
    }
}
