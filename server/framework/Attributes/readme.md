## 关于注解注解型的控制器写法

我们这里演示如何使用注解声明

这个控制器类演示了如何在一个文件中混合使用 **Spring 风格路由注解** (`RequestMapping`, `GetMapping` 等)、**业务逻辑注解** (`Auth`, `Role`, `Log`) 以及 **通用中间件注入注解** (`Middlewares`)。

基于我们之前的分析，**路由器（Router）** 会先解析路由注解，**调度器（Dispatcher）** 随后会扫描所有注解并合并中间件，最终形成洋葱调用链。

### 示例代码：`User.php`

```php
<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: UserController.php
 * @Date: 2025-12-18
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// 引入元数据注解
use Framework\Attributes\Auth;
use Framework\Attributes\Role;
use Framework\Attributes\Log;
use Framework\Attributes\Middlewares;
use Framework\Attributes\Transaction; // 顺便加上事务演示
// 假设你有一些 Spring 风格的路由注解
use Framework\Attributes\Route;
use Framework\Attributes\Routes\GetMapping;
use Framework\Attributes\Routes\PostMapping;
use Framework\Attributes\Routes\DeleteMapping;

// 引入中间件类
use App\Middlewares\CorsMiddleware;
use App\Middlewares\RateLimitMiddleware;
use App\Middlewares\JsonFormatterMiddleware;

/**
 * 用户管理控制器
 * 
 * 1. #[Route]: 声明这是一个 API 控制器，路由前缀为 /api/v1/users
 * 2. #[Middlewares]: 类级别的中间件，该类下所有方法都会经过 Cors 和 JsonFormatter
 */
#[Route(prefix: '/api/v1/users')]
#[Middlewares([
    CorsMiddleware::class,          // 全局处理跨域
    JsonFormatterMiddleware::class  // 全局统一 JSON 返回格式
])]
class User
{
    /**
     * 用户登录
     * 
     * - 公开接口，不需要 #[Auth]
     * - 使用 #[Log] 记录尝试登录的行为
     * - 使用 #[PostMapping] 定义路由
     */
    #[PostMapping(path: '/login')]
    #[Log(description: '用户登录尝试', level: 'info')]
    public function login(Request $request): JsonResponse
    {
        // 模拟登录逻辑
        $username = $request->get('username');
        // ... 验证逻辑
        
        return new JsonResponse(['token' => 'mock_token_123456']);
    }

    /**
     * 获取个人资料
     * 
     * - #[Auth]: 需要登录才能访问
     * - #[Log]: 记录访问日志
     * - #[GetMapping]: GET 请求
     */
    #[GetMapping(path: '/profile')]
    #[Auth] 
    #[Log(description: '查看个人资料')]
    public function profile(): JsonResponse
    {
        // 中间件已验证身份，可直接获取当前用户
        // $user = app('auth')->user();
        
        return new JsonResponse([
            'id' => 1,
            'username' => 'xuey863toy',
            'role' => 'editor'
        ]);
    }

    /**
     * 管理员删除用户
     * 
     * 这是一个“重兵把守”的接口：
     * 1. #[Auth]: 必须登录
     * 2. #[Role]: 必须是 'admin' 或 'super_admin' 角色
     * 3. #[Middlewares]: 额外注入了 RateLimitMiddleware，防止暴力调用
     * 4. #[Transaction]: 数据库操作自动开启事务
     * 5. #[Log]: 记录高危操作日志，级别为 warning
     */
    #[DeleteMapping(path: '/{id}')]
    #[Auth]
    #[Role(roles: ['admin', 'super_admin'])]
    #[Middlewares([RateLimitMiddleware::class])] 
    #[Transaction]
    #[Log(description: '删除用户', level: 'warning')]
    public function delete(int $id): JsonResponse
    {
        // 业务逻辑：如果这里抛错，Transaction 会回滚
        // app('db')->table('users')->delete($id);
        
        return new JsonResponse(['message' => "User {$id} has been deleted."]);
    }
}
```

---

### 幕后：MiddlewareDispatcher 是如何工作的？

当请求 `DELETE /api/v1/users/1` 到达时，你的 **调度器 (Dispatcher)** 会按以下步骤“组装”中间件链：

