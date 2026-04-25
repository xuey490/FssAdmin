## 服务层调用数据层（DAO/Repository）

在 FssPhp 框架中，服务层调用数据层（DAO/Repository）的核心方式与控制器调用服务层一致，**依托 Symfony 依赖注入容器**实现解耦，同时也支持手动获取数据层实例。服务层作为业务逻辑的核心，通过数据层封装的数据库操作接口实现数据交互，不直接与模型耦合。以下从**依赖注入（推荐）**、**容器手动获取**、**复杂业务中的数据层协作**三个维度结合代码示例详细说明。

### 一、核心前提：数据层的注册
数据层（Repository/DAO）需被注册到依赖注入容器，框架默认通过 PSR-4 自动扫描 `app/Repositories` 目录，也可在 `config/services.php` 中显式配置：
```php
// config/services.php
return [
    // 数据层类名 => 类名（默认单例）
    App\Repositories\UserRepository::class => App\Repositories\UserRepository::class,
    // 自定义服务ID（可选）
    'user.repository' => [
        'class' => App\Repositories\UserRepository::class,
        'shared' => true, // 单例模式
    ],
];
```

### 二、推荐方式：构造函数依赖注入
这是框架最推荐的方式，通过服务层的构造函数注入数据层实例，由 DI 容器自动解析依赖，解耦性强且便于单元测试。

#### 1. 定义数据层（DAO/Repository）
数据层封装数据库操作，依赖模型层实现具体的CRUD：
```php
// app/Repositories/UserRepository.php
namespace App\Repositories;

use App\Models\User;
use think\Paginator;

class UserRepository
{
    public function __construct(private User $userModel) {}

    // 根据ID查询用户
    public function findById(int $userId): ?User
    {
        return $this->userModel->find($userId);
    }

    // 新增用户
    public function create(array $data): ?User
    {
        $result = $this->userModel->allowField(true)->save($data);
        return $result ? $this->userModel : null;
    }

    // 分页查询VIP用户
    public function paginateVipUsers(int $page = 1, int $size = 10): Paginator
    {
        return $this->userModel
            ->where('vip_level', '>', 0)
            ->where('status', 1)
            ->order('vip_expire_time', 'desc')
            ->paginate(['page' => $page, 'list_rows' => $size]);
    }

    // 更新用户状态
    public function updateStatus(int $userId, int $status): bool
    {
        return $this->userModel->where('id', $userId)->update(['status' => $status]) > 0;
    }
}
```

#### 2. 服务层中注入并调用数据层
服务层通过构造函数声明数据层依赖，容器自动注入实例，随后在业务方法中调用数据层的接口：
```php
// app/Services/UserService.php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;
use think\Paginator;

class UserService
{
    // 构造函数注入数据层
    public function __construct(private UserRepository $userRepository) {}

    /**
     * 业务逻辑：获取用户详情（含VIP信息）
     */
    public function getUserDetail(int $userId): array
    {
        // 调用数据层方法查询用户
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            return ['code' => 404, 'msg' => '用户不存在', 'data' => []];
        }

        // 业务逻辑处理：封装用户VIP信息
        $userData = $user->toArray();
        $userData['is_vip'] = $userData['vip_level'] > 0 && $userData['vip_expire_time'] > date('Y-m-d H:i:s');
        $userData['vip_remain_days'] = $userData['is_vip'] ? (strtotime($userData['vip_expire_time']) - time()) / 86400 : 0;

        return ['code' => 200, 'msg' => 'success', 'data' => $userData];
    }

    /**
     * 业务逻辑：用户注册（含密码加密）
     */
    public function register(array $userData): array
    {
        // 业务验证：检查用户名是否重复
        $existUser = $this->userRepository->findByUsername($userData['username']);
        if ($existUser) {
            return ['code' => 400, 'msg' => '用户名已存在'];
        }

        // 业务处理：密码加密
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $userData['create_time'] = date('Y-m-d H:i:s');

        // 调用数据层新增用户
        $user = $this->userRepository->create($userData);
        return $user ? ['code' => 200, 'msg' => '注册成功', 'user_id' => $user->id] : ['code' => 500, 'msg' => '注册失败'];
    }

    /**
     * 业务逻辑：分页获取VIP用户列表
     */
    public function getVipUserList(int $page, int $size): array
    {
        // 调用数据层分页查询
        $paginator = $this->userRepository->paginateVipUsers($page, $size);
        return [
            'code' => 200,
            'data' => [
                'list' => $paginator->items(),
                'total' => $paginator->total(),
                'page' => $page,
                'size' => $size
            ]
        ];
    }

    /**
     * 业务逻辑：禁用用户（含业务校验）
     */
    public function disableUser(int $userId): array
    {
        // 业务校验：禁止禁用超级管理员
        $user = $this->userRepository->findById($userId);
        if ($user && $user->is_super) {
            return ['code' => 403, 'msg' => '无法禁用超级管理员'];
        }

        // 调用数据层更新状态
        $result = $this->userRepository->updateStatus($userId, 0);
        return $result ? ['code' => 200, 'msg' => '禁用成功'] : ['code' => 500, 'msg' => '禁用失败'];
    }
}
```

