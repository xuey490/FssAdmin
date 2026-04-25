<?php
// config/jwt.php

return [
    // ==================== 基础配置 ====================

    'single_device_login' => env('JWT_SINGLE_DEVICE_LOGIN', false), // 默认允许多点登录

    'secret' => env('JWT_SECRET', 'your-secret-key-here_dGkiOiJhNTE0YzhhMzZjZGRkZDhkM2FlOGY2NDRhMDdlMTJjYXQiOjE3Nj'),

    'algo' => 'HS256', // 支持 HS256, HS384, HS512, RS256 等

    'ttl' => 3600, // 默认 1 小时（秒）

    'refresh_ttl' => 86400, // 刷新令牌有效期：24 小时

    'audience' => 'FssPhp.Inc',

    'issuer' => 'FssPhp',

    'blacklist_enabled' => true,

    'blacklist_grace_period' => 60, // 黑名单宽限期（秒），防止并发请求问题

    // ==================== 多租户配置 ====================

    /**
     * 认证模式
     * - jwt: 仅使用 JWT Token 认证
     * - session: 仅使用 Session/Cookie 认证
     * - auto: 自动检测（优先 JWT，其次 Session）
     */
    'auth_mode' => env('JWT_AUTH_MODE', 'auto'),

    /**
     * 租户ID请求头名称
     * 用于从自定义 Header 获取租户ID
     */
    'tenant_header' => env('JWT_TENANT_HEADER', 'X-Tenant-ID'),

    /**
     * 租户ID查询参数名称
     * 用于调试时从 URL 参数获取租户ID
     */
    'tenant_query_param' => env('JWT_TENANT_PARAM', 'tenant_id'),

    /**
     * 调试模式
     * 开启后允许从 Query 参数获取租户ID（仅用于开发环境）
     */
    'tenant_debug' => env('JWT_TENANT_DEBUG', false),
];