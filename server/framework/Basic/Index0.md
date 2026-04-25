## 数据层（Dao）的操作是如何与模型层交互的？

在FssPhp框架中，**数据访问层（DAO/Repository）作为模型层（Model）的上层封装与交互桥梁**，负责统一管理数据库查询逻辑、协调模型的CRUD操作，并向服务层提供简洁的数据访问接口。二者的交互基于ThinkORM（框架集成的ORM组件）实现，遵循“模型映射数据表、DAO封装查询逻辑”的分层原则，具体交互方式可从以下维度展开：

### 一、核心交互基础：DAO持有模型实例
DAO层通过**依赖注入或直接实例化**获取模型对象，所有数据库操作均通过模型的ORM方法执行，DAO本身不直接编写SQL，而是调用模型的封装方法实现数据交互。
#### 1. 模型实例的注入方式
框架集成了Symfony的依赖注入容器，通常通过构造函数注入模型实例，解耦DAO与模型的耦合关系：
```php
<?php
namespace App\Repositories;

use App\Models\User;
use think\Model;

class UserRepository
{
    // 构造函数注入模型实例
    public function __construct(private User $userModel) {}

    // 也可通过静态方法获取模型（适用于简单场景）
    public function getDefaultModel(): Model
    {
        return new User();
    }
}
```
#### 2. 模型的基础能力
模型层（继承自`think\Model`）已封装了ThinkORM的核心能力：
- 数据表映射（`$table`属性）；
- 基础CRUD（`save`/`find`/`delete`等）；
- 查询构造器（`where`/`order`/`join`等）；
- 关联查询（`hasOne`/`belongsTo`等）；
- 批量赋值、时间戳自动维护等。

DAO层基于这些能力封装更贴合业务的查询逻辑。

### 二、DAO与模型的核心交互场景
DAO层对模型的调用主要分为**基础CRUD操作**、**复杂查询封装**、**关联查询**、**聚合操作**四大类，以下结合代码示例说明：

#### 1. 基础CRUD操作：封装模型的增删改查
DAO层将模型的基础操作封装为更语义化的方法，向服务层屏蔽ORM细节：
```php
<?php
namespace App\Repositories;

use App\Models\User;
use think\exception\DbException;

class UserRepository
{
    public function __construct(private User $userModel) {}

    /**
     * 根据ID查询用户（模型的find方法）
     */
    public function findById(int $id): ?User
    {
        return $this->userModel->find($id); // 模型的单条查询方法
    }

    /**
     * 新增用户（模型的save方法）
     */
    public function create(array $data): User
    {
        $this->userModel->allowField(true)->save($data); // 模型的新增方法（allowField开启批量赋值）
        return $this->userModel; // 返回包含自增ID的模型实例
    }

    /**
     * 更新用户（模型的update方法）
     */
    public function update(int $id, array $data): bool
    {
        return $this->userModel->where('id', $id)->update($data) > 0;
    }

    /**
     * 删除用户（模型的delete方法）
     */
    public function delete(int $id): bool
    {
        return $this->userModel->destroy($id); // 模型的静态删除方法
    }

    /**
     * 批量查询用户（模型的select方法）
     */
    public function findByStatus(int $status): array
    {
        return $this->userModel->where('status', $status)->select()->toArray();
    }
}
```

#### 2. 复杂查询：封装模型的查询构造器
对于多条件、分页、排序等复杂查询，DAO层通过链式调用模型的查询构造器方法，封装为可复用的接口：
```php
<?php
namespace App\Repositories;

use App\Models\User;
use think\Paginator;

class UserRepository
{
    public function __construct(private User $userModel) {}

    /**
     * 分页查询VIP用户（结合多条件与排序）
     */
    public function paginateVipUsers(int $page = 1, int $size = 10): Paginator
    {
        return $this->userModel
            ->where('vip_level', '>', 0) // 条件1：VIP等级>0
            ->where('status', 1)         // 条件2：账号正常
            ->order('vip_expire_time', 'desc') // 按VIP过期时间倒序
            ->field('id, username, email, vip_level, vip_expire_time') // 指定字段
            ->paginate([
                'page' => $page,
                'list_rows' => $size
            ]);
    }

    /**
     * 多表关联查询（用户+用户资料）
     */
    public function findUserWithProfile(int $userId): ?array
    {
        return $this->userModel
            ->alias('u')
            ->join('user_profiles p', 'u.id = p.user_id') // 关联用户资料表
            ->where('u.id', $userId)
            ->field('u.*, p.real_name, p.phone, p.avatar')
            ->find()?->toArray();
    }
}
```

