# 菜单系统移除租户隔离改造文档

## 一、改造背景

### 1.1 改造原因
菜单系统原本设计为租户隔离模式，每个租户拥有独立的菜单数据。但在实际业务场景中发现：
- 菜单配置属于系统级资源，各租户使用相同的菜单结构
- 租户隔离导致菜单数据冗余，维护成本高
- 权限控制应该通过角色-菜单关联实现，而非租户-菜单隔离

### 1.2 改造目标
将菜单系统从租户隔离模式改为全局共享模式：
- 菜单数据全局共享，所有租户使用同一套菜单配置
- 权限隔离通过角色-菜单关联实现
- 简化数据结构，降低维护成本

## 二、影响范围

### 2.1 数据库表
涉及3张表的结构变更：
1. `sa_system_menu` - 菜单表（移除tenant_id字段和索引）
2. `sa_system_role_menu` - 角色菜单关联表（移除tenant_id字段和索引）
3. `sa_system_user_menu` - 用户菜单关联表（移除tenant_id字段和索引）

### 2.2 代码层面
涉及以下文件的修改：

**Model层（4个文件）：**
- `app/Models/SysMenu.php` - 禁用租户隔离Scope
- `app/Models/SysRoleMenu.php` - 已移除tenant_id（无需修改）
- `app/Models/SysUserMenu.php` - 已移除tenant_id（无需修改）
- `app/Models/SysTenant.php` - 移除menus()关联方法
- `app/Models/SysUser.php` - 移除菜单查询中的tenant_id过滤

**Service层（2个文件）：**
- `app/Services/SysTenantService.php` - 移除删除租户时的菜单检查
- `app/Services/Casbin/CasbinService.php` - 移除权限同步中的tenant_id过滤

**Controller层：**
- 无需修改（tenant_id处理在Service层）

## 三、执行步骤

### 3.1 备份数据
```bash
# 备份数据库
mysqldump -u root -p your_database > backup_before_menu_tenant_removal_$(date +%Y%m%d_%H%M%S).sql
```

### 3.2 执行数据库迁移
```bash
# 执行迁移脚本
mysql -u root -p your_database < database/migrations/remove_tenant_id_from_menu_tables.sql
```

迁移脚本内容：
```sql
-- 1. 移除 sa_system_menu 表的 tenant_id 字段和索引
ALTER TABLE `sa_system_menu` DROP INDEX `idx_tenant_id`;
ALTER TABLE `sa_system_menu` DROP COLUMN `tenant_id`;

-- 2. 移除 sa_system_role_menu 表的 tenant_id 字段和索引
ALTER TABLE `sa_system_role_menu` DROP INDEX `idx_tenant_id`;
ALTER TABLE `sa_system_role_menu` DROP COLUMN `tenant_id`;

-- 3. 移除 sa_system_user_menu 表的 tenant_id 字段和索引
ALTER TABLE `sa_system_user_menu` DROP INDEX `idx_tenant_id`;
ALTER TABLE `sa_system_user_menu` DROP COLUMN `tenant_id`;
```

### 3.3 部署代码
```bash
# 拉取最新代码
git pull origin main

# 清理缓存（重要！）
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 如果使用Redis缓存，也需要清理
redis-cli FLUSHALL
```

### 3.4 验证功能
1. 登录系统，检查菜单是否正常显示
2. 测试菜单的增删改查功能
3. 测试角色菜单分配功能
4. 测试用户菜单分配功能
5. 验证不同租户用户的菜单权限是否正确

## 四、关键代码修改说明

### 4.1 SysMenu模型 - 禁用租户隔离
```php
/**
 * 禁用租户隔离
 * 菜单为系统级全局共享资源，不应用租户隔离
 */
protected static function bootLaBelongsToTenant()
{
    // 覆盖父类的 bootLaBelongsToTenant 方法，不添加租户作用域
    // 菜单表已移除 tenant_id 字段，为全局共享资源
}
```

