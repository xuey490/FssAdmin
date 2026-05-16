
### 其它上手教程：

* ### [数据层（Dao）的操作是如何与模型层交互的？](https://github.com/xuey490/project/blob/main/framework/Basic/Index0.md "Index0.md")
* ### [ 如何在控制器中调用服务层的方法？](https://github.com/xuey490/project/blob/main/framework/Basic/Index1.md "Index1.md")
* ### [ 服务层调用数据层（DAO/Repository）](https://github.com/xuey490/project/blob/main/framework/Basic/Index2.md "Index2.md")
* ### [ 如何在模型层中调用数据层的方法？](https://github.com/xuey490/project/blob/main/framework/Basic/Index3.md "Index3.md")


## 如何写控制器的代码

在很多情况下**连构造函数都不需要写**。


### 1. 原理查看 `BaseController`

定义 `protected DatabaseFactory $db` 属性（注意必须是 `protected`，如果是 `private` 子类就用不了了）。


无参数构造函数

```php
<?php

declare(strict_types=1);

namespace Framework\Basic;

use Symfony\Component\HttpFoundation\Request;
use Framework\Database\DatabaseFactory; // 引入类

abstract class BaseController
{
    // ... traits ...

    protected Request $request;
    
    // 这里的类型最好稍微宽泛一点或者确定的接口，
    // 如果你有 BaseService 接口最好，没有的话用 object 也可以，但建议用 BaseService
    protected object $service; 
    
    // 新增：数据库工厂，设为 protected 供子类使用
    protected DatabaseFactory $db;

    protected ?object $validator = null;
    
    protected string $serviceClass = '';

    // 构造函数不接受参数，完全由内部解决
    public function __construct()
    {
        // 1. 获取全局 Request
        $this->request = app('request'); 
        
        // 2. 获取全局 DB
        $this->db = app('db'); 
        
        // 3. 【自动初始化 Service】
        // 如果子类定义了 serviceClass，父类自动帮你实例化！
        // 只有定义了 serviceClass 才初始化，没定义就算了，说明这个控制器不需要通用CRUD
        if (!empty($this->serviceClass)) {
            $this->service = app()->make($this->serviceClass);
        }
		
        // 4. 钩子
        $this->initialize();
    }

    
    // ... 
}
```

---

### 2. 极致精简后的 `User` 控制器

现在，你的 `User` 控制器可以享受到**依赖注入容器**（DI Container）带来的巨大便利。

只要你的框架容器（看起来像是类似 Laravel/Symfony 的容器）支持自动解析父类构造函数，你的 `User` 类可以写成这样：

#### 方案 A：由容器自动完成一切（推荐，最干净）

**连构造函数都不用写了！** 容器会自动识别 `BaseController` 的构造函数签名，并自动注入 `Request`, `DatabaseFactory`。至于 `$service`，因为它是可选的（null），容器会传 null，然后基类代码会读取 `$serviceClass` 自动创建服务。

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use Framework\Basic\BaseController;

/**
 * 用户管理控制器
 * @property UserService $service 
 */
class User extends BaseController
{
    // 指定服务类名，基类会自动创建
    protected string $serviceClass = UserService::class;

    // 无需编写 __construct！
    // 父类的 __construct 会自动被调用，
    // $this->request, $this->db, $this->service 都会自动准备好。

    public function list()
    {
        // 这里的 $this->db 可以直接使用
        // $this->db->query(...) 
        
        $data = $this->service->listUsers([], []);
        return $this->success($data);
    }
}
```

#### 方案 B：如果子类必须有构造函数

如果 `User` 控制器还有自己特殊的依赖（比如 `UserLog` 记录类），你才需要写构造函数，并且需要**透传**父类的依赖：

```php
class User extends BaseController
{
    protected string $serviceClass = UserService::class;

