<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Services\RedisMonitorService;
use App\Controllers\ServerController;
use Symfony\Component\HttpFoundation\Request;

/**
 * RedisMonitoringIntegrationTest
 * 
 * Integration test for Task 5: Final checkpoint - Integration testing
 * 
 * Tests:
 * - Complete data flow from frontend to backend
 * - Data structure matches Vue component expectations
 * - All required metrics are present and correctly formatted
 * - Error states are handled gracefully
 */
class RedisMonitoringIntegrationTest extends TestCase
{
    private RedisMonitorService $service;
    private ?ServerController $controller = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RedisMonitorService();
    }

    /**
     * Test 1: Complete data flow - Service returns correct structure
     */
    public function test_service_returns_complete_data_structure(): void
    {
        $data = $this->service->getFullInfo();

        // Assert top-level properties exist
        $this->assertArrayHasKey('uptime_in_seconds', $data, 'Missing uptime_in_seconds');
        $this->assertArrayHasKey('uptime_in_days', $data, 'Missing uptime_in_days');
        $this->assertArrayHasKey('connected_clients', $data, 'Missing connected_clients');
        $this->assertArrayHasKey('used_memory', $data, 'Missing used_memory');
        $this->assertArrayHasKey('variable', $data, 'Missing variable object');

        // Assert variable is an array
        $this->assertIsArray($data['variable'], 'variable should be an array');
    }

    /**
     * Test 2: Top-level properties have correct types
     */
    public function test_top_level_properties_have_correct_types(): void
    {
        $data = $this->service->getFullInfo();

        // uptime_in_seconds should be integer
        $this->assertIsInt($data['uptime_in_seconds'], 'uptime_in_seconds should be integer');

        // uptime_in_days should be numeric (int or float)
        $this->assertIsNumeric($data['uptime_in_days'], 'uptime_in_days should be numeric');

        // connected_clients should be integer
        $this->assertIsInt($data['connected_clients'], 'connected_clients should be integer');

        // used_memory should be formatted string
        $this->assertIsString($data['used_memory'], 'used_memory should be string');
        $this->assertMatchesRegularExpression('/^\d+(\.\d+)?\s+(B|KB|MB|GB|TB)$/', $data['used_memory'], 
            'used_memory should be formatted like "2.5 MB"');
    }

    /**
     * Test 3: Variable object contains all required memory metrics
     */
    public function test_variable_contains_memory_metrics(): void
    {
        $data = $this->service->getFullInfo();
        $variable = $data['variable'];

        $requiredMemoryMetrics = [
            'used_memory',
            'used_memory_peak',
            'used_memory_rss',
            'mem_fragmentation_ratio',
        ];

        foreach ($requiredMemoryMetrics as $metric) {
            $this->assertArrayHasKey($metric, $variable, "Missing memory metric: $metric");
        }

        // Memory values should be numeric (raw bytes)
        $this->assertIsNumeric($variable['used_memory'], 'used_memory in variable should be numeric');
        $this->assertIsNumeric($variable['used_memory_peak'], 'used_memory_peak should be numeric');
        $this->assertIsNumeric($variable['used_memory_rss'], 'used_memory_rss should be numeric');
        $this->assertIsNumeric($variable['mem_fragmentation_ratio'], 'mem_fragmentation_ratio should be numeric');
    }

    /**
     * Test 4: Variable object contains all required cache efficiency metrics
     */
    public function test_variable_contains_cache_metrics(): void
    {
        $data = $this->service->getFullInfo();
        $variable = $data['variable'];

        $requiredCacheMetrics = [
            'keyspace_hits',
            'keyspace_misses',
            'expired_keys',
            'evicted_keys',
        ];

        foreach ($requiredCacheMetrics as $metric) {
            $this->assertArrayHasKey($metric, $variable, "Missing cache metric: $metric");
            $this->assertIsInt($variable[$metric], "$metric should be integer");
        }
    }

    /**
     * Test 5: Variable object contains all required performance metrics
     */
    public function test_variable_contains_performance_metrics(): void
    {
        $data = $this->service->getFullInfo();
        $variable = $data['variable'];

        $requiredPerformanceMetrics = [
            'instantaneous_ops_per_sec',
            'instantaneous_input_kbps',
            'instantaneous_output_kbps',
            'total_commands_processed',
        ];

        foreach ($requiredPerformanceMetrics as $metric) {
            $this->assertArrayHasKey($metric, $variable, "Missing performance metric: $metric");
            $this->assertIsNumeric($variable[$metric], "$metric should be numeric");
        }
    }

    /**
     * Test 6: Variable object contains all required configuration metrics
     */
    public function test_variable_contains_configuration_metrics(): void
    {
        $data = $this->service->getFullInfo();
        $variable = $data['variable'];

        $requiredConfigMetrics = [
            'redis_version' => 'string',
            'redis_mode' => 'string',
            'os' => 'string',
            'arch_bits' => 'int',
            'mem_allocator' => 'string',
            'role' => 'string',
            'tcp_port' => 'int',
            'aof_enabled' => 'int',
            'rdb_changes_since_last_save' => 'int',
            'total_connections_received' => 'int',
        ];

        foreach ($requiredConfigMetrics as $metric => $expectedType) {
            $this->assertArrayHasKey($metric, $variable, "Missing config metric: $metric");
            
            if ($expectedType === 'string') {
                $this->assertIsString($variable[$metric], "$metric should be string");
            } elseif ($expectedType === 'int') {
                $this->assertIsInt($variable[$metric], "$metric should be integer");
            }
        }
    }

    /**
     * Test 7: Uptime calculation is correct
     */
    public function test_uptime_calculation_is_correct(): void
    {
        $data = $this->service->getFullInfo();

        $uptime_in_seconds = $data['uptime_in_seconds'];
        $uptime_in_days = $data['uptime_in_days'];

        // Calculate expected days
        $expectedDays = floor($uptime_in_seconds / 86400);

        $this->assertEquals($expectedDays, $uptime_in_days, 
            'uptime_in_days should be calculated as uptime_in_seconds / 86400');
    }

    /**
     * Test 8: Memory formatting is consistent
     */
    public function test_memory_formatting_is_consistent(): void
    {
        $data = $this->service->getFullInfo();

        // Top-level used_memory should be formatted
        $this->assertIsString($data['used_memory'], 'Top-level used_memory should be formatted string');

        // Variable used_memory should be raw bytes
        $this->assertIsNumeric($data['variable']['used_memory'], 'Variable used_memory should be numeric bytes');

        // If Redis is connected, verify the values are related
        if (!isset($data['error']) || !$data['error']) {
            // The numeric value should be reasonable (not negative)
            $this->assertGreaterThanOrEqual(0, $data['variable']['used_memory'], 
                'Memory usage should not be negative');
        }
    }

    /**
     * Test 9: Error handling - Service returns default values when disconnected
     */
    public function test_error_handling_returns_default_values(): void
    {
        $data = $this->service->getFullInfo();

        // Even if Redis is disconnected, all required keys should exist
        $this->assertArrayHasKey('uptime_in_seconds', $data);
        $this->assertArrayHasKey('uptime_in_days', $data);
        $this->assertArrayHasKey('connected_clients', $data);
        $this->assertArrayHasKey('used_memory', $data);
        $this->assertArrayHasKey('variable', $data);

        // If error indicator is present, verify default values
        if (isset($data['error']) && $data['error']) {
            $this->assertEquals(0, $data['uptime_in_seconds'], 'Default uptime_in_seconds should be 0');
            $this->assertEquals(0, $data['uptime_in_days'], 'Default uptime_in_days should be 0');
            $this->assertEquals(0, $data['connected_clients'], 'Default connected_clients should be 0');
            $this->assertIsString($data['used_memory'], 'Default used_memory should be string');
        }
    }

    /**
     * Test 10: Data structure matches Vue component expectations
     */
    public function test_data_structure_matches_vue_component_expectations(): void
    {
        $data = $this->service->getFullInfo();

        // Vue component expects these exact keys for core metrics
        $this->assertArrayHasKey('uptime_in_seconds', $data, 'Vue needs uptime_in_seconds for formatUptime()');
        $this->assertArrayHasKey('uptime_in_days', $data, 'Vue needs uptime_in_days for metric card');
        $this->assertArrayHasKey('connected_clients', $data, 'Vue needs connected_clients for metric card');
        $this->assertArrayHasKey('used_memory', $data, 'Vue needs used_memory for metric card');

        // Vue component expects variable object for detailed metrics
        $this->assertArrayHasKey('variable', $data, 'Vue needs variable object');
        $variable = $data['variable'];

        // Vue component uses these for memory chart
        $this->assertArrayHasKey('used_memory', $variable, 'Vue needs variable.used_memory for chart');
        $this->assertArrayHasKey('used_memory_peak', $variable, 'Vue needs variable.used_memory_peak for chart');

        // Vue component uses these for hit rate calculation
        $this->assertArrayHasKey('keyspace_hits', $variable, 'Vue needs keyspace_hits for hit rate');
        $this->assertArrayHasKey('keyspace_misses', $variable, 'Vue needs keyspace_misses for hit rate');

        // Vue component uses these for performance metrics
        $this->assertArrayHasKey('instantaneous_ops_per_sec', $variable, 'Vue needs OPS for performance card');
        $this->assertArrayHasKey('instantaneous_input_kbps', $variable, 'Vue needs input traffic');
        $this->assertArrayHasKey('instantaneous_output_kbps', $variable, 'Vue needs output traffic');
        $this->assertArrayHasKey('total_commands_processed', $variable, 'Vue needs total commands');
    }

    /**
     * Test 11: All numeric values are non-negative
     */
    public function test_all_numeric_values_are_non_negative(): void
    {
        $data = $this->service->getFullInfo();

        // Top-level numeric values
        $this->assertGreaterThanOrEqual(0, $data['uptime_in_seconds'], 'uptime_in_seconds should be non-negative');
        $this->assertGreaterThanOrEqual(0, $data['uptime_in_days'], 'uptime_in_days should be non-negative');
        $this->assertGreaterThanOrEqual(0, $data['connected_clients'], 'connected_clients should be non-negative');

        // Variable numeric values
        $variable = $data['variable'];
        $numericMetrics = [
            'used_memory', 'used_memory_peak', 'used_memory_rss',
            'keyspace_hits', 'keyspace_misses', 'expired_keys', 'evicted_keys',
            'instantaneous_ops_per_sec', 'total_commands_processed',
            'arch_bits', 'tcp_port', 'aof_enabled', 'rdb_changes_since_last_save',
            'total_connections_received',
        ];

        foreach ($numericMetrics as $metric) {
            if (isset($variable[$metric])) {
                $this->assertGreaterThanOrEqual(0, $variable[$metric], 
                    "$metric should be non-negative, got: {$variable[$metric]}");
            }
        }
    }

    /**
     * Test 12: Response is JSON serializable
     */
    public function test_response_is_json_serializable(): void
    {
        $data = $this->service->getFullInfo();

        // Attempt to encode as JSON
        $json = json_encode($data);
        $this->assertNotFalse($json, 'Data should be JSON serializable');

        // Decode and verify structure is preserved
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded, 'Decoded JSON should be an array');
        $this->assertArrayHasKey('variable', $decoded, 'variable should be preserved in JSON');
    }

    /**
     * Test 13: Frontend API service file exists
     */
    public function test_frontend_api_service_exists(): void
    {
        $apiServicePath = __DIR__ . '/../../web/src/api/monitor/redis.ts';
        $this->assertFileExists($apiServicePath, 'Frontend API service file should exist at web/src/api/monitor/redis.ts');
    }

    /**
     * Test 14: Frontend API service has correct structure
     */
    public function test_frontend_api_service_has_correct_structure(): void
    {
        $apiServicePath = __DIR__ . '/../../web/src/api/monitor/redis.ts';
        
        if (!file_exists($apiServicePath)) {
            $this->markTestSkipped('Frontend API service file does not exist');
        }

        $content = file_get_contents($apiServicePath);
        
        // Check for required imports
        $this->assertStringContainsString('import', $content, 'Should have import statement');
        $this->assertStringContainsString('request', $content, 'Should import request utility');
        
        // Check for export
        $this->assertStringContainsString('export', $content, 'Should have export statement');
        
        // Check for list method
        $this->assertStringContainsString('list', $content, 'Should have list() method');
        
        // Check for correct endpoint
        $this->assertStringContainsString('/api/core/server/redis', $content, 'Should call correct endpoint');
    }

    /**
     * Test 15: Vue component file exists
     */
    public function test_vue_component_exists(): void
    {
        $componentPath = __DIR__ . '/../../web/src/views/safeguard/redis/index.vue';
        $this->assertFileExists($componentPath, 'Vue component should exist at web/src/views/safeguard/redis/index.vue');
    }
}
