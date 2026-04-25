<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: ThinkORMFactory.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\ORM\Factories;

use Framework\Core\App;
use Framework\ORM\Exception\Exception;
use Framework\Utils\Arr;
use think\Collection;
use think\db\Query;
use think\facade\Db;
use think\Model;
use think\Paginator;
use Throwable;

/**
 * ThinkPHP ORM工厂类
 * 
 * 提供ThinkPHP ORM的数据库操作封装，包括查询、插入、更新、删除等操作。
 * 支持搜索器、条件构建、分页、高精度计算等功能。
 */
class ThinkphpORMFactory
{
    /**
     * 模型类名
     * @var mixed
     */
    private mixed $modelClass;

    /**
     * 模型实例（懒加载）
     * @var Model|null
     */
    private ?Model $modelInstance = null;

    /**
     * 构造函数
     * 
     * 初始化ThinkPHP ORM工厂实例，接收模型类名或实例。
     * 
     * @param Model|string|null $model 模型类名或实例，用于初始化工厂
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
     * 获取模型实例（懒加载）
     * 
     * 延迟加载模型实例，首次调用时才创建实例。
     * 
     * @return Model 返回模型实例
     * @throws Exception 当模型类不存在或加载失败时抛出异常
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
            // 使用 App::make 解析模型
            $this->modelInstance = App::make($class);
            return $this->modelInstance;
        } catch (Throwable $e) {
            throw new Exception('模型加载失败: ' . $e->getMessage());
        }
    }

    /**
     * 构建查询条件
     * 
     * 根据条件数组构建查询构建器，支持普通条件和搜索器。
     * 
     * @param array      $where         查询条件数组
     * @param bool       $search        是否启用搜索器模式，默认false
     * @param array|null $withoutScopes 需要移除的作用域列表
     * @return Query 返回ThinkPHP查询构建器实例
     */
    private function buildQuery(array $where, bool $search = false, ?array $withoutScopes = null): Query
    {
        $query = $this->getModel()->db();
        if (!empty($withoutScopes)) {
            $this->applyScopeRemoval($query, $withoutScopes);
        }
        if ($search) {
            return $this->applySearchScopes($query, $where);
        }
        if (!empty($where)) {
            $this->applyConditions($query, $where);
        }
        return $query;
    }

    /**
     * 分离特殊查询条件
     * 
     * 将IN和NOT IN条件从普通条件中分离出来单独处理。
     * 
     * @param array $where 原始查询条件数组
     * @return array 返回[普通条件, 特殊条件]数组
     */
    private function splitWhere(array $where): array
    {
        $special = [];
        foreach ($where as $key => $condition) {
            if (is_array($condition) && count($condition) === 3) {
                $op = strtolower((string)($condition[1] ?? ''));
                if ($op === 'in' || $op === 'not in') {
                    $special[] = $condition;
                    unset($where[$key]);
                }
            }
        }
        return [$where, $special];
    }

    /**
     * 应用查询条件到构建器
     * 
     * 将条件数组应用到查询构建器，处理普通条件和IN/NOT IN特殊条件。
     * 
     * @param Query $query 查询构建器实例
     * @param array $where 查询条件数组
     * @return void
     */
    private function applyConditions(Query $query, array $where): void
    {
        [$normal, $special] = $this->splitWhere($where);
        if (!empty($normal)) {
            $query->where($normal);
        }
        foreach ($special as $condition) {
            if (empty($condition[2])) {
                continue;
            }
            $operator = strtolower((string)$condition[1]);
            $value = Arr::normalize($condition[2]);
            if ($operator === 'in') {
                $query->whereIn($condition[0], $value);
            } elseif ($operator === 'not in') {
                $query->whereNotIn($condition[0], $value);
            }
        }
    }

    /**
     * 应用字段选择
     * 
     * 设置查询返回的字段列表。
     * 
     * @param Query        $query 查询构建器实例
     * @param array|string $field 字段列表，支持数组或逗号分隔的字符串
     * @return void
     */
    private function applyFields(Query $query, array|string $field): void
    {
        $isWildcard = ($field === '*' || ($field === ['*']));
        if ($isWildcard) {
            return;
        }
        if (is_array($field)) {
            $query->field($field);
            return;
        }
        if (!empty($field)) {
            $query->field($field);
        }
    }

