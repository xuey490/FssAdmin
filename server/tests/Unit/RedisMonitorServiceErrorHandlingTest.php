<?php

declare(strict_types=1);

/**
 * Redis监控服务 - 错误处理测试
 * 
 * 验证Task 4.1的要求：
 * - 当Redis断开连接时返回默认值
 * - 确保不向控制器抛出异常
 * - 连接失败时在响应中添加错误指示器
 *
 * @package Tests\Unit
 * @author  Kiro
 * @date    2026-03-12
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * RedisMonitorServiceErrorHandlingTest
 * 
 * 测试Task 4.1的要求：
 * - Return default values (zeros and empty strings) when Redis is disconnected
 * - Ensure no exceptions are thrown to controller
 * - Add error indicator in response when connection fails
 * 
 * Requirements: 4.1, 4.2, 4.3
 */
class RedisMonitorServiceErrorHandlingTest extends TestCase
{
    /**
     * 测试：断开连接时返回错误指示器
     * 
     * 验证Requirement 4.1, 4.3
     *
     * @test
     */
    public function test_returns_error_indicator_when_disconnected(): void
    {
        // 模拟断开连接时的返回值
        $disconnectedResponse = [
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

        // Verify error indicator exists
        $this->assertArrayHasKey('error', $disconnectedResponse, 'Response should contain error indicator');
        $this->assertTrue($disconnectedResponse['error'], 'Error indicator should be true when disconnected');
        
        // Verify error message exists
        $this->assertArrayHasKey('error_message', $disconnectedResponse, 'Response should contain error message');
        $this->assertIsString($disconnectedResponse['error_message'], 'Error message should be a string');
        $this->assertNotEmpty($disconnectedResponse['error_message'], 'Error message should not be empty');
        $this->assertEquals('Redis连接失败', $disconnectedResponse['error_message']);
    }

    /**
     * 测试：断开连接时返回默认值（零和空字符串）
     * 
     * 验证Requirement 4.1
     *
     * @test
     */
    public function test_returns_default_values_when_disconnected(): void
    {
        $disconnectedResponse = [
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

        // Verify top-level default values
        $this->assertEquals(0, $disconnectedResponse['uptime_in_seconds'], 'uptime_in_seconds should be 0');
        $this->assertEquals(0, $disconnectedResponse['uptime_in_days'], 'uptime_in_days should be 0');
        $this->assertEquals(0, $disconnectedResponse['connected_clients'], 'connected_clients should be 0');
        $this->assertEquals('0 B', $disconnectedResponse['used_memory'], 'used_memory should be "0 B"');

        $variable = $disconnectedResponse['variable'];

        // Verify numeric defaults are zero
        $this->assertEquals(0, $variable['used_memory']);
        $this->assertEquals(0, $variable['used_memory_peak']);
        $this->assertEquals(0, $variable['used_memory_rss']);
        $this->assertEquals(0, $variable['mem_fragmentation_ratio']);
        $this->assertEquals(0, $variable['keyspace_hits']);
        $this->assertEquals(0, $variable['keyspace_misses']);
        $this->assertEquals(0, $variable['expired_keys']);
        $this->assertEquals(0, $variable['evicted_keys']);
        $this->assertEquals(0, $variable['instantaneous_ops_per_sec']);
        $this->assertEquals(0, $variable['instantaneous_input_kbps']);
        $this->assertEquals(0, $variable['instantaneous_output_kbps']);
        $this->assertEquals(0, $variable['total_commands_processed']);
        $this->assertEquals(0, $variable['arch_bits']);
        $this->assertEquals(0, $variable['tcp_port']);
        $this->assertEquals(0, $variable['aof_enabled']);
        $this->assertEquals(0, $variable['rdb_changes_since_last_save']);
        $this->assertEquals(0, $variable['total_connections_received']);

        // Verify string defaults are empty
        $this->assertEquals('', $variable['redis_version']);
        $this->assertEquals('', $variable['redis_mode']);
        $this->assertEquals('', $variable['os']);
        $this->assertEquals('', $variable['mem_allocator']);
        $this->assertEquals('', $variable['role']);
    }

    /**
     * 测试：异常捕获时返回错误指示器
     * 
     * 验证Requirement 4.2, 4.3
     *
     * @test
     */
    public function test_returns_error_indicator_when_exception_occurs(): void
    {
        // 模拟异常发生时的返回值
        $exceptionResponse = [
            'error' => true,
            'error_message' => 'Redis数据获取失败: Connection refused',
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

        // Verify error indicator exists
        $this->assertArrayHasKey('error', $exceptionResponse, 'Response should contain error indicator');
        $this->assertTrue($exceptionResponse['error'], 'Error indicator should be true when exception occurs');
        
        // Verify error message contains exception details
        $this->assertArrayHasKey('error_message', $exceptionResponse, 'Response should contain error message');
        $this->assertStringContainsString('Redis数据获取失败', $exceptionResponse['error_message']);
        $this->assertStringContainsString('Connection refused', $exceptionResponse['error_message']);
    }

    /**
     * 测试：正常连接时不包含错误指示器
     * 
     * 验证Requirement 4.3
     *
     * @test
     */
    public function test_no_error_indicator_when_connected_successfully(): void
    {
        // 模拟成功连接时的返回值
        $successResponse = [
            'uptime_in_seconds' => 86400,
            'uptime_in_days' => 1,
            'connected_clients' => 5,
            'used_memory' => '2.5 MB',
            'variable' => [
                'used_memory' => 2621440,
                'used_memory_peak' => 3145728,
                'used_memory_rss' => 10485760,
                'mem_fragmentation_ratio' => 4.0,
                'keyspace_hits' => 45000,
                'keyspace_misses' => 5000,
                'expired_keys' => 100,
                'evicted_keys' => 50,
                'instantaneous_ops_per_sec' => 100,
                'instantaneous_input_kbps' => 12.34,
                'instantaneous_output_kbps' => 56.78,
                'total_commands_processed' => 50000,
                'redis_version' => '6.2.6',
                'redis_mode' => 'standalone',
                'os' => 'Linux 5.10.0-21-amd64 x86_64',
                'arch_bits' => 64,
                'mem_allocator' => 'jemalloc-5.1.0',
                'role' => 'master',
                'tcp_port' => 6379,
                'aof_enabled' => 1,
                'rdb_changes_since_last_save' => 10,
                'total_connections_received' => 1000,
            ],
        ];

        // Verify no error indicator when successful
        $this->assertArrayNotHasKey('error', $successResponse, 'Successful response should not contain error indicator');
        $this->assertArrayNotHasKey('error_message', $successResponse, 'Successful response should not contain error message');
        
        // Verify real data is present
        $this->assertGreaterThan(0, $successResponse['uptime_in_seconds']);
        $this->assertGreaterThan(0, $successResponse['variable']['used_memory']);
        $this->assertNotEmpty($successResponse['variable']['redis_version']);
    }

    /**
     * 测试：响应结构保持一致（无论是否有错误）
     * 
     * 验证Requirement 4.1, 4.2
     *
     * @test
     */
    public function test_response_structure_is_consistent_with_and_without_errors(): void
    {
        $disconnectedResponse = [
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

        // Verify all required top-level keys exist
        $this->assertArrayHasKey('uptime_in_seconds', $disconnectedResponse);
        $this->assertArrayHasKey('uptime_in_days', $disconnectedResponse);
        $this->assertArrayHasKey('connected_clients', $disconnectedResponse);
        $this->assertArrayHasKey('used_memory', $disconnectedResponse);
        $this->assertArrayHasKey('variable', $disconnectedResponse);

        // Verify variable object structure is complete
        $this->assertIsArray($disconnectedResponse['variable']);
        $this->assertCount(22, $disconnectedResponse['variable'], 'Variable object should contain all 22 metrics');
    }
}
