<?php
declare(strict_types=1);

namespace Framework\Repository\Builders;

use Framework\Repository\Exceptions\DatabaseException;

/**
 * 查询条件构建器
 * 负责处理所有查询条件的构建逻辑，解耦BaseRepository
 */
class QueryConditionBuilder
{
    protected mixed $query;
    protected bool $isEloquent;
    protected bool $isModelClass;

    public function __construct(mixed $query, bool $isEloquent, bool $isModelClass)
    {
        $this->query = $query;
        $this->isEloquent = $isEloquent;
        $this->isModelClass = $isModelClass;
    }

    /**
     * 构建完整的查询条件
     */
    public function build(array $criteria, array $orderBy = []): mixed
    {
        try {
            // 预处理查询对象
            $this->prepareQuery();
            
            // 依次处理各类条件
            $this->handleSelect($criteria);
            $this->handleDistinct($criteria);
            $this->handleLock($criteria);
            $this->handleJoins($criteria);
            $this->handleNullConditions($criteria);
            $this->handleInConditions($criteria);
            $this->handleGroupBy($criteria);
            $this->handleHaving($criteria);
            $this->handleOrGroup($criteria);
            $this->handleWhereConditions($criteria);
            $this->handleOrderBy($orderBy);

            return $this->query;
        } catch (\Exception $e) {
            throw DatabaseException::queryFailed("构建查询条件失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 预处理查询对象，确保是正确的Builder实例
     */
    protected function prepareQuery(): void
    {
        if (!$this->isModelClass) {
            return;
        }

        if ($this->isEloquent) {
            if ($this->query instanceof \Illuminate\Database\Eloquent\Model) {
                $this->query = $this->query->newQuery();
            }
        } else {
            if ($this->query instanceof \think\Model) {
                $this->query = $this->query->db();
            }
        }
    }

    /**
     * 处理SELECT字段
     */
    protected function handleSelect(array &$criteria): void
    {
        if (!empty($criteria['select'])) {
            $this->query->select($criteria['select']);
            unset($criteria['select']);
        }
    }

    /**
     * 处理DISTINCT去重
     */
    protected function handleDistinct(array &$criteria): void
    {
        if (!empty($criteria['distinct'])) {
            $this->query->distinct();
            unset($criteria['distinct']);
        }
    }

    /**
     * 处理悲观锁
     */
    protected function handleLock(array &$criteria): void
    {
        if (!empty($criteria['lock'])) {
            if ($this->isEloquent) {
                $this->query->lockForUpdate();
            } else {
                $this->query->lock(true);
            }
            unset($criteria['lock']);
        }
    }

    /**
     * 处理JOIN操作
     */
    protected function handleJoins(array &$criteria): void
    {
        foreach (['join', 'leftJoin', 'rightJoin'] as $joinType) {
            if (empty($criteria[$joinType]) || !is_array($criteria[$joinType])) {
                continue;
            }

            foreach ($criteria[$joinType] as $join) {
                $table = $join[0] ?? null;
                $field1 = $join[1] ?? null;
                $operator = $join[2] ?? '=';
                $field2 = $join[3] ?? null;

                if (!$table || !$field1) {
                    continue;
                }

                // 自动补全等号
                if ($field2 === null && isset($join[2])) {
                    $field2 = $join[2];
                    $operator = '=';
                }

                if (!$this->isEloquent) {
                    $this->query->$joinType($table, "{$field1} {$operator} {$field2}");
                } else {
                    $this->query->$joinType($table, $field1, $operator, $field2);
                }
            }

            unset($criteria[$joinType]);
        }
    }

    /**
     * 处理NULL/NOT NULL条件
     */
    protected function handleNullConditions(array &$criteria): void
    {
        if (!empty($criteria['whereNull'])) {
            foreach ((array)$criteria['whereNull'] as $field) {
                $this->query->whereNull($field);
            }
            unset($criteria['whereNull']);
        }

        if (!empty($criteria['whereNotNull'])) {
            foreach ((array)$criteria['whereNotNull'] as $field) {
                $this->query->whereNotNull($field);
            }
            unset($criteria['whereNotNull']);
        }
    }

    /**
     * 处理IN/NOT IN条件
     */
    protected function handleInConditions(array &$criteria): void
    {
        if (!empty($criteria['whereIn'])) {
            foreach ($criteria['whereIn'] as $field => $values) {
                $this->query->whereIn($field, $values);
            }
            unset($criteria['whereIn']);
        }

        if (!empty($criteria['whereNotIn'])) {
            foreach ($criteria['whereNotIn'] as $field => $values) {
                $this->query->whereNotIn($field, $values);
            }
            unset($criteria['whereNotIn']);
        }
    }

    /**
     * 处理GROUP BY
     */
    protected function handleGroupBy(array &$criteria): void
    {
        if (!empty($criteria['groupBy'])) {
            $groupBy = (array) $criteria['groupBy'];
            $this->query->groupBy(...$groupBy);
            unset($criteria['groupBy']);
        }
    }

    /**
     * 处理HAVING条件
     */
    protected function handleHaving(array &$criteria): void
    {
        if (!empty($criteria['having']) && is_array($criteria['having'])) {
            foreach ($criteria['having'] as $cond) {
                if (count($cond) === 3) {
                    $this->query->having($cond[0], $cond[1], $cond[2]);
                } elseif (count($cond) === 2) {
                    $this->query->having($cond[0], '=', $cond[1]);
                }
            }
            unset($criteria['having']);
        }

        if (!empty($criteria['havingRaw'])) {
            $this->query->havingRaw($criteria['havingRaw']);
            unset($criteria['havingRaw']);
        }
    }

    /**
     * 处理OR分组条件
     */
    protected function handleOrGroup(array &$criteria): void
    {
        if (empty($criteria['or_group']) || !is_array($criteria['or_group'])) {
            return;
        }

        $orGroup = $criteria['or_group'];
        $this->query->where(function ($subQuery) use ($orGroup) {
            $isFirst = true;
            foreach ($orGroup as $field => $value) {
                $op = '=';
                $val = $value;

                if (is_array($value)) {
                    $op = $value[0] ?? '=';
                    $val = $value[1] ?? $value[0];
                }

                if ($this->isEloquent) {
                    $isFirst 
                        ? $subQuery->where($field, $op, $val) 
                        : $subQuery->orWhere($field, $op, $val);
                } else {
                    $isFirst 
                        ? $subQuery->where($field, $op, $val) 
                        : $subQuery->whereOr($field, $op, $val);
                }

                $isFirst = false;
            }
        });

        unset($criteria['or_group']);
    }

    /**
     * 处理基础WHERE条件
     */
    protected function handleWhereConditions(array &$criteria): void
    {
        foreach ($criteria as $field => $value) {
            // 忽略分页相关参数
            if (in_array($field, ['page', 'limit', 'per_page'])) {
                continue;
            }

            // 处理OR条件
            if ($field === 'or' && is_array($value)) {
                $callback = function ($q) use ($value) {
                    $builder = new self($q, $this->isEloquent, $this->isModelClass);
                    $builder->build($value);
                };

                if ($this->isEloquent) {
                    $this->query->orWhere($callback);
                } else {
                    $this->query->whereOr($callback);
                }
                continue;
            }

            // 处理AND条件
            if ($field === 'and' && is_array($value)) {
                $callback = function ($q) use ($value) {
                    $builder = new self($q, $this->isEloquent, $this->isModelClass);
                    $builder->build($value);
                };

                if ($this->isEloquent) {
                    $this->query->where($callback);
                } else {
                    $this->query->where($callback);
                }
                continue;
            }

            // 处理自定义分组
            if ($field === 'group' && is_callable($value)) {
                $this->query->where($value);
                continue;
            }

            // 处理原生SQL
            if ($field === 'raw') {
                $this->query->whereRaw($value);
                continue;
            }

            // 处理普通条件
            if (is_array($value)) {
                $this->handleArrayWhereCondition($field, $value);
            } else {
                $this->query->where($field, $value);
            }
        }
    }

    /**
     * 处理数组形式的WHERE条件
     */
    protected function handleArrayWhereCondition(string $field, array $value): void
    {
        [$op, $val] = $value;
        $op = strtolower($op);

        switch ($op) {
            case 'in':
                $this->query->whereIn($field, $val);
                break;
            case 'not in':
            case 'not_in':
                $this->query->whereNotIn($field, $val);
                break;
            case 'between':
                $this->query->whereBetween($field, $val);
                break;
            case 'not between':
                $this->query->whereNotBetween($field, $val);
                break;
            case 'like':
                $this->query->where($field, 'like', $val);
                break;
            case 'not like':
                $this->query->where($field, 'not like', $val);
                break;
            case 'null':
                $this->query->whereNull($field);
                break;
            case 'not null':
                $this->query->whereNotNull($field);
                break;
            case 'exists':
                if ($val instanceof \Closure) {
                    $this->query->whereExists($val);
                }
                break;
            default:
                $this->query->where($field, $op, $val);
        }
    }

    /**
     * 处理排序
     */
    protected function handleOrderBy(array $orderBy): void
    {
        foreach ($orderBy as $field => $direction) {
            if ($this->isEloquent) {
                $this->query->orderBy($field, $direction);
            } else {
                $this->query->order($field, $direction);
            }
        }
    }

    /**
     * 获取构建后的查询对象
     */
    public function getQuery(): mixed
    {
        return $this->query;
    }
}