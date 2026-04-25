-- ============================================================================
-- Migration: Add dept_id and tenant_id to sa_article table
-- Description: Add data scope and multi-tenant support fields to article table
-- Date: 2026-01-10
-- Related Requirements: 8.2, 8.3, 7.2, 7.3
-- ============================================================================

-- Step 1: Add dept_id and tenant_id columns
ALTER TABLE `sa_article` 
ADD COLUMN `dept_id` bigint(20) DEFAULT NULL COMMENT '所属部门ID' AFTER `author`,
ADD COLUMN `tenant_id` bigint(20) NOT NULL DEFAULT 1 COMMENT '租户ID' AFTER `is_hot`;

-- Step 2: Add indexes for data scope filtering
ALTER TABLE `sa_article`
ADD INDEX `idx_dept_id` (`dept_id`) USING BTREE,
ADD INDEX `idx_tenant_id` (`tenant_id`) USING BTREE,
ADD INDEX `idx_created_by` (`created_by`) USING BTREE;

-- Step 3: Update existing data with default values
-- Set default tenant_id to 1 and dept_id to 1 for existing records
UPDATE `sa_article` 
SET `tenant_id` = 1, `dept_id` = 1 
WHERE `tenant_id` = 0 OR `dept_id` IS NULL;

-- ============================================================================
-- Migration completed successfully
-- ============================================================================
