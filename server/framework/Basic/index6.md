基于 `TpBelongsToTenant` Trait，要在控制器中使用“超管（忽略租户隔离）”功能，主要有 **3 种方式**。

这些方式对应不同的业务场景，从**全局生效**到**特定查询生效**，你可以根据实际需求选择。

### 🛠️ 准备工作：引入 Facade
在控制器文件顶部，确保引入了 `Db` 门面（如果需要手动控制事务或更底层的操作）：
```php
use think\facade\Db;
```

---

### 方式一：全局开启/关闭 (粗粒度控制)
**适用场景**：整个控制器的方法中，所有的数据库操作都不受租户限制（例如：系统后台的统计报表）。

#### 代码示例
```php
public function adminReport()
{
    // 1. 开启超管模式（全局生效）
    User::ignoreTenant();

    // 2. 此时所有的查询/写入都不会自动添加 tenant_id 条件
    // 查询全平台用户
    $allUsers = User::select(); 
    
    // 查询全平台订单
    $allOrders = Order::with('user')->select(); 

    // 3. 业务逻辑处理...
    $data = [
        'total_users' => $allUsers->count(),
        'total_orders' => $allOrders->sum('amount'),
    ];

    // 4. 【重要】处理完后，记得恢复租户隔离（防止影响后续代码）
    User::restoreTenant();

    return json($data);
}
```

---

### 方式二：链式调用 (推荐，细粒度控制)
**适用场景**：大部分操作需要租户隔离，但某一次特定的查询需要跨租户。

#### 代码示例
```php
public function getUserInfo($id)
{
    // 1. 默认状态：受租户隔离保护（只能查到当前租户下的 $id 用户）
    // $user = User::find($id); 

    // 2. 使用链式调用临时忽略租户（仅本次查询生效）
    // 这里利用了我们优化 Trait 时保留的链式特性
    $user = User::ignoreTenant()->find($id); 
    
    // 3. 注意：链式调用后，后续如果还有查询，会自动恢复吗？
    // 答：不一定安全。为了严谨，建议手动恢复，或者使用下面的“闭包方式”。
    User::restoreTenant(); 

    return json($user);
}
```

---

### 方式三：闭包模式 (最安全)
**适用场景**：最推荐的方式。确保“超管逻辑”执行完后，无论成功还是抛出异常，都会自动恢复租户隔离，防止内存泄漏或静态属性污染。

#### 代码示例
```php
public function syncUserData()
{
    try {
        // 1. 执行闭包内的代码，忽略租户限制
        $result = User::withIgnoreTenant(function () {
            
            // 在这里，$ignoreTenantScope = true
            
            // 查询所有租户下 status=0 的用户（物理清理或归档）
            $usersToSync = User::where('status', 0)->select();
            
            foreach ($usersToSync as $user) {
                // 执行同步逻辑...
                // 因为忽略了租户，这里可以操作任何数据
            }
            
            return ['code' => 0, 'msg' => '同步成功', 'count' => $usersToSync->count()];
        });

        // 2. 代码执行到这里时，withIgnoreTenant 已经自动调用了 restoreTenant()
        // 后续的代码依然是安全的租户隔离模式
        return json($result);

    } catch (\Exception $e) {
        // 即使抛出异常，finally 也会保证 restoreTenant() 被调用
        User::restoreTenant(); 
        return json(['code' => 1, 'msg' => $e->getMessage()]);
    }
}
```

---

### 💡 特殊场景：在 Repository 或 Service 层使用

如果你的业务逻辑封装在了 Service 层，用法是一样的。

#### Service 层代码
```php
// app/service/UserService.php
public function getPlatformTotalCount()
{
    // 调用模型的静态方法
    return User::ignoreTenant()->count();
}
```

#### Controller 调用
```php
public function dashboard()
{
    // 即使 Service 层开启了 ignoreTenant，
    // 如果 Service 层没有手动 restore，这里可能会有残留风险
    // 所以在 Service 层内部最好也用 withIgnoreTenant 包裹
    $count = (new UserService())->getPlatformTotalCount();
    
    // 为了安全，Controller 这里最好也检查一下状态并恢复
    User::restoreTenant(); 

    return json(['total' => $count]);
}
```

### 📌 总结建议

| 方式 | 代码示例 | 推荐度 | 说明 |
| :--- | :--- | :--- | :--- |
| **闭包模式** | `User::withIgnoreTenant(fn() => {...})` | ⭐⭐⭐⭐⭐ | **最推荐**。自动开关，绝对安全，不会导致静态属性污染。 |
| **链式调用** | `User::ignoreTenant()->select()` | ⭐⭐⭐⭐ | 写法简洁。**注意**：调用后建议紧接着调用 `restoreTenant()`。 |
| **全局开关** | `User::ignoreTenant()` ... `User::restoreTenant()` | ⭐⭐⭐ | 适用于大段连续的逻辑。**风险**：容易忘记关闭，导致后续普通查询也变成全量查询。 |

**核心原则**：**“谁开启，谁关闭”**。如果你在控制器里开启了 `ignoreTenant`，最好在同一个控制器方法结束前调用 `restoreTenant`。