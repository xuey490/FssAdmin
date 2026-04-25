<?php

declare(strict_types=1);

/**
 * Casbin 适配器
 *
 * @package App\Services\Casbin
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services\Casbin;

use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use Casbin\Persist\UpdatableAdapter;
use Casbin\Persist\BatchAdapter;
use Casbin\Persist\FilteredAdapter;
use Casbin\Persist\Adapters\Filter;
use Casbin\Exceptions\InvalidFilterTypeException;

/**
 * DatabaseAdapter Casbin 数据库适配器
 *
 * 职责范围：
 * - 持久层适配器，负责 Casbin 策略规则的数据库存储和加载
 * - 与 Webman 框架的数据库层深度集成
 * - 提供完整的 Casbin 接口实现，支持批量操作、更新操作和过滤加载
 *
 * 实现的接口：
 * - Adapter: 基础策略加载和保存
 * - UpdatableAdapter: 策略更新功能
 * - BatchAdapter: 批量策略操作
 * - FilteredAdapter: 过滤式策略加载
 *
 * @package App\Services\Casbin
 * @author  Genie
 * @date    2026-03-12
 */
class DatabaseAdapter implements Adapter, UpdatableAdapter, BatchAdapter, FilteredAdapter
{
    use AdapterHelper;
    protected const MAX_RULE_COLUMNS = 6;

    /**
     * 标记当前策略是否经过过滤
     *
     * @var bool
     */
    private bool $filtered = false;

    /**
     * 表名
     * @var string
     */
    protected string $tableName;

    /**
     * 数据库连接
     * @var string|null
     */
    protected ?string $connection;

    /**
     * 构造函数
     *
     * @param string      $tableName  表名
     * @param string|null $connection 数据库连接
     */
    public function __construct(string $tableName = 'casbin_rule', ?string $connection = null)
    {
        $this->tableName = $tableName;
        $this->connection = $connection;
    }

    /**
     * 获取数据库查询构建器
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery(): \Illuminate\Database\Query\Builder
    {
        if ($this->connection !== null && $this->connection !== '') {
            $capsule = app('db')->getDriver()->getCapsule();
            return $capsule->getConnection($this->connection)->table($this->tableName);
        }

        return app('db')->table($this->tableName);
    }

    /**
     * 加载所有策略规则
     *
     * @param Model $model 模型
     * @return void
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->getQuery()->get();

        foreach ($rows as $row) {
            $line = $this->rowToLine($row);
            if ($line !== '') {
                $this->loadPolicyLine($line, $model);
            }
        }
    }

    /**
     * 保存所有策略规则
     *
     * @param Model $model 模型
     * @return void
     */
    public function savePolicy(Model $model): void
    {
        app('db')->transaction(function () use ($model) {
            $this->getQuery()->delete();

            foreach ($model['p'] as $ptype => $ast) {
                foreach ($ast->policy as $rule) {
                    $this->savePolicyLine($ptype, $rule);
                }
            }

            foreach ($model['g'] as $ptype => $ast) {
                foreach ($ast->policy as $rule) {
                    $this->savePolicyLine($ptype, $rule);
                }
            }
        });
    }

    /**
     * 添加策略规则
     *
     * @param string $sec  区域
     * @param string $ptype 策略类型
     * @param array  $rule  规则
     * @return void
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * 移除策略规则
     *
     * @param string $sec  区域
     * @param string $ptype 策略类型
     * @param array  $rule  规则
     * @return void
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $query = $this->getQuery()->where('ptype', $ptype);

        foreach (array_values($rule) as $index => $value) {
            if ($index >= self::MAX_RULE_COLUMNS) {
                break;
            }
            $query->where('v' . $index, $value);
        }

        $query->delete();
    }

    /**
     * 移除过滤后的策略规则
     *
     * @param string $sec   区域
     * @param string $ptype 策略类型
     * @param int    $field 字段索引
     * @param string ...$values 值
     * @return void
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $field, string ...$values): void
    {
        $query = $this->getQuery()->where('ptype', $ptype);

        foreach ($values as $key => $value) {
            $columnIndex = $field + $key;
            if ($columnIndex >= self::MAX_RULE_COLUMNS) {
                break;
            }
            if ($value === '') {
                continue;
            }
            $query->where('v' . $columnIndex, $value);
        }

        $query->delete();
    }

    /**
     * 批量添加策略规则（BatchAdapter 接口）
     *
     * 将多条策略规则一次性插入数据库，提高批量操作效率
     *
     * @param string     $sec   策略段（'p' 或 'g'）
     * @param string     $ptype 策略类型
     * @param string[][] $rules 策略规则二维数组
     * @return void
     */
    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        $cols = [];

