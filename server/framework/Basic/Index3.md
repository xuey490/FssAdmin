## 如何在模型层中调用数据层的方法？

在FssPhp框架中，模型层调用数据层方法属于**打破分层设计原则的非常规操作**，若处理不当会引发代码耦合、循环依赖、职责混乱等问题。结合框架的设计特性和实际开发场景，模型层调用数据层时需重点关注以下核心问题，同时给出对应的解决方案和避坑建议：

### 一、避免循环依赖问题
这是模型层调用数据层最核心的风险点。因为**数据层本身依赖模型层**（通过模型实现数据库操作），若模型层再反向注入数据层，会形成**循环依赖**，导致Symfony DI容器无法解析实例，抛出依赖注入异常。

#### 具体问题表现：
```php
// 数据层依赖模型
class UserRepository {
    public function __construct(private User $userModel) {} 
}

// 模型又依赖数据层（循环依赖）
class User extends Model {
    public function __construct(private UserRepository $userRepo) {} 
}
```
容器解析`User`时需要`UserRepository`，解析`UserRepository`又需要`User`，形成死循环。

#### 解决方案：
1. **延迟注入（推荐）**：模型中不直接在构造函数注入数据层，而是通过容器**延迟获取**数据层实例（如在调用方法时再获取）。
   ```php
   class User extends Model {
       public function getOrderStats() {
           // 调用时才通过容器获取，避免构造阶段的循环依赖
           $orderRepo = container(\App\Repositories\OrderRepository::class);
           return $orderRepo->sumUserOrderAmount($this->id);
       }
   }
   ```
2. **使用容器接口注入**：模型中注入`ContainerInterface`而非直接注入数据层，通过容器动态获取数据层，打破循环。
   ```php
   class User extends Model {
       private ContainerInterface $container;
       
       public function __construct(array $data = [], ContainerInterface $container = null) {
           parent::__construct($data);
           $this->container = $container ?? container();
       }
       
       public function getOrderCount() {
           $orderRepo = $this->container->get(OrderRepository::class);
           return $orderRepo->countUserOrders($this->id);
       }
   }
   ```

### 二、严格遵守单一职责原则
模型层的核心职责是**数据表映射、字段规则定义、关联关系配置**，以及基于单表的基础ORM操作（如`find`、`save`）；而数据层的职责是**封装复杂查询、多表交互、数据聚合**。

#### 具体问题表现：
若模型层大量调用数据层方法，会导致模型既负责数据映射，又负责复杂查询逻辑，违背“单一职责”，使代码难以维护。例如：
```php
class User extends Model {
    // 模型中封装了大量数据层的统计逻辑，职责混乱
    public function getVipStats() {
        $userRepo = container(UserRepository::class);
        return $userRepo->getVipGrowth($this->id);
    }
    
    public function getOrderTrend() {
        $orderRepo = container(OrderRepository::class);
        return $orderRepo->getUserOrderTrend($this->id);
    }
}
```

#### 解决方案：
1. **限制调用场景**：仅在**模型的自定义方法需复用数据层已封装的核心逻辑**时才调用，避免将数据层的所有逻辑都渗透到模型中。
2. **重构逻辑（优先）**：将模型中调用数据层的逻辑迁移至**服务层**，由服务层作为协调者，同时调用数据层和模型层，保持模型的纯粹性。
   ```php
   // 服务层协调调用（推荐）
   class UserService {
       public function __construct(
           private UserRepository $userRepo,
           private OrderRepository $orderRepo
       ) {}
       
       public function getUserFullInfo(int $userId) {
           $user = $this->userRepo->findById($userId);
           $vipStats = $this->userRepo->getVipGrowth($userId);
           $orderTrend = $this->orderRepo->getUserOrderTrend($userId);
           return compact('user', 'vipStats', 'orderTrend');
       }
   }
   ```

### 三、防止性能损耗
模型层调用数据层时，若方式不当会引入额外的性能开销，尤其在批量操作场景下。

#### 具体问题表现：
1. **频繁获取数据层实例**：在模型的循环调用方法中，每次都通过`container()`获取数据层实例，增加容器解析的开销。
   ```php
   class User extends Model {
       // 批量处理时，每次循环都重新获取数据层实例
       public function batchHandleOrders(array $orderIds) {
           foreach ($orderIds as $id) {
               $orderRepo = container(OrderRepository::class); // 重复获取
               $orderRepo->updateStatus($id, 1);
           }
       }
   }
   ```
2. **N+1查询问题**：模型的关联方法中调用数据层，若未做批量处理，会引发N+1查询（如查询10个用户，每个用户都调用数据层查订单）。

#### 解决方案：
1. **延迟初始化数据层实例**：在模型中通过私有方法缓存数据层实例，避免重复获取。
   ```php
   class User extends Model {
       private ?OrderRepository $orderRepo = null;
       
       private function getOrderRepo(): OrderRepository {
           if ($this->orderRepo === null) {
               $this->orderRepo = container(OrderRepository::class);
           }
           return $this->orderRepo;
       }
       
       public function batchHandleOrders(array $orderIds) {
           $orderRepo = $this->getOrderRepo(); // 仅获取一次
           foreach ($orderIds as $id) {
               $orderRepo->updateStatus($id, 1);
           }
       }
   }
   ```
