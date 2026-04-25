<?php

declare(strict_types=1);

/**
 * 系统用户模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Framework\Tenant\TenantContext;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SysUser 系统用户模型
 *
 * 用户表模型，包含用户基本信息、状态管理、角色关联等
 * 支持多租户：用户可属于多个租户，在不同租户拥有不同角色
 *
 * @property int         $id             用户ID
 * @property string      $username       登录账号
 * @property string      $password       密码
 * @property string      $realname       真实姓名
 * @property string      $gender         性别
 * @property string      $avatar         头像
 * @property string      $email          邮箱
 * @property string      $phone          手机号
 * @property string      $signed         个性签名
 * @property string      $dashboard      工作台
 * @property int         $is_super       是否超级管理员
 * @property int         $status         状态 0=禁用 1=启用
 * @property string      $remark         备注
 * @property \DateTime   $login_time     最后登录时间
 * @property string      $login_ip       最后登录IP
 * @property int         $created_by     创建人ID
 * @property int         $updated_by     更新人ID
 * @property \DateTime   $created_at     创建时间
 * @property \DateTime   $updated_at     更新时间
 * @property \DateTime   $deleted_at     删除时间
 *
 * @property-read SysRole[]   $roles      用户角色列表（当前租户）
 * @property-read SysMenu[]   $menus      用户个人菜单（当前租户）
 * @property-read SysDept     $dept       所属部门（当前租户）
 * @property-read SysTenant[] $tenants    用户所属的所有租户
 * @property-read SysPost[]   $posts      用户拥有的岗位
 */
