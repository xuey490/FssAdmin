<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-10
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Traits;

use Framework\Utils\Tree;

/**
 * CRUD 数据格式化器 Trait
 * 
 * 该 Trait 提供数据输出格式化功能，用于统一 CRUD 操作的响应格式，
 * 支持普通列表、分页选择器、树形结构等多种数据格式输出。
 * 
 * @package Framework\Basic\Traits
 */
trait CrudFormatterTrait
{
    /**
     * 格式化选择器列表响应
     * 
     * 用于前端选择器组件的数据格式化，返回带分页信息的列表数据。
     * 响应格式：{ total: 总数, items: 数据列表 }
     * 
     * @param mixed $list 数据列表
     * @param int $total 数据总条数
     * @return \Framework\Utils\Json 格式化后的 JSON 响应对象
     */
    protected function formatSelect($list, int $total)
    {
        return $this->success([
            'total' => $total,
            'items' => $list,
        ], 'ok');
    }

    /**
     * 格式化普通列表响应
     * 
     * 用于常规列表数据的格式化输出。
     * 响应格式：{ total: 总数, list: 数据列表 }
     * 
     * @param mixed $list 数据列表
     * @param int $total 数据总条数
     * @return \Framework\Utils\Json 格式化后的 JSON 响应对象
     */
    protected function formatNormal($list, int $total)
    {
        return $this->success([
            'total' => $total,
            'list'  => $list,
        ], 'ok');
    }

    /**
     * 格式化树形结构响应
     * 
     * 将扁平数据转换为树形结构，用于前端树形选择器、菜单等场景。
     * 自动识别数据对象中的 title、name 或 id 字段作为节点名称。
     * 
     * @param iterable $items 待转换的数据项集合，每个项应包含 id 字段，可选 pid 字段
     * @return \Framework\Utils\Json 格式化后的 JSON 响应对象，包含树形结构数据
     */
    protected function formatTree($items)
    {
        $nodes = [];
        foreach ($items as $item) {
            $nodes[] = [
                'name'  => $item->title ?? $item->name ?? $item->id,
                'value' => (string) $item->id,
                'id'    => $item->id,
                'pid'   => $item->pid ?? 0,
            ];
        }
        $tree = new Tree($nodes);
        return $this->success($tree->getTree(), 'ok');
    }
}
