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

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
$response = app('response')->setContent('Hello FSSPHP!');

//app('cookie')->queueCookie('token', $this->tokenString, 3600);
//app('cookie')->queueCookie('token111', 'oooooo', 3600);


// 在发送 Response 前统一绑定队列中的 Cookie
//app('cookie')->sendQueuedCookies($response);

// 快捷设置 Cookie
#app('cookie')->setResponseCookie($response, 'token', $this->tokenString , 3600);

// 快捷删除 Cookie
//app('cookie')->forgetResponseCookie($response, 'old_cookie');

return $response;
*/

/**
 * Cookie 管理类
 *
 * 提供完整的 Cookie 管理功能，包括 Cookie 的设置、获取、删除、
 * 队列管理等操作。支持 Cookie 值加密、签名验证，确保数据安全性。
 * 同时支持 Symfony Response 对象和原生 PHP setcookie 两种模式。
 *
 * @package Framework\Utils
 */
class CookieManager
{
    /**
     * Cookie 配置数组
     *
     * @var array
     */
    protected array $config;

    /**
     * 加密密钥
     *
     * @var string
     */
    protected string $secret;

    /**
     * 加密算法
     *
     * @var string
     */
    protected string $cipher;

    /**
     * Cookie 作用域
     *
     * @var string
     */
    protected string $domain;

    /**
     * Cookie 路径
     *
     * @var string
     */
    protected string $path;

    /**
     * 默认过期时间（秒）
     *
     * @var int
     */
    protected int $expire;

    /**
     * 是否仅 HTTPS 传输
     *
     * @var bool
     */
    protected bool $secure;

    /**
     * 是否仅 HTTP 访问（防止 XSS）
     *
     * @var bool
     */
    protected bool $httponly;

    /**
     * SameSite 策略
     *
     * @var string
     */
    protected string $samesite;

    /**
     * 是否启用加密
     *
     * @var bool
     */
    protected bool $encrypt;

    /**
     * 待发送的 Cookie 队列
     *
     * @var array
     */
    protected array $queuedCookies = [];

    /**
     * 构造函数
     *
     * 从配置文件加载 Cookie 配置，包括加密密钥、算法、作用域、路径、
     * 过期时间、安全选项等。
     *
     * @param string|null $configPath 配置文件路径，默认为 BASE_PATH/config/cookie.php
     *
     * @throws \RuntimeException 配置文件不存在或密钥长度不足时抛出异常
     */
    public function __construct(?string $configPath = null)
    {
        $configPath = $configPath ?? BASE_PATH . '/config/cookie.php';

        if (! file_exists($configPath)) {
            throw new \RuntimeException("Cookie config not found: {$configPath}");
        }

        $this->config = require $configPath;

        $this->secret   = (string) ($this->config['secret'] ?? '');
        if (strlen($this->secret) < 16) {
            throw new \RuntimeException('Cookie secret must be at least 16 characters.');
        }

        $this->cipher   = $this->config['cipher'] ?? 'AES-256-CBC';
        $this->domain   = $this->config['domain'] ?? '';
        $this->path     = $this->config['path']   ?? '/';
        $this->expire   = (int) ($this->config['expire'] ?? 86400);
        $this->secure   = (bool) ($this->config['secure'] ?? false);
        $this->httponly = (bool) ($this->config['httponly'] ?? true);
        $this->samesite = $this->config['samesite'] ?? 'Lax';
        $this->encrypt  = (bool) ($this->config['encrypt'] ?? false);
    }

    // --------------------------------------
    // Cookie 队列
    // --------------------------------------

    /**
     * 将 Cookie 添加到发送队列
     *
     * 将 Cookie 信息加入队列，等待统一发送。支持设置过期时间，
     * Cookie 值会经过编码处理（可选加密）。
     *
     * @param string    $name   Cookie 名称
     * @param string    $value  Cookie 值
     * @param int|null  $expire 过期时间（秒），默认使用配置中的过期时间
     */
    public function queueCookie(string $name, string $value, ?int $expire = null): void
    {
        $payload = $this->encodePayload($value);
        $expire  = time() + ($expire ?? $this->expire);

        $this->queuedCookies[] = compact('name', 'payload', 'expire');
    }

