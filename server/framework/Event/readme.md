无论你是使用 **接口方式** 还是 **注解方式 (`#[EventListener]`)** 来定义监听器，**在控制器中触发事件的代码是完全一样的**。

这是 PSR-14 事件系统的设计精髓：**发布者（Controller）不需要知道订阅者（Listener）是谁，也不需要知道订阅者是用什么方式注册的。**

下面是具体的范例，展示如何在控制器中优雅地触发事件。

### 场景设定
*   **事件**: `UserLoggedIn` (用户登录)
*   **监听器**: 使用注解定义的 `LogUserLogin`
*   **控制器**: `AuthController`

---

### 1. 准备工作 (回顾)

**事件类 (Event):**
```php
namespace App\Events;

class UserLoggedIn
{
    public function __construct(
        public int $userId,
        public string $ip
    ) {}
}
```

**监听器 (Listener - 注解版):**
```php
namespace App\Listeners;

use App\Events\UserLoggedIn;
use Framework\Event\Attribute\EventListener;

class LogUserLogin
{
    // ✅ 新的注解方式，系统会自动扫描到这里
    #[EventListener(priority: 100)]
    public function onLogin(UserLoggedIn $event): void
    {
        app('log')->info("注解监听到了：用户 {$event->userId} 登录了！");
    }
}
```

---

### 2. 在控制器中触发 (两种方式)

#### 方式 A：依赖注入 (推荐 ✅)
这是最符合现代化框架（如 Symfony/Laravel）规范的做法。通过构造函数注入 `EventDispatcherInterface`。

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Events\UserLoggedIn; // 引入事件类
use Psr\EventDispatcher\EventDispatcherInterface; // 引入PSR标准接口
use Symfony\Component\HttpFoundation\Request;

class AuthController
{
    // 通过构造函数自动注入分发器
    public function __construct(
        private EventDispatcherInterface $dispatcher
    ) {}

    public function login(Request $request): void
    {
        // 1. 模拟用户登录逻辑
        $userId = 10086;
        $ip = $request->getClientIp() ?? '127.0.0.1';

        echo "正在处理登录逻辑...<br>";

        // 2. 实例化事件对象
        $event = new UserLoggedIn($userId, $ip);

        // 3. ✅ 触发事件！
        // Dispatcher 会去查找所有关注 UserLoggedIn 的监听器（含注解注册的）并执行
        $this->dispatcher->dispatch($event);

        echo "登录流程结束。<br>";
    }
}
```

**为什么推荐这种方式？**
*   **解耦**: 控制器不依赖具体的 `Framework\Event\Dispatcher` 类，只依赖标准接口。
*   **清晰**: 一眼就能看出这个控制器需要发送事件。

---

#### 方式 B：使用全局辅助函数 (快捷 ⚡)
如果你在框架中封装了类似 `event()` 或 `app()` 的助手函数，也可以直接调用。

```php
<?php

namespace App\Controllers;

use App\Events\UserLoggedIn;

class AuthController
{
    public function login(): void
    {
        $userId = 888;
        
        // 1. 创建事件
        $event = new UserLoggedIn($userId, '192.168.1.1');

        // 2. ✅ 获取分发器并触发
        // 假设 app() 可以获取容器实例，并且你已经注册了 Dispatcher
        app(\Framework\Event\Dispatcher::class)->dispatch($event);
        
        // 或者如果你封装了全局函数 event()
        // event($event); 
    }
}
```

---

### 3. 确保依赖注入生效的关键点

为了让 **方式 A** 能正常工作，你需要确保在 DI 容器中，`Psr\EventDispatcher\EventDispatcherInterface` 接口指向了你自己的 `Dispatcher` 实现类。

在你的 `App\Providers\EventProvider` 或 `AppServiceProvider` 中添加别名：

```php
use Framework\Event\Dispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;

public function register(ContainerConfigurator $configurator): void
{
    $services = $configurator->services();

    // ... 注册 Dispatcher ...
    $services->set(Dispatcher::class)
        ->autowire()
        ->public();

    // ✅ 【关键】绑定接口别名
    // 当控制器请求 EventDispatcherInterface 时，容器会给它 Dispatcher 实例
    $services->alias(EventDispatcherInterface::class, Dispatcher::class);
}
```

### 总结流程图

1.  **Controller**: `new UserLoggedIn(...)` (创建快递)
2.  **Controller**: `$dispatcher->dispatch($event)` (把快递交给快递站)
3.  **Dispatcher**: 查找注册表（包含了之前通过 `Scanner` 扫描到的**注解**和**接口**监听器）。
4.  **Dispatcher**: 发现 `LogUserLogin::onLogin` 带有 `#[EventListener]` 且参数类型匹配。
5.  **Dispatcher**: 调用 `LogUserLogin->onLogin($event)`。
6.  **Listener**: 执行逻辑。