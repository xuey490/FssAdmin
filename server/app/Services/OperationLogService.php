<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SysOperationLog;
use Framework\Basic\BaseService;

class OperationLogService extends BaseService
{
    /**
     * 分页查询操作日志
     * 前端参数：username, ip, service_name, router, create_time(数组[start,end]), orderField, orderType, page, pageSize
     */
    public function getPageList(array $params): array
    {
        $page     = max(1, (int)($params['page'] ?? 1));
        $pageSize = max(1, (int)($params['limit'] ?? 20));

        $query = SysOperationLog::query();

        if (!empty($params['username'])) {
            $query->where('username', 'like', '%' . $params['username'] . '%');
        }
        if (!empty($params['ip'])) {
            $query->where('ip', 'like', '%' . $params['ip'] . '%');
        }
        if (!empty($params['service_name'])) {
            $query->where('service_name', 'like', '%' . $params['service_name'] . '%');
        }
        if (!empty($params['router'])) {
            $query->where('router', 'like', '%' . $params['router'] . '%');
        }
        // create_time 是 [start, end] 数组
        if (!empty($params['create_time']) && is_array($params['create_time'])) {
            if (!empty($params['create_time'][0])) {
                $query->where('create_time', '>=', $params['create_time'][0]);
            }
            if (!empty($params['create_time'][1])) {
                $query->where('create_time', '<=', $params['create_time'][1]);
            }
        }

        $orderField = in_array($params['orderField'] ?? '', ['create_time', 'id'])
            ? $params['orderField'] : 'create_time';
        $orderType  = strtolower($params['orderType'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $total = $query->count();
        $list  = $query->orderBy($orderField, $orderType)
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();

        $size = $pageSize;
        return [
            'data'         => $list,
            'list'         => $list,
            'total'        => $total,
            'page'         => $page,
            'current_page' => $page,
            'size'         => $size,
            'per_page'     => $size,
        ];
    }

    /**
     * 批量删除
     */
    public function delete(array $ids): int
    {
        return SysOperationLog::whereIn('id', $ids)->delete();
    }

    /**
     * 记录操作日志
     */
    public function record(array $data): SysOperationLog
    {
        return SysOperationLog::record($data);
    }
}
