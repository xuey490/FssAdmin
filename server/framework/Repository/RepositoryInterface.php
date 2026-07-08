<?php
declare(strict_types=1);

namespace Framework\Repository;

use Framework\Repository\Exceptions\DatabaseException;

interface RepositoryInterface
{
    /**
     * 根据ID查找记录
     * @throws DatabaseException
     * @param array<mixed> $with
 */
    public function findById(int|string $id, array $with = []): mixed;

    /**
     * 根据条件查找单条记录
     * @throws DatabaseException
     * @param array<mixed> $criteria
 * @param array<mixed> $with
 */
    public function findOneBy(array $criteria, array $with = []): mixed;

    /**
     * 根据条件查找多条记录
     * @throws DatabaseException
     * @param array<mixed> $criteria
 * @param array<mixed> $orderBy
 * @param array<mixed> $with
 */
    public function findAll(array $criteria = [], array $orderBy = [], ?int $limit = null, array $with = []): mixed;

    /**
     * 分页查询
     * @throws DatabaseException
     * @param array<mixed> $criteria
 * @param array<mixed> $orderBy
 * @param array<mixed> $with
 */
    public function paginate(array $criteria = [], int $perPage = 15, array $orderBy = [], array $with = []): mixed;

    /**
     * 创建记录
     * @throws DatabaseException
     * @param array<mixed> $data
 */
    public function create(array $data): mixed;

    /**
     * 更新记录
     * @throws DatabaseException
     * @param array<mixed> $criteria
 * @param array<mixed> $data
 */
    public function update(array $criteria, array $data): bool;

    /**
     * 按条件批量更新
     * @throws DatabaseException
     * @param array<mixed> $criteria
 * @param array<mixed> $data
 */
    public function updateBy(array $criteria, array $data): int;

    /**
     * 删除记录
     * @throws DatabaseException
     * @param array<mixed> $criteria
 */
    public function delete(array $criteria): bool;

    /**
     * 按条件批量删除
     * @throws DatabaseException
     * @param array<mixed> $criteria
 */
    public function deleteBy(array $criteria): int;

    /**
     * 自增操作
     * @throws DatabaseException
     * @param array<mixed> $criteria
 * @param array<mixed> $extra
 */
    public function increment(array $criteria, string $field, int $amount = 1, array $extra = []): bool;

    /**
     * 自减操作
     * @throws DatabaseException
     * @param array<mixed> $criteria
 * @param array<mixed> $extra
 */
    public function decrement(array $criteria, string $field, int $amount = 1, array $extra = []): bool;

    /**
     * 聚合查询
     * @throws DatabaseException
     * @param array<mixed> $criteria
 */
    public function aggregate(string $type, array $criteria = [], string $field = '*'): string|int|float;

    /**
     * 事务处理
     */
    public function transaction(\Closure $callback): mixed;

    /**
     * 原生查询
     * @throws DatabaseException
     * @param array<mixed> $bindings
 * @return array<mixed>
 */
    public function query(string $sql, array $bindings = []): array;

    /**
     * 原生执行
     * @throws DatabaseException
     * @param array<mixed> $bindings
 */
    public function execute(string $sql, array $bindings = []): int;
}