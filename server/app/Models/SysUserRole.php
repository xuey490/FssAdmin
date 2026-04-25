<?php

declare(strict_types=1);

/**
 * 用户角色关联模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SysUserRole 用户角色关联模型
 *
 * 多对多关联表模型，支持用户在不同租户拥有不同角色
 *
 * @property int         $id             主键ID
 * @property int         $user_id        用户ID
 * @property int         $role_id        角色ID
 * @property int         $tenant_id      租户ID（关键字段，支持多租户）
 * @property int         $created_by     创建人ID
 * @property int         $updated_by     更新人ID
 * @property \DateTime    $created_at     创建时间
 * @property \DateTime    $updated_at     更新时间
 *
 * @property-read SysUser   $user        关联用户
 * @property-read SysRole   $role        关联角色
 * @property-read SysTenant $tenant      关联租户
 */
class SysUserRole extends BaseLaORMModel
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_user_role';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'user_id',
        'role_id',
        'tenant_id',  // 新增：支持多租户
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'role_id' => 'integer',
        'tenant_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    /**
     * 是否自动维护时间戳
     * @var bool
     */
    public $timestamps = true;

    // ==================== 关联关系 ====================

    /**
     * 关联用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'user_id', 'id');
    }

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
     * 关联租户
     *
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(SysTenant::class, 'tenant_id', 'id');
    }

    // ==================== 查询方法 ====================

    /**
     * 获取用户在指定租户的角色ID列表
     *
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @return array 角色ID列表
     */
    public static function getRoleIdsByTenant(int $userId, int $tenantId): array
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->pluck('role_id')
            ->toArray();
    }

    /**
     * 获取指定租户下的所有用户角色关联
     *
     * @param int $tenantId 租户ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByTenant(int $tenantId)
    {
        return self::where('tenant_id', $tenantId)
            ->with(['user', 'role'])
            ->get();
    }

    /**
     * 获取用户的所有角色关联（跨租户）
     *
     * @param int $userId 用户ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByUserId(int $userId)
    {
        return self::where('user_id', $userId)
            ->with(['role', 'tenant'])
            ->get();
    }

    /**
     * 获取角色的所有用户关联（跨租户）
     *
     * @param int $roleId 角色ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByRoleId(int $roleId)
    {
        return self::where('role_id', $roleId)
            ->with(['user', 'tenant'])
            ->get();
    }

    /**
     * 检查用户是否拥有指定角色（在指定租户）
     *
     * @param int $userId 用户ID
     * @param int $roleId 角色ID
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function hasRole(int $userId, int $roleId, int $tenantId): bool
    {
        return self::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }

    /**
     * 检查用户是否拥有指定角色编码（在指定租户）
     *
     * @param int $userId 用户ID
     * @param string $roleCode 角色编码
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function hasRoleCode(int $userId, string $roleCode, int $tenantId): bool
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->whereHas('role', function ($query) use ($roleCode) {
                $query->where('code', $roleCode);
            })
            ->exists();
    }

    /**
     * 获取用户的角色编码列表（在指定租户）
     *
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @return array 角色编码数组
     */
    public static function getRoleCodesByTenant(int $userId, int $tenantId): array
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->with('role')
            ->get()
            ->pluck('role.code')
            ->filter()
            ->toArray();
    }

    // ==================== 修改方法 ====================

    /**
     * 批量插入用户角色关联（带租户ID）
     *
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @param array $roleIds 角色ID数组
     * @param int $createdBy 创建人ID
     * @return bool
     */
    public static function batchInsertByTenant(
        int $userId,
        int $tenantId,
        array $roleIds,
        int $createdBy = 0
    ): bool {
        if (empty($roleIds)) {
            return false;
        }

        $data = [];
        $now = date('Y-m-d H:i:s',time());

        foreach ($roleIds as $roleId) {
            $data[] = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'tenant_id' => $tenantId,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
                'create_time' => $now,
                'update_time' => $now,
            ];
        }

        return self::insert($data);
    }

    /**
     * 同步用户在指定租户的角色
     *
     * 先删除该租户下的所有角色关联，再插入新的关联
     *
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @param array $roleIds 角色ID数组
     * @param int $createdBy 创建人ID
     * @return void
     */
    public static function syncUserRolesByTenant(
        int $userId,
        int $tenantId,
        array $roleIds,
        int $createdBy = 0
    ): void {
        // 只删除该租户下的角色关联
        self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->delete();

        // 插入新的角色关联
        if (!empty($roleIds)) {
            self::batchInsertByTenant($userId, $tenantId, $roleIds, $createdBy);
        }
    }

    /**
     * 为用户添加单个角色（在指定租户）
     *
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @param int $roleId 角色ID
     * @param int $createdBy 创建人ID
     * @return self|null
     */
    public static function addRole(
        int $userId,
        int $tenantId,
        int $roleId,
        int $createdBy = 0
    ): ?self {
        // 检查是否已存在
        if (self::hasRole($userId, $roleId, $tenantId)) {
            return null;
        }

        return self::create([
            'user_id' => $userId,
            'role_id' => $roleId,
            'tenant_id' => $tenantId,
            'created_by' => $createdBy,
            'updated_by' => $createdBy,
        ]);
    }

    /**
     * 移除用户的单个角色（在指定租户）
     *
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @param int $roleId 角色ID
     * @return bool
     */
    public static function removeRole(int $userId, int $tenantId, int $roleId): bool
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('role_id', $roleId)
            ->delete() > 0;
    }

    /**
     * 删除用户的所有角色关联（跨租户）
     *
     * @param int $userId 用户ID
     * @return bool
     */
    public static function deleteByUserId(int $userId): bool
    {
        return self::where('user_id', $userId)->delete() !== false;
    }

    /**
     * 删除用户的所有角色关联（指定租户）
     *
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function deleteByUserIdAndTenant(int $userId, int $tenantId): bool
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->delete() !== false;
    }

    /**
     * 删除角色的所有用户关联
     *
     * @param int $roleId 角色ID
     * @return bool
     */
    public static function deleteByRoleId(int $roleId): bool
    {
        return self::where('role_id', $roleId)->delete() !== false;
    }

    /**
     * 删除角色的所有用户关联（指定租户）
     *
     * @param int $roleId 角色ID
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function deleteByRoleIdAndTenant(int $roleId, int $tenantId): bool
    {
        return self::where('role_id', $roleId)
            ->where('tenant_id', $tenantId)
            ->delete() !== false;
    }

    /**
     * 删除租户下的所有用户角色关联
     *
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function deleteByTenantId(int $tenantId): bool
    {
        return self::where('tenant_id', $tenantId)->delete() !== false;
    }

    /**
     * 复制用户角色到另一个租户
     *
     * @param int $userId 用户ID
     * @param int $fromTenantId 源租户ID
     * @param int $toTenantId 目标租户ID
     * @param int $createdBy 创建人ID
     * @return int 复制的角色数量
     */
    public static function copyRolesToTenant(
        int $userId,
        int $fromTenantId,
        int $toTenantId,
        int $createdBy = 0
    ): int {
        // 获取源租户的角色ID列表
        $roleIds = self::getRoleIdsByTenant($userId, $fromTenantId);

        if (empty($roleIds)) {
            return 0;
        }

        // 同步到目标租户
        self::syncUserRolesByTenant($userId, $toTenantId, $roleIds, $createdBy);

        return count($roleIds);
    }

    /**
     * 获取租户下的角色统计
     *
     * @param int $tenantId 租户ID
     * @return array
     */
    public static function getStatistics(int $tenantId): array
    {
        $totalAssociations = self::where('tenant_id', $tenantId)->count();
        $userCount = self::where('tenant_id', $tenantId)->distinct('user_id')->count('user_id');
        $roleCount = self::where('tenant_id', $tenantId)->distinct('role_id')->count('role_id');

        return [
            'total_associations' => $totalAssociations,
            'user_count' => $userCount,
            'role_count' => $roleCount,
            'avg_roles_per_user' => $userCount > 0 ? round($totalAssociations / $userCount, 2) : 0,
        ];
    }
}
