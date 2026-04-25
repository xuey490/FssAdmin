<?php

declare(strict_types=1);

namespace App\Services;

class ServerMonitorService
{
    /**
     * 获取完整服务器信息
     */
    public function getServerInfo(): array
    {
        return [
            'memory' => $this->getMemoryInfo(),
            'phpEnv' => $this->getPhpEnvInfo(),
            'disk'   => $this->getDiskInfo(),
        ];
    }

    /**
     * 获取完整监控信息（内存 + PHP环境 + 磁盘 + 缓存）
     */
    public function getFullInfo(): array
    {
        return array_merge($this->getServerInfo(), [
            'cache' => $this->getCacheInfo(),
        ]);
    }

    /**
     * 获取缓存信息（OPcache）
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
     * 清理缓存（OPcache + stat cache）
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
     */
    public function getMemoryInfo(): array
    {
        $totalBytes = 0;
        $freeBytes  = 0;

        if (PHP_OS_FAMILY === 'Windows') {
            // 方式1: wmic os get TotalVisibleMemorySize,FreePhysicalMemory (单位 KB)
            $out = [];
            exec('wmic os get TotalVisibleMemorySize,FreePhysicalMemory', $out);
            if (!empty($out[1])) {
                $line  = preg_replace('/\s+/', ' ', trim($out[1]));
                $parts = explode(' ', $line);
                if (count($parts) >= 2) {
                    $freeBytes  = (int)$parts[0] * 1024;
                    $totalBytes = (int)$parts[1] * 1024;
                }
            }

            // 方式2: COM/WMI 兜底（wmic 失败或返回0时使用）
            if ($totalBytes === 0 && class_exists('COM')) {
                try {
                    $wmi = new \COM('winmgmts://./root/cimv2');

                    $query = $wmi->ExecQuery('SELECT TotalPhysicalMemory FROM Win32_ComputerSystem');
                    foreach ($query as $item) {
                        $totalBytes = (int)$item->TotalPhysicalMemory;
                        break;
                    }

                    $query = $wmi->ExecQuery('SELECT FreePhysicalMemory FROM Win32_OperatingSystem');
                    foreach ($query as $item) {
                        $freeBytes = (int)$item->FreePhysicalMemory * 1024;
                        break;
                    }
                } catch (\Throwable $e) {
                    // COM 也不可用，保持 0
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
