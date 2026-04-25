<?php

declare(strict_types=1);

/**
 * 用户管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysUserService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

/**
 * UserController 用户管理控制器
 *
 * 处理用户的增删改查等操作
 */
class UserController extends BaseController
{
    /**
     * 用户服务
     * @var SysUserService
     */
    protected SysUserService $userService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->userService = new SysUserService();
    }

    /**
     * 获取用户列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/list', methods: ['GET'], name: 'user.list')]
    #[Auth(required: true)]
    #[Permission('core:user:index')]
    public function list(Request $request): BaseJsonResponse
    {
        // 获取请求参数（支持 application/json 和 form-data）
        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        $params = [
            'page' => (int)$this->input('page', 1 , true ,$request),
            'limit' => (int)$this->input('limit', 20 , true ,$request),
            'username' => $this->input('username', '' , true ,$request),
            'phone' => $this->input('phone', '' , true ,$request),
            'status' => $this->input('status', '', true ,$request),
            'dept_id' => $this->input('dept_id', '', true ,$request),
            'current_user_id' => $this->getOperatorId($request),
        ];

        $result = $this->userService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取用户详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/detail/{id}', methods: ['GET'], name: 'user.detail')]
    #[Auth(required: true)]
    #[Permission('core:user:read')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $result = $this->userService->getDetail($id);

        if (!$result) {
            return $this->fail('用户不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建用户
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/create', methods: ['POST'], name: 'user.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:user:save')]
    public function create(Request $request): BaseJsonResponse
    {
        // 获取请求参数（支持 application/json 和 form-data）
        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        $data = [
            'username' => $all['username'] ?? '',
            'password' => $all['password'] ?? '',
            'realname' => $all['realname'] ?? '',
            'email' => $all['email'] ?? '',
            'phone' => $all['phone'] ?? '',
            'avatar' => $all['avatar'] ?? '',
            'gender' => $all['gender'] ?? '',
            'dept_id' => (int)($all['dept_id'] ?? 0),
            'status' => (int)($all['status'] ?? 1),
            'remark' => $all['remark'] ?? '',
            'role_ids' => $all['role_ids'] ?? [],
            'post_ids' => $all['post_ids'] ?? [],
            'menu_ids' => $all['menu_ids'] ?? [],
        ];


        // 参数验证
        if (empty($data['username'])) {
            return $this->fail('用户名不能为空');
        }

        if (empty($data['password'])) {
            return $this->fail('密码不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $user = $this->userService->create($data, $operator);
            return $this->success(['id' => $user->id], '创建成功');
        } catch (\Exception $e) {
            dump('创建用户失败: ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新用户
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/update/{id}', methods: ['PUT'], name: 'user.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:user:update')]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        // 获取请求参数（支持 application/json 和 form-data）
        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        $data = [
            'realname' => $all['realname'] ?? '',
            'email' => $all['email'] ?? '',
            'phone' => $all['phone'] ?? '',
            'avatar' => $all['avatar'] ?? '',
            'gender' => $all['gender'] ?? '',
            'dept_id' => (int)($all['dept_id'] ?? 0),
            'status' => $all['status'] !== null && $all['status'] !== '' ? (int)$all['status'] : null,
            'remark' => $all['remark'] ?? '',
            'password' => $all['password'] ?? '',
            'role_ids' => $all['role_ids'] ?? [],
            'post_ids' => $all['post_ids'] ?? [],
            'menu_ids' => $all['menu_ids'] ?? [],
        ];

        // 过滤空值（保留数组类型字段，空密码表示不修改）
        $data = array_filter($data, function($v) {
            if (is_array($v)) return true;
            return $v !== null && $v !== '';
        });
        
        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->userService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Throwable $e) {
            error_log("[UserController::update] 用户更新失败 user_id={$id} error={$e->getMessage()} file={$e->getFile()}:{$e->getLine()}");
            error_log("[UserController::update] trace: {$e->getTraceAsString()}");
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除用户
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/delete/{id}', methods: ['DELETE'], name: 'user.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:user:destroy')]
    public function delete(Request $request): BaseJsonResponse
    {

        $id = (int)$request->attributes->get('id');

        if ($id === 0) {
            $ids = $this->parseIds($request);
            if (!empty($ids)) {
                $id = $ids[0];
            }
            if ($id === 0) {
                return $this->fail('Id不能为空!');
            }
        }
        
        // 不能删除自己
        $operatorId = $this->getOperatorId($request);
        if ($id === $operatorId) {
            return $this->fail('不能删除自己');
        }

        try {
            $this->userService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Throwable $e) {
            error_log("[UserController::delete] 用户删除失败 user_id={$id} error={$e->getMessage()} file={$e->getFile()}:{$e->getLine()}");

            error_log("[UserController::delete] trace: {$e->getTraceAsString()}");
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新用户状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/status/{id}', methods: ['PUT'], name: 'user.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:user:update')]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $status = (int)$this->input('status', 1);

        // 不能禁用自己
        $operatorId = $this->getOperatorId($request);
        if ($id === $operatorId) {
            return $this->fail('不能禁用自己');
        }

        $result = $this->userService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 重置密码
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/reset-password/{id}', methods: ['PUT'], name: 'user.resetPassword')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:user:password')]
    public function resetPassword(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $password = $this->input('password', '123456');

        if (strlen($password) < 6) {
            return $this->fail('密码长度不能少于6位');
        }

        $result = $this->userService->resetPassword($id, $password);

        return $result
            ? $this->success([], '密码重置成功')
            : $this->fail('密码重置失败');
    }

    /**
     * 修改密码（直接设置新密码，由管理员操作）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/change-password/{id}', methods: ['PUT'], name: 'user.changePassword')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:user:password')]
    public function changePassword(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $password = $this->input('password', '', true, $request);

        error_log("[UserController::changePassword] password=" . password_hash($password, PASSWORD_BCRYPT));

        if (strlen($password) < 6) {
            return $this->fail('密码长度不能少于6位');
        }

        try {
            $this->userService->resetPassword($id, $password);
            return $this->success([], '修改密码成功');
        } catch (\Exception $e) {
            //error_log("[UserController::changePassword] error={$e->getMessage()} file={$e->getFile()}:{$e->getLine()}");

            //error_log("[UserController::changePassword] trace: {$e->getTraceAsString()}");
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 清理用户缓存
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/clear-cache/{id}', methods: ['PUT'], name: 'user.clearCache')]
    #[Auth(required: true)]
    #[Permission('core:user:cache')]
    public function clearCache(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        try {
            $this->userService->clearCache($id);
            return $this->success([], '缓存清理成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取用户已分配的菜单ID列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/menus/{id}', methods: ['GET'], name: 'user.getMenus')]
    #[Auth(required: true)]
    #[Permission('core:user:read')]
    public function getMenus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $menuIds = $this->userService->getUserMenuIds($id);
        return $this->success($menuIds);
    }

    /**
     * 保存用户菜单分配
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/menus/{id}', methods: ['PUT'], name: 'user.saveMenus')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:user:update')]
    public function saveMenus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }

        if ($id === 0) {
            return $this->fail('Id不能为空!');
        }
        
        $menuIds = $jsonBody['menu_ids'] ?? [];
        $operator = $this->getOperatorId($request);

        try {
            $this->userService->saveUserMenus($id, $menuIds, $operator);
            return $this->success([], '分配菜单成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 设置用户首页/工作台
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/set-home-page/{id}', methods: ['PUT'], name: 'user.setHomePage')]
    #[Auth(required: true)]
    #[Permission('core:user:home')]
    public function setHomePage(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $dashboard = $this->input('dashboard', '', true, $request);

        if (empty($dashboard)) {
            return $this->fail('工作台不能为空');
        }

        try {
            $this->userService->setHomePage($id, $dashboard);
            return $this->success([], '设置成功');
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
    /**
     * 从请求中解析 ID 列表
     * 支持 { ids: [1,2] } 或 { id: 1 } 两种格式
     *
     * @param Request $request
     * @return array
     */
    protected function parseIds(Request $request): array
    {
        $body = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }
        $all = array_merge($request->request->all(), $body);

        if (!empty($all['ids']) && is_array($all['ids'])) {
            return array_map('intval', $all['ids']);
        }
        if (!empty($all['id'])) {
            return [(int)$all['id']];
        }
        return [];
    }        
}
