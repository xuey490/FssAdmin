<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Prefix.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes\Routes;

use Attribute;

/**
 * Prefix - 路由前缀注解
 *
 * 用于在控制器类上定义路由前缀，所有方法路由将自动添加此前缀。
 * 支持统一的中间件绑定和权限控制。
 *
 * 示例：
 * #[Prefix('/api/users')]
 * #[Prefix('/api/admin', middleware: [AuthMiddleware::class])]
 *
 * @package Framework\Attributes\Routes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Prefix
{
    /**
     * 构造函数
     *
     * @param string $prefix 路由前缀
     * @param array $middleware 中间件列表
     * @param bool|null $auth 是否需要认证
     * @param array $roles 允许访问的角色列表
     */
    public function __construct(
        public string $prefix,
        public array $middleware = [],
        public ?bool $auth = null,
        public array $roles = []
    ) {
    }
}
