<?php
declare(strict_types=1);

namespace Framework\Repository\Strategies;

// Laravel Eloquent策略实现
class EloquentStrategy implements OrmStrategyInterface
{
    public function getQueryBuilder(string $modelClass): mixed
    {
        if (class_exists($modelClass)) {
            return (new $modelClass)->newQuery();
        }
        return \Illuminate\Database\Capsule\Manager::table($modelClass);
    }

    public function increment(mixed $query, string $field, int $amount, array $extra): bool
    {
        return (bool) $query->increment($field, $amount, $extra);
    }

    public function decrement(mixed $query, string $field, int $amount, array $extra): bool
    {
        return (bool) $query->decrement($field, $amount, $extra);
    }

    public function transaction(\Closure $callback): mixed
    {
        return \Illuminate\Database\Capsule\Manager::transaction($callback);
    }

    public function query(string $sql, array $bindings): array
    {
        $result = \Illuminate\Database\Capsule\Manager::select($sql, $bindings);
        return array_map(fn($item) => (array) $item, $result);
    }

    public function execute(string $sql, array $bindings): int
    {
        return \Illuminate\Database\Capsule\Manager::affectingStatement($sql, $bindings);
    }
}

