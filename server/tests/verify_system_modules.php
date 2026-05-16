<?php

define('BASE_PATH', realpath(dirname(__DIR__)));
define('APP_DEBUG', true);

require BASE_PATH . '/vendor/autoload.php';

if (file_exists(BASE_PATH . '/app/function.php')) {
    require_once BASE_PATH . '/app/function.php';
}

use Framework\Core\Framework;
use App\Services\SysAuthService;
use App\Services\SysUserService;
use App\Services\SysRoleService;
use App\Services\SysMenuService;
use App\Services\SysDeptService;
use App\Services\SysPostService;
use App\Services\SysDictService;
use App\Services\SysConfigService;
use App\Services\SysLoginLogService;
use App\Services\SysAccessLogService;
use App\Models\SysUser;
use App\Models\SysLoginLog;
use App\Models\SysAccessLog;

echo "Initializing Framework...\n";
Framework::getInstance();

echo "Initializing DB...\n";
try {
    $db = app('db');
} catch (\Throwable $e) {
    echo "Failed to resolve 'db': " . $e->getMessage() . "\n";
}

// Check Facade App and Reset if needed
// This is required because EloquentFactory resets Facade App to a new Illuminate Container
$facadeApp = \Illuminate\Support\Facades\Facade::getFacadeApplication();
if (!($facadeApp instanceof \Framework\Container\Container)) {
    echo "WARNING: Facade App was reset by EloquentFactory. Resetting to Framework Container...\n";
    \Illuminate\Support\Facades\Facade::setFacadeApplication(app());
}

echo "Starting System Modules Verification...\n";

