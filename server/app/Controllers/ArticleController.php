<?php

declare(strict_types=1);

/**
 * 文章管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Controllers;

use App\Services\SysArticleService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Framework\Tenant\TenantContext;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;
use Symfony\Component\HttpFoundation\Request;

/**
 * ArticleController 文章管理控制器
 *
 * 处理文章的增删改查等操作
 */
class ArticleController extends BaseController
{
    /**
     * 文章服务
     * @var SysArticleService
     * @return mixed
     */
    protected SysArticleService $articleService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->articleService = new SysArticleService();
    }

    /**
     * 获取文章列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/list', methods: ['GET'], name: 'article.list')]
    #[Auth(required: true)]
    #[Permission('cms:article:index')]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'category_id' => $this->input('category_id', '', true, $request),
            'title' => $this->input('title', '', true, $request),
            'author' => $this->input('author', '', true, $request),
            'status' => $this->input('status', '', true, $request),
            'is_hot' => $this->input('is_hot', '', true, $request),
        ];

        $page = (int) $this->input('page', 1, true, $request);
        $pageSize = (int) $this->input('page_size', 10, true, $request);

        $result = $this->articleService->getList($params, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 获取文章详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/detail/{id}', methods: ['GET'], name: 'article.detail')]
    #[Auth(required: true)]
    #[Permission('cms:article:index')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        
        $article = $this->articleService->getDetail($id);

        if (!$article) {
            return $this->fail('文章不存在', 404);
        }

        // 增加浏览次数
        $articleModel = \App\Models\SysArticle::find($id);
        $articleModel?->incrementViewCount();

        return $this->success($article);
    }

    /**
     * 创建文章
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/create', methods: ['POST'], name: 'article.create')]
    #[Auth(required: true)]
    #[Permission('cms:article:save')]
    public function create(Request $request): BaseJsonResponse
    {
        $userId = $this->getOperatorId($request);
        $deptId = $this->getCurrentUserDeptId($request);
        $tenantId = TenantContext::getTenantId();

        $data = $this->getRequestData($request);

        try {
            $article = $this->articleService->create($data, $userId, $deptId, $tenantId);
            return $this->success(['id' => $article->id], '创建成功', 201);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新文章
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/update/{id}', methods: ['PUT'], name: 'article.update')]
    #[Auth(required: true)]
    #[Permission('cms:article:update')]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $userId = $this->getOperatorId($request);

        $data = $this->getRequestData($request);

        try {
            $this->articleService->update($id, $data, $userId);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除文章
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/delete/{id}', methods: ['DELETE'], name: 'article.delete')]
    #[Auth(required: true)]
    #[Permission('cms:article:destroy')]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');

        try {
            $this->articleService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新文章状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/status/{id}', methods: ['PUT'], name: 'article.status')]
    #[Auth(required: true)]
    #[Permission('cms:article:update')]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $data = $this->getRequestData($request);
        $status = array_key_exists('status', $data) ? (int) $data['status'] : 1;

        $result = $this->articleService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取请求数据（支持 JSON 和 form-data）
     *
     * @param Request $request 请求对象
     * @return array<array-key, mixed>
     */
    protected function getRequestData(Request $request): array
    {
        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        return array_merge($request->query->all(), $request->request->all(), $jsonBody);
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
     * 获取当前用户部门ID
     *
     * @param Request $request 请求对象
     * @return int
     */
    protected function getCurrentUserDeptId(Request $request): int
    {
        $userId = $this->getOperatorId($request);
        if (!$userId) {
            return 0;
        }
        $user = \App\Models\SysUser::find($userId);
        return $user->dept_id ?? 0;
    }
}