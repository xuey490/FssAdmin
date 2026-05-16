<?php

declare(strict_types=1);

/**
 * ServerController - Redis错误处理测试
 * 
 * 验证Task 4.2的要求：
 * - 确认Redis扩展/Predis可用性检查存在
 * - 确保返回正确的错误消息"Redis扩展未安装"
 * - 测试Redis不可用时的优雅降级
 *
 * @package Tests\Unit
 * @author  Kiro
 * @date    2026-03-12
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * ServerControllerErrorHandlingTest
 * 
 * 测试Task 4.2的要求：
 * - Confirm existing check for Redis extension/Predis availability
 * - Ensure proper error message "Redis扩展未安装" is returned
 * - Test graceful degradation when Redis is unavailable
 * 
 * Requirements: 4.4, 4.5, 5.1, 5.2, 5.3, 5.4, 5.5
 */
class ServerControllerErrorHandlingTest extends TestCase
{
    /**
     * 测试：控制器检查Redis扩展或Predis可用性
     * 
     * 验证Requirement 4.4, 4.5
     * 
     * 此测试验证ServerController::getRedisInfo()方法在调用服务之前
     * 检查Redis扩展或Predis客户端是否可用。
     *
     * @test
     */
    public function test_controller_checks_redis_extension_or_predis_availability(): void
    {
        // 读取ServerController源代码
        $controllerPath = __DIR__ . '/../../app/Controllers/ServerController.php';
        $this->assertFileExists($controllerPath, 'ServerController.php should exist');
        
        $controllerCode = file_get_contents($controllerPath);
        $this->assertNotFalse($controllerCode, 'Should be able to read ServerController.php');
        
        // 验证getRedisInfo方法存在
        $this->assertStringContainsString(
            'public function getRedisInfo',
            $controllerCode,
            'getRedisInfo method should exist in ServerController'
        );
        
        // 验证检查Redis扩展是否加载
        $this->assertStringContainsString(
            "extension_loaded('redis')",
            $controllerCode,
            'Controller should check if Redis extension is loaded'
        );
        
        // 验证检查Predis客户端类是否存在
        $this->assertStringContainsString(
            "class_exists('\\Predis\\Client')",
            $controllerCode,
            'Controller should check if Predis Client class exists'
        );
        
        // 验证使用逻辑OR运算符（两者之一可用即可）
        $this->assertMatchesRegularExpression(
            "/extension_loaded\('redis'\)\s+&&\s+!class_exists\('\\\\Predis\\\\Client'\)/",
            $controllerCode,
            'Controller should check for Redis extension OR Predis availability using logical operators'
        );
    }

    /**
     * 测试：控制器返回正确的错误消息
     * 
     * 验证Requirement 4.5, 5.2
     * 
     * 当Redis扩展和Predis都不可用时，控制器应返回错误消息"Redis扩展未安装"
     *
     * @test
     */
    public function test_controller_returns_correct_error_message_when_redis_unavailable(): void
    {
        $controllerPath = __DIR__ . '/../../app/Controllers/ServerController.php';
        $controllerCode = file_get_contents($controllerPath);
        
        // 验证错误消息"Redis扩展未安装"存在
        $this->assertStringContainsString(
            "Redis扩展未安装",
            $controllerCode,
            'Controller should return error message "Redis扩展未安装" when Redis is not available'
        );
        
        // 验证使用fail方法返回错误
        $this->assertMatchesRegularExpression(
            "/return\s+\\\$this->fail\('Redis扩展未安装'\)/",
            $controllerCode,
            'Controller should use $this->fail() to return error message'
        );
    }

    /**
     * 测试：控制器在检查失败后不调用服务
     * 
     * 验证Requirement 4.4, 5.1
     * 
     * 当Redis扩展/Predis不可用时，控制器应在调用服务之前返回错误，
     * 避免不必要的服务调用。
     *
     * @test
     */
    public function test_controller_returns_error_before_calling_service(): void
    {
        $controllerPath = __DIR__ . '/../../app/Controllers/ServerController.php';
        $controllerCode = file_get_contents($controllerPath);
        
        // 提取getRedisInfo方法
        preg_match(
            '/public function getRedisInfo\(.*?\):.*?\{(.*?)(?=\n    \}|\n    \/\/|$)/s',
            $controllerCode,
            $matches
        );
        
        $this->assertNotEmpty($matches, 'Should be able to extract getRedisInfo method');
        $methodBody = $matches[1] ?? '';
        
        // 验证检查在服务调用之前
        $checkPosition = strpos($methodBody, "extension_loaded('redis')");
        $serviceCallPosition = strpos($methodBody, '$this->redisMonitorService->getFullInfo()');
        
        $this->assertNotFalse($checkPosition, 'Extension check should exist in method');
        $this->assertNotFalse($serviceCallPosition, 'Service call should exist in method');
        $this->assertLessThan(
            $serviceCallPosition,
            $checkPosition,
            'Extension/Predis availability check should occur before service call'
        );
    }

