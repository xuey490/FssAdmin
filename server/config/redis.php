<?php
// config/redis.php

/**
 * Redis 多节点配置清单
 *
 * 说明：
 * - 按优先顺序排列，系统会从第一个节点开始尝试连接
 * - 如果连接失败，会自动切换到下一台
 * - 支持任意数量的 Redis 节点
 */

return [
    [
        'name'     => 'primary',
        'host'     => env('REDIS_HOST') ?? '127.0.0.1',
        'port'     => (int) (env('REDIS_PORT') ?? 6379),
        'password' => env('REDIS_PASSWORD') ?? null,
        'database' => (int) (env('REDIS_DB') ?? 0),
        'timeout'  => 2.0,
    ],
    [
        'name'     => 'backup-1',
        'host'     => '192.168.0.100',
        'port'     => 6379,
        'password' => null,
        'database' => 1,
        'timeout'  => 2.0,
    ],
    [
        'name'     => 'backup-2',
        'host'     => '192.168.0.101',
        'port'     => 6379,
        'password' => null,
        'database' => 2,
        'timeout'  => 2.0,
    ],
];

/*
return [
    'host'     =>  env('REDIS_HOST') ?? '127.0.0.1',
    'port'     => (int) (env('REDIS_PORT') ?? 6379),
    'database' => (int) (env('REDIS_DB') ?? 0),
    'password' => env('REDIS_PASSWORD') ?? null,
    'timeout'  => 3,
];

*/