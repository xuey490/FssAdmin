<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: BaseDao.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Framework\DI\Injectable;
use Framework\ORM\Adapter\ORMAdapterFactory;
use RuntimeException;
use Throwable;
use Framework\Database\DatabaseFactory;

/**
 * @method count(array $where = [], bool $search = true)
 * @method selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false)
 * @method selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false)
 * @method getCount(array $where)
 * @method getDistinctCount(array $where, $field, bool $search = true)
 * @method getPk()
 * @method getTableName()
 * @method get($id, ?array $field = [], ?array $with = [], string $order = '')
 * @method be($map, string $field = '')
 * @method getOne(array $where, ?string $field = '*', array $with = [])
 * @method value($where, ?string $field = '')
 * @method getColumn(array $where, string $field, string $key = '')
 * @method delete(array|int|string $id, ?string $key = null)
 * @method destroy(mixed $id, bool $force = false)
 * @method update(string|int|array $id, array $data, ?string $key = null)
 * @method setWhere($where, ?string $key = null)
 * @method batchUpdate(array $ids, array $data, ?string $key = null)
 * @method save(array $data)
 * @method saveAll(array $data)
 * @method getFieldValue($value, string $filed, ?string $valueKey = '', ?array $where = [])
 * @method search(array $where = [], bool $search = true)
 * @method sum(array $where, string $field, bool $search = false)
 * @method bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2)
 * @method bcDec($key, string $decField, string $dec, string $keyField = null, int $acc = 2)
 * @method getMax(array $where = [], string $field = '')
 * @method getMin(array $where = [], string $field = '')
 * @method decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales')
 * @method incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales')
 */
abstract class BaseDao
{
    // 引入注入能力
    use Injectable;

    /** @var mixed ORM Adapter，如 LaravelORMFactory 或 ThinkphpORMFactory */
    protected mixed $instance = null;

    protected ?string $mode = null;

    /** @var mixed Eloquent/ThinkORM 模型类名 */
    protected string $modelClass = '';

    public function __construct(?string $mode = null, object|string|null $modelClass = null)
    {
        $this->inject();
		
		

        // 1. 获取 ORM 模式
        if ($mode == null) {
            $mode = config('database.engine', 'thinkORM') ?? env('ORM_DRIVER');
        }

		$db = app('db');

        $this->mode = $mode;

        // 2. 获取模型类
        $modelClass = $modelClass ?? $this->setModel();

        // 3. 创建适配器
        $this->instance = ORMAdapterFactory::createAdapter($mode, $modelClass);
        #dump($this->instance);
        //dump("created model: " . get_class($this->instance));
        $this->initialize();
    }

    /**
     * 获取底层 ORM 适配器实例.
     */
    public function getAdapter(): mixed
    {
        return $this->instance;
    }

    /**
     * 动态代理调用 —— 将所有方法转发给 ORM Adapter.
     *
     * @throws RuntimeException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (! $this->instance) {
            throw new RuntimeException(
                sprintf(
                    '[DAO ERROR] %s 未初始化 ORM 适配器',
                    static::class
                )
            );
        }

        // 检查适配器是否支持该方法
        if (! method_exists($this->instance, $name)) {
            throw new RuntimeException(
                sprintf(
                    "[DAO ERROR] 方法不存在: %s::%s()\nAdapter: %s\nModel: %s",
                    static::class,
                    $name,
                    get_class($this->instance),
                    $this->modelClass
                )
            );
        }

        try {
            return $this->instance->{$name}(...$arguments);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf(
                    "[DAO ERROR] 调用 %s::%s() 时发生异常\nAdapter: %s\nModel: %s\nMessage: %s",
                    static::class,
                    $name,
                    $this->mode,
                    $this->modelClass,
                    $e->getMessage()
                ),
                0,
                $e
            );
        }
    }

    public function getModel(): mixed
    {
        if (method_exists($this->instance, 'getModel')) {
            return $this->instance->getModel();
        }

        throw new RuntimeException('当前 ORM 适配器不支持 getModel()');
    }

    /**
     * 子类可根据需要覆盖 lifecycle.
     */
    protected function initialize(): void
    {
    }

    /**
     * 获取当前模型类名.
     */
    abstract protected function setModel(): string;
}
