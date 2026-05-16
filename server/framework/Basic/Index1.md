## 如何在控制器中调用服务层的方法？

在 FssPhp 框架中，控制器调用服务层方法主要依托**Symfony 依赖注入容器**实现，同时框架也支持通过注解或容器手动获取服务实例，核心遵循“依赖注入解耦”的设计原则。以下从**依赖注入（推荐）**、**容器手动获取**、**基础控制器封装**三个维度，结合代码示例详细说明调用方式。

### 一、核心前提：服务层的注册
在调用服务层前，需确保服务类已被**注册到依赖注入容器**（框架默认通过 PSR-4 自动扫描 `app/Services` 目录，或在 `config/services.php` 中显式配置）。
```php
// config/services.php（可选，显式注册服务）
return [
    // 服务ID => 类名（或配置）
    App\Services\UserService::class => App\Services\UserService::class,
    // 也可定义为单例
    'user.service' => [
        'class' => App\Services\UserService::class,
        'shared' => true,
    ],
];
```

### 二、推荐方式：构造函数依赖注入
这是框架最推荐的方式，通过控制器的构造函数注入服务实例，由 Symfony DI 容器自动解析依赖，解耦性最强且便于单元测试。

#### 1. 定义服务层类
```php
// app/Services/UserService.php
namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    public function __construct(private UserRepository $userRepo) {}

    // 示例：获取用户信息
    public function getUserInfo(int $userId): array
    {
        $user = $this->userRepo->findById($userId);
        return $user ? $user->toArray() : [];
    }

    // 示例：用户注册业务
    public function register(array $userData): bool
    {
        // 业务逻辑：数据验证、密码加密、新增用户
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        return $this->userRepo->create($userData) !== null;
    }
}
```

#### 2. 控制器中注入并调用服务
控制器继承框架的 `BaseController`（已集成容器能力），通过构造函数声明服务依赖，容器会自动注入实例：
```php
// app/Controllers/UserController.php
namespace App\Controllers;

use App\Services\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends BaseController
{
    // 构造函数注入服务层
    public function __construct(private UserService $userService) {}

    /**
     * 示例：根据ID查询用户
     */
    public function info(Request $request): Response
    {
        $userId = $request->query->getInt('id');
        // 调用服务层方法
        $userInfo = $this->userService->getUserInfo($userId);
        
        if (empty($userInfo)) {
            return new Response(json_encode(['code' => 404, 'msg' => '用户不存在']), 404);
        }
        return new Response(json_encode(['code' => 200, 'data' => $userInfo]));
    }

    /**
     * 示例：用户注册
     */
    public function register(Request $request): Response
    {
        $userData = $request->request->all(); // 获取注册表单数据
        // 调用服务层注册方法
        $result = $this->userService->register($userData);
        
        if ($result) {
            return new Response(json_encode(['code' => 200, 'msg' => '注册成功']));
        }
        return new Response(json_encode(['code' => 500, 'msg' => '注册失败']), 500);
    }
}
```

### 三、灵活方式：通过容器手动获取服务
若需在控制器的某个方法中临时调用服务，可通过框架的**容器实例**手动获取服务对象（适用于非全局依赖的场景）。

#### 1. 借助基础控制器的容器属性
框架的 `BaseController` 已注入容器实例，可直接通过 `$this->container` 获取服务：
```php
// app/Controllers/OrderController.php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\OrderService;

class OrderController extends BaseController
{
    /**
     * 示例：手动获取服务并调用
     */
    public function create(Request $request): Response
    {
        // 从容器中获取OrderService实例
        $orderService = $this->container->get(OrderService::class);
        
        $orderData = $request->request->all();
        // 调用服务层方法创建订单
        $orderId = $orderService->createOrder($orderData);
        
        return new Response(json_encode(['order_id' => $orderId]));
    }
}
```

#### 2. 通过框架辅助函数获取容器
框架提供全局辅助函数 `container()`，可在控制器中直接调用获取服务：
```php
public function cancel(Request $request): Response
{
    $orderId = $request->query->getInt('id');
    // 辅助函数获取服务
    $orderService = container(OrderService::class);
    $result = $orderService->cancelOrder($orderId);
    
    return new Response(json_encode(['success' => $result]));
}
```

### 四、进阶方式：属性注入（通过注解）
框架支持通过**注解属性**实现服务的属性注入（需框架开启注解扫描），简化构造函数的依赖声明：
```php
// app/Controllers/PayController.php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Inject; // 框架注入注解
use App\Services\PayService;

class PayController extends BaseController
{
    // 注解注入服务
    #[Inject]
    private PayService $payService;

    /**
     * 示例：属性注入后调用服务
     */
    public function callback(): Response
    {
        // 调用支付服务的回调处理方法
        $result = $this->payService->handlePayCallback($_POST);
        return new Response($result ? 'success' : 'fail');
    }
}
```

### 五、注意事项与最佳实践
1. **服务层的单一职责**：服务层应专注封装业务逻辑，避免在控制器中直接编写复杂业务（如数据验证、事务处理），确保控制器仅负责“接收请求-调用服务-返回响应”。
2. **依赖注入的优势**：优先使用构造函数注入，便于通过容器管理服务的生命周期（如单例、原型），且在单元测试时可轻松替换服务的模拟实现。
3. **避免服务层嵌套调用**：若多个服务需交互，可通过容器在服务层中注入其他服务（而非在控制器中调用多个服务后传递数据），例如：
   ```php
   // 服务层中注入其他服务
   class OrderService
   {
       public function __construct(
           private OrderRepository $orderRepo,
           private UserService $userService // 服务层嵌套注入
       ) {}
   }
   ```
4. **响应格式化**：控制器调用服务后，统一格式化响应数据（如 JSON、模板渲染），避免服务层直接返回 `Response` 对象，保持服务层的通用性。

综上，FssPhp 框架中控制器调用服务层的核心是**依赖注入**，通过容器自动解析服务依赖，既保证了代码的解耦性，又提升了开发效率和可维护性。