### 4.2 SysUser模型 - 移除菜单查询中的tenant_id过滤
```php
// 修改前
return SysMenu::where('tenant_id', $tenantId ?? 1)
    ->where('status', SysMenu::STATUS_ENABLED)
    ->pluck('id')->toArray();

// 修改后
return SysMenu::where('status', SysMenu::STATUS_ENABLED)
    ->pluck('id')->toArray();
```

### 4.3 CasbinService - 移除权限同步中的tenant_id过滤
```php
// 修改前
$menuQuery = SysRoleMenu::where('role_id', $roleId);
if ($tenantId > 0) {
    $menuQuery->where('tenant_id', $tenantId);
}
$menuIds = $menuQuery->pluck('menu_id')->toArray();

// 修改后
$menuIds = SysRoleMenu::where('role_id', $roleId)
    ->pluck('menu_id')->toArray();
```

### 4.4 SysTenant模型 - 移除menus()关联
```php
// 已移除 menus() 方法
// 注意：菜单已改为全局共享资源，租户不再拥有独立菜单
// 如需获取租户可访问的菜单，请通过角色-菜单关联查询
```

## 五、回滚方案

如果改造后出现问题，可以按以下步骤回滚：

### 5.1 恢复数据库
```bash
# 恢复备份
mysql -u root -p your_database < backup_before_menu_tenant_removal_YYYYMMDD_HHMMSS.sql
```

### 5.2 回滚代码
```bash
# 回滚到改造前的版本
git checkout <previous_commit_hash>
```

### 5.3 数据库字段恢复（如果只需要恢复字段）
```sql
-- 恢复 sa_system_menu 表的 tenant_id 字段
ALTER TABLE `sa_system_menu` 
ADD COLUMN `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' 
COMMENT '所属租户ID，0表示系统级公共菜单' AFTER `sort`;
ALTER TABLE `sa_system_menu` ADD KEY `idx_tenant_id` (`tenant_id`);

