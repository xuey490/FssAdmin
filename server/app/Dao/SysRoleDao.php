<?php

declare(strict_types=1);

/**
 * 系统角色DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Dao;

use App\Models\SysRole;
use Framework\Basic\BaseDao;

/**
 * SysRoleDao 角色数据访问层
 *
 * 封装角色相关的数据查询操作
 */
class SysRoleDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysRole::class;
    }

    /**
     * 根据角色编码查找角色
     *
     * @param string $roleCode 角色编码
     * @return SysRole|null
     */
    public function findByRoleCode(string $roleCode): ?SysRole
    {
        return $this->getOne(['code' => $roleCode]);
    }

    /**
     * 获取启用的角色列表
     *
     * @param int $page  页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getEnabledList(int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['status' => SysRole::STATUS_ENABLED], '*', $page, $limit, 'sort asc')->toArray();
    }

    /**
     * 获取所有启用的角色
     *
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->selectList(['status' => SysRole::STATUS_ENABLED], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 获取子角色列表
     *
     * @param int $parentId 父角色ID
     * @return array
     */
    public function getChildrenByParentId(int $parentId): array
    {
        return $this->selectList(['parent_id' => $parentId], '*', 0, 0, 'sort asc')->toArray();
    }

    /**
     * 检查角色编码是否存在
     *
     * @param string $roleCode  角色编码
     * @param int    $excludeId 排除的角色ID
     * @return bool
     */
    public function isRoleCodeExists(string $roleCode, int $excludeId = 0): bool
    {
        $where = ['code' => $roleCode];
        if ($excludeId > 0) {
            return $this->be($where) && $this->value($where, 'id') != $excludeId;
        }
        return $this->be($where);
    }

    /**
     * 更新角色状态
     *
     * @param int $roleId 角色ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $roleId, int $status): bool
    {
        return $this->update($roleId, ['status' => $status]);
    }

    /**
     * 获取角色总数
     *
     * @param array $where 条件
     * @return int
     */
    public function getRoleCount(array $where = []): int
    {
        return $this->count($where);
    }

    /**
     * 获取角色ID列表
     *
     * @param array $where 条件
     * @return array
     */
    public function getRoleIds(array $where = []): array
    {
        return $this->getColumn($where, 'id');
    }
}
