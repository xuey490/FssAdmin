<?php

declare(strict_types=1);

/**
 * 角色-部门关联模型（数据权限自定义部门）
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SysRoleDept 角色-部门关联模型
 *
 * 用于存储角色的自定义数据权限部门，支持多租户隔离
 *
 * @property int         $id          主键ID
 * @property int         $role_id     角色ID
 * @property int         $dept_id     部门ID
 *
 * @property-read SysRole   $role    关联角色
 * @property-read SysDept   $dept    关联部门
 */
class SysRoleDept extends BaseLaORMModel
{
    protected $table = 'sa_system_role_dept';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'dept_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'role_id' => 'integer',
        'dept_id' => 'integer',
    ];

    // ==================== 关联关系 ====================

    /**
     * 关联角色
     *
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(SysRole::class, 'role_id', 'id');
    }

    /**
     * 关联部门
     *
     * @return BelongsTo
     */
    public function dept(): BelongsTo
    {
        return $this->belongsTo(SysDept::class, 'dept_id', 'id');
    }

    // ==================== 查询方法 ====================

    /**
     * 获取角色的自定义部门ID列表
     *
     * @param int $roleId 角色ID
     * @return array
     */
    public static function getDeptIdsByRole(int|string $roleId): array
    {
        return self::where('role_id', $roleId)->pluck('dept_id')->toArray();
    }

    /**
     * 获取多个角色的自定义部门ID列表（合并去重）
     *
     * @param array $roleIds 角色ID数组
     * @return array
     */
    public static function getDeptIdsByRoles(array $roleIds): array
    {
        if (empty($roleIds)) {
            return [];
        }

        return self::whereIn('role_id', $roleIds)->pluck('dept_id')->unique()->toArray();
    }

    /**
     * 检查角色是否有自定义部门
     */
    public static function hasCustomDepts(int|string $roleId): bool
    {
        return self::where('role_id', $roleId)->exists();
    }

    /**
     * 检查角色是否包含指定部门
     */
    public static function hasDept(int $roleId, int $deptId): bool
    {
        return self::where('role_id', $roleId)->where('dept_id', $deptId)->exists();
    }

    // ==================== 修改方法 ====================

    /**
     * 同步角色的自定义部门
     *
     * @param int   $roleId  角色ID
     * @param array $deptIds 部门ID数组
     * @return void
     */
    public static function syncRoleDepts(int $roleId, array $deptIds): void
    {
        self::where('role_id', $roleId)->delete();

        if (!empty($deptIds)) {
            $data = [];
            foreach ($deptIds as $deptId) {
                $data[] = [
                    'role_id' => $roleId,
                    'dept_id' => (int)$deptId,
                ];
            }
            self::insert($data);
        }
    }

    /**
     * 删除角色的所有部门关联
     */
    public static function deleteByRoleId(int|string $roleId): bool
    {
        return self::where('role_id', $roleId)->delete() !== false;
    }

    /**
     * 删除部门的所有角色关联
     */
    public static function deleteByDeptId(int|string $deptId): bool
    {
        return self::where('dept_id', $deptId)->delete() !== false;
    }

    /**
     * 获取角色的自定义部门数量
     */
    public static function getDeptCount(int|string $roleId): int
    {
        return self::where('role_id', $roleId)->count();
    }
}