2. **批量查询优化**：将模型中的单条查询改为数据层的批量查询，减少数据库交互次数。
   ```php
   // 数据层提供批量查询方法
   class OrderRepository {
       public function getOrderIdsByUserIds(array $userIds): array {
           return $this->orderModel->whereIn('user_id', $userIds)->column('id');
       }
   }
   
   // 模型中调用批量方法，避免N+1
   class User extends Model {
       public function batchGetOrderIds(array $userIds) {
           $orderRepo = $this->getOrderRepo();
           return $orderRepo->getOrderIdsByUserIds($userIds);
       }
   }
   ```

### 四、保证单元测试的可维护性
模型层依赖数据层后，会增加单元测试的复杂度——测试模型方法时，需要模拟数据层的行为，否则会真实操作数据库。

#### 具体问题表现：
直接调用数据层的模型方法，在单元测试中无法隔离数据层，导致测试依赖数据库环境，且难以模拟异常场景。

#### 解决方案：
1. **使用模拟对象（Mock）**：通过PHPUnit等测试框架，模拟数据层实例，替换容器中的真实数据层，实现无数据库的单元测试。
   ```php
   // 单元测试中模拟数据层
   public function testGetRecent30DaysOrderAmount() {
       // 模拟OrderRepository的返回值
       $mockOrderRepo = $this->createMock(OrderRepository::class);
       $mockOrderRepo->method('sumUserOrderAmount')->willReturn('100.00');
       
       // 将模拟对象注入容器
       container()->set(OrderRepository::class, $mockOrderRepo);
       
       // 测试模型方法
       $user = new User(['id' => 1]);
       $this->assertEquals('100.00', $user->getRecent30DaysOrderAmount());
   }
   ```
2. **减少模型对数据层的依赖**：尽可能将需测试的逻辑迁移至服务层，模型仅保留基础ORM操作，降低测试复杂度。

### 五、避免事务管理的混乱
数据层通常负责数据库事务的开启、提交和回滚，若模型层调用数据层的事务方法，可能导致事务边界模糊，引发数据一致性问题。

#### 具体问题表现：
模型层调用数据层的事务方法后，又在模型中执行其他数据库操作，若未正确处理事务回滚，会导致部分数据提交、部分数据未提交。

#### 解决方案：
1. **事务仅由数据层/服务层管理**：模型层不参与事务控制，所有事务操作都在数据层或服务层中完成。
   ```php
   // 数据层封装事务逻辑
   class OrderRepository {
       public function createOrderWithTransaction(array $data): bool {
           try {
               $this->orderModel->startTrans();
               $this->orderModel->save($data);
               $this->orderModel->commitTrans();
               return true;
           } catch (\Exception $e) {
               $this->orderModel->rollbackTrans();
               return false;
           }
       }
   }
   
   // 模型仅调用封装好的事务方法，不手动操作事务
   class User extends Model {
       public function createUserOrder(array $data) {
           $orderRepo = $this->getOrderRepo();
           return $orderRepo->createOrderWithTransaction($data);
       }
   }
   ```
2. **禁止模型嵌套事务**：若数据层已开启事务，模型层不再手动开启新事务，避免事务嵌套导致的回滚异常。

### 六、兼容框架的ORM特性
FssPhp框架集成了ThinkORM，模型层本身具备丰富的ORM能力（如关联查询、聚合操作），若盲目调用数据层，会忽略ORM的原生优势，增加代码冗余。

#### 具体问题表现：
模型层可以通过ORM直接实现的逻辑（如单表聚合、关联查询），却转而调用数据层，导致代码冗余。例如：
```php
// 冗余：模型可直接通过ORM统计，无需调用数据层
class User extends Model {
    public function getOrderCount() {
        $orderRepo = container(OrderRepository::class);
        return $orderRepo->countUserOrders($this->id);
    }
}

// 数据层的方法仅封装了简单的ORM调用
class OrderRepository {
    public function countUserOrders(int $userId): int {
        return $this->orderModel->where('user_id', $userId)->count();
    }
}
```

#### 解决方案：
1. **优先使用ORM原生能力**：模型层的单表操作、基础关联查询、简单聚合，直接通过ThinkORM实现，无需调用数据层。
   ```php
   // 优化：模型直接使用ORM，避免冗余调用
   class User extends Model {
       public function getOrderCount(): int {
           return $this->hasMany(Order::class, 'user_id')->count();
       }
   }
   ```
2. **数据层仅封装复杂逻辑**：仅当查询逻辑涉及**多表联查、子查询、复杂统计**时，才由数据层封装，模型层在必要时调用。

### 总结
模型层调用数据层是**非常规操作**，需严格控制使用场景，核心注意事项可归纳为：
1. **解决循环依赖**：通过延迟注入、容器动态获取避免构造阶段的循环依赖；
2. **坚守单一职责**：模型仅负责数据映射，复杂逻辑仍由数据层/服务层承担；
3. **优化性能**：缓存数据层实例、批量查询减少数据库交互；
4. **保证可测试性**：使用Mock对象隔离数据层，避免测试依赖数据库；
5. **规范事务管理**：事务仅由数据层/服务层控制，模型不参与；
6. **兼容ORM特性**：优先使用框架ORM原生能力，减少冗余调用。

若模型调用数据层的场景频繁，建议重新审视分层设计，将逻辑迁移至服务层，从根本上避免分层混乱。