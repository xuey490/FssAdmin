<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-9
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Traits;

use Framework\Basic\Scopes\TpTenantScope;
use think\db\Query;
use think\Model;
use Framework\Tenant\TenantContext;
use Closure;

/**
 * ThinkPHP 多租户隔离 Trait
 * 
 * 该 Trait 为 ThinkPHP 8 模型提供多租户数据隔离功能。
 * 通过模型事件和查询作用域自动实现租户数据的隔离。
 * 
 * 主要功能：
 * - 创建记录时自动填充租户ID
 * - 查询时自动添加租户过滤条件
 * - 支持超管模式忽略租户隔离
 * 
 * 使用方式：在需要多租户隔离的 ThinkPHP 模型中 use 此 Trait。
 * 
 * @package Framework\Basic\Traits
 */
trait TpBelongsToTenant
{

    /**
     * Trait 初始化方法
     * 
     * 注意：在 TP8 中，我们通常不需要手动调用 event 绑定，
     * 框架会自动扫描 onBeforeInsert 等静态方法。
     * 此方法保留仅用于兼容旧代码或手动调用。
     * 
     * @return void
     */
    public static function initTpBelongsToTenant(): void
    {
        // 留空或仅用于调试，主要逻辑移至 onBeforeInsert
        // TP8 会自动识别 onBeforeInsert 静态方法
    }

    // =========================================================================
    //  链式调用：开启/关闭 租户隔离
    // =========================================================================
	
    /**
     * 临时忽略租户隔离（链式调用）
     * 
     * 用于超级管理员查看所有租户数据的场景。
     * 
     * 用法示例：
     *   User::ignoreTenant()->find(123);
     * 
     * @return static 返回模型实例，支持链式调用
     */
    public function ignoreTenant(): static
    {
        TenantContext::ignore();
        return $this;
    }	

    /**
     * 恢复租户隔离
     * 
     * 在调用 ignoreTenant() 后，手动恢复租户隔离机制。
     * 一般用于链式操作后恢复状态。
     * 
     * @return static 返回模型实例，支持链式调用
     */
    public function restoreTenant(): static
    {
        TenantContext::restore();
        return $this;
    }

    /**
     * 临时超管访问闭包执行
     * 
     * 在闭包内临时忽略租户隔离，执行完毕后自动恢复。
     * 
     * 用法示例：
     *   User::withIgnoreTenant(function () {
     *       return User::all();
     *   });
     * 
     * @param Closure $closure 需要在忽略租户隔离下执行的闭包
     * @return mixed 闭包的返回值
     */
    public static function withIgnoreTenant(Closure $closure): mixed
    {
        TenantContext::ignore();
        try {
            return $closure();
        } finally {
            TenantContext::restore();
        }
    }

    // =========================================================================
    //  模型事件：自动填充 tenant_id
    // =========================================================================

    /**
     * ThinkPHP 8 模型事件：新增前
     * 
     * 在模型数据插入数据库前自动填充租户ID。
     * 统一使用这个标准事件，避免使用 init + event 的旧模式。
     * 
     * @param Model $model ThinkPHP 模型实例
     * @return void
     */
    public static function onBeforeInsert(Model $model): void
    {
        // 如果已经设置了 tenant_id 或者开启了忽略模式，跳过
        if (isset($model->tenant_id) || !TenantContext::shouldApplyTenant()) {
            return;
        }

        $tenantId = TenantContext::getTenantId();
        if ($tenantId) {
            $model->setAttr('tenant_id', $tenantId);
        }
    }

    // 如果需要更新时也检查（通常不需要，租户ID不应被修改），可以开启
    // public static function onBeforeUpdate(Model $model): void { ... }


    /**
     * 租户查询作用域
     * 
     * 用于模型查询时自动添加租户过滤条件。
     * 会被基类的全局作用域调用，或者手动调用。
     * 当开启忽略模式时（超管访问），不会添加租户条件。
     * 
     * @param Query $query ThinkPHP 查询构建器
     * @return void
     */
    public function scopeTenant(Query $query): void
    {
        // 如果开启了忽略模式，则不加 tenant_id 条件
        if (!TenantContext::shouldApplyTenant()) {
			// 超管或无租户，跳过条件
            return;
        }

        // 委托给具体的 Scope 类处理（保持逻辑分离）
        // 确保 TpTenantScope 存在且 apply 方法正确
        (new TpTenantScope())->apply($query, $this);
    }



}