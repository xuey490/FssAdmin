<?php

declare(strict_types=1);

/**
 * 用户菜单关联模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SysUserMenu 用户菜单关联模型
 *
 * 多对多关联表模型，用于用户个人菜单权限
 *
 * @property int         $id         主键ID
 * @property int         $user_id    用户ID
 * @property int         $menu_id    菜单ID
 * @property int         $created_by 创建人ID
 * @property int         $updated_by 更新人ID
 * @property \DateTime   $created_at 创建时间
 * @property \DateTime   $updated_at 更新时间
 */
class SysUserMenu extends Model
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_user_menu';

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
    //const DELETED_AT = 'delete_time';

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'user_id',
        'menu_id',
        'tenant_id',
        'created_by',
        'updated_by',
        'status',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'menu_id' => 'integer',
        'tenant_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'status' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        //'delete_time' => 'datetime',
    ];

    /**
     * 是否自动维护时间戳
     * @var bool
     */
    public $timestamps = true;

    // ==================== 业务方法 ====================

    /**
     * 批量插入用户菜单关联
     *
     * @param int   $userId    用户ID
     * @param array $menuIds   菜单ID数组
     * @param int   $tenantId  租户ID
     * @param int   $createdBy 创建人ID
     * @return bool
     */
    public static function batchInsert(int $userId, array $menuIds, int $tenantId = 0, int $createdBy = 0): bool
    {
        if (empty($menuIds)) {
            return false;
        }

        $data = [];
        $now = date('Y-m-d H:i:s',time());

        foreach ($menuIds as $menuId) {
            $data[] = [
                'user_id' => $userId,
                'menu_id' => $menuId,
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
     * 删除用户的所有菜单关联
     *
     * @param int      $userId   用户ID
     * @param int|null $tenantId 租户ID（可选，不传则删除所有租户）
     * @return bool
     */
    public static function deleteByUserId(int $userId, ?int $tenantId = null): bool
    {
        $query = self::where('user_id', $userId);
        
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }
        
        return $query->forceDelete() !== false;
    }

    /**
     * 删除菜单的所有用户关联
     *
     * @param int $menuId 菜单ID
     * @return bool
     */
    public static function deleteByMenuId(int $menuId): bool
    {
        //return self::where('menu_id', $menuId)->forceDelete() !== false;
        self::where('menu_id', $menuId)->delete();
        return true;
    }

    /**
     * 同步用户菜单
     *
     * @param int   $userId    用户ID
     * @param array $menuIds   菜单ID数组
     * @param int   $tenantId  租户ID
     * @param int   $createdBy 创建人ID
     * @return void
     */
    public static function syncUserMenus(int $userId, array $menuIds, int $tenantId = 0, int $createdBy = 0): void
    {
        // 先删除该租户下的旧关联
        self::deleteByUserId($userId, $tenantId);

        // 再插入新的关联
        if (!empty($menuIds)) {
            self::batchInsert($userId, $menuIds, $tenantId, $createdBy);
        }
    }

    /**
     * 获取用户菜单ID列表
     *
     * @param int      $userId   用户ID
     * @param int|null $tenantId 租户ID（可选）
     * @return array
     */
    public static function getMenuIdsByUserId(int $userId, ?int $tenantId = null): array
    {
        $query = self::where('user_id', $userId);
        
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }
        
        return $query->pluck('menu_id')->toArray();
    }
}
