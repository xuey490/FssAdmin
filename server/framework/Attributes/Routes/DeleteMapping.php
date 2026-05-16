<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: DeleteMapping.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes\Routes;

use Attribute;

/**
 * DeleteMapping - DELETE 请求映射注解
 *
 * 用于将控制器方法映射为 DELETE 请求路由。
 * 继承 BaseMapping，固定 HTTP 方法为 DELETE。
 *
 * 示例：
 * #[DeleteMapping('/users/{id}')]
 * #[DeleteMapping('/users/{id}', auth: true, roles: ['admin'])]
 *
 * @package Framework\Attributes\Routes
 */
#[Attribute(Attribute::TARGET_METHOD)]
class DeleteMapping extends BaseMapping
{
    /**
     * 构造函数
     *
     * @param string $path 路由路径
     * @param bool|null $auth 是否需要认证
     * @param array $roles 允许访问的角色列表
     * @param array $middleware 中间件列表
     */
    public function __construct(
        string $path,
        ?bool $auth = null,
        array $roles = [],
        array $middleware = []
    ) {
        parent::__construct(
            path: $path,
            methods: ['DELETE'],
            auth: $auth,
            roles: $roles,
            middleware: $middleware
        );
    }
}
