<?php

declare(strict_types=1);

/**
 * 系统用户服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysUser;
use App\Models\SysUserRole;
use App\Models\SysUserMenu;
use App\Models\SysUserPost;
use App\Models\SysUserTenant;
use App\Models\SysRole;
use App\Models\SysMenu;
use App\Models\SysPost;
use App\Dao\SysUserDao;
use App\Services\Casbin\CasbinService;
use Framework\Basic\BaseService;
use Framework\Tenant\TenantContext;

/**
 * SysUserService 用户服务
 *
 * 处理用户相关的业务逻辑
 */
class SysUserService extends BaseService
{
    /**
     * DAO 实例
     * @var SysUserDao
     */
    protected SysUserDao $userDao;

    /**
     * Casbin 服务
     * @var CasbinService
     */
    protected CasbinService $casbinService;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->userDao = new SysUserDao();
        $this->casbinService = new CasbinService();
    }

    // ==================== 用户认证 ====================

    /**
     * 用户登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $ip       登录 IP
     * @return array|null 成功返回用户信息和 token，失败返回 null
     */
    public function login(string $username, string $password, string $ip = '', int $tenantId = 0): ?array
    {
        // 查找用户
        $user = $this->userDao->findByUsername($username);

        if (!$user) {
            return null;
        }

        // 检查用户状态
        if ($user->status === SysUser::STATUS_DISABLED) {
            return null;
        }

        // 验证密码
        if (!password_verify($password, $user->password)) {
            return null;
        }

        // 更新最后登录信息
        $this->userDao->updateLoginInfo($user->id, $ip);

        // 同步用户角色到 Casbin
        $this->casbinService->syncUserRolesFromDatabase($user->id);

        // 生成 JWT Token（含 tenant_id）
        $token = $this->generateJwtToken($user, $tenantId);

        // 获取用户菜单
        $menus = $user->getMenuTree();
        $permissions = $user->getPermissions();

        return [
            'user' => $this->formatUser($user),
            'token' => $token,
            'menus' => $menus,
            'permissions' => $permissions,
        ];
    }

    /**
     * 生成 JWT Token
     *
     * @param SysUser $user 用户
     * @return string
     */
    protected function generateJwtToken(SysUser $user, int $tenantId = 0): string
    {
        $jwt = app('jwt');
        $roles = $user->getRoleCodes();
        $primaryRole = $roles[0] ?? 'user';

        $tokenData = $jwt->issue([
            'uid' => $user->id,
            'username' => $user->username,
            'role' => $primaryRole,
            'roles' => $roles,
            'tenant_id' => $tenantId,
        ]);

        return $tokenData['token'];
    }

    // ==================== 用户管理 ====================

    /**
     * 获取用户列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $keyword = $params['keyword'] ?? '';
        $username = $params['username'] ?? '';
        $phone = $params['phone'] ?? '';
        $status = $params['status'] ?? '';
        $deptId = $params['dept_id'] ?? '';
        $currentUserId = (int)($params['current_user_id'] ?? 0);

        // 获取当前租户ID
        $tenantId = \Framework\Tenant\TenantContext::getTenantId();

        $query = SysUser::query();

        // 不管是超级管理员还是普通管理员，都只能看到自己租户下的用户
        // 这样可以避免超级管理员误将自己的租户ID写入其他租户用户的菜单表
        if ($tenantId) {
            $tenantUserIds = \App\Models\SysUserTenant::where('tenant_id', $tenantId)
                ->pluck('user_id')
                ->toArray();
            
            if (empty($tenantUserIds)) {
                // 如果租户下没有用户，返回空列表
                return [
                    'list' => [],
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                ];
            }
            
            $query->whereIn('id', $tenantUserIds);
        }

        // keyword 同时模糊匹配 username / realname / phone
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('username', 'like', "%{$keyword}%")
                  ->orWhere('realname', 'like', "%{$keyword}%")
                  ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if ($username !== '') {
            $query->where('username', 'like', "%{$username}%");
        }

        if ($phone !== '') {
            $query->where('phone', 'like', "%{$phone}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        // 按部门过滤：从sa_system_user_dept表查询当前租户下该部门的用户
        if ($deptId !== '' && $tenantId) {
            $deptUserIds = \App\Models\SysUserDept::where('dept_id', (int)$deptId)
                ->where('tenant_id', $tenantId)
                ->pluck('user_id')
                ->toArray();
            
            if (empty($deptUserIds)) {
                // 如果该部门在当前租户下没有用户，返回空列表
                return [
                    'list' => [],
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit,
                ];
            }
            
            $query->whereIn('id', $deptUserIds);
        }

        $total = $query->count();
        // 优化：使用 Eloquent 标准的 skip/take 方法
        $list = $query->orderBy('id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->toArray();

        // 格式化数据并加载租户特定的部门信息
        foreach ($list as &$item) {
            $item = $this->formatUser($item);
            
            // 为每个用户加载租户特定的部门信息
            if ($tenantId) {
                $tenantDeptId = \App\Models\SysUserDept::getDeptIdByUserAndTenant($item['id'], $tenantId);
                if ($tenantDeptId !== null) {
                    $item['dept_id'] = $tenantDeptId;
                    $dept = \App\Models\SysDept::find($tenantDeptId);
                    if ($dept) {
                        $item['dept'] = $dept->toArray();
                    }
                } else {
                    // 用户在当前租户没有部门关联，设置为null
                    $item['dept_id'] = null;
                    $item['dept'] = null;
                }
            }
        }

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取用户详情
     *
     * @param int $userId 用户 ID
     * @return array|null
     */
    public function getDetail(int $userId): ?array
    {
        $user = SysUser::find($userId);

        if (!$user) {
            return null;
        }

        $data = $this->formatUser($user);

        // 获取租户特定的部门ID和部门对象
        $tenantId = \Framework\Tenant\TenantContext::getTenantId();
        if ($tenantId) {
            $tenantDeptId = \App\Models\SysUserDept::getDeptIdByUserAndTenant($userId, $tenantId);
            if ($tenantDeptId !== null) {
                $data['dept_id'] = $tenantDeptId;
                
                // 加载租户特定的部门对象
                $dept = \App\Models\SysDept::find($tenantDeptId);
                if ($dept) {
                    $data['dept'] = $dept->toArray();
                }
            } else {
                // 用户在当前租户没有部门关联，设置为null
                $data['dept_id'] = null;
                $data['dept'] = null;
            }
        }

        // 直接通过中间表获取用户角色（绕过 roles() 的 tenant_id 约束）
        $roleIds = SysUserRole::where('user_id', $userId)->pluck('role_id')->toArray();
        $data['role_ids'] = $roleIds;
        if (!empty($roleIds)) {
            $data['roleList'] = SysRole::whereIn('id', $roleIds)
                ->where('status', SysRole::STATUS_ENABLED)
                ->get()
                ->toArray();
        } else {
            $data['roleList'] = [];
        }

        // 直接通过中间表获取用户岗位（绕过 posts() 关联）
        $postIds = SysUserPost::where('user_id', $userId)->where('status', SysPost::ENABLED_ENABLED)->pluck('post_id')->toArray();
        $data['post_ids'] = $postIds;
        if (!empty($postIds)) {
            $data['postList'] = SysPost::whereIn('id', $postIds)
                ->where('status', SysPost::ENABLED_ENABLED)
                ->get()
                ->toArray();
        } else {
            $data['postList'] = [];
        }

        // 获取用户个人菜单
        $data['menu_ids'] = SysUserMenu::getMenuIdsByUserId($userId, $tenantId);

        return $data;
    }

    /**
     * 创建用户
     *
     * @param array $data     用户数据
     * @param int   $operator 操作人 ID
     * @return SysUser|null
     */
    public function create(array $data, int $operator = 0): ?SysUser
    {
        return $this->transaction(function () use ($data, $operator) {
            // 检查用户名是否存在
             
            if ($this->userDao->isUsernameExists($data['username'])) {
                throw new \Exception('用户名已存在');
            }

            // 检查手机号是否存在
            if (!empty($data['phone']) && $this->userDao->isMobileExists($data['phone'])) {
                throw new \Exception('手机号已存在');
            }

            // 设置审计字段
            $data['created_by'] = $operator;
            $data['updated_by'] = $operator;

            // 密码会自动通过模型 mutator 加密
            if (!isset($data['password'])) {
                $data['password'] = '123456'; // 默认密码
            }

            // 创建用户
            $user = SysUser::create($data);
            $tenantId = TenantContext::getTenantId() ?? 0;

            

            // 写入用户-租户关联表
            if ($tenantId > 0) {
                SysUserTenant::addUserToTenant($user->id, $tenantId, false, $operator);
            }
            

            // 分配角色
            if (!empty($data['role_ids'])) {
                SysUserRole::syncUserRolesByTenant($user->id, $tenantId, $data['role_ids'], $operator);
                
                // 将用户和角色相关信息同步到 casbin 表里
                $roles = \App\Models\SysRole::whereIn('id', $data['role_ids'])
                    ->where('status', \App\Models\SysRole::STATUS_ENABLED)
                    ->pluck('code')
                    ->toArray();
                
                foreach ($roles as $roleCode) {
                    $this->casbinService->addRoleForUser($user->id, $roleCode);
                }
            }

            // 分配岗位
            if (!empty($data['post_ids'])) {
                SysUserPost::saveUserPosts($user->id, $data['post_ids'], $tenantId, $operator);
            }

            // 分配个人菜单
            if (!empty($data['menu_ids'])) {
                SysUserMenu::syncUserMenus($user->id, $data['menu_ids'], $tenantId, $operator);
            }

            // 同步用户部门关联
            if (!empty($data['dept_id']) && $tenantId > 0) {
                \App\Models\SysUserDept::syncUserDept($user->id, $tenantId, $data['dept_id'], $operator);
            }

            return $user;
        });
    }

    /**
     * 更新用户
     *
     * @param int   $userId   用户 ID
     * @param array $data     用户数据
     * @param int   $operator 操作人 ID
     * @return bool
     */
    public function update(int $userId, array $data, int $operator = 0): bool
    {
        return $this->transaction(function () use ($userId, $data, $operator) {

            //app('cache')->set('update_user_' . $operator, $data);

            $user = SysUser::find($userId);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            // 检查用户名是否重复
            if (isset($data['username']) && $data['username'] !== $user->username) {
                if ($this->userDao->isUsernameExists($data['username'], $userId)) {
                    throw new \Exception('用户名已存在');
                }
            }

            // 检查手机号是否重复
            if (isset($data['phone']) && $data['phone'] !== $user->phone) {
                if ($this->userDao->isMobileExists($data['phone'], $userId)) {
                    throw new \Exception('手机号已存在');
                }
            }

            // 设置审计字段
            $data['updated_by'] = $operator;

            // 如果修改密码，密码会自动通过模型 mutator 加密
            if (isset($data['password']) && !empty($data['password'])) {
                // 模型自动处理，无需手动加密
            } else {
                unset($data['password']);
            }

            // 更新用户
            $user->fill($data);
            $user->save();
            $tenantId = TenantContext::getTenantId() ?? 0;

            // 更新角色
            if (isset($data['role_ids'])) {
                SysUserRole::syncUserRolesByTenant($userId, $tenantId, $data['role_ids'], $operator);
                // 同步 Casbin
                $this->casbinService->syncUserRolesFromDatabase($userId);
            }

            // 更新岗位
            if (isset($data['post_ids'])) {
                SysUserPost::saveUserPosts($userId, $data['post_ids'], $tenantId);
            }

            // 更新个人菜单
            if (isset($data['menu_ids'])) {
                SysUserMenu::syncUserMenus($userId, $data['menu_ids'], $tenantId, $operator);
            }

            // 同步用户部门关联
            if (isset($data['dept_id']) && $tenantId > 0) {
                \App\Models\SysUserDept::syncUserDept($userId, $tenantId, $data['dept_id'], $operator);
            }

            return true;
        });
    }

    /**
     * 删除用户
     *
     * @param int $userId 用户 ID
     * @return bool
     */
    public function delete(int $userId): bool
    {
        $user = SysUser::find($userId);
        if (!$user) {
            return false;
        }

        // 软删除用户
        $user->delete();

        // 删除用户角色关联
        SysUserRole::deleteByUserId($userId);

        // 删除用户岗位关联
        \App\Models\SysUserPost::where('user_id', $userId)->delete();

        // 删除用户菜单关联（所有租户）
        SysUserMenu::deleteByUserId($userId);

        // 删除用户租户关联
        \App\Models\SysUserTenant::where('user_id', $userId)->delete();

        // 删除用户部门关联
        \App\Models\SysUserDept::where('user_id', $userId)->delete();

        // 清除 Casbin 角色
        $this->casbinService->deleteRolesForUser($userId);

        return true;
    }

    /**
     * 更新用户状态
     *
     * @param int $userId 用户 ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $userId, int $status): bool
    {
        return $this->userDao->updateStatus($userId, $status);
    }

    /**
     * 重置密码
     *
     * @param int    $userId   用户 ID
     * @param string $password 新密码
     * @return bool
     */
    public function resetPassword(int $userId, string $password = '123456'): bool
    {
        $user = SysUser::find($userId);
        if (!$user) {
            return false;
        }

        $user->password = $password;
        return $user->save();
    }

    /**
     * 修改密码
     *
     * @param int    $userId      用户 ID
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     * @return bool
     * @throws \Exception
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        $user = SysUser::find($userId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }

        // 验证旧密码
        if (!$user->verifyPassword($oldPassword)) {
            throw new \Exception('旧密码错误');
        }

        $user->password = $newPassword;
        return $user->save();
    }

    /**
     * 清理用户缓存
     *
     * 清除与用户相关的所有缓存数据
     *
     * @param int $userId 用户 ID
     * @return bool
     */
    public function clearCache(int $userId): bool
    {
        // 清理用户权限缓存
        $redis = app('redis');
        if ($redis) {
            $redis->del("user:permissions:{$userId}");
            $redis->del("user:menus:{$userId}");
            $redis->del("user:roles:{$userId}");
        }

        return true;
    }

    /**
     * 获取用户已分配的菜单ID列表
     *
     * @param int $userId 用户 ID
     * @return array
     */
    public function getUserMenuIds(int $userId): array
    {
        $tenantId = \Framework\Tenant\TenantContext::getTenantId();
        return SysUserMenu::getMenuIdsByUserId($userId, $tenantId);
    }

    /**
     * 保存用户菜单分配（先清理再写入，并清除用户缓存）
     *
     * @param int   $userId   用户 ID
     * @param array $menuIds  菜单 ID 数组
     * @param int   $operator 操作人 ID
     * @return bool
     */
    public function saveUserMenus(int $userId, array $menuIds, int $operator = 0): bool
    {
        $user = SysUser::find($userId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }

        // 补全所有父级菜单ID，确保父子联动完整性
        $menuIds = SysMenu::expandWithParentIds($menuIds);

        // 先清理旧记录，再写入新记录
        $tenantId = \Framework\Tenant\TenantContext::getTenantId() ?? 0;
        SysUserMenu::syncUserMenus($userId, $menuIds, $tenantId, $operator);

        // 同步用户菜单权限到 Casbin
        $this->casbinService->syncUserMenuPermissions($userId);

        // 清除用户菜单/权限缓存，使变更立即生效
        $redis = app('redis');
        if ($redis) {
            $redis->del("user:permissions:{$userId}");
            $redis->del("user:menus:{$userId}");
            $redis->del("user:roles:{$userId}");
        }

        return true;
    }

    /**
     * 设置用户首页/工作台
     *
     * @param int    $userId    用户 ID
     * @param string $dashboard 工作台标识
     * @return bool
     * @throws \Exception
     */
    public function setHomePage(int $userId, string $dashboard): bool
    {
        $user = SysUser::find($userId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }

        $user->dashboard = $dashboard;
        return $user->save();
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化用户数据
     *
     * @param SysUser|array $user 用户
     * @return array
     */
    protected function formatUser(SysUser|array $user): array
    {
        if ($user instanceof SysUser) {
            $data = $user->toArray();
        } else {
            $data = $user;
        }

        // 移除敏感字段
        unset($data['password']);

        // 格式化时间
        if (isset($data['create_time'])) {
            $data['create_time'] = is_string($data['create_time'])
                ? $data['create_time']
                : $data['create_time']?->format('Y-m-d H:i:s');
        }

        if (isset($data['update_time'])) {
            $data['update_time'] = is_string($data['update_time'])
                ? $data['update_time']
                : $data['update_time']?->format('Y-m-d H:i:s');
        }

        // 状态文本
        $data['status_text'] = $data['status'] === SysUser::STATUS_ENABLED ? '启用' : '禁用';

        // 数据库值映射到字典值：DB 1=启用 0=禁用 → 字典 1=正常 2=停用
        if (isset($data['status'])) {
         //   $data['status'] = $data['status'] === 0 ? 2 : 1;
        }

        return $data;
    }
}