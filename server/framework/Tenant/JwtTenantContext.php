<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: JwtTenantContext.php
 * @Date: 2026-03-19
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Tenant;

use Framework\Utils\JwtFactory;

/**
 * JWT Token 租户上下文管理器
 *
 * 基于现有的 JwtFactory（Lcobucci）封装，提供租户信息管理能力。
 * 兼容现有 JWT 实现，支持：
 * - 生成包含租户ID的 JWT Token
 * - 从 JWT Token 解析租户ID
 * - Token 刷新和验证
 * - 与现有 JwtFactory 完全兼容
 *
 * 使用现有的 app('jwt') 服务，配置读取 config/jwt.php
 *
 * Token 结构（claims）：
 * ```
 * {
 *   "iss": "FssPhp",           // 签发者
 *   "iat": 1234567890,         // 签发时间
 *   "exp": 1234571490,         // 过期时间
 *   "uid": 1001,               // 用户ID
 *   "tenant_id": 2001,         // 租户ID（新增）
 *   "name": "admin",           // 用户名
 *   "role": "admin"            // 角色
 * }
 * ```
 *
 * @package Framework\Tenant
 */
final class JwtTenantContext
{
    /**
     * 默认 Token 有效期（秒）
     */
    private const DEFAULT_TTL = 7200; // 2小时

    /**
     * 刷新 Token 有效期（秒）
     */
    private const REFRESH_TTL = 604800; // 7天

    /**
     * 获取 JwtFactory 实例
     *
     * @return JwtFactory
     */
    protected static function getJwtFactory(): JwtFactory
    {
        return app('jwt');
    }

    /**
     * 生成访问 Token（包含租户信息）
     *
     * 使用现有的 JwtFactory::issue() 方法
     *
     * @param array $userData 用户数据，必须包含：
     *   - uid: 用户ID（必须）
     *   - name: 用户名
     *   - tenant_id: 租户ID（可选）
     *   - role: 角色（可选）
     *   - 其他自定义 claims
     * @param int|null $ttl Token 有效期（秒），默认使用 config/jwt.php 中的 ttl
     * @return array 包含 token、expiresAt、ttl 的数组
     */
    public static function generateToken(array $userData, ?int $ttl = null): array
    {
        // 确保 tenant_id 在 claims 中
        if (!isset($userData['tenant_id']) && isset($userData['tenant_id'])) {
            $userData['tenant_id'] = $userData['tenant_id'];
        }

        return self::getJwtFactory()->issue($userData, $ttl);
    }

    /**
     * 生成刷新 Token
     *
     * 使用现有的 JwtFactory::issueRefreshToken() 方法
     *
     * @param int $userId 用户ID
     * @param int|null $ttl 有效期（秒），默认 7 天
     * @return string 刷新 Token
     */
    public static function generateRefreshToken(int $userId, ?int $ttl = null): string
    {
        $ttl = $ttl ?? self::REFRESH_TTL;
        return self::getJwtFactory()->issueRefreshToken($userId, $ttl);
    }

    /**
     * 解析 Token 获取完整 Payload
     *
     * 使用现有的 JwtFactory::parse() 方法
     *
     * @param string $token JWT Token
     * @return \Lcobucci\JWT\Token\Plain Token 对象
     * @throws \Exception Token 无效或过期时抛出
     */
    public static function decodeToken(string $token): \Lcobucci\JWT\Token\Plain
    {
        return self::getJwtFactory()->parse($token);
    }