    public function __construct(
        Request $request, 
        DatabaseFactory $db,
        UserService $service, // 这里为了获得 IDE 提示，可以直接注入具体类
        private UserLog $log  // 子类独有的依赖
    ) {
        // 调用父类构造，传入父类需要的参数
        parent::__construct(); 
        
        // 处理子类自己的逻辑
    }
}
```

---

### 3. 一个架构层面的思考（重要）

虽然把 `DatabaseFactory` 放在 `BaseController` 很方便，但在标准的 **MVC + Service** 架构中，有一个原则：

> **Controller 层应该尽量不要直接操作数据库。**

*   **Controller 的职责**：接收请求、参数校验、调用 Service、返回响应。
*   **Service 的职责**：业务逻辑、事务处理、调用 Model/DB。

**现状分析**：
你现在把 `$db` 注入给了 Controller。如果你的 Controller 代码里充斥着 `$this->db->query(...)`，那么你的 Service 层就被架空了。

**建议**：
1.  虽然在基类注入了 `$db`，但**尽量少在 Controller 里直接用它**。
2.  应该把数据库操作封装在 `UserService` 里面。
3.  Controller 里的 `$this->service->listUsers()` 才是正道。

**那为什么还要注入 `$db` 到 Controller？**
唯一合理的理由是：你需要处理**跨 Service 的事务**。
例如：
```php
public function register() {
    $this->db->beginTransaction(); // 开启事务
    try {
        $this->userService->create();
        $this->walletService->create(); // 另一个 Service
        $this->db->commit();
    } catch (Exception $e) {
        $this->db->rollback();
    }
}
```



## 特殊业务编排

在某些业务情况下，需要多个service层，设计到 **业务编排（Orchestration）**的时候

当一个操作涉及多个独立的 Service（如用户 + 钱包）时，通常由 **Controller**（或者一个专门的 `RegistrationService`）来负责协调，并控制数据库事务。

针对你的框架架构，有两种主要方式来初始化这两个服务。

---

### 方案一：构造函数注入（推荐，最标准）

这是最符合依赖注入（DI）原则的写法。既然 `User` 控制器主要负责用户，但 `register` 方法需要 `WalletService`，我们就在构造函数里把它请进来。

**代码实现：**

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Basic\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Framework\Database\DatabaseFactory;
use App\Services\UserService;
use App\Services\WalletService;

/**
 * @property UserService $service  // 告诉 IDE，$this->service 就是 UserService
 */
class User extends BaseController
{
    // 指定主服务，父类会自动处理 $this->service
    protected string $serviceClass = UserService::class;

    // 定义额外的服务属性
    private WalletService $walletService;

    public function __construct(
        Request $request, 
        DatabaseFactory $db, 
        UserService $userService,     // 注入主服务
        WalletService $walletService  // 注入副服务
    ) {
        // 1. 调用父类构造，初始化 $request, $db, 和主 $service
        parent::__construct();

        // 2. 初始化副服务
        $this->walletService = $walletService;
    }

    public function register()
    {
        $input = $this->request->request->all();

        // 开启事务
        $this->db->beginTransaction();
        
        try {
            // 1. 创建用户 (使用主服务)
            // 注意：这里用 $this->service 和 $userService 是一样的引用
            $userId = $this->service->createUser($input); 
            
            if (!$userId) {
                throw new \Exception("创建用户失败");
            }

            // 2. 创建钱包 (使用副服务)
            // 假设钱包需要绑定用户ID
            $this->walletService->createWallet($userId); 

            // 提交事务
            $this->db->commit();

            return $this->success(['id' => $userId], '注册成功');

        } catch (\Throwable $e) {
            // 回滚事务
            $this->db->rollback();
            return $this->fail($e->getMessage());
        }
    }
}
```

**优点**：依赖关系清晰，一看构造函数就知道这个控制器依赖哪些服务。
**缺点**：需要写 `__construct`。

---

### 方案二：按需获取（懒人写法）

如果你不想写构造函数，或者 `WalletService` 只有在 `register` 这一处用到，不想污染整个类，可以使用全局容器函数 `app()->make()` 临时获取。

**代码实现：**

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Basic\BaseController;
use App\Services\UserService;
use App\Services\WalletService;

/**
 * @property UserService $service 
 */
class User extends BaseController
{
    protected string $serviceClass = UserService::class;

    // 不需要写 __construct，直接复用父类的

