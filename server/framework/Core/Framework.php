<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Framework.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use Framework\Basic\BaseJsonResponse;
use Framework\Container\Container;
use Framework\Middleware\MiddlewareDispatcher;
use Framework\Plugin\PluginManager;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouteCollection;
use Throwable;

/**
 * Class Framework.
 *
 * 框架主入口（单例）
 */
final class Framework
{
    // 核心路径常量（可通过配置覆盖）
    private const MAIN_CONTROLLER_DIR = BASE_PATH . '/app/Controllers';

    private const MAIN_CONTROLLER_NAMESPACE = 'App\Controllers';

    private const ROUTE_CACHE_FILE = BASE_PATH . '/storage/cache/routes.php';

    private const PLUGIN_CONFIG_FILE = BASE_PATH . '/config/plugin/plugins.php';

    private const DIR_PERMISSION = 0755; // 目录默认权限

    private static ?Framework $instance = null;

    private ?Request $request = null;

    private ContainerInterface $container;

    private Router $router;

    private MiddlewareDispatcher $middlewareDispatcher;

    private Kernel $kernel;

    private ?LoggerInterface $logger = null;

    private ?PluginManager $pluginManager = null;

    /**
     * 单例模式：禁止外部实例化.
     */
    private function __construct()
    {
        $this->initializeBasePath();
        $this->createRequiredDirs();
        $this->initializeConfigAndContainer();
        $this->initializeDependencies();
    }

    /**
     * 防止克隆单例实例.
     */
    private function __clone(): void
    {
    }

    /**
     * 防止反序列化单例实例（修正为 public 可见性）.
     *
     * @throws RuntimeException
     */
    public function __wakeup(): void
    {
        // 反序列化时抛出异常，彻底禁止重建实例
        throw new RuntimeException('Cannot unserialize singleton');
    }

    /**
     * 单例模式：获取实例.
     */
    public static function getInstance(): Framework
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 初始化 BASE_PATH.
     */
    private function initializeBasePath(): void
    {
        if (! defined('BASE_PATH')) {
            // 简化路径计算：基于当前文件位置定位项目根目录
            $base = realpath(dirname(__DIR__, 3));
            define('BASE_PATH', $base === false ? getcwd() : $base);
        }
    }

    /**
     * 创建必需目录（支持权限配置）.
     */
    private function createRequiredDirs(): void
    {
        $dirs = [
            BASE_PATH . '/storage/cache',
            BASE_PATH . '/storage/logs',
            BASE_PATH . '/storage/view',
        ];

        // 从配置获取目录权限（默认 0777）
        $permission = null;

        // config() 工具函数如果可用则使用，否则使用常量
        if (function_exists('config')) {
            /** @noinspection PhpUndefinedFunctionInspection */
            $permission = config('app.dir_permission', self::DIR_PERMISSION);
        }

        $permission = $permission ?? self::DIR_PERMISSION;

        foreach ($dirs as $dir) {
            if (! is_dir($dir) && ! mkdir($dir, (int) $permission, true) && ! is_dir($dir)) {
                throw new RuntimeException(sprintf('无法创建目录: %s', $dir));
            }
        }
    }

    /**
     * 初始化配置和容器（核心流程）.
     */
    private function initializeConfigAndContainer(): void
    {
        // 1. 初始化容器
        Container::init();
        $this->container = Container::getInstance();

        // 2. 启动内核
        $this->kernel = new Kernel($this->container);
        $this->kernel->boot();

        // 3. 从容器获取日志服务
        try {
            $this->logger = $this->container->get('log');
        } catch (Throwable $e) {
            // 回退到 null，并在必要时使用 logError
            $this->logger = null;
            // 仅在调试时可能需要知道为什么日志初始化失败
            $this->logError('Logger initialization warning: ' . $e->getMessage());
        }
    }

