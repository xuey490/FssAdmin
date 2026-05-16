实现“超管绕过租户限制”通常有两种主流方案：

1.  **显式绕过（推荐）**：在代码中明确调用一个方法（如 `withoutTenancy()`），告诉程序这次查询不需要隔离。
2.  **隐式绕过（自动）**：在 Scope 内部判断当前登录身份，如果是超管，直接跳过过滤逻辑。

下面是具体的实现代码，建议**优先使用方案一**（显式绕过），因为这样更安全，不会因为超管身份导致意外修改了全库数据。

---

### 方案一：增加 `withoutTenancy()` 方法（显式绕过）

我们需要修改 `LaTenantScope` 类，利用 Laravel 的 `extend` 机制注册一个自定义宏。

**修改文件：`Framework\Basic\Scopes\LaTenantScope.php`**

```php
<?php

declare(strict_types=1);

namespace Framework\Basic\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LaTenantScope implements Scope
{
    /**
     * 应用作用域
     */
    public function apply(Builder $builder, Model $model)
    {
        // 获取租户ID
        $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;

        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', '=', $tenantId);
        }
    }

    /**
     * 【新增】扩展 Builder 方法
     * 当这个 Scope 被注册时，Laravel 会自动调用 extend 方法
     * 我们在这里给 Builder 动态添加一个 withoutTenancy 方法
     */
    public function extend(Builder $builder)
    {
        // 注册宏指令：withoutTenancy
        $builder->macro('withoutTenancy', function (Builder $builder) {
            // 移除当前这个类定义的全局作用域
            return $builder->withoutGlobalScope($this);
        });
    }
}
```

**使用方式：**

```php
// 1. 普通查询（受租户限制）
$users = User::all(); 
// SQL: select * from users where tenant_id = 1001

// 2. 超管查询（绕过限制，查所有）
$allUsers = User::withoutTenancy()->get();
// SQL: select * from users

// 3. 结合其他条件
$adminUser = User::withoutTenancy()->where('id', 1)->first();
```

---

### 方案二：基于身份自动绕过（隐式绕过）

如果你希望超管登录后，**所有**查询自动变成全量查询（不需要改业务代码），可以在 `apply` 方法里加判断。

**修改文件：`Framework\Basic\Scopes\LaTenantScope.php`**

```php
    public function apply(Builder $builder, Model $model)
    {
        // 1. 【新增】如果是超管，直接 return，不加 where 条件
        // 假设你有一个全局函数 isSuperAdmin() 或者从 Session/Auth 判断
        if (function_exists('isSuperAdmin') && \isSuperAdmin()) {
            return; 
        }

        // 2. 普通租户逻辑
        $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;

        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', '=', $tenantId);
        }
    }
```

**⚠️ 风险提示：**
这种方式虽然省事，但非常危险。
*   **场景**：超管想去修改“租户A”的某个用户。
*   **后果**：因为自动绕过了限制，如果操作不当（例如 ID 传错），超管可能会意外查到并修改了“租户B”的数据，而且没有任何报错。

---

### 方案三：原生 Laravel 写法（无需修改 Scope）

如果你不想改 Scope 代码，Laravel 原生支持 `withoutGlobalScope` 方法，只是写起来稍微长一点：

```php
use Framework\Basic\Scopes\LaTenantScope;

// 移除指定的作用域类
$data = User::withoutGlobalScope(LaTenantScope::class)->get();
```

### 总结建议

建议采用 **方案一（withoutTenancy 宏）**。

在业务逻辑中（Controller/Service），明确区分“普通业务”和“管理业务”。

```php
public function index()
{
    $query = User::query();

    // 如果是超管，并且前端传了一个 flag 说要看全部
    if (isSuperAdmin() && request()->get('show_all')) {
        $query->withoutTenancy();
    }

    return $query->paginate();
}
```

这样既灵活又安全。