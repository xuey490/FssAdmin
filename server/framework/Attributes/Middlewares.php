<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Middlewares.php
 * @Date: 2025-12-18
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;

use Attribute;

/**
 * @Middlewares
 * 通用中间件注解，用于在控制器或方法上声明多个中间件。
 * 调度器会自动读取此注解返回的数组，并合并到执行链中。
 *
 * 示例：
 * #[Middlewares([CorsMiddleware::class, RateLimitMiddleware::class])]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Middlewares implements MiddlewareProviderInterface
{
    /**
     * @param array<string> $middlewares 中间件类名数组
     */
    public function __construct(
        public array $middlewares = []
    ) {
    }

    /**
     * 直接返回中间件数组
     */
    public function getMiddleware(): string|array
    {
        return $this->middlewares;
    }
}