    /**
     * 获取符合条件的记录数
     * 
     * @param array $where  查询条件数组
     * @param bool  $search 是否启用搜索器模式，默认false
     * @return int 返回符合条件的记录总数
     */
    public function count(array $where = [], bool $search = false): int
    {
        return $this->buildQuery($where, $search)->count();
    }

    /**
     * 查询列表数据
     * 
     * 根据条件查询数据列表，支持分页、排序、关联预加载。
     * 
     * @param array       $where         查询条件数组
     * @param array|string $field        返回字段列表，默认为所有字段
     * @param int         $page          页码，大于0时启用分页
     * @param int         $limit         每页记录数
     * @param string      $order         排序条件
     * @param array       $with          关联预加载配置
     * @param bool        $search        是否启用搜索器模式
     * @param array|null  $withoutScopes 需要移除的作用域列表
     * @return Collection|null 返回数据集合，分页时返回当前页数据
     */
    public function selectList(array $where, array|string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): ?Collection
    {

        $query = $this->selectModel($where, $field, $page, $limit, $order, $with, $search, $withoutScopes);

        if ($query instanceof Paginator) {
            return $query->getCollection();
        }

        return $query->select();
    }

    /**
     * 获取查询构建器或分页结果
     * 
     * 构建查询并返回构建器实例或分页对象。
     * 
     * @param array       $where         查询条件数组
     * @param array|string $field        返回字段列表
     * @param int         $page          页码
     * @param int         $limit         每页记录数
     * @param string      $order         排序条件
     * @param array       $with          关联预加载配置
     * @param bool        $search        是否启用搜索器模式
     * @param array|null  $withoutScopes 需要移除的作用域列表
     * @return Query|Paginator 返回查询构建器或分页对象
     */
    public function selectModel(array $where, array|string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): Query|Paginator
    {
        $query = $this->buildQuery($where, $search, $withoutScopes);

        $this->applyFields($query, $field);

        // 关联预加载
        if (!empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderRaw($order);
        }

        if ($page > 0 && $limit > 0) {
            return $query->paginate([
                'list_rows' => $limit,
                'page'      => $page,
            ]);
        }

        return $query;
    }

    /**
     * 获取符合条件的记录数（别名方法）
     * 
     * @param array $where 查询条件数组
     * @return int 返回记录总数
     */
    public function getCount(array $where): int
    {
        return $this->buildQuery($where)->count();
    }

    /**
     * 获取去重后的记录数
     * 
     * 计算指定字段去重后的记录数量。
     * 
     * @param array  $where  查询条件数组
     * @param string $field  需要去重的字段名
     * @param bool   $search 是否启用搜索器模式
     * @return int 返回去重后的记录数
     */
    public function getDistinctCount(array $where, string $field, bool $search = true): int
    {
        // ThinkPHP: count('DISTINCT field')
        return (int) $this->buildQuery($where, $search)->count('DISTINCT ' . $field);
    }

    /**
     * 获取模型主键名
     * 
     * @return string 返回主键字段名
     */
    public function getPk(): string
    {
        return $this->getModel()->getPk();
    }

    /**
     * 获取数据表名
     * 
     * @return string 返回表名（不含前缀）
     */
    public function getTableName(): string
    {
        return $this->getModel()->getName();
    }

    /**
     * 根据ID或条件获取单条记录
     * 
     * 支持通过主键ID或条件数组查询单条记录。
     * 
     * @param int|string|array     $id           主键值或条件数组
     * @param array|string|null    $field        返回字段列表
     * @param array|null           $with         关联预加载配置
     * @param string               $order        排序条件
     * @param array|null           $withoutScopes 需要移除的作用域列表
     * @return Model|null 返回模型实例或null
     */
    public function get($id, array|string|null $field = null, ?array $with = [], string $order = '', ?array $withoutScopes = null): ?Model
    {
        $where = is_array($id) ? $id : [$this->getPk() => $id];
        $query = $this->buildQuery($where, false, $withoutScopes);

        if (!empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderRaw($order);
        }

        $this->applyFields($query, $field ?? '*');
        return $query->find();
    }

