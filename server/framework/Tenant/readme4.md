基于你重构后的 `BaseRepository` 和 `TenantContext`，以下是几个实际的使用示例，涵盖了**基础CRUD**、**复杂查询（DSL）**、**多租户控制**以及**事务处理**。

### 1. 准备工作：定义模型和仓库

假设我们有一个用户表 `users`，我们需要先定义模型和对应的仓库类。

**A. 定义模型 (App/Models/User.php)**
这里以 Eloquent 为例（ThinkPHP 模型同理）：

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Framework\Basic\Scopes\LaTenantScope; // 假设你的租户Scope在这里

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['tenant_id', 'name', 'email', 'status', 'age'];

    // 自动加载租户 Scope
    protected static function booted()
    {
        static::addGlobalScope(new LaTenantScope());
    }
}
```

**B. 定义仓库 (App/Repositories/UserRepository.php)**

```php
namespace App\Repositories;

use Framework\Repository\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository
{
    // 【必须】指定当前仓库操作的模型
    protected string $modelClass = User::class;
    
    // 【可选】可以在这里添加特定于用户的业务查询方法
    public function findVips()
    {
        // $this() 等价于 newQuery()
        return $this()->where('is_vip', 1)->get();
    }
}
```

---

### 2. 场景一：基础 CRUD 操作

在控制器或服务层中注入 `UserRepository` 进行使用。

```php
use App\Repositories\UserRepository;
use Framework\Tenant\TenantContext;

class UserService
{
    public function __construct(protected UserRepository $userRepo) {}

    public function basicOperations()
    {
        // 0. 前置：设置当前租户上下文（通常在中间件完成）
        TenantContext::setTenantId(1001);

        // 1. 新增用户
        // 仓库会自动处理租户ID（如果模型有默认值）或你需要手动传入（视模型策略而定）
        $user = $this->userRepo->create([
            'name'   => '张三',
            'email'  => 'zhangsan@example.com',
            'status' => 1,
            'age'    => 25
        ]);

        // 2. 根据ID查找 (会自动带上 WHERE tenant_id = 1001)
        $info = $this->userRepo->findById($user->id);

        // 3. 更新
        $this->userRepo->update($user->id, ['age' => 26]);

        // 4. 自增字段
        $this->userRepo->increment($user->id, 'score', 10);

        // 5. 删除
        $this->userRepo->delete($user->id);
    }
}
```

---

### 3. 场景二：复杂查询（DSL 风格）

`BaseRepository` 强大的地方在于 `buildQuery` 方法，允许你用数组定义复杂的查询条件，这对于构建**搜索接口**非常有用。

```php
public function searchUsers(array $requestParams)
{
    // 假设前端传来的参数：?keyword=李&status=1
    
    // 构建查询 DSL 数组
    $criteria = [
        // 基础等于查询
        'status' => 1,
        
        // LIKE 查询
        'name'   => ['like', '%李%'],
        
        // 范围查询
        'age'    => ['between', [20, 50]],
        
        // IN 查询
        'level'  => ['in', [1, 2, 3]],
        
        // 关联预加载 (定义在 criteria 外也可以，这里演示 DSL 能力)
        // 'with' => ['profile', 'orders'], 
        
        // 排序
        // 'order' => ['id' => 'desc']
    ];

    // 执行分页查询 (每页 20 条，按创建时间倒序)
    // 自动应用 TenantContext，只查当前租户数据
    $result = $this->userRepo->paginate(
        $criteria, 
        20, 
        ['created_at' => 'desc'], 
        ['profile'] // 关联查询
    );

    return $result;
}
```

---

### 4. 场景三：超管跨租户操作 (TenantContext 集成)

这是重构后的核心亮点。当你需要进行“上帝模式”操作时（例如统计所有租户的总用户数，或者后台管理员查看特定租户数据）。

```php
use Framework\Tenant\TenantContext;

public function superAdminOperations()
{
    // 方式 A：临时忽略租户隔离（推荐）
    // 使用 withIgnore 闭包，执行完自动恢复，防止状态泄露
    $allUsersCount = TenantContext::withIgnore(function() {
        // 这里的查询不带 tenant_id 条件
        // Repository 会自动调用 withoutGlobalScope
        return $this->userRepo->aggregate('count');
    });

    // 方式 B：手动切换租户身份
    // 模拟切换到租户 2002 进行操作
    TenantContext::setTenantId(2002);
    $tenant2Users = $this->userRepo->findAll(['status' => 1]);
    
    // 方式 C：全局关闭（慎用，记得手动 restore）
    TenantContext::ignore();
    $allData = $this->userRepo->findAll(); // 查出全库数据
    TenantContext::restore(); // 必须恢复
}
```

---

### 5. 场景四：事务与原生 SQL

当业务逻辑涉及多个步骤，或者 ORM 性能不足时使用。

```php
public function transferPoints(int $fromUserId, int $toUserId, int $points)
{
    try {
        // 使用仓库的 transaction 方法
        $this->userRepo->transaction(function() use ($fromUserId, $toUserId, $points) {
            
            // 扣减积分
            $this->userRepo->decrement($fromUserId, 'points', $points);
            
            // 增加积分
            $this->userRepo->increment($toUserId, 'points', $points);
            
            // 如果需要执行非常复杂的原生 SQL
            // Repository 会根据当前环境调用 ThinkDb::execute 或 IlluminateDb::statement
            $this->userRepo->execute(
                "INSERT INTO point_logs (user_id, amount, created_at) VALUES (?, ?, ?)", 
                [$fromUserId, -$points, date('Y-m-d H:i:s')]
            );
            
            // 抛出异常会自动回滚
            if ($points > 10000) {
                throw new \Exception("转账金额过大");
            }
        });
        
        return true;
    } catch (\Exception $e) {
        return false;
    }
}
```

### 6. 场景五：纯表模式（无 Model）

如果你只需要操作一个中间表或日志表，不想创建 Model 类，可以这样定义仓库：

```php
class LogRepository extends BaseRepository
{
    // 不定义 $modelClass，或者定义为空
    protected string $modelClass = ''; 
    
    // 必须重写 getBuilder，或者直接用 factory->table()
    // 但 BaseRepository 默认依赖 modelClass。
    // 建议：即使是纯表，也建议定义一个空的 Model 类继承 Model，
    // 或者在该 Repository 中重写 getModel() 返回 DB::table('logs') 的构造器。
    
    // 更推荐的方式：在 Repository 内部使用 rawQuery()
    
    public function addLog($msg)
    {
        // rawQuery() 会根据 TenantContext 自动拼接 tenant_id
        return $this->rawQuery()
            ->from('system_logs')
            ->insert([
                'tenant_id' => TenantContext::getTenantId(),
                'message'   => $msg
            ]);
    }
}
```