1.  **收集 (Collect)**：
    *   **Global**: `[GlobalMiddlewareA, ...]`
    *   **Class (Middlewares)**: `[CorsMiddleware, JsonFormatterMiddleware]`
    *   **Method (Auth)**: `[AuthMiddleware]` (由 `#[Auth]` 提供)
    *   **Method (Role)**: `[RoleMiddleware]` (由 `#[Role]` 提供)
    *   **Method (Middlewares)**: `[RateLimitMiddleware]`
    *   **Method (Transaction)**: `[TransactionMiddleware]`
    *   **Method (Log)**: `[LogMiddleware]`

    *(注意：`#[Route]` 和 `#[DeleteMapping]` 属于路由注解，主要用于 UrlMatcher 匹配路由，它们解析出的中间件（如果有）也会被放入集合)*

2.  **拍平 (Flatten)**：
    调度器调用 `flattenArray`，将上述嵌套结构变成一维数组。

3.  **排序与去重 (Merge & Sort)**：
    最终执行顺序（洋葱模型，从外到内）：
    1.  `GlobalMiddleware` ...
    2.  `CorsMiddleware` (类级别)
    3.  `JsonFormatterMiddleware` (类级别)
    4.  `AuthMiddleware` (方法级，身份验证优先)
    5.  `RoleMiddleware` (方法级，权限验证次之)
    6.  `RateLimitMiddleware` (方法级，注入的限流)
    7.  `LogMiddleware` (日志记录)
    8.  `TransactionMiddleware` (事务包裹)
    9.  **`delete()` 方法本体**

4.  **属性注入 (Injection)**：
    在反射扫描阶段，`#[Role(['admin'])]` 这个**对象实例**已经被注入到了 `$request->attributes->set(Role::class, $instance)`。
    当 `RoleMiddleware` 执行时，它不需要知道自己是在哪里被定义的，它只需要：
    ```php
    $config = $request->attributes->get(Role::class);
    // $config->roles 就是 ['admin', 'super_admin']
    ```

这就是构建的这套架构的强大之处：**注解负责声明（What），调度器负责组装（How），中间件负责执行（Do），完美解耦。**



## 另外一个范例
下面是用Route 注解来写个范例

这个 `Route` 类非常强大，它实际上把 **路由定义** (`path`, `methods`) 和 **部分中间件配置** (`middleware`, `auth`, `roles`) 结合在了一起。

下面是基于 `Route` 注解，结合前面我们实现的 `Log`, `Transaction`, `Middlewares` 注解的 **UserController** 完整示例。

### 核心变化
1.  **类级别**：使用 `#[Route(prefix: '/api/v1/users')]` 替代 `#[RestController]`。
2.  **方法级别**：使用 `#[Route(path: '/xxx', methods: ['GET'])]` 替代 `#[GetMapping]`。
3.  **混合使用**：
    *   **路由控制**：交给 `#[Route]`。
    *   **业务增强**：交给 `#[Log]`, `#[Transaction]`。
    *   **中间件注入**：你可以选择写在 `#[Route(middleware: [...])]` 里（由 Router 提取），也可以写在 `#[Middlewares([...])]` 里（由 Dispatcher 提取）。**两者都能工作，最终都会被调度器合并。**

---

### User.php 示例代码

