<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Admin;
use App\Models\Asset;
use App\Models\Menu;

class AuthService
{
    /**
     * 登录并初始化双重权限
     */
    public function login(string $username, string $password, ?int $tenantId = null): ?Admin
    {
        // 1. 手动设置租户ID（所有业务模型都可调用此方法）
        if ($tenantId) {
            Admin::setManualTenantId($tenantId);
            Asset::setManualTenantId($tenantId);
            Menu::setManualTenantId($tenantId);
        }

        // 2. 查询用户（自动触发租户隔离）
        $admin = Admin::where('username', $username)->first();
        if (!$admin || !password_verify($password, $admin->password)) {
            return null;
        }

        // 3. 设置超级管理员状态
        $isSuper = $admin->isSuperAdmin();
        Admin::setSuperAdmin($isSuper);
        Asset::setSuperAdmin($isSuper);
        Menu::setSuperAdmin($isSuper);

        // 4. 初始化角色数据权限
        if (!$isSuper) {
            $dataScope = $admin->getMaxDataScope();
            Asset::initDataScope(
                $admin->id,
                $dataScope['scope'],
                $dataScope['dept_ids']
            );
        }

        return $admin;
    }

    /**
     * 退出登录，清空权限
     */
    public function logout(): void
    {
        // 清空租户ID
        Admin::setManualTenantId(null);
        Asset::setManualTenantId(null);
        Menu::setManualTenantId(null);
        // 清空超级管理员状态
        Admin::setSuperAdmin(false);
        Asset::setSuperAdmin(false);
        Menu::setSuperAdmin(false);
        // 清空数据权限
        Asset::clearDataScope();
    }
}
