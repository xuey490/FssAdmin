<?php

declare(strict_types=1);

/**
 * 租户模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SysTenant 租户模型
 *
 * 租户表模型，用于多租户数据隔离管理
 *
 * @property int         $id             租户ID
 * @property string      $tenant_name    租户名称
 * @property string      $tenant_code    租户编码（唯一）
 * @property string|null $contact_name   联系人姓名
 * @property string|null $contact_phone  联系人电话
 * @property string|null $contact_email  联系人邮箱
 * @property string|null $address        租户地址
 * @property string|null $logo_url       租户Logo URL
 * @property int         $status         状态：0=禁用 1=启用
 * @property \DateTime|null $expire_time 过期时间
 * @property int         $max_users      最大用户数，0=无限制
 * @property int         $max_depts      最大部门数，0=无限制
 * @property int         $max_roles      最大角色数，0=无限制
 * @property string|null $remark         备注
 * @property int         $created_by     创建人ID
 * @property int         $updated_by     更新人ID
 * @property \DateTime    $created_at     创建时间
 * @property \DateTime    $updated_at     更新时间
 * @property \DateTime|null $deleted_at   删除时间
 *
 * @property-read SysUser[] $users       租户下的用户
 * @property-read SysDept[] $depts       租户下的部门
 * @property-read SysRole[] $roles       租户下的角色
 */
class SysTenant extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_tenant';

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
        'tenant_name',
        'tenant_code',
        'contact_name',
        'contact_phone',
        'contact_email',
        'address',
        'logo_url',
        'status',
        'expire_time',
        'max_users',
        'max_depts',
        'max_roles',
        'remark',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'max_users' => 'integer',
        'max_depts' => 'integer',
        'max_roles' => 'integer',
        'expire_time' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    // ==================== 状态常量 ====================

    /** @var int 禁用状态 */
    public const STATUS_DISABLED = 0;

    /** @var int 启用状态 */
    public const STATUS_ENABLED = 1;

    // ==================== 关联关系 ====================

    /**
     * 租户下的用户（通过 sys_user_tenant 关联）
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(SysUserTenant::class, 'tenant_id', 'id');
    }

    /**
     * 租户下的部门
     *
     * @return HasMany
     */
    public function depts(): HasMany
    {
        return $this->hasMany(SysDept::class, 'tenant_id', 'id');
    }

    /**
     * 租户下的角色
     *
     * @return HasMany
     */
    public function roles(): HasMany
    {
        return $this->hasMany(SysRole::class, 'tenant_id', 'id');
    }

    // 注意：菜单已改为全局共享资源，租户不再拥有独立菜单
    // 如需获取租户可访问的菜单，请通过角色-菜单关联查询

    // ==================== 业务方法 ====================

    /**
     * 检查租户是否有效
     *
     * @return bool
     */
    public function isValid(): bool
    {
        // 检查状态
        if ($this->status !== self::STATUS_ENABLED) {
            return false;
        }

        // 检查是否过期
        if ($this->expire_time !== null && $this->expire_time->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * 检查租户是否已过期
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if ($this->expire_time === null) {
            return false;
        }
        return $this->expire_time->isPast();
    }

    /**
     * 检查租户是否被禁用
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    /**
     * 检查租户是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 获取当前用户数
     *
     * @return int
     */
    public function getCurrentUserCount(): int
    {
        return SysUserTenant::where('tenant_id', $this->id)->count();
    }

    /**
     * 检查是否达到最大用户数限制
     *
     * @return bool
     */
    public function isUserLimitReached(): bool
    {
        if ($this->max_users === 0) {
            return false; // 0 表示无限制
        }

        return $this->getCurrentUserCount() >= $this->max_users;
    }

    /**
     * 获取当前部门数
     *
     * @return int
     */
    public function getCurrentDeptCount(): int
    {
        return SysDept::where('tenant_id', $this->id)->count();
    }

    /**
     * 检查是否达到最大部门数限制
     *
     * @return bool
     */
    public function isDeptLimitReached(): bool
    {
        if ($this->max_depts === 0) {
            return false;
        }

        return $this->getCurrentDeptCount() >= $this->max_depts;
    }

    /**
     * 获取当前角色数
     *
     * @return int
     */
    public function getCurrentRoleCount(): int
    {
        return SysRole::where('tenant_id', $this->id)->count();
    }

    /**
     * 检查是否达到最大角色数限制
     *
     * @return bool
     */
    public function isRoleLimitReached(): bool
    {
        if ($this->max_roles === 0) {
            return false;
        }

        return $this->getCurrentRoleCount() >= $this->max_roles;
    }

    /**
     * 检查租户编码是否唯一
     *
     * @param string $tenantCode 租户编码
     * @param int $excludeId 排除的租户ID
     * @return bool
     */
    public static function isTenantCodeUnique(string $tenantCode, int $excludeId = 0): bool
    {
        $query = self::where('tenant_code', $tenantCode);

        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * 根据编码查找租户
     *
     * @param string $tenantCode 租户编码
     * @return self|null
     */
    public static function findByCode(string $tenantCode): ?self
    {
        return self::where('tenant_code', $tenantCode)->first();
    }

    /**
     * 获取有效的租户列表
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getValidTenants()
    {
        return self::where('status', self::STATUS_ENABLED)
            ->where(function ($query) {
                $query->whereNull('expire_time')
                    ->orWhere('expire_time', '>', date('Y-m-d H:i:s',time()));
            })
            ->get();
    }

    /**
     * 获取租户统计信息
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'user_count' => $this->getCurrentUserCount(),
            'user_limit' => $this->max_users,
            'user_usage_percent' => $this->max_users > 0 
                ? round(($this->getCurrentUserCount() / $this->max_users) * 100, 2) 
                : 0,
            'dept_count' => $this->getCurrentDeptCount(),
            'dept_limit' => $this->max_depts,
            'dept_usage_percent' => $this->max_depts > 0 
                ? round(($this->getCurrentDeptCount() / $this->max_depts) * 100, 2) 
                : 0,
            'role_count' => $this->getCurrentRoleCount(),
            'role_limit' => $this->max_roles,
            'role_usage_percent' => $this->max_roles > 0 
                ? round(($this->getCurrentRoleCount() / $this->max_roles) * 100, 2) 
                : 0,
        ];
    }
}
