-- ============================================================================
-- Test Script: Validate migration logic before execution
-- Description: Dry-run queries to preview migration results
-- ============================================================================

-- Test 1: Preview records that WILL be migrated
-- This shows exactly what will be inserted into sa_system_user_dept
SELECT 
    u.id AS user_id,
    u.username,
    d.tenant_id AS tenant_id,
    u.dept_id AS dept_id,
    d.name AS dept_name,
    'Will be migrated' AS status
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
ORDER BY u.id, d.tenant_id;

-- Test 2: Preview records that will be SKIPPED (orphaned dept_id)
SELECT 
    u.id AS user_id,
    u.username,
    u.dept_id,
    'Orphaned dept_id - department does not exist' AS reason
FROM 
    `sa_system_user` u
LEFT JOIN 
    `sa_system_dept` d ON u.dept_id = d.id
WHERE 
    u.dept_id IS NOT NULL
    AND u.dept_id > 0
    AND u.delete_time IS NULL
    AND d.id IS NULL;

-- Test 3: Preview records that will be SKIPPED (cross-tenant conflict)
SELECT 
    u.id AS user_id,
    u.username,
    u.dept_id,
    d.tenant_id AS dept_tenant_id,
    GROUP_CONCAT(ut.tenant_id) AS user_tenant_ids,
    'Cross-tenant conflict - user does not belong to department tenant' AS reason
FROM 
    `sa_system_user` u
INNER JOIN 
    `sa_system_dept` d ON u.dept_id = d.id
LEFT JOIN 
    `sa_system_user_tenant` ut ON u.id = ut.user_id
WHERE 
    u.dept_id IS NOT NULL
    AND u.dept_id > 0
    AND u.delete_time IS NULL
    AND d.delete_time IS NULL
    AND NOT EXISTS (
        SELECT 1 
        FROM `sa_system_user_tenant` ut2 
        WHERE ut2.user_id = u.id AND ut2.tenant_id = d.tenant_id
    )
GROUP BY u.id, u.username, u.dept_id, d.tenant_id;

-- Test 4: Preview records that will be SKIPPED (no tenant associations)
SELECT 
    u.id AS user_id,
    u.username,
    u.dept_id,
    'No tenant associations - user not in sa_system_user_tenant' AS reason
FROM 
    `sa_system_user` u
WHERE 
    u.dept_id IS NOT NULL
    AND u.dept_id > 0
    AND u.delete_time IS NULL
    AND NOT EXISTS (
        SELECT 1 
        FROM `sa_system_user_tenant` ut 
        WHERE ut.user_id = u.id
    );

-- Test 5: Summary statistics
SELECT 
    'Total users with dept_id' AS metric,
    COUNT(*) AS count
FROM 
    `sa_system_user`
WHERE 
    dept_id IS NOT NULL 
    AND dept_id > 0
    AND delete_time IS NULL

UNION ALL

SELECT 
    'Users that will be migrated' AS metric,
    COUNT(DISTINCT u.id) AS count
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

UNION ALL

SELECT 
    'Records that will be created' AS metric,
    COUNT(*) AS count
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

UNION ALL

SELECT 
    'Users that will be skipped' AS metric,
    (SELECT COUNT(*) FROM `sa_system_user` WHERE dept_id IS NOT NULL AND dept_id > 0 AND delete_time IS NULL) -
    (SELECT COUNT(DISTINCT u.id) FROM `sa_system_user` u
     INNER JOIN `sa_system_dept` d ON u.dept_id = d.id
     INNER JOIN `sa_system_user_tenant` ut ON u.id = ut.user_id AND d.tenant_id = ut.tenant_id
     WHERE u.dept_id IS NOT NULL AND u.dept_id > 0 AND u.delete_time IS NULL AND d.delete_time IS NULL) AS count;

-- ============================================================================
-- Usage Instructions:
-- ============================================================================
-- 1. Run this script BEFORE executing the actual migration
-- 2. Review the results to understand what will be migrated
-- 3. Investigate any unexpected skipped records
-- 4. If results look correct, proceed with 004_migrate_user_dept_data.sql
-- ============================================================================
