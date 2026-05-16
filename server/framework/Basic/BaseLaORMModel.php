<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: BaseLaORMModel.php
 * @Date: 2026-1-11
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as LaSoftDeletes;
use Illuminate\Support\Carbon;
use Framework\Utils\Snowflake;
use Framework\Basic\Traits\LaBelongsToTenant;
use Framework\Basic\Casts\TimestampCast;
use think\facade\Cache;
use Framework\Schema\SchemaRegistry;

/**
 * BaseLaORMModel - Laravel Eloquent ORM 模型基类
 *
 * 提供以下核心功能：
 * - 雪花算法主键生成
 * - 多租户隔离
 * - 自动时间戳管理（可配置字段名）
 * - 软删除支持
 * - 表结构缓存优化
 *
 * 子模型可通过以下属性自定义时间字段配置：
 * - $timestamps: 是否自动维护时间戳（默认 true）
 * - $createdAtColumn: 创建时间字段名（默认 'created_at'，设为 null 则不使用）
 * - $updatedAtColumn: 更新时间字段名（默认 'updated_at'，设为 null 则不使用）
 *
 * @package Framework\Basic
 */
class BaseLaORMModel extends Model
{
    use LaBelongsToTenant;

    // ================= 基础配置 =================

    /**
     * 是否自增主键（默认使用自增ID）
     * 子类如需使用雪花ID，设为 false 并配置 pkGenerateType = 'snowflake'
     * @var bool
     */
    public $incrementing = true;

    /**
     * 是否自动维护时间戳
     * 子类可覆盖设为 false 以禁用所有时间相关功能
     * @var bool
     */
    public $timestamps = true;

    /**
     * 创建时间字段名（Laravel Eloquent 标准常量）
     * 所有子模型统一使用 create_time
     */
    public const CREATED_AT = 'create_time';

    /**
     * 更新时间字段名（Laravel Eloquent 标准常量）
     * 所有子模型统一使用 update_time
     */
    public const UPDATED_AT = 'update_time';

    /**
     * 主键类型
     * @var string
     */
    protected $keyType = 'int';

    /**
     * 日期格式 - 数据库使用 datetime 类型
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * 雪花算法实例（单例）
     * @var Snowflake|null
     */
    private static ?Snowflake $snowflake = null;

    /**
     * 主键生成策略
     * 可选值: 'snowflake'（雪花ID）, 'auto'（自增）
     * @var string
     */
    protected string $pkGenerateType = 'auto';

    /**
     * 表字段缓存
     * @var array
     */
    private static array $tableColumns = [];

    /**
     * Schema 缓存最大数量
     */
    private const MAX_SCHEMA_CACHE = 128;

    /**
     * Schema 是否已冻结
     * @var bool
     */
    private static bool $schemaFrozen = false;

    /**
     * 创建时间字段名
     * 子类可覆盖定义，设为 null 则不自动填充
     * @var string|null
     */
    protected ?string $createdAtColumn = 'create_time';

    /**
     * 更新时间字段名
     * 子类可覆盖定义，设为 null 则不自动填充
     * @var string|null
     */
    protected ?string $updatedAtColumn = 'update_time';

    // ================= 初始化 =================

    /**
     * 初始化模型
     *
     * 自动为所有日期字段注册 TimestampCast，
     * 不侵入 setAttribute / getAttribute 方法。
     *
     * @return void
     */
    protected function initializeBaseLaORMModel(): void
    {
        foreach ($this->getDates() as $field) {
            $this->casts[$field] = TimestampCast::class;
        }
    }

