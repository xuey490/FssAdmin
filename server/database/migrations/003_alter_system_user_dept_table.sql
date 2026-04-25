-- ============================================================================
-- Migration: Alter sa_system_user_dept table for multi-tenant dept isolation
-- Description: Add missing columns and indexes to support tenant-specific user-dept associations
-- Date: 2026-01-15
-- Related Requirements: 2.1, 2.2, 2.5
-- ============================================================================

-- Step 1: Add created_by and updated_by columns
ALTER TABLE `sa_system_user_dept` 
ADD COLUMN `created_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '创建人ID' AFTER `dept_id`,
ADD COLUMN `updated_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT '更新人ID' AFTER `created_by`;

-- Step 2: Modify column types to match system standards (bigint unsigned)
ALTER TABLE `sa_system_user_dept`
MODIFY COLUMN `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
MODIFY COLUMN `user_id` bigint(20) UNSIGNED NOT NULL COMMENT '用户ID',
MODIFY COLUMN `tenant_id` bigint(20) UNSIGNED NOT NULL COMMENT '租户ID',
MODIFY COLUMN `dept_id` bigint(20) UNSIGNED NOT NULL COMMENT '部门ID';

-- Step 3: Modify timestamp columns to use TIMESTAMP type
ALTER TABLE `sa_system_user_dept`
MODIFY COLUMN `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
MODIFY COLUMN `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间';

-- Step 4: Drop existing unique key and recreate with correct column order (user_id, tenant_id)
ALTER TABLE `sa_system_user_dept`
DROP INDEX `uk_user_tenant`;

ALTER TABLE `sa_system_user_dept`
ADD UNIQUE INDEX `uk_user_tenant` (`user_id`, `tenant_id`) USING BTREE COMMENT '用户租户唯一索引';

-- Step 5: Add performance indexes
ALTER TABLE `sa_system_user_dept`
ADD INDEX `idx_user_id` (`user_id`) USING BTREE COMMENT '用户ID索引',
ADD INDEX `idx_tenant_id` (`tenant_id`) USING BTREE COMMENT '租户ID索引',
ADD INDEX `idx_dept_id` (`dept_id`) USING BTREE COMMENT '部门ID索引';

-- Step 6: Change storage engine to InnoDB for better transaction support
ALTER TABLE `sa_system_user_dept` ENGINE=InnoDB;

-- ============================================================================
-- Migration completed successfully
-- Notes:
-- - No foreign key constraints added to maintain flexibility for deletion operations
-- - Unique index ensures one dept per user per tenant
-- - Performance indexes added for common query patterns
-- - Storage engine changed to InnoDB for ACID compliance
-- ============================================================================
