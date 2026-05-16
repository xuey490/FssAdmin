<?php

declare(strict_types=1);

/**
 * 系统租户服务
 *
 * @package App\Services
 */

namespace App\Services;

use App\Dao\SysTenantDao;
use App\Models\SysDept;
use App\Models\SysMenu;
use App\Models\SysRole;
use App\Models\SysTenant;
use App\Models\SysUser;
use App\Models\SysUserTenant;
use Framework\Basic\BaseService;
use Symfony\Component\HttpFoundation\Request;

class SysTenantService extends BaseService
{
    protected SysTenantDao $tenantDao;

    public function __construct()
    {
        parent::__construct();
        $this->tenantDao = new SysTenantDao();
    }

    /**
     * 租户分页列表
     * , Request $request
     */
    public function getList(array $params ): array
    {
        // 获取当前用户和租户信息（从全局 Request 容器）

        $request = \Framework\Tenant\TenantContext::getCurrentRequest();
  
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, (int)($params['limit'] ?? 20));

        $tenantName = trim((string)($params['tenant_name'] ?? $params['name'] ?? ''));
        $tenantCode = trim((string)($params['tenant_code'] ?? $params['code'] ?? ''));
        $status = $params['status'] ?? '';

        //$query = SysTenant::query();

        $user = $request ? $request->attributes->get('current_user') : null;
        $currentTenantId = $request ? $request->attributes->get('user')['tenant_id'] ?? null : null;

        // 根据用户权限决定是否应用租户隔离
        if ($user && $user->isSuperAdmin()) {
            // 超级管理员：查询所有租户（不过滤）
            $query = SysTenant::withoutTenancy();
        } else {
            // 普通用户：只能查询自己的租户
            $query = SysTenant::withoutTenancy();
            if ($currentTenantId) {
                $query->where('id', $currentTenantId);
            } else {
                // 如果没有租户ID，返回空结果
                return [
                    'list' => [],
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                ];
            }
        }        

        if ($tenantName !== '') {
            $query->where('tenant_name', 'like', "%{$tenantName}%");
        }

