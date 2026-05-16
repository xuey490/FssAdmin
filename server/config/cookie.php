<?php
/**
 * Cookie 配置文件
 * 
 * 所有 Cookie 相关的安全参数和默认设置均在此定义
 * 生产环境务必修改 secret 为随机生成的长字符串
 */
return [
    // Cookie 密钥（用于签名和加密，必须设置！）
    // 生成方式：openssl_random_pseudo_bytes(32) 或在线随机字符串生成器
    'secret' => env('COOKIE_SECRET', 'your-32-character-random-secret-key'),

    // Cookie 域名（空表示当前域名）本地测试设置为null
    // 子域名共享 Cookie 可设置为 .example.com（注意前缀点）
    'domain' => null , // env('COOKIE_DOMAIN', ''),

    // Cookie 路径（默认根路径，所有页面可访问）
    'path' => '/',

    // 过期时间（秒），默认 1 天（86400 秒）
    'expire' => 86400,

    // 是否仅通过 HTTPS 传输（生产环境建议开启）
    // 框架会在 Kernel 中结合 APP_ENV 自动覆盖此值
    'secure' => env('APP_ENV') === 'prod',

    // 是否仅允许通过 HTTP 协议访问（防止 JS 读取，增强安全）
    'httponly' => true,

    // SameSite 属性（防止 CSRF 攻击）
    // 可选值：'lax'（默认，宽松模式）、'strict'（严格模式）、'none'（跨域允许）
    'samesite' => 'lax',

    // 是否加密 Cookie 内容（敏感信息建议开启）
    'encrypt' => true,

    // 加密算法（支持 AES-128-CBC、AES-256-CBC,AES-128-GCM）
    'cipher' => 'AES-256-CBC',
];