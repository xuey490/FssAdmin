<?php

declare(strict_types=1);

/**
 * Casbin 权限配置
 *
 * @package App\Config
 * @author  Genie
 * @date    2026-03-12
 */

return [
    // 模型配置文件路径
    'model' => [
        // RBAC 模型配置文件
        'path' => config_path('casbin_rbac_model.conf'),
        // 模型内容 (可选，如果设置了 path 则优先使用文件)
        'content' => null,
    ],

    // 适配器配置
    'adapter' => [
        // 数据库连接 (使用默认数据库)
        'connection' => null,
        // Casbin 规则表名
        'table_name' => 'casbin_rule',
    ],

    // 是否开启权限缓存
    'cache' => [
        'enabled' => true,
        // 缓存驱动: redis, file, memory
        'driver' => 'redis',
        // 缓存键前缀
        'prefix' => 'casbin:',
        // 缓存过期时间 (秒)
        'ttl' => 3600,
    ],

    // 默认角色
    'default_role' => 'guest',

    // 超级管理员角色
    'super_admin_role' => 'super_admin',

    // 权限验证失败时的处理
    'on_denied' => [
        // 是否抛出异常
        'throw_exception' => false,
        // 自定义异常类
        'exception_class' => \App\Exceptions\PermissionDeniedException::class,
    ],
];
