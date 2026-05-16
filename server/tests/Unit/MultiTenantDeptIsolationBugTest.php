<?php

declare(strict_types=1);

/**
 * Multi-Tenant Department Isolation Bug Condition Exploration Test
 * 
 * **Property 1: Bug Condition** - 多租户部门ID固定不变导致跨租户数据混乱
 * 
 * **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
 * **DO NOT attempt to fix the test or the code when it fails**
 * **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
 * **GOAL**: Surface counterexamples that demonstrate the bug exists
 * **Scoped PBT Approach**: For deterministic bugs, scope the property to the concrete failing case(s) to ensure reproducibility
 * 
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
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
 * MultiTenantDeptIsolationBugTest - Test multi-tenant department isolation bug
 * 
 * This test demonstrates the bug where users belonging to multiple tenants
 * have a fixed dept_id that doesn't change when switching tenants, causing
 * cross-tenant data confusion and permission failures.
 */
class MultiTenantDeptIsolationBugTest extends TestCase
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
     * Test: User with multiple tenants has fixed dept_id that doesn't change when switching tenants
     * 
     * **Bug Condition**: When a user belongs to multiple tenants and switches tenant context,
     * the system still uses the fixed dept_id field from sa_system_user table, which doesn't
     * reflect the user's actual department in the current tenant.
     * 
     * **Expected Behavior (after fix)**: The system should query sa_system_user_dept table
     * to get the user's department ID for the current tenant dynamically.
     * 
     * **Validates: Requirements 1.1, 1.2, 1.4**
     * 
     * @test
     */
    public function test_user_dept_id_should_change_when_switching_tenants(): void
    {
        // Arrange: Create test data
        // Tenant A (tenant_id=1) and Tenant B (tenant_id=2)
        $tenantA = $this->findOrCreateTenant(1, '租户A');
        $tenantB = $this->findOrCreateTenant(2, '租户B');
        
        // Department in Tenant A (dept_id=5, tenant_id=1)
        $deptA = $this->findOrCreateDept(5, 1, '技术部A');
        
        // Department in Tenant B (dept_id=8, tenant_id=2)
        $deptB = $this->findOrCreateDept(8, 2, '销售部B');
        
        // User belongs to both tenants, but sa_system_user.dept_id is fixed to 5 (Tenant A's dept)
        $user = $this->findOrCreateUser(999, 'test_user', 5);
        
        // Associate user with both tenants
        $this->associateUserWithTenant($user->id, $tenantA->id);
        $this->associateUserWithTenant($user->id, $tenantB->id);
        
        // Create user-dept associations in sa_system_user_dept table
        // User should have dept_id=5 in Tenant A and dept_id=8 in Tenant B
        $this->createUserDeptAssociation($user->id, $tenantA->id, $deptA->id);
        $this->createUserDeptAssociation($user->id, $tenantB->id, $deptB->id);
        
        // Act & Assert: Switch to Tenant A
        TenantContext::setTenantIdToRequest($this->request, $tenantA->id);
        $this->request->attributes->set('current_user', $user);
        
        // Reload user to get fresh instance
        $user = SysUser::find($user->id);
        
        // BUG: getCurrentDeptId() method doesn't exist yet, so this will fail
        // After fix: getCurrentDeptId() should return 5 (dept in Tenant A)
        $this->assertTrue(
            method_exists($user, 'getCurrentDeptId'),
            'COUNTEREXAMPLE: SysUser::getCurrentDeptId() method does not exist. ' .
            'This confirms the bug - there is no mechanism to get tenant-specific dept_id. ' .
            'Expected: Method should exist to query sa_system_user_dept table.'
        );
        
        if (method_exists($user, 'getCurrentDeptId')) {
            $deptIdInTenantA = $user->getCurrentDeptId();
            $this->assertEquals(
                5,
                $deptIdInTenantA,
                'COUNTEREXAMPLE: In Tenant A, user dept_id should be 5, but got: ' . $deptIdInTenantA .
                '. This confirms the bug - dept_id is not tenant-specific.'
            );
        }
        
        // Act & Assert: Switch to Tenant B
        TenantContext::setTenantIdToRequest($this->request, $tenantB->id);
        
        // Reload user to get fresh instance
        $user = SysUser::find($user->id);
        
        if (method_exists($user, 'getCurrentDeptId')) {
            $deptIdInTenantB = $user->getCurrentDeptId();
            $this->assertEquals(
                8,
                $deptIdInTenantB,
                'COUNTEREXAMPLE: In Tenant B, user dept_id should be 8, but got: ' . $deptIdInTenantB .
                '. This confirms the bug - dept_id remains fixed at ' . $user->dept_id . ' instead of changing to 8.'
            );
        }
        
        // Additional verification: The fixed dept_id should still be 5
        $this->assertEquals(
            5,
            $user->dept_id,
            'User fixed dept_id should remain 5 (from sa_system_user table)'
        );
    }

    /**
     * Test: DataScopeTrait uses fixed dept_id instead of tenant-specific dept_id
     * 
     * **Bug Condition**: DataScopeTrait directly reads $loginUser->dept_id without
     * considering tenant context, causing permission filtering to use wrong department.
     * 
     * **Expected Behavior (after fix)**: DataScopeTrait should use getCurrentDeptId()
     * to get tenant-specific department ID for permission filtering.
     * 
     * **Validates: Requirements 1.3**
     * 
     * @test
     */
    public function test_data_scope_trait_should_use_tenant_specific_dept_id(): void
    {
        // Arrange: Create test data
        $tenantA = $this->findOrCreateTenant(1, '租户A');
        $tenantB = $this->findOrCreateTenant(2, '租户B');
        
        $deptA = $this->findOrCreateDept(5, 1, '技术部A');
        $deptB = $this->findOrCreateDept(8, 2, '销售部B');
        
        $user = $this->findOrCreateUser(999, 'test_user', 5);
        
        $this->associateUserWithTenant($user->id, $tenantA->id);
        $this->associateUserWithTenant($user->id, $tenantB->id);
        
        $this->createUserDeptAssociation($user->id, $tenantA->id, $deptA->id);
        $this->createUserDeptAssociation($user->id, $tenantB->id, $deptB->id);
        
        // Act: Switch to Tenant B
        TenantContext::setTenantIdToRequest($this->request, $tenantB->id);
        $this->request->attributes->set('current_user', $user);
        
        // Reload user
        $user = SysUser::find($user->id);
        
        // Assert: Check if DataScopeTrait would use the correct dept_id
        // BUG: DataScopeTrait uses $loginUser->dept_id (fixed value 5) instead of
        // querying sa_system_user_dept for tenant-specific dept_id (should be 8)
        
        // We can't directly test DataScopeTrait without a model that uses it,
        // but we can verify the underlying issue: getCurrentDeptId() doesn't exist
        $this->assertTrue(
            method_exists($user, 'getCurrentDeptId'),
            'COUNTEREXAMPLE: DataScopeTrait cannot use tenant-specific dept_id because ' .
            'SysUser::getCurrentDeptId() method does not exist. ' .
            'This confirms the bug - DataScopeTrait will use fixed dept_id=' . $user->dept_id .
            ' instead of tenant-specific dept_id=8 in Tenant B.'
        );
        
        // If method exists, verify it returns correct value
        if (method_exists($user, 'getCurrentDeptId')) {
            $currentDeptId = $user->getCurrentDeptId();
            $this->assertEquals(
                8,
                $currentDeptId,
                'COUNTEREXAMPLE: In Tenant B context, getCurrentDeptId() should return 8, but got: ' . $currentDeptId .
                '. DataScopeTrait will use wrong dept_id for permission filtering.'
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
            $tenant->name = $name;
            $tenant->code = 'tenant_' . $id;
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
     * Create user-dept association in sa_system_user_dept table
     */
    private function createUserDeptAssociation(int $userId, int $tenantId, int $deptId): void
    {
        $exists = Capsule::table('sa_system_user_dept')
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->exists();
        
        if (!$exists) {
            Capsule::table('sa_system_user_dept')->insert([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'dept_id' => $deptId,
                'create_time' => time(),
                'update_time' => time(),
            ]);
        }
    }

    /**
     * Clean up test data after each test
     */
    protected function tearDown(): void
    {
        // Clean up test data
        Capsule::table('sa_system_user_dept')
            ->where('user_id', 999)
            ->delete();
        
        Capsule::table('sa_system_user_tenant')
            ->where('user_id', 999)
            ->delete();
        
        SysUser::where('id', 999)->forceDelete();
        
        // Note: We don't delete tenants and depts as they might be used by other tests
        
        parent::tearDown();
    }
}
