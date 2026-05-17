<?php

declare(strict_types=1);

/**
 * 系统文章DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Dao;

use App\Models\SysArticle;
use Framework\Basic\BaseDao;

/**
 * SysArticleDao 文章数据访问层
 *
 * 封装文章相关的数据查询操作
 */
class SysArticleDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysArticle::class;
    }

    /**
     * 根据分类ID获取文章列表
     *
     * @param int $categoryId 分类ID
     * @param int $page       页码
     * @param int $limit      每页数量
     * @return array
     */
    public function getListByCategoryId(int $categoryId, int $page = 1, int $limit = 10): array
    {
        return $this->selectList(
            ['category_id' => $categoryId, 'status' => SysArticle::STATUS_ENABLED],
            '*',
            $page,
            $limit,
            'sort asc, create_time desc'
        )->toArray();
    }

    /**
     * 获取热门文章列表
     *
     * @param int $limit 数量限制
     * @return array
     */
    public function getHotArticles(int $limit = 10): array
    {
        return $this->selectList(
            ['is_hot' => SysArticle::IS_HOT_YES, 'status' => SysArticle::STATUS_ENABLED],
            '*',
            0,
            $limit,
            'views desc, create_time desc'
        )->toArray();
    }

    /**
     * 检查文章标题是否存在
     *
     * @param string $title     文章标题
     * @param int    $excludeId 排除的文章ID
     * @return bool
     */
    public function isTitleExists(string $title, int $excludeId = 0): bool
    {
        $where = ['title' => $title];
        if ($excludeId > 0) {
            return $this->be($where) && $this->value($where, 'id') != $excludeId;
        }
        return $this->be($where);
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
        return $this->update($articleId, ['status' => $status]);
    }

    /**
     * 获取文章总数
     *
     * @param array $where 条件
     * @return int
     */
    public function getArticleCount(array $where = []): int
    {
        return $this->count($where);
    }
}
