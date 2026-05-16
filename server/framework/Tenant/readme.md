# BaseRepository & Tenant 完整使用范例（多场景覆盖）
以下范例将从「基础配置→子类实现→CURD操作→多租户场景→高级用法」全流程讲解，覆盖90%实际业务场景，可直接复制到项目中参考使用。

## 一、 前置准备（必须先完成，确保依赖可用）
### 1.  容器绑定（初始化时执行，如项目入口文件/服务提供者）
确保 `Tenant` 和 `DatabaseFactory` 被绑定到容器，让 `BaseRepository` 能自动注入依赖。
```php
<?php
// 兼容 Framework\Core\App 容器（项目自定义容器）
use Framework\Core\App;
use Framework\Tenant\Tenant;
use Framework\Database\DatabaseFactory;

// 1. 绑定 Tenant 类（单例模式，全局共享租户信息）
App()->bind('tenant', Tenant::class);
App()->singleton('tenant', Tenant::class); // 单例绑定，确保全局租户信息一致

// 2. 绑定 DatabaseFactory 类（数据库工厂，提供ORM类型判断）
App()->bind(DatabaseFactory::class, DatabaseFactory::class);
App()->singleton(DatabaseFactory::class, DatabaseFactory::class);

// 兼容 ThinkPHP 容器（若项目使用TP容器）
if (class_exists('\think\Container')) {
    \think\Container::getInstance()->bind('tenant', Tenant::class);
    \think\Container::getInstance()->singleton('tenant', Tenant::class);
    \think\Container::getInstance()->bind(DatabaseFactory::class, DatabaseFactory::class);
}
```

### 2.  定义模型类（兼容双ORM，示例分别提供TP和Laravel模型）
#### 示例1：ThinkPHP 8 模型（对应 User 表）
```php
<?php
namespace App\Models;

use think\Model;

/**
 * User 模型（ThinkPHP ORM）
 * 对应数据表：user（默认小写模型名，可自定义 table 属性）
 */
class User extends Model
{
    // 批量赋值白名单（必须配置，BaseRepository 的 create/save 方法依赖）
    protected $fillable = ['username', 'email', 'password', 'status', 'tenant_id', 'age'];
    
    // 主键名（默认id，若自定义主键可配置）
    protected $pk = 'id';
    
    // 数据表名（默认自动推断，手动配置更严谨）
    protected $table = 'user';
    
    // 时间字段自动填充（可选）
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
}
```

#### 示例2：Illuminate Eloquent 模型（对应 User 表）
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User 模型（Laravel ORM）
 */
class User extends Model
{
    // 批量赋值白名单
    protected $fillable = ['username', 'email', 'password', 'status', 'tenant_id', 'age'];
    
    // 数据表名（Laravel 默认复数，手动指定单数表名）
    protected $table = 'user';
    
    // 关闭时间戳自动填充（若不需要）
    // public $timestamps = false;
    
    // 自定义主键类型（如雪花ID设为 string）
    // protected $keyType = 'string';
    // public $incrementing = false;
}
```

## 二、 实现 BaseRepository 子类（业务核心，以 User 为例）
所有业务表的操作都需要继承 `BaseRepository`，并指定 `modelClass`。
```php
<?php
namespace App\Repository;

use Framework\Repository\BaseRepository;
use App\Models\User;

/**
 * UserRepository 用户数据操作类
 * 继承 BaseRepository，自动获得 CRUD、统计、事务等通用方法
 */
class UserRepository extends BaseRepository
{
    /**
     * 指定对应模型类（必须覆盖，否则抛出异常）
     * @var string
     */
    protected string $modelClass = User::class;

    // 可选：覆盖 initialize 方法，添加自定义初始化逻辑（如默认查询条件）
    protected function initialize(): void
    {
        parent::initialize();
        // 示例：全局过滤已删除的用户（若有 deleted_at 软删除字段）
        // $this->newQuery()->where('deleted_at', 0);
    }

