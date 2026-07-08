<?php

declare(strict_types=1);

namespace App\Services;

class ServerMonitorService
{
    /**
     * 获取完整服务器信息
     * @return array<array-key, mixed>
     */
    public function getServerInfo(): array
    {
        return [
            'memory' => $this->getMemoryInfo(),
            'phpEnv' => $this->getPhpEnvInfo(),
            'disk'   => $this->getDiskInfo(),
            'cpu'    => $this->getCpuInfo(),
        ];
    }

    /**
     * 获取完整监控信息（内存 + PHP环境 + 磁盘 + 缓存）
     * @return array<array-key, mixed>
     */
    public function getFullInfo(): array
    {
        return array_merge($this->getServerInfo(), [
            'cache' => $this->getCacheInfo(),
        ]);
    }

    /**
     * 获取缓存信息（OPcache）
     * @return array<array-key, mixed>
     */
    public function getCacheInfo(): array
    {
        $opcache = [];
        if (function_exists('opcache_get_status')) {
            $status = opcache_get_status(false) ?: [];
            $config = opcache_get_configuration()['directives'] ?? [];
            $mem    = $status['memory_usage'] ?? [];
            $used   = ($mem['used_memory'] ?? 0) + ($mem['wasted_memory'] ?? 0);
            $free   = $mem['free_memory'] ?? 0;
            $opcache = [
                'enabled'        => $status['opcache_enabled'] ?? false,
                'memory_used'    => $this->formatBytes($used),
                'memory_free'    => $this->formatBytes($free),
                'memory_total'   => $this->formatBytes($used + $free),
                'hit_rate'       => round($status['opcache_statistics']['opcache_hit_rate'] ?? 0, 2) . '%',
                'cached_scripts' => $status['opcache_statistics']['num_cached_scripts'] ?? 0,
                'max_files'      => $config['opcache.max_accelerated_files'] ?? 0,
            ];
        }
        return ['opcache' => $opcache, 'php_version' => PHP_VERSION];
    }

    /**
     * 获取 PHP 环境信息（供 MonitorController.php 使用）
     *
     * @return array
     */
    /**
     * 获取 PHP 信息
     *
     * @return array<array-key, mixed>
     */
    public function getPhpInfo(): array
    {
        return $this->getPhpEnvInfo();
    }

    /**
     * 清理缓存（OPcache + stat cache）
     *
     * @return array<array-key, mixed>
     */
    public function clearCache(): array
    {
        $results = [];

        if (function_exists('opcache_reset')) {
            $results['opcache'] = opcache_reset();
        }
        clearstatcache(true);
        $results['stat_cache'] = true;
        return ['cleared' => $results, 'message' => '缓存已清理'];
    }

    /**
     * 内存信息
     * 兼容 Linux / Windows
     *
     * @return array<array-key, mixed>
     */
    public function getMemoryInfo(): array
    {
        $totalBytes = 0;
        $freeBytes  = 0;

        if (PHP_OS_FAMILY === 'Windows') {
            // 使用 PowerShell 获取内存信息（wmic 已在新版 Windows 中移除）
            $psCommand = 'powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object TotalVisibleMemorySize, FreePhysicalMemory | ConvertTo-Json"';
            $output = shell_exec($psCommand);
            
            if ($output) {
                $memData = json_decode($output, true);
                if (is_array($memData)) {
                    $totalBytes = (int)($memData['TotalVisibleMemorySize'] ?? 0) * 1024;
                    $freeBytes  = (int)($memData['FreePhysicalMemory'] ?? 0) * 1024;
                }
            }
        } else {
            // Linux: 读取 /proc/meminfo
            if (is_readable('/proc/meminfo')) {
                $meminfo = file_get_contents('/proc/meminfo');
                preg_match('/MemTotal:\s+(\d+)\s+kB/i', $meminfo, $total);
                preg_match('/MemAvailable:\s+(\d+)\s+kB/i', $meminfo, $free);
                $totalBytes = isset($total[1]) ? (int)$total[1] * 1024 : 0;
                $freeBytes  = isset($free[1])  ? (int)$free[1]  * 1024 : 0;
            }
        }

        $usedBytes  = $totalBytes - $freeBytes;
        $phpBytes   = memory_get_usage(true);
        $rate       = $totalBytes > 0 ? round($usedBytes / $totalBytes * 100, 1) : 0;

        return [
            'total' => $this->formatBytes($totalBytes),
            'used'  => $this->formatBytes($usedBytes),
            'free'  => $this->formatBytes($freeBytes),
            'php'   => $this->formatBytes($phpBytes),
            'rate'  => (string)$rate,
        ];
    }

