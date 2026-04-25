<?php

declare(strict_types=1);

/**
 * 租户管理控制器
 *
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Services\SysTenantService;
use Framework\Attributes\Auth;
use Framework\Attributes\Route;
use Framework\Attributes\Permission;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TenantController extends BaseController
{
    protected SysTenantService $tenantService;

    /**
     * 初始化租户服务。
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->tenantService = new SysTenantService();
    }

    /**
     * 获取租户分页列表。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/list', methods: ['GET'], name: 'tenant.list')]
    #[Auth(required: true)]
    #[Permission('core:tenant:index')]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int)$this->input('page', 1, true, $request),
            'limit' => (int)$this->input('limit', 20, true, $request),
            'tenant_name' => $this->input('tenant_name', '', true, $request),
            'tenant_code' => $this->input('tenant_code', '', true, $request),
            'status' => $this->input('status', '', true, $request),
        ];

        $result = $this->tenantService->getList($params);
        return $this->success($result);
    }

    /**
     * 获取租户详情。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/detail/{id}', methods: ['GET'], name: 'tenant.detail')]
    #[Auth(required: true)]
    #[Permission('core:tenant:read')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $result = $this->tenantService->getDetail($id);

        if (!$result) {
            return $this->fail('租户不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建租户。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/create', methods: ['POST'], name: 'tenant.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:save')]
    public function create(Request $request): BaseJsonResponse
    {
        $body = $this->getJsonBody($request);
        $data = [
            'tenant_name' => trim((string)($body['tenant_name'] ?? '')),
            'tenant_code' => trim((string)($body['tenant_code'] ?? '')),
            'contact_name' => $this->normalizeNullableString($body['contact_name'] ?? null),
            'contact_phone' => $this->normalizeNullableString($body['contact_phone'] ?? null),
            'contact_email' => $this->normalizeNullableString($body['contact_email'] ?? null),
            'address' => $this->normalizeNullableString($body['address'] ?? null),
            'logo_url' => $this->normalizeNullableString($body['logo_url'] ?? null),
            'status' => (int)($body['status'] ?? 1),
            'expire_time' => $this->normalizeDateTimeInput($body['expire_time'] ?? null),
            'max_users' => (int)($body['max_users'] ?? 0),
            'max_depts' => (int)($body['max_depts'] ?? 0),
            'max_roles' => (int)($body['max_roles'] ?? 0),
            'remark' => $this->normalizeNullableString($body['remark'] ?? null),
        ];

        if ($data['tenant_name'] === '') {
            return $this->fail('租户名称不能为空');
        }

        if ($data['tenant_code'] === '') {
            return $this->fail('租户编码不能为空');
        }

        try {
            $tenant = $this->tenantService->create($data, $this->getOperatorId($request));
            return $this->success(['id' => $tenant?->id ?? 0], '创建成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新租户。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/update/{id}', methods: ['PUT'], name: 'tenant.update')]
    ##[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:update')]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $body = $this->getJsonBody($request);

        $hasExpireTime = array_key_exists('expire_time', $body);

        $hasTenantName = array_key_exists('tenant_name', $body);
        $hasTenantCode = array_key_exists('tenant_code', $body);
        $hasContactName = array_key_exists('contact_name', $body);
        $hasContactPhone = array_key_exists('contact_phone', $body);
        $hasContactEmail = array_key_exists('contact_email', $body);
        $hasAddress = array_key_exists('address', $body);
        $hasLogoUrl = array_key_exists('logo_url', $body);
        $hasRemark = array_key_exists('remark', $body);

        $data = [
            'tenant_name' => $hasTenantName ? trim((string)$body['tenant_name']) : null,
            'tenant_code' => $hasTenantCode ? trim((string)$body['tenant_code']) : null,
            'contact_name' => $hasContactName ? $this->normalizeNullableString($body['contact_name']) : null,
            'contact_phone' => $hasContactPhone ? $this->normalizeNullableString($body['contact_phone']) : null,
            'contact_email' => $hasContactEmail ? $this->normalizeNullableString($body['contact_email']) : null,
            'address' => $hasAddress ? $this->normalizeNullableString($body['address']) : null,
            'logo_url' => $hasLogoUrl ? $this->normalizeNullableString($body['logo_url']) : null,
            'status' => isset($body['status']) ? (int)$body['status'] : null,
            'expire_time' => $hasExpireTime ? $this->normalizeDateTimeInput($body['expire_time']) : null,
            'max_users' => isset($body['max_users']) ? (int)$body['max_users'] : null,
            'max_depts' => isset($body['max_depts']) ? (int)$body['max_depts'] : null,
            'max_roles' => isset($body['max_roles']) ? (int)$body['max_roles'] : null,
            'remark' => $hasRemark ? $this->normalizeNullableString($body['remark']) : null,
        ];

        $data = array_filter($data, fn($v) => $v !== null);

        if ($hasExpireTime) {
            $data['expire_time'] = $this->normalizeDateTimeInput($body['expire_time']);
        }

        if ($hasTenantName && ($data['tenant_name'] ?? '') === '') {
            return $this->fail('租户名称不能为空');
        }

        if ($hasTenantCode && ($data['tenant_code'] ?? '') === '') {
            return $this->fail('租户编码不能为空');
        }

        try {
            $this->tenantService->update($id, $data, $this->getOperatorId($request));
            return $this->success([], '更新成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除租户。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/delete/{id}', methods: ['DELETE'], name: 'tenant.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:destroy')]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        try {
            $this->tenantService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新租户启用状态。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/status/{id}', methods: ['PUT'], name: 'tenant.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:update')]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $body = $this->getJsonBody($request);
        $status = (int)($body['status'] ?? 1);

        $result = $this->tenantService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取租户下已关联用户列表。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/users/{tenantId}', methods: ['GET'], name: 'tenant.users')]
    ##[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:read')]
    public function users(Request $request): BaseJsonResponse
    {
        $tenantId = (int)$request->attributes->get('tenantId');
        $params = [
            'page' => (int)$this->input('page', 1, true, $request),
            'limit' => (int)$this->input('limit', 20, true, $request),
            'username' => $this->input('username', '', true, $request),
            'realname' => $this->input('realname', '', true, $request),
            'phone' => $this->input('phone', '', true, $request),
        ];

        $result = $this->tenantService->getTenantUsers($tenantId, $params);
        return $this->success($result);
    }

    /**
     * 获取租户下可关联用户列表。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/available-users/{tenantId}', methods: ['GET'], name: 'tenant.availableUsers')]
    ##[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:read')]
    public function availableUsers(Request $request): BaseJsonResponse
    {
        $tenantId = (int)$request->attributes->get('tenantId');
        $params = [
            'page' => (int)$this->input('page', 1, true, $request),
            'limit' => (int)$this->input('limit', 20, true, $request),
            'username' => $this->input('username', '', true, $request),
            'realname' => $this->input('realname', '', true, $request),
            'phone' => $this->input('phone', '', true, $request),
        ];

        $result = $this->tenantService->getAvailableUsers($tenantId, $params);
        return $this->success($result);
    }

    /**
     * 批量向租户添加用户。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/add-users/{tenantId}', methods: ['POST'], name: 'tenant.addUsers')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:update')]
    public function addUsers(Request $request): BaseJsonResponse
    {
        $tenantId = (int)$request->attributes->get('tenantId');
        $body = $this->getJsonBody($request);
        $userIds = is_array($body['user_ids'] ?? null) ? $body['user_ids'] : [];

        if (empty($userIds)) {
            return $this->fail('请选择要添加的用户');
        }

        try {
            $count = $this->tenantService->addUsers($tenantId, $userIds, $this->getOperatorId($request));
            return $this->success(['count' => $count], '添加成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 从租户中移除指定用户。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/remove-user/{tenantId}/{userId}', methods: ['DELETE'], name: 'tenant.removeUser')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:update')]
    public function removeUser(Request $request): BaseJsonResponse
    {
        $tenantId = (int)$request->attributes->get('tenantId');
        $userId = (int)$request->attributes->get('userId');

        try {
            $result = $this->tenantService->removeUser($tenantId, $userId);
            return $result
                ? $this->success([], '移除成功')
                : $this->fail('移除失败');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 设置租户管理员。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/set-admin/{tenantId}/{userId}', methods: ['PUT'], name: 'tenant.setAdmin')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:update')]
    public function setTenantAdmin(Request $request): BaseJsonResponse
    {
        $tenantId = (int)$request->attributes->get('tenantId');
        $userId = (int)$request->attributes->get('userId');
        $body = $this->getJsonBody($request);
        $isSuper = (int)($body['is_super'] ?? 0);

        try {
            $result = $this->tenantService->setTenantAdmin($tenantId, $userId, $isSuper);
            return $result
                ? $this->success([], $isSuper === 1 ? '设为租户管理员成功' : '取消租户管理员成功')
                : $this->fail('操作失败');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 设为默认租户。
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/tenant/set-default/{tenantId}/{userId}', methods: ['PUT'], name: 'tenant.setDefault')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:tenant:update')]
    public function setDefaultTenant(Request $request): BaseJsonResponse
    {
        $tenantId = (int)$request->attributes->get('tenantId');
        $userId = (int)$request->attributes->get('userId');
        $body = $this->getJsonBody($request);
        $isDefault = (int)($body['is_default'] ?? 1);

        try {
            $result = $this->tenantService->setDefaultTenant($tenantId, $userId, $isDefault);
            return $result
                ? $this->success([], $isDefault === 1 ? '设为默认租户成功' : '取消默认租户成功')
                : $this->fail('操作失败');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取当前操作人ID。
     *
     * @param Request $request 请求对象
     * @return int
     */
    protected function getOperatorId(Request $request): int
    {
        $user = $request->attributes->get('user');
        return $user['id'] ?? 0;
    }

    /**
     * 规范化可空字符串：空字符串转为 null。
     *
     * @param mixed $value 原始值
     * @return string|null
     */
    protected function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string)$value);
        return $str === '' ? null : $str;
    }

    /**
     * 规范化日期时间入参：空值返回 null。
     *
     * @param mixed $value 原始值
     * @return string|null
     */
    protected function normalizeDateTimeInput(mixed $value): ?string
    {
        return $this->normalizeNullableString($value);
    }
}
