<?php

declare(strict_types=1);

/**
 * 系统配置分组DAO
 *
 * @package App\Dao
 * @author  Genie
 */

namespace App\Dao;

use App\Models\SysConfigGroup;
use Framework\Basic\BaseDao;

/**
 * SysConfigGroupDao 配置分组数据访问层
 *
 * 封装配置分组相关的数据查询操作
 */
class SysConfigGroupDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysConfigGroup::class;
    }

    /**
     * 检查配置标识是否已存在
     *
     * @param string $code      配置标识
     * @param int    $excludeId 排除的ID（用于修改时排除自身）
     * @return bool
     */
    public function isCodeExists(string $code, int $excludeId = 0): bool
    {
        $query = SysConfigGroup::where('code', $code);
        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