class SysUser extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_user';

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
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'password',
        'delete_time',
    ];

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'realname',
        'gender',
        'avatar',
        'email',
        'phone',
        'signed',
        'dashboard',
        'is_super',
        'status',
        'remark',
        'login_time',
        'login_ip',
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
        //'dept_id' => 'integer', // 必须保留！
        'is_super' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'login_time' => 'datetime',
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
     * 用户在各租户的部门关联 (一对多)
     *
     * @return HasMany
     */
    public function depts(): HasMany
    {
        return $this->hasMany(SysUserDept::class, 'user_id', 'id');
    }

    /**
     * 获取用户在指定租户的部门
     *
     * @param int $tenantId 租户ID
     * @return SysDept|null
     */
    public function deptByTenant(int $tenantId): ?SysDept
    {
        $userDept = SysUserDept::where('user_id', $this->id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $userDept ? $userDept->dept : null;
    }

    /**
     * 用户拥有的角色 (多对多) - 当前租户
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $tenantId = TenantContext::getTenantId();

        return $this->belongsToMany(
            SysRole::class,
            'sa_system_user_role',
            'user_id',
            'role_id'
        )
        ->wherePivot('tenant_id', $tenantId ?? 0)
        ->withTimestamps();
    }

    /**
     * 获取指定租户的角色
     *
     * @param int $tenantId 租户ID
     * @return BelongsToMany
     */
    public function rolesByTenant(int $tenantId): BelongsToMany
    {
        return $this->belongsToMany(
            SysRole::class,
            'sa_system_user_role',
            'user_id',
            'role_id'
        )
        ->wherePivot('tenant_id', $tenantId)
        ->withTimestamps();
    }

    /**
     * 用户所属的所有租户
     *
     * @return HasMany
     */
    public function tenantRelations(): HasMany
    {
        return $this->hasMany(SysUserTenant::class, 'user_id', 'id');
    }

    /**
     * 用户所属的所有租户（通过关联表）
     *
     * @return BelongsToMany
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            SysTenant::class,
            'sa_system_user_tenant',
            'user_id',
            'tenant_id'
        )->withPivot('is_default', 'join_time')
         ->withTimestamps();
    }

    /**
     * 用户个人菜单 (多对多)
     *
     * @return BelongsToMany
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(
            SysMenu::class,
            'sa_system_user_menu',
            'user_id',
            'menu_id'
        )->withTimestamps();
    }

    /**
     * 用户拥有的岗位 (多对多)
     *
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            SysPost::class,
            'sa_system_user_post',
            'user_id',
            'post_id'
        );
    }

    // ==================== 业务方法 ====================

    /**
     * 检查用户是否被禁用
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    /**
     * 检查用户是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 检查是否为超级管理员
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super === 1;
    }

    /**
     * 获取当前租户下的部门ID
     *
     * 实现多租户部门隔离：
     * 从 sa_system_user_dept 中间表查询当前租户的部门ID
     *
     * @return int|null 部门ID，无部门时返回null
     */
    public function getCurrentDeptId(): ?int
    {
        $tenantId = TenantContext::getTenantId();
        if (!$tenantId) {
            return null;
        }

        return SysUserDept::getDeptIdByUserAndTenant($this->id, $tenantId);
    }

    /**
     * 验证密码
     *
     * @param string $password 明文密码
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * 设置密码 (加密)
     *
     * @param string $password 明文密码
     * @return void
     */
    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * 获取用户的所有角色编码（当前租户）
     *
     * @return array
     */
    public function getRoleCodes(): array
    {
        return $this->roles()->where('sa_system_role.status', SysRole::STATUS_ENABLED)->pluck('sa_system_role.code')->toArray();
    }

    /**
     * 获取用户的所有角色ID（当前租户）
     *
     * @return array
     */
    public function getRoleIds(): array
    {
        return $this->roles()->where('sa_system_role.status', SysRole::STATUS_ENABLED)->pluck('sa_system_role.id')->toArray();
    }

    /**
     * 获取用户在指定租户的角色编码
     *
     * @param int $tenantId 租户ID
     * @return array
     */
    public function getRoleCodesByTenant(int $tenantId): array
    {
        return $this->rolesByTenant($tenantId)
            ->where('sa_system_role.status', SysRole::STATUS_ENABLED)
            ->pluck('sa_system_role.code')
            ->toArray();
    }

    /**
     * 获取用户在指定租户的角色ID
     *
     * @param int $tenantId 租户ID
     * @return array
     */
    public function getRoleIdsByTenant(int $tenantId): array
    {
        return $this->rolesByTenant($tenantId)
            ->where('sa_system_role.status', SysRole::STATUS_ENABLED)
            ->pluck('sa_system_role.id')
            ->toArray();
    }

    /**
     * 获取用户可访问的所有租户ID
     *
     * @return array
     */
    public function getTenantIds(): array
    {
        return SysUserTenant::getTenantIdsByUser($this->id);
    }

    /**
     * 获取用户的默认租户ID
     *
     * @return int|null
     */
    public function getDefaultTenantId(): ?int
    {
        return SysUserTenant::getDefaultTenantId($this->id);
    }

    /**
     * 检查用户是否属于指定租户
     *
     * @param int $tenantId 租户ID
     * @return bool
     */
    public function belongsToTenant(int $tenantId): bool
    {
        return SysUserTenant::isUserInTenant($this->id, $tenantId);
    }

    /**
     * 获取用户的合并菜单ID列表 (角色菜单 + 个人菜单) - 当前租户
     *
     * @return array
     */
    public function getMergedMenuIds(): array
    {
        $tenantId = TenantContext::getTenantId() ?? SysUserTenant::getDefaultTenantId($this->id);

        // 获取租户内拥有的角色ID
        $roleIds = [];
        if ($tenantId) {
            $roleIds = $this->getRoleIdsByTenant($tenantId);
        } else {
            $roleIds = $this->getRoleIds();
        }

        

        // 超级管理员拥有所有菜单（菜单为全局共享资源）
        if ($this->isSuperAdmin()) {
            return SysMenu::where('status', SysMenu::STATUS_ENABLED)
                ->pluck('id')
                ->toArray();
        }

        // 1. 获取角色菜单ID（按当前租户过滤）
        $roleMenuIds = [];
        if (!empty($roleIds) && $tenantId) {
            $roleMenuIds = SysRoleMenu::whereIn('role_id', $roleIds)
                ->where('tenant_id', $tenantId)
                ->pluck('menu_id')
                ->toArray();
        }
        
        // 2. 获取用户个人菜单ID（按当前租户过滤）
        $userMenuIds = [];
        if ($tenantId) {
            $userMenuIds = SysUserMenu::where('user_id', $this->id)
                ->where('tenant_id', $tenantId)
                ->pluck('menu_id')
                ->toArray();
        }
        
        // 3. 合并去重
        return array_unique(array_merge($roleMenuIds, $userMenuIds));
    }

    /**
     * 获取用户的菜单列表 (树形结构)
     *
     * @return array
     */
    public function getMenuTree(): array
    {
        $menuIds = $this->getMergedMenuIds();


        if (empty($menuIds)) {
            return [];
        }

        $menus = SysMenu::whereIn('id', $menuIds)
            ->where('status', SysMenu::STATUS_ENABLED)
            ->orderBy('sort')
            ->get()
            ->toArray();
        
        return $this->buildMenuTree($menus, 0);
    }

    /**
     * 构建菜单树
     *
     * @param array $menus    菜单列表
     * @param int   $parentId 父ID
     * @return array
     */
    protected function buildMenuTree(array $menus, int $parentId = 0): array
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ((int)$menu['parent_id'] === $parentId) {
                $children = $this->buildMenuTree($menus, (int)$menu['id']);
                if ($children) {
                    $menu['children'] = $children;
                }
                $tree[] = $menu;
            }
        }
        return $tree;
    }

    /**
     * 获取用户的所有权限标识
     *
     * @return array
     */
    public function getPermissions(): array
    {
        $menuIds = $this->getMergedMenuIds();

        if (empty($menuIds)) {
            return [];
        }

        return SysMenu::whereIn('id', $menuIds)
            ->where('status', SysMenu::STATUS_ENABLED)
            ->where('slug', '!=', '')
            ->pluck('slug')
            ->toArray();
    }

    /**
     * 更新最后登录信息
     *
     * @param string $ip 登录IP
     * @return void
     */
    public function updateLoginInfo(string $ip): void
    {
        $this->login_ip = $ip;
        $this->login_time = date('Y-m-d H:i:s',time());
        $this->save();
    }
}
