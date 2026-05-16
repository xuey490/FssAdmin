<?php

declare(strict_types=1);

/**
 * Redis监控服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use Framework\Basic\BaseService;

/**
 * RedisMonitorService Redis监控服务
 */
class RedisMonitorService extends BaseService
{
    /**
     * Redis客户端
     * @var mixed
     */
    protected $redis = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->initRedis();
    }

    /**
     * 初始化Redis连接
     *
     * @return void
     */
    protected function initRedis(): void
    {
        try {
            // Use the system's Redis instance
            $this->redis = app('redis');
        } catch (\Exception $e) {
            $this->redis = null;
        }
    }

    /**
     * 检查Redis连接状态
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        if (!$this->redis) {
            return false;
        }

        try {
            $this->redis->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取Redis服务器信息
     *
     * @return array
     */
    public function getServerInfo(): array
    {
        if (!$this->isConnected()) {
            return ['error' => 'Redis连接失败'];
        }

        $info = [];

        try {
            // 获取服务器信息
            $serverInfo = $this->redis->info();

            // 解析服务器信息
            foreach (explode("\n", $serverInfo) as $line) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $info[trim($parts[0])] = trim($parts[1]);
                }
            }
        } catch (\Exception $e) {
            $info['error'] = $e->getMessage();
        }

        return $info;
    }

    /**
     * 获取Redis版本
     *
     * @return string
     */
    public function getVersion(): string
    {
        if (!$this->isConnected()) {
            return 'N/A';
        }

        try {
            return $this->redis->info('server')['redis_version'] ?? 'Unknown';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * 获取运行时间(秒)
     *
     * @return int
     */
    public function getUptime(): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            return (int)$this->redis->info('server')['uptime_in_seconds'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取已连接客户端数
     *
     * @return int
     */
    public function getConnectedClients(): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            return (int)$this->redis->info('clients')['connected_clients'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取内存信息
     *
     * @return array
     */
    public function getMemoryInfo(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $memory = $this->redis->info('memory');

            return [
                'used_memory' => $this->formatBytes($memory['used_memory'] ?? 0),
                'used_memory_peak' => $this->formatBytes($memory['used_memory_peak'] ?? 0),
                'used_memory_rss' => $this->formatBytes($memory['used_memory_rss'] ?? 0),
                'used_memory_dataset' => $this->formatBytes($memory['used_memory_dataset'] ?? 0),
                'total_system_memory' => $this->formatBytes($memory['total_system_memory'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取持久化信息
     *
     * @return array
     */
    public function getPersistenceInfo(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $persistence = $this->redis->info('persistence');

            return [
                'loading' => $persistence['loading'] ?? 0,
                'rdb_changes_since_last_save' => $persistence['rdb_changes_since_last_save'] ?? 0,
                'rdb_last_save_time' => $persistence['rdb_last_save_time'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取统计信息
     *
     * @return array
     */
    public function getStatsInfo(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $stats = $this->redis->info('stats');

            return [
                'total_connections_received' => $stats['total_connections_received'] ?? 0,
                'total_commands_processed' => $stats['total_commands_processed'] ?? 0,
                'instantaneous_ops_per_sec' => $stats['instantaneous_ops_per_sec'] ?? 0,
                'total_net_input_bytes' => $this->formatBytes($stats['total_net_input_bytes'] ?? 0),
                'total_net_output_bytes' => $this->formatBytes($stats['total_net_output_bytes'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取CPU信息
     *
     * @return array
     */
    public function getCpuInfo(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $cpu = $this->redis->info('cpu');

            return [
                'used_cpu_sys' => $cpu['used_cpu_sys'] ?? 0,
                'used_cpu_user' => $cpu['used_cpu_user'] ?? 0,
                'used_cpu_avg' => $cpu['used_cpu_avg'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取命令统计
     *
     * @return array
     */
    public function getCommandStats(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $commandStats = $this->redis->info('commandstats');
            return $commandStats;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取数据库大小
     *
     * @return array
     */
    public function getDbSize(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $size = [];
            $config = $this->redis->config('get', 'databases');
            $databases = isset($config[0]) ? (int)$config[0] : 16;

            for ($i = 0; $i < $databases; $i++) {
                $dbSize = $this->redis->info('memory', 'db' . $i);
                if (isset($dbSize['db' . $i])) {
                    $size['db' . $i] = $this->formatBytes($dbSize['db' . $i]);
                }
            }

            return $size;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取完整监控信息
     *
     * @return array
     */
    public function getFullInfo(): array
    {
        if (!$this->isConnected()) {
            return [
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
        }

        try {
            // Get full Redis INFO
            $infoRaw = $this->redis->info();
            
            // Predis returns array, phpredis returns string
            if (is_array($infoRaw)) {
                $info = $infoRaw;
            } else {
                $info = $this->parseRedisInfo($infoRaw);
            }

            // Extract top-level properties
            $uptime_in_seconds = (int)($info['uptime_in_seconds'] ?? 0);
            $uptime_in_days = floor($uptime_in_seconds / 86400);
            $connected_clients = (int)($info['connected_clients'] ?? 0);
            $used_memory_bytes = (int)($info['used_memory'] ?? 0);
            $used_memory_formatted = $this->formatBytes($used_memory_bytes);

            // Build variable object with all detailed metrics
            $variable = [
                // Memory metrics (raw bytes)
                'used_memory' => $used_memory_bytes,
                'used_memory_peak' => (int)($info['used_memory_peak'] ?? 0),
                'used_memory_rss' => (int)($info['used_memory_rss'] ?? 0),
                'mem_fragmentation_ratio' => (float)($info['mem_fragmentation_ratio'] ?? 0),
                
                // Cache efficiency metrics
                'keyspace_hits' => (int)($info['keyspace_hits'] ?? 0),
                'keyspace_misses' => (int)($info['keyspace_misses'] ?? 0),
                'expired_keys' => (int)($info['expired_keys'] ?? 0),
                'evicted_keys' => (int)($info['evicted_keys'] ?? 0),
                
                // Performance metrics
                'instantaneous_ops_per_sec' => (int)($info['instantaneous_ops_per_sec'] ?? 0),
                'instantaneous_input_kbps' => (float)($info['instantaneous_input_kbps'] ?? 0),
                'instantaneous_output_kbps' => (float)($info['instantaneous_output_kbps'] ?? 0),
                'total_commands_processed' => (int)($info['total_commands_processed'] ?? 0),
                
                // Configuration and server info
                'redis_version' => $info['redis_version'] ?? '',
                'redis_mode' => $info['redis_mode'] ?? '',
                'os' => $info['os'] ?? '',
                'arch_bits' => (int)($info['arch_bits'] ?? 0),
                'mem_allocator' => $info['mem_allocator'] ?? '',
                'role' => $info['role'] ?? '',
                'tcp_port' => (int)($info['tcp_port'] ?? 0),
                'aof_enabled' => (int)($info['aof_enabled'] ?? 0),
                'rdb_changes_since_last_save' => (int)($info['rdb_changes_since_last_save'] ?? 0),
                'total_connections_received' => (int)($info['total_connections_received'] ?? 0),
            ];

            return [
                'uptime_in_seconds' => $uptime_in_seconds,
                'uptime_in_days' => $uptime_in_days,
                'connected_clients' => $connected_clients,
                'used_memory' => $used_memory_formatted,
                'variable' => $variable,
            ];
        } catch (\Exception $e) {
            // Return default values on error with error indicator
            return [
                'error' => true,
                'error_message' => 'Redis数据获取失败: ' . $e->getMessage(),
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
        }
    }

    /**
     * 格式化字节
     *
     * @param int $bytes 字节数
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 解析Redis INFO命令响应
     *
     * @param string $infoRaw Redis INFO命令的原始响应
     * @return array 解析后的键值对数组
     */
    protected function parseRedisInfo(string $infoRaw): array
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

    /**
     * 清空所有缓存
     *
     * @return bool
     */
    public function flushAll(): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $this->redis->flushall();
            return true;
        } catch (\Exception $e) {
                return false;
            }
    }

    /**
     * 获取所有键的数量
     *
     * @return int
     */
    public function getDbSizeInfo(): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            return $this->redis->dbsize();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 扫描 Redis 键并按前缀分组
     *
     * @param string $pattern 匹配模式，默认 *
     * @param int $count 每次扫描数量
     * @return array 分组后的键列表
     */
    public function scanKeys(string $pattern = '*', int $count = 1000): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            // 临时使用 keys 命令（后续优化为 scan）
            if (method_exists($this->redis, 'keys')) {
                $keys = $this->redis->keys($pattern);
                return is_array($keys) ? $keys : [];
            }
            
            return [];
        } catch (\Exception $e) {
            error_log("scanKeys exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 获取键的层级结构（第一级）
     *
     * @param string $pattern 匹配模式
     * @return array 第一级键前缀列表
     */
    public function getFirstLevelKeys(string $pattern = '*'): array
    {
        $keys = $this->scanKeys($pattern);
        $prefixes = [];

        foreach ($keys as $key) {
            // 按 : 分割键名，获取第一级前缀
            $parts = explode(':', $key);
            if (count($parts) > 0) {
                $prefix = $parts[0];
                if (!isset($prefixes[$prefix])) {
                    $prefixes[$prefix] = [
                        'key' => $prefix,
                        'count' => 0,
                        'type' => 'prefix',
                    ];
                }
                $prefixes[$prefix]['count']++;
            }
        }

        return array_values($prefixes);
    }

    /**
     * 获取指定前缀的第二级键
     *
     * @param string $prefix 第一级前缀
     * @return array 第二级键列表
     */
    public function getSecondLevelKeys(string $prefix): array
    {
        $pattern = $prefix . ':*';
        $keys = $this->scanKeys($pattern);
        $secondLevel = [];

        foreach ($keys as $key) {
            $parts = explode(':', $key);
            if (count($parts) >= 2) {
                $secondPrefix = $parts[0] . ':' . $parts[1];
                if (!isset($secondLevel[$secondPrefix])) {
                    $secondLevel[$secondPrefix] = [
                        'key' => $secondPrefix,
                        'fullKey' => $key,
                        'count' => 0,
                        'type' => count($parts) > 2 ? 'prefix' : 'key',
                    ];
                }
                $secondLevel[$secondPrefix]['count']++;
            }
        }

        return array_values($secondLevel);
    }

    /**
     * 获取指定前缀的第三级键
     *
     * @param string $prefix 第二级前缀
     * @return array 第三级键列表
     */
    public function getThirdLevelKeys(string $prefix): array
    {
        $pattern = $prefix . ':*';
        $keys = $this->scanKeys($pattern);
        $thirdLevel = [];

        foreach ($keys as $key) {
            $thirdLevel[] = [
                'key' => $key,
                'type' => 'key',
                'ttl' => $this->getKeyTTL($key),
                'size' => $this->getKeySize($key),
            ];
        }

        return $thirdLevel;
    }

    /**
     * 获取键的 TTL
     *
     * @param string $key
     * @return int -1 表示永不过期，-2 表示键不存在
     */
    public function getKeyTTL(string $key): int
    {
        if (!$this->isConnected()) {
            return -2;
        }

        try {
            return $this->redis->ttl($key);
        } catch (\Exception $e) {
            return -2;
        }
    }

    /**
     * 获取键的大小（字节数）
     *
     * @param string $key
     * @return int
     */
    public function getKeySize(string $key): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            $type = $this->redis->type($key);
            
            switch ($type) {
                case 'string':
                    return strlen($this->redis->get($key));
                case 'list':
                    return $this->redis->llen($key);
                case 'set':
                    return $this->redis->scard($key);
                case 'zset':
                    return $this->redis->zcard($key);
                case 'hash':
                    return $this->redis->hlen($key);
                default:
                    return 0;
            }
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取键的详细信息
     *
     * @param string $key
     * @return array
     */
    public function getKeyInfo(string $key): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $type = $this->redis->type($key);
            $ttl = $this->getKeyTTL($key);
            $size = $this->getKeySize($key);

            $info = [
                'key' => $key,
                'type' => $type,
                'ttl' => $ttl,
                'size' => $size,
                'value' => null,
            ];

            // 根据类型获取值（限制大小）
            switch ($type) {
                case 'string':
                    $value = $this->redis->get($key);
                    $info['value'] = strlen($value) > 1000 ? substr($value, 0, 1000) . '...' : $value;
                    break;
                case 'list':
                    $info['value'] = $this->redis->lrange($key, 0, 99); // 最多100个
                    break;
                case 'set':
                    $info['value'] = $this->redis->smembers($key);
                    break;
                case 'zset':
                    $info['value'] = $this->redis->zrange($key, 0, 99); // 最多100个
                    break;
                case 'hash':
                    $info['value'] = $this->redis->hgetall($key);
                    break;
            }

            return $info;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 删除指定键
     *
     * @param string $key
     * @return bool
     */
    public function deleteKey(string $key): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            return $this->redis->del($key) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 批量删除键（按模式）
     *
     * @param string $pattern
     * @return int 删除的键数量
     */
    public function deleteKeysByPattern(string $pattern): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            $keys = $this->scanKeys($pattern);
            if (empty($keys)) {
                return 0;
            }

            return $this->redis->del($keys);
        } catch (\Exception $e) {
            return 0;
        }
    }
}

