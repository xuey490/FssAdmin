<?php

return [
    'engine'          => 'laravelORM', // env('ORM_DRIVER') ?? 'thinkORM', // 'thinkORM' or 'laravelORM'
    // 默认使用的数据库连接配置
    'default'         => 'mysql',
    // 自定义时间查询规则
    'time_query_rule' => [],
    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => true,
    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',
    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // Illuminate\Database driver必须指定
            #'driver' => 'mysql',
            // 数据库类型
            'type'               => 'mysql',
            // 服务器地址 // ThinkORM 可定义多个别名
            'hostname'           => '127.0.0.1',
            // Illuminate\Database host必须指定
            //'host'             => '127.0.0.1',
            // 数据库名
            'database'           => 'fssoa',
            // 用户名
            'username'           => 'root',
            // 密码
            'password'           => 'root',
            // 端口
            'hostport'           => '3306',
            // 数据库表前缀
            'prefix'             => '',
            // 数据库连接参数
            'params'             => [],
            // 数据库编码默认采用utf8mb4
            'charset'            => 'utf8mb4',
            // 数据库调试模式
            'debug'              => true,
            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'             => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'        => false,
            // 读写分离后 主服务器数量
            'master_num'         => 1,
            // 指定从服务器序号
            'slave_no'           => '',
            // 是否严格检查字段是否存在
            'fields_strict'      => true,
            // 是否需要断线重连
            'break_reconnect'    => false,
            // 监听SQL
            'trigger_sql'        => true,
            // 开启字段缓存
            'fields_cache'       => false,
        ],
    ],

    // ---------------------------------------------------------------
    // MySQL 连接池配置（Workerman 常驻模式下生效）
    // ---------------------------------------------------------------
    'pool' => [
        'enabled'              => (bool) (env('MYSQL_POOL_ENABLED') ?? true),
        'min_connections'      => (int) (env('MYSQL_POOL_MIN') ?? 2),
        'max_connections'      => (int) (env('MYSQL_POOL_MAX') ?? 10),
        'borrow_timeout'       => (float) (env('MYSQL_POOL_TIMEOUT') ?? 3.0),
        'retry_attempts'       => (int) (env('MYSQL_POOL_RETRY') ?? 3),
        'reconnect_on_failure' => (bool) (env('MYSQL_POOL_RECONNECT') ?? true),
    ],
];


/*
MysqlPool 里的 PDO 是独立创建的裸连接，和 Eloquent/ThinkORM 内部维护的连接完全隔离，两者同时运行操作同一个数据库不会互相影响。

MySQL 连接池适合用在哪些场景：

场景	推荐方式
日常 CRUD、关联查询				Eloquent ORM（简单、安全）
大批量原生 SQL、存储过程			连接池 PDO（性能更好）
需要精细控制 SQL / LOAD DATA		连接池 PDO
队列 Handler 中操作数据库			连接池 PDO（Queue Worker 里 ORM 未初始化）


┌─────────────────────────────────────────────┐
│          你的业务代码（DAO / Service）          │
│                                             │
│  Eloquent ORM ──→ Illuminate\Database PDO   │  ← ORM 自己管理的连接
│                                             │
│  PoolManager::borrow('mysql.default') ──→ PDO  │  ← 连接池管理的独立 PDO
└─────────────────────────────────────────────┘



					ORM（Eloquent/Think）			MySQL 连接池
连接来源				ORM 内部自己创建和管理				MysqlPool预建的 PDO
用途					日常 CRUD（model、查询构建器）		需要裸 PDO 的特殊场景
事务管理				ORM 负责							自己手动管理
归还					不需要							必须手动归还
互相影响				❌ 完全独立						❌ 完全独立

结论： 连接池的 PDO 连接和 ORM 的连接是两个不同的数据库连接，互不干扰。

使用连接池 PDO 的铁律：必须用 try/finally 确保 PoolManager::release() 被调用，否则连接永久泄漏，池耗尽后所有请求超时挂起。
*/