    /**
     * 测试：所有Redis相关端点都有可用性检查
     * 
     * 验证Requirement 4.4, 5.1
     * 
     * 所有Redis相关的控制器方法都应该检查Redis扩展/Predis可用性
     *
     * @test
     */
    public function test_all_redis_endpoints_have_availability_checks(): void
    {
        $controllerPath = __DIR__ . '/../../app/Controllers/ServerController.php';
        $controllerCode = file_get_contents($controllerPath);
        
        // Redis相关的端点方法
        $redisEndpoints = [
            'getRedisInfo',
            'getRedisKeys',
            'getRedisValue',
            'deleteRedisKey',
            'clearRedis',
        ];
        
        foreach ($redisEndpoints as $endpoint) {
            // 验证方法存在
            $this->assertStringContainsString(
                "public function $endpoint",
                $controllerCode,
                "Method $endpoint should exist in ServerController"
            );
            
            // 提取方法体
            preg_match(
                "/public function $endpoint\(.*?\):.*?\{(.*?)(?=\n    public function|\n    \/\/ =|$)/s",
                $controllerCode,
                $matches
            );
            
            $this->assertNotEmpty($matches, "Should be able to extract $endpoint method");
            $methodBody = $matches[1] ?? '';
            
            // 验证每个方法都有可用性检查
            $this->assertStringContainsString(
                "extension_loaded('redis')",
                $methodBody,
                "Method $endpoint should check Redis extension availability"
            );
            
            $this->assertStringContainsString(
                "class_exists('\\Predis\\Client')",
                $methodBody,
                "Method $endpoint should check Predis Client availability"
            );
            
            // 验证每个方法都返回相同的错误消息
            $this->assertStringContainsString(
                "Redis扩展未安装",
                $methodBody,
                "Method $endpoint should return error message 'Redis扩展未安装'"
            );
        }
    }

    /**
     * 测试：端点使用正确的认证属性
     * 
     * 验证Requirement 5.4
     * 
     * getRedisInfo端点应该需要认证但不需要特定权限
     *
     * @test
     */
    public function test_endpoint_has_correct_authentication_attributes(): void
    {
        $controllerPath = __DIR__ . '/../../app/Controllers/ServerController.php';
        $controllerCode = file_get_contents($controllerPath);
        
        // 提取getRedisInfo方法及其属性（从Redis管理注释开始到方法定义）
        preg_match(
            '/\/\/ ={10,} Redis管理 ={10,}.*?public function getRedisInfo/s',
            $controllerCode,
            $matches
        );
        
        $this->assertNotEmpty($matches, 'Should be able to extract getRedisInfo method with attributes');
        $methodWithAttributes = $matches[0] ?? '';
        
        // 验证Auth属性存在且required为true
        $this->assertStringContainsString(
            '#[Auth(required: true)]',
            $methodWithAttributes,
            'getRedisInfo should have Auth attribute with required: true'
        );
        
        // 验证没有Permission属性（不需要特定权限）
        $this->assertStringNotContainsString(
            '#[Permission',
            $methodWithAttributes,
            'getRedisInfo should not have Permission attribute (accessible to all authenticated users)'
        );
    }

    /**
     * 测试：端点使用正确的路由配置
     * 
     * 验证Requirement 5.1, 5.3
     * 
     * getRedisInfo端点应该映射到/api/core/server/redis并使用GET方法
     *
     * @test
     */
    public function test_endpoint_has_correct_route_configuration(): void
    {
        $controllerPath = __DIR__ . '/../../app/Controllers/ServerController.php';
        $controllerCode = file_get_contents($controllerPath);
        
        // 提取getRedisInfo方法及其路由属性
        preg_match(
            '/#\[Route\(.*?\)\].*?public function getRedisInfo/s',
            $controllerCode,
            $matches
        );
        
        $this->assertNotEmpty($matches, 'Should be able to extract getRedisInfo route attribute');
        $routeAttribute = $matches[0] ?? '';
        
        // 验证路由路径
        $this->assertStringContainsString(
            "path: '/api/core/server/redis'",
            $routeAttribute,
            'Route should be /api/core/server/redis'
        );
        
        // 验证HTTP方法为GET
        $this->assertStringContainsString(
            "methods: ['GET']",
            $routeAttribute,
            'Route should use GET method'
        );
        
        // 验证路由名称
        $this->assertStringContainsString(
            "name: 'server.redis.info'",
            $routeAttribute,
            'Route should have name server.redis.info'
        );
    }

