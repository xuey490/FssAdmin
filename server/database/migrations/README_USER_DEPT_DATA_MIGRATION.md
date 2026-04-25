# User Department Data Migration

## Overview

This migration script (`004_migrate_user_dept_data.sql`) populates the `sa_system_user_dept` table with historical user department associations from the `sa_system_user.dept_id` field, ensuring proper multi-tenant isolation.

## Purpose

In the multi-tenant system, users can belong to multiple tenants and should have independent department associations in each tenant. The original `sa_system_user.dept_id` field is a single fixed value that doesn't support this requirement. This migration transfers existing department associations to the new `sa_system_user_dept` intermediate table while maintaining tenant isolation.

## What This Migration Does

1. **Queries all users** with non-null `dept_id` from `sa_system_user`
2. **Validates department existence** in `sa_system_dept`
3. **Checks tenant associations** from `sa_system_user_tenant`
4. **Creates records** in `sa_system_user_dept` only when:
   - User has a valid dept_id (not null and > 0)
   - Department exists and is not deleted
   - User belongs to the department's tenant
5. **Handles edge cases** gracefully (see below)

## Edge Cases Handled

### 1. Orphaned dept_id
**Scenario**: User's dept_id points to a non-existent department  
**Handling**: Skipped (INNER JOIN ensures department exists)  
**Example**: User has dept_id=999, but no department with id=999 exists

### 2. Cross-tenant Conflicts
**Scenario**: User's dept_id points to a department in a different tenant  
**Handling**: Skipped (INNER JOIN with sa_system_user_tenant ensures tenant match)  
**Example**: User belongs to tenant_id=1, but dept_id points to a department in tenant_id=2

### 3. Missing Tenant Associations
**Scenario**: User has dept_id but no tenant associations in sa_system_user_tenant  
**Handling**: Skipped (INNER JOIN with sa_system_user_tenant)  
**Example**: User has dept_id=5 but no records in sa_system_user_tenant

### 4. Deleted Users/Departments
**Scenario**: User or department has been soft-deleted (delete_time IS NOT NULL)  
**Handling**: Filtered out by delete_time IS NULL checks  
**Example**: User has dept_id=5, but the department was deleted

### 5. Duplicate Prevention
**Scenario**: Migration is run multiple times  
**Handling**: ON DUPLICATE KEY UPDATE ensures idempotency  
**Example**: Re-running the migration updates existing records instead of failing

### 6. Fallback Preservation
**Scenario**: Single-tenant users or users without sa_system_user_dept records  
**Handling**: sa_system_user.dept_id field remains intact as fallback  
**Example**: System continues to work for users not migrated

## Migration Statistics

After running the migration, the script outputs three result sets:

1. **Total users with dept_id**: Count of users with non-null dept_id
2. **Migrated records**: Count of successfully created sa_system_user_dept records
3. **Edge cases**: List of users with dept_id but no migration, with reasons

## How to Run

```bash
# Execute the migration script
mysql -u username -p database_name < database/migrations/004_migrate_user_dept_data.sql

# Or using PHP/Laravel migration runner (if applicable)
php artisan migrate --path=database/migrations/004_migrate_user_dept_data.sql
```

## Rollback

To rollback this migration:

```sql
TRUNCATE TABLE `sa_system_user_dept`;
```

**Note**: This removes ALL records from sa_system_user_dept. The original `sa_system_user.dept_id` field remains intact, so the system will fall back to using it.

## Validation Queries

### Check migration success rate
```sql
SELECT 
    (SELECT COUNT(*) FROM sa_system_user WHERE dept_id IS NOT NULL AND dept_id > 0 AND delete_time IS NULL) AS total_users,
    (SELECT COUNT(*) FROM sa_system_user_dept) AS migrated_records,
    ROUND((SELECT COUNT(*) FROM sa_system_user_dept) * 100.0 / 
          (SELECT COUNT(*) FROM sa_system_user WHERE dept_id IS NOT NULL AND dept_id > 0 AND delete_time IS NULL), 2) AS success_rate_percent;
```

### Verify tenant isolation
```sql
-- Check that all migrated records have matching tenant associations
SELECT 
    sud.user_id,
    sud.tenant_id,
    sud.dept_id,
    d.tenant_id AS dept_tenant_id,
    ut.tenant_id AS user_tenant_id
FROM 
    sa_system_user_dept sud
INNER JOIN 
    sa_system_dept d ON sud.dept_id = d.id
INNER JOIN 
    sa_system_user_tenant ut ON sud.user_id = ut.user_id AND sud.tenant_id = ut.tenant_id
WHERE 
    sud.tenant_id != d.tenant_id OR sud.tenant_id != ut.tenant_id;
-- Should return 0 rows (no mismatches)
```

### Find users needing manual review
```sql
-- Users with dept_id but no migration (edge cases)
SELECT 
    u.id,
    u.username,
    u.dept_id,
    CASE
        WHEN d.id IS NULL THEN 'Orphaned dept_id'
        WHEN d.delete_time IS NOT NULL THEN 'Department deleted'
        WHEN ut.tenant_id IS NULL THEN 'Cross-tenant conflict'
        ELSE 'Unknown'
    END AS issue
FROM 
    sa_system_user u
LEFT JOIN 
    sa_system_dept d ON u.dept_id = d.id
LEFT JOIN 
    sa_system_user_tenant ut ON u.id = ut.user_id AND d.tenant_id = ut.tenant_id
WHERE 
    u.dept_id IS NOT NULL
    AND u.dept_id > 0
    AND u.delete_time IS NULL
    AND NOT EXISTS (SELECT 1 FROM sa_system_user_dept WHERE user_id = u.id);
```

## Related Requirements

- **2.1**: Users in multiple tenants get tenant-specific department associations
- **2.2**: Department data is isolated by tenant
- **3.1**: sa_system_user.dept_id preserved as fallback for single-tenant mode

## Related Files

- `database/migrations/003_alter_system_user_dept_table.sql` - Table structure migration
- `app/Models/SysUserDept.php` - Model for sa_system_user_dept table
- `app/Models/SysUser.php` - Extended with getCurrentDeptId() method
- `app/Traits/DataScopeTrait.php` - Modified to use dynamic dept_id

## Notes

1. **Idempotent**: Safe to run multiple times (uses ON DUPLICATE KEY UPDATE)
2. **Non-destructive**: Does not modify sa_system_user.dept_id field
3. **Tenant-aware**: Only creates associations where user belongs to department's tenant
4. **Performance**: Uses INNER JOINs for efficient filtering
5. **Audit trail**: Sets created_by and updated_by to 0 (system migration)

## Troubleshooting

### Issue: Migration creates fewer records than expected
**Cause**: Edge cases (orphaned dept_id, cross-tenant conflicts, etc.)  
**Solution**: Run the edge case query to identify specific issues

### Issue: Users can't see their department after migration
**Cause**: User doesn't belong to the department's tenant  
**Solution**: Either add user to the department's tenant or assign a different department

### Issue: Migration fails with duplicate key error
**Cause**: Unique index violation (user_id, tenant_id already exists)  
**Solution**: This is handled by ON DUPLICATE KEY UPDATE, but if it fails, check for data corruption

## Testing Recommendations

1. **Backup database** before running migration
2. **Run on staging** environment first
3. **Verify statistics** output matches expectations
4. **Check edge cases** query for any unexpected issues
5. **Test user login** in different tenants to verify department associations
6. **Validate data_scope** permissions work correctly with new associations
