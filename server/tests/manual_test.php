<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\SysDept;
use App\Models\SysUser;
use App\Models\SysRole;

$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections']['mysql'];

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $dbConfig['hostname'],
    'database'  => $dbConfig['database'],
    'username'  => $dbConfig['username'],
    'password'  => $dbConfig['password'],
    'charset'   => $dbConfig['charset'],
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    echo "Creating Dept...\n";
    $dept = SysDept::create([
        'dept_name' => 'IT Dept ' . time(),
        'order_num' => 1,
        'status' => '0'
    ]);
    echo "Dept Created: " . $dept->dept_id . "\n";
    
    echo "Creating Role...\n";
    $role = SysRole::create([
        'role_name' => 'Admin ' . time(),
        'role_key' => 'admin_' . time(),
        'role_sort' => 1,
        'data_scope' => '1', // All data
        'status' => '0'
    ]);
    echo "Role Created: " . $role->role_id . "\n";
    
    echo "Creating User...\n";
    $user = SysUser::create([
        'user_name' => 'test_admin_' . time(),
        'nick_name' => 'Test Admin',
        'dept_id' => $dept->dept_id,
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'status' => '0'
    ]);
    echo "User Created: " . $user->user_id . "\n";
    
    // Assign Role
    $user->roles()->attach($role->role_id);
    echo "Role Assigned.\n";
    
    // Test Data Scope
    // Mock user object with roles relation
    $userWithRoles = SysUser::with('roles')->find($user->user_id);
    
    // Query users with data scope
    $query = SysUser::query();
    $query->dataScope($userWithRoles);
    $count = $query->count();
    
    echo "Data Scope Count (Should be > 0): " . $count . "\n";
    
    // Clean up
    // $user->delete();
    // $role->delete();
    // $dept->delete();
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
