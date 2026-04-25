<?php

declare(strict_types=1);

namespace Framework\Basic\Traits;

/**
 * 数据权限隔离 Trait
 *
 * 该 Trait 提供数据权限隔离功能，用于实现细粒度的数据访问控制。
 * 根据用户的权限范围（scope）自动过滤查询结果，确保用户只能访问其权限范围内的数据。
 *
 * 支持的权限范围：
 * - 1: 全部数据权限（不限制）
 * - 2: 本部门数据
 * - 3: 本部门及子部门数据
 * - 4: 仅本人数据
 * - 5: 本部门及子部门 + 本人数据
 * - 6: 自定义部门数据
 * - 其他: 无权限
 *
 * 使用方式：
 * 1. 在模型中 use 此 Trait
 * 2. 确保表中有 created_by 和 dept_id 字段
 * 3. 在查询时使用 dataScope() 方法
 *
 * 示例：
 * ```php
 * // 在控制器中
 * Article::dataScope()->get();  // 自动应用数据权限
 *
 * // 忽略数据权限（超管）
 * Article::withoutDataScope()->get();
 * ```
 *
 * @package Framework\Basic\Traits
 */

trait DataScopeTrait
{
    /**
     * 数据权限范围常量
     */
    public const DATA_SCOPE_ALL = 1;           // 全部数据
    public const DATA_SCOPE_DEPT = 2;          // 本部门
    public const DATA_SCOPE_DEPT_AND_CHILD = 3; // 本部门及子部门
    public const DATA_SCOPE_SELF = 4;          // 仅本人
    public const DATA_SCOPE_DEPT_AND_SELF = 5; // 本部门及子部门 + 本人
    public const DATA_SCOPE_CUSTOM = 6;        // 自定义部门

    /**
     * 是否忽略数据权限
     * @var bool
     */
    protected static bool $ignoreDataScope = false;

    /**
     * 当前用户的数据权限范围
     * @var int|null
     */
    protected static ?int $currentDataScope = null;

    /**
     * 当前用户ID
     * @var int|null
     */
    protected static ?int $currentUserId = null;

    /**
     * 当前用户部门ID
     * @var int|null
     */
    protected static ?int $currentDeptId = null;

    /**
     * 自定义部门ID列表
     * @var array
     */
    protected static array $customDeptIds = [];

    /**
     * 启动数据权限 Trait
     *
     * 注册全局作用域，自动应用数据权限
     *
     * @return void
     */
    public static function bootDataScopeTrait(): void
    {
        static::addGlobalScope('data_scope', function ($builder) {
            if (!self::$ignoreDataScope && self::shouldApplyDataScope()) {
                self::applyDataScope($builder);
            }
        });
    }

