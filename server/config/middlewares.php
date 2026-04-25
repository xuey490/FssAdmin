<?php

return [
    // 测试环境写操作保护（仅在 APP_ENV 命中配置时生效）
    \App\Middlewares\TestEnvWriteGuardMiddleware::class,

    // 操作日志中间件（记录写操作）
    \App\Middlewares\OperationLogMiddleware::class,

	//租户id解析中间件
	\App\Middlewares\TenantMiddleware::class,

    // 以下中间件按需启用：
   //\App\Middlewares\CasbinRbacMiddleware::class,

];