        if ($tenantCode !== '') {
            $query->where('tenant_code', 'like', "%{$tenantCode}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $total = $query->count();

        $rows = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $tenantIds = $rows->pluck('id')->toArray();
        $userCountMap = [];
        if (!empty($tenantIds)) {
            // 使用 withoutTenancy() 统计各租户的用户数，不受当前租户限制
            $userCountMap = SysUserTenant::withoutTenancy()
                ->whereIn('tenant_id', $tenantIds)
                ->selectRaw('tenant_id, count(*) as c')
                ->groupBy('tenant_id')
                ->pluck('c', 'tenant_id')
                ->toArray();
        }

        $list = $rows->map(function (SysTenant $tenant) use ($userCountMap) {
            $item = $this->formatTenant($tenant);
            $item['user_count'] = (int)($userCountMap[$tenant->id] ?? 0);
            return $item;
        })->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 租户详情
     */
    public function getDetail(int $tenantId): ?array
    {
        // 使用 withoutTenancy() 允许超级管理员查看任意租户详情
        $tenant = SysTenant::withoutTenancy()->find($tenantId);
        if (!$tenant) {
            return null;
        }

        $data = $this->formatTenant($tenant);
        // 使用 withoutTenancy() 统计指定租户的用户数
        $data['user_count'] = SysUserTenant::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->count();
        return $data;
    }

    /**
     * 创建租户
     */
    public function create(array $data, int $operator = 0): ?SysTenant
    {
        return $this->transaction(function () use ($data, $operator) {
            if ($this->tenantDao->isTenantNameExists($data['tenant_name'])) {
                throw new \Exception('租户名称已存在');
            }

            if ($this->tenantDao->isTenantCodeExists($data['tenant_code'])) {
                throw new \Exception('租户编码已存在');
            }

            $data['created_by'] = $operator;
            $data['updated_by'] = $operator;

            return SysTenant::create($data);
        });
    }

    /**
     * 更新租户
     */
    public function update(int $tenantId, array $data, int $operator = 0): bool
    {
        return $this->transaction(function () use ($tenantId, $data, $operator) {
            // 使用 withoutTenancy() 允许超级管理员更新任意租户
            $tenant = SysTenant::withoutTenancy()->find($tenantId);
            if (!$tenant) {
                throw new \Exception('租户不存在');
            }

            if (isset($data['tenant_name']) && $data['tenant_name'] !== $tenant->tenant_name) {
                if ($this->tenantDao->isTenantNameExists($data['tenant_name'], $tenantId)) {
                    throw new \Exception('租户名称已存在');
                }
            }

            if (isset($data['tenant_code']) && $data['tenant_code'] !== $tenant->tenant_code) {
                if ($this->tenantDao->isTenantCodeExists($data['tenant_code'], $tenantId)) {
                    throw new \Exception('租户编码已存在');
                }
            }

            $data['updated_by'] = $operator;
            $tenant->fill($data);
            return $tenant->save();
        });
    }

    /**
     * 删除租户
     */
    public function delete(int $tenantId): bool
    {
        // 使用 withoutTenancy() 允许超级管理员删除任意租户
        $tenant = SysTenant::withoutTenancy()->find($tenantId);
        if (!$tenant) {
            return false;
        }

        // 使用 withoutTenancy() 检查指定租户的用户数
        $userCount = SysUserTenant::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->count();
        if ($userCount > 0) {
            throw new \Exception('租户下存在关联用户，无法删除');
        }

        $deptCount = SysDept::where('tenant_id', $tenantId)->count();
        if ($deptCount > 0) {
            throw new \Exception('租户下存在部门数据，无法删除');
        }

        $roleCount = SysRole::where('tenant_id', $tenantId)->count();
        if ($roleCount > 0) {
            throw new \Exception('租户下存在角色数据，无法删除');
        }

        // 菜单为全局共享资源，不再检查租户菜单数据

        return $tenant->delete();
    }

    /**
     * 更新租户状态
     */
    public function updateStatus(int $tenantId, int $status): bool
    {
        return $this->tenantDao->updateStatus($tenantId, $status);
    }

    /**
     * 查询租户关联用户
     */
    public function getTenantUsers(int $tenantId, array $params): array
    {
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, (int)($params['limit'] ?? 20));
        $username = trim((string)($params['username'] ?? ''));
        $realname = trim((string)($params['realname'] ?? ''));
        $phone = trim((string)($params['phone'] ?? ''));

        // 使用 withoutTenancy() 忽略租户隔离，因为我们要查询指定租户的用户
        // 而不是当前上下文租户的用户
        $query = SysUserTenant::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->with(['user' => function ($q) {
                $q->select('id', 'username', 'realname', 'phone', 'email', 'status');
            }]);

        if ($username !== '' || $realname !== '' || $phone !== '') {
            $query->whereHas('user', function ($q) use ($username, $realname, $phone) {
                if ($username !== '') {
                    $q->where('username', 'like', "%{$username}%");
                }
                if ($realname !== '') {
                    $q->where('realname', 'like', "%{$realname}%");
                }
                if ($phone !== '') {
                    $q->where('phone', 'like', "%{$phone}%");
                }
            });
        }

        $total = $query->count();
        $rows = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $list = $rows->map(function (SysUserTenant $row) {
            $user = $row->user;
            return [
                'id' => (int)$row->id,
                'tenant_id' => (int)$row->tenant_id,
                'user_id' => (int)$row->user_id,
                'is_default' => (int)$row->is_default,
                'is_super' => (int)($row->is_super ?? 0),
                'join_time' => $this->dateToString($row->join_time),
                'username' => $user?->username ?? '',
                'realname' => $user?->realname ?? '',
                'phone' => $user?->phone ?? '',
                'email' => $user?->email ?? '',
                'status' => (int)($user?->status ?? 0),
            ];
        })->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 查询可添加到租户的用户（排除已关联用户）
     */
    public function getAvailableUsers(int $tenantId, array $params): array
    {
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, (int)($params['limit'] ?? 20));
        $username = trim((string)($params['username'] ?? ''));
        $realname = trim((string)($params['realname'] ?? ''));
        $phone = trim((string)($params['phone'] ?? ''));

        // 使用 withoutTenancy() 忽略租户隔离，获取指定租户的已关联用户
        $usedUserIds = SysUserTenant::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->pluck('user_id')
            ->toArray();

        $query = SysUser::query();
        if (!empty($usedUserIds)) {
            $query->whereNotIn('id', $usedUserIds);
        }

        if ($username !== '') {
            $query->where('username', 'like', "%{$username}%");
        }
        if ($realname !== '') {
            $query->where('realname', 'like', "%{$realname}%");
        }
        if ($phone !== '') {
            $query->where('phone', 'like', "%{$phone}%");
        }

        $total = $query->count();

        $rows = $query->select('id', 'username', 'realname', 'phone', 'email', 'status')
            ->orderBy('id', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'list' => $rows,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 批量添加用户到租户
     */
    public function addUsers(int $tenantId, array $userIds, int $operator = 0): int
    {
        // 使用 withoutTenancy() 允许超级管理员向任意租户添加用户
        $tenant = SysTenant::withoutTenancy()->find($tenantId);
        if (!$tenant) {
            throw new \Exception('租户不存在');
        }

        if (empty($userIds)) {
            return 0;
        }

        $userIds = array_values(array_unique(array_map('intval', $userIds)));

        return $this->transaction(function () use ($tenant, $tenantId, $userIds, $operator) {
            // 使用 withoutTenancy() 统计和检查指定租户的用户数
            $currentCount = SysUserTenant::withoutTenancy()
                ->where('tenant_id', $tenantId)
                ->count();
            $alreadyCount = SysUserTenant::withoutTenancy()
                ->where('tenant_id', $tenantId)
                ->whereIn('user_id', $userIds)
                ->count();
            $needAddCount = count($userIds) - $alreadyCount;

            if ($needAddCount <= 0) {
                return 0;
            }

            if ((int)$tenant->max_users > 0 && ($currentCount + $needAddCount) > (int)$tenant->max_users) {
                throw new \Exception('超出租户最大用户数限制');
            }

            return SysUserTenant::batchAddUsers($tenantId, $userIds, $operator);
        });
    }

    /**
     * 从租户移除用户
     */
    public function removeUser(int $tenantId, int $userId): bool
    {
        // 使用 withoutTenancy() 允许超级管理员从任意租户移除用户
        $tenant = SysTenant::withoutTenancy()->find($tenantId);
        if (!$tenant) {
            throw new \Exception('租户不存在');
        }

        return SysUserTenant::removeUserFromTenant($userId, $tenantId);
    }

    /**
     * 设置租户管理员
     */
    public function setTenantAdmin(int $tenantId, int $userId, int $isSuper): bool
    {
        // 使用 withoutTenancy() 允许超级管理员操作任意租户
        $tenant = SysTenant::withoutTenancy()->find($tenantId);
        if (!$tenant) {
            throw new \Exception('租户不存在');
        }

        // 使用 withoutTenancy() 移除租户隔离，允许超级管理员操作其他租户的用户
        $userTenant = SysUserTenant::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        if (!$userTenant) {
            throw new \Exception('用户不在该租户中');
        }

        return $userTenant->update(['is_super' => $isSuper]);
    }

    /**
     * 设为默认租户
     */
    public function setDefaultTenant(int $tenantId, int $userId, int $isDefault): bool
    {
        // 使用 withoutTenancy() 允许超级管理员操作任意租户
        $tenant = SysTenant::withoutTenancy()->find($tenantId);
        if (!$tenant) {
            throw new \Exception('租户不存在');
        }

        // 使用 withoutTenancy() 移除租户隔离，允许超级管理员操作其他租户的用户
        $userTenant = SysUserTenant::withoutTenancy()
            ->where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        if (!$userTenant) {
            throw new \Exception('用户不在该租户中');
        }

        // 如果是设为默认（isDefault = 1），需要先取消其他租户的默认标识
        if ($isDefault === 1) {
            // 同样需要移除租户隔离，才能更新其他租户的记录
            SysUserTenant::withoutTenancy()
                ->where('user_id', $userId)
                ->where('tenant_id', '!=', $tenantId)
                ->update(['is_default' => 0]);
        }

        // 更新当前租户的默认标识
        return $userTenant->update(['is_default' => $isDefault]);
    }

    protected function formatTenant(SysTenant|array $tenant): array
    {
        $data = $tenant instanceof SysTenant ? $tenant->toArray() : $tenant;

        if (isset($data['expire_time'])) {
            $data['expire_time'] = $this->dateToString($data['expire_time']);
        }
        if (isset($data['create_time'])) {
            $data['create_time'] = $this->dateToString($data['create_time']);
        }
        if (isset($data['update_time'])) {
            $data['update_time'] = $this->dateToString($data['update_time']);
        }

        $data['status_text'] = (int)($data['status'] ?? 0) === 1 ? '启用' : '禁用';

        return $data;
    }

    protected function dateToString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string)$value;
    }
}
