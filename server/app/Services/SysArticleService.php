<?php

declare(strict_types=1);

/**
 * 系统文章服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Services;

use App\Models\SysArticle;
use App\Dao\SysArticleDao;
use Framework\Basic\BaseService;

/**
 * SysArticleService 文章服务
 *
 * 处理文章相关的业务逻辑
  * @extends BaseService<SysArticleDao>
 */
class SysArticleService extends BaseService
{
    /**
     * DAO 实例
     * @var SysArticleDao
     * @return mixed
     */
    protected SysArticleDao $articleDao;

    /**
     * 构造函数
     * @return mixed
     */
    public function __construct()
    {
        parent::__construct();
        $this->articleDao = new SysArticleDao();
    }

    /**
     * 获取文章列表
     *
     * @param array<array-key, mixed> $params  查询参数
     * @param int   $page    页码
     * @param int   $pageSize 每页数量
     * @return array<array-key, mixed>
     */
    public function getList(array $params, int $page = 1, int $pageSize = 10): array
    {
        $categoryId = $params['category_id'] ?? '';
        $title      = $params['title'] ?? '';
        $author     = $params['author'] ?? '';
        $status     = $params['status'] ?? '';
        $isHot      = $params['is_hot'] ?? '';

        $query = SysArticle::query();

        // 分类筛选
        if ($categoryId !== '') {
            $query->where('category_id', (int)$categoryId);
        }

        // 标题模糊搜索
        if ($title !== '') {
            $query->where('title', 'LIKE', "%{$title}%");
        }

        // 作者模糊搜索
        if ($author !== '') {
            $query->where('author', 'LIKE', "%{$author}%");
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        // 热门筛选
        if ($isHot !== '') {
            $query->where('is_hot', (int)$isHot);
        }

        $total = $query->count();
        $list = $query->orderBy('sort', 'asc')
            ->orderBy('create_time', 'desc')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();

        foreach ($list as &$item) {
            $item = $this->formatArticle($item);
        }

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'size' => $pageSize,
        ];
    }

    /**
     * 获取文章详情
     *
     * @param int $articleId 文章ID
     * @return array<array-key, mixed>|null
     */
    public function getDetail(int $articleId): ?array
    {
        $article = SysArticle::find($articleId);
        
        if (!$article) {
            return null;
        }

        return $this->formatArticle($article);
    }

    /**
     * 创建文章
     *
     * @param array<array-key, mixed> $data     文章数据
     * @param int   $userId   用户ID
     * @param int   $deptId   部门ID
     * @param int   $tenantId 租户ID
     * @return SysArticle
     * @throws \Exception
     */
    public function create(array $data, int $userId, int $deptId, int $tenantId): SysArticle
    {
        // 验证必填字段
        if (empty($data['title'])) {
            throw new \Exception('文章标题不能为空');
        }

        if (empty($data['category_id'])) {
            throw new \Exception('文章分类不能为空');
        }

        // 验证外链
        if (isset($data['is_link']) && $data['is_link'] == SysArticle::IS_LINK_YES && empty($data['link_url'])) {
            throw new \Exception('外链文章必须提供链接地址');
        }

        // 验证内容
        if (isset($data['is_link']) && $data['is_link'] == SysArticle::IS_LINK_NO && empty($data['content'])) {
            throw new \Exception('非外链文章必须提供内容');
        }

        // 设置默认值和审计字段
        $data['dept_id'] = $deptId;
        $data['tenant_id'] = $tenantId;
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $data['views'] = 0;
        $data['status'] = $data['status'] ?? SysArticle::STATUS_ENABLED;
        $data['sort'] = $data['sort'] ?? 100;
        $data['is_hot'] = $data['is_hot'] ?? SysArticle::IS_HOT_NO;

        return SysArticle::create($data);
    }

    /**
     * 更新文章
     *
     * @param int   $articleId 文章ID
     * @param array<array-key, mixed> $data      文章数据
     * @param int   $userId    用户ID
     * @return bool
     * @throws \Exception
     */
    public function update(int $articleId, array $data, int $userId): bool
    {
        $article = SysArticle::find($articleId);
        
        if (!$article) {
            throw new \Exception('文章不存在');
        }

        // 不允许修改的字段
        unset($data['dept_id'], $data['created_by'], $data['tenant_id']);

        // 设置审计字段
        $data['updated_by'] = $userId;

        $article->fill($data);
        return $article->save();
    }

    /**
     * 删除文章（软删除）
     *
     * @param int $articleId 文章ID
     * @return bool
     */
    public function delete(int $articleId): bool
    {
        $article = SysArticle::find($articleId);
        
        if (!$article) {
            return false;
        }

        return $article->delete();
    }

    /**
     * 更新文章状态
     *
     * @param int $articleId 文章ID
     * @param int $status    状态
     * @return bool
     */
    public function updateStatus(int $articleId, int $status): bool
    {
        return $this->articleDao->updateStatus($articleId, $status);
    }

    /**
     * 格式化文章数据
     *
     * @param SysArticle|array<string, mixed> $article 文章
     * @return array<array-key, mixed>
     */
    protected function formatArticle(SysArticle|array $article): array
    {
        if ($article instanceof SysArticle) {
            $data = $article->toArray();
        } else {
            $data = $article;
        }

        // 格式化时间
        if (isset($data['create_time'])) {
            $data['create_time'] = is_string($data['create_time'])
                ? $data['create_time']
                : $data['create_time']->format('Y-m-d H:i:s');
        }

        if (isset($data['update_time'])) {
            $data['update_time'] = is_string($data['update_time'])
                ? $data['update_time']
                : $data['update_time']->format('Y-m-d H:i:s');
        }

        // 状态文本
        $data['status_text'] = $data['status'] === SysArticle::STATUS_ENABLED ? '启用' : '禁用';
        $data['is_hot_text'] = $data['is_hot'] === SysArticle::IS_HOT_YES ? '是' : '否';
        $data['is_link_text'] = $data['is_link'] === SysArticle::IS_LINK_YES ? '是' : '否';

        return $data;
    }
}