<?php

declare(strict_types=1);

/**
 * 系统租户DAO
 *
 * @package App\Dao
 */

namespace App\Dao;

use App\Models\SysTenant;
use Framework\Basic\BaseDao;

class SysTenantDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysTenant::class;
    }

    /**
     * 根据租户编码查找
     *
     * @param string $tenantCode
     * @return SysTenant|null
     */
    public function findByTenantCode(string $tenantCode): ?SysTenant
    {
        return $this->getOne(['tenant_code' => $tenantCode]);
    }

    /**
     * 检查租户编码是否存在
     *
     * @param string $tenantCode
     * @param int $excludeId
     * @return bool
     */
    public function isTenantCodeExists(string $tenantCode, int $excludeId = 0): bool
    {
        $query = SysTenant::query()->where('tenant_code', $tenantCode);

        if ($excludeId > 0) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->exists();
    }

    /**
     * 检查租户名称是否存在
     *
     * @param string $tenantName
     * @param int $excludeId
     * @return bool
     */
    public function isTenantNameExists(string $tenantName, int $excludeId = 0): bool
    {
        $query = SysTenant::query()->where('tenant_name', $tenantName);

        if ($excludeId > 0) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->exists();
    }

    /**
     * 更新租户状态
     *
     * @param int $tenantId
     * @param int $status
     * @return bool
     */
    public function updateStatus(int $tenantId, int $status): bool
    {
        return $this->update($tenantId, ['status' => $status]);
    }
}
