# 恢复菜单关联表 tenant_id 字段 - 架构修正说明

## 一、为什么需要恢复 tenant_id

### 错误的架构理解（之前）
之前认为：
- `sa_system_menu` 是系统级共享资源，不需要 tenant_id ✓ **正确**
- `sa_system_role_menu` 不需要 tenant_id ✗ **错误**
- `sa_system_user_menu` 不需要 tenant_id ✗ **错误**

### 正确的多租户架构理解

#### 1. 菜单表（sa_system_menu）
- **性质**：系统级共享资源
- **是否需要 tenant_id**：❌ 不需要
- **原因**：菜单定义是全局的，所有租户共享同一套菜单结构

#### 2. 角色菜单关联表（sa_system_role_menu）
- **性质**：租户级配置数据
- **是否需要 tenant_id**：✅ 需要
- **原因**：
  - 同一个角色（如"管理员"）在不同租户下可能分配不同的菜单权限
  - 租户A的"管理员"可能有100个菜单权限
  - 租户B的"管理员"可能只有50个菜单权限
  - 必须通过 tenant_id 区分不同租户的角色菜单配置

#### 3. 用户菜单关联表（sa_system_user_menu）
- **性质**：租户级个人配置数据
- **是否需要 tenant_id**：✅ 需要
- **原因**：
  - 同一个用户在不同租户下可能有不同的个人菜单权限
  - 用户在租户A可能有额外的个人菜单
  - 用户在租户B可能有不同的个人菜单
  - 必须通过 tenant_id 区分不同租户的用户个人菜单

## 二、架构修正内容

### 1. 数据库层修改
执行 SQL 脚本：`restore_tenant_id_to_menu_relation_tables.sql`

**修改内容**：
- 恢复 `sa_system_role_menu.tenant_id` 字段
- 恢复 `sa_system_user_menu.tenant_id` 字段
- 添加相关索引：`idx_tenant_role`、`idx_tenant_menu`、`idx_tenant_user`

### 2. Model 层修改

#### SysRoleMenu 模型（app/Models/SysRoleMenu.php）
- ✅ 在 `$fillable` 中添加 `tenant_id`
- ✅ 在 `$casts` 中添加 `tenant_id => 'integer'`
- ✅ `batchInsert()` 方法添加 `$tenantId` 参数
- ✅ `syncRoleMenus()` 方法添加 `$tenantId` 参数
- ✅ `deleteByRoleId()` 方法添加 `$tenantId` 参数（可选）
- ✅ `getMenuIdsByRoleId()` 方法添加 `$tenantId` 参数（可选）
- ✅ `getMenuIdsByRoleIds()` 方法添加 `$tenantId` 参数（可选）

#### SysUserMenu 模型（app/Models/SysUserMenu.php）
- ✅ 在 `$fillable` 中添加 `tenant_id`
- ✅ 在 `$casts` 中添加 `tenant_id => 'integer'`
- ✅ `batchInsert()` 方法添加 `$tenantId` 参数
- ✅ `syncUserMenus()` 方法添加 `$tenantId` 参数
- ✅ `deleteByUserId()` 方法添加 `$tenantId` 参数（可选）
- ✅ `getMenuIdsByUserId()` 方法添加 `$tenantId` 参数（可选）

### 3. Service 层修改

#### SysRoleService（app/Services/SysRoleService.php）
- ✅ `create()` 方法：分配菜单时传递 `tenant_id`
- ✅ `update()` 方法：更新菜单时传递 `tenant_id`
- ✅ `assignMenus()` 方法：分配菜单时传递 `tenant_id`

#### SysUserService（app/Services/SysUserService.php）
- ✅ `create()` 方法：分配个人菜单时传递 `tenant_id`
- ✅ `update()` 方法：更新个人菜单时传递 `tenant_id`
- ✅ `saveUserMenus()` 方法：保存用户菜单时传递 `tenant_id`
- ✅ `getUserMenuIds()` 方法：获取用户菜单时传递 `tenant_id`
- ✅ `getDetail()` 方法：获取用户详情时按 `tenant_id` 过滤菜单
- ✅ `delete()` 方法：删除用户时删除所有租户的菜单关联

#### CasbinService（app/Services/Casbin/CasbinService.php）
- ✅ `syncRoleMenuPermissions()` 方法：恢复 `tenant_id` 过滤
- ✅ `syncUserMenuPermissions()` 方法：恢复 `tenant_id` 过滤