    // 可选：添加自定义业务方法（通用方法无法满足时扩展）
    /**
     * 根据邮箱查询用户（自定义业务方法）
     * @param string $email 邮箱
     * @param array $with 关联预加载
     * @return mixed
     */
    public function findByEmail(string $email, array $with = [])
    {
        return $this->findOneBy(['email' => $email], $with);
    }

    /**
     * 批量更新用户状态（自定义批量操作）
     * @param array $userIds 用户ID数组
     * @param int $status 目标状态
     * @return int 受影响行数
     */
    public function batchUpdateStatus(array $userIds, int $status): int
    {
        return $this->updateBy(
            ['id' => ['in', $userIds]], // 查询条件
            ['status' => $status, 'updated_at' => time()] // 更新数据
        );
    }
}
```

## 三、 Tenant 类独立使用范例（多场景获取/设置租户）
`Tenant` 类可全局使用，用于获取租户信息、切换租户等操作。
### 1.  基本使用：获取租户信息
```php
<?php
namespace App\Service;

use Framework\Core\App;
use Framework\Tenant\Tenant;

class UserService
{
    public function getTenantInfo()
    {
        // 方式1：从容器获取 Tenant 实例（推荐，单例共享）
        $tenant = App()->make('tenant'); // 等价于 App()->make(Tenant::class)

        // 方式2：直接实例化（不推荐，非单例，租户信息不共享）
        // $tenant = new Tenant();

        // 1. 获取租户ID（核心，BaseRepository 自动使用此ID筛选数据）
        $tenantId = $tenant->getId();
        var_dump("当前租户ID：", $tenantId);

        // 2. 获取租户全部信息
        $tenantAllInfo = $tenant->getInfo();
        var_dump("租户全部信息：", $tenantAllInfo);

        // 3. 获取租户单个字段（两种方式等价）
        $tenantName = $tenant->getInfo('name', '默认租户'); // 带默认值
        $tenantName2 = $tenant->name; // 魔术方法，更简洁
        $tenantDomain = $tenant->getDomain(); // 快捷方法
        var_dump("租户名称：", $tenantName, $tenantName2);
        var_dump("租户域名：", $tenantDomain);

        // 4. 检查租户是否有效（启用状态）
        if ($tenant->isValid()) {
            echo "当前租户有效";
        } else {
            echo "当前租户无效（已禁用或不存在）";
        }

        // 5. 获取租户状态（魔术方法直接获取字段）
        $tenantStatus = $tenant->status;
        var_dump("租户状态：", $tenantStatus);
    }
}
```

### 2.  手动设置/切换租户（如后台租户管理功能）
```php
<?php
namespace App\Controller\Admin;

use Framework\Core\App;
use Framework\Tenant\Tenant;

class TenantController
{
    // 切换租户（管理员操作）
    public function switchTenant(int $tenantId)
    {
        $tenant = App()->make('tenant');

        // 设置租户ID（自动保存到 Session + Cookie，持久化生效）
        $tenant->setId($tenantId, true);

        // 验证切换是否成功
        if ($tenant->getId() == $tenantId && $tenant->isValid()) {
            return json(['code' => 200, 'msg' => '租户切换成功', 'data' => ['tenant_name' => $tenant->getName()]]);
        } else {
            return json(['code' => 400, 'msg' => '租户切换失败，租户不存在或已禁用']);
        }
    }

    // 清除当前租户（退出登录时使用）
    public function clearTenant()
    {
        $tenant = App()->make('tenant');
        $tenant->clear(); // 清除租户ID、Session、Cookie、缓存

        if (is_null($tenant->getId())) {
            return json(['code' => 200, 'msg' => '租户信息已清除']);
        }
    }

    // 从请求头获取租户（前后端分离场景，前端传递 X-Tenant-Id 头信息）
    public function getTenantFromHeader()
    {
        $tenant = App()->make('tenant');
        // Tenant 类初始化时已自动从请求头加载，无需手动处理
        $tenantId = $tenant->getId();
        return json(['code' => 200, 'data' => ['tenant_id' => $tenantId]]);
    }
}
```

## 四、 UserRepository 使用范例（CRUD 全场景，核心业务）
### 1.  依赖注入获取 UserRepository 实例（推荐）
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;
use Framework\Core\App;

class UserService
{
    // 方式1：构造函数注入（推荐，自动依赖解析）
    protected UserRepository $userRepo;
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    // 方式2：手动从容器获取（灵活，适合动态获取）
    public function getRepoManual()
    {
        $userRepo = App()->make(UserRepository::class);
        return $userRepo;
    }
}
```