### 三、灵活方式：通过容器手动获取数据层
若需在服务层的某个方法中临时调用数据层，可通过框架的**容器实例**手动获取数据层对象（适用于非全局依赖的场景）。

#### 1. 服务层注入容器
首先在服务层中注入容器实例（框架的 `BaseService` 通常已集成容器属性，若未集成可手动注入）：
```php
// app/Services/OrderService.php
namespace App\Services;

use Psr\Container\ContainerInterface;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;

class OrderService
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 示例：手动获取多个数据层实例
     */
    public function createOrder(array $orderData): array
    {
        // 手动获取订单数据层
        $orderRepo = $this->container->get(OrderRepository::class);
        // 手动获取商品数据层
        $productRepo = $this->container->get(ProductRepository::class);

        // 业务逻辑：检查商品库存
        $product = $productRepo->findById($orderData['product_id']);
        if (!$product || $product->stock < $orderData['num']) {
            return ['code' => 400, 'msg' => '商品库存不足'];
        }

        // 调用数据层创建订单
        $order = $orderRepo->create($orderData);
        if (!$order) {
            return ['code' => 500, 'msg' => '创建订单失败'];
        }

        // 调用数据层扣减商品库存
        $productRepo->decreaseStock($product->id, $orderData['num']);

        return ['code' => 200, 'msg' => '创建成功', 'order_id' => $order->id];
    }
}
```

#### 2. 通过全局辅助函数获取数据层
框架提供全局 `container()` 辅助函数，可在服务层中直接调用获取数据层实例（简化代码）：
```php
// app/Services/StatisticsService.php
namespace App\Services;

use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;

class StatisticsService
{
    /**
     * 示例：统计今日数据（临时调用多个数据层）
     */
    public function getTodayStats(): array
    {
        // 辅助函数获取数据层
        $orderRepo = container(OrderRepository::class);
        $userRepo = container(UserRepository::class);

        // 调用数据层统计方法
        $todayOrderCount = $orderRepo->countTodayOrders();
        $todayUserCount = $userRepo->countTodayRegister();
        $todaySales = $orderRepo->sumTodaySales();

        return [
            'today_order_count' => $todayOrderCount,
            'today_user_count' => $todayUserCount,
            'today_sales' => $todaySales
        ];
    }
}
```

### 四、进阶方式：属性注入（通过注解）
框架支持通过**注解属性**实现数据层的属性注入（需开启注解扫描），简化构造函数的依赖声明：
```php
// app/Services/PayService.php
namespace App\Services;

use Framework\Attributes\Inject; // 框架注入注解
use App\Repositories\PayRepository;
use App\Repositories\OrderRepository;

class PayService
{
    // 注解注入支付数据层
    #[Inject]
    private PayRepository $payRepository;

    // 注解注入订单数据层
    #[Inject]
    private OrderRepository $orderRepository;

    /**
     * 示例：处理支付回调（属性注入后调用数据层）
     */
    public function handlePayCallback(array $callbackData): array
    {
        // 验证支付回调签名
        $isValid = $this->payRepository->verifyCallbackSign($callbackData);
        if (!$isValid) {
            return ['code' => 400, 'msg' => '签名验证失败'];
        }

        // 更新订单支付状态
        $orderNo = $callbackData['out_trade_no'];
        $result = $this->orderRepository->updatePayStatus($orderNo, 1);

        return $result ? ['code' => 200, 'msg' => '处理成功'] : ['code' => 500, 'msg' => '更新订单失败'];
    }
}
```

### 五、注意事项与最佳实践
1. **单一职责**：数据层仅负责数据库操作，服务层仅负责业务逻辑，避免在服务层中直接操作模型或编写SQL。
2. **依赖注入优先**：优先使用构造函数注入，避免大量使用 `container()` 辅助函数导致代码耦合度升高。
3. **事务处理**：若业务涉及多步数据库操作（如创建订单+扣减库存），需在服务层中通过数据层开启事务：
   ```php
   // 服务层中处理事务
   public function createOrderWithTransaction(array $data): array
   {
       try {
           // 调用数据层开启事务
           $this->orderRepository->startTrans();
           
           // 执行多步操作
           $order = $this->orderRepository->create($data);
           $this->productRepository->decreaseStock($data['product_id'], $data['num']);
           
           // 提交事务
           $this->orderRepository->commitTrans();
           return ['code' => 200, 'order_id' => $order->id];
       } catch (\Exception $e) {
           // 回滚事务
           $this->orderRepository->rollbackTrans();
           return ['code' => 500, 'msg' => $e->getMessage()];
       }
   }
   ```
4. **避免数据层嵌套调用**：服务层可调用多个数据层，但数据层之间应尽量避免互相调用，保持数据层的独立性。
5. **返回值标准化**：服务层调用数据层后，统一格式化返回结果（如数组格式的状态码+数据），便于控制器统一处理响应。

综上，FssPhp 框架中服务层调用数据层的核心是**依赖注入**，通过容器管理数据层实例，既保证了代码的解耦性，又提升了业务逻辑的可维护性。