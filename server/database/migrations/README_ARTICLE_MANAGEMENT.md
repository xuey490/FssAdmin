# 文章管理模块数据库迁移说明

## 概述

本目录包含文章管理模块的数据库迁移文件，用于添加多租户和数据权限支持。

## 迁移文件列表

### 1. 001_add_dept_tenant_to_article.sql
**目的**: 为 `sa_article` 表添加数据权限和多租户支持字段

**变更内容**:
- 添加 `dept_id` 字段（bigint(20)）- 所属部门ID，用于数据权限过滤
- 添加 `tenant_id` 字段（bigint(20)）- 租户ID，用于多租户隔离
- 添加索引 `idx_dept_id` - 提升部门数据查询性能
- 添加索引 `idx_tenant_id` - 提升租户数据查询性能
- 添加索引 `idx_created_by` - 提升创建者数据查询性能
- 为现有数据设置默认值（tenant_id=1, dept_id=1）

**相关需求**: 8.2, 8.3, 7.2, 7.3

### 2. 002_create_article_management_menu.sql
**目的**: 创建文章管理模块的菜单和权限配置

**菜单结构**:
```
文章管理 (article)
└── 文章列表 (article:list)
    ├── 查看文章 (article:read)
    ├── 新增文章 (article:create)
    ├── 编辑文章 (article:update)
    ├── 删除文章 (article:delete)
    └── 更新状态 (article:status)
```

**权限标识**:
- `article:list` - 文章列表查看权限
- `article:read` - 文章详情查看权限
- `article:create` - 文章创建权限
- `article:update` - 文章编辑权限
- `article:delete` - 文章删除权限
- `article:status` - 文章状态更新权限

## 执行顺序

**重要**: 必须按照以下顺序执行迁移文件：

1. 首先执行 `001_add_dept_tenant_to_article.sql` - 修改表结构
2. 然后执行 `002_create_article_management_menu.sql` - 创建菜单

## 执行方法

### 方法一：使用 MySQL 命令行

```bash
# 1. 修改表结构
mysql -u root -p database_name < database/migrations/001_add_dept_tenant_to_article.sql

# 2. 创建菜单
mysql -u root -p database_name < database/migrations/002_create_article_management_menu.sql
```

### 方法二：使用 MySQL 客户端工具

1. 打开 MySQL 客户端工具（如 Navicat、phpMyAdmin、DBeaver 等）
2. 连接到目标数据库
3. 依次打开并执行以下 SQL 文件：
   - `001_add_dept_tenant_to_article.sql`
   - `002_create_article_management_menu.sql`

### 方法三：直接在数据库中执行

1. 登录 MySQL：
   ```bash
   mysql -u root -p
   ```

2. 选择数据库：
   ```sql
   USE your_database_name;
   ```

3. 复制并执行 SQL 文件内容

## 验证迁移

### 验证表结构变更

```sql
-- 查看 sa_article 表结构
DESC sa_article;

-- 验证新增字段
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'sa_article' 
AND COLUMN_NAME IN ('dept_id', 'tenant_id');

-- 验证索引
SHOW INDEX FROM sa_article 
WHERE Key_name IN ('idx_dept_id', 'idx_tenant_id', 'idx_created_by');
```

### 验证菜单创建

```sql
-- 查看文章管理菜单
SELECT id, parent_id, name, path, permission, type, sort 
FROM sa_system_menu 
WHERE name LIKE '%文章%' 
ORDER BY parent_id, sort;
```

### 验证数据更新

```sql
-- 检查现有文章数据是否已设置默认值
SELECT id, title, dept_id, tenant_id, created_by 
FROM sa_article 
LIMIT 10;
```

## 回滚方案

如果需要回滚迁移，请执行以下 SQL：

### 回滚表结构变更

```sql
-- 删除索引
ALTER TABLE `sa_article` 
DROP INDEX `idx_dept_id`,
DROP INDEX `idx_tenant_id`,
DROP INDEX `idx_created_by`;

-- 删除字段
ALTER TABLE `sa_article` 
DROP COLUMN `dept_id`,
DROP COLUMN `tenant_id`;
```

### 回滚菜单创建

```sql
-- 删除文章管理相关菜单（注意：这会删除所有相关的子菜单和权限）
DELETE FROM sa_system_menu 
WHERE name = '文章管理' 
OR parent_id IN (
    SELECT id FROM (
        SELECT id FROM sa_system_menu WHERE name = '文章管理'
    ) AS temp
);
```

## 注意事项

1. **备份数据库**: 执行迁移前，请务必备份数据库
2. **测试环境验证**: 建议先在测试环境执行并验证，确认无误后再在生产环境执行
3. **权限检查**: 确保执行 SQL 的数据库用户具有 ALTER TABLE 和 INSERT 权限
4. **数据一致性**: 迁移会为现有文章数据设置默认的 tenant_id=1 和 dept_id=1，请根据实际情况调整
5. **菜单图标**: 菜单中使用的图标（如 'article'、'list'）需要确保前端项目中存在对应的图标资源

## 后续步骤

完成数据库迁移后，需要：

1. 实现后端代码（Model、DAO、Service、Controller）
2. 实现前端页面（列表页、表单组件）
3. 配置前端路由
4. 测试数据权限过滤功能
5. 测试多租户隔离功能

## 相关文档

- 需求文档: `.kiro/specs/article-management/requirements.md`
- 设计文档: `.kiro/specs/article-management/design.md`
- 任务列表: `.kiro/specs/article-management/tasks.md`

## 技术支持

如有问题，请参考设计文档或联系开发团队。
