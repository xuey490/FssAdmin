# Migration Execution Guide - User Department Data Migration

## Overview

This guide provides step-by-step instructions for executing the user department data migration (`004_migrate_user_dept_data.sql`). This migration is part of the multi-tenant department isolation fix.

## Prerequisites

1. **Backup Database**: Always backup your database before running migrations
   ```bash
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Verify Table Structure**: Ensure `sa_system_user_dept` table exists with correct structure
   ```sql
   SHOW CREATE TABLE sa_system_user_dept;
   ```
   
   Expected columns: id, user_id, tenant_id, dept_id, created_by, updated_by, create_time, update_time
   Expected indexes: uk_user_tenant (unique), idx_user_id, idx_tenant_id, idx_dept_id

3. **Check Current State**: Verify current data state
   ```sql
   -- Check if sa_system_user_dept is empty (expected before first migration)
   SELECT COUNT(*) FROM sa_system_user_dept;
   
   -- Check how many users have dept_id
   SELECT COUNT(*) FROM sa_system_user WHERE dept_id IS NOT NULL AND dept_id > 0 AND delete_time IS NULL;
   ```

## Execution Steps

### Step 1: Dry Run (Validation)

Run the validation script to preview what will be migrated:

```bash
mysql -u username -p database_name < database/migrations/TEST_MIGRATION_VALIDATION.sql
```

Review the output:
- **Test 1**: Records that WILL be migrated (should match your expectations)
- **Test 2**: Orphaned dept_id (users with invalid department references)
- **Test 3**: Cross-tenant conflicts (users with dept_id from different tenant)
- **Test 4**: No tenant associations (users without tenant records)
- **Test 5**: Summary statistics (overall migration impact)

**Action**: If any unexpected records appear in Tests 2-4, investigate and resolve before proceeding.

### Step 2: Execute Migration

Once validation looks good, execute the actual migration:

```bash
mysql -u username -p database_name < database/migrations/004_migrate_user_dept_data.sql
```

The script will:
1. Insert records into `sa_system_user_dept`
2. Display statistics (total users, migrated records, edge cases)
3. Show any users that were skipped with reasons

### Step 3: Verify Results

After migration, verify the results:

```sql
-- Check migration success rate
SELECT 
    (SELECT COUNT(*) FROM sa_system_user WHERE dept_id IS NOT NULL AND dept_id > 0 AND delete_time IS NULL) AS total_users,
    (SELECT COUNT(*) FROM sa_system_user_dept) AS migrated_records,
    ROUND((SELECT COUNT(*) FROM sa_system_user_dept) * 100.0 / 
          (SELECT COUNT(*) FROM sa_system_user WHERE dept_id IS NOT NULL AND dept_id > 0 AND delete_time IS NULL), 2) AS success_rate_percent;

-- Verify tenant isolation (should return 0 rows)
SELECT 
    sud.user_id,
    sud.tenant_id,
    sud.dept_id,
    d.tenant_id AS dept_tenant_id
FROM 
    sa_system_user_dept sud
INNER JOIN 
    sa_system_dept d ON sud.dept_id = d.id
WHERE 
    sud.tenant_id != d.tenant_id;

-- Check for any duplicate (user_id, tenant_id) - should return 0 rows
SELECT 
    user_id, 
    tenant_id, 
    COUNT(*) as count
FROM 
    sa_system_user_dept
GROUP BY 
    user_id, tenant_id
HAVING 
    COUNT(*) > 1;
```

### Step 4: Test Application

After migration, test the application:

1. **Login as multi-tenant user**: Verify department shows correctly in each tenant
2. **Switch tenants**: Verify department changes when switching tenants
3. **Test data_scope permissions**: Verify "本部门数据" permission filters correctly
4. **Test department dropdown**: Verify only current tenant's departments appear
5. **Create/update user**: Verify department association syncs to sa_system_user_dept

## Rollback Procedure

If issues are found, rollback the migration:

```sql
-- Rollback: Remove all migrated data
TRUNCATE TABLE sa_system_user_dept;