    /**
     * 从 Token 获取租户ID
     *
     * @param string $token JWT Token
     * @return int|null 租户ID，解析失败返回 null
     */
    public static function getTenantIdFromToken(string $token): ?int
    {
        try {
            $parsed = self::decodeToken($token);
            $claims = $parsed->claims()->all();
            return $claims['tenant_id'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 从 Token 获取用户ID
     *
     * @param string $token JWT Token
     * @return int|null 用户ID，解析失败返回 null
     */
    public static function getUserIdFromToken(string $token): ?int
    {
        try {
            $parsed = self::decodeToken($token);
            $claims = $parsed->claims()->all();
            return $claims['uid'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 从 Token 获取完整用户信息
     *
     * @param string $token JWT Token
     * @return array|null 用户信息数组，解析失败返回 null
     */
    public static function getUserDataFromToken(string $token): ?array
    {
        try {
            $parsed = self::decodeToken($token);
            $claims = $parsed->claims()->all();

            return [
                'user_id' => $claims['uid'] ?? null,
                'tenant_id' => $claims['tenant_id'] ?? null,
                'username' => $claims['name'] ?? null,
                'role' => $claims['role'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 验证 Token 是否有效
     *
     * 使用现有的 JwtFactory::parse() 方法验证
     *
     * @param string $token JWT Token
     * @return bool 有效返回 true
     */
    public static function validateToken(string $token): bool
    {
        try {
            self::decodeToken($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查 Token 是否即将过期
     *
     * @param string $token JWT Token
     * @param int $threshold 提前阈值（秒），默认 300 秒（5分钟）
     * @return bool 即将过期返回 true
     */
    public static function isTokenExpiringSoon(string $token, int $threshold = 300): bool
    {
        try {
            $parsed = self::decodeToken($token);
            $exp = $parsed->claims()->get('exp');
            if ($exp instanceof \DateTimeImmutable) {
                return (time() + $threshold) >= $exp->getTimestamp();
            }
            return true;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * 刷新访问 Token
     *
     * 使用现有的 JwtFactory::refresh() 方法
     *
     * @param string $token 当前 Token
     * @param int|null $ttl 新的有效期（秒）
     * @return string 新的访问 Token
     * @throws \Exception 刷新失败时抛出
     */
    public static function refreshAccessToken(string $token, ?int $ttl = null): string
    {
        return self::getJwtFactory()->refresh($token, $ttl);
    }

    /**
     * 轮换刷新 Token（用完即焚）
     *
     * 使用现有的 JwtFactory::rotateRefreshToken() 方法
     *
     * @param string $refreshToken 当前刷新 Token
     * @return string 新的刷新 Token
     */
    public static function rotateRefreshToken(string $refreshToken): string
    {
        return self::getJwtFactory()->rotateRefreshToken($refreshToken);
    }

    /**
     * 验证刷新 Token
     *
     * 使用现有的 JwtFactory::validateRefreshToken() 方法
     *
     * @param string $refreshToken 刷新 Token
     * @return int 用户ID
     * @throws \Exception 验证失败时抛出
     */
    public static function validateRefreshToken(string $refreshToken): int
    {
        return self::getJwtFactory()->validateRefreshToken($refreshToken);
    }

    /**
     * 从 Authorization Header 提取 Token
     *
     * @param string|null $authHeader Authorization Header 值
     * @return string|null Token 字符串，未找到返回 null
     */
    public static function extractTokenFromHeader(?string $authHeader): ?string
    {
        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 获取 Token 过期时间
     *
     * @param string $token JWT Token
     * @return int|null 过期时间戳，解析失败返回 null
     */
    public static function getTokenExpireTime(string $token): ?int
    {
        try {
            $parsed = self::decodeToken($token);
            $exp = $parsed->claims()->get('exp');
            if ($exp instanceof \DateTimeImmutable) {
                return $exp->getTimestamp();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取 Token 剩余有效时间
     *
     * @param string $token JWT Token
     * @return int 剩余秒数，已过期返回 0，解析失败返回 -1
     */
    public static function getTokenRemainingTime(string $token): int
    {
        try {
            $parsed = self::decodeToken($token);
            $exp = $parsed->claims()->get('exp');
            if ($exp instanceof \DateTimeImmutable) {
                $remaining = $exp->getTimestamp() - time();
                return max(0, $remaining);
            }
            return 0;
        } catch (\Exception $e) {
            return -1;
        }
    }

    /**
     * 注销 Token
     *
     * 使用现有的 JwtFactory::revoke() 方法
     *
     * @param string $token JWT Token
     * @return void
     */
    public static function revokeToken(string $token): void
    {
        self::getJwtFactory()->revoke($token);
    }

    /**
     * 注销用户的所有 Token（踢下线）
     *
     * 使用现有的 JwtFactory::revokeAllForUser() 方法
     *
     * @param int $userId 用户ID
     * @return void
     */
    public static function revokeAllForUser(int $userId): void
    {
        self::getJwtFactory()->revokeAllForUser($userId);
    }

    /**
     * 获取 Token 的 Payload（不验证签名，仅用于调试）
     *
     * 使用现有的 JwtFactory::getPayload() 方法
     *
     * @param string $token JWT Token
     * @return array Payload 数组
     */
    public static function getPayload(string $token): array
    {
        return self::getJwtFactory()->getPayload($token);
    }

    /**
     * 解析 Token（用于刷新场景，允许过期）
     *
     * 使用现有的 JwtFactory::parseForRefresh() 方法
     *
     * @param string $token JWT Token
     * @return \Lcobucci\JWT\Token\Plain Token 对象
     * @throws \Exception 解析失败时抛出
     */
    public static function parseForRefresh(string $token): \Lcobucci\JWT\Token\Plain
    {
        return self::getJwtFactory()->parseForRefresh($token);
    }

    /**
     * 生成包含租户信息的完整登录响应
     *
     * 整合 access token 和 refresh token 生成
     *
     * @param array $userData 用户数据
     * @param int|null $ttl Token 有效期
     * @return array 包含 token、refresh_token、expires_at 的数组
     */
    public static function generateLoginTokens(array $userData, ?int $ttl = null): array
    {
        // 生成 access token
        $access = self::generateToken($userData, $ttl);

        // 生成 refresh token
        $userId = $userData['uid'] ?? 0;
        $refreshToken = self::generateRefreshToken($userId);

        return [
            'token' => $access['token'],
            'refresh_token' => $refreshToken,
            'expires_at' => $access['expiresAt'],
            'ttl' => $access['ttl'],
        ];
    }
}
