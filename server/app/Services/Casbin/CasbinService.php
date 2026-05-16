<?php

declare(strict_types=1);

/**
 * Casbin 服务
 *
 * @package App\Services\Casbin
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services\Casbin;

use Casbin\Enforcer;
use Casbin\Model\Model;
use App\Models\SysUser;
use App\Models\SysRole;
use App\Models\SysMenu;
use App\Models\SysUserRole;
use App\Models\SysRoleMenu;
use App\Models\SysUserMenu;


/**
 * CasbinService Casbin 权限服务
 *
 * 提供权限验证、策略管理等功能
 */
class CasbinService
{
    /**
     * Enforcer 实例
     * @var Enforcer|null
     */
    protected ?Enforcer $enforcer = null;

    /**
     * 配置
     * @var array
     */
    protected array $config;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->config = config('casbin', []);
    }

    /**
     * 获取 Enforcer 实例
     *
     * @return Enforcer
     */
    public function getEnforcer(): Enforcer
    {
        if ($this->enforcer === null) {
            $this->enforcer = $this->createEnforcer();
        }

        return $this->enforcer;
    }

    /**
     * 创建 Enforcer 实例
     *
     * @return Enforcer
     */
    protected function createEnforcer(): Enforcer
    {
        // 创建模型
        $model = new Model();

        // 加载模型配置
        $modelPath = $this->config['model']['path'] ?? config_path('casbin_rbac_model.conf');

        if (file_exists($modelPath)) {
            $model->loadModel($modelPath);
        } elseif (!empty($this->config['model']['content'])) {
            $model->loadModelFromText($this->config['model']['content']);
        } else {
            // 使用默认 RBAC 模型
            $model->loadModelFromText($this->getDefaultModelText());
        }

        // 创建适配器
        $tableName = $this->config['adapter']['table_name'] ?? 'casbin_rule';
        $connection = $this->config['adapter']['connection'] ?? null;
        $adapter = new DatabaseAdapter($tableName, $connection);

        // 创建 Enforcer
        return new Enforcer($model, $adapter);
    }

    /**
     * 获取默认 RBAC 模型文本
     *
     * @return string
     */
    protected function getDefaultModelText(): string
    {
        return <<<'EOT'
[request_definition]
r = sub, obj, act

[policy_definition]
p = sub, obj, act

[role_definition]
g = _, _
g2 = _, _

[policy_effect]
e = some(where (p.eft == allow))

[matchers]
m = g(r.sub, p.sub) && (keyMatch2(r.obj, p.obj) || keyMatch(r.obj, p.obj)) && (r.act == p.act || p.act == "*")
EOT;
    }

    // ==================== 权限验证 ====================

    /**
     * 检查用户是否有权限
     *
     * @param int|string $user    用户ID或角色编码
     * @param string     $resource 资源 (如: /api/user/list)
     * @param string     $action   操作 (如: GET, POST, *)
     * @return bool
     */
    public function checkPermission(int|string $user, string $resource, string $action = '*'): bool
    {
        // 检查缓存
        $cacheKey = $this->getCacheKey('permission', $user, $resource, $action);
        
        if ($this->isCacheEnabled()) {
            $cached = app('cache')->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        // 执行权限检查
        $result = $this->getEnforcer()->enforce((string)$user, $resource, $action);

        // 缓存结果
        if ($this->isCacheEnabled()) {
            app('cache')->set($cacheKey, $result, $this->getCacheTtl());
        }

        return $result;
    }

    /**
     * 检查用户是否超级管理员
     *
     * @param int $userId 用户ID
     * @return bool
     */
    public function isSuperAdmin(int $userId): bool
    {
        $user = SysUser::find($userId);
        return $user && $user->isSuperAdmin();
    }

    /**
     * 获取用户的所有角色
     *
     * @param int $userId 用户ID
     * @return array
     */
    public function getRolesForUser(int $userId): array
    {
        return $this->getEnforcer()->getRolesForUser((string)$userId);
    }

    /**
     * 获取角色的所有权限
     *
     * @param string $role 角色编码
     * @return array
     */
    public function getPermissionsForRole(string $role): array
    {
        return $this->getEnforcer()->getPermissionsForUser($role);
    }

    // ==================== 角色管理 ====================

    /**
     * 添加角色给用户
     *
     * @param int    $userId   用户ID
     * @param string $roleCode 角色编码
     * @return bool
     */
    public function addRoleForUser(int $userId, string $roleCode): bool
    {
        $result = $this->getEnforcer()->addRoleForUser((string)$userId, $roleCode);
        $this->clearUserCache($userId);

        return $result;
    }

    /**
     * 删除用户的角色
     *
     * @param int    $userId   用户ID
     * @param string $roleCode 角色编码
     * @return bool
     */
    public function deleteRoleForUser(int $userId, string $roleCode): bool
    {
        $result = $this->getEnforcer()->deleteRoleForUser((string)$userId, $roleCode);
        $this->clearUserCache($userId);

        return $result;
    }

    /**
     * 删除用户的所有角色
     *
     * @param int $userId 用户ID
     * @return bool
     */
    public function deleteRolesForUser(int $userId): bool
    {
        // Casbin enforcer::deleteRolesForUser 内部传了空字符串作为 v1 条件，
        // 导致 WHERE v0=userId AND v1='' 匹配不到任何记录。
        // 直接操作 adapter 只按 ptype='g' AND v0=userId 删除，再全量 reload 同步内存。
        $adapter = $this->getEnforcer()->getAdapter();
        if ($adapter instanceof \App\Services\Casbin\DatabaseAdapter) {
            $adapter->removeFilteredPolicy('g', 'g', 0, (string)$userId);
            // 重新从 DB 加载策略，同步内存状态
            $this->getEnforcer()->loadPolicy();
        } else {
            $this->getEnforcer()->deleteRolesForUser((string)$userId);
        }

        $this->clearUserCache($userId);

        return true;
    }

    /**
     * 同步用户角色 (从数据库)
     *
     * @param int $userId 用户ID
     * @return void
     */
    public function syncUserRolesFromDatabase(int $userId): void
    {
        // 先清除 Casbin 中的用户角色
        $this->deleteRolesForUser($userId);

        // 从数据库获取用户角色
        $roles = SysUserRole::where('user_id', $userId)
            ->join((new SysRole)->getTable(), (new SysUserRole)->getTable() . '.role_id', '=', (new SysRole)->getTable() . '.id')
            ->where((new SysRole)->getTable() . '.status', SysRole::STATUS_ENABLED)
            ->pluck((new SysRole)->getTable() . '.code')
            ->toArray();

        // 添加到 Casbin
        foreach ($roles as $roleCode) {
            $this->addRoleForUser($userId, $roleCode);
        }

        $this->clearUserCache($userId);
    }

    // ==================== 权限策略管理 ====================

    /**
     * 添加权限策略
     *
     * @param string $role     角色编码
     * @param string $resource 资源
     * @param string $action   操作
     * @return bool
     */
    public function addPermission(string $role, string $resource, string $action = '*'): bool
    {
        return $this->getEnforcer()->addPolicy($role, $resource, $action);
    }

    /**
     * 删除权限策略
     *
     * @param string $role     角色编码
     * @param string $resource 资源
     * @param string $action   操作
     * @return bool
     */
    public function deletePermission(string $role, string $resource, string $action = '*'): bool
    {
        return $this->getEnforcer()->deletePolicy($role, $resource, $action);
    }

    /**
     * 删除角色的所有权限
     *
     * @param string $role 角色编码
     * @return bool
     */
    public function deletePermissionsForRole(string $role): bool
    {
        $adapter = $this->getEnforcer()->getAdapter();
        if ($adapter instanceof DatabaseAdapter) {
            $adapter->removeFilteredPolicy('p', 'p', 0, $role);
            $this->getEnforcer()->loadPolicy();
            return true;
        }

        $this->getEnforcer()->deletePermissionsForUser($role);
        return true;
    }

    /**
     * 同步角色菜单权限 (从数据库)
     *
     * @param int $roleId 角色ID
     * @return void
     */
    public function syncRoleMenuPermissions(int $roleId): void
    {
        $role = SysRole::find($roleId);
        if (!$role) {
            return;
        }

        try {
            // 直接通过 adapter 删除该角色在 casbin_rule 中的所有 p 策略
            // 不使用 deletePermissionsForRole（Enforcer 内置方法），避免内存与持久化不一致
            $adapter = $this->getEnforcer()->getAdapter();
            if ($adapter instanceof DatabaseAdapter) {
                $adapter->removeFilteredPolicy('p', 'p', 0, $role->code);
            } else {
                $this->getEnforcer()->deletePermissionsForUser($role->code);
            }

            // 获取角色菜单（按当前租户过滤）
            $tenantId = \Framework\Tenant\TenantContext::getTenantId() ?? 0;
            $menuIds = SysRoleMenu::where('role_id', $roleId)
                ->where('tenant_id', $tenantId)
                ->pluck('menu_id')
                ->toArray();

            if (empty($menuIds)) {
                // 菜单为空时也要 reload，确保内存与数据库一致
                $this->getEnforcer()->loadPolicy();
                return;
            }

            // 获取菜单（含 slug 的按钮/API 类型菜单才写入 Casbin 权限策略）
            $menus = SysMenu::whereIn('id', $menuIds)
                ->where('status', SysMenu::STATUS_ENABLED)
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->get();

            // 直接通过 adapter 添加权限策略，确保写入数据库
            $addedCount = 0;
            foreach ($menus as $menu) {
                $policies = $this->buildPoliciesFromMenu($role->code, $menu);
                foreach ($policies as $policy) {
                    if ($adapter instanceof DatabaseAdapter) {
                        $adapter->addPolicy('p', 'p', $policy);
                    } else {
                        $this->getEnforcer()->addPolicy(...$policy);
                    }
                    $addedCount++;
                }
            }

            // 重新从数据库加载策略，同步内存状态
            $this->getEnforcer()->loadPolicy();

            app('log')->info("Casbin syncRoleMenuPermissions: roleId={$roleId}, roleCode={$role->code}, tenantId={$tenantId}, addedPolicies={$addedCount}");

            // 重建该角色下所有用户的 g 策略（ptype='g'，v0=userId, v1=roleCode）
            $userIds = SysUserRole::where('role_id', $roleId)->pluck('user_id')->toArray();
            foreach ($userIds as $userId) {
                // 先删除该用户在 Casbin 中的所有角色绑定，再从数据库重新同步
                $this->syncUserRolesFromDatabase((int)$userId);
            }
        } catch (\Throwable $e) {
            app('log')->error("Casbin syncRoleMenuPermissions failed: roleId={$roleId}, error={$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * 同步用户菜单权限 (从数据库)
     *
     * @param int $userId 用户ID
     * @return void
     */
    public function syncUserMenuPermissions(int $userId): void
    {
        $user = SysUser::find($userId);
        if (!$user) {
            return;
        }

        try {
            // 直接通过 adapter 删除该用户在 casbin_rule 中的所有 p 策略
            $adapter = $this->getEnforcer()->getAdapter();
            if ($adapter instanceof DatabaseAdapter) {
                $adapter->removeFilteredPolicy('p', 'p', 0, (string)$userId);
            } else {
                $this->getEnforcer()->deletePermissionsForUser((string)$userId);
            }

            // 获取用户菜单（按当前租户过滤）
            $tenantId = \Framework\Tenant\TenantContext::getTenantId() ?? 0;
            $menuIds = SysUserMenu::where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->pluck('menu_id')
                ->toArray();

            if (empty($menuIds)) {
                $this->getEnforcer()->loadPolicy();
                return;
            }

            // 获取菜单（含 slug 的按钮/API 类型菜单才写入 Casbin 权限策略）
            $menus = SysMenu::whereIn('id', $menuIds)
                ->where('status', SysMenu::STATUS_ENABLED)
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->get();

            // 直接通过 adapter 添加权限策略，确保写入数据库
            $addedCount = 0;
            foreach ($menus as $menu) {
                $policies = $this->buildPoliciesFromMenu((string)$userId, $menu);
                foreach ($policies as $policy) {
                    if ($adapter instanceof DatabaseAdapter) {
                        $adapter->addPolicy('p', 'p', $policy);
                    } else {
                        $this->getEnforcer()->addPolicy(...$policy);
                    }
                    $addedCount++;
                }
            }

            // 重新从数据库加载策略，同步内存状态
            $this->getEnforcer()->loadPolicy();

            app('log')->info("Casbin syncUserMenuPermissions: userId={$userId}, tenantId={$tenantId}, addedPolicies={$addedCount}");

        } catch (\Throwable $e) {
            app('log')->error("Casbin syncUserMenuPermissions failed: userId={$userId}, error={$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * 从菜单构建 Casbin 策略数组列表
     *
     * @param string  $roleCode 角色编码
     * @param SysMenu $menu     菜单
     * @return array<int, array{0: string, 1: string, 2: string}>
     */
    protected function buildPoliciesFromMenu(string $roleCode, SysMenu $menu): array
    {
        $policies = [];
        $permission = $menu->slug;
        $path = $menu->path;

        // 如果有自定义权限标识，解析使用
        if ($permission) {
            $parts = explode(':', $permission);

            if (count($parts) >= 3) {
                $module = $parts[0];
                $controller = $parts[1];
                $action = $parts[2];

                // 根据动作确定 HTTP 方法
                $httpMethod = $this->getHttpMethod($action);
                $apiPath = "/api/{$module}/{$controller}";

                $policies[] = [$roleCode, $apiPath, $httpMethod];

                // 对于查询接口，同时添加列表路径
                if (in_array($action, ['list', 'query'])) {
                    $policies[] = [$roleCode, $apiPath . '/list', 'GET'];
                    $policies[] = [$roleCode, $apiPath . '/*', 'GET'];
                }
            }
        }

        // 如果有路由路径，直接添加
        if ($path && str_starts_with($path, '/')) {
            $policies[] = [$roleCode, $path, '*'];

            // 对于菜单，添加其子路径权限
            if ($menu->isMenu()) {
                $policies[] = [$roleCode, $path . '/*', '*'];
            }
        }

        return $policies;
    }

    /**
     * 从菜单添加权限策略
     *
     * @param string  $roleCode 角色编码
     * @param SysMenu $menu     菜单
     * @return void
     */
    protected function addPermissionFromMenu(string $roleCode, SysMenu $menu): void
    {
        // 根据菜单类型生成权限策略
        $permission = $menu->slug;
        $path = $menu->path;

        // 如果有自定义权限标识，解析使用
        if ($permission) {
            // 简单处理：将权限标识转换为 API 路径
            // system:user:list -> /api/system/user/list
            // system:user:add -> /api/system/user, POST
            $parts = explode(':', $permission);

            if (count($parts) >= 3) {
                $module = $parts[0];
                $controller = $parts[1];
                $action = $parts[2];

                // 根据动作确定 HTTP 方法
                $httpMethod = $this->getHttpMethod($action);
                $apiPath = "/api/{$module}/{$controller}";

                // 添加权限策略
                $this->addPermission($roleCode, $apiPath, $httpMethod);

                // 对于查询接口，同时添加列表路径
                if (in_array($action, ['list', 'query'])) {
                    $this->addPermission($roleCode, $apiPath . '/list', 'GET');
                    $this->addPermission($roleCode, $apiPath . '/*', 'GET');
                }
            }
        }

        // 如果有路由路径，直接添加
        if ($path && str_starts_with($path, '/')) {
            $this->addPermission($roleCode, $path, '*');

            // 对于菜单，添加其子路径权限
            if ($menu->isMenu()) {
                $this->addPermission($roleCode, $path . '/*', '*');
            }
        }
    }

    /**
     * 根据动作获取 HTTP 方法
     *
     * @param string $action 动作
     * @return string
     */
    protected function getHttpMethod(string $action): string
    {
        return match ($action) {
            'list', 'query', 'get', 'detail', 'info' => 'GET',
            'add', 'create', 'insert' => 'POST',
            'edit', 'update', 'modify' => 'PUT',
            'delete', 'remove', 'destroy' => 'DELETE',
            default => '*',
        };
    }

    // ==================== 缓存管理 ====================

    /**
     * 清除用户权限缓存
     *
     * @param int $userId 用户ID
     * @return void
     */
    public function clearUserCache(int $userId): void
    {
        if (!$this->isCacheEnabled()) {
            return;
        }

        $prefix = $this->getCachePrefix();
        $pattern = "{$prefix}permission:{$userId}:*";

        // 清除 Redis 缓存
        if ($this->config['cache']['driver'] === 'redis') {
            $redis = app('redis');
            $keys = $redis->keys($pattern);
            foreach ($keys as $key) {
                $redis->del($key);
            }
        }
    }

    /**
     * 清除所有权限缓存
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        if (!$this->isCacheEnabled()) {
            return;
        }

        $prefix = $this->getCachePrefix();

        if ($this->config['cache']['driver'] === 'redis') {
            $redis = app('redis');
            $keys = $redis->keys("{$prefix}*");
            foreach ($keys as $key) {
                $redis->del($key);
            }
        }
    }

    /**
     * 重新加载策略
     *
     * @return void
     */
    public function reloadPolicy(): void
    {
        $this->getEnforcer()->loadPolicy();
        $this->clearAllCache();
    }

    // ==================== 辅助方法 ====================

    /**
     * 检查缓存是否启用
     *
     * @return bool
     */
    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    /**
     * 获取缓存前缀
     *
     * @return string
     */
    protected function getCachePrefix(): string
    {
        return $this->config['cache']['prefix'] ?? 'casbin:';
    }

    /**
     * 获取缓存过期时间
     *
     * @return int
     */
    protected function getCacheTtl(): int
    {
        return $this->config['cache']['ttl'] ?? 3600;
    }

    /**
     * 生成缓存键
     *
     * @param mixed ...$args 参数
     * @return string
     */
    protected function getCacheKey(...$args): string
    {
        return $this->getCachePrefix() . implode(':', $args);
    }
}
