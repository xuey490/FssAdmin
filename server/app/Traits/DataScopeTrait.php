<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\SysDept as Dept;
use App\Models\SysRoleDept as RoleDept;

trait DataScopeTrait
{
    /**
     * 模型全局挂载数据权限作用域
     */
    protected static function booted(): void
    {
        static::addGlobalScope('data_scope', function (Builder $query) {
            static::buildDataScopeCondition($query);
        });
    }

    /**
     * 构建数据权限查询条件
     */
    protected static function buildDataScopeCondition(Builder $query): void
    {
        $request = \Framework\Tenant\TenantContext::getCurrentRequest();
        $loginUser = $request?->attributes->get('current_user');

        $tenantId = (int)\Framework\Tenant\TenantContext::getTenantId();

        //dump($tenantId);

        // 未登录直接无数据
        if (empty($loginUser)) {
            $query->whereRaw('1 = 0');
            return;
        }

        // 1、全局强制租户隔离（多租户核心）
        $tableName = method_exists(static::class, 'getTableName')
            ? static::getTableName()
            : (new static())->getTable();
        $query->where($tableName . '.tenant_id', $tenantId);

        // 2、系统超级管理员：跳过业务数据权限，只保留租户隔离
        // if (method_exists($loginUser, 'isSuperAdmin') && $loginUser->isSuperAdmin()) {
        //     return;
        // }

        // 3、获取当前角色的数据权限范围（优先单角色，其次 roles 集合第一个）
        $role = $loginUser->role ?? null;
        if (!$role) {
            if (method_exists($loginUser, 'relationLoaded') && $loginUser->relationLoaded('roles')) {
                $role = $loginUser->roles->first();
            } elseif (method_exists($loginUser, 'roles')) {
                $role = $loginUser->roles()->first();
            }
        }

        $dataScope = $role?->data_scope ?? \App\Models\SysRole::DATA_SCOPE_SELF;

        // 4、当前用户基础信息
        $userId = (int)($loginUser->id ?? 0);
        $deptId = method_exists($loginUser, 'getCurrentDeptId') ? $loginUser->getCurrentDeptId() : 0;

        // 5、按6种数据范围分支判断
        match ($dataScope) {
            // 1 全部数据：仅租户隔离，无额外限制
            \App\Models\SysRole::DATA_SCOPE_ALL => null,

            // 2 本部门数据
            \App\Models\SysRole::DATA_SCOPE_DEPT => $deptId > 0 ? $query->where('dept_id', $deptId) : $query->where('created_by', $userId),

            // 3 本部门及子部门数据
            \App\Models\SysRole::DATA_SCOPE_DEPT_AND_CHILD => $deptId > 0 ? static::withDeptAndChild($query, $deptId) : $query->where('created_by', $userId),

            // 4 仅本人数据
            \App\Models\SysRole::DATA_SCOPE_SELF => $query->where('created_by', $userId),

            // 5 本部门及子部门 + 本人数据
            \App\Models\SysRole::DATA_SCOPE_DEPT_AND_SELF => $deptId > 0 ? static::withDeptChildAndSelf($query, $deptId, $userId) : $query->where('created_by', $userId),

            // 6 自定义部门数据
            \App\Models\SysRole::DATA_SCOPE_CUSTOM => static::withCustomDept($query, $role?->id, $userId),

            // 默认降级：仅本人
            default => $query->where('created_by', $userId)
        };
    }

    /**
     * 本部门 + 所有子部门
     */
    protected static function withDeptAndChild(Builder $query, int $deptId): void
    {
        $deptIds = Dept::getAllChildIds($deptId);
        $query->whereIn('dept_id', $deptIds);
    }

    /**
     * 本部门+子部门 + 本人创建数据
     */
    protected static function withDeptChildAndSelf(Builder $query, int $deptId, int $userId): void
    {
        $deptIds = Dept::getAllChildIds($deptId);
        $query->where(function (Builder $q) use ($deptIds, $userId) {
            $q->whereIn('dept_id', $deptIds)
              ->orWhere('created_by', $userId);
        });
    }

    /**
     * 角色绑定的自定义部门
     */
    protected static function withCustomDept(Builder $query, ?int $roleId, int $userId): void
    {
        if (empty($roleId)) {
            $query->where('created_by', $userId);
            return;
        }

        $deptIds = RoleDept::where('role_id', $roleId)
            ->pluck('dept_id')
            ->toArray();

        if (!empty($deptIds)) {
            $query->whereIn('dept_id', $deptIds);
        } else {
            // 自定义部门无配置，降级为仅本人
            $query->where('created_by', $userId);
        }
    }
}