<?php

declare(strict_types=1);

/**
 * 菜单管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysMenuService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

/**
 * MenuController 菜单管理控制器
 *
 * 处理菜单的增删改查等操作
 */
class MenuController extends BaseController
{
    /**
     * 菜单服务
     * @var SysMenuService
     */
    protected SysMenuService $menuService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->menuService = new SysMenuService();
    }

    /**
     * 获取菜单列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/list', methods: ['GET'], name: 'menu.list')]
    #[Auth(required: true)]
    #[Permission('core:menu:index')]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'name' => $this->input('name', '', true, $request) ,
            'path' => $this->input('path', '', true, $request),
            'status' => $this->input('status', '', true, $request),
        ];

        $result = $this->menuService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取菜单树
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/tree', methods: ['GET'], name: 'menu.tree')]
    #[Auth(required: true)]
    #[Permission('core:menu:index')]
    public function tree(Request $request): BaseJsonResponse
    {
        $result = $this->menuService->getMenuTree();

        return $this->success($result);
    }

    /**
     * 获取用户菜单树
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/user-tree', methods: ['GET'], name: 'menu.userTree')]
    #[Auth(required: true)]
    public function userTree(Request $request): BaseJsonResponse
    {
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
            return $this->fail('未登录', 401);
        }

        $result = $this->menuService->getUserMenuTree($user['id']);

        return $this->success($result);
    }

    /**
     * 获取用户权限列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/user-permissions', methods: ['GET'], name: 'menu.userPermissions')]
    #[Auth(required: true)]
    public function userPermissions(Request $request): BaseJsonResponse
    {
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
            return $this->fail('未登录', 401);
        }

        $result = $this->menuService->getUserPermissions($user['id']);

        return $this->success($result);
    }

    /**
     * 获取目录和菜单树 (用于分配权限)
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/permission-tree', methods: ['GET'], name: 'menu.permissionTree')]
    #[Auth(required: true)]
    #[Permission('core:menu:index')]
    public function permissionTree(Request $request): BaseJsonResponse
    {
        $result = $this->menuService->getDirectoryAndMenuTree();

        return $this->success($result);
    }

    /**
     * 获取可访问的菜单树（含所有类型，带 label 字段，适配 ElTree）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/access-menu', methods: ['GET'], name: 'menu.accessMenu')]
    #[Auth(required: true)]
    #[Permission('core:menu:index')]
    public function accessMenu(Request $request): BaseJsonResponse
    {
        $result = $this->menuService->getAccessMenuTree();

        return $this->success($result);
    }

    /**
     * 获取可分配的菜单树（status=1，未删除，适配 ElTree）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/assignable-tree', methods: ['GET'], name: 'menu.assignableTree')]
    #[Auth(required: true)]
    #[Permission('core:menu:index')]
    public function assignableTree(Request $request): BaseJsonResponse
    {
        $result = $this->menuService->getAssignableMenuTree();
        return $this->success($result);
    }

    /**
     * 获取菜单详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/detail/{id}', methods: ['GET'], name: 'menu.detail')]
    #[Auth(required: true)]
    #[Permission('core:menu:read')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        $result = $this->menuService->getDetail($id);

        if (!$result) {
            return $this->fail('菜单不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 从请求体构建菜单字段（仅包含客户端显式提交的键，避免把未传字段写成 null）
     */
    protected function buildMenuPayload(array $body, bool $isCreate = false): array
    {
        $intFields = [
            'parent_id',
            'type',
            'sort',
            'is_hidden',
            'status',
            'is_iframe',
            'is_keep_alive',
            'is_fixed_tab',
            'is_full_page',
        ];
        $stringFields = [
            'name',
            'code',
            'path',
            'component',
            'slug',
            'icon',
            'link_url',
            'remark',
        ];

        $data = [];

        foreach ($intFields as $field) {
            if (array_key_exists($field, $body) && $body[$field] !== null && $body[$field] !== '') {
                $data[$field] = (int)$body[$field];
            }
        }

        foreach ($stringFields as $field) {
            if (array_key_exists($field, $body)) {
                $data[$field] = (string)($body[$field] ?? '');
            }
        }

        // 兼容前端字段别名
        if (!array_key_exists('type', $data)) {
            foreach (['menuType', 'menu_type'] as $alias) {
                if (array_key_exists($alias, $body) && $body[$alias] !== null && $body[$alias] !== '') {
                    $data['type'] = (int)$body[$alias];
                    break;
                }
            }
        }

        if ($isCreate) {
            $data['parent_id'] = $data['parent_id'] ?? (int)($body['parent_id'] ?? 0);
            $data['type'] = $data['type'] ?? (int)($body['type'] ?? 1);
            $data['sort'] = $data['sort'] ?? (int)($body['sort'] ?? 0);
            $data['is_hidden'] = $data['is_hidden'] ?? (int)($body['is_hidden'] ?? 2);
            $data['status'] = $data['status'] ?? (int)($body['status'] ?? 1);
            $data['is_iframe'] = $data['is_iframe'] ?? (int)($body['is_iframe'] ?? 2);
            $data['is_keep_alive'] = $data['is_keep_alive'] ?? (int)($body['is_keep_alive'] ?? 2);
            $data['is_fixed_tab'] = $data['is_fixed_tab'] ?? (int)($body['is_fixed_tab'] ?? 2);
            $data['is_full_page'] = $data['is_full_page'] ?? (int)($body['is_full_page'] ?? 2);
            $data['link_url'] = $data['link_url'] ?? (string)($body['link_url'] ?? '');
            $data['remark'] = $data['remark'] ?? (string)($body['remark'] ?? '');
        }

        return $data;
    }

    /**
     * 创建菜单
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/create', methods: ['POST'], name: 'menu.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:menu:save')]
    public function create(Request $request): BaseJsonResponse
    {
        $body = $this->getJsonBody($request);

        $data = $this->buildMenuPayload($body, true);
        $data['name'] = $data['name'] ?? (string)($body['name'] ?? '');
        $data['code'] = $data['code'] ?? (string)($body['code'] ?? '');
        $data['path'] = $data['path'] ?? (string)($body['path'] ?? '');
        $data['component'] = $data['component'] ?? (string)($body['component'] ?? '');
        $data['slug'] = $data['slug'] ?? (string)($body['slug'] ?? '');
        $data['icon'] = $data['icon'] ?? (string)($body['icon'] ?? '');

        // 参数验证
        if (empty($data['name'])) {
            return $this->fail('菜单名称不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $menu = $this->menuService->create($data, $operator);
            return $this->success(['id' => $menu->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新菜单
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/update/{id}', methods: ['PUT'], name: 'menu.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:menu:update')]
    public function update(Request $request): BaseJsonResponse
    {
        $id = $request->attributes->get('id');
        $body = $this->getJsonBody($request);

        $data = $this->buildMenuPayload($body, false);
        if ($data === []) {
            return $this->fail('没有可更新的字段');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->menuService->update((int)$id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除菜单
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/delete/{id}', methods: ['DELETE'], name: 'menu.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:menu:destroy')]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = $request->attributes->get('id');
        try {
            $this->menuService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新菜单状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/status/{id}', methods: ['PUT'], name: 'menu.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:menu:update')]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $body = $this->getJsonBody($request);
        $status = (int)($body['status'] ?? 1);

        $result = $this->menuService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取操作人ID
     *
     * @param Request $request 请求对象
     * @return int
     */
    protected function getOperatorId(Request $request): int
    {
        $user = $request->attributes->get('user');
        return $user['id'] ?? 0;
    }
}
