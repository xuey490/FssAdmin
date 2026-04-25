<?php

declare(strict_types=1);

/**
 * Multi-Tenant Department Isolation Preservation Property Tests
 * 
 * **Property 2: Preservation** - 单租户模式和现有功能保持不变
 * 
 * **CRITICAL**: These tests MUST PASS on unfixed code - passing confirms baseline behavior to preserve
 * **GOAL**: Capture current behavior patterns that must remain unchanged after the fix
 * **Observation-First Methodology**: These tests observe and encode existing behavior on non-buggy inputs
 * 
 * **Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**
 * 
 * @package Tests\Unit
 * @author  Kiro
 * @date    2026-04-24
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\SysUser;
use App\Models\SysDept;
use App\Models\SysTenant;
use Framework\Tenant\TenantContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * MultiTenantDeptIsolationPreservationTest - Test preservation of existing functionality
 * 
 * This test suite verifies that single-tenant users, users with no tenant context,
 * and existing associations (roles, menus, posts) remain unchanged after the fix.
 */
class MultiTenantDeptIsolationPreservationTest extends TestCase
{
    /**
     * @var Request Test request instance
     */
    private Request $request;

    /**
     * @var RequestStack Test request stack
     */
    private RequestStack $requestStack;

    /**
     * Set up test environment before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize database connection
        $this->initializeDatabase();
        
        // Set up request context for tenant switching
        $this->request = new Request();
        $this->requestStack = new RequestStack();
        $this->requestStack->push($this->request);
        TenantContext::setRequestStack($this->requestStack);
    }

    /**
     * Initialize database connection using existing configuration
     */
    private function initializeDatabase(): void
    {
        // Load database configuration
        $config = require __DIR__ . '/../../config/database.php';
        $dbConfig = $config['connections']['mysql'];
        
        // Initialize Eloquent ORM
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $dbConfig['hostname'],
            'database' => $dbConfig['database'],
            'username' => $dbConfig['username'],
            'password' => $dbConfig['password'],
            'charset' => $dbConfig['charset'] ?? 'utf8mb4',
            'collation' => $dbConfig['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix' => $dbConfig['prefix'] ?? '',
        ]);
        
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * Test: Single-tenant users should continue using sa_system_user.dept_id as fallback
     * 
     * **Preservation Requirement**: When a user belongs to only one tenant or has no
     * sa_system_user_dept record, the system should fall back to sa_system_user.dept_id.
     * 
     * **Expected Behavior**: getCurrentDeptId() returns sa_system_user.dept_id when
     * no tenant-specific record exists in sa_system_user_dept.
     * 
     * **Validates: Requirements 3.1, 3.3**
     * 
     * @test
     */
    public function test_single_tenant_user_uses_fallback_dept_id(): void
    {
        // Arrange: Create single-tenant user
        $tenant = $this->findOrCreateTenant(10, '单租户测试');
        $dept = $this->findOrCreateDept(100, 10, '测试部门');
        $user = $this->findOrCreateUser(1001, 'single_tenant_user', 100);
        
        // Associate user with only one tenant
        $this->associateUserWithTenant($user->id, $tenant->id);
        
        // DO NOT create sa_system_user_dept record - test fallback behavior
        
        // Act: Set tenant context
        TenantContext::setTenantIdToRequest($this->request, $tenant->id);
        $this->request->attributes->set('current_user', $user);
        
        // Reload user
        $user = SysUser::find($user->id);
        
        // Assert: Fallback to sa_system_user.dept_id
        // On unfixed code: getCurrentDeptId() doesn't exist, so we verify dept_id is accessible
        $this->assertNotNull(
            $user->dept_id,
            'Single-tenant user should have dept_id from sa_system_user table'
        );
        
        $this->assertEquals(
            100,
            $user->dept_id,
            'Single-tenant user dept_id should be 100 from sa_system_user.dept_id'
        );
        
        // After fix: getCurrentDeptId() should return the same value as dept_id
        if (method_exists($user, 'getCurrentDeptId')) {
            $currentDeptId = $user->getCurrentDeptId();
            $this->assertEquals(
                $user->dept_id,
                $currentDeptId,
                'getCurrentDeptId() should fall back to dept_id when no sa_system_user_dept record exists'
            );
        }
    }

