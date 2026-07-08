<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\ORM\Trait;

use think\Model;

trait ModelTrait
{
    /**
     * 时间段搜索器.
     *
     * @param Model $query
     
     * @param mixed $value

     * @param mixed $data
*/
    public function searchTimeAttr($query, $value, $data): void
    {
        if ($value) {
            $timeKey = $data['timeKey'] ?? 'create_time';
            if (is_array($value)) {
                $startTime = $value[0] ?? 0;
                $endTime   = $value[1] ?? 0;
                if ($startTime || $endTime) {
                    if ($startTime == $endTime || $endTime == strtotime(date('Y-m-d', $endTime))) {
                        $endTime = $endTime + 86400;
                    }
                    $query->whereBetween($timeKey, [$startTime, $endTime]);
                }
            } elseif (is_string($value)) {
                switch ($value) {
                    case 'today':
                    case 'week':
                    case 'month':
                    case 'year':
                    case 'yesterday':
                    case 'last year':
                    case 'last week':
                    case 'last month':
                        $query->whereTime($timeKey, $value);
                        break;
                    case 'quarter':
                        [$startTime, $endTime] = $this->getMonth();
                        $query->whereBetween($timeKey, [strtotime($startTime), strtotime($endTime)]);
                        break;
                    case 'lately7':
                        $query->whereBetween($timeKey, [strtotime('-7 day'), time()]);
                        break;
                    case 'lately30':
                        $query->whereBetween($timeKey, [strtotime('-30 day'), time()]);
                        break;
                    default:
                        if (strstr($value, '-') !== false) {
                            [$startTime, $endTime] = explode('-', $value);
                            $startTime             = trim($startTime) ? strtotime($startTime) : 0;
                            $endTime               = trim($endTime) ? strtotime($endTime) : 0;
                            if ($startTime && $endTime) {
                                if ($startTime == $endTime || $endTime == strtotime(date('Y-m-d', $endTime))) {
                                    $endTime = $endTime + 86400;
                                }
                                $query->whereBetween($timeKey, [$startTime, $endTime]);
                            } elseif (! $startTime && $endTime) {
                                $query->whereTime($timeKey, '<', $endTime + 86400);
                            } elseif ($startTime) {
                                $query->whereTime($timeKey, '>=', $startTime);
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * 获取本季度 time.
     *
     * @return array<mixed> */
    public function getMonth(int $ceil = 0)
    {
        if ($ceil != 0) {
            $season = ceil(date('n') / 3) - $ceil;
        } else {
            $season = ceil(date('n') / 3);
        }
        $firstday = date('Y-m-01', mktime(0, 0, 0, (int) (($season - 1) * 3 + 1), 1, (int) date('Y')));
        $lastday  = date('Y-m-t', mktime(0, 0, 0, (int) ($season * 3), 1, (int) date('Y')));
        return [$firstday, $lastday];
    }

    /**
     * 获取某个字段内的值
     *
     * @param array<mixed>|string[] $where
     *
     * @return mixed
     
     * @param mixed $value
*/
    public function getFieldValue($value, string $filed, ?string $valueKey = '', ?array $where = [])
    {
        $model = $this->where($filed, $value);
        if ($where) {
            $model->where(...$where);
        }
        return $model->value($valueKey ?: $filed);
    }
}