    /**
     * 将删除 Cookie 的指令加入队列
     *
     * 通过设置过期时间为过去时间来删除指定的 Cookie。
     *
     * @param string $name 要删除的 Cookie 名称
     */
    public function queueForgetCookie(string $name): void
    {
        $this->queuedCookies[] = [
            'name'   => $name,
            'payload'=> '',
            'expire' => time() - 3600,
        ];
    }

    /**
     * 发送队列中的所有 Cookie
     *
     * 遍历队列中的 Cookie，根据是否提供 Response 对象决定使用 Symfony Cookie
     * 还是原生 setcookie 函数进行设置。发送后清空队列。
     *
     * @param Response|null $response Symfony Response 对象，为空则使用原生 setcookie
     */
    public function sendQueuedCookies(?Response $response = null): void
    {
        foreach ($this->queuedCookies as $c) {
            if ($response instanceof Response) {
                $cookie = Cookie::create(
                    $c['name'],
                    $c['payload'],
                    $c['expire'],
                    $this->path,
                    $this->domain ?: null,
                    $this->secure,
                    $this->httponly,
                    false,
                    ucfirst($this->samesite)
                );
                $response->headers->setCookie($cookie);
            } else {
                // Workerman / FPM 原生
                setcookie(
                    $c['name'],
                    $c['payload'],
                    [
                        'expires'  => $c['expire'],
                        'path'     => $this->path,
                        'domain'   => $this->domain,
                        'secure'   => $this->secure,
                        'httponly' => $this->httponly,
                        'samesite' => ucfirst($this->samesite),
                    ]
                );
                $_COOKIE[$c['name']] = $c['payload'];
            }
        }

        $this->queuedCookies = [];
    }

    // --------------------------------------
    // Response API
    // --------------------------------------

    /**
     * 直接在 Response 对象上设置 Cookie
     *
     * 快捷方法，直接将 Cookie 添加到 Symfony Response 对象的头部。
     *
     * @param Response  $response Symfony Response 对象
     * @param string    $name     Cookie 名称
     * @param string    $value    Cookie 值
     * @param int|null  $expire   过期时间（秒），默认使用配置中的过期时间
     */
    public function setResponseCookie(Response $response, string $name, string $value, ?int $expire = null): void
    {
        $payload = $this->encodePayload($value);
        $cookie  = Cookie::create(
            $name,
            $payload,
            time() + ($expire ?? $this->expire),
            $this->path,
            $this->domain ?: null,
            $this->secure,
            $this->httponly,
            false,
            ucfirst($this->samesite)
        );
        $response->headers->setCookie($cookie);
    }

    /**
     * 在 Response 对象上删除 Cookie
     *
     * 通过设置过期时间为过去时间来删除指定的 Cookie。
     *
     * @param Response $response Symfony Response 对象
     * @param string   $name     要删除的 Cookie 名称
     */
    public function forgetResponseCookie(Response $response, string $name): void
    {
        $cookie = Cookie::create(
            $name,
            '',
            time() - 3600,
            $this->path,
            $this->domain ?: null,
            $this->secure,
            $this->httponly,
            false,
            ucfirst($this->samesite)
        );
        $response->headers->setCookie($cookie);
    }

    // --------------------------------------
    // 读取 Cookie
    // --------------------------------------

    /**
     * 从请求中获取 Cookie 值
     *
     * 从 Request 对象中获取指定名称的 Cookie，并自动解码和解密。
     *
     * @param Request $request Symfony Request 对象
     * @param string  $name    Cookie 名称
     *
     * @return string|null Cookie 值，不存在或验证失败返回 null
     */
    public function get(Request $request, string $name): ?string
    {
        $raw = $request->cookies->get($name);
        if (! $raw) {
            return null;
        }

        return $this->decodePayload($raw);
    }

    // --------------------------------------
    // 加密 + 签名 + 封装
    // --------------------------------------

