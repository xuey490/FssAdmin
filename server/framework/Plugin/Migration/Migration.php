<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Migration.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Plugin\Migration;

use Framework\Database\DatabaseFactory;
use Framework\Core\App;
use ReflectionClass;

/**
 * 插件迁移基类
 *
 * 所有插件迁移文件应继承此类。
 *
 * @package Framework\Plugin\Migration
 */
abstract class Migration
{
    /**
     * 数据库工厂实例
     *
     * @var DatabaseFactory
     */
    protected DatabaseFactory $db;

    /**
     * 迁移文件名
     *
     * @var string|null
     */
    protected ?string $filename = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->db = app('db');
    }

    /**
     * 执行迁移
     *
     * 创建数据库表、索引等。
     *
     * @return void
     */
    abstract public function up(): void;

    /**
     * 回滚迁移
     *
     * 删除数据库表、索引等。
     *
     * @return void
     */
    abstract public function down(): void;

    /**
     * 获取迁移名称
     *
     * @return string
     */
    public function getName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }

    /**
     * 获取迁移文件路径
     *
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filename;
    }

    /**
     * 设置迁移文件路径
     *
     * @param string $path
     * @return self
     */
    public function setFilePath(string $path): self
    {
        $this->filename = $path;
        return $this;
    }

    /**
     * 获取 Schema 构建器
     *
     * 根据当前 ORM 引擎返回对应的 Schema 构建器。
     *
     * @return mixed
     */
    protected function schema(): mixed
    {
        $config = require BASE_PATH . '/config/database.php';
        $engine = $config['engine'] ?? 'thinkORM';

        if ($engine === 'laravelORM') {
            // Laravel ORM Schema
            return $this->db->getSchemaBuilder();
        }

        // ThinkORM Schema (需要适配)
        return $this->db;
    }

    /**
     * 创建表
     *
     * @param string $table 表名
     * @param callable $callback 回调函数
     * @return void
     */
    protected function createTable(string $table, callable $callback): void
    {
        $config = require BASE_PATH . '/config/database.php';
        $engine = $config['engine'] ?? 'thinkORM';

        if ($engine === 'laravelORM') {
            // 通过 SchemaBuilder 执行，避免依赖 Facade 容器别名
            $this->schema()->create($table, $callback);
        } else {
            // ThinkORM 方式 - 执行原始 SQL
            // 简化实现，实际应根据 callback 解析字段
            $this->db->statement("CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }
    }

    /**
     * 删除表
     *
     * @param string $table 表名
     * @return void
     */
    protected function dropTable(string $table): void
    {
        $config = require BASE_PATH . '/config/database.php';
        $engine = $config['engine'] ?? 'thinkORM';

        if ($engine === 'laravelORM') {
            $this->schema()->dropIfExists($table);
        } else {
            $this->db->statement("DROP TABLE IF EXISTS `{$table}`;");
        }
    }

    /**
     * 检查表是否存在
     *
     * @param string $table 表名
     * @return bool
     */
    protected function tableExists(string $table): bool
    {
        $config = require BASE_PATH . '/config/database.php';
        $engine = $config['engine'] ?? 'thinkORM';

        if ($engine === 'laravelORM') {
            return $this->schema()->hasTable($table);
        }

        // ThinkORM 方式
        $result = $this->db->select("SHOW TABLES LIKE '{$table}'");
        return !empty($result);
    }

    /**
     * 执行原始 SQL
     *
     * @param string $sql SQL 语句
     * @return mixed
     */
    protected function statement(string $sql): mixed
    {
        return $this->db->statement($sql);
    }
}
