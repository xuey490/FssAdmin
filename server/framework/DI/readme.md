## 全局的MVC注解写法

这套方案涵盖了 元数据定义、上下文容器、注入核心逻辑 Trait、基类控制器 以及 最终使用示例。

这套机制本身就是**与层级无关**的。只要任何类（Service, DAO, Model）引用了这个 Trait 并在初始化时调用了 `inject()`，它就能自动扫描属性并从容器中抓取依赖。

为了保持架构的整洁，建议按照以下步骤将这套机制推广到 Service 和 DAO 层。

### 1. 建立 Service 和 DAO 的基类

就像 `BaseController` 一样，我们需要让 Service 和 DAO 的基类也自动触发注入。

**BaseService.php**
```php
<?php
declare(strict_types=1);

namespace App\Services; // 或 Framework\Service

use Framework\DI\Injectable;

abstract class BaseService
{
    use Injectable; // 引入注入能力

    public function __construct()
    {
        // 实例化时自动注入
        $this->inject();
    }
}
```

**BaseDao.php** (或者 BaseRepository)
```php
<?php
declare(strict_types=1);

namespace App\Dao;

use Framework\DI\Injectable;

abstract class BaseDao
{
    use Injectable;

    public function __construct()
    {
        $this->inject();
    }
}
```

---

### 2. 实际应用示例 (Controller -> Service -> DAO)

现在我们可以实现一个完整的 **无构造函数** 调用链。

#### 第一层：DAO (数据访问层)
假设这里需要注入数据库连接对象（假设容器里有 `db` 服务）。

```php
namespace App\Dao;

use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Scope;

class UserDao extends BaseDao
{
    // 注入数据库连接
    #[Inject(id: 'db')]
    protected $db;

    public function findById(int $id)
    {
        // $this->db 已经自动注入可用
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }
}
```

#### 第二层：Service (业务逻辑层)
Service 需要使用 DAO，同时也想记录日志。

```php
namespace App\Services;

use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use App\Dao\UserDao;
use Psr\Log\LoggerInterface;

class UserService extends BaseService
{
    // 自动装配 DAO
    #[Autowire]
    protected UserDao $userDao;

    // 注入日志接口
    #[Inject(id: LoggerInterface::class)]
    protected LoggerInterface $logger;

    public function getUser(int $id)
    {
        $this->logger->info("Fetching user $id");
        return $this->userDao->findById($id);
    }
}
```

#### 第三层：Controller (控制器层)
Controller 使用 Service。

```php
namespace App\Controller;

use Framework\Http\BaseController;
use Framework\DI\Attribute\Autowire;
use App\Services\UserService;

class UserController extends BaseController
{
    #[Autowire]
    protected UserService $userService;

    public function detail()
    {
        // 这里的调用链：
        // 1. Controller 实例化 -> 注入 UserService
        // 2. UserService 实例化 -> 注入 UserDao 和 Logger
        // 3. UserDao 实例化 -> 注入 DB
        // 全程自动化，无 new，无构造函数传参
        return $this->userService->getUser(1);
    }
}
```

### 无构造函数的控制器写法
```
namespace App\Controller;

use Framework\Http\BaseController;
use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Context;
use App\Service\UserService;
use App\Service\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    // 用法 1: 自动装配 (根据类型 UserService 注入)
    #[Autowire]
    protected UserService $userService;

    // 用法 2: 指定接口注入 (当属性类型是接口时，指定注入具体的实现类ID)
    // 假设容器里 'logger.file' 绑定了 FileLogger
    #[Inject(id: 'logger.file')] 
    protected LoggerInterface $logger;

    // 用法 3: 上下文注入 (注入当前请求对象)
    #[Context('request')]
    protected Request $request;

    // 可以在这里写初始化逻辑，替代 __construct
    protected function initialize()
    {
        // $this->logger 已经可用了
        $this->logger->log("Controller initialized.");
    }

    public function index()
    {
        $username = $this->userService->getName();
        $ip = $this->request->getClientIp();

        $this->logger->log("User accessed index. IP: {$ip}");

        return "Hello, {$username}";
    }
}
```

---

### 3. 关于 Model (实体模型) 的特殊说明

**Model (Entity) 能不能用？**
能用，但要**谨慎**。

通常 Model（如 `User` 模型）是用来承载数据的（Data Object），比如 `$user->username`。它们通常是**多例**的（每次查询数据库都 `new User`），而且往往不在容器里管理，而是由 ORM 动态创建。

如果你在 Model 里使用 `#[Inject]`：
1.  **性能开销**：如果一次查询返回 1000 个 User 对象，每个对象都要反射、扫描注解、去容器拿东西，性能会受到影响。
2.  **设计原则**：Model 最好保持纯净，尽量不要依赖 Service。

**场景建议**：
如果你的 Model 是 **ActiveRecord 模式** (类似 Laravel 的 Eloquent，可以直接调用 `$user->save()`)，那么你需要注入 `db` 连接。

**BaseModel.php**
```php
namespace App\Models;

use Framework\DI\Injectable;
use Framework\DI\Attribute\Inject;

abstract class BaseModel
{
    use Injectable;

    #[Inject('db')]
    protected $db; // 注入数据库连接供 save/update 使用

    public function __construct()
    {
        // Model 通常会有 new User(['name'=>'...']) 这种传参
        // 所以 inject 最好放在构造函数最前面
        $this->inject(); 
    }
    
    public function save() {
        $this->db->insert(...);
    }
}
```

---

### 4. 关键点：容器必须具备“递归创建能力”

 `Container::get($class)` 方法必须足够智能使得让这一切完美运行，

*   **现状**：你之前的简易容器可能只是 `new $class()`。
*   **问题**：如果 Service 继承了 `BaseService`，`new $class()` 会触发构造函数中的 `$this->inject()`，这是没问题的。
*   **优化**：但在更标准的 DI 实现中，容器应该负责创建。

只要你所有的 Service/DAO 都继承了带有 `__construct() { $this->inject(); }` 的基类，目前的简易容器就能完美工作。

### 总结

1.  **复制模式**：创建 `BaseService` 和 `BaseDao`，在构造函数中加入 `use Injectable;` 和 `$this->inject();`。
2.  **直接使用**：在 Service 和 DAO 的属性上直接挂 `#[Autowire]` 或 `#[Inject]`。
3.  **Model 慎用**：虽然可以实现，但在高频创建的对象上使用反射注入会拖慢性能，建议 Model 只作为数据载体，或者手动赋予 DB 连接。

一个全栈的（Controller-Service-Dao）无构造函数注入体系，让代码耦合度会大大降低，开发体验会非常丝滑。