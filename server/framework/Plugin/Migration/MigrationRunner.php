<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: MigrationRunner.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Plugin\Migration;

use Framework\Core\App;
use Framework\Database\DatabaseFactory;
use RuntimeException;

/**
 * 插件迁移执行器
 *
 * 负责执行和回滚插件的数据库迁移。
 *
 * @package Framework\Plugin\Migration
 */
class MigrationRunner
{
    /**
     * 迁移记录表名
     *
     * @var string
     */
    private string $migrationTable;

    /**
     * 数据库实例
     *
     * @var DatabaseFactory
     */
    private DatabaseFactory $db;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->db = app('db');
        $config = require BASE_PATH . '/config/plugin/migration.php';
        $this->migrationTable = $config['migration_table'] ?? 'plugin_migrations';

        $this->ensureMigrationTableExists();
    }

    /**
     * 执行插件迁移
     *
     * @param string $pluginName 插件名称
     * @param string $migrationDir 迁移目录
     * @return array 已执行的迁移文件列表
     */
    public function run(string $pluginName, string $migrationDir): array
    {
        $migrations = $this->getPendingMigrations($pluginName, $migrationDir);
        $executed = [];

        // 按文件名排序（时间戳排序）
        sort($migrations);

        foreach ($migrations as $migrationFile) {
            $migration = $this->loadMigration($migrationFile);

            if ($migration === null) {
                continue;
            }

            try {
                // 执行迁移
                $migration->up();

                // 记录执行
                $this->recordMigration($pluginName, $migration->getName(), $migrationFile);

                $executed[] = $migration->getName();
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    "Migration failed for {$pluginName}: " . $migration->getName() . " - " . $e->getMessage()
                );
            }
        }

        return $executed;
    }

    /**
     * 回滚插件迁移
     *
     * @param string $pluginName 插件名称
     * @param string $migrationDir 迁移目录
     * @param int $steps 回滚步数（0 表示全部回滚）
     * @return array 已回滚的迁移文件列表
     */
    public function rollback(string $pluginName, string $migrationDir, int $steps = 0): array
    {
        $executed = $this->getExecutedMigrations($pluginName);
        $rolledBack = [];

        // 按执行时间倒序排列
        $executed = array_reverse($executed);

        if ($steps > 0) {
            $executed = array_slice($executed, 0, $steps);
        }

        foreach ($executed as $record) {
            $migrationFile = $record['migration_file'];

            if (!file_exists($migrationFile)) {
                continue;
            }

            $migration = $this->loadMigration($migrationFile);

            if ($migration === null) {
                continue;
            }

            try {
                // 执行回滚
                $migration->down();

                // 删除记录
                $this->deleteMigrationRecord($pluginName, $record['migration_name']);

                $rolledBack[] = $migration->getName();
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    "Rollback failed for {$pluginName}: " . $migration->getName() . " - " . $e->getMessage()
                );
            }
        }

        return $rolledBack;
    }

    /**
     * 获取待执行的迁移文件
     *
     * @param string $pluginName 插件名称
     * @param string $migrationDir 迁移目录
     * @return array
     */
    public function getPendingMigrations(string $pluginName, string $migrationDir): array
    {
        if (!is_dir($migrationDir)) {
            return [];
        }

        // 获取已执行的迁移
        $executedNames = array_column($this->getExecutedMigrations($pluginName), 'migration_name');

        // 扫描迁移目录
        $files = glob($migrationDir . '/*.php') ?: [];

        $pending = [];
        foreach ($files as $file) {
            $migrationName = basename($file, '.php');

            // 跳过已执行的
            if (in_array($migrationName, $executedNames)) {
                continue;
            }

            $pending[] = $file;
        }

        return $pending;
    }

    /**
     * 获取已执行的迁移记录
     *
     * @param string $pluginName 插件名称
     * @return array
     */
    public function getExecutedMigrations(string $pluginName): array
    {
        try {
            $rows = $this->db->table($this->migrationTable)
                ->where('plugin_name', $pluginName)
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();

            $normalized = [];
            foreach ($rows as $row) {
                if (is_object($row)) {
                    /** @var object $row */
                    $row = get_object_vars($row);
                }
                if (!is_array($row)) {
                    continue;
                }

                $normalized[] = [
                    'plugin_name' => (string) ($row['plugin_name'] ?? ''),
                    'migration_name' => (string) ($row['migration_name'] ?? ''),
                    'migration_file' => (string) ($row['migration_file'] ?? ''),
                    'executed_at' => (string) ($row['executed_at'] ?? ''),
                ];
            }

            return $normalized;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * 加载迁移文件
     *
     * @param string $file 迁移文件路径
     * @return Migration|null
     */
    private function loadMigration(string $file): ?Migration
    {
        if (!file_exists($file)) {
            return null;
        }

        // 获取类名
        $className = $this->getClassNameFromFile($file);

        if ($className === null) {
            return null;
        }

        // 包含文件
        require_once $file;

        if (!class_exists($className)) {
            return null;
        }

        $migration = new $className();

        if (!($migration instanceof Migration)) {
            return null;
        }

        $migration->setFilePath($file);

        return $migration;
    }

    /**
     * 从文件获取类名
     *
     * @param string $file 文件路径
     * @return string|null
     */
    private function getClassNameFromFile(string $file): ?string
    {
        $content = file_get_contents($file);

        if ($content === false) {
            return null;
        }

        // 匹配命名空间
        $namespace = '';
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];
        }

        // 匹配类名
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            $className = $matches[1];

            if ($namespace) {
                return $namespace . '\\' . $className;
            }

            return $className;
        }

        return null;
    }

    /**
     * 记录迁移执行
     *
     * @param string $pluginName 插件名称
     * @param string $migrationName 迁移名称
     * @param string $migrationFile 迁移文件路径
     */
    private function recordMigration(string $pluginName, string $migrationName, string $migrationFile): void
    {
        $this->db->table($this->migrationTable)->insert([
            'plugin_name' => $pluginName,
            'migration_name' => $migrationName,
            'migration_file' => $migrationFile,
            'executed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 删除迁移记录
     *
     * @param string $pluginName 插件名称
     * @param string $migrationName 迁移名称
     */
    private function deleteMigrationRecord(string $pluginName, string $migrationName): void
    {
        $this->db->table($this->migrationTable)
            ->where('plugin_name', $pluginName)
            ->where('migration_name', $migrationName)
            ->delete();
    }

    /**
     * 确保迁移记录表存在
     */
    private function ensureMigrationTableExists(): void
    {
        // 检查表是否存在
        $result = $this->db->select("SHOW TABLES LIKE '{$this->migrationTable}'");

        if (empty($result)) {
            // 创建迁移记录表
            $this->db->statement("
                CREATE TABLE `{$this->migrationTable}` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `plugin_name` VARCHAR(100) NOT NULL,
                    `migration_name` VARCHAR(255) NOT NULL,
                    `migration_file` VARCHAR(500) NOT NULL,
                    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_plugin_name` (`plugin_name`),
                    INDEX `idx_migration_name` (`migration_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }
}
