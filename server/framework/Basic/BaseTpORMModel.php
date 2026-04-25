<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: BaseTpORMModel.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Framework\Basic\Traits\TpBelongsToTenant;
use Framework\Utils\Snowflake;
use think\Model as TpModel;
use think\model\concern\SoftDelete as TpSoftDelete;
use think\facade\Config;
use Framework\Tenant\TenantContext;

/**
 * BaseTpORMModel - ThinkPHP 8 模型基类封装
 *
 * 特性：
 * - 雪花ID主键生成
 * - 多租户隔离
 * - 自动时间戳管理（int类型，可配置字段名）
 * - 日期格式化
 * - 软删除支持
 *
 * 子模型可通过以下属性自定义时间字段配置：
 * - $autoWriteTimestamp: 是否自动维护时间戳（默认 true，设为 false 则禁用所有时间相关功能）
 * - $createTime: 创建时间字段名（默认 'create_time'，设为 null 则不使用）
 * - $updateTime: 更新时间字段名（默认 'update_time'，设为 null 则不使用）
 * - $deleteTime: 软删除时间字段名（默认 'delete_time'，设为 null 则不使用）
 *
 * @package Framework\Basic
 */
class BaseTpORMModel extends TpModel
{
    use \Framework\ORM\Trait\ModelTrait;
    use TpBelongsToTenant;

    // =========================================================================
    //  核心配置
    // =========================================================================

    /**
     * 时间戳自动写入
     * 设为 false 则禁用所有时间相关功能
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * 创建时间字段名
     * 子类可覆盖定义，设为 null 或 false 则不自动填充
     * @var string|null|false
     */
    protected $createTime = 'create_time';

    /**
     * 更新时间字段名
     * 子类可覆盖定义，设为 null 或 false 则不自动填充
     * @var string|null|false
     */
    protected $updateTime = 'update_time';

    /**
     * 软删除时间字段名
     * 子类可覆盖定义，设为 null 则不使用
     * @var string|null
     */
    protected $deleteTime = 'delete_time';

    /**
     * 软删除字段默认值（int类型）
     * @var int
     */
    protected $defaultSoftDelete = 0;