#### 3. 关联查询：调用模型的关联方法
模型层定义了数据表之间的关联关系（如`hasOne`/`hasMany`/`belongsTo`），DAO层直接调用这些关联方法实现关联数据查询：
```php
<?php
// 模型层：App\Models\User.php
namespace App\Models;

use think\Model;

class User extends Model
{
    // 定义一对一关联：用户-用户资料
    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    // 定义一对多关联：用户-订单
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }
}

// DAO层：App\Repositories\UserRepository.php
namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function __construct(private User $userModel) {}

    /**
     * 查询用户及其关联的资料（模型关联方法）
     */
    public function findUserWithProfile(int $userId): ?array
    {
        $user = $this->userModel->with('profile')->find($userId); // with预加载关联
        return $user?->toArray();
    }

    /**
     * 查询用户及其最近的3个订单
     */
    public function findUserWithLatestOrders(int $userId): ?array
    {
        $user = $this->userModel
            ->with([
                'orders' => function ($query) {
                    $query->order('create_time', 'desc')->limit(3); // 关联查询条件
                }
            ])
            ->find($userId);
        return $user?->toArray();
    }
}
```

#### 4. 聚合操作：调用模型的聚合方法
对于统计类需求（如计数、求和、平均值），DAO层调用模型的聚合方法（`count`/`sum`/`avg`等）实现：
```php
<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function __construct(private User $userModel) {}

    /**
     * 统计不同VIP等级的用户数量
     */
    public function countByVipLevel(): array
    {
        return $this->userModel
            ->where('status', 1)
            ->group('vip_level')
            ->field('vip_level, COUNT(*) as user_count')
            ->select()
            ->toArray();
    }

    /**
     * 计算所有用户的余额总和
     */
    public function sumUserBalance(): string
    {
        return $this->userModel->sum('balance'); // ThinkORM返回字符串，避免精度丢失
    }

    /**
     * 统计今日注册的用户数
     */
    public function countTodayRegister(): int
    {
        return $this->userModel
            ->where('create_time', '>=', date('Y-m-d 00:00:00'))
            ->count();
    }
}
```

### 三、DAO与模型交互的设计原则
1. **单一职责**：模型仅负责映射数据表、定义关联与字段规则；DAO负责封装所有数据查询逻辑，服务层通过DAO而非直接调用模型获取数据。
2. **解耦复用**：相同的查询逻辑（如分页查询VIP用户）在DAO中封装为方法，服务层可多处复用，避免重复代码。
3. **依赖注入**：通过Symfony DI容器注入模型实例，而非在DAO中硬编码`new User()`，便于单元测试时替换模型模拟对象。
4. **结果格式化**：DAO层可将模型返回的对象（如`think\Model`实例）转换为数组或DTO，向服务层提供更易用的数据结构。

### 四、特殊场景：DAO直接执行原生SQL
若模型的ORM方法无法满足复杂查询（如多表联查、子查询），DAO层可通过模型的`query`/`execute`方法执行原生SQL，仍保持与模型的交互：
```php
<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function __construct(private User $userModel) {}

    /**
     * 原生SQL查询用户地域分布
     */
    public function getUserRegionStats(): array
    {
        $sql = "
            SELECT p.region, COUNT(u.id) as user_count
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.status = 1
            GROUP BY p.region
            ORDER BY user_count DESC
        ";
        return $this->userModel->query($sql)->fetchAll(); // 执行原生查询
    }
}
```

综上，FssPhp框架中DAO层是模型层的“使用者”与“封装者”，通过调用模型的ORM能力实现数据操作，同时向上层提供统一、可复用的数据访问接口，是服务层与模型层之间的重要桥梁。