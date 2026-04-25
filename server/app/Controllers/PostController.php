<?php

declare(strict_types=1);

/**
 * 岗位管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Controllers;

use App\Services\SysPostService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;
#use Framework\Attributes\Middlewares;

/**
 * PostController 岗位管理控制器
 *
 * 处理岗位的增删改查等操作
 */
class PostController extends BaseController
{
    /**
     * 岗位服务
     * @var SysPostService
     */
    protected SysPostService $postService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->postService = new SysPostService();
    }

    /**
     * 获取岗位列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/list', methods: ['GET'], name: 'post.list')]
    #[Auth(required: true)]
    #[Permission('core:post:index')]
    ##[Middlewares([\App\Middlewares\PermissionMiddleware::class])]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'code' => $this->input('code', '', true, $request),
            'name' => $this->input('name', '', true, $request),
            'status' => $this->input('status', '', true, $request),
        ];

        $result = $this->postService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取岗位详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/detail/{id}', methods: ['GET'], name: 'post.detail')]
    #[Auth(required: true)]
    #[Permission('core:post:read')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $result = $this->postService->getDetail($id);

        if (!$result) {
            return $this->fail('岗位不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建岗位
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/create', methods: ['POST'], name: 'post.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:post:save')]
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
        $data = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        if (empty($data['name'])) {
            return $this->fail('岗位名称不能为空');
        }

        if (empty($data['code'])) {
            return $this->fail('岗位编码不能为空');
        }

        $data['sort'] = (int)($data['sort'] ?? 0);
        $data['status'] = (int)($data['status'] ?? 1);
        $data['remark'] = $data['remark'] ?? '';

        $operator = $this->getOperatorId($request);

        try {
            $post = $this->postService->create($data, $operator);
            return $this->success(['id' => $post->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新岗位
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/update/{id}', methods: ['PUT'], name: 'post.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:post:update')]
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
        $data = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        $operator = $this->getOperatorId($request);

        try {
            $this->postService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除岗位
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/delete/{id}', methods: ['DELETE'], name: 'post.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:post:destroy')]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        try {
            $this->postService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新岗位状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/status/{id}', methods: ['PUT'], name: 'post.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:post:update')]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $status = (int)$this->input('status', 1);

        $result = $this->postService->updateEnabled($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取所有启用的岗位
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/enabled', methods: ['GET'], name: 'post.enabled')]
    #[Auth(required: true)]
    #[Permission('core:post:index')]
    public function getEnabled(Request $request): BaseJsonResponse
    {
        $result = $this->postService->getAllEnabled();

        return $this->success($result);
    }

    /**
     * 获取可访问的岗位列表（供用户编辑弹窗下拉选择使用）
     *
     * 返回包含 id、name 字段的扁平数组
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/access-post', methods: ['GET'], name: 'post.accessPost')]
    #[Auth(required: true)]
    #[Permission('core:post:index')]
    public function accessPost(Request $request): BaseJsonResponse
    {
        $result = $this->postService->getAccessPostList();

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
