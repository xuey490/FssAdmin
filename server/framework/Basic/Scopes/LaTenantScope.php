<?php

declare(strict_types=1);

namespace Framework\Basic\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Framework\Tenant\TenantContext;

/**
 * Laravel 模型租户隔离作用域
 *
 * 为 Eloquent 模型提供自动的租户隔离功能。当模型使用此全局作用域时，
 * 所有查询、更新、删除操作都会自动添加 tenant_id 条件，
 * 确保多租户环境下的数据隔离。
 *
 * 使用方式：
 * 1. 在模型中 use LaBelongsToTenant trait（推荐）：
 *    class User extends Model {
 *        use \Framework\Basic\Traits\LaBelongsToTenant;
 *    }
 *
 * 2. 或手动注册全局作用域：
 *    protected static function booted() {
 *        static::addGlobalScope(new LaTenantScope());
 *    }
 *
* 为特定租户查询（超管使用）
* User::forTenant(1001)->get();

* 查询所有租户（保留作用域但忽略上下文）
* User::allTenants()->get();
* 模型结构变更后清理缓存
* LaTenantScope::clearCache();
* class User extends Model {
*     protected string $tenantColumn = 'custom_tenant_id';
* }
 * 临时跳过租户隔离：
 * - Model::withoutTenancy()->get();           // 移除作用域
 * - Model::withIgnoreTenant(fn() => ...);     // 安全作用域方式
 * - TenantContext::withIgnore(fn() => ...);   // 直接使用上下文
 *
 * @package Framework\Basic\Scopes
 */
// 基础使用（自动隔离）
// User::all(); // SELECT * FROM users WHERE tenant_id = 1001

// 超管查看特定租户
// User::forTenant(1002)->get();

// 超管查看所有租户
// User::allTenants()->get();

// 临时忽略隔离
// User::withoutTenancy()->get();

// 自定义租户字段的模型
//class CustomModel extends Model {
//    protected string $tenantColumn = 'company_id';
//}

class LaTenantScope implements Scope
{
    /**
     * 已检查的模型字段缓存
     * 避免重复检查模型是否有 tenant_id 字段
     *
     * @var array<class-string<Model>, bool>
     */
    private static array $checkedModels = [];

    /**
     * 应用租户隔离作用域
     *
     * 在查询、更新、删除时自动追加 tenant_id 条件。
     * 如果当前处于"忽略租户"状态或租户ID为空，则不添加限制条件。
     * 只有模型表包含 tenant_id 字段时才会生效。
     *
     * @param Builder $builder Eloquent 查询构建器
     * @param Model $model 模型实例
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 如果当前处于"忽略租户"状态，直接放行
        if (!TenantContext::shouldApplyTenant()) {
            return;
        }

        $tenantId = TenantContext::getTenantId();

        if ($tenantId === null) {
            return;
        }

        // 检查模型是否有 tenant_id 字段（使用缓存）
        $modelClass = get_class($model);
        if (!isset(self::$checkedModels[$modelClass])) {
            self::$checkedModels[$modelClass] = $this->hasTenantColumn($model);
        }

        if (!self::$checkedModels[$modelClass]) {
            return;
        }

        // 获取租户字段名（支持自定义）
        $column = $this->getTenantColumn($model);

        // 应用租户条件
        $builder->where($column, '=', $tenantId);
    }

    /**
     * 扩展查询构建器，增加租户相关方法
     *
     * @param Builder $builder Eloquent 查询构建器
     * @return void
     */
    public function extend(Builder $builder): void
    {
        // 移除全局租户作用域
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        // 为特定租户查询（超管使用）
        $builder->macro('forTenant', function (Builder $builder, int $tenantId) {
            return $builder->withoutGlobalScope($this)
                ->where($builder->getModel()->qualifyColumn('tenant_id'), '=', $tenantId);
        });

        // 查询所有租户的数据（超管使用，保留作用域但忽略上下文）
        $builder->macro('allTenants', function (Builder $builder) {
            return TenantContext::withIgnore(function () use ($builder) {
                return $builder;
            });
        });
    }

    /**
     * 检查模型是否有租户字段
     *
     * 优先通过 $fillable 快速判断，避免不必要的数据库查询。
     * 如果 $fillable 中没有声明 tenant_id，直接返回 false，
     * 防止向无 tenant_id 列的表添加租户过滤条件。
     *
     * @param Model $model 模型实例
     * @return bool 有租户字段返回 true
     */
    private function hasTenantColumn(Model $model): bool
    {
        // 优先通过 fillable 快速判断（无需查数据库）
        if (!in_array('tenant_id', $model->getFillable(), true)) {
            return false;
        }

        // fillable 中有 tenant_id，再通过 getFields 确认表结构
        try {
            $fields = $model->getFields();
            return in_array('tenant_id', $fields, true);
        } catch (\Throwable $e) {
            // 获取字段失败时，以 fillable 声明为准，返回 true
            return true;
        }
    }

    /**
     * 获取租户字段名
     *
     * 支持模型通过 $tenantColumn 属性自定义字段名
     *
     * @param Model $model 模型实例
     * @return string 完整的字段名（如：users.tenant_id）
     */
    private function getTenantColumn(Model $model): string
    {
        // 支持模型自定义字段名
        if (property_exists($model, 'tenantColumn')) {
            return $model->qualifyColumn($model->tenantColumn);
        }

        return $model->qualifyColumn('tenant_id');
    }

    /**
     * 清理模型缓存
     *
     * 在模型结构变更后调用，清除字段检查缓存
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$checkedModels = [];
    }
}
