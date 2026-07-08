<?php
declare(strict_types=1);

namespace Framework\Repository\Strategies;

/**
 * ORM策略接口
 * 定义不同ORM需要实现的通用方法
 */
interface OrmStrategyInterface
{
    /**
     * 获取查询构建器
     */
    public function getQueryBuilder(string $modelClass): mixed;

    /**
     * 执行自增操作
     * @param array<mixed> $extra
 */
    public function increment(mixed $query, string $field, int $amount, array $extra): bool;

    /**
     * 执行自减操作
     * @param array<mixed> $extra
 */
    public function decrement(mixed $query, string $field, int $amount, array $extra): bool;

    /**
     * 执行事务
     */
    public function transaction(\Closure $callback): mixed;

    /**
     * 执行原生查询
     * @param array<mixed> $bindings
 * @return array<mixed>
 */
    public function query(string $sql, array $bindings): array;

    /**
     * 执行原生执行
     * @param array<mixed> $bindings
 */
    public function execute(string $sql, array $bindings): int;
}
