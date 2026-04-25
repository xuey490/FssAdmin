<?php

declare(strict_types=1);

/**
 * 博客文章控制器
 *
 * @package Plugins\Blog\Controllers
 */

namespace Plugins\Blog\Controllers;

use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Symfony\Component\HttpFoundation\Request;

/**
 * 文章控制器
 */
class PostController extends BaseController
{
    /**
     * 获取文章列表
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/blog/posts', methods: ['GET'], name: 'blog.post.list')]
    #[Auth(required: true)]
    public function list(Request $request): BaseJsonResponse
    {
        $page = (int) $this->input('page', 1);
        $limit = (int) $this->input('limit', 20);
        $status = $this->input('status', '');
        $keyword = $this->input('keyword', '');

        // TODO: 实现实际的列表查询逻辑
        $result = [
            'total' => 100,
            'page' => $page,
            'limit' => $limit,
            'list' => [
                [
                    'id' => 1,
                    'title' => '示例文章标题',
                    'summary' => '这是文章摘要...',
                    'status' => 'published',
                    'view_count' => 123,
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ],
        ];

        return $this->success($result);
    }

    /**
     * 获取文章详情
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/blog/posts/{id}', methods: ['GET'], name: 'blog.post.detail')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');

        // TODO: 实现实际的详情查询逻辑
        $post = [
            'id' => $id,
            'title' => '示例文章标题',
            'content' => '这是文章内容...',
            'status' => 'published',
            'view_count' => 123,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->success($post);
    }

    /**
     * 创建文章
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/blog/posts', methods: ['POST'], name: 'blog.post.create')]
    #[Auth(required: true)]
    public function create(Request $request): BaseJsonResponse
    {
        $title = $this->input('title', '');
        $content = $this->input('content', '');
        $status = $this->input('status', 'draft');

        if (empty($title)) {
            return $this->fail('文章标题不能为空');
        }

        // TODO: 实现实际的创建逻辑

        return $this->success([
            'id' => 1,
        ], '文章创建成功');
    }

    /**
     * 更新文章
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/blog/posts/{id}', methods: ['PUT'], name: 'blog.post.update')]
    #[Auth(required: true)]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');

        // TODO: 实现实际的更新逻辑

        return $this->success([], '文章更新成功');
    }

    /**
     * 删除文章
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/blog/posts/{id}', methods: ['DELETE'], name: 'blog.post.delete')]
    #[Auth(required: true)]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');

        // TODO: 实现实际的删除逻辑

        return $this->success([], '文章删除成功');
    }

    /**
     * 发布文章
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/blog/posts/{id}/publish', methods: ['PUT'], name: 'blog.post.publish')]
    #[Auth(required: true)]
    public function publish(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');

        // TODO: 实现实际的发布逻辑

        return $this->success([], '文章发布成功');
    }
}