### 2.  查询操作范例（findById/findOneBy/findAll/paginate）
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $userRepo)
    {
    }

    // 1. 根据主键ID查询用户（支持关联预加载）
    public function getUserById(int $userId)
    {
        // 示例：预加载用户的「订单」和「个人资料」关联（模型需定义关联关系）
        $user = $this->userRepo->findById($userId, ['orders', 'profile']);

        if (!$user) {
            return "用户不存在";
        }

        // 输出用户信息（双ORM兼容，模型实例可直接获取字段）
        echo "用户名：" . $user->username;
        echo "邮箱：" . $user->email;

        // 输出关联数据（若预加载了 orders）
        if (isset($user->orders)) {
            foreach ($user->orders as $order) {
                echo "订单号：" . $order->order_no;
            }
        }

        return $user;
    }

    // 2. 根据条件查询单个用户
    public function getUserByCondition()
    {
        // 示例1：简单条件（等值查询）
        $user1 = $this->userRepo->findOneBy(['username' => 'zhangsan', 'status' => 1]);

        // 示例2：复杂条件（多操作符，兼容 BaseRepository 的 DSL 语法）
        $user2 = $this->userRepo->findOneBy([
            'age' => ['>', 18], // 年龄大于18
            'email' => ['like', '%@gmail.com'], // 邮箱包含 @gmail.com
            'create_time' => ['between', [1719609600, 1722288000]], // 创建时间在指定区间
            'tenant_id' => 1 // 租户ID筛选（手动指定，BaseRepository 会自动筛选，无需手动添加）
        ]);

        return $user2;
    }

    // 3. 根据条件查询多个用户（支持排序、分页限制）
    public function getUsersList()
    {
        // 示例：查询状态为1的用户，按创建时间倒序，只返回前20条
        $users = $this->userRepo->findAll(
            ['status' => 1], // 查询条件
            ['create_time' => 'desc', 'id' => 'asc'], // 排序条件
            20, // 限制返回20条
            ['profile'] // 预加载关联
        );

        // 遍历用户列表（双ORM兼容，Collection/数组均可遍历）
        foreach ($users as $user) {
            echo "用户ID：" . $user->id . "，用户名：" . $user->username;
        }

        return $users;
    }

    // 4. 分页查询用户（业务列表页常用）
    public function getUsersPaginate()
    {
        // 示例：分页查询，每页15条，按ID倒序
        $paginate = $this->userRepo->paginate(
            ['status' => 1], // 查询条件
            15, // 每页条数
            ['id' => 'desc'], // 排序条件
            ['profile'] // 关联预加载
        );

        // 分页数据获取（双ORM兼容，用法一致）
        $users = $paginate->items(); // 当前页数据
        $total = $paginate->total(); // 总条数
        $pages = $paginate->lastPage(); // 总页数
        $currentPage = $paginate->currentPage(); // 当前页码
        $perPage = $paginate->perPage(); // 每页条数

        // 输出分页信息
        echo "当前页：{$currentPage}/{$pages}，总条数：{$total}";

        // 遍历当前页用户
        foreach ($users as $user) {
            echo "用户名：" . $user->username;
        }

        return $paginate;
    }

    // 5. 自定义查询方法调用（UserRepository 中扩展的方法）
    public function getUserByEmail(string $email)
    {
        $user = $this->userRepo->findByEmail($email);
        return $user;
    }
}
```

### 3.  写入操作范例（create/save/update/delete）
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $userRepo)
    {
    }

    // 1. 新增用户（create 方法）
    public function createUser()
    {
        $userData = [
            'username' => 'lisi',
            'email' => 'lisi@example.com',
            'password' => password_hash('123456', PASSWORD_DEFAULT), // 密码加密
            'status' => 1,
            'age' => 25,
            'tenant_id' => 1 // 多租户场景，BaseRepository 自动填充，无需手动指定
        ];

        // 新增用户，返回模型实例
        $user = $this->userRepo->create($userData);

        if ($user) {
            echo "用户创建成功，ID：" . $user->id;
        } else {
            echo "用户创建失败";
        }

        return $user;
    }

    // 2. 保存用户（支持新增/更新，自动判断主键）
    public function saveUser()
    {
        // 示例1：新增用户（无主键 id）
        $newUserData = [
            'username' => 'wangwu',
            'email' => 'wangwu@example.com',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'status' => 1
        ];
        $newUser = $this->userRepo->save($newUserData);
        echo "新增用户ID：" . $newUser->id;

        // 示例2：更新用户（有主键 id）
        $updateUserData = [
            'id' => 1, // 主键ID，自动判断为更新
            'username' => 'zhangsan_update',
            'age' => 26,
            'status' => 0
        ];
        $updateUser = $this->userRepo->save($updateUserData);
        echo "用户更新成功，用户名：" . $updateUser->username;

        return $updateUser;
    }

    // 3. 根据ID更新用户
    public function updateUserById(int $userId)
    {
        $updateData = [
            'email' => 'update@example.com',
            'age' => 28,
            'updated_at' => time()
        ];

        // 更新用户，返回布尔值（是否成功）
        $isSuccess = $this->userRepo->update($userId, $updateData);

        if ($isSuccess) {
            echo "用户更新成功";
        } else {
            echo "用户更新失败（用户不存在或无数据变更）";
        }

        return $isSuccess;
    }

    // 4. 批量更新用户（自定义方法调用）
    public function batchUpdateUserStatus()
    {
        $userIds = [1, 2, 3, 4, 5]; // 要更新的用户ID
        $status = 0; // 目标状态（禁用）

        // 批量更新，返回受影响行数
        $affectedRows = $this->userRepo->batchUpdateStatus($userIds, $status);

        echo "批量更新成功，受影响行数：" . $affectedRows;
        return $affectedRows;
    }

    // 5. 根据ID删除用户
    public function deleteUserById(int $userId)
    {
        $isSuccess = $this->userRepo->delete($userId);

        if ($isSuccess) {
            echo "用户删除成功";
        } else {
            echo "用户删除失败（用户不存在）";
        }

        return $isSuccess;
    }

    // 6. 批量删除用户
    public function batchDeleteUsers()
    {
        // 示例：删除状态为0且创建时间小于指定时间的用户
        $criteria = [
            'status' => 0,
            'create_time' => ['<', 1719609600]
        ];

        // 批量删除，返回受影响行数
        $affectedRows = $this->userRepo->deleteBy($criteria);

        echo "批量删除成功，受影响行数：" . $affectedRows;
        return $affectedRows;
    }
}
```