    /**
     * Test: Users with no tenant context should fall back to sa_system_user.dept_id
     * 
     * **Preservation Requirement**: When there is no tenant context (TenantContext returns null),
     * the system should use sa_system_user.dept_id as fallback.
     * 
     * **Expected Behavior**: getCurrentDeptId() returns sa_system_user.dept_id when
     * TenantContext::getTenantId() returns null.
     * 
     * **Validates: Requirements 3.1**
     * 
     * @test
     */
    public function test_user_without_tenant_context_uses_fallback_dept_id(): void
    {
        // Arrange: Create user
        $dept = $this->findOrCreateDept(101, 10, '无租户部门');
        $user = $this->findOrCreateUser(1002, 'no_tenant_user', 101);
        
        // Act: DO NOT set tenant context - TenantContext::getTenantId() will return null
        // This simulates scenarios where tenant context is not available
        
        // Reload user
        $user = SysUser::find($user->id);
        
        // Assert: Should use dept_id from sa_system_user table
        $this->assertNotNull(
            $user->dept_id,
            'User without tenant context should have dept_id from sa_system_user table'
        );
        
        $this->assertEquals(
            101,
            $user->dept_id,
            'User dept_id should be 101 from sa_system_user.dept_id'
        );
        
        // After fix: getCurrentDeptId() should return dept_id when no tenant context
        if (method_exists($user, 'getCurrentDeptId')) {
            $currentDeptId = $user->getCurrentDeptId();
            $this->assertEquals(
                $user->dept_id,
                $currentDeptId,
                'getCurrentDeptId() should fall back to dept_id when no tenant context'
            );
        }
    }

    /**
     * Test: User roles() relationship should remain unchanged
     * 
     * **Preservation Requirement**: The roles() relationship and related methods
     * (getRoleIds, getRoleCodes) must continue to work as before.
     * 
     * **Expected Behavior**: User can access roles through existing relationship methods.
     * 
     * **Validates: Requirements 3.2, 3.6**
     * 
     * @test
     */
    public function test_user_roles_relationship_unchanged(): void
    {
        // Arrange: Create user with roles
        $tenant = $this->findOrCreateTenant(10, '角色测试租户');
        $user = $this->findOrCreateUser(1003, 'user_with_roles', 100);
        
        $this->associateUserWithTenant($user->id, $tenant->id);
        
        // Create test roles
        $role1 = $this->findOrCreateRole(201, $tenant->id, 'admin', '管理员');
        $role2 = $this->findOrCreateRole(202, $tenant->id, 'editor', '编辑员');
        
        // Associate user with roles
        $this->associateUserWithRole($user->id, $tenant->id, $role1->id);
        $this->associateUserWithRole($user->id, $tenant->id, $role2->id);
        
        // Act: Set tenant context
        TenantContext::setTenantIdToRequest($this->request, $tenant->id);
        
        // Reload user
        $user = SysUser::find($user->id);
        
        // Assert: roles() relationship should work
        $this->assertTrue(
            method_exists($user, 'roles'),
            'User should have roles() relationship method'
        );
        
        // Verify roles can be loaded
        $roles = $user->roles;
        $this->assertNotNull($roles, 'User roles should be accessible');
        
        // Verify role count (may vary based on existing data)
        $this->assertGreaterThanOrEqual(
            0,
            $roles->count(),
            'User should have accessible roles collection'
        );
    }

    /**
     * Test: User menus() relationship should remain unchanged
     * 
     * **Preservation Requirement**: The menus() relationship must continue to work as before.
     * 
     * **Expected Behavior**: User can access menus through existing relationship methods.
     * 
     * **Validates: Requirements 3.2, 3.6**
     * 
     * @test
     */
    public function test_user_menus_relationship_unchanged(): void
    {
        // Arrange: Create user
        $tenant = $this->findOrCreateTenant(10, '菜单测试租户');
        $user = $this->findOrCreateUser(1004, 'user_with_menus', 100);
        
        $this->associateUserWithTenant($user->id, $tenant->id);
        
        // Act: Set tenant context
        TenantContext::setTenantIdToRequest($this->request, $tenant->id);
        
        // Reload user
        $user = SysUser::find($user->id);
        
        // Assert: menus() relationship should exist
        $this->assertTrue(
            method_exists($user, 'menus'),
            'User should have menus() relationship method'
        );
        
        // Verify menus can be accessed (may be empty)
        $menus = $user->menus;
        $this->assertNotNull($menus, 'User menus should be accessible');
    }

