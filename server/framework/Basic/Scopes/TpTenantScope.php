<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-11
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Scopes;

use think\db\Query;
use think\Model;
use Framework\Tenant\TenantContext;

/**
 * ThinkPHP 模型租户隔离作用域
 *
 * 为 ThinkPHP 模型提供自动的租户隔离功能。当模型使用此作用域时，
 * 所有查询操作都会自动添加 tenant_id 条件，确保多租户环境下的数据隔离。
 *
 * 主要特性：
 * - 自动检测超管模式并跳过租户隔离
 * - 检查模型表是否包含 tenant_id 字段
 * - 避免重复添加 where 条件
 *
 * 使用方式：
 * 在模型中使用：
 * protected $globalScope = ['tenant'];
 *
 * 或者在模型中手动调用：
 * public static function init()
 * {
 *     parent::init();
 *     self::addGlobalScope('tenant', new TpTenantScope());
 * }
 *
 * @package Framework\Basic\Scopes
 */
class TpTenantScope
{
    /**
     * 应用租户隔离作用域
     *
     * 为查询添加 tenant_id 条件，实现多租户数据隔离。
     * 在以下情况下不会添加租户限制：
     * 1. 处于超管模式（忽略租户隔离状态）
     * 2. 无法获取当前租户ID
     * 3. 模型表不包含 tenant_id 字段
     * 4. 已经存在相同的 where 条件（避免重复）
     *
     * @param Query $query ThinkPHP 查询构建器
     * @param Model $model 模型实例
     * @return void
     */
    public function apply(Query $query, Model $model): void
    {
        // 超管模式忽略租户隔离
        if (TenantContext::isIgnoring()) {
            return;
        }

        $tenantId = TenantContext::getTenantId();
        if (!$tenantId) {
            return;
        }

        $fields = $model->getFields();
        if (!is_array($fields) || !array_key_exists('tenant_id', $fields)) {
            return;
        }

        $fieldKey = $model->getTable() . '.tenant_id';

        // 避免重复添加
        $options = $query->getOptions();
        $hasWhere = false;
        if (!empty($options['where'])) {
            foreach ($options['where'] as $where) {
                if (isset($where[0]) && $where[0] === $fieldKey) {
                    $hasWhere = true;
                    break;
                }
            }
        }

        if (!$hasWhere) {
            $query->where($fieldKey, '=', $tenantId);
        }
    }
}
