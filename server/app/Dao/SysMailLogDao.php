<?php

declare(strict_types=1);

namespace App\Dao;

use App\Models\SysMailLog;
use Framework\Basic\BaseDao;

class SysMailLogDao extends BaseDao
{
    protected function setModel(): string
    {
        return SysMailLog::class;
    }

    public function getPageList(array $params): array
    {
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, (int)($params['limit'] ?? 10));

        $query = SysMailLog::query();

        if (!empty($params['from'])) {
            $query->where('from', 'like', '%' . $params['from'] . '%');
        }
        if (!empty($params['email'])) {
            $query->where('email', 'like', '%' . $params['email'] . '%');
        }
        if (isset($params['status']) && $params['status'] !== '' && $params['status'] !== null) {
            $query->where('status', (string)$params['status']);
        }
        if (!empty($params['create_time']) && is_array($params['create_time'])) {
            if (!empty($params['create_time'][0])) {
                $query->where('create_time', '>=', $params['create_time'][0]);
            }
            if (!empty($params['create_time'][1])) {
                $query->where('create_time', '<=', $params['create_time'][1]);
            }
        }

        $orderField = in_array($params['orderField'] ?? '', ['id', 'create_time', 'update_time'], true)
            ? $params['orderField'] : 'create_time';
        $orderType = strtolower((string)($params['orderType'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $total = $query->count();
        $list = $query->orderBy($orderField, $orderType)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'data' => $list,
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'current_page' => $page,
            'size' => $limit,
            'per_page' => $limit,
        ];
    }

    public function deleteByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return SysMailLog::whereIn('id', $ids)->delete();
    }
}
