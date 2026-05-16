## 如何实现多租户数据隔离


> **用一个“上下文对象（Context）”决定：当前请求是否启用租户隔离**

不是 Query 决定，不是 Model 决定，而是 **运行上下文决定**。

下面我给你一个**完整、可直接用、框架级**的 `TenantContext` 实现。

---

## 三、完整的 `TenantContext` 实现（推荐版）

### 1️⃣ TenantContext（核心）

```php
<?php
declare(strict_types=1);

namespace Framework\Tenant;

final class TenantContext
{
    /** 当前租户 ID（普通用户） */
    private static ?int $tenantId = null;

    /** 是否忽略租户隔离（超管 / 系统） */
    private static bool $ignoreTenant = false;

    /**
     * 设置当前租户（登录后调用）
     */
    public static function setTenantId(?int $tenantId): void
    {
        self::$tenantId = $tenantId;
    }

    /**
     * 获取当前租户 ID
     */
    public static function getTenantId(): ?int
    {
        return self::$tenantId;
    }

    /**
     * 是否启用租户隔离
     */
    public static function shouldApplyTenant(): bool
    {
        return !self::$ignoreTenant && self::$tenantId !== null;
    }

    /**
     * 超管 / 系统：忽略租户隔离
     */
    public static function ignore(): void
    {
        self::$ignoreTenant = true;
    }

    /**
     * 恢复租户隔离（一般不常用）
     */
    public static function restore(): void
    {
        self::$ignoreTenant = false;
    }

    /**
     * 是否正在忽略租户隔离
     */
    public static function isIgnoring(): bool
    {
        return self::$ignoreTenant;
    }
}
```

这个类的设计目标只有三个：

* **无 ORM 依赖**
* **无 Auth 依赖**
* **全局、可预测**

---

### 2️⃣ 登录成功后，注入 Context（非常关键）

```php
use Framework\Tenant\TenantContext;

public function loginSuccess(array $user): void
{
    // 普通用户
    if (empty($user['is_super_admin'])) {
        TenantContext::setTenantId((int)$user['tenant_id']);
        return;
    }

    // 超管
    TenantContext::ignore();
}
```

> 注意：
> **超管不是 tenant_id = null，而是明确 ignore**

这是设计上的一个关键点。

---

### 3️⃣ CLI / Job / Crontab 的默认策略

```php
use Framework\Tenant\TenantContext;

// CLI 场景默认忽略租户
if (PHP_SAPI === 'cli') {
    TenantContext::ignore();
}
```

---

## 四、BaseModel 中的 `scopeTenant` 最终形态

你现在的 `scopeTenant`，建议最终定型为：

```php
use Framework\Tenant\TenantContext;

public function scopeTenant($query): void
{
    // 1. 当前上下文不启用租户隔离
    if (!TenantContext::shouldApplyTenant()) {
        return;
    }

    // 2. 当前模型没有 tenant_id 字段
    if (!in_array('tenant_id', array_keys($this->getFields()))) {
        return;
    }

    // 3. 正常加租户条件
    $query->where(
        $this->getTable() . '.tenant_id',
        TenantContext::getTenantId()
    );
}
```

这一版有几个**非常重要的特性**：

* scope **无 if-else 业务判断**
* 所有“超管 / 普通用户 / CLI”逻辑都在 Context
* ORM 只负责“数据隔离”，不负责“你是谁”

---

## 五、那 ThinkPHP 里“显式跨租户”怎么做？

既然没有 `withoutGlobalScope()`，正确姿势是：

### ✅ 临时关闭租户（代码级）

```php
use Framework\Tenant\TenantContext;

TenantContext::ignore();

$list = User::select();

TenantContext::restore();
```

### ✅ 超管“指定某个租户”视角

```php
TenantContext::restore();
TenantContext::setTenantId(1001);

$list = User::select();
```

**不改任何 Model / Query / Service 代码**。

---

## 六、为什么我说这是“框架级正确解法”？

因为它满足所有硬约束：

