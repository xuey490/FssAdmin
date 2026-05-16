<?php
declare(strict_types=1);

namespace Framework\Repository\Strategies;

// ThinkPHP策略实现
class ThinkStrategy implements OrmStrategyInterface
{
    public function getQueryBuilder(string $modelClass): mixed
    {
        if (class_exists($modelClass)) {
            return (new $modelClass)->db();
        }
        return \think\facade\Db::table($modelClass);
    }

    public function increment(mixed $query, string $field, int $amount, array $extra): bool
    {
        return (bool) $query->inc($field, $amount)->update($extra);
    }

    public function decrement(mixed $query, string $field, int $amount, array $extra): bool
    {
        return (bool) $query->dec($field, $amount)->update($extra);
    }

    public function transaction(\Closure $callback): mixed
    {
        return \think\facade\Db::transaction($callback);
    }

    public function query(string $sql, array $bindings): array
    {
        return \think\facade\Db::query($sql, $bindings);
    }

    public function execute(string $sql, array $bindings): int
    {
        return (int) \think\facade\Db::execute($sql, $bindings);
    }
}