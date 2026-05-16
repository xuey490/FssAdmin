<?php

declare(strict_types=1);

/**
 * 系统部门DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Dao;

use App\Models\SysDept;
use Framework\Basic\BaseDao;

/**
 * SysDeptDao 部门数据访问层
 *
 * 封装部门相关的数据查询操作
 */
class SysDeptDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysDept::class;
    }

    /**
     * 根据部门编码查找部门
     *
     * @param string $deptCode 部门编码
     * @return SysDept|null
     */
    public function findByDeptCode(string $deptCode): ?SysDept
    {
        return $this->getOne(['code' => $deptCode]);
    }

    /**
     * 获取启用的部门列表
     *
     * @param int $page  页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getEnabledList(int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['status' => SysDept::STATUS_ENABLED], '*', $page, $limit, 'sort asc')->toArray();
    }

    /**
     * 获取所有启用的部门
     *
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->selectList(['status' => SysDept::STATUS_ENABLED], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 获取子部门列表
     *
     * @param int $parentId 父部门ID
     * @return array
     */
    public function getChildrenByParentId(int $parentId): array
    {
        return $this->selectList(['parent_id' => $parentId], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 检查部门编码是否存在
     *
     * @param string $deptCode  部门编码
     * @param int    $excludeId 排除的部门ID
     * @return bool
     */
    public function isDeptCodeExists(string $deptCode, int $excludeId = 0): bool
    {
        $where = ['code' => $deptCode];
        if ($excludeId > 0) {
            return $this->be($where) && $this->value($where, 'id') != $excludeId;
        }
        return $this->be($where);
    }

    /**
     * 更新部门状态
     *
     * @param int $deptId 部门ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $deptId, int $status): bool
    {
        return $this->update($deptId, ['status' => $status]);
    }

    /**
     * 获取部门总数
     *
     * @param array $where 条件
     * @return int
     */
    public function getDeptCount(array $where = []): int
    {
        return $this->count($where);
    }

    /**
     * 获取部门ID列表
     *
     * @param array $where 条件
     * @return array
     */
    public function getDeptIds(array $where = []): array
    {
        return $this->getColumn($where, 'id');
    }

    /**
     * 检查部门是否有子部门
     *
     * @param int $deptId 部门ID
     * @return bool
     */
    public function hasChildren(int $deptId): bool
    {
        return $this->be(['parent_id' => $deptId]);
    }
}
