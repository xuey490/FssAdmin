<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SysUser;
use App\Models\SysTenant;
use App\Models\SysUserTenant;
use App\Models\SysUserRole;
use App\Models\SysLoginLog;
use App\Services\SysUserService;
use App\Services\Casbin\CasbinService;
use App\Services\LoginLogService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Framework\Tenant\TenantContext;
use Framework\Tenant\JwtTenantContext;
use Framework\Tenant\SessionTenantContext;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends BaseController
{
    protected SysUserService $userService;
    protected array $jwtConfig;
    protected CasbinService $casbinService;
    protected LoginLogService $loginLogService;

    protected function initialize(): void
    {
        $this->userService     = new SysUserService();
        $this->jwtConfig       = config('jwt', []);
        $this->casbinService   = new CasbinService();
        $this->loginLogService = new LoginLogService();
    }

    #[Route(path: '/api/core/login', methods: ['POST'], name: 'auth.login')]
    public function login(Request $request): BaseJsonResponse|JsonResponse
    {
        try{
            $jsonBody = [];
            $content  = $request->getContent();
            if (!empty($content)) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $jsonBody = $decoded;
                }
            }
            $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

            $username = $all['username'] ?? '';
            $password = $all['password'] ?? '';
            $code     = $all['code'] ?? '';
            $uuid     = $all['uuid'] ?? '';
            $remember = $all['rememberPassword'] ?? false;
            $tenantId = $all['tenant_id'] ?? null;

            if (empty($username) || empty($password)) {
                return $this->fail('用户名和密码不能为空');
            }

            if (empty($tenantId)) {
                return $this->fail('租户ID不能为空');
            }

            if (empty($code) || empty($uuid)) {
                return $this->fail('验证码不能为空');
            }

            // TODO: enable captcha check after cache/session service is ready
            // $cachedCode = $this->getCaptchaCode($uuid);
            // if (strtolower($cachedCode) !== strtolower($code)) {
            //     $this->recordLoginLog($request, $username, false, 'captcha error');
            //     return $this->fail('captcha error');
            // }

            $user = SysUser::where('username', $username)->first();
            if (!$user || !$user->verifyPassword($password)) {
                $this->recordLoginLog($request, $username, false, '用户名或密码错误');
                return $this->fail('用户名或密码错误', 400);
            }

            if ($user->isDisabled()) {
                $this->recordLoginLog($request, $username, false, '账号已被禁用');
                return $this->fail('账号已被禁用', 403);
            }

            if ($tenantId) {
                $hasTenant = SysUserTenant::isUserInTenant($user->id, (int)$tenantId);
                if (!$hasTenant && !$user->isSuperAdmin()) {
                    $this->recordLoginLog($request, $username, false, '您不属于该租户');
                    return $this->fail('您不属于该租户', 403);
                }
                $tenant = SysTenant::withoutTenancy()->find($tenantId);
                if (!$tenant || !$tenant->isValid()) {
                    $this->recordLoginLog($request, $username, false, '租户无效或已过期');
                    return $this->fail('租户无效或已过期', 403);
                }
            } else {
                $tenantId = SysUserTenant::getDefaultTenantId($user->id);
                if (!$tenantId && !$user->isSuperAdmin()) {
                    $this->recordLoginLog($request, $username, false, '请先选择租户');
                    return $this->fail('请先选择租户', 403);
                }
            }

            $ttl = $remember ? 604800 : ($this->jwtConfig['ttl'] ?? 3600);
            $tokenRole = $this->resolveTokenRoleClaim($user, (int) $tenantId);
            $tokens = JwtTenantContext::generateLoginTokens([
                'uid'       => $user->id,
                'name'      => $user->username,
                'nickname'  => $user->realname,
                'tenant_id' => (int)$tenantId,
                'role'      => $tokenRole,
                'roles'     => is_array($tokenRole) ? $tokenRole : [$tokenRole],
            ], $ttl);


            
            $tenants = SysUserTenant::getTenantsByUser($user->id);
            
            SessionTenantContext::setTenantSession((int)$tenantId, $user->id, $tenants);

            $this->casbinService->syncUserRolesFromDatabase($user->id);
            $user->updateLoginInfo($request->getClientIp() ?? '');

            $this->recordLoginLog($request, $username, true, '登录成功');

            TenantContext::setTenantIdToRequest($request, (int)$tenantId > 0 ? (int)$tenantId : null);
            TenantContext::setTenantId((int)$tenantId > 0 ? (int)$tenantId : null);

            

            $menus       = $user->getMenuTree();
            $permissions = $user->getPermissions();

            $response = $this->success([
                'user' => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->realname,
                    'avatar'   => $user->avatar,
                    'is_admin' => $user->isSuperAdmin(),
                ],
                'access_token'  => $tokens['token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in'    => $tokens['ttl'],
                'tenant_id'     => (int)$tenantId,
                'menus'         => $menus,
                'permissions'   => $permissions,
            ], '登录成功');

            $this->setAuthCookies($response, $tokens['token'], $tokens['refresh_token'], $request->isSecure());

            return $response;
        } catch (\Exception $e) {
            return $this->fail('error:' . $e->getMessage());
        }             
    }

    #[Route(path: '/api/core/tenants-by-username', methods: ['GET'], name: 'auth.tenantsByUsername')]
    public function getTenantsByUsername(Request $request): BaseJsonResponse
    {
        $username = $request->query->get('username', '');

        if (empty($username)) {
            return $this->fail('用户名不能为空');
        }

        $user = SysUser::where('username', $username)->first();
        if (!$user) {
            return $this->success([]);
        }

        $tenants = SysUserTenant::getTenantsByUser($user->id);

        $validTenants = array_filter($tenants, function ($tenant) {
            $tenantModel = SysTenant::withoutTenancy()->find($tenant['id']);
            return $tenantModel && $tenantModel->isValid();
        });

        return $this->success(array_values($validTenants));
    }

    #[Route(path: '/api/core/user-tenants', methods: ['GET'], name: 'auth.userTenants')]
    public function getUserTenants(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        if (!$userId) {
            return $this->fail('未登录', 401);
        }
        $tenants = SysUserTenant::getTenantsByUser($userId);
        return $this->success($tenants);
    }

    #[Route(path: '/api/core/switch-tenant', methods: ['POST'], name: 'auth.switchTenant')]
    public function switchTenant(Request $request): BaseJsonResponse
    {
        try{
            $jsonBody = [];
            $content  = $request->getContent();
            if (!empty($content)) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    $jsonBody = $decoded;
                }
            }
            $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

            $userId      = $this->getCurrentUserId($request);
            $newTenantId = (int)($all['tenant_id'] ?? 0);

            if (!$userId) {
                return $this->fail('未登录', 401);
            }
            if ($newTenantId <= 0) {
                return $this->fail('租户ID不能为空', 400);
            }

            if (!SysUserTenant::isUserInTenant($userId, $newTenantId)) {
                return $this->fail('您不属于该租户', 403);
            }

            $tenant = SysTenant::withoutTenancy()->find($newTenantId);
            if (!$tenant || !$tenant->isValid()) {
                return $this->fail('租户无效或已过期', 403);
            }

            $user = SysUser::find($userId);
            if (!$user) {
                return $this->fail('用户不存在', 404);
            }

            SysUserTenant::setDefaultTenant($userId, $newTenantId);

            $tokenRole = $this->resolveTokenRoleClaim($user, $newTenantId);
            $tokens = JwtTenantContext::generateLoginTokens([
                'uid'       => $user->id,
                'name'      => $user->username,
                'nickname'  => $user->realname,
                'tenant_id' => $newTenantId,
                'role'      => $tokenRole,
                'roles'     => is_array($tokenRole) ? $tokenRole : [$tokenRole],
            ]);

            if ($request->hasSession()) {
                SessionTenantContext::switchTenant($newTenantId);
            }

            TenantContext::setTenantId($newTenantId);
            $menus       = $user->getMenuTree();
            $permissions = $user->getPermissions();

            $response = $this->success([
                'access_token'  => $tokens['token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_in'    => $tokens['ttl'],
                'tenant_id'     => $newTenantId,
                'tenant_name'   => $tenant->tenant_name,
                'menus'         => $menus,
                'permissions'   => $permissions,
            ], '切换成功');

            $this->setAuthCookies($response, $tokens['token'], $tokens['refresh_token'], $request->isSecure());

            return $response;
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }     
    }

    #[Route(path: '/api/core/refresh', methods: ['POST'], name: 'auth.refresh')]
    public function refresh(Request $request): BaseJsonResponse
    {
        $refreshToken = $request->cookies->get('refresh_token');

        // 检查 refresh_token 是否存在
        if (!$refreshToken) {
            return $this->fail('Refresh token 不存在，请重新登录', 401);
        }

        try {
            $newRefreshToken = JwtTenantContext::rotateRefreshToken($refreshToken);
            $userId          = JwtTenantContext::validateRefreshToken($newRefreshToken);

            $user = SysUser::find($userId);
            if (!$user) {
                return $this->fail('用户不存在', 404);
            }

            $tenantId = SessionTenantContext::getTenantId()
                ?? SysUserTenant::getDefaultTenantId($userId)
                ?? 0;

            $tokenRole = $this->resolveTokenRoleClaim($user, (int) $tenantId);
            $tokens = JwtTenantContext::generateLoginTokens([
                'uid'       => $user->id,
                'name'      => $user->username,
                'nickname'  => $user->realname,
                'tenant_id' => $tenantId,
                'role'      => $tokenRole,
                'roles'     => is_array($tokenRole) ? $tokenRole : [$tokenRole],
            ]); 

            $response = $this->success([
                'access_token'  => $tokens['token'],
                'refresh_token' => $newRefreshToken,
                'expires_in'    => $tokens['ttl'],
            ]);

            $this->setAuthCookies($response, $tokens['token'], $newRefreshToken, $request->isSecure());

            return $response;
        } catch (\Throwable $e) {
            return $this->fail('Token 刷新失败: ' . $e->getMessage(), 401);
        }
    }

    #[Route(path: '/api/core/logout', methods: ['POST'], name: 'auth.logout')]
    public function logout(Request $request): BaseJsonResponse
    {
        $token        = $request->cookies->get('access_token');
        $refreshToken = $request->cookies->get('refresh_token');

        if ($token) {
            try {
                JwtTenantContext::revokeToken($token);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($request->hasSession()) {
            SessionTenantContext::clearTenantSession();
        }

        $response = $this->success([], '登出成功');
        $this->clearAuthCookies($response, $request->isSecure());

        return $response;
    }

    #[Route(path: '/api/core/system/user', methods: ['GET'], name: 'auth.me')]
    public function me(Request $request): BaseJsonResponse
    {
        $userId   = $this->getCurrentUserId($request);
        $tenantId = TenantContext::getTenantId();

        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        $user = SysUser::find($userId);
        if (!$user) {
            return $this->fail('用户不存在', 404);
        }

        if (!$tenantId) {
            $tenantId = SysUserTenant::getDefaultTenantId($userId);
        }

        $tenant = $tenantId ? SysTenant::find($tenantId) : null;


        $roles = [];
        if ($tenantId) {
            $roles = SysUserRole::getRoleCodesByTenant($userId, $tenantId);
        }

        $user->load(['posts', 'tenants','roles','menus']);

        
        // 获取当前租户下的部门信息
        $department = null;
        if ($tenantId) {
            $tenantDeptId = \App\Models\SysUserDept::getDeptIdByUserAndTenant($userId, $tenantId);
            if ($tenantDeptId) {
                $dept = \App\Models\SysDept::find($tenantDeptId);
                if ($dept) {
                    $department = [
                        'id'   => $dept->id,
                        'name' => $dept->name,
                    ];
                }
            }
        }

        return $this->success([
            'id'         => $user->id,
            'username'   => $user->username,
            'nickname'   => $user->realname,
            'realname'   => $user->realname,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'avatar'     => $user->avatar,
            'gender'     => $user->gender,
            'signed'     => $user->signed,
            'remark'     => $user->remark,
            'login_time' => $user->login_time,
            'login_ip'   => $user->login_ip,
            'is_admin'   => $user->isSuperAdmin(),
            'buttons'    => $user->isSuperAdmin() ? ['*'] : $user->getPermissions(),
            'roles'      => $roles,
            'department' => $department,
            'posts'      => $user->posts->map(fn($p) => [
                'id'   => $p->id,
                'name' => $p->name,
            ])->values()->all(),
            'tenant'     => $tenant ? [
                'id'   => $tenant->id,
                'name' => $tenant->tenant_name,
                'code' => $tenant->tenant_code,
            ] : null,
        ]);
    }

    #[Route(path: '/api/core/system/menu', methods: ['GET'], name: 'auth.menus')]
    public function menus(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        if (!$userId) {
            return $this->fail('未登录', 401);
        }
        $sysUser = SysUser::find($userId);
        if (!$sysUser) {
            return $this->fail('用户不存在', 404);
        }
        return $this->success($sysUser->getMenuTree());
    }

    #[Route(path: '/api/core/system/permissions', methods: ['GET'], name: 'auth.permissions')]
    public function permissions(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        if (!$userId) {
            return $this->fail('未登录', 401);
        }
        $sysUser = SysUser::find($userId);
        if (!$sysUser) {
            return $this->fail('用户不存在', 404);
        }
        return $this->success($sysUser->getPermissions());
    }

    #[Route(path: '/api/core/user/modifyPassword', methods: ['POST'], name: 'auth.changePassword')]
    public function changePassword(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        $oldPassword = $this->input('oldPassword', '' ,true , $request);
        $newPassword = $this->input('newPassword', '' ,true , $request);

        if (empty($oldPassword) || empty($newPassword)) {
            return $this->fail('旧密码和新密码不能为空');
        }
        if (strlen($newPassword) < 6) {
            return $this->fail('新密码长度不能少于6位');
        }

        try {
            $this->userService->changePassword($userId, $oldPassword, $newPassword);
            return $this->success([], '密码修改成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/user/updateInfo', methods: ['POST'], name: 'auth.updateInfo')]
    public function updateInfo(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        $body = array_merge(
            $request->request->all(),
            (array) json_decode($request->getContent(), true)
        );

        $data = array_filter([
            'realname' => $body['realname'] ?? null,
            'email'    => $body['email'] ?? null,
            'phone'    => $body['phone'] ?? null,
            'gender'   => $body['gender'] ?? null,
            'signed'   => $body['signed'] ?? null,
            'avatar'   => $body['avatar'] ?? null,
        ], fn($v) => $v !== null);



        try {
            $this->userService->update($userId, $data, $userId);
            $user = SysUser::find($userId);
            return $this->success([
                'id'       => $user->id,
                'username' => $user->username,
                'realname' => $user->realname,
                'nickname' => $user->realname,
                'email'    => $user->email,
                'phone'    => $user->phone,
                'gender'   => $user->gender,
                'signed'   => $user->signed,
                'avatar'   => $user->avatar,
            ], '资料修改成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== helpers ====================

    protected function getCurrentUserId(Request $request): ?int
    {
        $userId = $request->attributes->get('user')['id'] ?? null;
        if ($userId) {
            return (int)$userId;
        }

        $authHeader = $request->headers->get('Authorization');
        if ($authHeader) {
            $token = JwtTenantContext::extractTokenFromHeader($authHeader);
            if ($token) {
                return JwtTenantContext::getUserIdFromToken($token);
            }
        }

        $token = $request->cookies->get('access_token');
        if ($token) {
            return JwtTenantContext::getUserIdFromToken($token);
        }

        if ($request->hasSession()) {
            return SessionTenantContext::getUserId();
        }

        return null;
    }

    protected function setAuthCookies(
        BaseJsonResponse $response,
        string $accessToken,
        string $refreshToken,
        bool $isSecure
    ): void {
        $sameSite = $isSecure ? 'Strict' : 'Lax';
        $response->headers->setCookie(new Cookie('access_token', $accessToken, time() + 3600, '/', null, $isSecure, true, false, $sameSite));
        $response->headers->setCookie(new Cookie('refresh_token', $refreshToken, time() + 86400 * 7, '/', null, $isSecure, true, false, $sameSite));
    }

    protected function clearAuthCookies(BaseJsonResponse $response, bool $isSecure): void
    {
        $sameSite = $isSecure ? 'Strict' : 'Lax';
        $response->headers->setCookie(new Cookie('access_token', '', time() - 3600, '/', null, $isSecure, true, false, $sameSite));
        $response->headers->setCookie(new Cookie('refresh_token', '', time() - 3600, '/', null, $isSecure, true, false, $sameSite));
    }

    /**
     * 生成写入 token 的角色 claim（按当前租户取角色，空则回退 user）
     */
    protected function resolveTokenRoleClaim(SysUser $user, int $tenantId): array|string
    {
        if ($user->isSuperAdmin()) {
            return ['super_admin', 'admin'];
        }

        if ($tenantId <= 0) {
            return 'user';
        }

        $roles = SysUserRole::getRoleCodesByTenant((int) $user->id, $tenantId);
        $roles = array_values(array_unique(array_filter($roles, fn($role) => is_string($role) && $role !== '')));

        return $roles !== [] ? $roles : 'user';
    }

    /**
     * 记录登录日志（成功或失败）
     */
    protected function recordLoginLog(Request $request, string $username, bool $success, string $message = ''): void
    {
        try {
            $userAgent = $request->headers->get('User-Agent', '');
            $ip        = $request->getClientIp() ?? '';

            $browser = 'Other';
            foreach (['Edge', 'Chrome', 'Firefox', 'Safari', 'MSIE', 'Trident'] as $b) {
                if (stripos($userAgent, $b) !== false) {
                    $browser = $b === 'Trident' ? 'IE' : $b;
                    break;
                }
            }
            $os = 'Other';
            foreach (['Windows', 'Mac', 'Linux', 'Android', 'iOS'] as $o) {
                if (stripos($userAgent, $o) !== false) {
                    $os = $o;
                    break;
                }
            }

            $this->loginLogService->record([
                'username'    => $username,
                'ip'          => $ip,
                'ip_location' => '',
                'os'          => $os,
                'browser'     => $browser,
                'status'      => $success ? SysLoginLog::STATUS_SUCCESS : SysLoginLog::STATUS_FAIL,
                'message'     => $message,
            ]);
        } catch (\Throwable $e) {
            // log write failure must not affect main flow
        }
    }
}
