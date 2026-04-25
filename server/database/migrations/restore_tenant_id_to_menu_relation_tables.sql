-- ============================================================
-- 恢复菜单关联表的 tenant_id 字段
-- 
-- 架构修正说明：
-- 1. sa_system_menu: 系统级共享资源，不需要 tenant_id（保持当前状态）
-- 2. sa_system_role_menu: 需要 tenant_id（同一角色在不同租户下可能分配不同菜单）
-- 3. sa_system_user_menu: 需要 tenant_id（同一用户在不同租户下可能有不同个人菜单）
-- 
-- 执行时间: 2026-03-12
-- ============================================================

-- ============================================================
-- 1. 恢复 sa_system_role_menu 表的 tenant_id 字段
-- ============================================================

-- 添加 tenant_id 字段（如果不存在）
ALTER TABLE `sa_system_role_menu` 
ADD COLUMN IF NOT EXISTS `tenant_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户ID' AFTER `role_id`;

-- 添加索引（如果不存在）
ALTER TABLE `sa_system_role_menu` 
ADD INDEX IF NOT EXISTS `idx_tenant_role` (`tenant_id`, `role_id`);

ALTER TABLE `sa_system_role_menu` 
ADD INDEX IF NOT EXISTS `idx_tenant_menu` (`tenant_id`, `menu_id`);

-- 更新注释
ALTER TABLE `sa_system_role_menu` 
COMMENT = '角色菜单关联表（支持多租户：同一角色在不同租户下可分配不同菜单）';

-- ============================================================
-- 2. 恢复 sa_system_user_menu 表的 tenant_id 字段
-- ============================================================

-- 添加 tenant_id 字段（如果不存在）
ALTER TABLE `sa_system_user_menu` 
ADD COLUMN IF NOT EXISTS `tenant_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户ID' AFTER `user_id`;

-- 添加索引（如果不存在）
ALTER TABLE `sa_system_user_menu` 
ADD INDEX IF NOT EXISTS `idx_tenant_user` (`tenant_id`, `user_id`);

ALTER TABLE `sa_system_user_menu` 
ADD INDEX IF NOT EXISTS `idx_tenant_menu` (`tenant_id`, `menu_id`);

-- 更新注释
ALTER TABLE `sa_system_user_menu` 
COMMENT = '用户菜单关联表（支持多租户：同一用户在不同租户下可有不同个人菜单）';

-- ============================================================
-- 3. 数据迁移说明
-- ============================================================

-- 注意：如果表中已有数据，需要根据实际业务逻辑更新 tenant_id 值
-- 示例：将现有数据关联到默认租户（tenant_id = 1）
-- UPDATE `sa_system_role_menu` SET `tenant_id` = 1 WHERE `tenant_id` = 0;
-- UPDATE `sa_system_user_menu` SET `tenant_id` = 1 WHERE `tenant_id` = 0;

-- ============================================================
-- 执行完成
-- ============================================================
