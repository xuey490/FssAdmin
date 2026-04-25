-- ============================================
-- 移除菜单相关表的 tenant_id 字段迁移脚本
-- 创建时间: 2026-04-20
-- 说明: 将菜单系统从租户隔离改为全局共享
-- ============================================

-- 1. 移除 sa_system_menu 表的 tenant_id 字段和索引
ALTER TABLE `sa_system_menu` DROP INDEX `idx_tenant_id`;
ALTER TABLE `sa_system_menu` DROP COLUMN `tenant_id`;

-- 2. 移除 sa_system_role_menu 表的 tenant_id 字段和索引
ALTER TABLE `sa_system_role_menu` DROP INDEX `idx_tenant_id`;
ALTER TABLE `sa_system_role_menu` DROP COLUMN `tenant_id`;

-- 3. 移除 sa_system_user_menu 表的 tenant_id 字段和索引
ALTER TABLE `sa_system_user_menu` DROP INDEX `idx_tenant_id`;
ALTER TABLE `sa_system_user_menu` DROP COLUMN `tenant_id`;

-- ============================================
-- 回滚脚本 (如需回滚，请执行以下SQL)
-- ============================================
-- ALTER TABLE `sa_system_menu` ADD COLUMN `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '所属租户ID，0表示系统级公共菜单' AFTER `sort`;
-- ALTER TABLE `sa_system_menu` ADD KEY `idx_tenant_id` (`tenant_id`);
--
-- ALTER TABLE `sa_system_role_menu` ADD COLUMN `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '租户上下文ID' AFTER `menu_id`;
-- ALTER TABLE `sa_system_role_menu` ADD KEY `idx_tenant_id` (`tenant_id`);
--
-- ALTER TABLE `sa_system_user_menu` ADD COLUMN `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `menu_id`;
-- ALTER TABLE `sa_system_user_menu` ADD KEY `idx_tenant_id` (`tenant_id`);