### 4. SysUser 模型修改（app/Models/SysUser.php）
- ✅ `getMergedMenuIds()` 方法：恢复 `tenant_id` 过滤
  - 角色菜单按 `tenant_id` 过滤
  - 用户个人菜单按 `tenant_id` 过滤

## 三、执行步骤

### 步骤 1：备份数据库
```bash
# 备份当前数据库
mysqldump -u root -p your_database > backup_before_restore_tenant_id.sql
```

### 步骤 2：执行数据库迁移
```bash
# 执行 SQL 脚本
mysql -u root -p your_database < database/migrations/restore_tenant_id_to_menu_relation_tables.sql
```

### 步骤 3：数据迁移（如果需要）
如果表中已有数据，需要根据实际业务逻辑更新 `tenant_id` 值：

```sql
-- 示例：将现有数据关联到默认租户（tenant_id = 1）
UPDATE `sa_system_role_menu` SET `tenant_id` = 1 WHERE `tenant_id` = 0;
UPDATE `sa_system_user_menu` SET `tenant_id` = 1 WHERE `tenant_id` = 0;

-- 或者根据实际业务逻辑，从其他表关联获取正确的 tenant_id
-- 例如：从用户-租户关联表获取用户的默认租户
UPDATE `sa_system_user_menu` um
INNER JOIN `sa_system_user_tenant` ut ON um.user_id = ut.user_id AND ut.is_default = 1
SET um.tenant_id = ut.tenant_id
WHERE um.tenant_id = 0;
```

### 步骤 4：验证代码修改
所有代码修改已完成，无需手动修改。

### 步骤 5：清除缓存
```bash
# 清除应用缓存
php artisan cache:clear

# 清除 Redis 缓存（如果使用）
redis-cli FLUSHDB
```

## 四、测试要点

### 1. 角色菜单分配测试
- [ ] 在租户A创建角色，分配菜单，验证 `sa_system_role_menu` 表中 `tenant_id` 正确
- [ ] 在租户B创建同名角色，分配不同菜单，验证两个租户的菜单配置互不影响
- [ ] 切换租户后，验证角色菜单权限正确隔离

### 2. 用户个人菜单测试
- [ ] 在租户A为用户分配个人菜单，验证 `sa_system_user_menu` 表中 `tenant_id` 正确
- [ ] 在租户B为同一用户分配不同个人菜单，验证两个租户的配置互不影响
- [ ] 切换租户后，验证用户个人菜单正确隔离

### 3. 权限验证测试
- [ ] 用户在租户A登录，验证菜单树和权限列表正确
- [ ] 用户在租户B登录，验证菜单树和权限列表正确
- [ ] 验证 Casbin 权限策略按租户正确同步

### 4. 数据删除测试
- [ ] 删除角色时，验证只删除当前租户的角色菜单关联
- [ ] 删除用户时，验证删除所有租户的用户菜单关联
- [ ] 验证删除操作不影响其他租户的数据

## 五、注意事项

### 1. 数据一致性
- 执行迁移前务必备份数据库
- 确保现有数据的 `tenant_id` 正确更新
- 验证数据迁移后的完整性

### 2. 缓存清理
- 执行迁移后必须清除所有相关缓存
- 包括应用缓存、Redis 缓存、用户权限缓存

### 3. 多租户隔离
- 确保所有涉及菜单关联的查询都带上 `tenant_id` 过滤
- 验证租户间数据完全隔离
- 测试租户切换场景

### 4. 向后兼容
- Model 方法的 `$tenantId` 参数设置为可选（默认值 0）
- 保持 API 接口的向后兼容性
- 逐步迁移现有调用代码

## 六、回滚方案

如果需要回滚，执行以下步骤：

### 1. 恢复数据库
```bash
mysql -u root -p your_database < backup_before_restore_tenant_id.sql
```

### 2. 回滚代码
```bash
git revert <commit_hash>
```

### 3. 清除缓存
```bash
php artisan cache:clear
redis-cli FLUSHDB
```

## 七、总结

本次架构修正的核心理念：

> **菜单定义是全局共享的，但菜单权限配置是租户隔离的**

- ✅ `sa_system_menu`：全局共享，不需要 tenant_id
- ✅ `sa_system_role_menu`：租户隔离，需要 tenant_id
- ✅ `sa_system_user_menu`：租户隔离，需要 tenant_id

这样的设计既保证了菜单定义的统一性，又实现了权限配置的租户隔离，是正确的多租户架构实现。

---

**执行日期**：2026-03-12  
**执行人**：Kiro AI Assistant  
**版本**：v1.0