### 4.  字段增减 & 统计 & 事务操作范例
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $userRepo)
    {
    }

    // 1. 字段自增（如：用户年龄+1，同时更新更新时间）
    public function incrementUserAge(int $userId)
    {
        $isSuccess = $this->userRepo->increment(
            $userId, // 用户ID
            'age', // 自增字段
            1, // 自增数量
            ['updated_at' => time()] // 额外更新的字段
        );

        echo $isSuccess ? "年龄自增成功" : "年龄自增失败";
        return $isSuccess;
    }

    // 2. 字段自减（如：用户积分-10）
    public function decrementUserScore(int $userId)
    {
        $isSuccess = $this->userRepo->decrement(
            $userId,
            'score',
            10,
            ['updated_at' => time()]
        );

        echo $isSuccess ? "积分自减成功" : "积分自减失败";
        return $isSuccess;
    }

    // 3. 聚合查询（count/sum/max/min/avg）
    public function getUserStatistics()
    {
        // 示例1：统计状态为1的用户总数
        $userCount = $this->userRepo->aggregate('count', ['status' => 1]);
        echo "有效用户总数：" . $userCount;

        // 示例2：统计所有用户的平均年龄
        $avgAge = $this->userRepo->aggregate('avg', [], 'age');
        echo "用户平均年龄：" . $avgAge;

        // 示例3：统计用户的最大年龄
        $maxAge = $this->userRepo->aggregate('max', [], 'age');
        echo "用户最大年龄：" . $maxAge;

        // 示例4：统计用户的积分总和
        $sumScore = $this->userRepo->aggregate('sum', ['status' => 1], 'score');
        echo "有效用户积分总和：" . $sumScore;

        return [
            'count' => $userCount,
            'avg_age' => $avgAge,
            'max_age' => $maxAge,
            'sum_score' => $sumScore
        ];
    }

    // 4. 事务操作（确保多步操作原子性，要么全部成功，要么全部回滚）
    public function transactionDemo(int $userId1, int $userId2)
    {
        try {
            // 事务闭包内执行多步操作
            $result = $this->userRepo->transaction(function () use ($userId1, $userId2) {
                // 步骤1：用户1积分+100
                $this->userRepo->increment($userId1, 'score', 100);

                // 步骤2：用户2积分-100
                $this->userRepo->decrement($userId2, 'score', 100);

                // 步骤3：手动抛出异常，测试事务回滚（实际业务中可删除）
                // throw new \Exception("测试事务回滚");

                // 事务成功，返回自定义结果
                return [
                    'status' => 'success',
                    'msg' => '积分转账成功'
                ];
            });

            echo $result['msg'];
            return $result;
        } catch (\Exception $e) {
            // 事务失败，捕获异常
            echo "事务执行失败：" . $e->getMessage();
            return ['status' => 'fail', 'msg' => $e->getMessage()];
        }
    }

    // 5. 原生SQL执行（复杂查询/操作，通用方法无法满足时使用）
    public function rawSqlDemo()
    {
        // 示例1：执行原生查询SQL
        $sql = "SELECT id, username, email FROM user WHERE status = ? AND age > ?";
        $bindings = [1, 18]; // 绑定参数，防止SQL注入
        $users = $this->userRepo->query($sql, $bindings);
        echo "原生查询返回用户数：" . count($users);

        // 示例2：执行原生执行SQL（更新操作）
        $updateSql = "UPDATE user SET status = ? WHERE id = ?";
        $updateBindings = [0, 1];
        $affectedRows = $this->userRepo->execute($updateSql, $updateBindings);
        echo "原生更新受影响行数：" . $affectedRows;

        return $users;
    }
}
```

## 五、 多租户场景整合使用（BaseRepository + Tenant 自动筛选）
这是核心整合场景，`BaseRepository` 会自动从 `Tenant` 获取租户ID，实现数据隔离。
### 1.  自动数据隔离（无需手动添加 tenant_id 条件）
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;
use Framework\Core\App;
use Framework\Tenant\Tenant;

class TenantUserService
{
    public function __construct(protected UserRepository $userRepo)
    {
    }

    public function getTenantUsers()
    {
        // 1. Tenant 自动加载租户ID（请求头/Session/Cookie/配置）
        $tenant = App()->make('tenant');
        echo "当前租户ID：" . $tenant->getId();

        // 2. BaseRepository 自动拼接 tenant_id 条件，无需手动添加
        // 实际执行的SQL：SELECT * FROM user WHERE status = 1 AND tenant_id = ?
        $users = $this->userRepo->findAll(['status' => 1]);

        // 遍历租户下的用户
        foreach ($users as $user) {
            echo "租户用户：" . $user->username . "，租户ID：" . $user->tenant_id;
        }

        return $users;
    }

    // 2. 手动覆盖租户条件（特殊场景，如超级管理员查看所有租户数据）
    public function getAllUsersBySuperAdmin()
    {
        // 示例：手动传入 tenant_id = ['in', [1,2,3]]，覆盖自动筛选
        $users = $this->userRepo->findAll([
            'status' => 1,
            'tenant_id' => ['in', [1, 2, 3]] // 手动指定租户ID，BaseRepository 不再自动拼接
        ]);

        return $users;
    }
}
```