    /**
     * 检查记录是否存在
     * 
     * 判断符合条件的记录是否存在。
     * 
     * @param mixed  $map   主键值或条件数组
     * @param string $field 字段名（当$map为单值时使用）
     * @return bool 存在返回true，否则返回false
     */
    public function be($map, string $field = ''): bool
    {
        if (!is_array($map) && empty($field)) {
            $field = $this->getPk();
        }
        $map = !is_array($map) ? [$field => $map] : $map;

        // ThinkPHP 使用 count() > 0 或 value() 判断
        return $this->buildQuery($map)->count() > 0;
    }

    /**
     * 根据条件获取单条记录
     * 
     * 通过条件数组查询第一条匹配的记录。
     * 
     * @param array              $where 查询条件数组
     * @param array|string|null  $field 返回字段列表
     * @param array              $with  关联预加载配置
     * @return Model|null 返回模型实例或null
     */
    public function getOne(array $where, array|string|null $field = '*', array $with = []): ?Model
    {
        $query = $this->buildQuery($where);

        if (!empty($with)) {
            $query->with($with);
        }

        $this->applyFields($query, $field ?? '*');
        return $query->find();
    }

    /**
     * 获取指定字段的值
     * 
     * 根据条件查询单个字段的值。
     * 
     * @param mixed       $where 主键值或条件数组
     * @param string|null $field 要获取的字段名，默认为主键
     * @return mixed 返回字段值
     */
    public function value($where, ?string $field = null): mixed
    {
        $pk = $this->getPk();
        $where = $this->setWhere($where);
        
        return $this->buildQuery($where)->value($field ?? $pk);
    }

    /**
     * 获取字段值数组
     * 
     * 获取指定字段的所有值，可指定键名。
     * 
     * @param array  $where 查询条件数组
     * @param string $field 要获取的字段名
     * @param string $key   作为数组键的字段名
     * @return array 返回字段值数组
     */
    public function getColumn(array $where, string $field, string $key = ''): array
    {
        $query = $this->buildQuery($where);
        return $query->column($field, $key ?: null);
    }

