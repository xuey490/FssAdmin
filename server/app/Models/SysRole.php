<?php

declare(strict_types=1);

/**
 * 系统角色模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SysRole 系统角色模型
 *
 * 角色表模型，用于RBAC权限控制
 *
 * @property int         $id          角色ID
 * @property string      $name        角色名称
 * @property string      $code        角色标识
 * @property int         $level       角色级别
 * @property int         $data_scope  数据范围
 * @property string      $remark      备注
 * @property int         $sort        排序
 * @property int         $parent_id   父角色ID
 * @property int         $tenant_id   所属租户ID
 * @property int         $status      状态: 1启用, 0禁用
 * @property int         $created_by  创建人ID
 * @property int         $updated_by  更新人ID
 * @property \DateTime   $created_at  创建时间
 * @property \DateTime   $updated_at  更新时间
 * @property \DateTime   $deleted_at  删除时间
 *
 * @property-read SysUser[]   $users  拥有此角色的用户
 * @property-read SysMenu[]   $menus  角色拥有的菜单
 * @property-read SysRole     $parent 父角色
 * @property-read SysRole[]   $children 子角色
 */
class SysRole extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_role';

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
        'name',
        'code',
        'level',
        'data_scope',
        'remark',
        'sort',
        'parent_id',
        'tenant_id',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'level' => 'integer',
        'sort' => 'integer',
        'parent_id' => 'integer',
        'tenant_id' => 'integer',
        'status' => 'integer',
        'data_scope' => 'integer',
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

    // ==================== 数据权限范围常量 ====================

    /** @var int 全部数据 */
    public const DATA_SCOPE_ALL = 1;

    /** @var int 本部门数据 */
    public const DATA_SCOPE_DEPT = 2;

    /** @var int 本部门及子部门数据 */
    public const DATA_SCOPE_DEPT_AND_CHILD = 3;

    /** @var int 仅本人数据 */
    public const DATA_SCOPE_SELF = 4;

    /** @var int 本部门及子部门 + 本人数据 */
    public const DATA_SCOPE_DEPT_AND_SELF = 5;

    /** @var int 自定义部门数据 */
    public const DATA_SCOPE_CUSTOM = 6;

    // ==================== 关联关系 ====================

    /**
     * 拥有此角色的用户 (多对多)
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            SysUser::class,
            'sa_system_user_role',
            'role_id',
            'user_id'
        )->withTimestamps();
    }

    /**
     * 角色拥有的菜单 (多对多)
     *
     * @return BelongsToMany
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(
            SysMenu::class,
            'sa_system_role_menu',
            'role_id',
            'menu_id'
        )->withTimestamps();
    }

    /**
     * 父角色
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(SysRole::class, 'parent_id', 'id');
    }

    /**
     * 子角色
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(SysRole::class, 'parent_id', 'id');
    }

    /**
     * 自定义数据权限部门
     *
     * @return BelongsToMany
     */
    public function dataScopeDepts(): BelongsToMany
    {
        return $this->belongsToMany(
            SysDept::class,
            'sa_system_role_dept',
            'role_id',
            'dept_id'
        );
    }

    // ==================== 业务方法 ====================

    /**
     * 检查角色是否被禁用
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    /**
     * 检查角色是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 获取角色的菜单ID列表
     *
     * @return array
     */
    public function getMenuIds(): array
    {
        return $this->menus()->pluck('sa_system_menu.id')->toArray();
    }

    /**
     * 同步角色菜单
     *
     * @param array $menuIds 菜单ID数组
     * @return void
     */
    public function syncMenus(array $menuIds): void
    {
        $this->menus()->sync($menuIds);
    }

    /**
     * 获取角色树 (递归)
     *
     * @param int $parentId 父ID
     * @return array
     */
    public static function getRoleTree(int $parentId = 0): array
    {
        $roles = self::where('parent_id', $parentId)
            ->where('status', self::STATUS_ENABLED)
            ->orderBy('sort')
            ->get()
            ->toArray();

        foreach ($roles as &$role) {
            $role['children'] = self::getRoleTree($role['id']);
        }

        return $roles;
    }

    /**
     * 检查角色编码是否唯一
     *
     * @param string $roleCode 角色编码
     * @param int    $excludeId 排除的角色ID
     * @return bool
     */
    public static function isRoleCodeUnique(string $roleCode, int $excludeId = 0): bool
    {
        $query = self::where('code', $roleCode);

        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    // ==================== 数据权限方法 ====================

    /**
     * 获取数据权限范围名称
     *
     * @return string
     */
    public function getDataScopeName(): string
    {
        return match ($this->data_scope) {
            self::DATA_SCOPE_ALL => '全部数据',
            self::DATA_SCOPE_DEPT => '本部门数据',
            self::DATA_SCOPE_DEPT_AND_CHILD => '本部门及子部门数据',
            self::DATA_SCOPE_SELF => '仅本人数据',
            self::DATA_SCOPE_DEPT_AND_SELF => '本部门及子部门+本人数据',
            self::DATA_SCOPE_CUSTOM => '自定义部门数据',
            default => '未知',
        };
    }

    /**
     * 是否为自定义数据权限
     *
     * @return bool
     */
    public function isCustomDataScope(): bool
    {
        return $this->data_scope === self::DATA_SCOPE_CUSTOM;
    }

    /**
     * 同步自定义数据权限部门
     *
     * @param array $deptIds 部门ID数组
     * @return void
     */
    public function syncDataScopeDepts(array $deptIds): void
    {
        $this->dataScopeDepts()->sync($deptIds);
    }

    /**
     * 获取自定义数据权限部门ID列表
     *
     * @return array
     */
    public function getDataScopeDeptIds(): array
    {
        return $this->dataScopeDepts()->pluck('sa_system_dept.id')->toArray();
    }

    /**
     * 获取所有数据权限选项（用于前端选择）
     *
     * @return array
     */
    public static function getDataScopeOptions(): array
    {
        return [
            ['value' => self::DATA_SCOPE_ALL, 'label' => '全部数据', 'desc' => '可查看所有数据'],
            ['value' => self::DATA_SCOPE_DEPT, 'label' => '本部门数据', 'desc' => '仅可查看本部门数据'],
            ['value' => self::DATA_SCOPE_DEPT_AND_CHILD, 'label' => '本部门及子部门', 'desc' => '可查看本部门及所有子部门数据'],
            ['value' => self::DATA_SCOPE_SELF, 'label' => '仅本人数据', 'desc' => '仅可查看自己创建的数据'],
            ['value' => self::DATA_SCOPE_DEPT_AND_SELF, 'label' => '部门+本人', 'desc' => '可查看本部门、子部门及自己创建的数据'],
            ['value' => self::DATA_SCOPE_CUSTOM, 'label' => '自定义部门', 'desc' => '可查看指定部门的数据'],
        ];
    }
}
