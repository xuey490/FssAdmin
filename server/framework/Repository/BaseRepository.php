<?php

declare(strict_types=1);

namespace Framework\Repository;

use Framework\Database\DatabaseFactory;
use Framework\Repository\Builders\QueryConditionBuilder;
use Framework\Repository\Exceptions\DatabaseException;
use Framework\Repository\Strategies\EloquentStrategy;
use Framework\Repository\Strategies\OrmStrategyInterface;
use Framework\Repository\Strategies\ThinkStrategy;
use Framework\DI\Injectable;
use Psr\SimpleCache\CacheInterface;
use Framework\Tenant\TenantContext;	//启用租户隔离#

/**
 * Class BaseRepository
 * 核心数据库操作基类（优化版）
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected string $modelClass;
    protected OrmStrategyInterface $ormStrategy;
    protected ?CacheInterface $cache = null; // 改为可空类型并初始化为null
    protected int $cacheTtl = 3600; // 默认缓存过期时间（秒）

    // 引入注入能力
    use Injectable;

    public function __construct(protected DatabaseFactory $factory)
    {
        $this->inject();
		
		$this->cache = app('cache');

        if (empty($this->modelClass)) {
            throw new DatabaseException('Repository必须定义$modelClass属性');
        }

        // 初始化ORM策略
        $this->initializeOrmStrategy();

        // 初始化缓存（如果有注入）
        $this->initializeCache();

        $this->initialize();
		
    }

    /**
     * 初始化ORM策略
     */
    protected function initializeOrmStrategy(): void
    {
		#dump($this->factory->isEloquent());
        $this->ormStrategy = $this->factory->isEloquent() 
            ? new EloquentStrategy() 
            : new ThinkStrategy();
    }

    /**
     * 初始化缓存
     */
    protected function initializeCache(): void
    {
        // 子类可重写此方法注入缓存实例
    }

    /**
     * 子类可根据需要覆盖生命周期方法
     */
    protected function initialize(): void
    {
    }

    /**
     * 判断是否配置了有效的模型类
     */
    protected function isModelClass(): bool
    {
        return class_exists($this->modelClass);
    }

    /**
     * 获取查询构建器
     */
    protected function newQuery(?string $modelClass = null): mixed
    {
        try {
            return $this->ormStrategy->getQueryBuilder($modelClass ?? $this->modelClass);
        } catch (\Exception $e) {
            throw DatabaseException::queryFailed("获取查询构建器失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 语法糖：$repo() 获取底层 Builder
     */
    public function __invoke(?string $modelClass = null): mixed
    {
        return $this->newQuery($modelClass);
    }

    /**
     * 统一处理Eager Loading
     */
    protected function applyWith(mixed $query, array $with = []): mixed
    {
        if (empty($with) || !$this->isModelClass()) {
            return $query;
        }

        if (method_exists($query, 'with')) {
            return $query->with($with);
        }

        return $query;
    }

    // --- 查询方法（统一异常处理 + 缓存支持）---

    /**
     * 根据ID查找记录
     * @throws DatabaseException
     */	
	// 修改查询方法中的缓存逻辑（增加缓存开关判断）
	public function findById(int|string $id, array $with = []): mixed
	{
		try {
			// 缓存开关：不启用则直接查询数据库
			$cacheEnabled = $this->isCacheEnabled(__FUNCTION__, func_get_args());
			$cacheKey = $cacheEnabled ? $this->getCacheKey(__FUNCTION__, $id, $with) : '';

			// 尝试从缓存获取
			if ($cacheEnabled && $this->cache !== null && $this->cache->has($cacheKey)) {
				return $this->cache->get($cacheKey);
			}

			// 数据库查询逻辑（原有逻辑不变）
			$result = null;
			if ($this->isModelClass() && $this->factory->isEloquent()) {
				/** @var \Illuminate\Database\Eloquent\Model $model */
				$model = new $this->modelClass;
				$result = $model->with($with)->find($id);
			} else {
				$query = $this->newQuery();
				$query = $this->applyWith($query, $with);
				if ($this->isModelClass()) {
					$result = $query->find($id);
				} else {
					$result = $query->where('id', $id)->first() ?? null;
				}
			}

			// 写入缓存（仅当缓存启用且查询结果非空时）
			if ($cacheEnabled && $this->cache !== null && $result !== null) {
				$this->cache->set($cacheKey, $result, $this->cacheTtl);
			}

			return $result;
		} catch (\Exception $e) {
			throw DatabaseException::queryFailed("根据ID[{$id}]查询失败: {$e->getMessage()}", $e);
		}
	}

    /**
     * 根据条件查找单条记录
     * @throws DatabaseException
     */
    public function findOneBy(array $criteria, array $with = []): mixed
    {
        try {
            $cacheKey = $this->getCacheKey('findOneBy', $criteria, $with);
            if ($this->cache && $this->cache->has($cacheKey)) {
                return $this->cache->get($cacheKey);
            }

            $query = $this->buildQuery($this->newQuery(), $criteria);
            $query = $this->applyWith($query, $with);

            $result = $this->factory->isEloquent() ? $query->first() : ($query->find() ?: null);

            if ($this->cache && $result) {
                $this->cache->set($cacheKey, $result, $this->cacheTtl);
            }

            return $result;
        } catch (\Exception $e) {
            throw DatabaseException::queryFailed("根据条件查询单条记录失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 根据条件查找多条记录
     * @throws DatabaseException
     */
    public function findAll(array $criteria = [], array $orderBy = [], ?int $limit = null, array $with = []): mixed
    {
        try {

			// 缓存开关：不启用则直接查询数据库
			$cacheEnabled = $this->isCacheEnabled(__FUNCTION__, func_get_args());
			$cacheKey = $this->getCacheKey('findAll', $criteria, $orderBy, $limit, $with);

			// 尝试从缓存获取
			if ($cacheEnabled && $this->cache !== null && $this->cache->has($cacheKey)) {
				return $this->cache->get($cacheKey);
			}
			
            $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);
            $query = $this->applyWith($query, $with);

            if ($limit) {
                $query->limit($limit);
            }

            $result = $this->factory->isEloquent() ? $query->get() : $query->select();

            if ($this->cache && $result) {
                $this->cache->set($cacheKey, $result, $this->cacheTtl);
            }

            return $result;
        } catch (\Exception $e) {
            throw DatabaseException::queryFailed("查询多条记录失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 分页查询
     * @throws DatabaseException
     */
    public function paginate(array $criteria = [], int $perPage = 15, array $orderBy = [], array $with = []): mixed
    {
        try {
            $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);
            $query = $this->applyWith($query, $with);
            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw DatabaseException::queryFailed("分页查询失败: {$e->getMessage()}", $e);
        }
    }

    // --- 写入方法（统一异常处理 + 清除缓存）---

    /**
     * 创建记录
     * @throws DatabaseException
     */
    public function create(array $data): mixed
    {
        try {
            $this->clearCache(); // 创建成功后清除缓存

            if ($this->isModelClass()) {
                $result = forward_static_call([$this->modelClass, 'create'], $data);
            } else {
                if ($this->factory->isEloquent()) {
                    $id = $this->newQuery()->insertGetId($data);
                } else {
                    $id = $this->newQuery()->insert($data, true);
                }
                $result = $this->findById($id);
            }

            if (!$result) {
                throw new DatabaseException("创建记录后无法获取新记录");
            }

            return $result;
        } catch (\Exception $e) {
            throw DatabaseException::createFailed($e->getMessage(), $e);
        }
    }

    /**
     * 根据ID更新记录
     * @throws DatabaseException
     */
    public function update(array $criteria, array $data): bool
    {
        try {
            // 统一参数格式：支持ID或条件数组
            if (isset($criteria['id']) || is_string($criteria) || is_int($criteria)) {
                $id = is_array($criteria) ? $criteria['id'] : $criteria;
                $item = $this->findById($id);
                $criteria = ['id' => $id];
            } else {
                $item = $this->findOneBy($criteria);
            }

            if (!$item) {
                throw new DatabaseException("未找到符合条件的记录");
            }

            $result = false;

            if (is_object($item) && method_exists($item, 'save')) {
                if ($this->factory->isEloquent()) {
                    $item->fill($data);
                    $result = (bool) $item->save();
                } else {
                    $res = $item->save($data);
                    $result = $res !== false;
                }
            } else {
                $query = $this->buildQuery($this->newQuery(), $criteria);
                $result = $query->update($data) > 0;
            }

            if ($result) {
                $this->clearCache(); // 更新成功后清除缓存
            }

            return $result;
        } catch (\Exception $e) {
            throw DatabaseException::updateFailed($e->getMessage(), $e);
        }
    }

    /**
     * 按条件批量更新（兼容旧方法，统一参数格式）
     * @throws DatabaseException
     */
    public function updateBy(array $criteria, array $data): int
    {
        try {
            $query = $this->buildQuery($this->newQuery(), $criteria);
            $affected = (int) $query->update($data);
            
            if ($affected > 0) {
                $this->clearCache();
            }
            
            return $affected;
        } catch (\Exception $e) {
            throw DatabaseException::updateFailed("批量更新失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 删除记录
     * @throws DatabaseException
     */
    public function delete(array $criteria): bool
    {
        try {
            // 统一参数格式：支持ID或条件数组
            if (isset($criteria['id']) || is_string($criteria) || is_int($criteria)) {
                $id = is_array($criteria) ? $criteria['id'] : $criteria;
                $criteria = ['id' => $id];
            }

            $result = false;

            if ($this->isModelClass()) {
                $id = $criteria['id'] ?? null;
                $result = (bool) forward_static_call([$this->modelClass, 'destroy'], $id ?? $criteria);
            } else {
                $query = $this->buildQuery($this->newQuery(), $criteria);
                $result = (bool) $query->delete();
            }

            if ($result) {
                $this->clearCache(); // 删除成功后清除缓存
            }

            return $result;
        } catch (\Exception $e) {
            throw DatabaseException::deleteFailed($e->getMessage(), $e);
        }
    }

    /**
     * 按条件批量删除（兼容旧方法）
     * @throws DatabaseException
     */
    public function deleteBy(array $criteria): int
    {
        try {
            $query = $this->buildQuery($this->newQuery(), $criteria);
            $affected = (int) $query->delete();
            
            if ($affected > 0) {
                $this->clearCache();
            }
            
            return $affected;
        } catch (\Exception $e) {
            throw DatabaseException::deleteFailed("批量删除失败: {$e->getMessage()}", $e);
        }
    }

    // --- 通用操作方法 ---

    /**
     * 自增操作
     * @throws DatabaseException
     */
    public function increment(array $criteria, string $field, int $amount = 1, array $extra = []): bool
    {
        try {
            $query = $this->buildQuery($this->newQuery(), $criteria);
            $result = $this->ormStrategy->increment($query, $field, $amount, $extra);
            
            if ($result) {
                $this->clearCache();
            }
            
            return $result;
        } catch (\Exception $e) {
            throw DatabaseException::updateFailed("自增操作失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 自减操作
     * @throws DatabaseException
     */
    public function decrement(array $criteria, string $field, int $amount = 1, array $extra = []): bool
    {
        try {
            $query = $this->buildQuery($this->newQuery(), $criteria);
            $result = $this->ormStrategy->decrement($query, $field, $amount, $extra);
            
            if ($result) {
                $this->clearCache();
            }
            
            return $result;
        } catch (\Exception $e) {
            throw DatabaseException::updateFailed("自减操作失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 聚合查询
     * @throws DatabaseException
     */
    public function aggregate(string $type, array $criteria = [], string $field = '*'): string|int|float
    {
        try {
            $query = $this->buildQuery($this->newQuery(), $criteria);
            
            $result = match (strtolower($type)) {
                'count' => $query->count($field),
                'sum'   => $query->sum($field),
                'max'   => $query->max($field),
                'min'   => $query->min($field),
                'avg'   => $query->avg($field),
                default => 0,
            };

            if ($type === 'sum' && is_numeric($result)) {
                return (string) $result;
            }

            return $result;
        } catch (\Exception $e) {
            throw DatabaseException::queryFailed("聚合查询失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 事务处理
     */
    public function transaction(\Closure $callback): mixed
    {
        try {
            return $this->ormStrategy->transaction($callback);
        } catch (\Exception $e) {
            throw new DatabaseException("事务执行失败: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * 原生查询
     */
    public function query(string $sql, array $bindings = []): array
    {
        try {
            return $this->ormStrategy->query($sql, $bindings);
        } catch (\Exception $e) {
            throw DatabaseException::queryFailed("原生查询失败: {$e->getMessage()}", $e);
        }
    }

    /**
     * 原生执行
     */
    public function execute(string $sql, array $bindings = []): int
    {
        try {
            $affected = $this->ormStrategy->execute($sql, $bindings);
            
            if ($affected > 0) {
                $this->clearCache();
            }
            
            return $affected;
        } catch (\Exception $e) {
            throw DatabaseException::updateFailed("原生执行失败: {$e->getMessage()}", $e);
        }
    }

    // --- 内部工具方法 ---

    /**
     * 构建查询条件（使用独立的构建器）
     */
    protected function buildQuery(mixed $query, array $criteria, array $orderBy = []): mixed
    {
        $builder = new QueryConditionBuilder(
            $query,
            $this->factory->isEloquent(),
            $this->isModelClass()
        );
        
        return $builder->build($criteria, $orderBy);
    }


	/**
	 * 生成缓存Key（彻底修复：完整包含所有动态参数）
	 * @param string $method 调用的方法名（如findById、findAll）
	 * @param mixed ...$params 方法的所有入参（查询条件、排序、分页等）
	 * @return string 唯一的缓存Key
	 */
	protected function getCacheKey(string $method, ...$params): string
	{
		// 1. 收集所有影响查询结果的动态因素（核心：不能遗漏任何维度）
		$cacheFactors = [
			// 基础维度：模型类 + 方法名
			'model' => $this->modelClass,
			'method' => $method,
			
			// 动态维度1：方法的所有入参（查询条件、排序、分页、关联等）
			'params' => $this->normalizeParams($params),
			
			// 动态维度2：业务上下文（租户ID、用户ID等）
			'context' => [
				'tenant_id' => TenantContext::getTenantId() , //method_exists('TenantContext', 'getTenantId') ? TenantContext::getTenantId() : 0,
				// 可扩展：其他上下文（如用户ID、语言、环境等）
				// 'user_id' => UserContext::getUserId() ?? 0,
			],
			
			// 动态维度3：ORM类型（避免不同ORM的缓存冲突）
			'orm_type' => $this->factory->isEloquent() ? 'eloquent' : 'think',
		];
		
		#dump($cacheFactors);

		// 2. 序列化并生成MD5（确保唯一性和固定长度）
		$serializedFactors = serialize($cacheFactors);
		$factorsMd5 = md5($serializedFactors);

		// 3. 生成易读且唯一的缓存Key（便于调试）
		return sprintf(
			'repo:%s:%s:%s',
			str_replace('\\', '_', $this->modelClass), // 替换命名空间分隔符
			$method,
			$factorsMd5
		);
	}

	/**
	 * 标准化参数（解决数组顺序、空值等导致的Key不一致问题）
	 * @param array $params 原始参数
	 * @return array 标准化后的参数
	 */
	private function normalizeParams(array $params): array
	{
		array_walk_recursive($params, function (&$value) {
			// 处理闭包（无法序列化）：闭包查询条件不缓存
			if ($value instanceof \Closure) {
				throw new \RuntimeException("包含闭包的查询条件不支持缓存，请关闭缓存或移除闭包");
			}
			// 统一空值格式（避免null和''导致Key不同）
			if ($value === '') {
				$value = null;
			}
			// 数组排序（避免['id'=>1, 'status'=>2]和['status'=>2, 'id'=>1]生成不同Key）
			if (is_array($value)) {
				ksort($value);
			}
		});
		
		// 排序顶层参数（确保参数顺序不影响Key）
		ksort($params);
		
		return $params;
	}

	/**
	 * 是否启用缓存（子类可重写，或通过入参控制）
	 * @param string $method 调用的方法名
	 * @param array $params 方法入参
	 * @return bool
	 */
	protected function isCacheEnabled(string $method, array $params): bool
	{
		// 1. 全局关闭缓存（可通过配置控制）
		$cacheDisabled = false;
		if (function_exists('config')) {
			$cacheDisabled = (bool)config('cache.REPO_CACHE_DISABLED', false);
		}
		#dump($cacheDisabled);
		if ($cacheDisabled) {
			return true;
		}
		
		// 2. 包含闭包的查询不缓存（闭包无法序列化）
		$hasClosure = false;
		array_walk_recursive($params, function ($value) use (&$hasClosure) {
			if ($value instanceof \Closure) {
				$hasClosure = true;
			}
		});
		if ($hasClosure) {
			return false;
		}
		
		// 3. 写入操作不缓存（只缓存查询操作）
		$writeMethods = ['create', 'update', 'delete', 'increment', 'decrement'];
		if (in_array($method, $writeMethods)) {
			return false;
		}
		
		return true;
	}


    /**
     * 清除缓存
     */
    protected function clearCache(?string $key = null): void
    {
        if (!$this->cache) {
            return;
        }

        if ($key) {
            $this->cache->delete($key);
        } else {
            // 可以实现按前缀清除缓存
            // $this->cache->deleteMatching("repo:{$this->modelClass}:*");
        }
    }

    /**
     * 设置缓存实例
     */
    public function setCache(CacheInterface $cache, int $ttl = 3600): void
    {
        $this->cache = $cache;
        $this->cacheTtl = $ttl;
    }
}