        foreach ($rules as $rule) {
            // 兼容历史两段 p 策略：自动补齐 action = "*"
            if (str_starts_with($ptype, 'p') && count($rule) === 2) {
                $rule[] = '*';
            }

            $data = ['ptype' => $ptype];
            $rule = array_values(array_slice($rule, 0, self::MAX_RULE_COLUMNS));

            foreach ($rule as $index => $value) {
                $data['v' . $index] = (string) $value;
            }

            // 填充空值
            for ($i = count($rule); $i < self::MAX_RULE_COLUMNS; $i++) {
                $data['v' . $i] = '';
            }

            $cols[] = $data;
        }

        // 批量插入
        if (!empty($cols)) {
            $this->getQuery()->insert($cols);
        }
    }

    /**
     * 批量移除策略规则（BatchAdapter 接口）
     *
     * 使用事务保证批量删除操作的原子性
     *
     * @param string     $sec   策略段（'p' 或 'g'）
     * @param string     $ptype 策略类型
     * @param string[][] $rules 策略规则二维数组
     * @return void
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        app('db')->transaction(function () use ($sec, $ptype, $rules) {
            foreach ($rules as $rule) {
                $this->removePolicy($sec, $ptype, $rule);
            }
        });
    }

    /**
     * 更新单条策略规则（UpdatableAdapter 接口）
     *
     * 找到旧规则并将其更新为新规则
     * 包含空值判断避免异常
     *
     * @param string   $sec       策略段（'p' 或 'g'）
     * @param string   $ptype     策略类型
     * @param string[] $oldRule   旧策略规则数组
     * @param string[] $newPolicy 新策略规则数组
     * @return void
     */
    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newPolicy): void
    {
        $where = ['ptype' => $ptype];
        foreach (array_values($oldRule) as $index => $value) {
            if ($index >= self::MAX_RULE_COLUMNS) {
                break;
            }
            $where['v' . $index] = $value;
        }

        $instance = $this->getQuery()->where($where)->first();

        // 空值判断，避免异常
        if (is_null($instance)) {
            return;
        }

        $update = [];
        foreach (array_values($newPolicy) as $index => $value) {
            if ($index >= self::MAX_RULE_COLUMNS) {
                break;
            }
            $update['v' . $index] = (string) $value;
        }

        $this->getQuery()->where($where)->update($update);
    }

    /**
     * 批量更新策略规则（UpdatableAdapter 接口）
     *
     * 使用事务保证批量更新操作的原子性
     * 包含边界检查确保新规则数组索引存在
     *
     * @param string     $sec      策略段（'p' 或 'g'）
     * @param string     $ptype    策略类型
     * @param string[][] $oldRules 旧策略规则二维数组
     * @param string[][] $newRules 新策略规则二维数组
     * @return void
     */
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        app('db')->transaction(function () use ($sec, $ptype, $oldRules, $newRules) {
            foreach ($oldRules as $i => $oldRule) {
                // 边界检查：确保新规则数组索引存在
                if (!isset($newRules[$i])) {
                    continue;
                }
                $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
            }
        });
    }

    /**
     * 更新匹配过滤条件的策略规则（UpdatableAdapter 接口）
     *
     * 删除匹配过滤条件的旧规则，并添加新的策略规则
     * 使用事务保证操作原子性
     *
     * @param string   $sec          策略段（'p' 或 'g'）
     * @param string   $ptype        策略类型
     * @param array    $newPolicies  新策略规则数组
     * @param int      $fieldIndex   字段起始索引
     * @param string   ...$fieldValues 字段值列表
     * @return array 被删除的旧规则数组
     */
    public function updateFilteredPolicies(string $sec, string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues): array
    {
        $oldRules = [];

        app('db')->transaction(function () use ($sec, $ptype, $fieldIndex, $fieldValues, $newPolicies, &$oldRules) {
            // 获取被删除的旧规则
            $oldRules = $this->getFilteredPolicies($ptype, $fieldIndex, $fieldValues);

            // 删除匹配的旧规则
            $this->removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);

            // 添加新规则
            $this->addPolicies($sec, $ptype, $newPolicies);
        });

        return $oldRules;
    }

    /**
     * 获取匹配过滤条件的策略规则（内部辅助方法）
     *
     * 根据字段索引和字段值过滤策略规则，返回匹配的规则数组
     *
     * @param string $ptype       策略类型
     * @param int    $fieldIndex  字段起始索引（0-5）
     * @param array  $fieldValues 字段值数组
     * @return array 匹配的规则数组
     */
    protected function getFilteredPolicies(string $ptype, int $fieldIndex, array $fieldValues): array
    {
        $query = $this->getQuery()->where('ptype', $ptype);

        foreach (range(0, self::MAX_RULE_COLUMNS - 1) as $value) {
            $offset = $value - $fieldIndex;
            if ($fieldIndex <= $value && $offset < count($fieldValues)) {
                $fieldValue = $fieldValues[$offset];
                if ($fieldValue !== '' && !is_null($fieldValue)) {
                    $query->where('v' . $value, $fieldValue);
                }
            }
        }

        $rows = $query->get();
        $rules = [];

        foreach ($rows as $row) {
            $rule = [];
            for ($i = 0; $i < self::MAX_RULE_COLUMNS; $i++) {
                $field = 'v' . $i;
                if (isset($row->$field) && $row->$field !== '') {
                    $rule[] = (string) $row->$field;
                }
            }
            if (!empty($rule)) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * 加载匹配过滤条件的策略规则（FilteredAdapter 接口）
     *
     * 根据过滤条件从数据库加载策略规则，支持多种过滤方式：
     * - 字符串：原生 SQL WHERE 条件
     * - Filter 对象：Casbin 标准过滤器
     * - Closure 闭包：自定义过滤逻辑
     *
     * @param Model $model  Casbin 模型实例
     * @param mixed $filter 过滤条件（字符串/Filter对象/闭包）
     * @throws InvalidFilterTypeException 无效的过滤类型异常
     * @return void
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        $query = $this->getQuery();

        if (is_string($filter)) {
            // 字符串过滤：原生 SQL WHERE 条件
            $query = $query->whereRaw($filter);
        } elseif ($filter instanceof Filter) {
            // Filter 对象过滤：Casbin 标准过滤器
            foreach ($filter->p as $k => $v) {
                $query = $query->where($v, $filter->g[$k]);
            }
        } elseif ($filter instanceof \Closure) {
            // 闭包过滤：自定义过滤逻辑
            $query = $query->where($filter);
        } else {
            throw new InvalidFilterTypeException('invalid filter type');
        }

        $rows = $query->get();

        foreach ($rows as $row) {
            $line = $this->rowToLine($row);
            if ($line !== '') {
                $this->loadPolicyLine($line, $model);
            }
        }

        $this->setFiltered(true);
    }

    /**
     * 判断当前策略是否已过滤（FilteredAdapter 接口）
     *
     * @return bool 如果策略已过滤返回 true，否则返回 false
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    /**
     * 设置策略过滤状态（FilteredAdapter 接口）
     *
     * @param bool $filtered 是否已过滤
     * @return void
     */
    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }

    /**
     * 将数据库行转换为策略行
     *
     * @param object $row 数据库行
     * @return string
     */
    protected function rowToLine(object $row): string
    {
        $ptype = (string) ($row->ptype ?? '');
        if ($ptype === '') {
            return '';
        }

        $values = [];
        for ($i = 0; $i < self::MAX_RULE_COLUMNS; $i++) {
            $field = 'v' . $i;
            $values[$i] = isset($row->$field) ? trim((string) $row->$field) : '';
        }

        // p 规则至少应有 sub/obj/act，兼容历史两段规则时补齐 act='*'
        if (str_starts_with($ptype, 'p')) {
            if ($values[0] === '' || $values[1] === '') {
                return '';
            }
            if ($values[2] === '') {
                $values[2] = '*';
            }
        }

        // g 规则至少应有 user/role
        if (str_starts_with($ptype, 'g') && ($values[0] === '' || $values[1] === '')) {
            return '';
        }

        $lastIndex = -1;
        for ($i = self::MAX_RULE_COLUMNS - 1; $i >= 0; $i--) {
            if ($values[$i] !== '') {
                $lastIndex = $i;
                break;
            }
        }

        if ($lastIndex < 0) {
            return '';
        }

        $line = $ptype;
        for ($i = 0; $i <= $lastIndex; $i++) {
            $line .= ', ' . $values[$i];
        }

        return $line;
    }

    /**
     * 保存策略行
     *
     * @param string $ptype 策略类型
     * @param array  $rule  规则
     * @return void
     */
    protected function savePolicyLine(string $ptype, array $rule): void
    {
        // 兼容历史两段 p 策略：自动补齐 action = "*"
        if (str_starts_with($ptype, 'p') && count($rule) === 2) {
            $rule[] = '*';
        }

        $data = ['ptype' => $ptype];
        $rule = array_values(array_slice($rule, 0, self::MAX_RULE_COLUMNS));

        foreach ($rule as $index => $value) {
            $data['v' . $index] = (string) $value;
        }

        // 填充空值
        for ($i = count($rule); $i < self::MAX_RULE_COLUMNS; $i++) {
            $data['v' . $i] = '';
        }

        $this->getQuery()->insert($data);
    }
}