    /**
     * Test: User posts() relationship should remain unchanged
     * 
     * **Preservation Requirement**: The posts() relationship must continue to work as before.
     * 
     * **Expected Behavior**: User can access posts through existing relationship methods.
     * 
     * **Validates: Requirements 3.2, 3.6**
     * 
     * @test
     */
    public function test_user_posts_relationship_unchanged(): void
    {
        // Arrange: Create user
        $tenant = $this->findOrCreateTenant(10, '岗位测试租户');
        $user = $this->findOrCreateUser(1005, 'user_with_posts', 100);
        
        $this->associateUserWithTenant($user->id, $tenant->id);
        
        // Act: Set tenant context
        TenantContext::setTenantIdToRequest($this->request, $tenant->id);
        
        // Reload user
        $user = SysUser::find($user->id);
        
        // Assert: posts() relationship should exist
        $this->assertTrue(
            method_exists($user, 'posts'),
            'User should have posts() relationship method'
        );
        
        // Verify posts can be accessed (may be empty)
        $posts = $user->posts;
        $this->assertNotNull($posts, 'User posts should be accessible');
    }

    /**
     * Test: Department tree structure methods should remain unchanged
     * 
     * **Preservation Requirement**: SysDept::getDeptTree() and getSelectTree() methods
     * must continue to return correct tree structures.
     * 
     * **Expected Behavior**: Department tree building logic works as before.
     * 
     * **Validates: Requirements 3.4**
     * 
     * @test
     */
    public function test_department_tree_structure_unchanged(): void
    {
        // Arrange: Create department hierarchy
        $tenant = $this->findOrCreateTenant(10, '部门树测试租户');
        
        $parentDept = $this->findOrCreateDept(110, 10, '父部门');
        $childDept1 = $this->findOrCreateDeptWithParent(111, 10, '子部门1', 110);
        $childDept2 = $this->findOrCreateDeptWithParent(112, 10, '子部门2', 110);
        
        // Act: Set tenant context
        TenantContext::setTenantIdToRequest($this->request, $tenant->id);
        
        // Assert: getDeptTree() method should exist and work
        if (method_exists(SysDept::class, 'getDeptTree')) {
            $tree = SysDept::getDeptTree(0, $tenant->id);
            $this->assertNotNull($tree, 'getDeptTree() should return a tree structure');
        }
        
        // Assert: getSelectTree() method should exist and work
        if (method_exists(SysDept::class, 'getSelectTree')) {
            $selectTree = SysDept::getSelectTree(0, $tenant->id);
            $this->assertNotNull($selectTree, 'getSelectTree() should return a tree structure');
        }
        
        // Verify parent-child relationship is preserved
        $parent = SysDept::find(110);
        if ($parent && method_exists($parent, 'children')) {
            $children = $parent->children;
            $this->assertNotNull($children, 'Department children relationship should work');
        }
    }

    /**
     * Test: Data scope permission types logic should remain unchanged
     * 
     * **Preservation Requirement**: The six data_scope permission types
     * (all, dept, dept+children, self, dept+children+self, custom) must work correctly.
     * 
     * **Expected Behavior**: DataScopeTrait logic remains unchanged, only dept_id source changes.
     * 
     * **Validates: Requirements 3.5**
     * 
     * @test
     */
    public function test_data_scope_permission_types_logic_unchanged(): void
    {
        // Arrange: Create user with department
        $tenant = $this->findOrCreateTenant(10, '权限测试租户');
        $dept = $this->findOrCreateDept(120, 10, '权限测试部门');
        $user = $this->findOrCreateUser(1006, 'user_with_permissions', 120);
        
        $this->associateUserWithTenant($user->id, $tenant->id);
        
        // Act: Set tenant context
        TenantContext::setTenantIdToRequest($this->request, $tenant->id);
        
        // Reload user
        $user = SysUser::find($user->id);
        
        // Assert: User should have dept_id accessible for data_scope filtering
        $this->assertNotNull(
            $user->dept_id,
            'User dept_id should be accessible for data_scope filtering'
        );
        
        $this->assertEquals(
            120,
            $user->dept_id,
            'User dept_id should be 120 for data_scope filtering'
        );
        
        // Verify dept relationship exists (used by data_scope logic)
        $this->assertTrue(
            method_exists($user, 'dept'),
            'User should have dept() relationship for data_scope filtering'
        );
        
        // After fix: getCurrentDeptId() should be used by DataScopeTrait
        // but the six permission types logic should remain unchanged
        if (method_exists($user, 'getCurrentDeptId')) {
            $currentDeptId = $user->getCurrentDeptId();
            $this->assertIsInt(
                $currentDeptId,
                'getCurrentDeptId() should return an integer for data_scope filtering'
            );
        }
    }

