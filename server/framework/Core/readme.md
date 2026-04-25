关于Framework\Core\App，可以看作是 Framework\Container\Container 类快捷使用助手类

两者的区别如下：
### 单一职责原则 (Single Responsibility Principle)：
Framework\Container\Container 类已经承担了与容器相关的核心职责：初始化、加载配置、编译、缓存以及提供 make 方法。将 singleton 这种服务注册的核心功能放在这里，符合其作为容器管理器的职责。

Framework\Core\App 类的主要作用是作为一个静态代理或门面 (Facade)，为全局访问容器提供一个简单、统一的入口。它不应该包含容器的核心实现逻辑，否则会变得臃肿，违背了其作为 “入口” 的单一职责。

### 代码组织与清晰度：

所有与容器构建、配置、注册相关的逻辑都集中在 Container 类中，使得代码结构更加清晰。开发者在寻找容器相关功能时，可以直接在 Container 类中找到，而不需要在 App 和 Container 之间来回切换。

App 类则可以保持简洁，只暴露 get、make、has 等最终用户常用的方法，对用户隐藏容器内部复杂的实现细节。

### 与现有 make 方法的协同：

在 Container 类中已经实现了一个自定义的 make 方法，该方法在容器中找不到服务时会使用反射来创建实例。将 singleton 方法也放在这里，可以让 make 方法和 singleton 方法共享同一个容器实例（self::$container），它们之间的交互（例如，make 优先从容器获取已注册的单例）会更加直接和高效。

### 避免循环依赖和状态管理问题：

App 类持有一个 Container 的实例。如果 App 负责注册服务，它需要调用自身或 Container 的方法，这可能会引入不必要的复杂性。将注册逻辑放在 Container 内部，可以更好地管理自身的状态。


```php
// 1. 初始化框架，这会调用 Container::init()
// ... (你的启动代码)

// 2. 获取 App 或 Container 实例来注册服务
$app = App::class; // 或者 $container = Container::getInstance();

// 假设 $db 是一个已经实例化的数据库连接对象
$db = new \Some\Database\Connection();

// 使用 App 类（推荐，保持统一入口）
App::singleton('db', function() use ($db) {
    echo "创建数据库管理器实例...\n";
    return $db->getDatabaseManager();
});

// 或者直接使用 Container 实例
// $container->singleton('db', function() use ($db) { ... });

// 3. 获取服务
$dbManager1 = App::get('db'); // 输出 "创建数据库管理器实例..."
$dbManager2 = App::make('db');

var_dump($dbManager1 === $dbManager2); // bool(true)

$dbManager3 = App::get('db'); // 不再输出，直接返回已创建的实例
var_dump($dbManager1 === $dbManager3); // bool(true)
```

```php

use Framework\Core\App;
use Symfony\Component\DependencyInjection\ContainerBuilder;

// ... 假设 $db 已经实例化 ...

// 1. 创建一个 ContainerBuilder 实例
$containerBuilder = new ContainerBuilder();

// 2. 将其设置为 App 的容器
App::setContainer($containerBuilder);

// 3. 使用新的 singleton 方法注册你的服务
App::singleton('db', function () use ($db) {
    // 这个闭包只会在第一次获取 'db' 服务时被调用一次
    echo "工厂闭包执行了...\n";
    return $db->getDatabaseManager();
});

// 此时容器还未编译，你可以继续注册其他服务...

// 4. (可选但推荐) 编译容器
// 编译可以优化容器性能，并执行一些验证。
// 编译后，你就不能再注册新服务了，但仍然可以获取已注册的服务。
$containerBuilder->compile();

// 5. 获取服务
$dbManager1 = App::get('db'); // 会输出 "工厂闭包执行了..."，并返回实例
$dbManager2 = App::get('db'); // 不会输出任何内容，直接返回同一个实例

var_dump($dbManager1 === $dbManager2); // 输出: bool(true)，证明是同一个实例

// 使用 make 也能获取到同一个单例实例，因为服务已注册
$dbManager3 = App::make('db');
var_dump($dbManager1 === $dbManager3); // 输出: bool(true)

```