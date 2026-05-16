<?php

// config/middleware.php

return [
    /*
    |--------------------------------------------------------------------------
    | 全局中间件配置
    |--------------------------------------------------------------------------
    | 控制哪些中间件启用，以及它们的参数
    */
	// Csrf的配置
    'csrf_protection' => [
        'enabled' => true,
        'token_name' => '_token',
        'except' => [
            '/api/*',
            '/admin/*',
            '/Admin/*',
            '/webhook/*',
            '/payment/notify'
        ],
        'error_message' => '请求无效，请刷新页面后重试。',
        'remove_after_validation' => false, // 用完即焚
    ],

	// Referer配置
    'referer_check' => [
        'enabled' => true,
        'allowed_hosts' => [
            'localhost',
            '127.0.0.1',
            'yourdomain.com',
            'sub.yourdomain.com'
        ],
        'allowed_schemes' => ['http', 'https'],
        'except' => [
            '/api/*',
            '/admin/*',
            '/Admin/*',
            '/payment/*'
        ],
        'strict' => false, // false = 允许空 Referer（如隐私模式）
        'error_message' => '请求来源不被允许。',
    ],
	
	
    'rate_limit' => [
        'enabled' => true,
        'maxRequests' => 10000,	//60秒内的最大请求数 ,测试设大一点的数
		'period'	=> 60,  //60秒
        'except' => [
            '/api/*',
            '/admin/*',
            '/Admin/*',
            '/webhook/*',
            '/payment/notify'
        ],		
    ],
	
	'debug'	=> [
		'enabled' => env('APP_DEBUG' , true),
	],

    // 测试环境写操作保护（拦截 POST/PUT/PATCH/DELETE）
    'test_env_write_guard' => [
        'enabled' => true,
        'only_envs' => ['test', 'testing'],
        'block_methods' => ['POST', 'PUT', 'PATCH', 'DELETE'],
        'whitelist' => [
            '/api/core/login',
            '/api/core/logout',
            '/api/core/refresh',
            '/api/core/captcha*',
            '/api/core/switch-tenant',
        ],
    ],

    // 可扩展其他中间件
    // 'rate_limit' => [...]
];
