Framework\Container\Container 类设计思路是围绕 Symfony DI 容器构建一个统一入口类，既负责：

- .env 环境加载；
- services.php 服务定义文件加载；
- 生产环境容器编译缓存；
- 对 ContainerInterface 的代理封装。
- 兼容psr-13的接口

Container.php 内部逻辑示意图 + 初始化流程图，用文字图（ASCII 风格）和流程描述，让整个 compile / cache / prod/dev 逻辑一目了然

1️⃣ Container.php 内部逻辑示意图（单文件概览）

```cpp
Container (implements Symfony ContainerInterface)
├─ private static $container : SymfonyContainerInterface|null
├─ private const CACHE_FILE = BASE_PATH.'/storage/cache/container.php'
├─ public static init(array $parameters = [])
│   ├─ 如果 self::$container 已存在 -> return
│   ├─ 加载 .env
│   ├─ 获取 APP_ENV, 判断是否生产环境 $isProd
│   ├─ 确认 config 目录存在
│   ├─ 确认 services.php 文件存在
│   ├─ 创建 ContainerBuilder
│   │   ├─ 设置 kernel.project_dir
│   │   ├─ 设置 kernel.debug
│   │   └─ 设置 kernel.environment
│   ├─ 如果 $parameters 不为空 -> 注入 'config' 参数
│   ├─ 加载服务文件 PhpFileLoader(services.php)
│   ├─ $containerBuilder->compile()
│   ├─ 如果 $isProd (生产环境)
│   │   ├─ mkdir cache 目录
│   │   ├─ PhpDumper dump -> cacheContent
│   │   ├─ 写入 CACHE_FILE
│   │   └─ require CACHE_FILE -> self::$container
│   └─ 否则 (开发环境)
│       └─ self::$container = $containerBuilder
├─ public static getInstance(): self
│   └─ 调用 init() -> return new self()
├─ PSR-11 代理方法
│   ├─ get($id)
│   ├─ has($id)
│   ├─ getParameter($name)
│   └─ setParameter($name, $value)

```


2️⃣ Container 初始化流程图（ASCII 风格）

```cpp
+-------------------------+
|  Container::init()      |
+-------------------------+
            |
            v
+-------------------------+
| 已有容器? self::$container != null
+-------------------------+
            | Yes
            v
       return existing
            |
           No
            v
+-------------------------+
| 加载 .env 文件          |
+-------------------------+
            |
            v
+-------------------------+
| 读取 APP_ENV, APP_DEBUG  |
| 判断 $isProd            |
+-------------------------+
            |
            v
+-------------------------+
| 检查 config/ 目录和     |
| services.php 文件       |
+-------------------------+
            |
            v
+-------------------------+
| 创建 ContainerBuilder   |
| 设置参数 kernel.*       |
| 注入全局参数 $parameters|
+-------------------------+
            |
            v
+-------------------------+
| 加载服务文件 services.php|
+-------------------------+
            |
            v
+-------------------------+
| $containerBuilder->compile() |
+-------------------------+
            |
            v
+-------------------------+
| 生产环境? $isProd        |
+-------------------------+
   | Yes                   | No
   v                       v
+-------------------+   +-------------------+
| 生成 PhpDumper    |   | 使用 ContainerBuilder |
| dump -> cache.php  |   | 直接作为 self::$container |
| require cache.php  |   +-------------------+
| self::$container=loaded
+-------------------+

```

3️⃣ 核心思路总结

单例静态容器：self::$container

开发环境：每次都重新构建，保证修改立即生效

生产环境：生成编译缓存文件，避免每次构建

PSR-11 兼容：提供 get() / has() 方法

参数注入：kernel.* + 全局 config

服务加载：services.php + autowire + public + args

最终容器可通过：

Container::getInstance() 获取容器对象

App::setContainer(Container::getInstance()) 注入全局

helpers: app('service_id') / getService(ClassName::class)

===================================================

