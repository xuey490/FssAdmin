<?php

declare(strict_types=1);

/**
 * 系统部门模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * SysDept 系统部门模型
 *
 * 部门表模型，支持无限级层级结构，支持多租户隔离
 *
 * @property int         $id          部门ID
 * @property int         $parent_id   父部门ID，0为根节点
 * @property string      $name        部门名称
 * @property string|null $code        部门编码
 * @property int|null    $leader_id   部门负责人ID
 * @property string      $level       祖级列表，格式: 0,1,5,
 * @property int         $sort        排序
 * @property int         $tenant_id   所属租户ID
 * @property int         $status      状态: 1启用 0禁用
 * @property string|null $remark      备注
 * @property int|null    $created_by  创建人ID
 * @property int|null    $updated_by  更新人ID
 * @property \DateTime   $created_at  创建时间
 * @property \DateTime   $updated_at  更新时间
 * @property \DateTime|null $deleted_at 删除时间
 *
 * @property-read SysDept     $parent    父部门
 * @property-read SysDept[]   $children  子部门
 * @property-read SysUser[]   $users     部门下的用户
 * @property-read SysUser     $leader    部门负责人
 * @property-read SysTenant   $tenant    所属租户
 * @property-read SysRole[]   $roles     关联的角色（通过 role_dept）
 */
class SysDept extends BaseLaORMModel
{
    use SoftDeletes;

    protected $table = 'sa_system_dept';

    public $incrementing = true;
    
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

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'leader_id',
        'level',
        'sort',
        'tenant_id',
        'status',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'leader_id' => 'integer',
        'sort' => 'integer',
        'tenant_id' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // ==================== 状态常量 ====================

    /** @var int 禁用状态 */
    public const STATUS_DISABLED = 0;

    /** @var int 启用状态 */
    public const STATUS_ENABLED = 1;

    // ==================== 关联关系 ====================

    /**
     * 父部门
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(SysDept::class, 'parent_id', 'id');
    }

    /**
     * 子部门
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(SysDept::class, 'parent_id', 'id');
    }

    /**
     * 部门下的用户
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(SysUser::class, 'dept_id', 'id');
    }

    /**
     * 部门负责人
     *
     * @return BelongsTo
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'leader_id', 'id');
    }

    /**
     * 所属租户
     *
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(SysTenant::class, 'tenant_id', 'id');
    }

    /**
     * 关联的角色（数据权限自定义部门）
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            SysRole::class,
            'sa_system_role_dept',
            'dept_id',
            'role_id'
        );
    }

    // ==================== 业务方法 ====================

    /**
     * 检查部门是否被禁用
     */
    public function isDisabled(): bool
    {
        return $this->status !== self::STATUS_ENABLED;
    }

    /**
     * 检查部门是否启用
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 获取部门树 (递归)
     *
     * @param int $parentId 父ID
     * @param int|null $tenantId 租户ID（可选过滤）
     * @param bool $enabledOnly 是否只返回启用的部门
     * @return array
     */
    public static function getDeptTree(int $parentId = 0, ?int $tenantId = null, bool $enabledOnly = true): array
    {
        $query = self::with('leader')
            ->where('parent_id', $parentId)
            ->orderBy('sort');

        if ($enabledOnly) {
            $query->where('status', self::STATUS_ENABLED);
        }

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $depts = $query->get()->toArray();

        foreach ($depts as &$dept) {
            // 数据库值映射到字典值：DB 1=启用 0=禁用 → 字典 1=正常 2=停用
            $dept['status'] = ($dept['status'] ?? 0) === 0 ? 2 : 1;
            $dept['children'] = self::getDeptTree($dept['id'], $tenantId, $enabledOnly);
        }

        return $depts;
    }

    /**
     * 获取部门选择树（带 label/value 字段，适配前端 ElTreeSelect）
     *
     * @param int $parentId 父ID
     * @param int|null $tenantId 租户ID（可选过滤）
     * @return array
     */
    public static function getSelectTree(int $parentId = 0, ?int $tenantId = null): array
    {
        $query = self::where('parent_id', $parentId)
            ->where('status', self::STATUS_ENABLED)
            ->orderBy('sort');

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        $depts = $query->get();

        $tree = [];
        foreach ($depts as $dept) {
            $node = [
                'id' => $dept->id,
                'value' => $dept->id,
                'label' => $dept->name,
                'name' => $dept->name,
                'code' => $dept->code,
                'parent_id' => $dept->parent_id,
                'children' => self::getSelectTree($dept->id, $tenantId),
            ];
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * 获取所有子部门ID (包含自己)
     *
     * @param int $deptId 部门ID
     * @return array
     */
    public static function getAllChildIds(int|string $deptId): array
    {
        $ids = [$deptId];
        $children = self::where('parent_id', $deptId)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $ids = array_merge($ids, self::getAllChildIds($childId));
        }

        return $ids;
    }

    /**
     * 获取部门层级路径
     *
     * @return array
     */
    public function getPath(): array
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, [
                'id' => $current->id,
                'name' => $current->name,
            ]);
            $current = $current->parent;
        }

        return $path;
    }

    /**
     * 检查部门编码是否唯一
     *
     * @param string $deptCode  部门编码
     * @param int    $excludeId 排除的部门ID
     * @return bool
     */
    public static function isCodeUnique(string $deptCode, int $excludeId = 0): bool
    {
        $query = self::where('code', $deptCode);

        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * 检查是否有子部门
     */
    public function hasChildren(): bool
    {
        return self::where('parent_id', $this->id)->exists();
    }

    /**
     * 检查部门下是否有用户
     */
    public function hasUsers(): bool
    {
        return SysUser::where('dept_id', $this->id)->exists();
    }
}
