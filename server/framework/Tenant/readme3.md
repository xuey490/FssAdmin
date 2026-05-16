
1. **TenantContext 现在如何真正承担“租户隔离 + 超管越级”职责**
2. **给你一整套 CRUD 示例（查 / 增 / 改 / 删）**
3. **对比：`getCurrentTenantId()` 全局函数 vs 上下文级 TenantContext**

---

# 一、TenantContext 在新架构中的真实定位

现在你的体系是：

```
Controller / Service
        ↓
TenantContext（唯一状态源）
        ↓
LaTenantScope（是否加 where tenant_id）
        ↓
Eloquent ORM
```

也就是说：

> **TenantContext = 权限上下文**
> ORM 只是被动读取这个上下文，不再保存任何权限状态。

---

## TenantContext 的最终职责

```php
TenantContext::setTenantId(1001);   // 当前登录用户的租户
TenantContext::ignore();           // 超管模式
TenantContext::restore();          // 恢复隔离
TenantContext::shouldApplyTenant();// ORM 判断是否加租户条件
```

---

# 二、CRUD 全场景示例（隔离 + 越级）

假设模型：`User` 使用了 `LaBelongsToTenant`

---

## 1️⃣ 普通用户：自动租户隔离

### 初始化（登录后）

```php
TenantContext::setTenantId(1001);
```

---

### 查询（自动加 where tenant_id = 1001）

```php
$user = User::find(10);
// 实际 SQL:
// select * from users where id = 10 and tenant_id = 1001
```

---

### 新增（自动写 tenant_id）

```php
$user = new User();
$user->name = 'Alice';
$user->save();

// insert into users (...) values (..., tenant_id = 1001)
```

---

### 更新（只能更新自己租户的数据）

```php
User::where('id', 10)->update([
    'name' => 'Alice New',
]);
// SQL: update users set name = ... where id = 10 and tenant_id = 1001
```

---

### 删除（同样受限）

```php
User::where('id', 10)->delete();
// delete from users where id = 10 and tenant_id = 1001
```

---

## 2️⃣ 超级管理员：临时越级访问

---

### 方式 A：安全推荐（作用域执行）

```php
$user = User::withIgnoreTenant(function () {
    return User::find(4152260622576254976);
});
```

执行期间：

```
TenantContext::ignore()
→ LaTenantScope 不加 tenant 条件
→ 查询完成
→ TenantContext 自动 restore()
```

---

### 方式 B：TP 风格（你熟悉的）

```php
$user = User::ignoreTenant()->find(4152260622576254976);

// 内部其实是：
TenantContext::ignore();
User::find(...);
```

⚠️ 注意：这种方式**需要你在请求结束时 restore**，否则会影响后续逻辑。

---

### 方式 C：批量操作越级

```php
TenantContext::withIgnore(function () {
    User::where('status', 0)->delete();
});
```

---

## 3️⃣ 超管 + 新增数据

通常有两种策略：

### 策略 1：超管新增仍归属当前租户

```php
TenantContext::setTenantId(1001);
TenantContext::ignore();

$user = new User();
$user->name = 'System User';
$user->save();
// tenant_id 仍然是 1001
```

### 策略 2：系统级数据（无租户）

```php
TenantContext::ignore();
TenantContext::setTenantId(null);

$user = new User();
$user->name = 'Platform Admin';
$user->save();
// tenant_id 不写
```

---

# 三、TenantContext vs getCurrentTenantId()

你现在面临的是一个**架构升级点**，我们直接对比。

---

## 方案 A：函数式（旧方案）

```php
$tenantId = function_exists('getCurrentTenantId')
    ? getCurrentTenantId()
    : null;
```

### 特点

| 维度       | 表现               |
| -------- | ---------------- |
| 入口       | 全局函数             |
| 状态       | 分散在各处            |
| 超管模式     | 需要再造一套 ignore 机制 |
| 测试       | 很难 mock          |
| 并发 / 长连接 | 容易出状态残留          |
| 可维护性     | 低                |
| 扩展       | 困难               |

---

## 方案 B：TenantContext（推荐）

```php
TenantContext::setTenantId(1001);
TenantContext::ignore();
```

### 特点

| 维度          | 表现               |
| ----------- | ---------------- |
| 入口          | 单一上下文            |
| 状态          | 集中               |
| 超管模式        | 天然支持             |
| 测试          | 极易 mock          |
| 并发 / Swoole | 可控（请求结束 restore） |
| 维护          | 高                |
| 扩展          | 非常容易             |

---

## 结论一句话

> **函数方式适合“获取一个值”**
> **TenantContext 适合“管理一套权限状态”**

你现在做的是：
**多租户 + 超管越级 + 批量操作限制** —— 这是典型的**上下文权限系统**，
继续用 `getCurrentTenantId()` 已经不够用了。

---

# 四、推荐的标准工作流（生产级）

在你的框架里，我建议固定一个流程：

---

## 1️⃣ 请求开始（Middleware）

```php
TenantContext::setTenantId($loginUser->tenant_id);

if ($loginUser->isSuperAdmin()) {
    // 默认仍然隔离，只有显式 ignore 才越级
}
```

---

## 2️⃣ 业务代码

```php
// 普通业务
$list = Order::where('status', 1)->get();

// 超管越级
Order::withIgnoreTenant(function () {
    return Order::where('status', 1)->get();
});
```

---

## 3️⃣ 请求结束（Middleware finally）

```php
TenantContext::restore();
TenantContext::setTenantId(null);
```

---

# 五、你现在这套体系的最终评价

你现在这个组合：

* `TenantContext`
* `LaTenantScope`
* `LaBelongsToTenant`

已经是**企业级多租户权限模型**了，具备：

* 请求级权限上下文
* ORM 自动隔离
* 超管临时越权
* 无状态模型
* 无污染实例
* 无 protected 错误
* 可测试、可维护

---

# 六、最终建议

1. **全面弃用 `getCurrentTenantId()` 这种函数式方案**
2. 统一入口：`TenantContext`
3. 所有租户 / 超管判断，只允许从 `TenantContext` 读取
4. ORM 层永远只做“执行者”，不做“状态保存者”

---

如果你愿意，我可以下一步直接帮你：

* 给你一套 **Middleware 示例（初始化 + 回收 TenantContext）**
* 或者把你现在的所有 `getCurrentTenantId()` 用法**批量迁移**到 `TenantContext` 方案。
