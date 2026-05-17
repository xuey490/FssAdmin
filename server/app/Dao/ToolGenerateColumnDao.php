<?php

declare(strict_types=1);

/**
 * 代码生成业务字段 DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-29
 */

namespace App\Dao;

use App\Models\ToolGenerateColumn;
use Framework\Basic\BaseDao;

class ToolGenerateColumnDao extends BaseDao
{
    protected function setModel(): string
    {
        return ToolGenerateColumn::class;
    }

    /**
     * 获取某表的所有字段配置
     *
     * @param int $tableId
     * @return array
     */
    public function getByTableId(int $tableId): array
    {
        return ToolGenerateColumn::query()
            ->where('table_id', $tableId)
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * 批量插入字段配置
     *
     * @param array $rows
     * @return bool
     */
    public function batchInsert(array $rows): bool
    {
        if (empty($rows)) return true;
        return ToolGenerateColumn::query()->insert($rows);
    }

    /**
     * 删除某表的所有字段配置
     *
     * @param int $tableId
     * @return int
     */
    public function deleteByTableId(int $tableId): int
    {
        return ToolGenerateColumn::query()
            ->where('table_id', $tableId)
            ->delete();
    }

    /**
     * 批量删除（按 table_id 数组）
     *
     * @param array $tableIds
     * @return int
     */
    public function deleteByTableIds(array $tableIds): int
    {
        return ToolGenerateColumn::query()
            ->whereIn('table_id', $tableIds)
            ->delete();
    }

    /**
     * 同步字段：新增不存在的字段，删除已移除的字段，保留现有配置
     *
     * @param int   $tableId     业务表ID
     * @param array $dbColumns   从数据库读取的最新列 [{column_name, column_type, column_comment, ...}]
     * @param int   $operatorId  操作人ID
     * @return void
     */
    public function syncColumns(int $tableId, array $dbColumns, int $operatorId = 0): void
    {
        // 现有字段（按 column_name 索引）
        $existing = ToolGenerateColumn::query()
            ->where('table_id', $tableId)
            ->get()
            ->keyBy('column_name')
            ->toArray();

        $dbColNames = array_column($dbColumns, 'column_name');

        // 删除数据库中已不存在的字段
        $toDelete = array_diff(array_keys($existing), $dbColNames);
        if (!empty($toDelete)) {
            ToolGenerateColumn::query()
                ->where('table_id', $tableId)
                ->whereIn('column_name', array_values($toDelete))
                ->delete();
        }

        // 插入新增字段
        $now = date('Y-m-d H:i:s');
        foreach ($dbColumns as $sort => $col) {
            $colName = $col['column_name'];
            if (isset($existing[$colName])) {
                // 仅更新可能变化的物理字段
                ToolGenerateColumn::query()
                    ->where('table_id', $tableId)
                    ->where('column_name', $colName)
                    ->update([
                        'column_type'    => $col['column_type']    ?? '',
                        'column_comment' => $col['column_comment'] ?? '',
                        'default_value'  => $col['default_value']  ?? null,
                        'updated_by'     => $operatorId,
                        'update_time'    => $now,
                    ]);
            } else {
                // 新增
                ToolGenerateColumn::query()->create(
                    array_merge(
                        self::buildDefaultColumn($col, $tableId, $sort),
                        ['created_by' => $operatorId, 'updated_by' => $operatorId, 'create_time' => $now, 'update_time' => $now]
                    )
                );
            }
        }
    }

    /**
     * 构建默认字段配置（初始化时使用）
     *
     * @param array $col       数据库列信息
     * @param int   $tableId
     * @param int   $sort
     * @return array
     */
    public static function buildDefaultColumn(array $col, int $tableId, int $sort = 0): array
    {
        $columnName = $col['column_name'] ?? '';
        $columnType = strtolower($col['column_type'] ?? '');
        $isPk       = ($col['is_pk'] ?? false) ? ToolGenerateColumn::IS_PK_YES : ToolGenerateColumn::IS_PK_NO;

        // 跳过主键字段的列表/编辑配置
        $skipFields = in_array($columnName, ['id', 'created_by', 'updated_by', 'create_time', 'update_time', 'delete_time']);

        // 根据字段类型推断页面控件
        $viewType = 'input';
        if (str_contains($columnType, 'text') || str_contains($columnType, 'longtext')) {
            $viewType = 'textarea';
        } elseif (str_contains($columnType, 'tinyint') || str_contains($columnType, 'smallint')) {
            $viewType = 'select';
        } elseif (str_contains($columnType, 'datetime') || str_contains($columnType, 'date')) {
            $viewType = 'date';
        }

        return [
            'table_id'       => $tableId,
            'column_name'    => $columnName,
            'column_comment' => $col['column_comment'] ?? '',
            'column_type'    => $col['column_type']    ?? '',
            'default_value'  => $col['default_value']  ?? null,
            'is_pk'          => $isPk,
            'is_required'    => $skipFields ? ToolGenerateColumn::FLAG_NO : ToolGenerateColumn::FLAG_NO,
            'is_insert'      => $skipFields ? ToolGenerateColumn::FLAG_NO : ToolGenerateColumn::FLAG_YES,
            'is_edit'        => $skipFields ? ToolGenerateColumn::FLAG_NO : ToolGenerateColumn::FLAG_YES,
            'is_list'        => $skipFields ? ToolGenerateColumn::FLAG_NO : ToolGenerateColumn::FLAG_YES,
            'is_query'       => ToolGenerateColumn::FLAG_NO,
            'is_sort'        => ToolGenerateColumn::FLAG_NO,
            'query_type'     => 'eq',
            'view_type'      => $viewType,
            'dict_type'      => null,
            'allow_roles'    => null,
            'options'        => null,
            'sort'           => $sort,
            'remark'         => null,
        ];
    }
}