| 需求            | 是否满足 |
| ------------- | ---- |
| 自动隔离          | ✅    |
| 超管自动放行        | ✅    |
| CLI / Job 安全  | ✅    |
| 不污染业务代码       | ✅    |
| 不依赖 TP 内部 API | ✅    |
| 可测试           | ✅    |







## 一、为什么 `dump($currentTenant)` 没有执行，看不到调试信息？

你现在用的是：

```php
User::where('id', 1)->update([
    'nickname' => 'hack',
]);
```

### ⚠️ 关键事实（ThinkORM 的设计）

> **`Query::update()` 不会触发模型事件**
>
> 包括：

* `onBeforeUpdate`
* `onAfterUpdate`
* `onBeforeDelete`
* `onAfterDelete`

### 实际执行路径是：

```text
User::where()
  → 返回 think\db\Query
  → 直接执行 SQL
  → 完全绕过 Model 实例
```

所以：

* `onBeforeUpdate()` **根本没有被调用**
* `dump()` 不可能出现
* 这是 ThinkORM 的既定行为，不是你的代码问题

---

## 二、那为什么 SQL 里又自动加了 tenant_id？

因为你已经在 **查询层（scope / baseQuery / builder hook）** 做了这件事：

```sql
UPDATE oa_custom
SET nickname = 'hack'
WHERE id = 1
  AND tenant_id = 1001
```

也就是说：

* **SQL 层租户隔离已经生效**
* 更新 0 行是预期结果
* 这属于“软防护”（通过条件限制）

✔️ 这一层是正确且必须存在的

---

## 三、什么时候 `onBeforeUpdate` 才会执行？

只有 **Model 实例化路径** 才会触发。

### ✅ 正确触发模型事件的写法

```php
$user = User::find(1);
$user->nickname = 'hack';
$user->save();
```

此时执行流程是：

```text
find()
 → hydrate Model
 → save()
 → onBeforeUpdate()   ✅
 → SQL
```

这时候你会看到：

```php
dump($currentTenant); // 会输出
```

并且如果 tenant 不匹配：

```text
Tenant access denied (update)
```

---

## 四、这说明你的多租户设计已经是“分层防护”

你现在实际上有 **两道防线**：

### 第一层（SQL 级别，永远存在）

```php
Query::update()
```

* 自动拼 tenant 条件
* 不会越权更新
* 但不会抛异常
* **静默失败（0 rows affected）**

### 第二层（Model 级别，强校验）

```php
Model::save()
```

* 触发 `onBeforeUpdate`
* 可以抛异常
* 明确告诉你“这是越权行为”

这在架构上是**非常健康的状态**。

---

## 五、那“超限访问防止”应该放在哪里？

你现在可以这样分工：

### ✅ SQL 层（Query）

**职责**：

> “不能更新到不属于你的数据”

手段：

* 自动加 `tenant_id`
* 永远不信任调用者

---

### ✅ Model 层（事件）

**职责**：

> “有人试图越权，必须明确阻断”

手段：

* `onBeforeUpdate`
* `onBeforeDelete`
* 抛异常

---

### ✅ Repository / Service 层（强烈建议）

**职责**：

> “危险操作必须显式授权”

例如：

```php
public function updateById(int $id, array $data)
{
    $model = User::findOrFail($id);

    // 这里一定触发模型事件
    return $model->save($data);
}
```

**原则**：

> ❌ 业务代码禁止直接 `where()->update()`
>
> ✅ 所有写操作必须走 Model / Repository

---

## 六、如果你想“强制禁止 where()->update()”

这是进阶做法，我给你一个**可选方案**。

### 方案：在 BaseModel 中打标

```php
class BaseTpORMModel extends Model
{
    protected static bool $forbidBatchUpdate = true;
}
```

然后在 Repository 里：

```php
if ($model::$forbidBatchUpdate) {
    throw new Exception('Batch update is forbidden in tenant mode');
}
```

这样你就实现了：

> **多租户系统中，批量写是“默认禁止”的**

---

## 七、一句话给你定性结论

> 你现在看到的行为
> **不是 bug，而是 ThinkORM 的“Query vs Model”边界**。

并且：

* SQL 层隔离 ✅
* Model 层防越权 ✅
* 超管 ignore / 指定租户视角 ✅