    /**
     * 删除记录
     * 
     * 根据主键或条件删除记录。
     * 
     * @param array|int|string $id  主键值、主键数组或条件数组
     * @param string|null      $key 条件字段名
     * @return int 返回影响的行数
     * @throws Exception 删除失败时抛出异常
     */
    public function delete(array|int|string $id, ?string $key = null): int
    {
        try {
            // ThinkPHP destroy 支持 主键、数组主键、闭包条件
            if (is_array($id) && array_is_list($id) && is_null($key)) {
                return (int) $this->getModel()->destroy($id);
            }

            $query = $this->getModel()->db();
            if (is_array($id)) {
                $query->where($id);
            } else {
                $query->where($key ?: $this->getPk(), $id);
            }

            // delete 返回影响行数
            return (int) $query->delete();
        } catch (Throwable $e) {
            throw new Exception("删除失败:" . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * 销毁记录
     * 
     * 直接通过主键销毁记录，支持强制删除。
     * 
     * @param mixed $id    主键值或主键数组
     * @param bool  $force 是否强制删除（用于软删除模型）
     * @return bool 删除成功返回true
     */
    public function destroy(mixed $id, bool $force = false): bool
    {
        return $this->getModel()->destroy($id, $force);
    }

    /**
     * 更新记录
     * 
     * 根据主键或条件更新记录。
     * 
     * @param string|int|array $id   主键值或条件数组
     * @param array            $data 要更新的数据
     * @param string|null      $key  条件字段名
     * @return mixed 返回影响的行数
     */
    public function update(string|int|array $id, array $data, ?string $key = null): mixed
    {
        $where = is_array($id) ? $id : [is_null($key) ? $this->getPk() : $key => $id];
        
        // ThinkPHP update 返回影响行数 (int) 或 Model
        // 使用 Query 对象的 update 方法返回 int
        return $this->buildQuery($where)->update($data);
    }

    /**
     * 设置查询条件
     * 
     * 将单值转换为条件数组格式。
     * 
     * @param mixed       $where 条件值或条件数组
     * @param string|null $key   条件字段名
     * @return array 返回条件数组
     */
    protected function setWhere($where, ?string $key = null): array
    {
        if (!is_array($where)) {
            $where = [is_null($key) ? $this->getPk() : $key => $where];
        }
        return $where;
    }

    /**
     * 批量更新记录
     * 
     * 根据主键数组批量更新多条记录。
     * 
     * @param array       $ids  主键值数组
     * @param array       $data 要更新的数据
     * @param string|null $key  条件字段名，默认为主键
     * @return bool 更新成功返回true
     */
    public function batchUpdate(array $ids, array $data, ?string $key = null): bool
    {
        return (bool) $this->getModel()
            ->whereIn(is_null($key) ? $this->getPk() : $key, $ids)
            ->update($data);
    }

    /**
     * 保存单条记录
     * 
     * 创建新记录并返回模型实例。
     * 
     * @param array $data 要保存的数据
     * @return Model|null 返回创建的模型实例
     */
    public function save(array $data): ?Model
    {
        return $this->getModel()->create($data);
    }

    /**
     * 批量插入记录
     * 
     * 批量创建多条记录。
     * 
     * @param array $data 要插入的数据数组
     * @return bool 插入成功返回true
     */
    public function saveAll(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        // ThinkPHP saveAll 返回 Collection
        $result = $this->getModel()->saveAll($data);
        return !$result->isEmpty();
    }

    /**
     * 获取指定条件下的字段值
     * 
     * 根据条件查询特定字段的值。
     * 
     * @param mixed       $value    条件值
     * @param string      $field    要获取的字段名
     * @param string|null $valueKey 条件字段名
     * @param array|null  $where    额外的查询条件
     * @return mixed 返回字段值
     */
    public function getFieldValue($value, string $field, ?string $valueKey = null, ?array $where = []): mixed
    {
        if ($valueKey) {
            $where[$valueKey] = $value;
        } else {
            $where[$this->getPk()] = $value;
        }

        return $this->buildQuery($where)->value($field);
    }

    /**
     * 应用搜索器作用域
     * 
     * 使用ThinkPHP的withSearch功能应用搜索器。
     * 
     * @param Query $query 查询构建器实例
     * @param array $where 查询条件数组
     * @return Query 返回处理后的查询构建器
     */
    private function applySearchScopes(Query $query, array $where): Query
    {
        [$searchFields, $searchData, $otherWhere] = $this->getSearchData($where);
        if (!empty($searchFields)) {
            $query->withSearch($searchFields, $searchData);
        }
        $filteredWhere = $this->filterWhere($otherWhere);
        if (!empty($filteredWhere)) {
            $this->applyConditions($query, $filteredWhere);
        }
        return $query;
    }

    /**
     * 获取搜索器数据
     * 
     * 分离搜索器条件和普通查询条件。
     * 适配ThinkPHP的searchFieldNameAttr搜索器命名规则。
     * 
     * @param array $where 原始查询条件数组
     * @return array 返回[搜索字段, 搜索数据, 其他条件]
     */
    private function getSearchData(array $where): array
    {
        $searchFields = [];
        $searchData = [];
        $otherWhere = [];
        $model = $this->getModel();
        
        foreach ($where as $key => $value) {
            // ThinkPHP 搜索器方法名规则：searchFieldNameAttr
            $method = 'search' . self::studly($key) . 'Attr';
            
            if (method_exists($model, $method)) {
                $searchFields[] = $key;
                $searchData[$key] = $value;
            } else {
                // 保留特殊字段逻辑
                if (!in_array($key, ['timeKey', 'store_stock', 'integral_time'])) {
                    if (!is_array($value)) {
                        $otherWhere[$key] = $value;
                    } else {
                        // 复杂条件保留，key 可能为数字索引
                        $otherWhere[] = $value;
                    }
                }
            }
        }
        return [$searchFields, $searchData, $otherWhere];
    }

    /**
     * 根据搜索器获取查询结果（内部方法）
     * 
     * @param array $where  查询条件数组
     * @param bool  $search 是否启用搜索器
     * @return Query 返回查询构建器
     */
    protected function withSearchSelect(array $where, bool $search): Query
    {
        return $this->buildQuery($where, $search);
    }

    /**
     * 过滤无效字段
     * 
     * 移除数据表中不存在的字段条件。
     * 
     * @param array $where 查询条件数组
     * @return array 返回过滤后的条件数组
     */
    protected function filterWhere(array $where = []): array
    {
        $fields = $this->getModel()->getTableFields();

        if (empty($fields)) {
            return $where; 
        }

        foreach ($where as $key => $item) {
            if (is_int($key)) {
                if (is_array($item) && isset($item[0]) && !in_array($item[0], $fields)) {
                    unset($where[$key]);
                }
                continue;
            }
            
            if (!in_array($key, $fields)) {
                unset($where[$key]);
            }
        }
        return $where;
    }

    /**
     * 执行搜索查询
     * 
     * 根据条件执行搜索，支持搜索器模式。
     * 
     * @param array $where  查询条件数组
     * @param bool  $search 是否启用搜索器模式
     * @return Model|Query 返回模型实例或查询构建器
     */
    public function search(array $where = [], bool $search = true): Model|Query
    {
        if ($where) {
            return $this->buildQuery($where, $search);
        }
        return $this->getModel();
    }

    /**
     * 计算字段求和
     * 
     * 对指定字段进行求和计算。
     * 
     * @param array  $where  查询条件数组
     * @param string $field  要求和的字段名
     * @param bool   $search 是否启用搜索器模式
     * @return float 返回求和结果
     */
    public function sum(array $where, string $field, bool $search = false): float
    {
        return (float) $this->buildQuery($where, $search)->sum($field);
    }

    /**
     * 高精度加法运算
     * 
     * 对指定字段进行高精度加法运算，避免浮点数精度问题。
     * 
     * @param mixed       $key      主键值或条件值
     * @param string      $incField 要增加的字段名
     * @param string      $inc      增加的值
     * @param string|null $keyField 条件字段名，默认为主键
     * @param int         $acc      精度（小数位数），默认为2
     * @return bool 操作成功返回true
     */
    public function bcInc(mixed $key, string $incField, string $inc, ?string $keyField = null, int $acc = 2): bool
    {
        $pk = $keyField ?: $this->getPk();
        $query = $this->getModel()->where($pk, $key);
        
        // ThinkPHP Db::raw
        return $query->update([
            $incField => Db::raw("COALESCE($incField, 0) + CAST($inc AS DECIMAL(20, $acc))")
        ]) > 0;
    }

    /**
     * 高精度减法运算
     * 
     * 对指定字段进行高精度减法运算。
     * 
     * @param mixed       $key      主键值或条件值
     * @param string      $decField 要减少的字段名
     * @param string      $dec      减少的值
     * @param string|null $keyField 条件字段名
     * @param int         $acc      精度（小数位数）
     * @return bool 操作成功返回true，值不足时返回false
     */
    public function bcDec($key, string $decField, string $dec, ?string $keyField = null, int $acc = 2): bool
    {
        return $this->bc($key, $decField, $dec, $keyField, 2, $acc);
    }

    /**
     * 高精度计算并保存
     * 
     * 在应用层进行高精度计算后保存结果。
     * 
     * @param mixed       $key      主键值或条件值
     * @param string      $field    要计算的字段名
     * @param string      $value    计算值
     * @param string|null $keyField 条件字段名
     * @param int         $type     计算类型：1=加法，2=减法
     * @param int         $acc      精度（小数位数）
     * @return bool 操作成功返回true
     */
    public function bc($key, string $field, string $value, ?string $keyField = null, int $type = 1, int $acc = 2): bool
    {
        $result = $keyField === null ? $this->get($key) : $this->getOne([$keyField => $key]);
        if (!$result) return false;
        
        $current = (string) ($result->{$field} ?? '0');
        $newValue = '0';

        if ($type === 1) {
            $newValue = bcadd($current, $value, $acc);
        } elseif ($type === 2) {
            if (bccomp($current, $value, $acc) < 0) return false;
            $newValue = bcsub($current, $value, $acc);
        }
        
        $result->{$field} = $newValue;
        return $result->save();
    }

    /**
     * 减库存加销量（原子更新）
     * 
     * 在一次操作中减少库存并增加销量，保证数据一致性。
     * 
     * @param array  $where 查询条件数组
     * @param int    $num   操作数量
     * @param string $stock 库存字段名
     * @param string $sales 销量字段名
     * @return bool 操作成功返回true
     */
    public function decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $query = $this->buildQuery($where);

        // ThinkPHP 链式操作: where(...)->dec(字段, 值)->inc(字段, 值)->update()
        // 必须加上 where stock >= num 保证安全
        $query->where($stock, '>=', $num);

        $result = $query->dec($stock, $num)
              ->inc($sales, $num)
              ->update();
        
        return $result > 0;
    }

    /**
     * 加库存减销量（原子更新）
     * 
     * 在一次操作中增加库存并减少销量，用于撤销操作。
     * 
     * @param array  $where 查询条件数组
     * @param int    $num   操作数量
     * @param string $stock 库存字段名
     * @param string $sales 销量字段名
     * @return bool 操作成功返回true
     */
    public function incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $query = $this->buildQuery($where);

        // 可选：检查销量是否足够减
        $query->where($sales, '>=', $num);

        $result = $query->inc($stock, $num)
              ->dec($sales, $num)
              ->update();

        return $result > 0;
    }

    /**
     * 获取字段最大值
     * 
     * @param array  $where 查询条件数组
     * @param string $field 字段名
     * @return mixed 返回最大值
     */
    public function getMax(array $where = [], string $field = ''): mixed
    {
        return $this->buildQuery($where)->max($field);
    }

    /**
     * 获取字段最小值
     * 
     * @param array  $where 查询条件数组
     * @param string $field 字段名
     * @return mixed 返回最小值
     */
    public function getMin(array $where = [], string $field = ''): mixed
    {
        return $this->buildQuery($where)->min($field);
    }

    /**
     * 将字符串转换为驼峰命名
     * 
     * 将下划线或连字符分隔的字符串转换为驼峰命名。
     * 
     * @param string $string 原始字符串
     * @return string 驼峰命名字符串
     */
    private static function studly(string $string): string
    {
        $string = str_replace(['-', '_'], ' ', $string);
        return str_replace(' ', '', ucwords($string));
    }

    /**
     * 应用作用域移除
     * 
     * 移除指定的全局作用域，如软删除作用域。
     * 
     * @param Query       $query  查询构建器实例
     * @param array|null  $scopes 需要移除的作用域列表
     * @return void
     */
    protected function applyScopeRemoval(Query $query, ?array $scopes): void
    {
        if (empty($scopes)) return;
        // ThinkPHP 的 Global Scope 移除逻辑比较特殊
        // 如果是软删除，通常使用 removeOption('soft_delete') 或 withTrashed()
        // 这里做一个简单的模拟兼容
        foreach ($scopes as $scope) {
            if ($scope === 'soft_delete' || str_contains($scope, 'SoftDelete')) {
                // 假设是想查询包含已删除的数据
                if (method_exists($query, 'withTrashed')) {
                    $query->withTrashed();
                }
            }
        }
    }

    /**
     * 检查数据表是否存在
     * 
     * @param string $table 表名
     * @return bool 存在返回true，否则返回false
     */
    public function tableExists($table): bool
    {
        try {
            $res = Db::query("SHOW TABLES LIKE ?", [$table]);
            return !empty($res);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