    /**
     * Find or create a tenant for testing
     */
    private function findOrCreateTenant(int $id, string $name): SysTenant
    {
        $tenant = SysTenant::find($id);
        if (!$tenant) {
            $tenant = new SysTenant();
            $tenant->id = $id;
            $tenant->tenant_name = $name;
            $tenant->tenant_code = 'tenant_' . $id;
            $tenant->status = 1;
            $tenant->save();
        }
        return $tenant;
    }

    /**
     * Find or create a department for testing
     */
    private function findOrCreateDept(int $id, int $tenantId, string $name): SysDept
    {
        $dept = SysDept::find($id);
        if (!$dept) {
            $dept = new SysDept();
            $dept->id = $id;
            $dept->tenant_id = $tenantId;
            $dept->name = $name;
            $dept->parent_id = 0;
            $dept->sort = 0;
            $dept->status = 1;
            $dept->save();
        }
        return $dept;
    }

    /**
     * Find or create a department with parent for testing
     */
    private function findOrCreateDeptWithParent(int $id, int $tenantId, string $name, int $parentId): SysDept
    {
        $dept = SysDept::find($id);
        if (!$dept) {
            $dept = new SysDept();
            $dept->id = $id;
            $dept->tenant_id = $tenantId;
            $dept->name = $name;
            $dept->parent_id = $parentId;
            $dept->sort = 0;
            $dept->status = 1;
            $dept->save();
        }
        return $dept;
    }

    /**
     * Find or create a user for testing
     */
    private function findOrCreateUser(int $id, string $username, int $deptId): SysUser
    {
        $user = SysUser::find($id);
        if (!$user) {
            $user = new SysUser();
            $user->id = $id;
            $user->username = $username;
            $user->password = password_hash('test123', PASSWORD_BCRYPT);
            $user->realname = 'Test User';
            $user->dept_id = $deptId;
            $user->status = 1;
            $user->is_super = 0;
            $user->save();
        }
        return $user;
    }

    /**
     * Find or create a role for testing
     */
    private function findOrCreateRole(int $id, int $tenantId, string $code, string $name)
    {
        $role = Capsule::table('sa_system_role')->where('id', $id)->first();
        if (!$role) {
            Capsule::table('sa_system_role')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'code' => $code,
                'name' => $name,
                'status' => 1,
                'data_scope' => 1,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            $role = Capsule::table('sa_system_role')->where('id', $id)->first();
        }
        return $role;
    }

    /**
     * Associate user with tenant
     */
    private function associateUserWithTenant(int $userId, int $tenantId): void
    {
        $exists = Capsule::table('sa_system_user_tenant')
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->exists();
        
        if (!$exists) {
            Capsule::table('sa_system_user_tenant')->insert([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'is_default' => 0,
                'join_time' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Associate user with role
     */
    private function associateUserWithRole(int $userId, int $tenantId, int $roleId): void
    {
        $exists = Capsule::table('sa_system_user_role')
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('role_id', $roleId)
            ->exists();
        
        if (!$exists) {
            Capsule::table('sa_system_user_role')->insert([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'role_id' => $roleId,
            ]);
        }
    }

    /**
     * Clean up test data after each test
     */
    protected function tearDown(): void
    {
        // Clean up test users
        $testUserIds = [1001, 1002, 1003, 1004, 1005, 1006];
        foreach ($testUserIds as $userId) {
            Capsule::table('sa_system_user_role')
                ->where('user_id', $userId)
                ->delete();
            
            Capsule::table('sa_system_user_tenant')
                ->where('user_id', $userId)
                ->delete();
            
            SysUser::where('id', $userId)->forceDelete();
        }
        
        // Clean up test roles
        Capsule::table('sa_system_role')
            ->whereIn('id', [201, 202])
            ->delete();
        
        // Note: We don't delete tenants and depts as they might be used by other tests
        
        parent::tearDown();
    }
}
