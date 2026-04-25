<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Role.php
 * @Date: 2025-12-17
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;

use App\Middlewares\RoleMiddleware;
use Attribute;

/**
 * @Role
 * 用于限制控制器或方法的访问权限。
 *
 * 示例：
 * #[Role(['admin'])] // 仅管理员可访问
 * #[Role(['admin', 'editor'])] // 管理员和编辑可访问
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Role implements MiddlewareProviderInterface
{
    /**
     * @param array<string> $roles 允许访问的角色代码列表
     */
    public function __construct(
        public array $roles = []
    ) {
    }

    /**
     * 绑定中间件
     */
    public function getMiddleware(): string|array
    {
        return RoleMiddleware::class;
    }
}