### 2.  租户切换后的数据查询（动态切换租户）
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;
use Framework\Core\App;
use Framework\Tenant\Tenant;

class TenantSwitchService
{
    public function __construct(protected UserRepository $userRepo)
    {
    }

    public function switchTenantAndGetUsers(int $targetTenantId)
    {
        $tenant = App()->make('tenant');

        // 1. 切换租户ID
        $tenant->setId($targetTenantId, true); // 保存到 Session + Cookie

        // 2. 验证租户是否有效
        if (!$tenant->isValid()) {
            return "租户无效，无法查询数据";
        }

        // 3. 查询当前租户下的用户（自动筛选 targetTenantId）
        $users = $this->userRepo->findAll(['status' => 1]);
        echo "租户「{$tenant->getName()}」下的用户数：" . count($users);

        return $users;
    }
}
```

## 六、 高级用法：关联查询 & 复杂条件查询
### 1.  关联预加载（避免N+1查询问题）
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;

class UserRelationService
{
    public function __construct(protected UserRepository $userRepo)
    {
    }

    public function getUserWithRelations(int $userId)
    {
        // 示例1：预加载单个关联
        $user = $this->userRepo->findById($userId, ['orders']);
        // 访问关联数据，无额外SQL查询
        foreach ($user->orders as $order) {
            echo "订单号：" . $order->order_no;
        }

        // 示例2：预加载多个关联
        $user = $this->userRepo->findById($userId, ['orders', 'profile', 'roles']);
        // 访问个人资料
        echo "用户昵称：" . $user->profile->nickname;
        // 访问角色
        foreach ($user->roles as $role) {
            echo "角色名称：" . $role->role_name;
        }

        // 示例3：分页查询时预加载关联
        $paginate = $this->userRepo->paginate(['status' => 1], 15, ['id' => 'desc'], ['orders']);
        foreach ($paginate->items() as $user) {
            echo "用户：" . $user->username . "，订单数：" . count($user->orders);
        }

        return $user;
    }
}
```

