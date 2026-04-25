<?php

declare(strict_types=1);

/**
 * 角色管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysRoleService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;
#use Framework\Attributes\Middlewares;

/**
 * RoleController 角色管理控制器
 *
 * 处理角色的增删改查等操作
 */
class RoleController extends BaseController
{
    /**
     * 角色服务
     * @var SysRoleService
     */
    protected SysRoleService $roleService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->roleService = new SysRoleService();
    }

    /**
     * 获取角色列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/list', methods: ['GET'], name: 'role.list')]
    #[Auth(required: true)]
    #[Permission('core:role:index')]
    ##[Middlewares([\App\Middlewares\PermissionMiddleware::class])]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int)$this->input('page', 1, true, $request),
            'limit' => (int)$this->input('limit', 20, true, $request),
            'name' => $this->input('name', '', true, $request) ?: $this->input('role_name', '', true, $request),
            'code' => $this->input('code', '', true, $request) ?: $this->input('role_code', '', true, $request),
            'status' => $this->input('status', '', true, $request),
        ];

        $result = $this->roleService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取所有启用的角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/all', methods: ['GET'], name: 'role.all')]
    #[Auth(required: true)]
    #[Permission('core:role:index')]
    public function all(Request $request): BaseJsonResponse
    {
        $result = $this->roleService->getAllEnabled();

        return $this->success($result);
    }

    /**
     * 获取可访问的角色列表（供用户编辑弹窗下拉选择使用）
     *
     * 返回包含 id、name 字段的扁平数组
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/access-role', methods: ['GET'], name: 'role.accessRole')]
    #[Auth(required: true)]
    #[Permission('core:role:index')]
    public function accessRole(Request $request): BaseJsonResponse
    {
        $result = $this->roleService->getAccessRoleList();

        return $this->success($result);
    }

    /**
     * 获取角色树
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/tree', methods: ['GET'], name: 'role.tree')]
    #[Auth(required: true)]
    #[Permission('core:role:index')]
    public function tree(Request $request): BaseJsonResponse
    {
        $result = $this->roleService->getRoleTree();

        return $this->success($result);
    }

    /**
     * 获取角色详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/detail/{id}', methods: ['GET'], name: 'role.detail')]
    #[Auth(required: true)]
    #[Permission('core:role:read')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $result = $this->roleService->getDetail($id);

        if (!$result) {
            return $this->fail('角色不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/create', methods: ['POST'], name: 'role.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:role:save')]
    public function create(Request $request): BaseJsonResponse
    {
        $body = $this->getJsonBody($request);

        $data = [
            'name' => $body['name'] ?? '',
            'code' => $body['code'] ?? '',
            'level' => (int)($body['level'] ?? 0),
            'sort' => (int)($body['sort'] ?? 0),
            'status' => (int)($body['status'] ?? 1),
            'remark' => $body['remark'] ?? '',
            'data_scope' => (int)($body['data_scope'] ?? 1),
            'menu_ids' => $body['menu_ids'] ?? [],
            'dept_ids' => $body['dept_ids'] ?? [],
        ];

        // 参数验证
        if (empty($data['name'])) {
            return $this->fail('角色名称不能为空');
        }

        if (empty($data['code'])) {
            return $this->fail('角色编码不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $role = $this->roleService->create($data, $operator);
            return $this->success(['id' => $role->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/update/{id}', methods: ['PUT'], name: 'role.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:role:update')]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $body = $this->getJsonBody($request);

        $data = [
            'name' => $body['name'] ?? null,
            'code' => $body['code'] ?? null,
            'level' => isset($body['level']) ? (int)$body['level'] : null,
            'sort' => isset($body['sort']) ? (int)$body['sort'] : null,
            'status' => isset($body['status']) ? (int)$body['status'] : null,
            'remark' => $body['remark'] ?? null,
            'data_scope' => isset($body['data_scope']) ? (int)$body['data_scope'] : null,
            'menu_ids' => $body['menu_ids'] ?? null,
            'dept_ids' => $body['dept_ids'] ?? null,
        ];



        // 过滤空值
        $data = array_filter($data, fn($v) => $v !== null);

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->roleService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/delete/{id}', methods: ['DELETE'], name: 'role.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:role:destroy')]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        try {
            $this->roleService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新角色状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/status/{id}', methods: ['PUT'], name: 'role.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:role:update')]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $body = $this->getJsonBody($request);
        $status = (int)($body['status'] ?? 1);

        $result = $this->roleService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 分配菜单给角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/assign-menus/{id}', methods: ['PUT'], name: 'role.assignMenus')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:role:menu')]
    public function assignMenus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $body = $this->getJsonBody($request);
        $menuIds = $body['menu_ids'] ?? [];
        $operator = $this->getOperatorId($request);

        try {
            $this->roleService->assignMenus($id, $menuIds, $operator);
            return $this->success([], '菜单分配成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取角色已分配的菜单ID列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/menu-by-role/{id}', methods: ['GET'], name: 'role.menuByRole')]
    #[Auth(required: true)]
    #[Permission('core:role:read')]
    public function menuByRole(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $menuIds = $this->roleService->getMenuIds($id);

        // 返回包含 id 的菜单对象数组，与前端 data.menus 对齐
        $menus = array_map(fn($menuId) => ['id' => $menuId], $menuIds);

        return $this->success(['menus' => $menus]);
    }

    /**
     * 保存角色菜单权限（与 assignMenus 功能相同，兼容前端调用）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/menu-permission/{id}', methods: ['PUT'], name: 'role.menuPermission')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:role:menu')]
    public function menuPermission(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $body = $this->getJsonBody($request);
        $menuIds = $body['menu_ids'] ?? [];
        $operator = $this->getOperatorId($request);

        try {
            $this->roleService->assignMenus($id, $menuIds, $operator);
            return $this->success([], '菜单权限保存成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
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
