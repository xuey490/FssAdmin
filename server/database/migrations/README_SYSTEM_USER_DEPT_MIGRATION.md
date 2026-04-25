# System User Dept Table Migration

## Overview
This migration alters the existing `sa_system_user_dept` table to support multi-tenant department isolation. The table stores the association between users and departments within specific tenant contexts.

## Migration File
- **File**: `003_alter_system_user_dept_table.sql`
- **Date**: 2026-01-15
- **Related Requirements**: 2.1, 2.2, 2.5

## Changes Applied

### 1. New Columns
- `created_by` (bigint unsigned, nullable): ID of the user who created this record
- `updated_by` (bigint unsigned, nullable): ID of the user who last updated this record

### 2. Column Type Modifications
- `id`: Changed to `bigint(20) UNSIGNED` for consistency
- `user_id`: Changed to `bigint(20) UNSIGNED NOT NULL`
- `tenant_id`: Changed to `bigint(20) UNSIGNED NOT NULL`
- `dept_id`: Changed to `bigint(20) UNSIGNED NOT NULL`
- `create_time`: Changed to `TIMESTAMP` type
- `update_time`: Changed to `TIMESTAMP` type

### 3. Index Changes
- **Unique Index**: `uk_user_tenant` on (`user_id`, `tenant_id`)
  - Ensures one department per user per tenant
  - Column order changed from (tenant_id, user_id) to (user_id, tenant_id) for better query performance
  
- **Performance Indexes**:
  - `idx_user_id`: Index on `user_id` for user-based queries
  - `idx_tenant_id`: Index on `tenant_id` for tenant-based queries
  - `idx_dept_id`: Index on `dept_id` for department-based queries

### 4. Storage Engine
- Changed from MyISAM to InnoDB for better transaction support and ACID compliance

## Design Decisions

### No Foreign Key Constraints
Foreign key constraints are intentionally NOT added to maintain flexibility for deletion operations. Data consistency will be managed at the application layer.

### Index Strategy
- The unique index on (user_id, tenant_id) prevents duplicate associations
- Additional indexes on individual columns optimize common query patterns:
  - Finding all departments for a user
  - Finding all users in a tenant
  - Finding all users in a department

## Usage

### Apply Migration
```bash
mysql -u username -p database_name < database/migrations/003_alter_system_user_dept_table.sql
```

### Verify Migration
```sql
-- Check table structure
DESCRIBE sa_system_user_dept;

-- Check indexes
SHOW INDEX FROM sa_system_user_dept;

-- Verify storage engine
SHOW TABLE STATUS WHERE Name = 'sa_system_user_dept';
```

## Related Files
- Design Document: `.kiro/specs/multi-tenant-dept-isolation-fix/design.md`
- Requirements: `.kiro/specs/multi-tenant-dept-isolation-fix/bugfix.md`
- Tasks: `.kiro/specs/multi-tenant-dept-isolation-fix/tasks.md`

## Next Steps
After applying this migration:
1. Create the `SysUserDept` model (Task 3.2)
2. Extend the `SysUser` model with dept relationship methods (Task 3.3)
3. Modify `DataScopeTrait` to use dynamic dept_id (Task 3.4)
4. Update service and controller layers (Tasks 3.5-3.6)
5. Run data migration for historical data (Task 3.7)
