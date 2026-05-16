<?php
declare(strict_types=1);

namespace Framework\Schema;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Framework\Basic\BaseLaORMModel;

/**
 * ====================================================
 * SchemaWarmup: 自动扫描 BaseModel 子类并预热
 * ====================================================
 */
final class SchemaWarmup
{
    /** @var string 模型根目录 */
    private static string $modelRootPath = '';

    /** @var string 模型根命名空间 */
    private static string $modelBaseNamespace = '';

    /** @var array 忽略的模型类 */
    private static array $ignoreModels = [];

    /**
     * 设置模型扫描目录
     *
     * @param string $rootPath
     * @param string $baseNamespace
     */
    public static function setScanPath(string $rootPath, string $baseNamespace): void
    {
		$db = app('db');
        self::$modelRootPath = rtrim($rootPath, DIRECTORY_SEPARATOR);
        self::$modelBaseNamespace = rtrim($baseNamespace, '\\');
    }

    /**
     * 设置忽略的模型类
     *
     * @param array $classes
     */
    public static function ignore(array $classes): void
    {
        self::$ignoreModels = $classes;
    }

    /**
     * 自动扫描并 warmup 所有 BaseModel 子类
     */
    public static function warmupAll(): void
    {
        if (!self::$modelRootPath || !self::$modelBaseNamespace) {
            throw new RuntimeException("Model scan path or namespace not set");
        }

        $models = self::scanModels(self::$modelRootPath, self::$modelBaseNamespace);

        foreach ($models as $modelClass) {
            self::warmupModel($modelClass);
        }
    }

    /* ==================== 内部方法 ==================== */

    /**
     * 扫描目录，返回所有 BaseModel 子类
     *
     * @param string $path
     * @param string $namespace
     * @return array
     */
    private static function scanModels(string $path, string $namespace): array
    {
        $classes = [];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            // 解析类名
            $relative = str_replace($path, '', $file->getPathname());
            $relative = trim(str_replace(['/', '\\'], '\\', $relative), '\\');
            $class = $namespace . '\\' . str_replace('.php', '', $relative);

            if (!class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);

            // 忽略抽象类和非 BaseModel 子类
            if ($ref->isAbstract() ||
                !$ref->isSubclassOf(\Framework\Utils\BaseModel::class) ||
                in_array($class, self::$ignoreModels, true)
            ) {
                continue;
            }

            $classes[] = $class;
        }

        return array_values(array_unique($classes));
    }


	
/**
 * 预热单个模型
 *
 * @param string $modelClass
 */
protected static function warmupModel(string $modelClass): void
{
    // 支持动态别名 BaseModel
    if (!is_subclass_of($modelClass, Model::class) &&
        !is_subclass_of($modelClass, \Framework\Utils\BaseModel::class)
    ) {
        return;
    }

    /** @var Model $model */
    $model = new $modelClass();
	
    $resolver = Model::getConnectionResolver();
    if (!$resolver) {
        throw new RuntimeException('Eloquent ConnectionResolver not initialized');
    }

    // 获取连接
    $connection = $model->getConnectionName() ?? $resolver->getDefaultConnection();
    $conn = $resolver->connection($connection);
    $schema = $conn->getSchemaBuilder();

    // 获取逻辑表名（不带前缀）
    $table = $model->getTable();
    // 拼接完整表名（前缀+表名）
    $fullTableName = $conn->getTablePrefix() . $table;

    // 核心修复：用原生SQL查询当前数据库的所有表名（替代不存在的getAllTables）
    $allTables = [];
    try {
        // MySQL 原生查询：获取当前数据库的所有表名
        $tablesResult = $conn->select("SHOW TABLES");
        // 解析查询结果（适配MySQL返回格式：数组/对象）
        foreach ($tablesResult as $item) {
            if (is_object($item)) {
                // 提取对象中的表名字段（字段名格式：Tables_in_数据库名）
                $field = 'Tables_in_' . $conn->getDatabaseName();
                $allTables[] = $item->$field;
            } elseif (is_array($item)) {
                // 数组格式直接取第一个值
                $allTables[] = reset($item);
            }
        }
    } catch (\Exception $e) {
        // 数据库连接失败/无权限时，直接返回（避免阻断启动）
        return;
    }

    // 判断表是否存在，不存在则跳过注册
    if (!in_array($fullTableName, $allTables) && !in_array($table, $allTables)) {
        return;
    }
				
    // 仅表存在时执行注册
    SchemaRegistry::register(
        $table,
        $schema->getColumnListing($table),
        self::loadIndexes($conn, $table),
        self::resolveAuditFields($modelClass)
    );
}

/**
 * 加载表索引（增加表存在性判断）
 *
 * @param \Illuminate\Database\Connection $conn
 * @param string $table
 * @return array
 */
protected static function loadIndexes($conn, string $table): array
{
    $fullTableName = $conn->getTablePrefix() . $table;
    // 先查询所有表，判断目标表是否存在
    try {
        $tablesResult = $conn->select("SHOW TABLES");
        $allTables = [];
        foreach ($tablesResult as $item) {
            $field = 'Tables_in_' . $conn->getDatabaseName();
            $allTables[] = is_object($item) ? $item->$field : reset($item);
        }
    } catch (\Exception $e) {
        return [];
    }

    // 表不存在则返回空索引
    if (!in_array($fullTableName, $allTables) && !in_array($table, $allTables)) {
        return [];
    }

    // 表存在时执行索引查询
    try {
        $indexes = $conn->select("SHOW INDEX FROM `{$fullTableName}`");
        return $indexes ?: [];
    } catch (\Exception $e) {
        return [];
    }
}

    /**
     * 获取审计字段（演示示例，可根据 BaseModel 约定）
     *
     * @param string $modelClass
     * @return array
     */
    protected static function resolveAuditFields(string $modelClass): array
    {
        $fields = [];

        if (!class_exists($modelClass)) {
            return $fields;
        }

        $ref = new ReflectionClass($modelClass);

        if ($ref->hasProperty('auditColumns')) {
            $prop = $ref->getProperty('auditColumns');
            $prop->setAccessible(true);
            $fields = $prop->getValue(new $modelClass()) ?: [];
        }

        return $fields;
    }
}