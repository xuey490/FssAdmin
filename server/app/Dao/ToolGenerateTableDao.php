<?php

declare(strict_types=1);

/**
 * 代码生成业务表 DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-29
 */

namespace App\Dao;

use App\Models\ToolGenerateTable;
use Framework\Basic\BaseDao;

class ToolGenerateTableDao extends BaseDao
{
    protected function setModel(): string
    {
        return ToolGenerateTable::class;
    }

    /**
     * 分页获取代码生成表列表
     *
     * @param array<array-key, mixed> $params 查询参数（table_name/source/page/limit）
     * @return array<array-key, mixed> [list, total]
     */
    public function getPageList(array $params = []): array
    {
        $tableName = $params['table_name'] ?? '';
        $source    = $params['source'] ?? '';
        $page      = max(1, (int)($params['page'] ?? 1));
        $limit     = min(100, max(1, (int)($params['limit'] ?? 15)));

        $model = ToolGenerateTable::query();

        if (!empty($tableName)) {
            $model->where('table_name', 'like', "%{$tableName}%");
        }
        if (!empty($source)) {
            $model->where('source', $source);
        }

        $total = $model->count();
        $list  = $model->orderBy('id', 'desc')
            ->forPage($page, $limit)
            ->get()
            ->toArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 根据ID查询记录（含软删除判断）
     *
     * @param int $id
     * @return ToolGenerateTable|null
     */
    public function findById(int $id): ?ToolGenerateTable
    {
        return ToolGenerateTable::query()->find($id);
    }

    /**
     * 批量删除（软删除）
     *
     * @param array<array-key, mixed> $ids
     * @return int 删除数量
     */
    public function batchDeleteByIds(array $ids): int
    {
        return ToolGenerateTable::query()->whereIn('id', $ids)->delete();
    }

    /**
     * 检查表名是否已装载
     *
     * @param string $tableName
     * @param string $source
     * @return bool
     */
    public function isTableLoaded(string $tableName, string $source = ''): bool
    {
        $query = ToolGenerateTable::query()->where('table_name', $tableName);
        if (!empty($source)) {
            $query->where('source', $source);
        }
        return $query->exists();
    }
}