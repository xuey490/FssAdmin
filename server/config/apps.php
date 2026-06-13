<?php
/**
 * 多应用配置
 *
 * 定义框架中的各个子应用，支持：
 * - 独立控制器目录和命名空间
 * - URL 前缀自动路由（如 /admin/xxx → App\Admin\Controllers）
 * - 域名绑定自动路由（如 admin.example.com/xxx → App\Admin\Controllers，无前缀）
 * - Attribute 注解路由扫描
 * - 自动注册 PSR-4 命名空间（Models, Providers, Services 等子命名空间均自动加载）
 * - 自动发现服务提供者（每个应用的 Providers/ 目录自动扫描）
 *
 * 域名绑定机制：
 * - 当请求 Host 匹配某应用的 domain 值时，该请求无需 URL 前缀即可路由到该应用
 * - 路由优先级：定义路由 > 插件自动路由 > 前缀自动路由 > 域名绑定自动路由 > 默认兜底
 * - prefix 同时作为域名绑定的标识 key，与 getAppAutoRouteNamespaces() 对齐
 * - domain 为空时仅通过 prefix 访问
 *
 * 字段说明：
 * - dir:       控制器目录路径
 * - namespace: 控制器命名空间
 * - prefix:    URL 前缀，也是域名绑定的标识 key（建议全小写）
 * - domain:    绑定的完整域名，如 'admin.example.com'（可选）
 *
 * 注意：新增应用只需在此文件中添加配置，无需修改 composer.json 或运行 composer dump-autoload
 *       'default' 应用是传统 app/Controllers 目录，prefix 为空保持向后兼容
 *       其他应用的 prefix 作为 URL 第一段路径，用于自动路由
 */
return [
    // ============================================================
    // 默认应用（向后兼容）
    // 访问: /controller/action 或 /子目录/controller/action
    // 命名空间: App\Controllers\
    // ============================================================
    'default' => [
        'dir'       => BASE_PATH . '/app/Controllers',
        'namespace' => 'App\Controllers',
        'prefix'    => '',  // 无前缀，作为兜底
    ],

    // ============================================================
    // 后台管理应用
    // 访问: /admin/controller/action
    // 域名绑定: 设置 domain 后，admin.example.com/xxx 无前缀访问
    // 命名空间: App\Admin\Controllers\
    // ============================================================
    'admin' => [
        'dir'       => BASE_PATH . '/app/admin/Controllers',
        'namespace' => 'App\Admin\Controllers',
        'prefix'    => '',	//http://127.0.0.1:8000/xxx/home/list，xxx是prefix
        'domain'    => '',  // 可选：绑定域名，如 'admin.example.com'。为空则仅通过 prefix 访问
    ],

    // ============================================================
    // 系统 API 应用
    // 访问: /system/controller/action
    // 域名绑定: 设置 domain 后，system.example.com/xxx 无前缀访问
    // 命名空间: App\System\Controllers\
    // ============================================================
    'system' => [
        'dir'       => BASE_PATH . '/app/system/Controllers',
        'namespace' => 'App\System\Controllers',
        'prefix'    => 'system',
        'domain'    => '',  // 可选：绑定域名，如 'system.example.com'
    ],

    // ============================================================
    // 测试应用
    // 访问: /test/controller/action
    // 命名空间: App\Test\Controllers\
    // ============================================================
    'test' => [
        'dir'       => BASE_PATH . '/app/test/Controllers',
        'namespace' => 'App\Test\Controllers',
        'prefix'    => 'test',
        'domain'    => '',
    ],
];