下面我画一张直观的 Provider 注册 & boot 顺序图，展示现在框架的流程，包括核心 Provider 和应用 Provider，以及 register() 和 boot() 阶段。为了清晰，我用文本流程图的形式描述
```
+------------------------+
| Container 初始化        |
|  ContainerConfigurator |
+------------------------+
            |
            v
+------------------------+
| 核心 Provider 注册阶段  |
| 读取 config/providers.php |
+------------------------+
            |
            v
+------------------------+
| 逐个 register() 调用   |
|  RequestProvider       |
|  ResponseProvider      |
|  SessionServiceProvider|
|  CookieServiceProvider |
|  MiddlewaresProvider   |
|  ConfigServiceProvider |
|  LoggerServiceProvider |
|  ...                   |
+------------------------+
            |
            v
+------------------------+
| 应用 Provider 注册阶段  |
| 扫描 app/Providers     |
| 自动 register()        |
+------------------------+
            |
            v
+------------------------+
| 所有 Provider boot()   |
|  按 loadedProviders 顺序|
| 核心 -> 应用 Provider   |
+------------------------+
            |
            v
+------------------------+
| 容器编译完成            |
|  可以安全获取任意服务   |
+------------------------+
```

说明

register() 阶段

只是注册服务定义到容器

不会触发服务实例化（延迟到容器编译时）

核心 Framework\Provider 应该先注册，保证 App Provider 可以引用它们的服务

boot() 阶段

触发 Provider 内的初始化逻辑（比如事件监听器、Session 启动）

顺序按 loadedProviders 数组，也就是先注册的先 boot()

如果某个 Provider 依赖其他 Provider 的初始化结果，必须确保它在依赖 Provider 之后注册

容器使用

ContainerConfigurator 会在 Framework\Container\Container 的 compile 阶段解析依赖

Autowire 会自动注入构造函数所需的类型

如果服务在 register() 时未定义，会报错.


--------------------------------------------------------------------------

除了 singleton , make，Container 类还可以实现多种服务注册方式，以满足不同的依赖注入需求。以下是几种常见的服务注册方式及其实现，结合Container 类进行扩展：

### 1.绑定接口到实现（Bind Interface to Implementation）
将一个接口绑定到一个具体的实现类，容器会自动解析接口为对应的实现。
实现思路
- 使用 setDefinition 注册接口，并指定其实现类。
- 可以选择是否为单例。

在 Container 类中添加：

```
public function bind(string $abstract, string $concrete, bool $shared = false): void
{
    if (self::$container === null) {
        throw new \RuntimeException('容器尚未初始化。');
    }

    if (!self::$container instanceof ContainerBuilder) {
        throw new \RuntimeException('当前容器不支持动态注册服务。');
    }

    $containerBuilder = self::$container;

    if ($containerBuilder->isCompiled()) {
        throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
    }

    $definition = new Definition($concrete);
    $definition->setShared($shared);

    $containerBuilder->setDefinition($abstract, $definition);
}
```
DEMO:
```php
// 绑定接口到实现
$container->bind(LoggerInterface::class, FileLogger::class);

// 解析接口时，容器会自动返回 FileLogger 实例
$logger = $container->get(LoggerInterface::class);
```

### 2.绑定工厂函数（Bind Factory Function）
通过一个工厂函数来创建服务实例，适用于需要复杂初始化逻辑的场景。
实现思路
- 使用 setFactory 指定一个闭包或可调用对象作为工厂。
- 可以选择是否为单例。

在 Container 类中添加：
```
public function factory(string $id, callable $factory, bool $shared = false): void
{
    if (self::$container === null) {
        throw new \RuntimeException('容器尚未初始化。');
    }

    if (!self::$container instanceof ContainerBuilder) {
        throw new \RuntimeException('当前容器不支持动态注册服务。');
    }

    $containerBuilder = self::$container;

    if ($containerBuilder->isCompiled()) {
        throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
    }

    $definition = new Definition();
    $definition->setFactory($factory);
    $definition->setShared($shared);

    $containerBuilder->setDefinition($id, $definition);
}
```
Demo

// 注册一个工厂函数来创建数据库连接
$container->factory('db.connection', function () {
    return new DatabaseConnection([
        'host' => 'localhost',
        'user' => 'root',
        'pass' => 'password',
    ]);
}, true); // 设为单例

// 获取服务
$db = $container->get('db.connection');

### 3.绑定实例（Bind Instance）
直接将一个已存在的对象实例注册到容器中，适用于预初始化的对象。
实现思路
- 使用 set 方法直接注册实例（Symfony 容器原生支持）。
- 注意：编译后的容器可能不支持 set 方法，因此需要在编译前调用。

