<?php

declare(strict_types=1);

/**
 * 系统菜单DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Dao;

use App\Models\SysMenu;
use Framework\Basic\BaseDao;

/**
 * SysMenuDao 菜单数据访问层
 *
 * 封装菜单相关的数据查询操作
 */
class SysMenuDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysMenu::class;
    }

    /**
     * 根据权限标识查找菜单
     *
     * @param string $permission 权限标识
     * @return SysMenu|null
     */
    public function findByPermission(string $permission): ?SysMenu
    {
        return $this->getOne(['slug' => $permission]);
    }

    /**
     * 获取启用的菜单列表
     *
     * @param int $page  页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getEnabledList(int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['status' => SysMenu::STATUS_ENABLED], '*', $page, $limit, 'sort asc')->toArray();
    }

    /**
     * 获取所有启用的菜单
     *
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->selectList(['status' => SysMenu::STATUS_ENABLED], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 获取子菜单列表
     *
     * @param int $parentId 父菜单ID
     * @return array
     */
    public function getChildrenByParentId(int $parentId): array
    {
        return $this->selectList(['parent_id' => $parentId], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 根据菜单类型获取菜单列表
     *
     * @param int $menuType 菜单类型
     * @return array
     */
    public function getListByMenuType(int $menuType): array
    {
        return $this->selectList(['type' => $menuType, 'status' => SysMenu::STATUS_ENABLED], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 获取目录和菜单类型列表 (用于分配权限)
     *
     * @return array
     */
    public function getDirectoryAndMenuList(): array
    {
        return $this->selectList(
            [
                ['type', 'in', [SysMenu::TYPE_DIRECTORY, SysMenu::TYPE_MENU]],
                'status' => SysMenu::STATUS_ENABLED,
            ],
            '*',
            0,
            0,
            'sort asc'
        )->toArray();
    }

    /**
     * 获取按钮类型列表 (用于按钮权限)
     *
     * @param int $parentId 父菜单ID (可选)
     * @return array
     */
    public function getButtonList(int $parentId = 0): array
    {
        $where = ['type' => SysMenu::TYPE_BUTTON, 'status' => SysMenu::STATUS_ENABLED];
        if ($parentId > 0) {
            $where['parent_id'] = $parentId;
        }
        return $this->selectList($where, '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 更新菜单状态
     *
     * @param int $menuId 菜单ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $menuId, int $status): bool
    {
        return $this->update($menuId, ['status' => $status]);
    }

    /**
     * 获取菜单总数
     *
     * @param array $where 条件
     * @return int
     */
    public function getMenuCount(array $where = []): int
    {
        return $this->count($where);
    }

    /**
     * 获取菜单ID列表
     *
     * @param array $where 条件
     * @return array
     */
    public function getMenuIds(array $where = []): array
    {
        return $this->getColumn($where, 'id');
    }

    /**
     * 检查菜单是否有子菜单
     *
     * @param int $menuId 菜单ID
     * @return bool
     */
    public function hasChildren(int $menuId): bool
    {
        return $this->be(['parent_id' => $menuId]);
    }

    /**
     * 根据菜单ID列表获取菜单
     *
     * @param array $menuIds 菜单ID数组
     * @return array
     */
    public function getByIds(array $menuIds): array
    {
        if (empty($menuIds)) {
            return [];
        }
        return $this->selectList([['id', 'in', $menuIds]], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 获取用户可见菜单
     *
     * @param array $menuIds 菜单ID数组
     * @return array
     */
    public function getVisibleMenus(array $menuIds): array
    {
        if (empty($menuIds)) {
            return [];
        }
        return $this->selectList(
            [
                ['id', 'in', $menuIds],
                'status' => SysMenu::STATUS_ENABLED,
                'is_hidden' => 2,
            ],
            '*',
            0,
            0,
            'sort asc'
        )->toArray();
    }
}
