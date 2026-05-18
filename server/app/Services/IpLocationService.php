<?php

declare(strict_types=1);

/**
 * IP地理位置服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use Framework\Basic\BaseService;

/**
 * IpLocationService IP地理位置服务
 */
class IpLocationService extends BaseService
{
    /**
     * 缓存前缀
     * @var string
     */
    protected string $cachePrefix = 'ip_location:';

    /**
     * 缓存时间(秒)
     * @var int
     */
    protected int $cacheTtl = 86400; // 24小时

    /**
     * 根据IP获取地理位置
     *
     * @param string $ip IP地址
     * @return string
     */
    public function getLocation(string $ip): string
    {
        // 本地IP
        if ($this->isLocalIp($ip)) {
            return '本地';
        }

        // 尝试从缓存获取
        $cached = $this->getFromCache($ip);
        if ($cached !== null) {
            return $cached;
        }

        // 调用IP定位API
        $location = $this->fetchLocation($ip);

        // 缓存结果
        $this->saveToCache($ip, $location);

        return $location;
    }

    /**
     * 检查是否本地IP
     *
     * @param string $ip IP地址
     * @return bool
     */
    protected function isLocalIp(string $ip): bool
    {
        $localIps = ['127.0.0.1', '::1', '0.0.0.0'];

        if (in_array($ip, $localIps)) {
            return true;
        }

        // 检查内网IP（10.x.x.x、172.16-31.x.x、192.168.x.x）
        if (preg_match('/^(10\.\d{1,3}\.\d{1,3}\.\d{1,3}|172\.(1[6-9]|2\d|3[0-1])\.\d{1,3}\.\d{1,3}|192\.168\.\d{1,3}\.\d{1,3})$/', $ip)) {
            return true;
        }

        return false;
    }

    /**
     * 从缓存获取
     *
     * @param string $ip IP地址
     * @return string|null
     */
    protected function getFromCache(string $ip): ?string
    {
        try {
            $cached = app('cache')->get($this->cachePrefix . $ip);
            if (!is_string($cached) || trim($cached) === '') {
                return null;
            }

            // "未知" 视为失败结果，不命中缓存，允许后续重试外部API
            if ($cached === '未知') {
                return null;
            }

            return $cached;
        } catch (\Throwable $e) {
            // 缓存异常时降级为直连查询，不能影响主流程
            return null;
        }
    }

    /**
     * 保存到缓存
     *
     * @param string $ip       IP地址
     * @param string $location 地理位置
     * @return void
     */
    protected function saveToCache(string $ip, string $location): void
    {
        // 失败结果不缓存，避免短暂网络异常导致长时间固定为"未知"
        if ($location === '未知' || trim($location) === '') {
            return;
        }

        try {
            app('cache')->set($this->cachePrefix . $ip, $location, $this->cacheTtl);
        } catch (\Throwable $e) {
            // 缓存写入失败不影响主流程
        }
    }

    /**
     * 调用IP定位API
     *
     * @param string $ip IP地址
     * @return string
     */
    protected function fetchLocation(string $ip): string
    {
        try {
            // 使用免费的IP定位API (示例：ip-api.com)
            $url = "http://ip-api.com/json/{$ip}?lang=zh-CN";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'NovaPHP-IpLocationService/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);

                if (isset($data['status']) && $data['status'] === 'success') {
                    $country = $data['country'] ?? '';
                    $region = $data['regionName'] ?? '';
                    $city = $data['city'] ?? '';

                    $location = trim($country . ' ' . $region . ' ' . $city);
                    return $location ?: '未知';
                }
            }
        } catch (\Exception $e) {
            // 忽略异常
        }

        return '未知';
    }

    /**
     * 批量获取IP地理位置
     *
     * @param array $ips IP数组
     * @return array
     */
    public function getLocations(array $ips): array
    {
        $locations = [];

        foreach ($ips as $ip) {
                $locations[$ip] = $this->getLocation($ip);
            }

        return $locations;
    }
}
