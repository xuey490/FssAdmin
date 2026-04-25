<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;

/**
 * @Auth
 * 用于声明需要登录验证和角色控制的控制器/方法。
 *
 * 示例：
 * #[Auth]
 * #[Auth(roles: ['admin', 'editor'])]
 * #[Auth(required: false)] // 可选认证
 */

use Attribute;
// 假设你的 AuthMiddleware 在这里，你需要在这里引入它，而不是在 Loader 里
use App\Middlewares\AuthMiddleware; 

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Auth implements MiddlewareProviderInterface // <--- 实现接口
{
    /**
     * @param array<string> $roles    允许访问的角色列表
     * @param bool          $required 是否强制要求认证（false 表示匿名也能访问）
     * @param string|null          $guard  指定认证守卫 留空则使用默认
     */
    public function __construct(
        public bool $required = true,
		public ?array $roles = [], 
        public ?string $guard = null
    ) {}

    // 🔥 告诉 Loader：只要用了我这个注解，就请加载 AuthMiddleware
    public function getMiddleware(): string|array
    {
        return AuthMiddleware::class;
    }
}