    /**
     * 初始化路由和中间件.
     */
    private function initializeDependencies(): void
    {
        // 1. 初始化插件管理器
        $this->initializePluginManager();

        // 2. 加载路由（主应用 + 插件，支持缓存）
        $allRoutes = $this->loadAllRoutes();

        // 3. 初始化中间件调度器
        // 优先尝试容器获取，否则新建
        try {
            if ($this->container->has(MiddlewareDispatcher::class)) {
                $this->middlewareDispatcher = $this->container->get(MiddlewareDispatcher::class);
            } else {
                $this->middlewareDispatcher = new MiddlewareDispatcher($this->container);
            }
        } catch (Throwable $e) {
            // 回退
            $this->middlewareDispatcher = new MiddlewareDispatcher($this->container);
            $this->logError('Failed to initialize MiddlewareDispatcher: ' . $e->getMessage());
        }

        // 4. 初始化路由
        $this->router = new Router(
            $allRoutes,
            $this->container,
			#$this->logger,
            self::MAIN_CONTROLLER_NAMESPACE
        );
		// 5. 从容器获取缓存实例
		$cacheService = app('cache');

		// 6. 注入到 Router
		// 必须做类型检查，因为 Router 强类型要求 Psr\SimpleCache\CacheInterface
		if ($cacheService instanceof CacheInterface) {
			$this->router->setCache($cacheService);
		} else {
			// 假如你的 cache 是 PSR-6 (Symfony CacheItemPool)，可以用适配器转一下
			// $psr16Cache = new \Symfony\Component\Cache\Psr16Cache($cacheService);
			// $router->setCache($psr16Cache);
			
			// 或者记录个日志警告
			error_log("Warning: app('cache') does not implement PSR-16 SimpleCache.");
		}
		
		// 7. 配置安全策略 (可选，但推荐)
		$this->router->setSecurityPolicy(
			requireExplicitAction: false, // 默认关闭，建议开启，强制要求 #[Action]
			blacklist: []
		);
		
    }

    /**
     * 初始化插件管理器
     */
    private function initializePluginManager(): void
    {
        // 检查插件配置文件是否存在
        if (!file_exists(self::PLUGIN_CONFIG_FILE)) {
            $this->pluginManager = null;
            return;
        }

        try {
            $pluginConfig = require self::PLUGIN_CONFIG_FILE;
            $this->pluginManager = new PluginManager($pluginConfig);
            $this->pluginManager->discover();
            $this->pluginManager->loadEnabled();

            $loadedPlugins = array_keys($this->pluginManager->getLoaded());
            if (!empty($loadedPlugins)) {
                $this->logger?->info('[Plugins] Loaded: ' . implode(', ', $loadedPlugins));
            }
        } catch (Throwable $e) {
            $this->logError('Failed to initialize PluginManager: ' . $e->getMessage());
            $this->pluginManager = null;
        }
    }

    /**
     * FPM入口：完整调度流程.
     */
    public function run(): void
    {
        $request  = Request::createFromGlobals();
        $response = $this->dispatch($request);
        $response->send();
    }

    /*
     * 由workerman调度
     * 传入的是symfony 的request
     */
    public function handleRequest(Request $request): Response
    {
        return $this->dispatch($request);
    }

    /**
     * 获取容器（对外提供接口）.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 核心统一调度入口（FPM/Workerman/Swoole 都走这里）.
     */
    private function dispatch(Request $request): Response
    {
        $start         = microtime(true);
        $this->request = $request;

        $response = new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);