    /**
     * PHP 及环境信息
     *
     * @return array<string, mixed>
     */
    public function getPhpEnvInfo(): array
    {
        $extensions = get_loaded_extensions();
        sort($extensions);

        return [
            'php_version'        => PHP_VERSION,
            'os'                 => PHP_OS . ' ' . php_uname('r'),
            'project_path'       => defined('BASE_PATH') ? BASE_PATH : getcwd(),
            'memory_limit'       => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'error_reporting'    => $this->errorReportingToString((int)ini_get('error_reporting')),
            'display_errors'     => ini_get('display_errors') ? 'On' : 'Off',
            'upload_max_filesize'=> ini_get('upload_max_filesize'),
            'post_max_size'      => ini_get('post_max_size'),
            'extension_dir'      => ini_get('extension_dir'),
            'loaded_extensions'  => implode(', ', $extensions),
        ];
    }

    /**
     * 磁盘分区信息
     * 兼容 Linux / Windows
     *
     * @return array<array-key, mixed>
     */
    public function getDiskInfo(): array
    {
        $disks = [];

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: 枚举盘符
            foreach (range('A', 'Z') as $letter) {
                $path = $letter . ':\\';
                if (!is_dir($path)) {
                    continue;
                }
                $total     = disk_total_space($path);
                $free      = disk_free_space($path);
                $used      = $total - $free;
                $pct       = $total > 0 ? round($used / $total * 100, 1) : 0;

                $disks[] = [
                    'filesystem'     => $letter . ':',
                    'size'           => $this->formatBytes($total),
                    'used'           => $this->formatBytes($used),
                    'available'      => $this->formatBytes($free),
                    'use_percentage' => $pct . '%',
                    'mounted_on'     => $path,
                ];
            }
        } else {
            // Linux: 解析 df -h 输出
            $output = shell_exec('df -h 2>/dev/null');
            if ($output) {
                $lines = array_filter(explode("\n", trim($output)));
                array_shift($lines); // 去掉表头
                foreach ($lines as $line) {
                    $parts = preg_split('/\s+/', trim($line));
                    if (count($parts) < 6) {
                        continue;
                    }
                    $disks[] = [
                        'filesystem'     => $parts[0],
                        'size'           => $parts[1],
                        'used'           => $parts[2],
                        'available'      => $parts[3],
                        'use_percentage' => $parts[4],
                        'mounted_on'     => $parts[5],
                    ];
                }
            } else {
                // fallback: 只读根目录
                $total = disk_total_space('/');
                $free  = disk_free_space('/');
                $used  = $total - $free;
                $pct   = $total > 0 ? round($used / $total * 100, 1) : 0;
                $disks[] = [
                    'filesystem'     => 'rootfs',
                    'size'           => $this->formatBytes($total),
                    'used'           => $this->formatBytes($used),
                    'available'      => $this->formatBytes($free),
                    'use_percentage' => $pct . '%',
                    'mounted_on'     => '/',
                ];
            }
        }

