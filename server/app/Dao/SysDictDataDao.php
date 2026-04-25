<?php

declare(strict_types=1);

/**
 * 数据字典数据DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Dao;

use App\Models\SysDictData;
use Framework\Basic\BaseDao;

/**
 * SysDictDataDao 数据字典数据数据访问层
 */
class SysDictDataDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysDictData::class;
    }

    /**
     * 根据字典类型ID获取数据列表
     *
     * @param int $dictTypeId 字典类型ID
     * @return array
     */
    public function getListByDictTypeId(int $dictTypeId): array
    {
        return $this->selectList(
            ['type_id' => $dictTypeId, 'status' => SysDictData::STATUS_ENABLED],
            '*',
            0,
            0,
            'sort asc'
        )->toArray();
    }

    /**
     * 根据字典编码获取数据列表
     *
     * @param string $dictCode 字典编码
     * @return array
     */
    public function getListByDictCode(string $dictCode): array
    {
        $dictType = \App\Models\SysDictType::where('code', $dictCode)
            ->where('status', \App\Models\SysDictType::STATUS_ENABLED)
            ->first();

        if (!$dictType) {
            return [];
        }

        return $this->getListByDictTypeId($dictType->id);
    }

    /**
     * 检查字典值是否存在
     *
     * @param int    $dictTypeId 字典类型ID
     * @param string $dictValue  字典值
     * @param int    $excludeId  排除的ID
     * @return bool
     */
    public function isDictValueExists(int $dictTypeId, string $dictValue, int $excludeId = 0): bool
    {
        $where = ['type_id' => $dictTypeId, 'value' => $dictValue];
        if ($excludeId > 0) {
            return $this->be($where) && $this->value($where, 'id') != $excludeId;
        }
        return $this->be($where);
    }

    /**
     * 删除字典类型下的所有数据
     *
     * @param int $dictTypeId 字典类型ID
     * @return bool
     */
    public function deleteByDictTypeId(int $dictTypeId): bool
    {
        return $this->delete(['type_id' => $dictTypeId]) !== false;
    }

    /**
     * 获取字典标签
     *
     * @param int    $dictTypeId 字典类型ID
     * @param string $dictValue  字典值
     * @return string
     */
    public function getDictLabel(int $dictTypeId, string $dictValue): string
    {
        return $this->value([
            'type_id' => $dictTypeId,
            'value' => $dictValue,
            'status' => SysDictData::STATUS_ENABLED,
        ], 'label') ?? '';
    }
}
