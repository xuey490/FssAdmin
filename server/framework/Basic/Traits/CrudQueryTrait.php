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

use Framework\Utils\DateTime;
use Symfony\Component\HttpFoundation\Request;

/**
 * CRUD 查询构建器 Trait
 * 
 * 该 Trait 提供 CRUD 查询参数解析和条件构建功能，
 * 支持多种查询操作符（IN、LIKE、比较、BETWEEN 等），
 * 用于简化控制器层的查询逻辑。
 * 
 * @package Framework\Basic\Traits
 */
trait CrudQueryTrait
{
    /**
     * 支持的查询前缀列表
     * 
     * @var array<string>
     */
    protected array $prefixes = [
        'IN_', 'LIKE_', 'EQ_', 'GT_', 'LT_', 'GTE_', 'LTE_', 'NE_', 'BETWEEN_',
    ];

    /**
     * 解析查询请求参数
     * 
     * 从 HTTP 请求中提取并验证查询参数，包括分页、排序、字段选择等。
     * 同时构建 WHERE 查询条件数组。
     * 
     * @param Request $request HTTP 请求对象
     * @return array 返回包含查询参数的数组：[$where, $format, $limit, $field, $order, $page]
     *               - $where: 查询条件数组
     *               - $format: 输出格式（normal/select/tree）
     *               - $limit: 每页条数
     *               - $field: 查询字段
     *               - $order: 排序规则
     *               - $page: 当前页码
     */
    protected function selectInput(Request $request): array
    {
        $params = $request->query->all() + $request->request->all();

        $field  = $params['field'] ?? '*';
        $sort   = $params['order'] ?? 'id desc';
        $format = $params['format'] ?? 'normal';
        $limit  = max(1, (int) ($params['limit'] ?? 10));
        $page   = max(1, (int) ($params['page'] ?? 1));

        $model = $this->service->getModel();
        $allow = $model->getFields();

        // 排序处理
        $order        = '';
        [$col, $rank] = array_pad(explode(' ', $sort), 2, 'desc');
        if (in_array($col, $allow)) {
            $order = "$col $rank";
        }

        // 字段验证
        if ($field !== '*' && ! in_array($field, $allow)) {
            $field = '*';
        }

        $where = $this->buildWhereConditions($params, $allow);

        return [$where, $format, $limit, $field, $order, $page];
    }

    /**
     * 构建查询条件数组
     * 
     * 根据参数前缀自动识别查询操作符，生成标准的 WHERE 条件数组。
     * 支持的前缀：
     * - IN_: IN 查询（值需为数组）
     * - LIKE_: 模糊查询（自动添加百分号）
     * - EQ_: 等于查询
     * - GT_: 大于查询
     * - LT_: 小于查询
     * - GTE_: 大于等于查询
     * - LTE_: 小于等于查询
     * - NE_: 不等于查询
     * - BETWEEN_: 区间查询（值需为包含两个元素的数组，用于时间范围查询）
     * 
     * @param array $params 请求参数数组
     * @param array<string> $allowColumns 允许查询的字段列表
     * @return array 查询条件数组，每个元素为 [$column, $operator, $value] 格式
     */
    protected function buildWhereConditions(array $params, array $allowColumns): array
    {
        $where = [];

        foreach ($params as $column => $value) {
            $prefix       = '';
            $actualColumn = $column;

            if (preg_match('/^([A-Z_]+)_(.*)$/', $column, $m)) {
                $prefix       = rtrim($m[1], '_');
                $actualColumn = strtolower($m[2]);
            }

            if (! in_array($actualColumn, $allowColumns)) {
                continue;
            }

            switch ($prefix) {
                case 'IN':
                    $where[] = [$actualColumn, 'IN', (array) $value];
                    break;

                case 'LIKE':
                    $where[] = [$actualColumn, 'LIKE', "%{$value}%"];
                    break;

                case 'GT':
                    $where[] = [$actualColumn, '>', $value];
                    break;

                case 'LT':
                    $where[] = [$actualColumn, '<', $value];
                    break;

                case 'GTE':
                    $where[] = [$actualColumn, '>=', $value];
                    break;

                case 'LTE':
                    $where[] = [$actualColumn, '<=', $value];
                    break;

                case 'NE':
                    $where[] = [$actualColumn, '!=', $value];
                    break;

                case 'BETWEEN':
                    if (is_array($value) && count($value) === 2) {
                        $where[] = [$actualColumn, '>=', DateTime::dateTimeStringToTimestamp($value[0])];
                        $where[] = [$actualColumn, '<=', DateTime::dateTimeStringToTimestamp($value[1])];
                    }
                    break;

                default:
                    $where[] = [$actualColumn, '=', $value];
            }
        }

        return $where;
    }
}
