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
use Illuminate\Support\Facades\Cache;

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

        // 检查内网IP
        if (preg_match('/^(10\.\d{1,3}\.\d{1,3}\.\d{1,3}|172\.(1[6-9]\.\d{1,3}\.\d{1,3}|192\.168)/', $ip)) {
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
        return Cache::get($this->cachePrefix . $ip);
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
        Cache::put($this->cachePrefix . $ip, $location, $this->cacheTtl);
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

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
