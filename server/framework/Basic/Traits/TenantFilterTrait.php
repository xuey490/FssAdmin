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

use Closure;
use RuntimeException;

/**
 * 多租户数据自动过滤 Trait
 * 
 * 该 Trait 用于 BaseDao 层，自动为查询拼接租户条件。
 * 与 LaBelongsToTenant/TpBelongsToTenant 不同，此 Trait 用于手动拼接条件，
 * 而非通过全局作用域实现。
 * 
 * 主要功能：
 * - 自动为查询添加租户过滤条件
 * - 支持临时忽略租户过滤
 * - 避免重复添加租户条件
 * 
 * @package Framework\Basic\Traits
 */
trait TenantFilterTrait
{
    /**
     * 租户字段名
     * 
     * 子类可重写此属性以自定义租户字段名。
     * 
     * @var string
     */
    protected string $tenantField = 'tenant_id';

    /**
     * 是否忽略租户过滤标记
     * 
     * 单次请求有效，使用后自动重置。
     * 
     * @var bool
     */
    protected bool $ignoreTenantFilter = false;

    /**
     * 获取当前租户ID的回调函数
     * 
     * 需在框架初始化时注入，例如从请求头、上下文、登录信息中获取。
     * 
     * @var Closure|null
     */
    protected static ?Closure $getTenantIdCallback = null;

    /**
     * 初始化租户ID获取回调
     * 
     * 在框架初始化时调用，设置获取当前租户ID的方式。
     * 
     * @param Closure $callback 回调函数，需返回当前租户ID（int|string）
     * @return void
     */
    public static function initTenantCallback(Closure $callback): void
    {
        self::$getTenantIdCallback = $callback;
    }

    /**
     * 忽略租户过滤（单次查询有效）
     * 
     * 调用此方法后，下一次查询将不会自动添加租户条件。
     * 使用后状态会自动重置。
     * 
     * 用法示例：
     *   $dao->ignoreTenant()->selectList(...);
     * 
     * @return $this
     */
    public function ignoreTenant(): self
    {
        $this->ignoreTenantFilter = true;
        return $this;
    }

    /**
     * 重置租户过滤状态
     * 
     * 在每次查询后自动调用，确保忽略标记只生效一次。
     * 
     * @return void
     */
    protected function resetTenantFilter(): void
    {
        $this->ignoreTenantFilter = false;
    }

    /**
     * 获取当前租户ID
     * 
     * 通过回调函数获取当前请求的租户ID。
     * 
     * @return int|string 当前租户ID
     * @throws RuntimeException 当回调未初始化或租户ID为空时抛出异常
     */
    protected function getCurrentTenantId(): int|string
    {
        if (!self::$getTenantIdCallback) {
            throw new RuntimeException('租户ID获取回调未初始化，请调用 ' . static::class . '::initTenantCallback()');
        }
        $tenantId = call_user_func(self::$getTenantIdCallback);
        if ($tenantId === null || $tenantId === '') {
            throw new RuntimeException('当前租户ID为空，无法进行数据过滤');
        }
        return $tenantId;
    }

    /**
     * 自动拼接租户条件
     * 
     * 根据当前上下文自动为查询条件添加租户过滤。
     * 如果已设置忽略标记或条件中已包含租户条件，则跳过。
     * 
     * @param array $where 原始查询条件数组
     * @return array 添加租户条件后的查询条件数组
     */
    protected function autoAddTenantCondition(array $where): array
    {
        // 1. 忽略过滤或无租户字段时，直接返回原条件
        if ($this->ignoreTenantFilter || empty($this->tenantField)) {
            $this->resetTenantFilter();
            return $where;
        }

        // 2. 获取当前租户ID
        $tenantId = $this->getCurrentTenantId();

        // 3. 避免重复添加租户条件
        $hasTenantCondition = false;
        foreach ($where as $key => $value) {
            if (is_string($key) && $key === $this->tenantField) {
                $hasTenantCondition = true;
                break;
            }
        }

        if (!$hasTenantCondition) {
            $where[static::getModel()->getTable().'.'.$this->tenantField] = $tenantId;
        }

        // 4. 重置状态，确保单次忽略只生效一次
        $this->resetTenantFilter();
        return $where;
    }
}