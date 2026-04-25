<?php

declare(strict_types=1);

/**
 * 部门管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysDeptService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Role;
use Framework\Attributes\Permission;
use Framework\Attributes\Cache;
/**
 * DeptController 部门管理控制器
 *
 * 处理部门的增删改查等操作
 */
class DeptController extends BaseController
{
    /**
     * 部门服务
     * @var SysDeptService
     */
    protected SysDeptService $deptService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->deptService = new SysDeptService();
    }

    /**
     * 获取可访问的部门树（供用户管理左侧树使用）
     *
     * 返回带 label 字段的树结构，适配前端 ElTree 组件
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/access-dept', methods: ['GET'], name: 'dept.accessDept')]
    ##[Permission(['core:user:index', 'core:dept:index'], mode: 'OR')]
    #[Permission('core:dept:index')]
    public function accessDept(Request $request): BaseJsonResponse
    {
        $result = $this->deptService->getAccessDeptTree();

        return $this->success($result);
    }

    /**
     * 获取所有启用的部门（扁平列表，供下拉选择使用）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/all-enabled', methods: ['GET'], name: 'dept.allEnabled')]
    #[Auth(required: true)]
    #[Permission('core:dept:index')]
    public function allEnabled(Request $request): BaseJsonResponse
    {
        $result = $this->deptService->getAllEnabled();
        return $this->success($result);
    }

    /**
     * 获取部门列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/list', methods: ['GET'], name: 'dept.list')]
	##[Auth(required: true, roles: ['admin', 'super_admin'])]
	##[Role(['admin'])]
    #[Permission('core:dept:index')]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'name' => $this->input('name', '', true, $request),
            'code' => $this->input('code', '', true, $request),
            'status' => $this->input('status', '', true, $request),
        ];

        $result = $this->deptService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取部门树（带 label/value 字段，适配前端 ElTreeSelect）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/tree', methods: ['GET'], name: 'dept.tree')]
    #[Auth(required: true)]
    #[Permission('core:dept:tree')]
    public function tree(Request $request): BaseJsonResponse
    {
        $result = $this->deptService->getSelectTree();

        return $this->success($result);
    }

    /**
     * 获取部门详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/detail/{id}', methods: ['GET'], name: 'dept.detail')]
    #[Auth(required: true)]
    #[Permission('core:dept:read')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $result = $this->deptService->getDetail($id);

        if (!$result) {
            return $this->fail('部门不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建部门
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/create', methods: ['POST'], name: 'dept.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dept:save')]
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
            'parent_id' => (int)($all['parent_id'] ?? 0),
            'name' => $all['name'] ?? '',
            'code' => $all['code'] ?? '',
            'leader_id' => (int)($all['leader_id'] ?? 0),
            'phone' => $all['phone'] ?? '',
            'email' => $all['email'] ?? '',
            'sort' => (int)($all['sort'] ?? 100),
            'status' => (int)($all['status'] ?? 1),
            'remark' => $all['remark'] ?? '',
        ];


        // 参数验证
        if (empty($data['name'])) {
            return $this->fail('部门名称不能为空');
        }

        if (empty($data['code'])) {
            return $this->fail('部门编码不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $dept = $this->deptService->create($data, $operator);
            return $this->success(['id' => $dept->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新部门
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/update/{id}', methods: ['PUT'], name: 'dept.update')]
    #[Auth(required: true, roles: ['admin'])]
    #[Permission('core:dept:update')]
    ##[Role(['super_adminxx'])]
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
            'parent_id' => isset($all['parent_id']) && $all['parent_id'] !== '' ? (int)$all['parent_id'] : null,
            'name' => $all['name'] ?? '',
            'code' => $all['code'] ?? '',
            'leader_id' => isset($all['leader_id']) && $all['leader_id'] !== '' ? (int)$all['leader_id'] : null,
            'phone' => $all['phone'] ?? '',
            'email' => $all['email'] ?? '',
            'sort' => isset($all['sort']) && $all['sort'] !== '' ? (int)$all['sort'] : null,
            'status' => isset($all['status']) && $all['status'] !== '' ? (int)$all['status'] : null,
            'remark' => $all['remark'] ?? '',
        ];



        // 过滤null值，但保留0值（如status=0）
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->deptService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除部门
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/delete/{id}', methods: ['DELETE'], name: 'dept.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dept:destroy')]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        //error_log(json_encode(['delete_dept_id' => $id]));
        try {
            $this->deptService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            //error_log($e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新部门状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/status/{id}', methods: ['PUT'], name: 'dept.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dept:update')]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $status = (int)$this->input('status', 1);


        $result = $this->deptService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取所有子部门ID
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/children/{id}', methods: ['GET'], name: 'dept.children')]
    #[Auth(required: true)]
    #[Permission('core:dept:index')]
    public function getChildren(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $result = $this->deptService->getAllChildIds($id);

        return $this->success($result);
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
