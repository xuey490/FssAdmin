<?php

declare(strict_types=1);

/**
 * 系统用户部门关联模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SysUserDept 用户-部门-租户关联模型
 *
 * 用于解决多租户环境下用户在不同租户拥有不同部门的问题
 * 一个用户在一个租户只能有一个部门（通过唯一索引保证）
 *
 * @property int         $id          主键ID
 * @property int         $user_id     用户ID
 * @property int         $tenant_id   租户ID
 * @property int         $dept_id     部门ID
 * @property int|null    $created_by  创建人ID
 * @property int|null    $updated_by  更新人ID
 * @property \DateTime   $create_time 创建时间
 * @property \DateTime   $update_time 更新时间
 *
 * @property-read SysUser   $user   关联的用户
 * @property-read SysTenant $tenant 关联的租户
 * @property-read SysDept   $dept   关联的部门
 */
class SysUserDept extends BaseLaORMModel
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_user_dept';

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

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'user_id',
        'tenant_id',
        'dept_id',
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
        'tenant_id' => 'integer',
        'dept_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    // ==================== 关联关系 ====================

    /**
     * 关联的用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'user_id', 'id');
    }

    /**
     * 关联的租户
     *
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(SysTenant::class, 'tenant_id', 'id');
    }

    /**
     * 关联的部门
     *
     * @return BelongsTo
     */
    public function dept(): BelongsTo
    {
        return $this->belongsTo(SysDept::class, 'dept_id', 'id');
    }

    // ==================== 业务方法 ====================

    /**
     * 获取用户在指定租户的部门ID
     *
     * @param int $userId   用户ID
     * @param int $tenantId 租户ID
     * @return int|null 部门ID，不存在返回null
     */
    public static function getDeptIdByUserAndTenant(int $userId, int $tenantId): ?int
    {
        $record = self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        return $record ? $record->dept_id : null;
    }

    /**
     * 同步用户部门关联
     *
     * 如果记录存在则更新，不存在则创建
     *
     * @param int $userId     用户ID
     * @param int $tenantId   租户ID
     * @param int $deptId     部门ID
     * @param int $operatorId 操作人ID
     * @return void
     */
    public static function syncUserDept(int $userId, int $tenantId, int $deptId, int $operatorId): void
    {
        $record = self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($record) {
            // 更新现有记录
            $record->dept_id = $deptId;
            $record->updated_by = $operatorId;
            $record->save();
        } else {
            // 创建新记录
            self::create([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'dept_id' => $deptId,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ]);
        }
    }

    /**
     * 获取部门下的所有用户ID
     *
     * @param int $deptId   部门ID
     * @param int $tenantId 租户ID
     * @return array 用户ID数组
     */
    public static function getUsersByDept(int $deptId, int $tenantId): array
    {
        return self::where('dept_id', $deptId)
            ->where('tenant_id', $tenantId)
            ->pluck('user_id')
            ->toArray();
    }

    /**
     * 获取用户在所有租户的部门关联
     *
     * @param int $userId 用户ID
     * @return array 格式：[['tenant_id' => 1, 'dept_id' => 5], ...]
     */
    public static function getDeptsByUser(int $userId): array
    {
        return self::where('user_id', $userId)
            ->get(['tenant_id', 'dept_id'])
            ->toArray();
    }
}
