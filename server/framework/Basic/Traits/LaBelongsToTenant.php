<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-23
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Traits;

use Illuminate\Database\Eloquent\Model;
use Framework\Basic\Scopes\LaTenantScope;
use Framework\Tenant\TenantContext;

/**
 * Laravel 多租户隔离 Trait
 * 
 * 该 Trait 为 Laravel Eloquent 模型提供多租户数据隔离功能。
 * 通过全局作用域自动为查询添加租户条件，并在创建记录时自动填充租户ID。
 * 
 * 主要功能：
 * - 自动添加租户查询条件（读、改、删）
 * - 创建时自动写入租户ID
 * - 支持超管模式忽略租户隔离
 * 
 * 使用方式：在需要多租户隔离的模型中 use 此 Trait。
 * 
 * @package Framework\Basic\Traits
 */
trait LaBelongsToTenant
{
    /**
     * Trait 引导方法
     * 
     * 在模型启动时自动调用，完成以下操作：
     * 1. 注册全局租户作用域（用于读、改、删操作的数据隔离）
     * 2. 绑定 creating 事件（用于新增时自动填充租户ID）
     * 
     * @return void
     */
    public static function bootLaBelongsToTenant()
    {
        // 1. 添加全局作用域（读、改、删限制）
        static::addGlobalScope(new LaTenantScope());

        // 2. 创建时自动写入租户ID（新增限制）
        static::creating(function (Model $model) {
            // 仅当模型的 fillable 中声明了 tenant_id 字段时才写入
            // 避免向没有 tenant_id 列的表插入数据时报错
            if (!in_array('tenant_id', $model->getFillable(), true)) {
                return;
            }

            $tenantId = null;
            try {
                $tenantId = TenantContext::getTenantId();
            } catch (\Throwable $e) {
                // TenantContext 未初始化时静默跳过
            }

            // 有租户ID，且模型里还没有设置该字段时才写入
            if ($tenantId && !isset($model->tenant_id)) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });
    }
	
    // ==================================================
    // 超管模式相关方法（简化版，直接使用 TenantContext）
    // ==================================================

    /**
     * 移除全局租户作用域
     *
     * 允许临时忽略租户限制（例如超级管理员后台查看所有数据）。
     * 这是最常用的方式，直接移除全局作用域。
     *
     * 用法示例：
     *   User::withoutTenancy()->get(); // 获取所有租户的用户
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutTenancy()
    {
        return static::withoutGlobalScope(LaTenantScope::class);
    }

    /**
     * 在闭包内临时忽略租户隔离（安全作用域方式）
     *
     * 推荐使用的临时超管访问方式，在闭包执行完毕后自动恢复租户隔离。
     * 这是 TenantContext::withIgnore() 的便捷包装。
     *
     * 用法示例：
     *   User::withIgnoreTenant(function() {
     *       return User::all(); // 返回所有租户数据
     *   });
     *
     * @param callable $fn 需要在忽略租户隔离下执行的闭包
     * @return mixed 闭包的返回值
     */
    public static function withIgnoreTenant(callable $fn)
    {
        return TenantContext::withIgnore($fn);
    }
}