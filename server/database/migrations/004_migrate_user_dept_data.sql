-- ============================================================================
-- Migration: Migrate historical user department data to sa_system_user_dept
-- Description: Populate sa_system_user_dept with existing dept_id from sa_system_user
-- Date: 2026-01-15
-- Related Requirements: 2.1, 2.2, 3.1
-- ============================================================================

-- This migration populates the sa_system_user_dept table with historical data
-- from sa_system_user.dept_id, ensuring proper tenant isolation.

-- Step 1: Insert user-dept-tenant associations for users with valid dept_id
-- Only create associations where:
-- 1. User has a non-null dept_id
-- 2. Department exists in sa_system_dept
-- 3. User belongs to the department's tenant (via sa_system_user_tenant)
-- 4. No duplicate (user_id, tenant_id) exists (handled by unique index)

INSERT INTO `sa_system_user_dept` 
    (`user_id`, `tenant_id`, `dept_id`, `created_by`, `updated_by`, `create_time`, `update_time`)
SELECT DISTINCT
    u.id AS user_id,
    d.tenant_id AS tenant_id,
    u.dept_id AS dept_id,
    0 AS created_by,
    0 AS updated_by,
    NOW() AS create_time,
    NOW() AS update_time
FROM 
    `sa_system_user` u
INNER JOIN 
    `sa_system_dept` d ON u.dept_id = d.id
INNER JOIN 
    `sa_system_user_tenant` ut ON u.id = ut.user_id AND d.tenant_id = ut.tenant_id
WHERE 
    u.dept_id IS NOT NULL
    AND u.dept_id > 0
    AND u.delete_time IS NULL
    AND d.delete_time IS NULL
ON DUPLICATE KEY UPDATE
    dept_id = VALUES(dept_id),
    updated_by = 0,
    update_time = NOW();

-- ============================================================================
-- Migration Statistics and Validation
-- ============================================================================

-- Count total users with dept_id
SELECT 
    COUNT(*) AS total_users_with_dept,
    'Total users with dept_id (non-null and > 0)' AS description
FROM 
    `sa_system_user`
WHERE 
    dept_id IS NOT NULL 
    AND dept_id > 0
    AND delete_time IS NULL;

-- Count successfully migrated records
SELECT 
    COUNT(*) AS migrated_records,
    'Successfully migrated user-dept-tenant associations' AS description
FROM 
    `sa_system_user_dept`;

-- Identify users with dept_id but no migration (edge cases)
SELECT 
    u.id AS user_id,
    u.username,
    u.dept_id,
    d.id AS dept_exists,
    d.tenant_id AS dept_tenant_id,
    ut.tenant_id AS user_tenant_id,
    CASE
        WHEN d.id IS NULL THEN 'Orphaned dept_id (department does not exist)'
        WHEN d.delete_time IS NOT NULL THEN 'Department is deleted'
        WHEN ut.tenant_id IS NULL THEN 'User does not belong to department tenant (cross-tenant conflict)'
        ELSE 'Unknown issue'
    END AS reason
FROM 
    `sa_system_user` u
LEFT JOIN 
    `sa_system_dept` d ON u.dept_id = d.id AND d.delete_time IS NULL
LEFT JOIN 
    `sa_system_user_tenant` ut ON u.id = ut.user_id AND d.tenant_id = ut.tenant_id
WHERE 
    u.dept_id IS NOT NULL
    AND u.dept_id > 0
    AND u.delete_time IS NULL
    AND NOT EXISTS (
        SELECT 1 
        FROM `sa_system_user_dept` sud 
        WHERE sud.user_id = u.id
    );

-- ============================================================================
-- Rollback Instructions
-- ============================================================================
-- To rollback this migration, execute:
-- TRUNCATE TABLE `sa_system_user_dept`;
-- 
-- Note: This will remove ALL records from sa_system_user_dept.
-- The original sa_system_user.dept_id field remains intact as a fallback.
-- ============================================================================

-- ============================================================================
-- Edge Cases Handled:
-- ============================================================================
-- 1. Orphaned dept_id: Users with dept_id pointing to non-existent departments
--    are skipped (INNER JOIN ensures department exists)
--
-- 2. Cross-tenant conflicts: Users with dept_id from a different tenant are
--    skipped (INNER JOIN with sa_system_user_tenant ensures tenant match)
--
-- 3. Missing tenant associations: Users without tenant associations are
--    skipped (INNER JOIN with sa_system_user_tenant)
--
-- 4. Deleted users/departments: Filtered out by delete_time IS NULL checks
--
-- 5. Duplicate prevention: ON DUPLICATE KEY UPDATE handles re-running migration
--
-- 6. Fallback preservation: sa_system_user.dept_id field is NOT modified,
--    ensuring single-tenant mode and historical compatibility
-- ============================================================================
