<?php

declare(strict_types=1);

namespace App\Services;

use Framework\Basic\BaseService;

class DatabaseMaintainService extends BaseService
{
    protected function db()
    {
        return app('db');
    }

    protected function getDatabase(): string
    {
        return config('database.database', '');
    }

    /**
     * 获取所有数据表（无分页，支持名称搜索）
     */
    public function getTables(array $params = []): array
    {
        $name = $params['name'] ?? '';

        $rows = $this->db()->select("SHOW TABLE STATUS");

        $list = [];
        foreach ($rows as $row) {
            $row = (array)$row;
            $tableName = $row['Name'] ?? '';

            if ($name !== '' && stripos($tableName, $name) === false) {
                continue;
            }

            $dataFree   = (int)($row['Data_free'] ?? 0);
            $dataLength = (int)($row['Data_length'] ?? 0) + (int)($row['Index_length'] ?? 0);

            $list[] = [
                'name'        => $tableName,
                'comment'     => $row['Comment'] ?? '',
                'engine'      => $row['Engine'] ?? '',
                'rows'        => (int)($row['Rows'] ?? 0),
                'data_free'   => $this->formatBytes($dataFree),
                'data_length' => $this->formatBytes($dataLength),
                'collation'   => $row['Collation'] ?? '',
                'create_time' => $row['Create_time'] ?? '',
                'update_time' => $row['Update_time'] ?? '',
            ];
        }

        return $list;
    }

    /**
     * 获取数据源（所有表名+注释）
     */
    public function getDataSource(): array
    {
        // 之前误返回了表名数组，应该返回数据源连接名称列表
        $connections = config('database.connections', []);
        $keys = array_keys($connections);
        return empty($keys) ? ['mysql'] : $keys;
    }

    /**
     * 获取表字段详情（含注释）
     */
    public function getTableDetailed(string $tableName): array
    {
        if (empty($tableName)) return [];

        // 优先用 SHOW FULL COLUMNS，兼容性最好且一定能拿到注释
        $rows = $this->db()->select("SHOW FULL COLUMNS FROM `{$tableName}`");

        return array_map(fn($r) => [
            'column_name'    => ((array)$r)['Field'] ?? '',
            'column_type'    => ((array)$r)['Type'] ?? '',
            'column_key'     => ((array)$r)['Key'] ?? '',
            'is_nullable'    => (((array)$r)['Null'] ?? '') === 'YES',
            'column_default' => ((array)$r)['Default'] ?? null,
            'column_comment' => ((array)$r)['Comment'] ?? '',
        ], $rows);
    }

    /**
     * 获取建表语句
     */
    public function getCreateTableSql(string $tableName): array
    {
        if (empty($tableName)) return ['sql' => ''];

        $rows = $this->db()->select("SHOW CREATE TABLE `{$tableName}`");
        if (empty($rows)) return ['sql' => ''];

        $row = (array)$rows[0];
        // 键名可能是 'Create Table' 或 'Create View'
        $sql = $row['Create Table'] ?? $row['Create View'] ?? '';

        return ['table' => $tableName, 'sql' => $sql];
    }

    /**
     * 获取回收站数据（delete_time 不为空的记录）
     */
    public function getRecycleData(array $params = []): array
    {
        $table = $params['table'] ?? '';
        $page  = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);

        if (empty($table)) {
            return ['list' => [], 'total' => 0, 'page' => $page, 'limit' => $limit];
        }

        // 检查是否有 delete_time 字段
        $cols = $this->db()->select("DESCRIBE `{$table}`");
        $hasDeleteTime = false;
        foreach ($cols as $col) {
            if (((array)$col)['Field'] === 'delete_time') {
                $hasDeleteTime = true;
                break;
            }
        }

        if (!$hasDeleteTime) {
            return ['list' => [], 'total' => 0, 'page' => $page, 'limit' => $limit];
        }

        $countRows = $this->db()->select("SELECT COUNT(*) AS cnt FROM `{$table}` WHERE delete_time IS NOT NULL");
        $total  = (int)(((array)$countRows[0])['cnt'] ?? 0);
        $offset = ($page - 1) * $limit;
        $rows   = $this->db()->select(
            "SELECT * FROM `{$table}` WHERE delete_time IS NOT NULL ORDER BY delete_time DESC LIMIT {$limit} OFFSET {$offset}"
        );

        // 每行转为含 id、delete_time、json_data 的结构
        $list = array_map(function ($r) {
            $row = (array)$r;
            return [
                'id'          => $row['id'] ?? null,
                'delete_time' => $row['delete_time'] ?? '',
                'json_data'   => json_encode($row, JSON_UNESCAPED_UNICODE),
            ];
        }, $rows);

        return [
            'data'  => $list,
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 销毁回收站数据（物理删除）
     */
    public function destroyRecycleData(string $table, array $ids): bool
    {
        if (empty($table) || empty($ids)) return false;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db()->delete("DELETE FROM `{$table}` WHERE id IN ({$placeholders})", $ids);
        return true;
    }

    /**
     * 恢复回收站数据（将 delete_time 置为 NULL）
     */
    public function recoveryRecycleData(string $table, array $ids): bool
    {
        if (empty($table) || empty($ids)) return false;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db()->update(
            "UPDATE `{$table}` SET delete_time = NULL WHERE id IN ({$placeholders})",
            $ids
        );
        return true;
    }

    /**
     * 优化表
     */
    public function optimizeTables(array $tables): array
    {
        $results = [];
        foreach ($tables as $table) {
            try {
                $this->db()->statement("OPTIMIZE TABLE `{$table}`");
                $results[$table] = ['success' => true];
            } catch (\Exception $e) {
                $results[$table] = ['success' => false, 'message' => $e->getMessage()];
            }
        }
        return $results;
    }

    /**
     * 清理表碎片
     */
    public function cleanTableFragment(array $tables): array
    {
        $results = [];
        foreach ($tables as $table) {
            try {
                $this->db()->statement("OPTIMIZE TABLE `{$table}`");
                $this->db()->statement("ANALYZE TABLE `{$table}`");
                $results[$table] = ['success' => true];
            } catch (\Exception $e) {
                $results[$table] = ['success' => false, 'message' => $e->getMessage()];
            }
        }
        return $results;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 2) . ' KB';
        if ($bytes < 1024 * 1024 * 1024) return round($bytes / 1024 / 1024, 2) . ' MB';
        return round($bytes / 1024 / 1024 / 1024, 2) . ' GB';
    }
}