-- Verify rollback
SELECT COUNT(*) FROM sa_system_user_dept;  -- Should return 0
```

**Note**: The original `sa_system_user.dept_id` field is NOT modified by the migration, so the system will automatically fall back to using it after rollback.

## Common Issues and Solutions

### Issue 1: Low Migration Success Rate

**Symptom**: Fewer records migrated than expected  
**Cause**: Edge cases (orphaned dept_id, cross-tenant conflicts, etc.)  
**Solution**: 
1. Run TEST_MIGRATION_VALIDATION.sql to identify specific issues
2. Fix data issues (assign correct departments, add tenant associations)
3. Re-run migration (idempotent - safe to run multiple times)

### Issue 2: Duplicate Key Error

**Symptom**: Error 1062 - Duplicate entry for key 'uk_user_tenant'  
**Cause**: Unique index violation (should be handled by ON DUPLICATE KEY UPDATE)  
**Solution**: 
1. Check for data corruption in sa_system_user_dept
2. If needed, manually remove duplicates before re-running

### Issue 3: Users Can't See Department After Migration

**Symptom**: User's department appears empty after login  
**Cause**: User doesn't belong to the department's tenant  
**Solution**: 
1. Check user's tenant associations in sa_system_user_tenant
2. Either add user to department's tenant OR assign different department
3. Manually insert record into sa_system_user_dept if needed

### Issue 4: Cross-Tenant Data Visible

**Symptom**: User sees data from other tenants  
**Cause**: TenantContext not properly set or DataScopeTrait not using getCurrentDeptId()  
**Solution**: 
1. Verify TenantContext::getTenantId() returns correct tenant
2. Verify DataScopeTrait uses getCurrentDeptId() method
3. Check application logs for errors

## Manual Data Fixes

If you need to manually fix specific users:

```sql
-- Add missing user-dept-tenant association
INSERT INTO sa_system_user_dept (user_id, tenant_id, dept_id, created_by, updated_by, create_time, update_time)
VALUES (100, 1, 5, 0, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE dept_id = 5, updated_by = 0, update_time = NOW();

-- Update existing association
UPDATE sa_system_user_dept 
SET dept_id = 10, updated_by = 0, update_time = NOW()
WHERE user_id = 100 AND tenant_id = 1;

-- Remove incorrect association
DELETE FROM sa_system_user_dept 
WHERE user_id = 100 AND tenant_id = 1;
```

## Post-Migration Monitoring

After migration, monitor for:

1. **Application errors**: Check logs for dept_id related errors
2. **Permission issues**: Users reporting incorrect data visibility
3. **Performance**: Query performance on sa_system_user_dept table
4. **Data consistency**: Regular checks for orphaned records

## Related Documentation

- `README_USER_DEPT_DATA_MIGRATION.md` - Detailed migration documentation
- `004_migrate_user_dept_data.sql` - Migration script
- `TEST_MIGRATION_VALIDATION.sql` - Validation script
- `.kiro/specs/multi-tenant-dept-isolation-fix/design.md` - Technical design
- `.kiro/specs/multi-tenant-dept-isolation-fix/bugfix.md` - Bug requirements

## Support

If you encounter issues not covered in this guide:

1. Review the edge cases section in `004_migrate_user_dept_data.sql`
2. Check the validation queries in `README_USER_DEPT_DATA_MIGRATION.md`
3. Consult the technical design document for expected behavior
4. Contact the development team with specific error messages and data samples

## Checklist

Before marking migration complete:

- [ ] Database backup created
- [ ] Table structure verified (sa_system_user_dept exists with correct schema)
- [ ] Validation script executed (TEST_MIGRATION_VALIDATION.sql)
- [ ] Validation results reviewed (no unexpected edge cases)
- [ ] Migration script executed (004_migrate_user_dept_data.sql)
- [ ] Migration statistics reviewed (success rate acceptable)
- [ ] Verification queries executed (no data integrity issues)
- [ ] Application tested (login, tenant switch, permissions work)
- [ ] Rollback procedure tested (optional but recommended on staging)
- [ ] Documentation updated (if any custom fixes applied)
- [ ] Team notified (migration complete, any known issues)
