<?php

declare(strict_types=1);

/**
 * 系统菜单服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysMenu;
use App\Models\SysRoleMenu;
use App\Models\SysUserMenu;
use App\Services\Casbin\CasbinService;
use App\Dao\SysMenuDao;
use Framework\Basic\BaseService;

/**
 * SysMenuService 菜单服务
 *
 * 处理菜单相关的业务逻辑
 */
class SysMenuService extends BaseService
{
    /**
     * DAO 实例
     * @var SysMenuDao
     */
    protected SysMenuDao $menuDao;

    /**
     * Casbin 服务
     * @var CasbinService
     */
    protected CasbinService $casbinService;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->menuDao = new SysMenuDao();
        $this->casbinService = new CasbinService();
    }

    /**
     * 获取菜单列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $name = $params['name'] ?? '';
        $path = $params['path'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysMenu::query();

        // 无论有无搜索条件，都先获取全量数据构建树形结构，然后再在树中进行过滤，否则会导致子节点匹配但由于缺失父节点导致整棵树断裂的问题。
        $list = $query->orderBy('sort')->get()->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            $item = $this->formatMenu($item);
        }

        // 构建树形结构
        $tree = $this->buildTree($list, 0);

        // 如果没有搜索条件，直接返回完整树
        if ($name === '' && $path === '' && (string)$status === '') {
            return $tree;
        }

        // 如果有搜索条件，在树中过滤
        return $this->filterTree($tree, $name, $path, $status);
    }

    /**
     * 过滤菜单树，保留匹配项及其祖先/子孙节点
     */
    protected function filterTree(array $tree, string $name, string $path, mixed $status): array
    {
        $result = [];
        foreach ($tree as $item) {
            $nameMatch = ($name === '' || mb_stripos($item['name'], $name) !== false);
            $pathMatch = ($path === '' || mb_stripos($item['path'] ?? '', $path) !== false);
            $statusMatch = ((string)$status === '' || (string)$item['status'] === (string)$status);

            $isMatch = $nameMatch && $pathMatch && $statusMatch;

            // 递归检查子节点
            $children = $item['children'] ?? [];
            if (!empty($children)) {
                $item['children'] = $this->filterTree($children, $name, $path, $status);
            }

            // 如果当前节点自身匹配，或者它的子孙节点中有匹配的，则保留该节点
            if ($isMatch || !empty($item['children'])) {
                $result[] = $item;
            }
        }
        
        return $result;
    }

    /**
     * 获取菜单树
     *
     * @return array
     */
    public function getMenuTree(): array
    {
        return SysMenu::getMenuTree();
    }

    /**
     * 获取菜单详情
     *
     * @param int $menuId 菜单ID
     * @return array|null
     */
    public function getDetail(int $menuId): ?array
    {
        $menu = SysMenu::find($menuId);

        if (!$menu) {
            return null;
        }

        $data = $this->formatMenu($menu);
        $data['path'] = $menu->getPath();
        $data['menu_type_name'] = $menu->getMenuTypeName();

        return $data;
    }

    /**
     * 创建菜单
     *
     * @param array $data     菜单数据
     * @param int   $operator 操作人ID
     * @return SysMenu|null
     */
    public function create(array $data, int $operator = 0): ?SysMenu
    {
        // 设置审计字段
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        // 如果是外链，设置 is_frame = 1
        if (($data['type'] ?? 0) === SysMenu::TYPE_LINK) {
            $data['is_iframe'] = 1;
        }

        return SysMenu::create($data);
    }

    /**
     * 更新菜单
     *
     * @param int   $menuId   菜单ID
     * @param array $data     菜单数据
     * @param int   $operator 操作人ID
     * @return bool
     */
    public function update(int $menuId, array $data, int $operator = 0): bool
    {
        $menu = SysMenu::find($menuId);
        if (!$menu) {
            throw new \Exception('菜单不存在');
        }

        // 检查父菜单是否有效
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            if ($data['parent_id'] == $menuId) {
                throw new \Exception('父菜单不能是自己');
            }

            // 检查父菜单是否存在
            if (!SysMenu::where('id', $data['parent_id'])->exists()) {
                throw new \Exception('父菜单不存在');
            }
        }

        // 设置审计字段
        $data['updated_by'] = $operator;

        // 如果是外链，设置 is_frame = 1
        if (($data['type'] ?? $menu->type) === SysMenu::TYPE_LINK) {
            $data['is_iframe'] = 1;
        }

        $menu->fill($data);
        $result = $menu->save();

        // 更新 Casbin 权限
        if ($result && !empty($menu->slug)) {
            $this->syncMenuPermissions($menuId);
        }

        return $result;
    }

    /**
     * 删除菜单
     *
     * @param int $menuId 菜单ID
     * @return bool
     */
    public function delete(int|string $menuId): bool
    {
        $menu = SysMenu::find($menuId);
        if (!$menu) {
            return false;
        }

        // 检查是否有子菜单
        if ($menu->hasChildren()) {
            throw new \Exception('该菜单下存在子菜单，无法删除');
        }

        // 软删除菜单
        $menu->delete();

        // 删除角色菜单关联
        SysRoleMenu::deleteByMenuId((int)$menuId);

        // 删除用户菜单关联
        SysUserMenu::deleteByMenuId((int)$menuId);

        return true;
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
        return $this->menuDao->updateStatus($menuId, $status);
    }

    /**
     * 获取用户菜单树
     *
     * @param int $userId 用户ID
     * @return array
     */
    public function getUserMenuTree(int $userId): array
    {
        $user = \App\Models\SysUser::find($userId);
        if (!$user) {
            return [];
        }

        return $user->getMenuTree();
    }

    /**
     * 获取用户权限列表
     *
     * @param int $userId 用户ID
     * @return array
     */
    public function getUserPermissions(int $userId): array
    {
        $user = \App\Models\SysUser::find($userId);
        if (!$user) {
            return [];
        }

        return $user->getPermissions();
    }

    /**
     * 获取目录和菜单类型列表 (用于分配权限)
     *
     * @return array
     */
    public function getDirectoryAndMenuTree(): array
    {
        $menus = SysMenu::where('status', SysMenu::STATUS_ENABLED)
            ->whereIn('type', [SysMenu::TYPE_DIRECTORY, SysMenu::TYPE_MENU])
            ->orderBy('sort')
            ->get()
            ->toArray();

        return $this->buildTree($menus, 0);
    }

    /**
     * 获取可分配的菜单树（status=1，未删除，带 label 字段，适配 ElTree）
     *
     * @return array
     */
    public function getAssignableMenuTree(): array
    {
        $menus = SysMenu::where('status', SysMenu::STATUS_ENABLED)
            ->orderBy('sort')
            ->get()
            ->toArray();

        return $this->buildAccessTree($menus, 0);
    }

    /**
     * 获取可访问的菜单树（含所有类型，带 label 字段，适配 ElTree）
     *
     * @return array
     */
    public function getAccessMenuTree(): array
    {
        // 菜单为全局共享资源，不按租户过滤；权限隔离由角色-菜单关联（tenant_id）控制
        $menus = SysMenu::where('status' , 1 )->orderBy('sort')->get()->toArray();

        return $this->buildAccessTree($menus, 0);
    }

    /**
     * 构建可访问菜单树（带 label 字段）
     *
     * @param array $items    数据列表
     * @param int   $parentId 父ID
     * @return array
     */
    protected function buildAccessTree(array $items, int $parentId = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ((int)$item['parent_id'] === $parentId) {
                $children = $this->buildAccessTree($items, (int)$item['id']);
                $node = [
                    'id' => $item['id'],
                    'label' => $item['name'],
                    'type' => $item['type'],
                    'parent_id' => $item['parent_id'],
                ];
                if ($children) {
                    $node['children'] = $children;
                }
                $tree[] = $node;
            }
        }
        return $tree;
    }

    /**
     * 同步菜单权限到 Casbin
     *
     * @param int $menuId 菜单ID
     * @return void
     */
    protected function syncMenuPermissions(int $menuId): void
    {
        // 获取拥有该菜单的所有角色
        $roleIds = SysRoleMenu::where('menu_id', $menuId)->pluck('role_id')->toArray();

        foreach ($roleIds as $roleId) {
            $this->casbinService->syncRoleMenuPermissions($roleId);
        }
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化菜单数据
     *
     * @param SysMenu|array $menu 菜单
     * @return array
     */
    protected function formatMenu(SysMenu|array $menu): array
    {
        if ($menu instanceof SysMenu) {
            $data = $menu->toArray();
        } else {
            $data = $menu;
        }

        // 格式化时间
        if (isset($data['create_time'])) {
            $data['create_time'] = is_string($data['create_time'])
                ? $data['create_time']
                : $data['create_time']?->format('Y-m-d H:i:s');
        }

        if (isset($data['update_time'])) {
            $data['update_time'] = is_string($data['update_time'])
                ? $data['update_time']
                : $data['update_time']?->format('Y-m-d H:i:s');
        }

        // 状态文本
        $data['status_text'] = $data['status'] === SysMenu::STATUS_ENABLED ? '启用' : '禁用';
        $data['visible_text'] = ($data['is_hidden'] ?? 2) !== 1 ? '显示' : '隐藏';
        $data['menu_type_name'] = $this->getMenuTypeName($data['type'] ?? 1);

        return $data;
    }

    /**
     * 获取菜单类型名称
     *
     * @param int $menuType 菜单类型
     * @return string
     */
    protected function getMenuTypeName(int $menuType): string
    {
        return match ($menuType) {
            SysMenu::TYPE_DIRECTORY => '目录',
            SysMenu::TYPE_MENU => '菜单',
            SysMenu::TYPE_BUTTON => '按钮',
            SysMenu::TYPE_LINK => '外链',
            default => '未知',
        };
    }

    /**
     * 构建树形结构
     *
     * @param array $items    数据列表
     * @param int   $parentId 父ID
     * @return array
     */
    protected function buildTree(array $items, int $parentId = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ((int)$item['parent_id'] === $parentId) {
                $children = $this->buildTree($items, (int)$item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                // 添加 value/label 字段适配 el-tree-select
                $item['value'] = $item['id'];
                $item['label'] = $item['name'] ?? '';
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
