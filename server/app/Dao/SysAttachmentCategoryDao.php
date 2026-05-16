<?php

declare(strict_types=1);

/**
 * 附件分类DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Dao;

use App\Models\SysAttachmentCategory;
use Framework\Basic\BaseDao;

/**
 * SysAttachmentCategoryDao 附件分类数据访问层
 */
class SysAttachmentCategoryDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysAttachmentCategory::class;
    }

    /**
     * 获取启用的分类列表
     *
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->selectList(['status' => SysAttachmentCategory::STATUS_ENABLED], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 获取子分类列表
     *
     * @param int $parentId 父分类ID
     * @return array
     */
    public function getChildrenByParentId(int $parentId): array
    {
        return $this->selectList(['parent_id' => $parentId], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 检查分类编码是否存在
     *
     * @param string $categoryCode 分类编码
     * @param int    $excludeId    排除的ID
     * @return bool
     */
    public function isCategoryCodeExists(string $categoryCode, int $excludeId = 0): bool
    {
        $where = ['category_code' => $categoryCode];
        if ($excludeId > 0) {
            return $this->be($where) && $this->value($where, 'id') != $excludeId;
        }
        return $this->be($where);
    }

    /**
     * 检查是否有子分类
     *
     * @param int $categoryId 分类ID
     * @return bool
     */
    public function hasChildren(int $categoryId): bool
    {
        return $this->be(['parent_id' => $categoryId]);
    }
}