-- 恢复 sa_system_role_menu 表的 tenant_id 字段
ALTER TABLE `sa_system_role_menu` 
ADD COLUMN `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' 
COMMENT '租户上下文ID' AFTER `menu_id`;
ALTER TABLE `sa_system_role_menu` ADD KEY `idx_tenant_id` (`tenant_id`);

-- 恢复 sa_system_user_menu 表的 tenant_id 字段
ALTER TABLE `sa_system_user_menu` 
ADD COLUMN `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' 
COMMENT '租户ID' AFTER `menu_id`;
ALTER TABLE `sa_system_user_menu` ADD KEY `idx_tenant_id` (`tenant_id`);
```

## 六、测试要点

### 6.1 功能测试
- [ ] 菜单列表查询
- [ ] 菜单树查询
- [ ] 菜单详情查询
- [ ] 菜单创建
- [ ] 菜单更新
- [ ] 菜单删除
- [ ] 菜单状态更新
- [ ] 角色菜单分配
- [ ] 用户菜单分配
- [ ] 用户菜单树查询
- [ ] 用户权限列表查询

### 6.2 权限测试
- [ ] 超级管理员可以访问所有菜单
- [ ] 普通管理员只能访问分配的菜单
- [ ] 不同租户的用户权限隔离正确
- [ ] 角色菜单权限生效
- [ ] 用户个人菜单权限生效

### 6.3 数据一致性测试
- [ ] 菜单数据完整性
- [ ] 角色菜单关联数据正确
- [ ] 用户菜单关联数据正确
- [ ] Casbin权限数据同步正确

### 6.4 性能测试
- [ ] 菜单查询性能
- [ ] 权限验证性能
- [ ] 大量用户并发访问

## 七、注意事项

### 7.1 数据迁移注意事项
1. **必须先备份数据**：执行任何数据库变更前，务必完整备份数据库
2. **在测试环境验证**：先在测试环境完整执行一遍流程，确认无误后再在生产环境执行
3. **选择低峰期执行**：建议在业务低峰期执行，减少对用户的影响
4. **准备回滚方案**：确保回滚脚本和备份文件可用
5. **清理所有缓存**：部署后必须清理应用缓存、Redis缓存等

### 7.2 代码部署注意事项
1. **代码和数据库同步**：确保代码部署和数据库迁移同步完成
2. **清理缓存**：部署后必须清理所有缓存（应用缓存、Redis缓存等）
3. **监控日志**：部署后密切关注错误日志和业务日志

### 7.3 业务影响
1. **菜单权限变更**：改造后菜单为全局共享，权限通过角色控制
2. **现有数据保留**：现有的菜单数据和权限关联数据会保留
3. **用户体验无变化**：对最终用户来说，菜单显示和权限控制逻辑不变

## 八、技术细节

### 8.1 Model层变更
**SysMenu.php：**
- 添加 `bootLaBelongsToTenant()` 方法覆盖父类，禁用租户隔离
- 菜单查询不再自动添加 `where tenant_id = ?` 条件

**SysRoleMenu.php 和 SysUserMenu.php：**
- `$fillable` 数组中已移除 `tenant_id` 字段
- `batchInsert()` 等方法已移除 `$tenantId` 参数

**SysUser.php：**
- `getMergedMenuIds()` 方法移除 tenant_id 过滤
- 超级管理员获取菜单时移除 tenant_id 条件

**SysTenant.php：**
- 移除 `menus()` 关联方法

### 8.2 Service层变更
**SysTenantService.php：**
- 删除租户时移除菜单数据检查

**CasbinService.php：**
- `syncRoleMenuPermissions()` 移除 tenant_id 过滤
- `syncUserMenuPermissions()` 移除 tenant_id 过滤

### 8.3 权限控制逻辑
改造后的权限控制流程：
1. 菜单数据全局共享，不区分租户
2. 角色-菜单关联控制角色可访问的菜单
3. 用户-菜单关联控制用户个人的额外菜单权限
4. 最终用户权限 = 角色菜单权限 + 用户个人菜单权限

## 九、常见问题

### Q1: 改造后不同租户会看到相同的菜单吗？
A: 菜单数据是全局共享的，但用户实际能访问的菜单由其角色和个人权限决定。不同租户的用户拥有不同的角色，因此看到的菜单也不同。

### Q2: 如何为不同租户配置不同的菜单？
A: 通过为不同租户的用户分配不同的角色来实现。每个角色可以配置不同的菜单权限。

### Q3: 改造后如何新增租户专属菜单？
A: 菜单为全局共享，不支持租户专属菜单。如果需要租户特定功能，建议通过菜单权限控制实现。

### Q4: 改造失败如何快速恢复？
A: 使用备份的数据库文件恢复，然后回滚代码到改造前的版本。

### Q5: 为什么登录时报错"Column not found: tenant_id"？
A: 这是因为 SysMenu 模型继承自 BaseLaORMModel，该基类使用了 LaBelongsToTenant trait 自动应用租户隔离。解决方法是在 SysMenu 模型中覆盖 `bootLaBelongsToTenant()` 方法，禁用租户隔离。

## 十、相关文档

- 数据库迁移脚本：`database/migrations/remove_tenant_id_from_menu_tables.sql`
- Model层代码：
  - `app/Models/SysMenu.php`
  - `app/Models/SysRoleMenu.php`
  - `app/Models/SysUserMenu.php`
  - `app/Models/SysUser.php`
  - `app/Models/SysTenant.php`
- Service层代码：
  - `app/Services/SysTenantService.php`
  - `app/Services/Casbin/CasbinService.php`

## 十一、更新日志

| 日期 | 版本 | 说明 | 操作人 |
|------|------|------|--------|
| 2026-04-20 | 1.0 | 初始版本，完成菜单系统租户隔离移除改造 | Genie |
| 2026-04-20 | 1.1 | 修复登录时"Column not found: tenant_id"错误，添加SysMenu模型租户隔离禁用 | Genie |
| 2026-04-20 | 1.2 | 完善所有相关代码，移除所有tenant_id引用 | Genie |