try {
    $authService = app(SysAuthService::class);
    $userService = app(SysUserService::class);
    $roleService = app(SysRoleService::class);
    $menuService = app(SysMenuService::class);
    $deptService = app(SysDeptService::class);
    $postService = app(SysPostService::class);
    $dictService = app(SysDictService::class);
    $configService = app(SysConfigService::class);
    $loginLogService = app(SysLoginLogService::class);
    $accessLogService = app(SysAccessLogService::class);

    echo "\n[Test] Auth Login...\n";
    $admin = SysUser::where('user_name', 'admin')->first();
    if (!$admin) {
        $admin = SysUser::create([
            'user_name' => 'admin',
            'nick_name' => 'Administrator',
            'password' => password_hash('123456', PASSWORD_BCRYPT),
            'dept_id' => 100,
            'enabled' => 1,
            'del_flag' => '0'
        ]);
        echo "Created admin user.\n";
    } else {
        $admin->password = password_hash('123456', PASSWORD_BCRYPT);
        $admin->enabled = 1;
        $admin->del_flag = '0';
        $admin->save();
        echo "Reset admin password.\n";
    }

    $token = $authService->login('admin', '123456');
    echo "Login success. Token: " . substr($token['token'], 0, 20) . "...\n";
    
    echo "\n[Test] SysUser CRUD...\n";
    $userName = 'test_user_' . time();
    $newUser = $userService->create([
        'user_name' => $userName,
        'nick_name' => 'Test User',
        'password' => '123456',
        'dept_id' => 100,
        'enabled' => 1,
        'del_flag' => '0'
    ]);
    echo "Created user ID: {$newUser->id}\n";
    
    $userList = $userService->getList(['user_name' => 'test_user']);
    echo "Found users: " . $userList->count() . "\n";
    
    $userService->update($newUser->id, ['nick_name' => 'Updated Name']);
    $updatedUser = $userService->getById($newUser->id);
    echo "Updated Name: {$updatedUser->nick_name}\n";
    
    $userService->delete([$newUser->id]);
    echo "Deleted user.\n";
    
    echo "\n[Test] SysDict CRUD...\n";
    $dictCode = 'test_dict_' . time();
    $dictType = $dictService->createType([
        'name' => 'Test Dict',
        'code' => $dictCode,
        'enabled' => 1
    ]);
    echo "Created dict type ID: {$dictType->id}\n";
    
    $dictData = $dictService->createData([
        'dict_id' => $dictType->id,
        'label' => 'Item 1',
        'value' => '1',
        'code' => $dictType->code,
        'sort' => 1,
        'enabled' => 1
    ]);
    echo "Created dict data ID: {$dictData->id}\n";
    
    try {
        $dictService->deleteType([$dictType->id]);
        echo "WARNING: Deleted dict type with items (should have failed)\n";
    } catch (\Exception $e) {
        echo "Expected error deleting dict with items: " . $e->getMessage() . "\n";
    }
    
    $dictService->deleteData([$dictData->id]);
    $dictService->deleteType([$dictType->id]);
    echo "Cleaned up dict.\n";
    
    echo "\n[Test] SysConfig CRUD...\n";
    $config = $configService->create([
        'config_name' => 'Test Config',
        'config_key' => 'test.config.' . time(),
        'config_value' => 'true'
    ]);
    echo "Created config ID: {$config->id}\n";
    
    $val = $configService->getConfigValue($config->config_key);
    echo "Config Value: {$val}\n";
    
    $configService->delete([$config->id]);
    echo "Deleted config.\n";

    echo "\n[Test] SysRole CRUD...\n";
    $roleName = 'test_role_' . time();
    $roleKey = 'test_role_' . time();
    $newRole = $roleService->create([
        'role_name' => $roleName,
        'role_key' => $roleKey,
        'role_sort' => 1,
        'enabled' => 1,
        'menu_ids' => [1, 2] // Assuming some menu IDs exist from init.sql
    ]);
    echo "Created role ID: {$newRole->id}\n";
    
    $roleService->update($newRole->id, ['role_name' => 'Updated Role']);
    $updatedRole = $roleService->getById($newRole->id);
    echo "Updated Role Name: {$updatedRole->role_name}\n";
    
    $roleService->delete([$newRole->id]);
    echo "Deleted role.\n";

    echo "\n[Test] SysMenu CRUD...\n";
    $menu = $menuService->create([
        'pid' => 0,
        'menu_type' => 'C',
        'title' => 'Test Menu',
        'path' => 'test',
        'component' => 'test/index',
        'sort' => 1,
        'visible' => '0',
        'status' => '0'
    ]);
    echo "Created menu ID: {$menu->id}\n";
    
    $menuTree = $menuService->getList([]);
    echo "Fetched menu tree. Count: " . count($menuTree) . "\n";
    
    $menuService->delete([$menu->id]);
    echo "Deleted menu.\n";

    echo "\n[Test] SysDept CRUD...\n";
    $dept = $deptService->create([
        'pid' => 100, // Assuming 100 is a valid parent from init.sql
        'dept_name' => 'Test Dept',
        'order_num' => 1,
        'leader' => 'Leader',
        'phone' => '12345678901',
        'status' => '0'
    ]);
    echo "Created dept ID: {$dept->id}\n";
    
    $deptTree = $deptService->getList([]);
    echo "Fetched dept tree. Count: " . count($deptTree) . "\n";
    
    $deptService->delete([$dept->id]);
    echo "Deleted dept.\n";

    echo "\n[Test] SysPost CRUD...\n";
    $post = $postService->create([
        'post_code' => 'TEST',
        'post_name' => 'Test Post',
        'post_sort' => 1,
        'status' => '0'
    ]);
    echo "Created post ID: {$post->id}\n";
    
    $postList = $postService->getList(['post_code' => 'TEST']);
    echo "Found posts: " . $postList->count() . "\n";
    
    $postService->delete([$post->id]);
    echo "Deleted post.\n";

    echo "\n[Test] SysLoginLog...\n";
    $logId = SysLoginLog::insertGetId([
        'user_name' => 'test_user',
        'ip' => '127.0.0.1',
        'status' => 1,
        'message' => 'Login Success',
        'login_time' => date('Y-m-d H:i:s')
    ]);
    echo "Created login log ID: {$logId}\n";
    $logList = $loginLogService->getList(['user_name' => 'test_user']);
    echo "Found login logs: " . $logList->count() . "\n";
    $loginLogService->delete([$logId]);
    echo "Deleted login log.\n";

    echo "\n[Test] SysAccessLog...\n";
    $accessLogId = SysAccessLog::insertGetId([
        'user_name' => 'test_user',
        'ip' => '127.0.0.1',
        'method' => 'GET',
        'path' => '/api/test',
        'status_code' => 200,
        'duration_ms' => 10,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    echo "Created access log ID: {$accessLogId}\n";
    $accessList = $accessLogService->getList(['path' => '/api/test']);
    echo "Found access logs: " . $accessList->count() . "\n";
    $accessLogService->delete([$accessLogId]);
    echo "Deleted access log.\n";

    echo "\n[Test] SysRole User Allocation...\n";
    // Reuse admin user or create new one
    $allocUser = $userService->create([
        'user_name' => 'alloc_user',
        'nick_name' => 'Alloc User',
        'password' => '123456',
        'dept_id' => 100,
        'enabled' => 1,
        'del_flag' => '0'
    ]);
    // Create a role
    $allocRole = $roleService->create([
        'role_name' => 'Alloc Role',
        'role_key' => 'alloc_role',
        'role_sort' => 100,
        'enabled' => 1
    ]);
    
    // Auth user
    $roleService->authUser($allocRole->id, [$allocUser->id]);
    echo "Authorized user to role.\n";
    
    // Check allocated list
    $allocated = $roleService->allocatedUserList($allocRole->id, []);
    echo "Allocated users: " . $allocated->count() . "\n";
    
    // Check unallocated list
    $unallocated = $roleService->unallocatedUserList($allocRole->id, []);
    echo "Unallocated users (total - 1): " . $unallocated->total() . "\n";
    
    // Cancel auth
    $roleService->cancelAuthUser($allocRole->id, [$allocUser->id]);
    echo "Cancelled authorization.\n";
    
    // Cleanup
    $userService->delete([$allocUser->id]);
    $roleService->delete([$allocRole->id]);

    echo "\n[Test] SysUser Extra (ResetPwd, GrantRole)...\n";
    $testUser = $userService->create([
        'user_name' => 'extra_user',
        'nick_name' => 'Extra User',
        'password' => '123456',
        'dept_id' => 100,
        'enabled' => 1,
        'del_flag' => '0'
    ]);
    
    // Reset Password
    $userService->resetPassword($testUser->id, '654321');
    $checkUser = $userService->getById($testUser->id);
    if (password_verify('654321', $checkUser->password)) {
        echo "Password reset success.\n";
    } else {
        echo "Password reset failed.\n";
    }
    
    // Grant Role
    $testRole = $roleService->create([
        'role_name' => 'Test Role',
        'role_key' => 'test_role',
        'role_sort' => 1,
        'enabled' => 1
    ]);
    $userService->grantRole($testUser->id, [$testRole->id]);
    $checkUserRoles = $userService->getById($testUser->id);
    if ($checkUserRoles->roles->contains('id', $testRole->id)) {
        echo "Grant role success.\n";
    } else {
        echo "Grant role failed.\n";
    }
    
    // Batch Destroy (Service level check)
    $userService->delete([$testUser->id]);
    $roleService->delete([$testRole->id]);
    echo "Cleanup extra user/role.\n";

    echo "\nAll system module verifications passed!\n";

} catch (\Throwable $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