    /**
     * 初始化模型实例
     *
     * Laravel 会在模型构造后自动调用此方法。
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->initializeBaseLaORMModel();
    }

    // ================= Model Boot =================

    /**
     * 模型启动方法
     *
     * 注册以下模型事件：
     * - creating: 生成雪花ID、填充创建时间
     * - updating: 填充更新时间
     * - deleted: 执行删除后处理
     *
     * 注意：软删除由 SoftDeletes trait 自行处理，不需要在此设置
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // 创建事件
        static::creating(function ($model) {
            // 1. 雪花ID生成
            if ($model->pkGenerateType === 'snowflake') {
                if (empty($model->{$model->getKeyName()})) {
                    $model->{$model->getKeyName()} = self::generateSnowflakeID();
                }
            }

            // 2. 自动时间字段填充（仅在 timestamps 启用时生效）
            if ($model->timestamps) {
                $now = Carbon::now()->timestamp;
                $model->fillTimestampFields($now, true);
            }
        });

        // 更新事件
        static::updating(function ($model) {
            if ($model->timestamps) {
                $now = Carbon::now()->timestamp;
                $model->fillTimestampFields($now, false);
            }
        });

        // 删除后处理
        static::deleted(function ($model) {
            self::onAfterDelete($model);
        });
    }

    /**
     * 填充时间戳字段
     *
     * 根据子模型定义的字段名自动填充创建时间或更新时间。
     * 支持同时填充 Laravel 风格（created_at/updated_at）和 ThinkPHP 风格（create_time/update_time）字段。
     *
     * @param int $timestamp 当前时间戳
     * @param bool $isCreate 是否为创建操作
     * @return void
     */
    protected function fillTimestampFields(int $timestamp, bool $isCreate): void
    {
        if ($isCreate) {
            // 创建时填充创建时间
            if ($this->createdAtColumn && $this->hasColumnCached($this->createdAtColumn) && empty($this->{$this->createdAtColumn})) {
                $this->{$this->createdAtColumn} = $timestamp;
            }

            // 同时填充更新时间（创建时通常等于创建时间）
            if ($this->updatedAtColumn && $this->hasColumnCached($this->updatedAtColumn) && empty($this->{$this->updatedAtColumn})) {
                $this->{$this->updatedAtColumn} = $timestamp;
            }
        } else {
            // 更新时只填充更新时间
            if ($this->updatedAtColumn && $this->hasColumnCached($this->updatedAtColumn)) {
                $this->{$this->updatedAtColumn} = $timestamp;
            }
        }
    }

    // ================= 日期体系（核心优化） =================

    /**
     * 子类可扩展日期字段
     *
     * 子类可覆盖此方法添加自定义的日期字段。
     *
     * @return array 日期字段列表
     */
    protected function extraDates(): array
    {
        return ['delete_time'];
    }

    /**
     * 统一日期字段入口
     *
     * 合并父类日期字段、类属性 $dates 和子类扩展字段。
     *
     * @return array 日期字段列表
     */
    public function getDates(): array
    {
        return array_values(array_unique(array_merge(
            parent::getDates(),
            $this->dates ?? [],
            $this->extraDates()
        )));
    }

    /**
     * 统一序列化格式
     *
     * 将日期对象序列化为 'Y-m-d H:i:s' 格式字符串。
     *
     * @param \DateTimeInterface $date 日期对象
     * @return string 格式化后的日期字符串
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return Carbon::instance($date)->format('Y-m-d H:i:s');
    }

    // ================= 审计字段 =================

    /**
     * 检查字段是否存在（带缓存）
     *
     * 使用 Schema 缓存避免重复查询数据库结构。
     *
     * @param string $column 字段名
     * @return bool 字段是否存在
     */
    protected function hasColumnCached(string $column): bool
    {
        $table = $this->getTable();

        return in_array(
            $column,
            $this->getTableColumnsCached(),
            true
        );
    }

    /**
     * 获取表字段缓存
     *
     * 使用缓存系统存储表结构信息，提升性能。
     *
     * @return array 字段名列表
     */
    protected function getTableColumnsCached(): array
    {
        $connection = $this->getConnection();
        $driver = $connection->getDriverName();

        $table = $this->getTable();
        $cacheKey = 'schema:columns:' . $driver . ':' . $table;

        return Cache::remember($cacheKey, function () use ($table) {
            return $this->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($table);
        });
    }

    // ================= 字段相关 =================

    /**
     * 获取模型字段列表
     *
     * 根据运行环境选择不同的获取方式：
     * - Workerman 环境：使用 SchemaRegistry
     * - 其他环境：使用数据库查询
     *
     * @return array 字段列表
     */
    public function getFields(): array
    {
        if (defined('WORKERMAN_ENV')) {
            return SchemaRegistry::getColumns($this->getTable());
        } else {
            return $this->getTableColumns();
        }
    }

    /**
     * 检查字段是否存在（SchemaRegistry 方式）
     *
     * @param string $column 字段名
     * @return bool 字段是否存在
     */
    protected function hasColumn(string $column): bool
    {
        return SchemaRegistry::hasColumn($this->getTable(), $column);
    }

    /**
     * 缓存治理层
     *
     * 管理表字段的内存缓存，避免重复查询。
     *
     * @return array 字段列表
     */
    protected function getTableColumns(): array
    {
        $table = $this->getTable();

        if (isset(self::$tableColumns[$table])) {
            return self::$tableColumns[$table];
        }

        if (self::$schemaFrozen) {
            throw new \RuntimeException(
                "Schema is frozen. Table columns not preloaded: {$table}"
            );
        }

        $columns = $this->loadTableColumnsFromDb($table);

        return $this->rememberTableColumns($table, $columns);
    }

