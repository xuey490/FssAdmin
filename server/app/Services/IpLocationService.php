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
    protected int $cacheTtl = 86400;

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

        $cached = $this->getFromCache($ip);
        if ($cached !== null) {
            return $cached;
        }

        $location = $this->fetchLocation($ip);
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
     * 调用IP定位API
     *
     * @param string $ip IP地址
     * @return string
     */
    protected function fetchLocation(string $ip): string
    {
        try {
            $location = $this->requestIpApi($ip);
            if ($location !== null) {
                return $location;
            }

            // 备用接口，避免单一服务偶发失败导致长期显示“未知”
            $location = $this->requestIpWhois($ip);
            if ($location !== null) {
                return $location;
            }
        } catch (\Throwable $e) {
            app('log')->warning('IpLocationService fetchLocation exception', [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);
        }

        return '未知';
    }

    protected function getFromCache(string $ip): ?string
    {
        try {
            $cached = app('cache')->get($this->cachePrefix . $ip);
            if (!is_string($cached) || trim($cached) === '') {
                return null;
            }

            // 失败结果不命中缓存，允许后续重试远程接口
            if ($cached === '未知') {
                return null;
            }

            return $cached;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function saveToCache(string $ip, string $location): void
    {
        // 失败结果不缓存，避免短期异常导致长时间显示未知
        if ($location === '未知' || trim($location) === '') {
            return;
        }

        try {
            app('cache')->set($this->cachePrefix . $ip, $location, $this->cacheTtl);
        } catch (\Throwable $e) {
            // 缓存异常不影响主流程
        }
    }

    protected function requestIpApi(string $ip): ?string
    {
        $url = "http://ip-api.com/json/{$ip}?lang=zh-CN";
        [$response, $httpCode, $curlErrNo, $curlErrMsg] = $this->httpGet($url);

        if ($curlErrNo !== 0 || $httpCode !== 200 || $response === '') {
            app('log')->warning('IpLocationService ip-api request failed', [
                'ip' => $ip,
                'http_code' => $httpCode,
                'curl_errno' => $curlErrNo,
                'curl_error' => $curlErrMsg,
            ]);
            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data) || ($data['status'] ?? '') !== 'success') {
            app('log')->warning('IpLocationService ip-api response invalid', [
                'ip' => $ip,
                'response' => $response,
            ]);
            return null;
        }

        return $this->formatLocation(
            (string)($data['country'] ?? ''),
            (string)($data['regionName'] ?? ''),
            (string)($data['city'] ?? '')
        );
    }

    protected function requestIpWhois(string $ip): ?string
    {
        $url = "https://ipwho.is/{$ip}?lang=zh";
        [$response, $httpCode, $curlErrNo, $curlErrMsg] = $this->httpGet($url);

        if ($curlErrNo !== 0 || $httpCode !== 200 || $response === '') {
            app('log')->warning('IpLocationService ipwho.is request failed', [
                'ip' => $ip,
                'http_code' => $httpCode,
                'curl_errno' => $curlErrNo,
                'curl_error' => $curlErrMsg,
            ]);
            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data) || ($data['success'] ?? false) !== true) {
            app('log')->warning('IpLocationService ipwho.is response invalid', [
                'ip' => $ip,
                'response' => $response,
            ]);
            return null;
        }

        return $this->formatLocation(
            (string)($data['country'] ?? ''),
            (string)($data['region'] ?? ''),
            (string)($data['city'] ?? '')
        );
    }

    /**
     * @return array{0:string,1:int,2:int,3:string}
     */
    protected function httpGet(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'NovaPHP-IpLocationService/1.0');

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        return [is_string($response) ? $response : '', $httpCode, $errno, $error];
    }

    protected function formatLocation(string $country, string $region, string $city): string
    {
        $location = trim(implode(' ', array_filter([$country, $region, $city], fn($v) => trim($v) !== '')));
        return $location !== '' ? $location : '未知';
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
