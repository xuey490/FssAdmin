<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Route.php
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;

use Attribute;

/**
 * Route - 路由注解类
 *
 * 完全兼容 Symfony 路由写法的 Attribute 路由定义类。
 * 支持在控制器类和方法上声明路由信息。
 *
 * 支持的标准属性：
 * - path: 路由路径
 * - methods: 允许的 HTTP 方法
 * - name: 路由名称
 * - defaults: 默认参数
 * - requirements: 参数约束
 * - schemes: URL 协议约束
 * - host: 主机名约束
 *
 * 扩展属性：
 * - prefix: 路由前缀
 * - group: 路由分组
 * - middleware: 中间件列表
 * - auth: 是否需要认证
 * - roles: 允许的角色列表
 *
 * 示例：
 * #[Route('/users', methods: ['GET'])]
 * #[Route('/users/{id}', methods: ['GET', 'PUT'], requirements: ['id' => '\d+'])]
 *
 * @package Framework\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * 构造函数
     *
     * @param string $path 路由路径
     * @param array $methods 允许的 HTTP 方法列表
     * @param string|null $name 路由名称
     * @param array $defaults 路由默认参数
     * @param array $requirements 路由参数约束（正则表达式）
     * @param array $schemes URL 协议约束（如 ['https']）
     * @param string|null $host 主机名约束
     * @param string|null $prefix 路由前缀（控制器级）
     * @param string|null $group 路由分组
     * @param array $middleware 中间件列表
     * @param bool|null $auth 是否需要认证
     * @param array $roles 允许访问的角色列表
     */
    public function __construct(
        public string $path = '',
        public array $methods = [],
        public ?string $name = null,
        public array $defaults = [],
        public array $requirements = [],
        public array $schemes = [],
        public ?string $host = null,

        // ==== 扩展属性 ====
        public ?string $prefix = null,
        public ?string $group = null,
        public array $middleware = [],

        // ==== 权限控制 ====
        public ?bool $auth = null,
        public array $roles = [],
    ) {
    }
}