    /**
     * 从数据库加载表字段
     *
     * 直接查询数据库获取表结构信息。
     *
     * @param string $table 表名
     * @return array 字段列表
     */
    protected function loadTableColumnsFromDb(string $table): array
    {
        $connection = $this->getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $prefix = $connection->getTablePrefix();
            $rows = $connection->select(
                'SHOW COLUMNS FROM `' . $prefix . $table . '`'
            );

            return array_map(
                static fn($row) => $row->Field,
                $rows
            );
        }

        return $connection
            ->getSchemaBuilder()
            ->getColumnListing($table);
    }

    /**
     * 缓存表字段
     *
     * 将字段列表存入内存缓存，使用 FIFO 策略控制缓存大小。
     *
     * @param string $table 表名
     * @param array $columns 字段列表
     * @return array 字段列表
     */
    protected function rememberTableColumns(string $table, array $columns): array
    {
        if (count(self::$tableColumns) >= self::MAX_SCHEMA_CACHE) {
            array_shift(self::$tableColumns); // FIFO
        }

        return self::$tableColumns[$table] = $columns;
    }

    /**
     * 冻结 Schema
     *
     * 冻结后禁止从数据库加载新的表结构。
     * 适用于生产环境预加载场景。
     *
     * @return void
     */
    public static function freezeSchema(): void
    {
        self::$schemaFrozen = true;
    }

    // ================= 动态字段控制 =================

    /**
     * 动态隐藏字段
     *
     * 兼容 ThinkPHP 风格的动态隐藏方法。
     *
     * @param array $fields 要隐藏的字段列表
     * @return static 当前模型实例
     */
    public function dynamicHidden(array $fields): static
    {
        $this->makeHidden($fields);
        return $this;
    }

    /**
     * 动态显示字段
     *
     * 兼容 ThinkPHP 风格的动态显示方法。
     *
     * @param array $fields 要显示的字段列表
     * @return static 当前模型实例
     */
    public function dynamicVisible(array $fields): static
    {
        $this->makeVisible($fields);
        return $this;
    }

    // ================= TP 风格兼容 =================

    /**
     * 获取字段值
     *
     * 兼容 ThinkPHP 的 getData 方法。
     * 自动将 Carbon 对象格式化为字符串。
     *
     * @param string $field 字段名
     * @return mixed 字段值
     */
    public function getData(string $field): mixed
    {
        $value = $this->getAttribute($field);

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    /**
     * 设置字段值
     *
     * 兼容 ThinkPHP 的 set 方法。
     *
     * @param string $name 字段名
     * @param mixed $value 字段值
     * @return void
     */
    public function set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    // ================= 表信息 =================

    /**
     * 获取表名
     *
     * 支持三种方式指定表名：
     * 1. $table 属性
     * 2. $name 属性
     * 3. 父类自动推断
     *
     * @return string 表名
     */
    public function getTable(): string
    {
        if (!empty($this->table)) {
            return $this->table;
        }
        if (property_exists($this, 'name') && !empty($this->name)) {
            return (string) $this->name;
        }
        return parent::getTable();
    }

    /**
     * 获取模型定义的数据库表名（全称）
     *
     * 静态方法，便于在不实例化模型的情况下获取表名。
     *
     * @return string 表名
     */
    public static function getTableName(): string
    {
        $self = new static();

        return $self->getTable();
    }

    /**
     * 获取模型对应表的字段列表
     *
     * 复用静态缓存，性能最优。
     *
     * @return array 字段列表
     */
    public function getFields2(): array
    {
        try {
            $table = $this->getTable();
            if (!isset(self::$tableColumns[$table])) {
                self::$tableColumns[$table] = $this->getConnection()->getSchemaBuilder()->getColumnListing($table);
            }
            return self::$tableColumns[$table];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取主键名称
     *
     * 兼容 ThinkPHP 的 getPk 方法。
     *
     * @return string 主键名
     */
    public function getPk(): string
    {
        return $this->getKeyName();
    }

    /**
     * 是否开启软删除
     *
     * 检查当前模型是否使用了 SoftDeletes trait。
     *
     * @return bool 是否开启软删除
     */
    public static function isSoftDeleteEnabled(): bool
    {
        return in_array(LaSoftDeletes::class, class_uses(static::class));
    }

    // ================= 删除后处理 =================

    /**
     * 删除后处理
     *
     * 物理删除后执行清理逻辑（预留回收站功能）。
     *
     * @param Model $model 被删除的模型实例
     * @return void
     */
    public static function onAfterDelete(Model $model)
    {
        if ($model::isSoftDeleteEnabled()) {
            return;
        }

        try {
            // 回收站逻辑预留
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                "Recycle bin error: " . $e->getMessage()
            );
        }
    }

    // ================= 雪花算法部分优化 =================

    /**
     * 生成雪花ID
     *
     * 公共静态方法，支持外部调用。
     * 使用懒加载模式初始化雪花算法实例。
     *
     * @return string 雪花ID字符串
     */
    public static function generateSnowflakeID(): string
    {
        // 懒加载雪花算法实例，全局只初始化一次
        if (self::$snowflake === null) {
            self::$snowflake = self::createSnowflakeInstance();
        }

        // 生成雪花ID并转为字符串（避免精度丢失）
        return (string) self::$snowflake->nextId();
    }

    /**
     * 创建雪花算法实例
     *
     * 从配置文件读取 worker_id 和 datacenter_id。
     * 配置建议在 config/app.php 中添加：
     * - 'snowflake_worker_id' => 0
     * - 'snowflake_datacenter_id' => 0
     *
     * @return Snowflake 雪花算法实例
     * @throws \InvalidArgumentException 配置值超出范围时抛出
     */
    private static function createSnowflakeInstance(): Snowflake
    {
        $workerId = (int) config('app.snowflake_worker_id', 0);
        $dataCenterId = (int) config('app.snowflake_datacenter_id', 0);

        // 校验 worker_id 和 datacenter_id 范围（雪花算法标准：0-31）
        if ($workerId < 0 || $workerId > 31 || $dataCenterId < 0 || $dataCenterId > 31) {
            throw new \InvalidArgumentException("雪花算法 WorkerId 和 DataCenterId 必须在 0-31 之间");
        }

        return new Snowflake($workerId, $dataCenterId);
    }

    /**
     * 重置雪花算法实例
     *
     * 供子类或特殊场景自定义雪花算法实例。
     *
     * @param Snowflake|null $snowflake 自定义的雪花算法实例，null 则重新创建
     * @return void
     */
    public static function resetSnowflakeInstance(?Snowflake $snowflake = null): void
    {
        if ($snowflake === null) {
            self::$snowflake = self::createSnowflakeInstance();
        } else {
            self::$snowflake = $snowflake;
        }
    }

    /**
     * 获取表字段的详细结构信息
     *
     * 基于 Schema，无原生 SQL 依赖。
     *
     * @return array 格式：[字段名 => [type, nullable, default, ...]]
     */
    public function getFieldDetails(): array
    {
        try {
            $table = $this->getTable();
            $connection = $this->getConnection();
            $schema = $connection->getSchemaBuilder();
            $columns = $schema->getColumns($table);

            // 获取当前表的所有主键字段（复合主键也支持）
            $primaryKeys = $this->getPrimaryKeys();

            $fieldDetails = [];
            foreach ($columns as $column) {
                $fieldName = $column['name'] ?? '';
                if (empty($fieldName)) {
                    continue; // 过滤无效字段
                }
                $fieldDetails[$fieldName] = [
                    'auto_increment' => $column['auto_increment'],
                    'type_name' => $column['type_name'] ?? 'unknown',
                    'type' => $column['type'] ?? 'unknown',
                    'nullable' => $column['nullable'] ?? null,
                    'default' => $column['default'] ?? null,
                    'length' => $column['length'] ?? null,
                    'comment' => $column['comment'] ?? null,
                    'primary' => in_array($fieldName, $primaryKeys),
                ];
            }

            return $fieldDetails;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 检查指定字段是否为目标类型
     *
     * 基于 Schema，兼容性强。
     *
     * @param string $column 字段名
     * @param string|array $targetTypes 目标类型（如 'varchar'、['int', 'bigint']）
     * @return bool 是否匹配
     */
    public function isFieldType(string $column, string|array $targetTypes): bool
    {
        if (!is_array($targetTypes)) {
            $targetTypes = [$targetTypes];
        }

        $fieldDetails = $this->getFieldDetails();
        if (!isset($fieldDetails[$column])) {
            return false;
        }

        // 忽略大小写，提升兼容性
        $fieldType = strtolower($fieldDetails[$column]['type']);
        $targetTypes = array_map('strtolower', $targetTypes);

        return in_array($fieldType, $targetTypes);
    }

    /**
     * 获取表的主键字段
     *
     * 独立方法，供关联使用。
     * 支持复合主键。
     *
     * @return array 主键字段列表
     */
    public function getPrimaryKeys(): array
    {
        try {
            $table = $this->getTable();
            $connection = $this->getConnection();
            $schema = $connection->getSchemaBuilder();

            // 获取主键信息（兼容所有 Laravel 版本和数据库驱动）
            $indexes = $schema->getIndexes($table);
            $primaryKeys = [];

            foreach ($indexes as $index) {
                if ($index['primary']) {
                    // 复合主键会返回多个字段，普通主键仅一个
                    $primaryKeys = array_merge($primaryKeys, $index['columns']);
                    break; // 主键索引唯一，找到后直接退出循环
                }
            }

            return $primaryKeys;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("获取主键失败：{$e->getMessage()}");
            return [];
        }
    }
}
