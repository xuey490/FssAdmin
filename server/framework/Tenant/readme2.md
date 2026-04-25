通过 **改造 `BaseRepository` 实现「可控的多租户筛选」**，既解决冲突问题，又支持超管临时取消数据范围，同时你可以放心放弃模型层的多租户实现，以下是完整解决方案：

## 一、 核心设计思路
1.  **避免双重筛选冲突**：给 `BaseRepository` 增加「多租户开关」和「手动覆盖标识」，默认自动筛选，可按需关闭或手动覆盖条件，避免SQL出现两个 `tenant_id=xxx`
2.  **支持临时超管权限**：通过「全局上下文/仓库实例属性」实现临时关闭多租户筛选，超管操作时可取消数据范围限制，操作完成后自动恢复（或手动恢复）
3.  **向下兼容原有功能**：保留原有自动筛选逻辑，不影响普通业务使用，仅新增超管相关控制能力

## 二、 第一步：改造 `BaseRepository`，增加多租户控制能力
### 1.  新增多租户控制属性（在 `BaseRepository` 中添加）
```php
<?php
abstract class BaseRepository implements RepositoryInterface
{
    // ... 原有属性（modelClass、isEloquent 等）

    /**
     * 多租户筛选开关（默认开启，自动拼接 tenant_id 条件）
     * 超管可临时关闭，取消数据隔离
     * @var bool
     */
    protected bool $tenantFilterEnabled = true;

    /**
     * 手动覆盖租户条件标识（避免自动筛选与手动条件冲突）
     * 若查询条件中已包含 tenant_id，自动跳过自动筛选
     * @var string
     */
    protected string $tenantField = 'tenant_id';

    /**
     * 临时关闭多租户筛选的标记（用于超管全局临时操作）
     * 静态属性：支持跨仓库实例共享超管状态
     * @var bool
     */
    protected static bool $superAdminTempDisable = false;

    // ... 原有方法
}
```

### 2.  改造 `applyTenantFilter` 方法，解决双重筛选冲突
修改原有 `applyTenantFilter` 方法，增加「开关判断」「手动覆盖判断」「超管临时关闭判断」，避免生成重复的 `tenant_id` 条件：
```php
/**
 * 应用多租户筛选条件（优化版：避免冲突，支持开关控制）
 * 自动判断是否需要拼接 tenant_id 条件，解决与模型层的双重筛选冲突
 * @param mixed $query 查询构造器
 * @param array $criteria 查询条件
 * @return void
 */
protected function applyTenantFilter(mixed $query, array $criteria): void
{
    // 3大不筛选场景：1.开关关闭 2.超管临时关闭 3.手动已传 tenant_id 条件
    if (
        !$this->tenantFilterEnabled // 实例级开关关闭
        || self::$superAdminTempDisable // 超管全局临时关闭
        || isset($criteria[$this->tenantField]) // 查询条件中已手动携带 tenant_id，避免重复
    ) {
        return; // 直接返回，不拼接 tenant_id 条件
    }

    // 获取租户ID（原有逻辑）
    $tenant = App()->make('tenant');
    $tenantId = $tenant->getId();

    // 租户ID不存在时，不拼接条件
    if (is_null($tenantId)) {
        return;
    }

    // 拼接租户筛选条件（仅执行一次，无重复冲突）
    $query->where($this->tenantField, $tenantId);
}
```

### 3.  新增多租户控制方法（实例级+静态级，支持超管操作）
在 `BaseRepository` 中添加以下方法，用于控制多租户筛选开关：
```php
/**
 * 实例级：开启/关闭多租户筛选（单个仓库实例生效）
 * 适用于：单个业务方法中临时关闭筛选（如超管查询单个租户数据）
 * @param bool $enabled true=开启，false=关闭
 * @return $this
 */
public function setTenantFilterEnabled(bool $enabled): self
{
    $this->tenantFilterEnabled = $enabled;
    return $this;
}

/**
 * 实例级：获取当前多租户筛选开关状态
 * @return bool
 */
public function isTenantFilterEnabled(): bool
{
    return $this->tenantFilterEnabled;
}

/**
 * 静态级：超管临时全局关闭多租户筛选（所有仓库实例生效）
 * 适用于：超管查看所有租户数据、批量操作所有租户数据等场景
 * @return void
 */
public static function superAdminDisableTenantFilter(): void
{
    self::$superAdminTempDisable = true;
}

/**
 * 静态级：超管操作完成后，恢复全局多租户筛选
 * 【重要】使用后必须手动调用恢复，避免影响后续业务
 * @return void
 */
public static function superAdminRestoreTenantFilter(): void
{
    self::$superAdminTempDisable = false;
}

/**
 * 静态级：获取超管临时关闭状态
 * @return bool
 */
public static function isSuperAdminTempDisabled(): bool
{
    return self::$superAdminTempDisable;
}
```

## 三、 第二步：放弃模型层多租户，使用 `BaseRepository` 实现数据隔离
### 1.  移除模型层多租户逻辑
删除模型中所有与多租户相关的代码（如 `globalScope` 全局作用域、`boot` 方法中的租户筛选等），示例：
```php
<?php
namespace App\Models;

use think\Model; // 或 Illuminate\Database\Eloquent\Model

class User extends Model
{
    // 移除原有模型层多租户代码（如下示例为需要删除的内容）
    // ########## 需删除的模型层多租户逻辑 ##########
    // protected static function boot()
    // {
    //     parent::boot();
    //     // 全局作用域添加租户筛选（现在由 BaseRepository 实现，需删除）
    //     static::addGlobalScope('tenant', function ($query) {
    //         $tenant = App()->make('tenant');
    //         $query->where('tenant_id', $tenant->getId());
    //     });
    // }
    // ###########################################

    // 保留原有其他配置
    protected $fillable = ['username', 'email', 'password', 'status', 'tenant_id', 'age'];
    protected $table = 'user';
}
```