        try {
            $route = $this->router->match($this->request);
			
			
            if ($route === null || $route === false) {
                $response = $this->handleNotFound();
                $this->logRequestAndResponse($this->request, $response, $start);
                return $response;
            }

            if ($this->isEasterEggRoute($route)) {
                $response = $this->handleEasterEgg($route);
                $this->logRequestAndResponse($this->request, $response, $start);
                return $response;
            }
			
            $this->request->attributes->set('_route', $route);

            $response = $this->middlewareDispatcher->dispatch(
                $this->request,
                fn (Request $req): Response => $this->callController($route)
            );

            if (! $response instanceof Response) {
                $response = $this->normalizeResponse($response);
            }

            $this->logRequestAndResponse($this->request, $response, $start);
            return $response;
        } catch (Throwable $e) {
            return $this->handleException($e);
        } finally {
            // Workerman 下必须释放
            $this->request = null;
        }
    }

    /**
     * 记录简单错误到 storage/logs/error.log（用于在容器日志不可用时回退）.
     */
    private function logError(string $message): void
    {
        $logDir = BASE_PATH . '/storage/logs';

        if (! is_dir($logDir)) {
            // 使用常量权限
            if (! mkdir($logDir, self::DIR_PERMISSION, true) && ! is_dir($logDir)) {
                return; // 无法创建日志目录，放弃记录
            }
        }

        $file = $logDir . '/error.log';
        $time = date('Y-m-d H:i:s');

        file_put_contents($file, "[{$time}] {$message}\n", FILE_APPEND);
    }


    /**
     * 加载所有路由（手动+注解，支持环境区分的缓存）.
     */
    private function loadAllRoutes(): RouteCollection
    {
        $isProduction = false;
        if (function_exists('config')) {
            /** @noinspection PhpUndefinedFunctionInspection */
            $isProduction = (string) config('app.env') === 'prod';
        }

        // 生产环境且缓存存在时，直接加载缓存
        if ($isProduction && file_exists(self::ROUTE_CACHE_FILE)) {
            $serializedRoutes = file_get_contents(self::ROUTE_CACHE_FILE);
            if ($serializedRoutes !== false) {
                $routes = unserialize($serializedRoutes);
                if ($routes instanceof RouteCollection) {
                    $this->logger?->info('Loaded routes from cache');
                    return $routes;
                }

                $this->logger?->warning('Route cache is invalid, regenerating');
                unlink(self::ROUTE_CACHE_FILE);
            }
        }

        // 1. 加载手动路由
        $manualRoutes = null;
        $manualCount  = 0;
        $allRoutes    = new RouteCollection();

        $routesFile = BASE_PATH . '/config/routes.php';
        if (file_exists($routesFile)) {
            $manualRoutes = require $routesFile;
            if ($manualRoutes instanceof RouteCollection) {
                $allRoutes->addCollection($manualRoutes);
                $manualCount = $manualRoutes->count();
            }
        }

        // 2. 加载 Attribute 注解路由（主应用）
        $attrLoader = new AttributeRouteLoader(
            self::MAIN_CONTROLLER_DIR,
            self::MAIN_CONTROLLER_NAMESPACE
        );

        $annotatedRoutes = $attrLoader->loadRoutes();
        $annotatedCount  = 0;

        if ($annotatedRoutes instanceof RouteCollection) {
            $allRoutes->addCollection($annotatedRoutes);
            $annotatedCount = $annotatedRoutes->count();
        }

        // 3. 加载插件路由
        $pluginCount = 0;
        if ($this->pluginManager !== null) {
            $pluginControllerDirs = $this->pluginManager->getControllerDirs();
            if (!empty($pluginControllerDirs)) {
                $pluginRoutes = $attrLoader->loadRoutesFromMultipleDirs($pluginControllerDirs);
                if ($pluginRoutes instanceof RouteCollection) {
                    $allRoutes->addCollection($pluginRoutes);
                    $pluginCount = $pluginRoutes->count();
                }
            }
        }

        // 生产环境缓存路由
        if ($isProduction) {
            $this->cacheRoutes($allRoutes);
        }

        $this->logger?->info(sprintf(
            '[Route Loaded] Loaded %d routes (manual: %d, annotated: %d, plugins: %d)',
            $allRoutes->count(),
            $manualCount,
            $annotatedCount,
            $pluginCount
        ));

        return $allRoutes;
    }

    /**
     * 获取插件管理器
     *
     * @return PluginManager|null
     */
    public function getPluginManager(): ?PluginManager
    {
        return $this->pluginManager;
    }

    /**
     * 缓存路由集合（添加序列化错误处理）.
     */
    private function cacheRoutes(RouteCollection $routes): void
    {
        $serialized = serialize($routes);
        if ($serialized === false) {
            throw new RuntimeException('Failed to serialize route collection');
        }

        file_put_contents(self::ROUTE_CACHE_FILE, $serialized);
        chmod(self::ROUTE_CACHE_FILE, 0644); // 缓存文件权限只读
    }

    /**
     * 调用控制器方法（优化参数解析和返回值处理）.
     *
     * @param array<string,mixed> $route
     */
    private function callController(array $route): Response
    {
        $controllerClass = $route['controller'] ?? '';
        $method          = $route['method']     ?? '';
        $routeParams     = $route['params']     ?? [];

        if ($controllerClass === '' || $method === '') {
            return $this->handleNotFound();
        }

        // 运行时兜底：插件未安装/已卸载(或未启用)时，禁止继续访问插件控制器
        if (! $this->isPluginControllerAvailable($controllerClass)) {
            return BaseJsonResponse::error('插件未安装或已卸载', Response::HTTP_NOT_FOUND);
        }

        // 从容器获取控制器实例（支持依赖注入）
        //$controller = $this->container->get($controllerClass);
        // 它会尝试从容器获取，如果获取不到，会自动 new 并执行我们注入逻辑#
        $controller = \Framework\Core\App::make($controllerClass);
		
        // 处理路径参数和查询参数的类型转换
        $this->processRequestParameters($controllerClass, $method, $routeParams);

        // 解析控制器方法参数（Symfony ArgumentResolver）
        $argumentResolver = new ArgumentResolver();
        $arguments        = $argumentResolver->getArguments($this->request, [$controller, $method]);

        // 调用控制器方法
        $response = $controller->{$method}(...$arguments);

        // 统一处理返回值
        return $this->normalizeResponse($response);
    }

    /**
     * 判断插件控制器当前是否可访问（已安装且启用）
     */
    private function isPluginControllerAvailable(string $controllerClass): bool
    {
        if (!str_starts_with($controllerClass, 'Plugins\\')) {
            return true;
        }

        $segments = explode('\\', $controllerClass);
        $pluginNamespaceName = $segments[1] ?? '';
        if ($pluginNamespaceName === '') {
            return false;
        }

        if (!file_exists(self::PLUGIN_CONFIG_FILE)) {
            return false;
        }

        $config = require self::PLUGIN_CONFIG_FILE;
        $installed = is_array($config['installed'] ?? null) ? $config['installed'] : [];
        if (empty($installed)) {
            return false;
        }

        $pluginKey = null;
        if (array_key_exists($pluginNamespaceName, $installed)) {
            $pluginKey = $pluginNamespaceName;
        } else {
            $needle = strtolower($pluginNamespaceName);
            foreach (array_keys($installed) as $name) {
                if (strtolower((string) $name) === $needle) {
                    $pluginKey = (string) $name;
                    break;
                }
            }
        }

        if ($pluginKey === null) {
            return false;
        }

        $pluginInfo = is_array($installed[$pluginKey] ?? null) ? $installed[$pluginKey] : [];
        return ($pluginInfo['enabled'] ?? false) === true;
    }

    /**
     * 处理请求参数类型转换.
     *
     * @param class-string        $controllerClass
     * @param array<string,mixed> $routeParams
     */
    private function processRequestParameters(string $controllerClass, string $method, array $routeParams): void
    {
        try {
            $reflection = new \ReflectionMethod($controllerClass, $method);
        } catch (Throwable $e) {
            // 如果反射失败则跳过类型转换
            $this->logger?->warning('ReflectionMethod failed', ['exception' => $e]);
            return;
        }

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $type      = $param->getType();

            // 优先获取路径参数，其次查询参数
            if (array_key_exists($paramName, $routeParams)) {
                $value = $routeParams[$paramName];
            } elseif ($this->request->query->has($paramName)) {
                $value = $this->request->query->get($paramName);
            } else {
                // 无参数值，跳过
                continue;
            }

            // 内置类型转换
            if ($value !== null && $type !== null && $type->isBuiltin()) {
                $typedName = $type->getName();
                $value     = $this->castValueToType($value, $typedName);
                $this->request->attributes->set($paramName, $value);
            }
        }
    }

    /**
     * 类型转换工具方法.
     */
    private function castValueToType(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int'    => (int) $value,
            'float'  => (float) $value,
            'bool'   => (bool) filter_var((string) $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?: false,
            'string' => (string) $value,
            'array'  => is_array($value) ? $value : explode(',', (string) $value),
            default  => $value,
        };
    }

    /**
     * 标准化响应格式.
     */
    private function normalizeResponse(mixed $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        if ($response === null) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        if (is_array($response) || is_object($response)) {
            $payload = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $payload = $payload === false ? '' : $payload;

            return new Response(
                $payload,
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response((string) $response, Response::HTTP_OK);
    }

    /**
     * 处理 404 错误.
     */
    private function handleNotFound(): Response
    {
        $content = '404 Not Found';
        try {
            $content = view('errors/404.html.twig', [
                'status_code' => 404,
                'path'        => $this->request->getPathInfo(),
            ]);
        } catch (Throwable) {
            // ignore
        }
        return new Response($content, Response::HTTP_NOT_FOUND);
    }


    /**
     * 处理异常.
     */
    private function handleException(Throwable $e): Response
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($e instanceof HttpExceptionInterface) {
            $statusCode = (int) $e->getStatusCode();
        } else {
            $code = (int) $e->getCode();
            if ($code >= 400 && $code <= 599) {
                $statusCode = $code;
            }
        }

        // 准备模板所需的所有变量（直接传递具体值，不依赖模板函数）
        $templateVars = [
            // 异常信息
            'exception_class'   => get_class($e),
            'exception_code'    => $statusCode,
            'exception_message' => $e->getMessage(),
            'exception_file'    => $e->getFile(),
            'exception_line'    => $e->getLine(),
            'trace'             => $e->getTraceAsString(),
            'stack_frames'      => count($e->getTrace()), // 堆栈帧数

            // 请求信息（从当前 request 对象获取）
            'request_method' => $this->request->getMethod(),
            'request_uri'    => $this->request->getUri(),
            'client_ip'      => $this->request->getClientIp() ?: 'unknown',
            'request_time'   => date('Y-m-d H:i:s'),
            'user_agent'     => $this->request->headers->get('User-Agent') ?: 'unknown',

            // 环境信息（从容器或配置获取）
            'php_version' => PHP_VERSION,
            'app_env'     => function_exists('config') ? config('app.env') : 'prod',
            'app_debug'   => function_exists('config') ? config('app.debug') : false,
        ];

        // 开发环境渲染调试模板
        $content = '';
        try {
            if (function_exists('config') && config('app.debug')) {
                $content = view('errors/debug.html.twig', $templateVars);
            } else {
                $content = view('errors/500.html.twig', [
                    'status_code' => $statusCode,
                    'status_text' => Response::$statusTexts[$statusCode] ?? 'Server Error',
                    'message'     => 'An unexpected error occurred. Please try again later. 程序发生错误，请稍后再试！',
                ]);
            }
        } catch (Throwable $e2) {
            // 记录渲染模板失败的错误日志
            $this->logError('Failed to render exception view: ' . $e2->getMessage());
            // 兜底返回简单的错误文本，避免二次报错
            $content = 'Server Error~';
        }

        return new Response($content, $statusCode);
    }

    /**
     * 彩蛋路由判断.
     *
     * @param array<string,mixed> $route
     */
    private function isEasterEggRoute(array $route): bool
    {
        if (!isset($route['controller'], $route['method'])) {
            return false;
        }

        return
            ($route['controller'] === '__FrameworkVersionController__' && $route['method'] === '__showVersion__')
            || ($route['controller'] === '__FrameworkTeamController__' && $route['method'] === '__showTeam__');
    }

    /**
     * 处理彩蛋响应.
     *
     * @param array<string,mixed> $route
     */
    private function handleEasterEgg(array $route): Response
    {
        if (isset($route['controller']) && $route['controller'] === '__FrameworkVersionController__') {
            return EasterEgg::getResponse();
        }

        return EasterEgg::getTeamResponse();
    }

    /**
     * 记录请求和响应日志.
     */
    private function logRequestAndResponse(Request $request, Response $response, float $startTime): void
    {
        $duration = microtime(true) - $startTime;

        try {
            $this->logger?->info('[Request processed]', [
                'method'   => $request->getMethod(),
                'path'     => $request->getPathInfo(),
                'status'   => $response->getStatusCode(),
                'duration' => round($duration * 1000, 2) . 'ms', // 转换为毫秒
                'ip'       => $request->getClientIp(),
            ]);
        } catch (Throwable $e) {
            // 回退到文件日志
            $this->logError('Failed to write structured request log: ' . $e->getMessage());
        }
    }
}
