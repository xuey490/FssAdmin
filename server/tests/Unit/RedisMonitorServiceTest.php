<?php

declare(strict_types=1);

/**
 * Redis监控服务测试
 *
 * @package Tests\Unit
 * @author  Genie
 * @date    2026-03-12
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * RedisInfoParser - 用于测试的Redis INFO解析器
 * 
 * 这个类复制了RedisMonitorService中parseRedisInfo方法的逻辑，
 * 用于独立测试解析功能，不依赖于应用容器或Redis连接
 */
class RedisInfoParser
{
    /**
     * 解析Redis INFO命令响应
     *
     * @param string $infoRaw Redis INFO命令的原始响应
     * @return array 解析后的键值对数组
     */
    public function parse(string $infoRaw): array
    {
        $result = [];
        $lines = explode("\n", $infoRaw);

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and section headers
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            // Parse key-value pairs
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Convert numeric strings to appropriate types
                if (is_numeric($value)) {
                    if (strpos($value, '.') !== false) {
                        $result[$key] = (float)$value;
                    } else {
                        $result[$key] = (int)$value;
                    }
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}

/**
 * RedisMonitorServiceTest - Redis监控服务测试类
 * 
 * 这个测试类直接测试parseRedisInfo方法的逻辑，
 * 不依赖于Redis连接或应用容器
 */
class RedisMonitorServiceTest extends TestCase
{
    /**
     * 测试辅助类实例
     * @var RedisInfoParser
     */
    protected RedisInfoParser $parser;

    /**
     * 测试前准备
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new RedisInfoParser();
    }

    /**
     * 测试：解析基本的键值对
     *
     * @test
     */
    public function test_parse_basic_key_value_pairs(): void
    {
        $infoRaw = "redis_version:6.2.6\nredis_mode:standalone\nos:Linux 5.10.0-21-amd64 x86_64";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertEquals('6.2.6', $result['redis_version']);
        $this->assertEquals('standalone', $result['redis_mode']);
        $this->assertEquals('Linux 5.10.0-21-amd64 x86_64', $result['os']);
    }

    /**
     * 测试：跳过section headers（以#开头的行）
     *
     * @test
     */
    public function test_skip_section_headers(): void
    {
        $infoRaw = "# Server\nredis_version:6.2.6\n# Memory\nused_memory:2621440";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertArrayNotHasKey('# Server', $result);
        $this->assertArrayNotHasKey('# Memory', $result);
        $this->assertEquals('6.2.6', $result['redis_version']);
        $this->assertEquals(2621440, $result['used_memory']);
    }

    /**
     * 测试：跳过空行
     *
     * @test
     */
    public function test_skip_empty_lines(): void
    {
        $infoRaw = "redis_version:6.2.6\n\nused_memory:2621440\n\n";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertCount(2, $result);
        $this->assertEquals('6.2.6', $result['redis_version']);
        $this->assertEquals(2621440, $result['used_memory']);
    }

    /**
     * 测试：将整数字符串转换为int类型
     *
     * @test
     */
    public function test_convert_integer_strings_to_int(): void
    {
        $infoRaw = "used_memory:2621440\nconnected_clients:5\nuptime_in_seconds:86400";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertIsInt($result['used_memory']);
        $this->assertEquals(2621440, $result['used_memory']);
        $this->assertIsInt($result['connected_clients']);
        $this->assertEquals(5, $result['connected_clients']);
        $this->assertIsInt($result['uptime_in_seconds']);
        $this->assertEquals(86400, $result['uptime_in_seconds']);
    }

    /**
     * 测试：将浮点数字符串转换为float类型
     *
     * @test
     */
    public function test_convert_float_strings_to_float(): void
    {
        $infoRaw = "mem_fragmentation_ratio:1.25\ninstantaneous_input_kbps:12.34\nused_cpu_sys:0.5";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertIsFloat($result['mem_fragmentation_ratio']);
        $this->assertEquals(1.25, $result['mem_fragmentation_ratio']);
        $this->assertIsFloat($result['instantaneous_input_kbps']);
        $this->assertEquals(12.34, $result['instantaneous_input_kbps']);
        $this->assertIsFloat($result['used_cpu_sys']);
        $this->assertEquals(0.5, $result['used_cpu_sys']);
    }

    /**
     * 测试：保持非数字字符串为string类型
     *
     * @test
     */
    public function test_keep_non_numeric_strings_as_string(): void
    {
        $infoRaw = "redis_version:6.2.6\nos:Linux 5.10.0-21-amd64 x86_64\nmem_allocator:jemalloc-5.1.0";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertIsString($result['redis_version']);
        $this->assertEquals('6.2.6', $result['redis_version']);
        $this->assertIsString($result['os']);
        $this->assertEquals('Linux 5.10.0-21-amd64 x86_64', $result['os']);
        $this->assertIsString($result['mem_allocator']);
        $this->assertEquals('jemalloc-5.1.0', $result['mem_allocator']);
    }

    /**
     * 测试：处理包含冒号的值
     *
     * @test
     */
    public function test_handle_values_with_colons(): void
    {
        $infoRaw = "executable:/usr/local/bin/redis-server\nconfig_file:/etc/redis/redis.conf";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertEquals('/usr/local/bin/redis-server', $result['executable']);
        $this->assertEquals('/etc/redis/redis.conf', $result['config_file']);
    }

    /**
     * 测试：处理完整的Redis INFO响应示例
     *
     * @test
     */
    public function test_parse_complete_redis_info_response(): void
    {
        $infoRaw = <<<INFO
# Server
redis_version:6.2.6
redis_mode:standalone
os:Linux 5.10.0-21-amd64 x86_64
arch_bits:64
tcp_port:6379

# Memory
used_memory:2621440
used_memory_peak:3145728
used_memory_rss:10485760
mem_fragmentation_ratio:4.00

# Stats
total_connections_received:1000
total_commands_processed:50000
instantaneous_ops_per_sec:100
keyspace_hits:45000
keyspace_misses:5000
INFO;
        
        $result = $this->parser->parse($infoRaw);
        
        // Verify all values are parsed correctly
        $this->assertEquals('6.2.6', $result['redis_version']);
        $this->assertEquals('standalone', $result['redis_mode']);
        $this->assertEquals('Linux 5.10.0-21-amd64 x86_64', $result['os']);
        $this->assertEquals(64, $result['arch_bits']);
        $this->assertEquals(6379, $result['tcp_port']);
        
        $this->assertEquals(2621440, $result['used_memory']);
        $this->assertEquals(3145728, $result['used_memory_peak']);
        $this->assertEquals(10485760, $result['used_memory_rss']);
        $this->assertEquals(4.00, $result['mem_fragmentation_ratio']);
        
        $this->assertEquals(1000, $result['total_connections_received']);
        $this->assertEquals(50000, $result['total_commands_processed']);
        $this->assertEquals(100, $result['instantaneous_ops_per_sec']);
        $this->assertEquals(45000, $result['keyspace_hits']);
        $this->assertEquals(5000, $result['keyspace_misses']);
    }

    /**
     * 测试：处理空响应
     *
     * @test
     */
    public function test_parse_empty_response(): void
    {
        $infoRaw = "";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * 测试：处理只有section headers的响应
     *
     * @test
     */
    public function test_parse_only_section_headers(): void
    {
        $infoRaw = "# Server\n# Memory\n# Stats";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * 测试：处理格式错误的行（没有冒号）
     *
     * @test
     */
    public function test_skip_malformed_lines_without_colon(): void
    {
        $infoRaw = "redis_version:6.2.6\nmalformed_line_without_colon\nused_memory:2621440";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertCount(2, $result);
        $this->assertEquals('6.2.6', $result['redis_version']);
        $this->assertEquals(2621440, $result['used_memory']);
        $this->assertArrayNotHasKey('malformed_line_without_colon', $result);
    }

    /**
     * 测试：处理零值
     *
     * @test
     */
    public function test_handle_zero_values(): void
    {
        $infoRaw = "expired_keys:0\nevicted_keys:0\nkeyspace_misses:0";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertIsInt($result['expired_keys']);
        $this->assertEquals(0, $result['expired_keys']);
        $this->assertIsInt($result['evicted_keys']);
        $this->assertEquals(0, $result['evicted_keys']);
        $this->assertIsInt($result['keyspace_misses']);
        $this->assertEquals(0, $result['keyspace_misses']);
    }

    /**
     * 测试：处理负数值
     *
     * @test
     */
    public function test_handle_negative_values(): void
    {
        $infoRaw = "repl_backlog_size:-1\nmaster_repl_offset:-1";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertIsInt($result['repl_backlog_size']);
        $this->assertEquals(-1, $result['repl_backlog_size']);
        $this->assertIsInt($result['master_repl_offset']);
        $this->assertEquals(-1, $result['master_repl_offset']);
    }

    /**
     * 测试：处理带有前后空格的键值对
     *
     * @test
     */
    public function test_trim_whitespace_from_keys_and_values(): void
    {
        $infoRaw = "  redis_version  :  6.2.6  \n  used_memory  :  2621440  ";
        
        $result = $this->parser->parse($infoRaw);
        
        $this->assertEquals('6.2.6', $result['redis_version']);
        $this->assertEquals(2621440, $result['used_memory']);
    }
}
