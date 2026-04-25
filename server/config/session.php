<?php
// config/session.php

return [
	// 可选值：file / redis / redis_grouped
    'storage_type' => 'redis_grouped' , //env('SESSION_STORAGE') ?? 'redis', // 'file' 或 'redis' 'redis_grouped'

    'options' => [
        'cookie_secure'   => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'lax',
        'use_cookies'     => true,
        'gc_maxlifetime'  => 3600, // 单位秒
        'gc_probability'  => 1,
        'gc_divisor'      => 100,
		'name'            => 'file_session_', // ← 这就是file session的cookie 名称（前缀）
    ],
	
    // 新增：仅用于 file 存储的路径
    'file_save_path' => BASE_PATH . '/storage/sessions',
	

    // redis_grouped 模式扩展参数
    'redis' => [
        'ttl'          => (int) (env('SESSION_TTL') ?? 3600),
        'group_prefix' 		=> env('SESSION_GROUP_PREFIX', 'session:default'),
    ],
	
    // encryption
    'encrypt' => [
        'enabled' => true,
        'key'     => 'your-secret-key-32bytes',
    ],

    'rate_limit' => [
        'enabled' => true,
        'limit'   => 60,   // 每分钟最多 60 次访问
        'window'  => 60,
    ],

    'queue' => [
        'enabled' => true,
    ],
	
];