```php
<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: User.php
 * @Date: 2025-12-18
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// 1. 引入核心路由注解 (你提供的那个类)
use Framework\Attributes\Route;

// 2. 引入业务增强注解 (我们之前写的)
use Framework\Attributes\Auth;
use Framework\Attributes\Role;
use Framework\Attributes\Log;
use Framework\Attributes\Transaction;
use Framework\Attributes\Middlewares; // 额外的中间件注入

// 3. 引入中间件类
use App\Middlewares\CorsMiddleware;
use App\Middlewares\RateLimitMiddleware;
use App\Middlewares\JsonFormatterMiddleware;

/**
 * 用户管理控制器
 *
 * 使用 #[Route] 的 prefix 属性定义统一前缀。
 * 使用 #[Middlewares] 注入类级别的通用中间件（如 CORS, 格式化）。
 */
#[Route(prefix: '/api/v1/users')] 
#[Middlewares([
    CorsMiddleware::class, 
    JsonFormatterMiddleware::class
])]
class User
{
    /**
     * 用户登录
     * 
     * 路由：POST /api/v1/users/login
     * 功能：不需要认证，但记录日志。
     */
    #[Route(path: '/login', methods: ['POST'])]
    #[Log(description: '用户登录尝试', level: 'info')]
    public function login(Request $request): JsonResponse
    {
        // 模拟登录逻辑
        $username = $request->get('username');
        
        return new JsonResponse([
            'token' => 'mock_token_' . time(),
            'user' => $username
        ]);
    }

    /**
     * 获取个人资料
     * 
     * 路由：GET /api/v1/users/profile
     * 功能：需要认证。
     * 
     * 方式一：使用独立的 #[Auth] 注解 (推荐，语义清晰)
     * 方式二：使用 #[Route(auth: true)] (如果你路由层支持提取 auth)
     * 这里演示方式一，与 Log 配合。
     */
    #[Route(path: '/profile', methods: ['GET'])]
    #[Auth] 
    #[Log(description: '查看个人资料')]
    public function profile(): JsonResponse
    {
        // 假设中间件已将用户信息注入 request
        // $user = request()->attributes->get('current_user');
        
        return new JsonResponse([
            'id' => 1,
            'username' => 'xuey863toy',
            'role' => 'editor'
        ]);
    }

    /**
     * 管理员删除用户
     * 
     * 路由：DELETE /api/v1/users/{id}
     * 功能：
     * 1. 路由定义 (Route)
     * 2. 权限控制 (Auth + Role)
     * 3. 事务保护 (Transaction)
     * 4. 关键日志 (Log)
     * 5. 接口限流 (Middlewares) - 演示 Route 中间件和 Middlewares 注解混用
     */
    #[Route(
        path: '/{id}', 
        methods: ['DELETE'], 
        requirements: ['id' => '\d+'] // 限制 id 必须为数字
    )]
    #[Auth]                              // 1. 必须登录
    #[Role(roles: ['admin'])]            // 2. 必须是管理员
    #[Middlewares([RateLimitMiddleware::class])] // 3. 独立注入限流中间件
    #[Transaction]                       // 4. 数据库事务
    #[Log(description: '删除用户', level: 'warning')] // 5. 记录警告日志
    public function delete(int $id): JsonResponse
    {
        // 业务逻辑...
        // app('db')->table('users')->delete($id);
        
        return new JsonResponse(['message' => "User {$id} deleted successfully."]);
    }

    /**
     * 演示：直接利用 Route 注解自带属性
     * 
     * 如果你的 Router 层编写了逻辑，将 Route 中的 middleware 属性
     * 自动提取到 request 的 _middleware 数组中，
     * 那么你可以直接在 Route 里写中间件，而不用 #[Middlewares] 注解。
     */
    #[Route(
        path: '/test-legacy', 
        methods: ['GET'], 
        middleware: [RateLimitMiddleware::class], // 直接在 Route 里定义
        auth: true,  // 直接在 Route 里定义需要认证
        roles: ['editor'] // 直接在 Route 里定义角色
    )]
    public function testLegacy(): JsonResponse
    {
        return new JsonResponse(['msg' => 'Mixed style works too!']);
    }
}
```

### 架构解析：这一切是如何协同工作的？

1.  **路由解析阶段 (UrlMatcher / Router)**：
    *   扫描到 `#[Route(path: '/login', ...)]`。
    *   解析 `prefix` 和 `path` 生成最终路由 `/api/v1/users/login`。
    *   **关键点**：如果你的 `Router` 代码里有逻辑去读取 `#[Route]` 中的 `middleware`, `auth`, `roles` 属性，它应该把这些信息转换成具体的中间件类名，并存入 `$request->attributes->get('_middleware')`。

2.  **调度阶段 (MiddlewareDispatcher)**：
    *   它拿到 `$request`。
    *   **来源 A**：它从 `$request->attributes->get('_middleware')` 获取路由层解析出的中间件（包括 `#[Route]` 里写的）。
    *   **来源 B**：它**再次反射**控制器方法，发现了 `#[Log]`, `#[Transaction]`, `#[Middlewares]` 这些独立注解。
    *   **合并**：它把来源 A 和来源 B 的中间件合并。

3.  **结果**：
    *   `login` 方法：执行 `Cors` -> `JsonFormatter` -> `Log` -> 业务逻辑。
    *   `delete` 方法：执行 `Cors` -> `JsonFormatter` -> `Auth` -> `Role` -> `RateLimit` -> `Transaction` -> `Log` -> 业务逻辑。

这种写法非常灵活，既保留了 Symfony 路由的强大配置能力，又利用了 PHP 8 Attributes 实现了优雅的 AOP 切面编程。