在 Container 类中添加：
```php
public function instance(string $id, object $instance): void
{
    if (self::$container === null) {
        throw new \RuntimeException('容器尚未初始化。');
    }

    // 直接注册实例
    self::$container->set($id, $instance);
}
```

demo:
// 预创建一个实例
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// 注册实例
$container->instance('redis', $redis);

// 获取实例
$redisInstance = $container->get('redis');

### 4.绑定参数（Bind Parameter）
注册一个参数（如配置值），供其他服务依赖注入时使用。
实现思路
- 使用 setParameter 方法注册参数。
- 参数可以是字符串、数组、数字等。
代码实现
在 Container 类中添加：

```php
public function parameter(string $name, mixed $value): void
{
    if (self::$container === null) {
        throw new \RuntimeException('容器尚未初始化。');
    }

    self::$container->setParameter($name, $value);
}
```

DEMO:
```php
// 注册参数
$container->parameter('app.name', 'My Framework');
$container->parameter('database.config', [
    'host' => 'localhost',
    'user' => 'root',
]);

// 在服务中通过构造函数注入参数
class ConfigService
{
    public function __construct(
        string $appName,
        array $databaseConfig
    ) {
        // ...
    }
}

// 服务配置（services.php）
return [
    ConfigService::class => [
        'arguments' => [
            '$appName' => '%app.name%',
            '$databaseConfig' => '%database.config%',
        ],
    ],
];
```
### 5.绑定带标签的服务（Bind Tagged Services）
为服务添加标签，方便批量获取同一类服务（如事件监听器、命令等）。
实现思路
- 在服务定义中添加标签。
- 通过 findTaggedServiceIds 方法获取所有带特定标签的服务。
代码实现
在 Container 类中添加：

```php
public function tag(string $id, string $tag, array $attributes = []): void
{
    if (self::$container === null) {
        throw new \RuntimeException('容器尚未初始化。');
    }

    if (!self::$container instanceof ContainerBuilder) {
        throw new \RuntimeException('当前容器不支持动态注册服务。');
    }

    $containerBuilder = self::$container;

    if ($containerBuilder->isCompiled()) {
        throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
    }

    $definition = $containerBuilder->getDefinition($id);
    $definition->addTag($tag, $attributes);
}
```
DEMO:
```php
// 注册一个事件监听器并添加标签
$container->bind(OrderCreatedListener::class, OrderCreatedListener::class);
$container->tag(OrderCreatedListener::class, 'event.listener', ['event' => 'order.created']);

// 获取所有事件监听器
$listeners = $container->getContainer()->findTaggedServiceIds('event.listener');
foreach ($listeners as $id => $tags) {
    $listener = $container->get($id);
    // ...
}
```

### 6.绑定延迟服务（Bind Lazy Services）
延迟服务的初始化，直到第一次调用时才创建实例，适用于重量级服务。
实现思路
- 在服务定义中设置 setLazy(true)。
- Symfony 容器会自动生成一个代理类，延迟实例化。
代码实现
在 Container 类中添加：

```php
public function lazy(string $id, string $concrete, bool $shared = true): void
{
    if (self::$container === null) {
        throw new \RuntimeException('容器尚未初始化。');
    }

    if (!self::$container instanceof ContainerBuilder) {
        throw new \RuntimeException('当前容器不支持动态注册服务。');
    }

    $containerBuilder = self::$container;

    if ($containerBuilder->isCompiled()) {
        throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
    }

    $definition = new Definition($concrete);
    $definition->setShared($shared);
    $definition->setLazy(true);

    $containerBuilder->setDefinition($id, $definition);
}
```
DEMO:

```php
// 注册一个延迟初始化的服务
$container->lazy('heavy.service', HeavyService::class);

// 此时不会创建实例
$service = $container->get('heavy.service');

// 第一次调用方法时才会初始化
$service->doSomething();
```

### 总结
通过以上扩展，你的 Container 类将支持多种服务注册方式，覆盖了大部分常见的依赖注入场景：
- make: 单例服务 用于模拟 Laravel/Webman 的构建行为
- singleton: 单例服务。
- bind: 接口绑定实现。
- factory: 工厂函数创建服务。
- instance: 直接注册实例。
- parameter: 注册配置参数。
- tag: 为服务添加标签，方便批量获取。
- lazy: 延迟初始化服务。
这些方法可以根据实际需求选择性实现，建议优先实现make、 bind、factory 和 instance，以满足大部分开发场景。