### 2.  复杂条件查询（BaseRepository DSL 语法）
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;

class UserComplexQueryService
{
    public function __construct(protected UserRepository $userRepo)
    {
    }

    public function getComplexUsers()
    {
        // 复杂查询条件（包含 JOIN、GROUP BY、HAVING、OR 分组等）
        $criteria = [
            'select' => ['user.id', 'user.username', 'count(order.id) as order_count'], // 指定查询字段
            'status' => 1, // 基础条件
            'age' => ['between', [18, 30]], // 年龄区间
            'leftJoin' => [ // 左关联订单表
                ['order', 'user.id', '=', 'order.user_id']
            ],
            'groupBy' => ['user.id'], // 分组
            'having' => [ // 分组筛选
                ['order_count', '>', 0] // 有订单的用户
            ],
            'or_group' => [ // OR 分组（id=1 OR username=zhangsan OR email like %@gmail.com）
                'id' => 1,
                'username' => 'zhangsan',
                'email' => ['like', '%@gmail.com']
            ],
            'distinct' => true, // 去重
            'lock' => true // 悲观锁（for update）
        ];

        // 排序条件
        $orderBy = ['order_count' => 'desc', 'user.create_time' => 'desc'];

        // 查询用户
        $users = $this->userRepo->findAll($criteria, $orderBy);

        foreach ($users as $user) {
            echo "用户：" . $user->username . "，订单数：" . $user->order_count;
        }

        return $users;
    }
}
```

## 七、 总结
1.  **核心流程**：容器绑定 → 定义模型 → 实现 Repository 子类 → 业务层注入使用
2.  **Tenant 用法**：全局获取租户信息、手动切换租户、验证租户有效性，自动与 BaseRepository 整合实现数据隔离
3.  **BaseRepository 用法**：
    - 基础查询：findById/findOneBy/findAll/paginate
    - 写入操作：create/save/update/delete
    - 高级操作：increment/decrement/aggregate/transaction/原生SQL
    - 复杂场景：关联预加载、DSL 复杂条件查询
4.  **多租户整合**：BaseRepository 自动拼接 tenant_id 条件，无需手动处理，支持手动覆盖条件