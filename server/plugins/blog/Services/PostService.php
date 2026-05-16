<?php

declare(strict_types=1);

/**
 * 博客文章服务
 *
 * @package Plugins\Blog\Services
 */

namespace Plugins\Blog\Services;

use Framework\Basic\BaseService;
use Plugins\Blog\Models\Post;
use Plugins\Blog\Models\Category;
use Plugins\Blog\Models\Tag;

/**
 * 文章服务
 */
class PostService extends BaseService
{
    /**
     * 获取文章列表
     *
     * @param array $params
     * @return array
     */
    public function getList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;
        $status = $params['status'] ?? '';
        $keyword = $params['keyword'] ?? '';
        $categoryId = $params['category_id'] ?? '';

        $query = Post::query();

        // 状态筛选
        if (!empty($status)) {
            $query->where('status', $status);
        }

        // 关键词搜索
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('content', 'like', "%{$keyword}%");
            });
        }

        // 分类筛选
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // 排序
        $query->orderBy('is_top', 'desc')
              ->orderBy('published_at', 'desc');

        // 分页
        $total = $query->count();
        $list = $query->offset(($page - 1) * $limit)
                      ->limit($limit)
                      ->get();

        return [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'list' => $list,
        ];
    }

    /**
     * 获取文章详情
     *
     * @param int $id
     * @return Post|null
     */
    public function getDetail(int $id): ?Post
    {
        return Post::with(['category', 'tags'])->find($id);
    }

    /**
     * 创建文章
     *
     * @param array $data
     * @return Post
     */
    public function create(array $data): Post
    {
        return Post::create($data);
    }

    /**
     * 更新文章
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return Post::where('id', $id)->update($data) > 0;
    }

    /**
     * 删除文章
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return Post::destroy($id) > 0;
    }

    /**
     * 发布文章
     *
     * @param int $id
     * @return bool
     */
    public function publish(int $id): bool
    {
        return $this->update($id, [
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 增加浏览量
     *
     * @param int $id
     * @return bool
     */
    public function incrementViewCount(int $id): bool
    {
        return Post::where('id', $id)->increment('view_count') > 0;
    }
}