    public function register()
    {
        $input = $this->request->request->all();

        // 临时实例化 WalletService
        /** @var WalletService $walletService */
        $walletService = app()->make(WalletService::class);

        $this->db->beginTransaction();
        try {
            // 1. 主服务
            $userId = $this->service->createUser($input);
            
            // 2. 副服务
            $walletService->createWallet($userId);

            $this->db->commit();
            return $this->success(['id' => $userId]);

        } catch (\Throwable $e) {
            $this->db->rollback();
            return $this->fail('注册失败: ' . $e->getMessage());
        }
    }
}
```

---

### Service 层应该怎么写？

Service 层**不需要知道事务的存在**。它们只需要专注于执行 SQL。只要 Service 内部使用的 `$db` 实例和 Controller 里的 `$this->db` 是**同一个连接实例**（单例模式），事务就会自动生效。

**UserService.php:**
```php
class UserService extends BaseService
{
    public function createUser(array $data): int
    {
        // 这里只是单纯的执行 insert
        // 如果外层没开事务，这就直接写入
        // 如果外层开了事务，这句 SQL 就会等待 commit
        return $this->db->insert('users', $data); 
    }
}
```

**WalletService.php:**
```php
class WalletService extends BaseService
{
    public function createWallet(int $userId): bool
    {
        return (bool)$this->db->insert('wallets', [
            'user_id' => $userId,
            'balance' => 0
        ]);
    }
}
```

### 关键点总结

1.  **单例数据库连接**：确保你的 `DatabaseFactory` 或容器（Container）在一次请求中，返回给 Controller、UserService、WalletService 的是**同一个数据库连接对象**。如果不一致，Controller 开启的事务就管不到 Service 里的操作了。
2.  **异常捕获**：在 Controller 捕获异常并 Rollback 是正确的做法。
3.  **选择方案**：
    *   如果 `WalletService` 在这个控制器里好几个方法都要用 -> **用方案一（构造函数注入）**。
    *   如果只有 `register` 这一次用 -> **用方案二（app()->make）**，代码更少。
	
既然你已经选择了 **无参数构造函数（Service Locator 模式）** 的基类设计，那么在子类中引入多个服务时，为了保持风格一致且逻辑顺畅，推荐使用以下两种方式。

这里假设你的场景是：`User` 控制器，主服务是 `UserService`（父类自动处理），额外需要 `WalletService`。

---


## 前端控制器的两种写法

### 方式一：手动获取（最稳健，风格统一）

这是最符合你当前 `BaseController` 设计风格的写法。子类覆盖构造函数，手动去容器里“拿”需要的额外服务。

**优点**：完全受控，不依赖容器对构造函数参数的自动注入能力。
**注意**：一定要先调用 `parent::__construct()`。

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Basic\BaseController;
use App\Services\UserService;
use App\Services\WalletService;

/**
 * @property UserService $service  // 主服务（IDE提示用）
 */
class User extends BaseController
{
    // 1. 设置主服务，父类会自动初始化它到 $this->service
    protected string $serviceClass = UserService::class;

    // 2. 定义额外服务属性
    private WalletService $walletService;

    public function __construct()
    {
        // 【第一步】必须先调用父类构造！
        // 父类会帮你初始化 Request, DB 以及主服务 UserService
        parent::__construct(); 

        // 【第二步】手动获取额外服务
        $this->walletService = app()->make(WalletService::class);
    }

    public function register()
    {
        // 使用主服务
        $userId = $this->service->createUser(...);

        // 使用额外服务
        $this->walletService->createWallet($userId);
        
        return $this->success();
    }
}
```

---

### 方式二：混合注入（如果容器支持）

假如你的框架在实例化控制器时（比如在路由分发阶段），是使用容器（`$container->make(Controller::class)`）来创建实例的，那么你可以使用这种“混合写法”。

**原理**：PHP 允许子类的构造函数参数与父类不同。父类无参，子类有参，完全合法。

**优点**：代码看起来更显式，更有“依赖注入”的味道。

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Basic\BaseController;
use App\Services\UserService;
use App\Services\WalletService;

class User extends BaseController
{
    protected string $serviceClass = UserService::class;
    
    private WalletService $walletService;

    // 子类请求注入 WalletService
    public function __construct(WalletService $walletService)
    {
        // 1. 先调用父类无参构造（处理 Request, DB, UserService）
        parent::__construct(); 

        // 2. 赋值注入进来的额外服务
        $this->walletService = $walletService;
    }
}
```

> **警示**：方式二只有当你的框架底层是用 `Container` 来实例化控制器时才有效。如果是 `new $controllerName()` 这种原生写法，方式二会报错（提示缺少参数）。鉴于你之前的修改，**方式一（手动获取）是最保险的**。

---

### 总结建议

既然你已经把 `BaseController` 改造成了自动拉取依赖的模式，我强烈建议你**坚持使用方式一**。

这样你的代码逻辑非常清晰：
1.  **通用依赖**（Request, DB, MainService）：父类帮你搞定，你什么都不用管。
2.  **特殊依赖**（ExtraService）：自己在构造函数里 `app()->make()`，随用随拿。

最终代码如下：

```php
    public function __construct()
    {
        parent::__construct(); // 也就是这行代码，让父类帮你干脏活累活
        
        // 自己处理特殊的
        $this->walletService = app()->make(WalletService::class);
        $this->logService    = app()->make(LogService::class);
    }
```