### 2.  普通业务场景：自动数据隔离（无冲突，无需额外操作）
普通业务中，`BaseRepository` 会自动拼接 `tenant_id` 条件，实现数据隔离，且不会出现重复条件：
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $userRepo)
    {
    }

    // 普通用户查询：自动筛选当前租户数据，无重复 tenant_id 条件
    public function getCurrentTenantUsers()
    {
        // 执行SQL：SELECT * FROM user WHERE status = 1 AND tenant_id = ?
        // 仅拼接一次 tenant_id 条件，无冲突
        $users = $this->userRepo->findAll(['status' => 1]);
        return $users;
    }
}
```

## 四、 第三步：实现超管临时取消数据范围功能（核心需求）
提供 **两种超管使用方式**（实例级：单个仓库生效；静态级：全局所有仓库生效），灵活适配不同场景：

### 方式1：实例级临时关闭（推荐，仅影响当前仓库实例，无副作用）
适用于：超管查询「单个租户」的全部数据、或「单个业务」中临时取消筛选
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\OrderRepository;

class SuperAdminService
{
    public function __construct(
        protected UserRepository $userRepo,
        protected OrderRepository $orderRepo
    ) {
    }

    // 超管查询：单个仓库临时关闭筛选，查询所有租户的用户
    public function getAllUsersBySuperAdmin()
    {
        // 1. 实例级关闭多租户筛选（仅当前 userRepo 生效，orderRepo 不受影响）
        $this->userRepo->setTenantFilterEnabled(false);

        // 2. 查询所有租户数据：SQL 无 tenant_id 条件
        $allUsers = $this->userRepo->findAll(['status' => 1]);

        // 3. （可选）操作完成后，手动恢复筛选（避免影响后续使用该实例）
        $this->userRepo->setTenantFilterEnabled(true);

        return $allUsers;
    }

    // 超管查询：手动携带 tenant_id 条件，自动跳过自动筛选（无重复冲突）
    public function getUsersBySpecifiedTenant(int $tenantId)
    {
        // 查询条件中已携带 tenant_id，BaseRepository 自动跳过自动筛选
        // SQL：SELECT * FROM user WHERE status = 1 AND tenant_id = ?
        // 仅拼接一次（手动传入的）tenant_id 条件，无冲突
        $users = $this->userRepo->findAll([
            'status' => 1,
            'tenant_id' => $tenantId // 手动指定租户，自动跳过自动筛选
        ]);
        return $users;
    }
}
```

### 方式2：静态级全局关闭（适用于：超管批量操作所有租户数据）
适用于：超管导出所有租户数据、批量更新所有租户数据等场景（**使用后必须手动恢复**）
```php
<?php
namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use Framework\Repository\BaseRepository;

class SuperAdminBatchService
{
    public function __construct(
        protected UserRepository $userRepo,
        protected OrderRepository $orderRepo
    ) {
    }

    // 超管批量操作：全局关闭多租户筛选，所有仓库实例均生效
    public function batchHandleAllTenantData()
    {
        try {
            // 1. 全局关闭多租户筛选（所有继承 BaseRepository 的仓库均生效）
            BaseRepository::superAdminDisableTenantFilter();

            // 2. 操作1：查询所有租户的用户（无 tenant_id 条件）
            $allUsers = $this->userRepo->findAll(['status' => 1]);

            // 3. 操作2：查询所有租户的订单（无 tenant_id 条件）
            $allOrders = $this->orderRepo->findAll(['pay_status' => 1]);

            // 4. 批量操作（如：导出、批量更新等）
            // ... 你的业务逻辑 ...

            return [
                'user_count' => count($allUsers),
                'order_count' => count($allOrders)
            ];
        } finally {
            // 【关键】无论是否异常，都要恢复全局多租户筛选，避免影响后续业务
            BaseRepository::superAdminRestoreTenantFilter();
        }
    }
}
```

## 五、 关键优势（解决你的核心痛点）
1.  **无冲突**：通过「开关控制」「手动覆盖判断」，彻底避免 SQL 出现重复 `tenant_id=xxx` 条件，解决与模型层的冲突问题
2.  **灵活可控**：
    -  普通业务：自动开启筛选，实现数据隔离
    -  超管业务：支持「实例级」「静态级」两种关闭方式，按需取消数据范围
3.  **无副作用**：
    -  实例级关闭：仅影响当前仓库实例，不影响其他业务
    -  静态级关闭：通过 `try...finally` 强制恢复，避免遗漏恢复导致的安全问题
4.  **易于维护**：所有多租户逻辑统一在 `BaseRepository` 中实现，无需在每个模型中编写重复代码，后续修改只需调整仓库层

## 六、 完整使用流程总结
1.  **移除模型层多租户**：删除模型中的全局作用域等租户相关代码
2.  **使用普通业务**：直接调用 `Repository` 方法，自动实现数据隔离，无额外操作
3.  **超管临时操作**：
    -  单个仓库：调用 `$repo->setTenantFilterEnabled(false)` 关闭，操作后可恢复
    -  全局仓库：调用 `BaseRepository::superAdminDisableTenantFilter()` 关闭，在 `finally` 中调用 `superAdminRestoreTenantFilter()` 恢复
4.  **手动指定租户**：查询条件中直接传入 `tenant_id`，自动跳过自动筛选，无重复冲突