    /**
     * 是否应该应用数据权限
     *
     * @return bool
     */
    protected static function shouldApplyDataScope(): bool
    {
        // 如果没有设置当前用户，不应用数据权限
        if (self::$currentUserId === null) {
            return false;
        }

        // 超级管理员不限制
        if (self::isSuperAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * 应用数据权限到查询
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected static function applyDataScope($builder): void
    {
        $scope = self::$currentDataScope ?? self::DATA_SCOPE_SELF;

        switch ($scope) {
            case self::DATA_SCOPE_ALL:
                // 全部数据，不添加限制
                break;

            case self::DATA_SCOPE_DEPT:
                // 本部门数据
                self::applyDeptScope($builder, false);
                break;

            case self::DATA_SCOPE_DEPT_AND_CHILD:
                // 本部门及子部门数据
                self::applyDeptScope($builder, true);
                break;

            case self::DATA_SCOPE_SELF:
                // 仅本人数据
                self::applySelfScope($builder);
                break;

            case self::DATA_SCOPE_DEPT_AND_SELF:
                // 本部门及子部门 + 本人数据
                self::applyDeptAndSelfScope($builder);
                break;

            case self::DATA_SCOPE_CUSTOM:
                // 自定义部门数据
                self::applyCustomDeptScope($builder);
                break;

            default:
                // 默认仅本人数据（最安全）
                self::applySelfScope($builder);
                break;
        }
    }

    /**
     * 应用部门数据权限
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param bool $includeChildren 是否包含子部门
     * @return void
     */
    protected static function applyDeptScope($builder, bool $includeChildren = false): void
    {
        $deptId = self::$currentDeptId;

        if ($deptId === null) {
            // 如果没有部门，只能看自己的数据
            self::applySelfScope($builder);
            return;
        }

        if ($includeChildren) {
            // 获取所有子部门ID
            $deptIds = self::getChildDeptIds($deptId);
            $deptIds[] = $deptId;
            $builder->whereIn(self::getDeptColumn(), $deptIds);
        } else {
            $builder->where(self::getDeptColumn(), $deptId);
        }
    }

    /**
     * 应用本人数据权限
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected static function applySelfScope($builder): void
    {
        $builder->where(self::getCreatedByColumn(), self::$currentUserId);
    }

    /**
     * 应用部门 + 本人数据权限
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected static function applyDeptAndSelfScope($builder): void
    {
        $deptId = self::$currentDeptId;

        if ($deptId === null) {
            self::applySelfScope($builder);
            return;
        }

        // 获取所有子部门ID
        $deptIds = self::getChildDeptIds($deptId);
        $deptIds[] = $deptId;

        $builder->where(function ($query) use ($deptIds) {
            $query->whereIn(self::getDeptColumn(), $deptIds)
                ->orWhere(self::getCreatedByColumn(), self::$currentUserId);
        });
    }

    /**
     * 应用自定义部门数据权限
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected static function applyCustomDeptScope($builder): void
    {
        $deptIds = self::$customDeptIds;

        if (empty($deptIds)) {
            // 如果没有自定义部门，只能看自己的数据
            self::applySelfScope($builder);
            return;
        }

        $builder->whereIn(self::getDeptColumn(), $deptIds);
    }

    // ==================== 公共方法 ====================

    /**
     * 设置当前用户的数据权限上下文
     *
     * @param int $userId 用户ID
     * @param int $dataScope 数据权限范围
     * @param int|null $deptId 部门ID
     * @param array $customDeptIds 自定义部门ID列表
     * @return void
     */
    public static function setDataScopeContext(
        int $userId,
        int $dataScope = self::DATA_SCOPE_SELF,
        ?int $deptId = null,
        array $customDeptIds = []
    ): void {
        self::$currentUserId = $userId;
        self::$currentDataScope = $dataScope;
        self::$currentDeptId = $deptId;
        self::$customDeptIds = $customDeptIds;
    }

    /**
     * 清除数据权限上下文
     *
     * @return void
     */
    public static function clearDataScopeContext(): void
    {
        self::$currentUserId = null;
        self::$currentDataScope = null;
        self::$currentDeptId = null;
        self::$customDeptIds = [];
    }

    /**
     * 忽略数据权限（用于超管）
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutDataScope($query)
    {
        return $query->withoutGlobalScope('data_scope');
    }

    /**
     * 强制应用数据权限（即使已设置忽略）
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithDataScope($query)
    {
        self::$ignoreDataScope = false;
        return $query;
    }

    /**
     * 临时忽略数据权限执行回调
     *
     * @param callable $callback
     * @return mixed
     */
    public static function withoutDataScope(callable $callback)
    {
        $previous = self::$ignoreDataScope;
        self::$ignoreDataScope = true;

        try {
            return $callback();
        } finally {
            self::$ignoreDataScope = $previous;
        }
    }

    // ==================== 辅助方法 ====================

    /**
     * 检查是否为超级管理员
     *
     * @return bool
     */
    protected static function isSuperAdmin(): bool
    {
        // 可以通过 session 或上下文获取当前用户是否为超管
        // 这里简化处理，实际项目中应根据业务逻辑判断
        if (function_exists('isSuperAdmin')) {
            return isSuperAdmin();
        }

        // 默认判断：user_id 为 1 的是超管
        return self::$currentUserId === 1;
    }

    /**
     * 获取子部门ID列表
     *
     * @param int $deptId 部门ID
     * @return array
     */
    protected static function getChildDeptIds(int $deptId): array
    {
        // 如果有 SysDept 模型，使用模型的方法
        if (class_exists('App\Models\SysDept')) {
            return \App\Models\SysDept::getAllChildIds($deptId);
        }

        // 否则返回空数组
        return [];
    }

    /**
     * 获取部门字段名
     *
     * @return string
     */
    protected static function getDeptColumn(): string
    {
        return property_exists(static::class, 'dataScopeDeptColumn')
            ? (new static)->dataScopeDeptColumn
            : 'dept_id';
    }

    /**
     * 获取创建人字段名
     *
     * @return string
     */
    protected static function getCreatedByColumn(): string
    {
        return property_exists(static::class, 'dataScopeCreatedByColumn')
            ? (new static)->dataScopeCreatedByColumn
            : 'created_by';
    }

    /**
     * 获取数据权限范围名称
     *
     * @param int $scope 权限范围值
     * @return string
     */
    public static function getDataScopeName(int $scope): string
    {
        return match ($scope) {
            self::DATA_SCOPE_ALL => '全部数据',
            self::DATA_SCOPE_DEPT => '本部门数据',
            self::DATA_SCOPE_DEPT_AND_CHILD => '本部门及子部门数据',
            self::DATA_SCOPE_SELF => '仅本人数据',
            self::DATA_SCOPE_DEPT_AND_SELF => '本部门及子部门+本人数据',
            self::DATA_SCOPE_CUSTOM => '自定义部门数据',
            default => '未知',
        };
    }

    /**
     * 获取所有数据权限选项
     *
     * @return array
     */
    public static function getDataScopeOptions(): array
    {
        return [
            ['value' => self::DATA_SCOPE_ALL, 'label' => '全部数据', 'desc' => '可查看所有数据'],
            ['value' => self::DATA_SCOPE_DEPT, 'label' => '本部门数据', 'desc' => '仅可查看本部门数据'],
            ['value' => self::DATA_SCOPE_DEPT_AND_CHILD, 'label' => '本部门及子部门', 'desc' => '可查看本部门及所有子部门数据'],
            ['value' => self::DATA_SCOPE_SELF, 'label' => '仅本人数据', 'desc' => '仅可查看自己创建的数据'],
            ['value' => self::DATA_SCOPE_DEPT_AND_SELF, 'label' => '部门+本人', 'desc' => '可查看本部门、子部门及自己创建的数据'],
            ['value' => self::DATA_SCOPE_CUSTOM, 'label' => '自定义部门', 'desc' => '可查看指定部门的数据'],
        ];
    }
}
