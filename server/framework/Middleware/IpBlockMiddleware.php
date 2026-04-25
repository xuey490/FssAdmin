<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IpBlockMiddleware implements MiddlewareInterface
{
    private array $whitelist = [];

    private array $blacklist = [];

    private bool $enabled = true;

    public function __construct(?string $configFile = null)
    {
        if ($configFile && file_exists($configFile)) {
            $config          = require $configFile;
            $this->whitelist = $config['whitelist'] ?? [];
            $this->blacklist = $config['blacklist'] ?? [];
            $this->enabled   = $config['enabled']   ?? true;
        }
    }

    public function handle(Request $request, callable $next): Response
    {
        if (! $this->enabled) {
            return $next($request);
        }

        $ip = $request->getClientIp();

        if ($ip === null) {
            return $this->buildForbiddenResponse($request, '无法识别客户端 IP');
        }

        // 1. 检查黑名单（支持 CIDR）
        if (! empty($this->blacklist) && $this->isIpInList($ip, $this->blacklist)) {
            return $this->buildForbiddenResponse($request, '您的 IP 已被禁止访问');
        }

        // 2. 检查白名单（如果设置了）
        if (! empty($this->whitelist) && ! $this->isIpInList($ip, $this->whitelist)) {
            return $this->buildForbiddenResponse($request, '仅限授权 IP 或网段访问');
        }

        return $next($request);
    }

    /**
     * 判断 IP 是否匹配列表中的任意 CIDR 或精确 IP.
     */
    private function isIpInList(string $ip, array $list): bool
    {
        foreach ($list as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }

            // 检查是否为 CIDR 格式（包含 /）
            if (strpos($entry, '/') !== false) {
                if ($this->cidrMatch($ip, $entry)) {
                    return true;
                }
            } else {
                // 精确 IP 匹配（支持 IPv4 和 IPv6）
                if (strtolower($ip) === strtolower($entry)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 判断 IP 是否在 CIDR 网段内.
     */
    private function cidrMatch(string $ip, string $cidr): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        [$subnet, $mask] = explode('/', $cidr, 2);

        if (! filter_var($subnet, FILTER_VALIDATE_IP)) {
            return false;
        }

        $mask = (int) $mask;
        if ($mask < 0) {
            return false;
        }

        // IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (! filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return false;
            }
            if ($mask > 32) {
                return false;
            }

            $ipLong     = ip2long($ip);
            $subnetLong = ip2long($subnet);
            if ($ipLong === false || $subnetLong === false) {
                return false;
            }

            $maskBits = -1 << (32 - $mask);
            return ($ipLong & $maskBits) === ($subnetLong & $maskBits);
        }

        // IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if (! filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return false;
            }
            if ($mask > 128) {
                return false;
            }

            $ipBin     = $this->ip2bin($ip);
            $subnetBin = $this->ip2bin($subnet);

            // 比较前 $mask 位
            $bytes = intval($mask / 8);
            $bits  = $mask % 8;

            // 先比较完整字节
            if ($bytes > 0) {
                if (substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
                    return false;
                }
            }

            // 再比较剩余比特
            if ($bits > 0) {
                $maskByte   = 0xFF << (8 - $bits);
                $ipByte     = ord($ipBin[$bytes] ?? "\x00");
                $subnetByte = ord($subnetBin[$bytes] ?? "\x00");
                if (($ipByte & $maskByte) !== ($subnetByte & $maskByte)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * 将 IPv6 地址转换为 128 位二进制字符串.
     */
    private function ip2bin(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4 映射到 IPv6 ::ffff:a.b.c.d
            $ip = '::ffff:' . $ip;
        }

        $expanded = inet_pton($ip);
        if ($expanded === false) {
            return str_repeat("\x00", 16);
        }

        // 确保返回 16 字节（IPv6）
        return str_pad($expanded, 16, "\x00", STR_PAD_LEFT);
    }

    private function buildForbiddenResponse(Request $request, string $reason): Response
    {
        $message  = '访问被拒绝：' . $reason;
        $clientIp = $request->getClientIp() ?: '未知';

        if ($request->isXmlHttpRequest()
            || strpos($request->headers->get('Accept', ''), 'application/json') !== false) {
            return new JsonResponse([
                'success'   => false,
                'error'     => 'forbidden',
                'message'   => $message,
                'client_ip' => $clientIp,
            ], 403);
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>访问被拒绝</title>
    <style>
        body { font-family: system-ui, sans-serif; text-align: center; padding: 50px; background: #fafafa; }
        .box { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        h1 { color: #e74c3c; font-size: 1.8em; margin-bottom: 20px; }
        p { color: #555; line-height: 1.6; }
        .ip { color: #7f8c8d; margin-top: 15px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔒 访问被拒绝</h1>
        <p>{$message}</p>
        <div class="ip">您的 IP：{$clientIp}</div>
    </div>
</body>
</html>
HTML;

        return new Response($html, 403, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
