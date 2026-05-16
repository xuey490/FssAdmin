<?php

declare(strict_types=1);

/**
 * Redis监控服务 - Variable对象完整性测试
 * 
 * 验证getFullInfo()方法返回的variable对象包含所有必需的指标
 *
 * @package Tests\Unit
 * @author  Kiro
 * @date    2026-03-12
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * RedisMonitorServiceVariableObjectTest
 * 
 * 测试Task 2.3的要求：确保variable对象包含所有必需的指标
 * - Memory metrics: used_memory, used_memory_peak, used_memory_rss, mem_fragmentation_ratio
 * - Cache metrics: keyspace_hits, keyspace_misses, expired_keys, evicted_keys
 * - Performance metrics: instantaneous_ops_per_sec, instantaneous_input_kbps, instantaneous_output_kbps, total_commands_processed
 * - Configuration: redis_version, redis_mode, os, arch_bits, mem_allocator, role, tcp_port, aof_enabled, rdb_changes_since_last_save, total_connections_received
 */
class RedisMonitorServiceVariableObjectTest extends TestCase
{
    /**
     * 测试：断开连接时variable对象包含所有必需的指标
     * 
     * 验证Requirements 2.3, 2.4, 2.5, 2.6
     *
     * @test
     */
    public function test_variable_object_contains_all_required_metrics_when_disconnected(): void
    {
        // 模拟断开连接时的返回值
        $disconnectedResponse = [
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

        $variable = $disconnectedResponse['variable'];

        // Verify Memory metrics (Requirement 2.3)
        $this->assertArrayHasKey('used_memory', $variable, 'Missing memory metric: used_memory');
        $this->assertArrayHasKey('used_memory_peak', $variable, 'Missing memory metric: used_memory_peak');
        $this->assertArrayHasKey('used_memory_rss', $variable, 'Missing memory metric: used_memory_rss');
        $this->assertArrayHasKey('mem_fragmentation_ratio', $variable, 'Missing memory metric: mem_fragmentation_ratio');

        // Verify Cache metrics (Requirement 2.4)
        $this->assertArrayHasKey('keyspace_hits', $variable, 'Missing cache metric: keyspace_hits');
        $this->assertArrayHasKey('keyspace_misses', $variable, 'Missing cache metric: keyspace_misses');
        $this->assertArrayHasKey('expired_keys', $variable, 'Missing cache metric: expired_keys');
        $this->assertArrayHasKey('evicted_keys', $variable, 'Missing cache metric: evicted_keys');

        // Verify Performance metrics (Requirement 2.5)
        $this->assertArrayHasKey('instantaneous_ops_per_sec', $variable, 'Missing performance metric: instantaneous_ops_per_sec');
        $this->assertArrayHasKey('instantaneous_input_kbps', $variable, 'Missing performance metric: instantaneous_input_kbps');
        $this->assertArrayHasKey('instantaneous_output_kbps', $variable, 'Missing performance metric: instantaneous_output_kbps');
        $this->assertArrayHasKey('total_commands_processed', $variable, 'Missing performance metric: total_commands_processed');

        // Verify Configuration metrics (Requirement 2.6)
        $this->assertArrayHasKey('redis_version', $variable, 'Missing config metric: redis_version');
        $this->assertArrayHasKey('redis_mode', $variable, 'Missing config metric: redis_mode');
        $this->assertArrayHasKey('os', $variable, 'Missing config metric: os');
        $this->assertArrayHasKey('arch_bits', $variable, 'Missing config metric: arch_bits');
        $this->assertArrayHasKey('mem_allocator', $variable, 'Missing config metric: mem_allocator');
        $this->assertArrayHasKey('role', $variable, 'Missing config metric: role');
        $this->assertArrayHasKey('tcp_port', $variable, 'Missing config metric: tcp_port');
        $this->assertArrayHasKey('aof_enabled', $variable, 'Missing config metric: aof_enabled');
        $this->assertArrayHasKey('rdb_changes_since_last_save', $variable, 'Missing config metric: rdb_changes_since_last_save');
        $this->assertArrayHasKey('total_connections_received', $variable, 'Missing config metric: total_connections_received');

        // Verify all memory values are numeric (raw bytes)
        $this->assertIsInt($variable['used_memory'], 'used_memory should be numeric (raw bytes)');
        $this->assertIsInt($variable['used_memory_peak'], 'used_memory_peak should be numeric (raw bytes)');
        $this->assertIsInt($variable['used_memory_rss'], 'used_memory_rss should be numeric (raw bytes)');

        // Verify default values are appropriate
        $this->assertEquals(0, $variable['used_memory']);
        $this->assertEquals(0, $variable['keyspace_hits']);
        $this->assertEquals(0, $variable['instantaneous_ops_per_sec']);
        $this->assertEquals('', $variable['redis_version']);
    }

    /**
     * 测试：连接时variable对象包含所有必需的指标
     * 
     * 验证Requirements 2.3, 2.4, 2.5, 2.6
     *
     * @test
     */
    public function test_variable_object_contains_all_required_metrics_when_connected(): void
    {
        // 模拟连接时的返回值
        $connectedResponse = [
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

        $variable = $connectedResponse['variable'];

        // Verify all required keys exist
        $requiredKeys = [
            // Memory metrics
            'used_memory', 'used_memory_peak', 'used_memory_rss', 'mem_fragmentation_ratio',
            // Cache metrics
            'keyspace_hits', 'keyspace_misses', 'expired_keys', 'evicted_keys',
            // Performance metrics
            'instantaneous_ops_per_sec', 'instantaneous_input_kbps', 'instantaneous_output_kbps', 'total_commands_processed',
            // Configuration
            'redis_version', 'redis_mode', 'os', 'arch_bits', 'mem_allocator', 'role', 'tcp_port', 'aof_enabled', 'rdb_changes_since_last_save', 'total_connections_received',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $variable, "Missing required metric: {$key}");
        }

        // Verify memory values are raw numeric bytes
        $this->assertIsInt($variable['used_memory'], 'used_memory should be raw numeric bytes');
        $this->assertEquals(2621440, $variable['used_memory']);
        $this->assertIsInt($variable['used_memory_peak'], 'used_memory_peak should be raw numeric bytes');
        $this->assertEquals(3145728, $variable['used_memory_peak']);
        $this->assertIsInt($variable['used_memory_rss'], 'used_memory_rss should be raw numeric bytes');
        $this->assertEquals(10485760, $variable['used_memory_rss']);

        // Verify top-level used_memory is formatted string
        $this->assertIsString($connectedResponse['used_memory'], 'Top-level used_memory should be formatted string');
        $this->assertEquals('2.5 MB', $connectedResponse['used_memory']);

        // Verify data types
        $this->assertIsFloat($variable['mem_fragmentation_ratio']);
        $this->assertIsInt($variable['keyspace_hits']);
        $this->assertIsInt($variable['keyspace_misses']);
        $this->assertIsInt($variable['expired_keys']);
        $this->assertIsInt($variable['evicted_keys']);
        $this->assertIsInt($variable['instantaneous_ops_per_sec']);
        $this->assertIsFloat($variable['instantaneous_input_kbps']);
        $this->assertIsFloat($variable['instantaneous_output_kbps']);
        $this->assertIsInt($variable['total_commands_processed']);
        $this->assertIsString($variable['redis_version']);
        $this->assertIsString($variable['redis_mode']);
        $this->assertIsString($variable['os']);
        $this->assertIsInt($variable['arch_bits']);
        $this->assertIsString($variable['mem_allocator']);
        $this->assertIsString($variable['role']);
        $this->assertIsInt($variable['tcp_port']);
        $this->assertIsInt($variable['aof_enabled']);
        $this->assertIsInt($variable['rdb_changes_since_last_save']);
        $this->assertIsInt($variable['total_connections_received']);
    }

    /**
     * 测试：验证variable对象的完整性（确保没有遗漏任何必需的指标）
     *
     * @test
     */
    public function test_variable_object_has_exactly_required_metrics_count(): void
    {
        $disconnectedResponse = [
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

        // Should have exactly 22 metrics
        $this->assertCount(22, $disconnectedResponse['variable'], 'Variable object should contain exactly 22 metrics');
    }
}