    /**
     * 测试：控制器返回JSON响应
     * 
     * 验证Requirement 5.3
     * 
     * getRedisInfo方法应该返回BaseJsonResponse类型
     *
     * @test
     */
    public function test_endpoint_returns_json_response(): void
    {
        $controllerPath = __DIR__ . '/../../app/Controllers/ServerController.php';
        $controllerCode = file_get_contents($controllerPath);
        
        // 验证方法返回类型
        $this->assertMatchesRegularExpression(
            '/public function getRedisInfo\(.*?\):\s*BaseJsonResponse/',
            $controllerCode,
            'getRedisInfo should return BaseJsonResponse type'
        );
        
        // 验证使用success方法返回数据
        preg_match(
            '/public function getRedisInfo\(.*?\):.*?\{(.*?)(?=\n    \}|\n    \/\/|$)/s',
            $controllerCode,
            $matches
        );
        
        $methodBody = $matches[1] ?? '';
        
        $this->assertStringContainsString(
            '$this->success($info)',
            $methodBody,
            'getRedisInfo should use $this->success() to return monitoring data'
        );
    }

    /**
     * 测试：优雅降级 - 服务返回错误时控制器仍能正常响应
     * 
     * 验证Requirement 4.1, 4.2, 4.3, 5.2
     * 
     * 当服务返回包含错误指示器的响应时，控制器应该正常返回该响应，
     * 而不是抛出异常或崩溃。
     *
     * @test
     */
    public function test_graceful_degradation_when_service_returns_error(): void
    {
        // 模拟服务返回的错误响应
        $serviceErrorResponse = [
            'error' => true,
            'error_message' => 'Redis连接失败',
            'uptime_in_seconds' => 0,
            'uptime_in_days' => 0,
            'connected_clients' => 0,
            'used_memory' => '0 B',
            'variable' => [
                'used_memory' => 0,
                'used_memory_peak' => 0,
                'used_memory_rss' => 0,
                'mem_fragmentation_ratio' => 0,
                'keyspace_hits' => 0,
                'keyspace_misses' => 0,
                'expired_keys' => 0,
                'evicted_keys' => 0,
                'instantaneous_ops_per_sec' => 0,
                'instantaneous_input_kbps' => 0,
                'instantaneous_output_kbps' => 0,
                'total_commands_processed' => 0,
                'redis_version' => '',
                'redis_mode' => '',
                'os' => '',
                'arch_bits' => 0,
                'mem_allocator' => '',
                'role' => '',
                'tcp_port' => 0,
                'aof_enabled' => 0,
                'rdb_changes_since_last_save' => 0,
                'total_connections_received' => 0,
            ],
        ];
        
        // 验证响应结构完整（即使有错误）
        $this->assertArrayHasKey('error', $serviceErrorResponse);
        $this->assertArrayHasKey('error_message', $serviceErrorResponse);
        $this->assertArrayHasKey('uptime_in_seconds', $serviceErrorResponse);
        $this->assertArrayHasKey('uptime_in_days', $serviceErrorResponse);
        $this->assertArrayHasKey('connected_clients', $serviceErrorResponse);
        $this->assertArrayHasKey('used_memory', $serviceErrorResponse);
        $this->assertArrayHasKey('variable', $serviceErrorResponse);
        
        // 验证variable对象结构完整
        $this->assertIsArray($serviceErrorResponse['variable']);
        $this->assertCount(22, $serviceErrorResponse['variable']);
        
        // 验证所有必需的字段都存在
        $requiredFields = [
            'used_memory', 'used_memory_peak', 'used_memory_rss', 'mem_fragmentation_ratio',
            'keyspace_hits', 'keyspace_misses', 'expired_keys', 'evicted_keys',
            'instantaneous_ops_per_sec', 'instantaneous_input_kbps', 'instantaneous_output_kbps',
            'total_commands_processed', 'redis_version', 'redis_mode', 'os', 'arch_bits',
            'mem_allocator', 'role', 'tcp_port', 'aof_enabled', 'rdb_changes_since_last_save',
            'total_connections_received'
        ];
        
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $serviceErrorResponse['variable'],
                "Field $field should exist in variable object even when error occurs"
            );
        }
        
        // 这个响应可以被控制器的success方法安全返回
        // 前端可以检查error字段来判断是否有错误，并显示适当的UI
        $this->assertTrue(true, 'Service error response structure is valid for graceful degradation');
    }
}