    /**
     * 编码 Cookie 值
     *
     * 对 Cookie 值进行可选加密，然后添加签名，最后进行 Base64 URL 安全编码。
     *
     * @param string $value 原始 Cookie 值
     *
     * @return string 编码后的 Cookie 值
     */
    protected function encodePayload(string $value): string
    {
        $data = $this->encrypt ? $this->encryptValue($value) : $value;
        $sig  = $this->sign($data);

        $json = json_encode(['data' => $data, 'sig' => $sig], JSON_UNESCAPED_SLASHES);

        // 全流程统一 URL-safe Base64
        return $this->base64url_encode($json);
    }

    /**
     * 解码 Cookie 值
     *
     * 对编码后的 Cookie 值进行解码，验证签名，解密数据（如果启用加密）。
     *
     * @param string $payload 编码后的 Cookie 值
     *
     * @return string|null 解码后的原始值，验证失败返回 null
     */
    protected function decodePayload(string $payload): ?string
    {
        $json = $this->base64url_decode($payload);
        if (! $json) {
            return null;
        }

        $arr = json_decode($json, true);
        if (! is_array($arr) || ! isset($arr['data'], $arr['sig'])) {
            return null;
        }

        if (! $this->verify($arr['data'], $arr['sig'])) {
            return null;
        }

        return $this->encrypt ? $this->decryptValue($arr['data']) : $arr['data'];
    }

    // ------------------------ 内部加密/签名方法 ------------------------

    // --------------------------------------
    // 加密 / 解密
    // --------------------------------------

    /**
     * 加密 Cookie 值
     *
     * 使用配置的加密算法对值进行加密，返回 Base64 URL 安全编码的密文。
     *
     * @param string $value 原始值
     *
     * @return string 加密并编码后的值
     */
    protected function encryptValue(string $value): string
    {
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv    = random_bytes($ivLen);

        $ciphertext = openssl_encrypt(
            $value,
            $this->cipher,
            $this->secret,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $this->base64url_encode($iv . $ciphertext);
    }

    /**
     * 解密 Cookie 值
     *
     * 对加密后的值进行解密，返回原始值。
     *
     * @param string $encoded 加密编码后的值
     *
     * @return string 解密后的原始值
     */
    protected function decryptValue(string $encoded): string
    {
        $data  = $this->base64url_decode($encoded);
        $ivLen = openssl_cipher_iv_length($this->cipher);

        $iv         = substr($data, 0, $ivLen);
        $ciphertext = substr($data, $ivLen);

        $plain = openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $this->secret,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $plain ?: '';
    }

    // --------------------------------------
    // 签名
    // --------------------------------------

    /**
     * 对数据生成 HMAC 签名
     *
     * 使用 SHA256 算法和密钥对数据进行签名，确保数据完整性。
     *
     * @param string $data 待签名的数据
     *
     * @return string 签名字符串
     */
    protected function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->secret);
    }

    /**
     * 验证签名
     *
     * 使用时序安全比较验证签名是否正确，防止时序攻击。
     *
     * @param string $data 原始数据
     * @param string $sig  待验证的签名
     *
     * @return bool 签名验证通过返回 true，否则返回 false
     */
    protected function verify(string $data, string $sig): bool
    {
        return hash_equals($this->sign($data), $sig);
    }

    // --------------------------------------
    // Base64 Url-safe
    // --------------------------------------

    /**
     * URL 安全的 Base64 编码
     *
     * 将标准 Base64 编码中的 '+' 和 '/' 替换为 '-' 和 '_'，
     * 并移除填充字符 '='，使其可安全用于 URL 和 Cookie。
     *
     * @param string $data 待编码的数据
     *
     * @return string 编码后的字符串
     */
    protected function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * URL 安全的 Base64 解码
     *
     * 将 URL 安全的 Base64 字符串还原为原始数据，
     * 自动补齐填充字符。
     *
     * @param string $data 编码后的字符串
     *
     * @return string 解码后的原始数据
     */
    protected function base64url_decode(string $data): string
    {
        $pad = 4 - (strlen($data) % 4);
        if ($pad < 4) {
            $data .= str_repeat('=', $pad);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
