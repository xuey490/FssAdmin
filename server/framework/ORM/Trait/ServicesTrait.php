<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-26
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\ORM\Trait;

/**
 * Trait ServicesTrait
 *
 * @package app\services
 * @method array<mixed>|Model|null get($id, ?array<mixed> $field = []) 获取一条数据
 * @method array<mixed>|Model|null getOne(array<mixed> $where, ?string $field = '*') 获取一条数据（不走搜素器）
 * @method string|null batchUpdate(array<mixed> $ids, array<mixed> $data, ?string $key = null) 批量修改
 * @method float sum(array<mixed> $where, string $field, bool $search = false) 求和
 * @method mixed update($id, array<mixed> $data, ?string $field = '') 修改数据
 * @method bool be($map, string $field = '') 查询一条数据是否存在
 * @method mixed value(array<mixed> $where, string $field) 获取指定条件下的数据
 * @method int count(array<mixed> $where = []) 读取数据条数
 * @method int getCount(array<mixed> $where = []) 获取某些条件总数（不走搜素器）
 * @method array<mixed> getColumn(array<mixed> $where, string $field, string $key = '') 获取某个字段数组（不走搜素器）
 * @method mixed delete($id, ?string $key = null) 删除
 * @method mixed destroy(array<mixed> $id, $force = false) 删除
 * @method mixed save(array<mixed> $data) 保存数据
 * @method mixed saveAll(array<mixed> $data) 批量保存数据
 * @method Model selectList(array<mixed> $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array<mixed> $with = [], bool $search = false) 获取列表
 * @method bool bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2) 高精度加法
 * @method bool bcDec($key, string $decField, string $dec, string $keyField = null, int $acc = 2) 高精度 减法
 * @method mixed decStockIncSales(array<mixed> $where, int $num, string $stock = 'stock', string $sales = 'sales') 减库存加销量
 * @method mixed incStockDecSales(array<mixed> $where, int $num, string $stock = 'stock', string $sales = 'sales') 加库存减销量
 */
trait ServicesTrait
{

}