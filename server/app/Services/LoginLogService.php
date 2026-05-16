<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SysLoginLog;
use Framework\Basic\BaseService;

class LoginLogService extends BaseService
{
    /**
     * 分页查询登录日志
     * 前端参数：username, ip, status, login_time(数组[start,end]), orderField, orderType, page, pageSize
     */
    public function getPageList(array $params): array
    {
        $page     = max(1, (int)($params['page'] ?? 1));
        $pageSize = max(1, (int)($params['limit'] ?? 20));

        $query = SysLoginLog::query();

        if (!empty($params['username'])) {
            $query->where('username', 'like', '%' . $params['username'] . '%');
        }
        if (!empty($params['ip'])) {
            $query->where('ip', 'like', '%' . $params['ip'] . '%');
        }
        // 兼容 status 和 login_status 两种参数名
        $statusVal = $params['status'] ?? $params['login_status'] ?? null;
        if (isset($statusVal) && $statusVal !== '' && $statusVal !== null) {
            $query->where('status', (int)$statusVal);
        }
        // 兼容 login_time 数组 和 start_time/end_time 两种传参方式
        if (!empty($params['login_time']) && is_array($params['login_time'])) {
            if (!empty($params['login_time'][0])) {
                $query->where('login_time', '>=', $params['login_time'][0]);
            }
            if (!empty($params['login_time'][1])) {
                $query->where('login_time', '<=', $params['login_time'][1]);
            }
        } else {
            if (!empty($params['start_time'])) {
                $query->where('login_time', '>=', $params['start_time']);
            }
            if (!empty($params['end_time'])) {
                $query->where('login_time', '<=', $params['end_time']);
            }
        }

        $orderField = in_array($params['orderField'] ?? '', ['login_time', 'create_time', 'id'])
            ? $params['orderField'] : 'login_time';
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
        return SysLoginLog::whereIn('id', $ids)->delete();
    }

    /**
     * 记录登录日志
     */
    public function record(array $data): SysLoginLog
    {
        return SysLoginLog::record($data);
    }
}
