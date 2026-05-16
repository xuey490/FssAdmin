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

namespace Framework\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * CSRF 令牌管理器.
 *
 * 该类负责生成、验证和管理 CSRF（跨站请求伪造）令牌。
 * 通过 Session 存储令牌，提供安全的令牌验证机制以防止 CSRF 攻击。
 *
 * 主要功能：
 * - 生成 CSRF 令牌
 * - 刷新 CSRF 令牌
 * - 验证令牌有效性（使用时序安全比较）
 * - 移除令牌
 *
 * @package Framework\Security
 */
class CsrfTokenManager
{
    /**
     * Session 存储实例，用于持久化 CSRF 令牌.
     *
     * @var SessionInterface
     */
    private SessionInterface $session;

    /**
     * 令牌存储的命名空间，用于区分不同用途的令牌.
     *
     * @var string
     */
    private string $namespace;

    /**
     * 构造函数，初始化 CSRF 令牌管理器.
     *
     * @param SessionInterface $session   Session 存储实例
     * @param string           $namespace 令牌命名空间，默认为 'csrf_token'
     */
    public function __construct(SessionInterface $session, string $namespace = 'csrf_token')
    {
        $this->session   = $session;
        $this->namespace = $namespace;
    }

    /**
     * 获取或创建 CSRF 令牌.
     *
     * 如果指定 ID 的令牌已存在，则返回现有令牌；
     * 否则生成新的 64 位十六进制令牌并存储到 Session。
     *
     * @param string $tokenId 令牌标识符，用于区分不同表单或功能，默认为 'default'
     *
     * @return string 64 位十六进制 CSRF 令牌字符串
     */
    public function getToken(string $tokenId = 'default'): string
    {
        $key = $this->getSessionKey($tokenId);

        $token = $this->session->get($key);
        if ($token) {
            return $token;
        }

        $token = bin2hex(random_bytes(32));
        $this->session->set($key, $token);

        return $token;
    }

    /**
     * 刷新 CSRF 令牌.
     *
     * 强制生成新的令牌并覆盖 Session 中存储的旧令牌。
     * 适用于需要频繁更换令牌的安全敏感场景。
     *
     * @param string $tokenId 令牌标识符，默认为 'default'
     *
     * @return string 新生成的 64 位十六进制 CSRF 令牌
     */
    public function refreshToken(string $tokenId = 'default'): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set($this->getSessionKey($tokenId), $token);
        return $token;
    }


    /**
     * 验证 CSRF 令牌的有效性.
     *
     * 使用 hash_equals 函数进行时序安全的字符串比较，
     * 防止时序攻击（Timing Attack）。
     *
     * @param string $tokenId 令牌标识符
     * @param string $token   待验证的令牌值
     *
     * @return bool 令牌有效返回 true，无效或不存在返回 false
     */
    public function isTokenValid(string $tokenId, string $token): bool
    {
        $expected = $this->session->get($this->getSessionKey($tokenId));
        if (! $expected) {
            return false;
        }
        return hash_equals($expected, $token);
    }

    /**
     * 移除指定的 CSRF 令牌.
     *
     * 从 Session 中删除指定 ID 的令牌，通常在一次请求完成后调用。
     *
     * @param string $tokenId 令牌标识符，默认为 'default'
     *
     * @return void
     */
    public function removeToken(string $tokenId = 'default'): void
    {
        $this->session->remove($this->getSessionKey($tokenId));
    }

    /**
     * 生成 Session 存储键名.
     *
     * 根据命名空间和令牌 ID 组合生成唯一的 Session 键名。
     *
     * @param string $tokenId 令牌标识符
     *
     * @return string 组合后的 Session 键名，格式为 "{namespace}.{tokenId}"
     */
    private function getSessionKey(string $tokenId): string
    {
        return $this->namespace . '.' . $tokenId;
    }
}
