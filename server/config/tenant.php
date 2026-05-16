<?php
// 租户配置文件
return [
    // 默认租户ID（单租户场景使用）
    'default_tenant_id' => 1,
    // 租户标识键名（Cookie/Session/请求头）
    'tenant_key' => 'tenant_id',
    // 租户表名（默认：tenant）
    'table_name' => 'tenant',
    // 缓存过期时间（秒）
    'cache_expire' => 3600,
];