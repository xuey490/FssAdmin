<?php
declare(strict_types=1);

namespace Framework\Schema;

use Illuminate\Database\Eloquent\Model;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;

/**
 * ====================================================
 * SchemaRegistry: 常驻进程 ORM Schema 缓存
 * ====================================================
 */
final class SchemaRegistry
{
    /**
     * 格式：
     * [
     *   table_name => [
     *       'columns' => [],
     *       'indexes' => [],
     *       'audit'   => [],
     *   ]
     * ]
     */
    private static array $schemas = [];

    /** 是否冻结，冻结后禁止注册新表 */
    private static bool $frozen = false;

    /**
     * 注册表结构
     *
     * @param string $table      表名（逻辑表名，不带前缀）
     * @param array  $columns    字段列表
     * @param array  $indexes    索引信息
     * @param array  $auditColumns 审计字段
     */
    public static function register(
        string $table,
        array $columns,
        array $indexes = [],
        array $auditColumns = []
    ): void {
        if (self::$frozen) {
            throw new RuntimeException("SchemaRegistry is frozen");
        }

        self::$schemas[$table] = [
            'columns' => array_values(array_unique($columns)),
            'indexes' => $indexes,
            'audit'   => $auditColumns,
        ];
    }

    /* ================= 查询接口 ================= */

    public static function hasTable(string $table): bool
    {
        return isset(self::$schemas[$table]);
    }

    public static function getColumns(string $table): array
    {
        self::assertExists($table);
        return self::$schemas[$table]['columns'];
    }

    public static function hasColumn(string $table, string $column): bool
    {
        self::assertExists($table);
        return in_array($column, self::$schemas[$table]['columns'], true);
    }

    public static function getAuditColumns(string $table): array
    {
        self::assertExists($table);
        return self::$schemas[$table]['audit'];
    }

    /**
     * 返回所有已注册 schema
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$schemas;
    }

    /* ================= 冻结 ================= */

    public static function freeze(): void
    {
        self::$frozen = true;
    }

    public static function isFrozen(): bool
    {
        return self::$frozen;
    }

    /* ================= 内部方法 ================= */

    private static function assertExists(string $table): void
    {
        if (!isset(self::$schemas[$table])) {
            throw new RuntimeException("Schema not registered for table: {$table}");
        }
    }
}
