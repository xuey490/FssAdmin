<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: UserAction.php
 * @Date: 2025-12-17
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;

use App\Middlewares\UserActionMiddleware;
use Attribute;

/**
 * @UserAction
 * 用于在控制器业务执行成功后，记录用户行为到数据库表。
 *
 * 示例：
 * #[UserAction(type: 'login')]
 * #[UserAction(type: 'register')]
 */
#[Attribute(Attribute::TARGET_METHOD)]
class UserAction implements MiddlewareProviderInterface
{
    /**
     * @param string|null $type 动作类型标识
     */
    public function __construct(
        public ?string $type = null
    ) {
    }

    public function getMiddleware(): string|array
    {
        return UserActionMiddleware::class;
    }
}