    /**
     * 日期字段列表
     * 子类可覆盖扩展
     * @var string[]
     */
    protected $dates = [
        'create_time',
        'update_time',
        'delete_time',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * 日期输出格式
     */
    public const DATE_OUTPUT_FORMAT = 'Y-m-d H:i:s';

    /**
     * 主键类型
     * @var string
     */
    protected string $pkType = 'string';

    /**
     * 全局作用域（多租户）
     * @var array
     */
    protected array $globalScope = ['tenant'];

    /**
     * 只读字段
     * @var array
     */
    protected $readonly = ['created_by', 'tenant_id'];

    /**
     * 雪花算法单例
     * @var Snowflake|null
     */
    private static ?Snowflake $snowflake = null;

    /**
     * 主键策略配置
     * 可选值: 'auto'（自增）, 'snowflake'（雪花ID）
     * @var string
     */
    protected string $pkGenerateType = 'snowflake';

    // =========================================================================
    //  初始化方法
    // =========================================================================

    /**
     * 模型初始化
     *
     * 注意：TP8 中 init() 是静态方法，用于注册事件。
     * 子类覆盖时需调用 parent::init() 避免丢失父类逻辑。
     *
     * @return void
     */
    protected function init()
    {
        parent::init();
    }

    /**
     * 构造函数
     *
     * 处理表前缀兼容逻辑。
     *
     * @param array $data 初始数据
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        // 兼容表前缀
        if (empty($this->name) && empty($this->table)) {
            $prefix = (string) $this->getConfig('prefix');
            $this->name = $this->getName();
            if ($prefix) {
                $this->table = $prefix . $this->name;
            }
        }
    }

    /**
     * 设置主键生成策略
     *
     * @param string $type 策略类型：'auto' 或 'snowflake'
     * @return void
     */
    public function setPkGenerateType(string $type): void
    {
        $this->pkGenerateType = in_array($type, ['auto', 'snowflake']) ? $type : 'auto';
    }

    // =========================================================================
    //  模型事件
    // =========================================================================

    /**
     * 新增前处理
     *
     * 执行主键生成和时间字段填充。
     *
     * @param TpModel $model 模型实例
     * @return void
     */
    protected static function beforeInsert(TpModel $model): void
    {
        // 仅在自动时间戳启用时处理
        if ($model->autoWriteTimestamp) {
            $createTimeField = $model->createTime;
            $updateTimeField = $model->updateTime;

            // 自动填充创建时间
            if (!empty($createTimeField)) {
                $model->setAttr($createTimeField, time());
            }
            // 自动填充更新时间
            if (!empty($updateTimeField)) {
                $model->setAttr($updateTimeField, time());
            }
        }
    }

    /**
     * 模型事件：新增前
     *
     * 执行主键生成、租户ID设置、创建人设置、日期字段规范化。
     *
     * @param TpModel $model 模型实例
     * @return void
     * @throws \BadMethodCallException
     */
    public static function onBeforeInsert(TpModel $model): void
    {
        try {
            self::beforeInsert($model);

            if ($model->pkGenerateType === 'snowflake') {
                self::setPrimaryKey($model);
            }

            self::setTenantId($model);

            self::normalizeDateFields($model);
        } catch (\Exception $e) {
            throw new \BadMethodCallException($e->getMessage());
        }
    }

    /**
     * 模型事件：更新前
     *
     * 执行租户权限检查、日期字段规范化。
     *
     * @param TpModel $model 模型实例
     * @return void
     * @throws \think\exception\ValidateException
     */
    public static function onBeforeUpdate(TpModel $model): void
    {
        // 1. 检查是否越权
        self::checkTenantAccess($model);

        // 2. 更新时间戳
        if ($model->autoWriteTimestamp && !empty($model->updateTime)) {
            $model->setAttr($model->updateTime, time());
        }

        // 3. 日期字段规范化
        self::normalizeDateFields($model);
    }

    /**
     * 模型事件：删除前校验
     *
     * 物理删除和软删除通用。
     *
     * @param TpModel $model 模型实例
     * @return void
     * @throws \think\exception\ValidateException
     */
    public static function onBeforeDelete(TpModel $model): void
    {
        // 检查是否越权
        self::checkTenantAccess($model);
    }

    /**
     * 模型事件：更新后
     *
     * 子类可覆盖扩展。
     *
     * @param TpModel $model 模型实例
     * @return void
     */
    public static function onAfterUpdate(TpModel $model): void
    {
        // 子类可扩展
    }

    /**
     * 模型事件：删除后
     *
     * 物理删除后执行清理逻辑。
     *
     * @param TpModel $model 模型实例
     * @return void
     * @throws \BadMethodCallException
     */
    public static function onAfterDelete(TpModel $model): void
    {
        if ($model->isSoftDeleteEnabled()) {
            return;
        }

        $table = $model->getName();
        $tableData = $model->getData();
        $prefix = $model->getConfig('prefix');

        try {
            // 删除后逻辑（预留）
        } catch (\Exception $e) {
            throw new \BadMethodCallException($e->getMessage());
        }
    }

    /**
     * 模型事件：查询后
     *
     * 格式化日期字段为字符串格式。
     *
     * @param TpModel $model 模型实例
     * @return void
     */
    public static function onAfterRead(TpModel $model): void
    {
        // 只处理模型实例
        if (!($model instanceof self)) {
            return;
        }
        $model->formatDateFields();
    }

    // =========================================================================
    //  日期处理
    // =========================================================================

    /**
     * 子类可扩展日期字段
     *
     * @return array 日期字段列表
     */
    protected function extraDates(): array
    {
        return [];
    }

    /**
     * 统一日期字段入口
     *
     * 合并类属性 $dates 和子类扩展字段。
     *
     * @return array 日期字段列表
     */
    public function getDates(): array
    {
        return array_values(array_unique(array_merge(
            $this->dates ?? [],
            $this->extraDates()
        )));
    }

    /**
     * 格式化日期字段
     *
     * 将时间戳转换为字符串格式。
     *
     * @return void
     */
    private function formatDateFields(): void
    {
        foreach ($this->getDates() as $field) {
            $value = $this->getData($field);
            if ($value > 0) {
                $this->setAttr($field, date(self::DATE_OUTPUT_FORMAT, (int)$value));
            }
        }
    }

    /**
     * 格式化日期字段（不破坏原始 data）
     *
     * 创建虚拟字段保存格式化后的值。
     *
     * @return void
     */
    protected function formatDateFields1(): void
    {
        foreach ($this->getDates() as $field) {
            // 原始值（int）
            $raw = $this->getData($field);

            if (!$raw || !is_numeric($raw)) {
                continue;
            }

            // 放到 append 中作为展示字段
            $this->append([$field . '_text']);

            // 动态设置虚拟字段值
            $this->setAttr($field . '_text', date(self::DATE_OUTPUT_FORMAT, (int)$raw));
        }
    }

    /**
     * 将日期字符串/时间戳转为 int 时间戳
     *
     * @param mixed $value 输入值
     * @return int 时间戳
     */
    private function convertToTimestamp($value): int
    {
        if (is_numeric($value)) {
            return (int)$value;
        } else {
            $timestamp = strtotime($value);
            return $timestamp !== false ? (int)$timestamp : 0;
        }
    }

    /**
     * 将所有日期字段统一转为 int 时间戳
     *
     * @param TpModel $model 模型实例
     * @return void
     */
    protected static function normalizeDateFields(TpModel $model): void
    {
        foreach ($model->getDates() as $field) {
            // 如果模型里根本没有这个字段，跳过
            if (!$model->hasData($field)) {
                continue;
            }

            $value = $model->getData($field);

            // 已经是 int，直接跳过
            if (is_int($value) || ctype_digit((string)$value)) {
                continue;
            }

            // 空值处理
            if ($value === null || $value === '') {
                continue;
            }

            // 尝试解析字符串日期
            $timestamp = strtotime((string)$value);

            if ($timestamp !== false) {
                $model->setAttr($field, $timestamp);
            }
        }
    }

    // =========================================================================
    //  字段相关
    // =========================================================================

    /**
     * 获取字段列表
     *
     * 安全处理，确保返回数组。
     *
     * @param string|null $field 指定字段名
     * @return array|mixed 字段列表或指定字段信息
     */
    public function getFields(?string $field = null): mixed
    {
        $res = parent::getFields($field);
        return $field ? $res : ($res ?: []);
    }

    // =========================================================================
    //  软删除
    // =========================================================================

    /**
     * 判断是否开启软删除
     *
     * @return bool 是否开启
     */
    public function isSoftDeleteEnabled(): bool
    {
        return method_exists($this, 'delete');
    }

    /**
     * 强制物理删除
     *
     * @param mixed $id 主键值
     * @return bool 是否成功
     */
    public static function forceDeleteById($id): bool
    {
        return self::withTrashed()->where((new static)->getPk(), $id)->delete(true);
    }

    /**
     * 恢复软删除
     *
     * @param mixed $id 主键值
     * @return bool 是否成功
     */
    public static function restoreById($id): bool
    {
        $model = self::onlyTrashed()->find($id);
        return $model ? $model->restore() : false;
    }

    // =========================================================================
    //  租户相关
    // =========================================================================

    /**
     * 安全的 Join 方法，自动追加租户ID
     *
     * @param mixed $query 查询构建器
     * @param string $joinTable 关联表名（如 'oa_order'）
     * @param string $alias 关联表别名（如 'o'）
     * @param string $condition 关联条件（如 'o.user_id = u.id'）
     * @param string $type JOIN类型（LEFT, INNER等）
     * @return void
     */
    public function scopeJoinTenant($query, string $joinTable, string $alias, string $condition, string $type = 'LEFT')
    {
        $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;

        // 只有当存在租户ID时，才追加限制
        if ($tenantId) {
            $condition .= " AND {$alias}.tenant_id = {$tenantId}";
        }

        // 执行原生 join
        $query->join("{$joinTable} {$alias}", $condition, $type);
    }

    /**
     * 获取完整表名
     *
     * @return string 表名
     */
    public static function getTableName(): string
    {
        return (new static)->getTable();
    }

    /**
     * 获取当前租户ID
     *
     * 封装函数依赖。
     *
     * @return string|null 租户ID
     */
    protected static function getCurrentTenantId(): ?string
    {
        if (class_exists(TenantContext::class)) {
            return (string)TenantContext::getTenantId() ?? null;
        }
        return null;
    }

    /**
     * 获取当前用户ID
     *
     * @return mixed 用户ID
     */
    protected static function getCurrentUser()
    {
        return function_exists('getCurrentUser') ? getCurrentUser() : null;
    }

    /**
     * 数据安全检查
     *
     * 防止越权操作。在更新和删除前触发。
     *
     * @param TpModel $model 模型实例
     * @return void
     * @throws \think\exception\ValidateException
     */
    protected static function checkTenantAccess(TpModel $model): void
    {
        $currentTenantId = self::getCurrentTenantId();
        if (!$currentTenantId) {
            return; // 无租户上下文，跳过
        }

        // 获取数据原始的 tenant_id
        $dataTenantId = $model->getOrigin('tenant_id');

        if ($dataTenantId && (string)$dataTenantId !== (string)$currentTenantId) {
            throw new \think\exception\ValidateException('无权操作此条数据（租户不匹配）');
        }
    }

    // =========================================================================
    //  雪花ID生成
    // =========================================================================

    /**
     * 生成雪花ID
     *
     * 使用单例模式，全局只初始化一次雪花算法实例。
     *
     * @return int 雪花ID
     */
    protected static function generateSnowflakeID(): int
    {
        if (self::$snowflake === null) {
            $workerId = 1;
            $datacenterId = 1;
            self::$snowflake = new Snowflake($workerId, $datacenterId);
        }
        return self::$snowflake->nextId();
    }

    // =========================================================================
    //  私有辅助方法
    // =========================================================================

    /**
     * 设置主键
     *
     * 仅雪花ID模式生效。
     *
     * @param TpModel $model 模型实例
     * @return void
     */
    private static function setPrimaryKey(TpModel $model): void
    {
        $pk = $model->getPk();
        if (is_string($pk) && empty($model->{$pk})) {
            $model->{$pk} = (string) self::generateSnowflakeID();
        }
    }

    /**
     * 设置租户ID
     *
     * @param TpModel $model 模型实例
     * @return void
     */
    private static function setTenantId(TpModel $model): void
    {
        if (!isset($model->tenant_id)) {
            $tenantId = self::getCurrentTenantId();
            if ($tenantId) {
                $model->setAttr('tenant_id', $tenantId);
            }
        }
    }
}
