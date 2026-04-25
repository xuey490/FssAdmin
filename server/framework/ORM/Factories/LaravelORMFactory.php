<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: LaravelORMFactory.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\ORM\Factories;

use Framework\Core\App;
use Framework\ORM\Exception\Exception;
use Framework\Utils\Arr;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class LaravelORMFactory
{
    private mixed $modelClass;

    private ?Model $modelInstance = null;

    /**
     * 构造函数.
     * @param Model|string|null $model 模型类名或实例
     */
    public function __construct(Model|string|null $model = null)
    {
        if (is_object($model)) {
            $this->modelInstance = $model;
            $this->modelClass = get_class($model);
        } else {
            $this->modelClass = $model;
        }
    }

    /**
     * 获取模型实例 (懒加载).
     */
    public function getModel(): Model
    {
        if ($this->modelInstance) {
            return $this->modelInstance;
        }

        try {
            $class = $this->modelClass;
            if (!class_exists($class)) {
                throw new Exception($class . ' 不是一个有效的模型类');
            }
            $this->modelInstance = App::make($class);
            return $this->modelInstance;
        } catch (Throwable $e) {
            throw new Exception('模型加载失败: ' . $e->getMessage());
        }
    }

    private function splitWhere(array $where): array
    {
        $special = [];
        foreach ($where as $key => $condition) {
            if (is_array($condition) && count($condition) === 3) {
                $op = strtolower($condition[1]);
                if ($op === 'in' || $op === 'not in') {
                    $special[] = $condition;
                    unset($where[$key]);
                }
            }
        }
        return [$where, $special];
    }

    private function applyConditions(Builder $query, array $where): void
    {
		
        [$normal, $special] = $this->splitWhere($where);
        if (!empty($normal)) {
            $query->where($normal);
        }
        foreach ($special as $condition) {
            if (empty($condition[2])) {
                continue;
            }
            $operator = strtolower($condition[1]);
            $value = Arr::normalize($condition[2]);
            if ($operator === 'in') {
                $query->whereIn($condition[0], $value);
            } elseif ($operator === 'not in') {
                $query->whereNotIn($condition[0], $value);
            }
        }
    }

    private function buildQuery(array $where = []): Builder
    {
		
        $query = $this->getModel()->query();
        if (!empty($where)) {
            $this->applyConditions($query, $where);
        }
        return $query;
    }

    private function applyFields(Builder $query, array|string $field): void
    {
        $isWildcard = ($field === '*' || ($field === ['*']));
        if ($isWildcard) {
            return;
        }
        if (is_array($field)) {
            $field = array_filter($field, function ($f) { return !empty($f); });
            $field = implode(',', $field);
        }
        if (!empty($field)) {
            $query->selectRaw($field);
        }
    }

     /**
     * 获取条数
     *
     * @param array $where
     * @param bool  $search
     *
     * @return int
     * @throws Exception
     */
    public function count(array $where = [], bool $search = false): int
    {
        $query = $search ? $this->search($where) : $this->buildQuery($where);

        return $query->count();
    }


    /**
     * 查询列表
     *
     * @param array $where
     * @param string|array $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param array $with
     * @param bool $search
     * @param array|null $withoutScopes
     *
     * @return Collection|null
     * @throws Exception
     */
    public function selectList(array $where, string|array $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): ?Collection
    {
        $query = $this->selectModel($where, $field, $page, $limit, $order, $with, $search, $withoutScopes);
        $this->applyFields($query, $field);

        // 应用分页
        if ($page > 0 && $limit > 0) {
            // 只返回数据部分
            return $query->paginate($limit, ['*'], 'page', $page)->getCollection();
        }
        // var_dump($query->toSql());die;
        return $query->get(); // 返回所有数据
    }


    /**
     * 获取某些条件数据
     *
     * @param array $where
     * @param array|string $field
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param array $with
     * @param bool $search
     * @param array|null $withoutScopes
     *
     * @return Builder|\Illuminate\Database\Query\Builder|LengthAwarePaginator|null
     * @throws Exception
     */
    public function selectModel(array $where, array|string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): Builder|\Illuminate\Database\Query\Builder|LengthAwarePaginator|null
    {
        $query = $this->getModel()->query();

        // 作用域处理
        if (!empty($withoutScopes)) {
            $this->applyScopeRemoval($query, $withoutScopes);
        }

        if ($search) {
            $query = $this->search($where);
        } else {
            $this->applyConditions($query, $where);
        }

        // 应用字段选择
        $this->applyFields($query, $field);
        if ($order !== '') {
            $query->orderByRaw($order);
        }
        if (!empty($with)) {
            $query->with($with);
        }
        return $query; // 返回查询构建器
    }

    /**
     * 获取条数
     *
     * @param array $where
     *
     * @return int
     * @throws Exception
     */
    public function getCount(array $where): int
    {
        $query = $this->buildQuery($where);
        return $query->count();
    }

    /**
     * 计算符合条件的唯一记录数量
     *
     * @param array  $where
     * @param string $field
     * @param bool   $search
     *
     * @return int
     * @throws Exception
     */
    public function getDistinctCount(array $where, string $field, bool $search = true): int
    {
        $query = $search ? $this->search($where) : $this->buildQuery($where);
        // 获取唯一计数
        return $query->distinct()->count($field);
    }


    /**
     * 获取模型主键
     *
     * @return string
     * @throws Exception
     */
    public function getPk(): string
    {
        return $this->getModel()->getKeyName();
    }

    /**
     * 获取表名
     *
     * @return string
     * @throws ReflectionException|Exception
     */
    public function getTableName(): string
    {
        return $this->getModel()->getTable();
    }

    /**
     * 获取一条数据
     *
     * @param            $id
     * @param array|null $field
     * @param array|null $with
     * @param string     $order
     * @param array|null $withoutScopes
     *
     * @return Model|null
     * @throws \Exception
     */
    public function get($id, ?array $field = null, ?array $with = [], string $order = '', ?array $withoutScopes = null): ?Model
    {
        $where = is_array($id) ? $id : [$this->getPk() => $id];
        $query = $this->buildQuery($where);
        $this->applyScopeRemoval($query, $withoutScopes);
        // 添加关联加载
        if (!empty($with)) {
            $query->with($with);
        }
        // 添加排序条件
        if ($order !== '') {
            $query->orderByRaw($order);
        }
        return $query->select($field ?? ['*'])->first();
    }


    /**
     * 查询一条数据是否存在
     *
     * @param        $map
     * @param string $field
     *
     * @return bool
     * @throws Exception
     */
    public function be($map, string $field = ''): bool
    {
        // 如果 $map 不是数组且 $field 为空，使用主键
        if (!is_array($map) && empty($field)) {
            $field = $this->getPk();
        }

        // 如果 $map 不是数组，将其转换为数组
        $map = !is_array($map) ? [$field => $map] : $map;

        $query = $this->buildQuery($map);
        return $query->exists();
    }


    /**
     * 根据条件获取一条数据
     *
     * @param array       $where
     * @param string|null $field
     * @param array       $with
     *
     * @return Model|null
     * @throws Exception
     */
    public function getOne(array $where, ?string $field = '*', array $with = []): ?Model
    {
        // 将字段字符串转换为数组
        $fieldArray = $field === '*' ? ['*'] : explode(',', $field);

        $query = $this->buildQuery($where);
        // 添加关联加载
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->select($fieldArray)->first();
    }


    /**
     * 获取某字段的值
     *
     * @param             $where
     * @param string|null $field
     *
     * @return mixed
     * @throws Exception
     */
    public function value($where, ?string $field = null): mixed
    {
        $pk = $this->getPk(); // 获取主键
        $where = $this->setWhere($where); // 设置查询条件

        $query = $this->buildQuery($where);
        return $query->value($field ?? $pk);
    }


    /**
     * 获取某个字段数组
     *
     * @param array  $where
     * @param string $field
     * @param string $key
     *
     * @return array
     * @throws ReflectionException|Exception
     */
    public function getColumn(array $where, string $field, string $key = ''): array
    {
        $query = $this->buildQuery($where);


        // 如果指定了键，则使用 keyBy 方法
        if ($key) {
            return $query->pluck($field, $key)->toArray();
        }

        // 否则，直接获取字段数组
        return $query->pluck($field)->toArray();
    }

    /**
     * 删除
     *
     * @param array|int|string $id
     * @param string|null      $key
     *
     * @return mixed
     * @throws Exception
     */
    public function delete(array|int|string $id, ?string $key = null): int
    {
        try {
            $query = $this->getModel()->query();
            if (is_array($id)) {
                if (array_is_list($id)) {
                    return (int)$this->getModel()->destroy($id);
                } else {
                    foreach ($id as $field => $value) {
                        $query->where($field, $value);
                    }
                }
            } else {
                $query->where($key ?: $this->getPk(), $id);
            }
            $models = $query->get();
            foreach ($models as $model) {
                $model->delete();//触发事件
            }
            return $models->count();
        } catch (Exception $e) {
            throw new Exception("删除失败:" . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * 删除记录
     *
     * @param mixed $id
     * @param bool  $force
     *
     * @return bool
     * @throws Exception
     */
    public function destroy(mixed $id, bool $force = false): bool
    {
        // 使用 Eloquent 的 destroy 方法删除记录
        return $this->getModel()->destroy($id, $force) > 0;
    }

    /**
     * 更新
     *
     * @param string|int|array $id
     * @param array            $data
     * @param string|null      $key
     *
     * @return mixed
     * @throws Exception
     */
    public function update(string|int|array $id, array $data, ?string $key = null): mixed
    {
        $where             = is_array($id) ? $id : [is_null($key) ? $this->getPk() : $key => $id];
        $query             = $this->getModel()->query();
        $whereInConditions = [];
        $whereInKeys       = [];

        // 分离 IN 条件
        foreach ($where as $key => $condition) {
            if (!is_array($condition)) {
                //条件不是数组直接跳过
                continue;
            }
            if ($this->isInCondition($condition)) {
                $whereInKeys[]       = $key;
                $whereInConditions[] = $condition;
            }
        }

        // 移除已处理的 IN 条件
        foreach ($whereInKeys as $key) {
            unset($where[$key]);
        }

        // 处理普通条件
        if (!empty($where)) {
            $query->where($where);
        }

        // 处理 IN/NOT IN 条件
        foreach ($whereInConditions as $condition) {
            list($column, $operator, $value) = $condition;
            $normalizedValue = is_array($value) ? $value : [$value];
            $query->where(function ($q) use ($column, $operator, $normalizedValue) {
                $op = strtolower($operator);
                if ($op === 'in') {
                    $q->whereIn($column, $normalizedValue);
                } elseif ($op === 'not in') {
                    $q->whereNotIn($column, $normalizedValue);
                }
            });
        }
        return $query->update($data);
    }

    /**
     * setWhere
     *
     * @param             $where
     * @param string|null $key
     *
     * @return array
     * @throws Exception
     */
    protected function setWhere($where, ?string $key = null): array
    {
        // 如果 $where 不是数组，则构建数组
        if (!is_array($where)) {
            $where = [is_null($key) ? $this->getPk() : $key => $where];
        }
        return $where;
    }

    /**
     * 批量更新
     *
     * @param array       $ids
     * @param array       $data
     * @param string|null $key
     *
     * @return mixed
     * @throws Exception
     */
    public function batchUpdate(array $ids, array $data, ?string $key = null): bool
    {
        return $this->getModel()->whereIn(is_null($key) ? $this->getPk() : $key, $ids)->update($data);
    }

    /**
     * 保存返回模型
     *
     * @param array $data
     *
     * @return Model|null
     * @throws Exception
     */
    public function save(array $data): ?Model
    {
        return $this->getModel()->create($data);
    }

    /**
     * 批量插入
     *
     * @param array $data
     *
     * @return bool
     */
    public function saveAll(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        try {
            $models = $this->getModel()->newCollection();

            foreach ($data as $item) {
                $models->push($this->getModel()->newInstance($item));
            }

            $savedAll = true;
            $models->each(function ($model) use (&$savedAll) {
                if (!$model->save()) {
                    $savedAll = false;
                }
            });

            return $savedAll;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取某字段内的值
     *
     * @param             $value
     * @param string      $field
     * @param string|null $valueKey
     * @param array|null  $where
     *
     * @return mixed
     * @throws Exception
     */
    public function getFieldValue($value, string $field, ?string $valueKey = null, ?array $where = []): mixed
    {
        // 如果提供了 $valueKey，则构建查询条件
        if ($valueKey) {
            $where[$valueKey] = $value; // 将 valueKey 和 value 加入条件
        } else {
            $where[$this->getPk()] = $value; // 默认使用主键作为条件
        }

        // 使用 Eloquent 查询构建器获取字段值
        $query = $this->getModel()->query();

        $whereInConditions = [];
        foreach ($where as $key => $condition) {
            if (is_array($condition) && count($condition) === 3) {
                $operator = strtolower($condition[1]);
                if ($operator === 'in' || $operator === 'not in') {
                    $whereInConditions[] = $condition;
                    unset($where[$key]);//移除分离后的条件
                }
            }
        }
        //普通条件格式直接传入构建
        if (!empty($where)) {
            $query->where($where);
        }

        //特殊条件格式额外处理
        foreach ($whereInConditions as $condition) {
            if (empty($condition[2])) {
                continue;
            }
            $operator = strtolower($condition[1]);
            $value = Arr::normalize($condition[2]);
            if ($operator === 'in') {
                $query->whereIn($condition[0], $value);
            } elseif ($operator === 'not in') {
                $query->whereNotIn($condition[0], $value);
            }
        }

        return $query->value($field);
    }


    /**
     * 获取搜索器和搜索条件key,以及不在搜索器的条件数组
     *
     * @param array $where
     *
     * @return array[]
     * @throws ReflectionException
     */
    private function getSearchData(array $where): array
    {
        $with       = [];
        $withValues = []; // 用于存储与 $with 对应的值
        $otherWhere = [];
        $model      = $this->getModel();
        $responses  = new \ReflectionClass($model);

        foreach ($where as $key => $value) {
            $method = 'scope' . self::studly($key);
            if ($responses->hasMethod($method)) {
                $with[]           = $key; // 将搜索器方法的键加入 $with
                $withValues[$key] = $value; // 将对应的值存储到 $withValues
            } else {
                // 过滤不在搜索器中的条件
                if (!in_array($key, ['timeKey', 'store_stock', 'integral_time'])) {
                    if (!is_array($value)) {
                        $otherWhere[] = [$key, '=', $value]; // 单个条件
                    } elseif (count($value) === 3) {
                        $otherWhere[] = $value; // 复杂条件
                    }
                }
            }
        }
        return [$with, $withValues, $otherWhere]; // 返回 $with, $withValues 和 $otherWhere
    }

    /**
     * 根据搜索器获取内容
     *
     * @param array $where
     * @param bool  $search
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws Exception
     */
    protected function withSearchSelect(array $where, bool $search): mixed
    {
        [$with, $withValues, $otherWhere] = $this->getSearchData($where);
        $query = $this->getModel()->query();
        foreach ($with as $item) {
            $func = self::studly($item);
            if (method_exists($this->getModel(), 'scope' . $func)) {
                $value = $withValues[$item] ?? null;
                if ($value !== null) {
                    $query->$func($value);
                }
            }
        }
        $filteredWhere = $this->filterWhere($otherWhere);
        if (!empty($filteredWhere)) {
            $query->where($filteredWhere);
        }
        return $query; // 返回查询构建器
    }

    /**
     * 过滤数据表中不存在的字段
     *
     * @param array $where
     *
     * @return array
     * @throws Exception
     */
    protected function filterWhere(array $where = []): array
    {
        $fields = $this->getModel()->getFields(); // 获取模型的可填充字段
        foreach ($where as $key => $item) {
            // 检查键是否在可填充字段中
            if (!in_array($key, $fields)) {
                unset($where[$key]); // 过滤掉不存在的字段
            }
        }
        return $where; // 返回过滤后的条件
    }

    /**
     * 搜索
     *
     * @param array $where
     * @param bool  $search
     *
     * @return mixed
     * @throws Exception
     */
    public function search(array $where = [], bool $search = true): mixed
    {
        if ($where) {
            return $this->withSearchSelect($where, $search); // 返回查询构建器
        } else {
            return $this->getModel(); // 返回模型实例
        }
    }

    /**
     * 求和
     *
     * @param array  $where
     * @param string $field
     * @param bool   $search
     *
     * @return float
     * @throws Exception
     */
    public function sum(array $where, string $field, bool $search = false): float
    {
        // 构建查询
        $query = $this->getModel()->query();
        // 应用搜索条件
        if ($search) {
            $query = $this->search($where);
        } else {
            $whereInConditions = [];
            foreach ($where as $key => $condition) {
                if (is_array($condition) && count($condition) === 3) {
                    $operator = strtolower($condition[1]);
                    if ($operator === 'in' || $operator === 'not in') {
                        $whereInConditions[] = $condition;
                        unset($where[$key]);//移除分离后的条件
                    }
                }
            }
            //普通条件格式直接传入构建
            if (!empty($where)) {
                $query->where($where);
            }

            //特殊条件格式额外处理
            foreach ($whereInConditions as $condition) {
                if (empty($condition[2])) {
                    continue;
                }
                $operator = strtolower($condition[1]);
                $value = Arr::normalize($condition[2]);
                if ($operator === 'in') {
                    $query->whereIn($condition[0], $value);
                } elseif ($operator === 'not in') {
                    $query->whereNotIn($condition[0], $value);
                }
            }
        }
        // 计算总和并返回
        return (float)$query->sum($field);
    }


    /**
     * 高精度加法（修正精度问题）
     *
     * @param mixed $key 主键值或条件值
     * @param string $incField 要增加的字段
     * @param string $inc 增加的值
     * @param string|null $keyField 条件字段名，默认为'id'
     * @param int $acc 精度（小数位数）
     *
     * @return bool
     * @throws Exception
     */
    public function bcInc(mixed $key, string $incField, string $inc, ?string $keyField = null, int $acc = 2): bool
    {
        // 获取模型实例
        $model = $this->getModel();
        $query = $keyField ? $model->where($keyField, $key) : $model->where('id', $key);
        return $query->update([$incField => DB::raw("COALESCE($incField, 0) + CAST($inc AS DECIMAL(10, $acc))")]) > 0;
    }

    /**
     * 高精度 减法
     *
     * @param             $key
     * @param string      $decField
     * @param string      $dec
     * @param string|null $keyField
     * @param int         $acc
     *
     * @return bool
     * @throws ReflectionException
     */
    public function bcDec($key, string $decField, string $dec, ?string $keyField = null, int $acc = 2): bool
    {
        return $this->bc($key, $decField, $dec, $keyField, 2, $acc);
    }

    /**
     * 高精度计算并保存
     *
     * @param             $key
     * @param string      $field
     * @param string      $value
     * @param string|null $keyField
     * @param int         $type
     * @param int         $acc
     *
     * @return bool
     * @throws ReflectionException
     */
    public function bc($key, string $field, string $value, ?string $keyField = null, int $type = 1, int $acc = 2): bool
    {
        // 获取记录
        $result = $keyField === null ? $this->get($key) : $this->getOne([$keyField => $key]);
        if (!$result) return false;
        $newValue = 0;
        if ($type === 1) {
            // 加法
            $newValue = bcadd($result->{$field}, $value, $acc);
        } elseif ($type === 2) {
            // 减法
            if ($result->{$field} < $value) return false; // 检查是否足够减去
            $newValue = bcsub($result->{$field}, $value, $acc);
        }
        // 更新字段
        $result->{$field} = $newValue;
        // 保存更新
        return $result->save();
    }

    /**
     * 减库存加销量
     *
     * @param array  $where
     * @param int    $num
     * @param string $stock
     * @param string $sales
     *
     * @return bool
     * @throws Exception
     */
    public function decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $query = $this->buildQuery($where);
        $product = $query->first();
        if ($product) {
            // 重新构建查询以执行更新操作
            $updateQuery = $this->buildQuery($where);

            $updateQuery->decrement($stock, $num);
            $updateQuery->increment($sales, $num);
            return true;
        }
        return false;
    }

    /**
     * 加库存减销量
     *
     * @param array  $where
     * @param int    $num
     * @param string $stock
     * @param string $sales
     *
     * @return bool
     * @throws Exception
     */
    public function incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $query = $this->buildQuery($where);
        $product = $query->first();
        if ($product) {
            // 重新构建查询以执行更新操作
            $updateQuery1 = $this->buildQuery($where);
            $updateQuery2 = $this->buildQuery($where);

            $updateQuery1->increment($stock, $num);
            $updateQuery2->decrement($sales, $num);
            return true;
        }
        return false;
    }

    /**
     * 获取条件数据中的某个值的最大值
     *
     * @param array  $where
     * @param string $field
     *
     * @return mixed
     * @throws Exception
     */
    public function getMax(array $where = [], string $field = ''): mixed
    {
        $query = $this->buildQuery($where);
        return $query->max($field);
    }

    /**
     * 获取条件数据中的某个值的最小值
     *
     * @param array  $where
     * @param string $field
     *
     * @return mixed
     * @throws Exception
     */
    public function getMin(array $where = [], string $field = ''): mixed
    {
        $query = $this->buildQuery($where);
        return $query->min($field);
    }

    private function studly(string $string): string
    {
        // 用下划线分隔单词
        $words = preg_split('/[\s_]+/', $string);
        // 将每个单词的首字母大写并连接
        $studlyCase = array_map('ucfirst', $words);
        // 返回连接后的结果
        return implode('', $studlyCase);
    }

    /**
     * 执行作用域移除操作
     *
     * @param            $query
     * @param array|null $scopes
     */
    protected function applyScopeRemoval($query, ?array $scopes): void
    {
        if (empty($scopes)) return;
        foreach ($scopes as $scope) {
            // 全局作用域移除
            if (is_string($scope) && class_exists($scope)) {
                $query->withoutGlobalScope($scope);
            } elseif (is_string($scope) && method_exists($query, 'withoutNamedScope')) {
                $query->withoutNamedScope($scope);
            } // 闭包动态移除
            elseif ($scope instanceof \Closure) {
                $scope($query);
            }
        }
    }

    /**
     * 是否in条件
     *
     * @param array $condition
     *
     * @return bool
     */
    protected function isInCondition(array $condition): bool
    {
        return count($condition) === 3
            && in_array(strtolower($condition[1] ?? ''), ['in', 'not in'], true);
    }
    /**
     * 检测表是否存在
     * @param $table
     * @return bool
     */
    public function tableExists($table): bool
    {
        return DB::schema()->hasTable($table);
    }
}