        return $disks;
    }


    /**
     * 获取 CPU 信息
     * 兼容 Linux / Windows
     *
     * @return array<string, mixed>
     */
    public function getCpuInfo(): array
    {
        $info = [
            'architecture'     => 'unknown',
            'model'           => 'unknown',
            'physical_cores'  => 0,
            'logical_cores'   => 0,
            'max_frequency'   => 'unknown',
            'current_usage'   => 0,
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            // Windows: 使用 PowerShell 获取 CPU 信息（wmic 已在新版 Windows 中移除）
            $psCommand = 'powershell -Command "Get-CimInstance Win32_Processor | Select-Object Name, NumberOfCores, NumberOfLogicalProcessors, MaxClockSpeed, Architecture | ConvertTo-Json"';
            $output = shell_exec($psCommand);
            
            if ($output) {
                $cpuData = json_decode($output, true);
                if (is_array($cpuData) && isset($cpuData['Name'])) {
                    // 单个 CPU
                    $cpuData = [$cpuData];
                }
                if (!empty($cpuData)) {
                    $cpu = is_array($cpuData[0]) ? $cpuData[0] : $cpuData;
                    $info['model'] = $cpu['Name'] ?? 'unknown';
                    $info['physical_cores'] = (int)($cpu['NumberOfCores'] ?? 0);
                    $info['logical_cores'] = (int)($cpu['NumberOfLogicalProcessors'] ?? 0);
                    $maxMhz = $cpu['MaxClockSpeed'] ?? 0;
                    $info['max_frequency'] = $maxMhz > 0 ? round($maxMhz / 1000, 2) . ' GHz' : 'unknown';
                    
                    // 架构映射
                    $archMap = [
                        0 => 'x86',
                        1 => 'MIPS',
                        2 => 'Alpha',
                        5 => 'ARM',
                        6 => 'IA64',
                        9 => 'x64',
                        12 => 'ARM64',
                    ];
                    $info['architecture'] = $archMap[(int)($cpu['Architecture'] ?? 9)] ?? 'unknown';
                }
            }
            
            // 获取 CPU 利用率
            $info['current_usage'] = $this->getWindowsCpuUsage();

        } else {
            // Linux: 读取 /proc/cpuinfo
            if (is_readable('/proc/cpuinfo')) {
                $cpuinfo = file_get_contents('/proc/cpuinfo');
                
                // 获取 CPU 型号
                if (preg_match('/model name\s*:\s*(.+)/i', $cpuinfo, $matches)) {
                    $info['model'] = trim($matches[1]);
                }
                
                // 获取架构
                if (preg_match('/architecture\s*:\s*(.+)/i', $cpuinfo, $matches)) {
                    $info['architecture'] = trim($matches[1]);
                } elseif (preg_match('/Hardware\s*:\s*(.+)/i', $cpuinfo, $matches)) {
                    $info['architecture'] = trim($matches[1]);
                } else {
                    $info['architecture'] = trim(shell_exec('uname -m') ?? 'unknown');
                }
                
                // 统计物理核心数和逻辑核心数
                $physicalIds = [];
                $coreIds = [];
                $processors = 0;
                
                foreach (explode("\n", $cpuinfo) as $line) {
                    if (preg_match('/^processor\s*:\s*(\d+)/i', $line, $m)) {
                        $processors = max($processors, (int)$m[1] + 1);
                    }
                    if (preg_match('/^physical id\s*:\s*(\d+)/i', $line, $m)) {
                        $physicalIds[(int)$m[1]] = true;
                    }
                    if (preg_match('/^core id\s*:\s*(\d+)/i', $line, $m)) {
                        $coreIds[(int)$m[1]] = true;
                    }
                }
                
                $physicalCount = count($physicalIds);
                $info['physical_cores'] = $physicalCount > 0 ? $physicalCount * count($coreIds) : $processors;
                $info['logical_cores'] = $processors;
                
                // 获取 CPU 最大频率
                if (preg_match('/cpu MHz\s*:\s*([\d.]+)/i', $cpuinfo, $matches)) {
                    $info['max_frequency'] = round((float)$matches[1] / 1000, 2) . ' GHz';
                } elseif (preg_match('/cpu max MHz\s*:\s*([\d.]+)/i', $cpuinfo, $matches)) {
                    $info['max_frequency'] = round((float)$matches[1] / 1000, 2) . ' GHz';
                }
                
                // 获取 CPU 利用率
                $info['current_usage'] = $this->getLinuxCpuUsage();
            }
        }

        return $info;
    }

    /**
     * 获取 Windows CPU 利用率
     */
    private function getWindowsCpuUsage(): float
    {
        // 使用 PowerShell 获取 CPU 利用率
        $psCommand = 'powershell -Command "(Get-CimInstance Win32_PerfFormattedData_PerfOS_Processor | Where-Object { $_.Name -eq \'_Total\' }).PercentProcessorTime"';
        $output = shell_exec($psCommand);
        
        if ($output) {
            $usage = trim($output);
            if (is_numeric($usage)) {
                return round((float)$usage, 1);
            }
        }
        
        return 0;
    }

    /**
     * 获取 Linux CPU 利用率
     */
            /**
             */
    private function getLinuxCpuUsage(): float
    {
        // 方法1: 读取 /proc/stat
        if (is_readable('/proc/stat')) {
            $stat1 = $this->readCpuStats();
            usleep(100000); // 等待 100ms
            $stat2 = $this->readCpuStats();
            
            $totalDiff = $stat2['total'] - $stat1['total'];
            $idleDiff = $stat2['idle'] - $stat1['idle'];
            
            if ($totalDiff > 0) {
                return round(($totalDiff - $idleDiff) / $totalDiff * 100, 1);
            }
        }
        
        // 方法2: 使用 top 命令
        $output = shell_exec("top -bn1 2>/dev/null | grep 'Cpu(s)' | awk '{print \$2}'");
        if ($output) {
            $usage = trim($output);
            if (is_numeric($usage)) {
                return round((float)$usage, 1);
            }
        }
        
        return 0;
    }

    /**
     * 读取 /proc/stat 中的 CPU 统计数据
     *
     * @return array<string, mixed>
     */
    private function readCpuStats(): array
    {
        $stat = file_get_contents('/proc/stat');
        if (preg_match('/cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $stat, $matches)) {
            $user = (int)$matches[1];
            $nice = (int)$matches[2];
            $system = (int)$matches[3];
            $idle = (int)$matches[4];
            $iowait = (int)$matches[5];
            $irq = (int)$matches[6];
            $softirq = (int)$matches[7];
            
            $total = $user + $nice + $system + $idle + $iowait + $irq + $softirq;
            
            return [
                'total' => $total,
                'idle'  => $idle,
            ];
        }
        
        return ['total' => 0, 'idle' => 0];
    }

    // ==================== helpers ====================

    private function formatBytes(int|float $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int)floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);
        return round($bytes / (1024 ** $i), 2) . ' ' . $units[$i];
    }

    private function errorReportingToString(int $level): string
    {
        if ($level === 0) {
            return 'Off (0)';
        }
        $map = [
            E_ALL       => 'E_ALL',
            E_ERROR     => 'E_ERROR',
            E_WARNING   => 'E_WARNING',
            E_NOTICE    => 'E_NOTICE',
            E_DEPRECATED=> 'E_DEPRECATED',
        ];
        foreach ($map as $const => $name) {
            if ($level === $const) {
                return "{$name} ({$level})";
            }
        }
        return (string)$level;
    }
}