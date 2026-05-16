<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: BaseMapping.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes\Routes;

use Attribute;

/**
 * BaseMapping - HTTP 方法映射注解基类
 *
 * 提供路由映射的通用属性定义，子类继承此类实现具体的 HTTP 方法映射。
 * 支持 RESTful 风格的路由定义，包括权限控制、中间件绑定等功能。
 *
 * 子类包括：
 * - GetMapping: GET 请求映射
 * - PostMapping: POST 请求映射
 * - PutMapping: PUT 请求映射
 * - DeleteMapping: DELETE 请求映射
 * - PatchMapping: PATCH 请求映射
 *
 * @package Framework\Attributes\Routes
 */
#[Attribute(Attribute::TARGET_METHOD)]
abstract class BaseMapping
{
    /**
     * 构造函数
     *
     * @param string $path 路由路径
     * @param array $methods 允许的 HTTP 方法列表
     * @param bool|null $auth 是否需要认证，null 表示继承父级设置
     * @param array $roles 允许访问的角色列表
     * @param array $middleware 中间件列表
     * @param array $defaults 路由默认参数
     * @param array $requirements 路由参数约束
     * @param array $schemes URL 协议约束
     * @param string|null $host 主机名约束
     * @param string|null $name 路由名称
     */
    public function __construct(
        public string $path,
        public array $methods = [],
        public ?bool $auth = null,
        public array $roles = [],
        public array $middleware = [],
        public array $defaults = [],
        public array $requirements = [],
        public array $schemes = [],
        public ?string $host = null,
        public ?string $name = null
    ) {
    }
}
