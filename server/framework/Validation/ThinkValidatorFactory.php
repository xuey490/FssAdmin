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

namespace Framework\Validation;

use think\Validate;

class ThinkValidatorFactory
{
    /**
    * @param array<mixed> $rule
    * @param array<mixed> $message
    */
    public function create(array $rule = [], array $message = []): Validate
    {
        // 可在此处统一配置全局规则/提示（如手机号、邮箱正则）
        $globalRule = [
            // 'mobile' => '/^1[3-9]\d{9}$/',
            // 'email' => 'email',
        ];
        $globalMsg = [
            // 'mobile' => '手机号格式错误',
            // 'email' => '邮箱格式错误',
        ];

        $validate = new Validate();
        $validate->rule(array_merge($globalRule, $rule));
        $validate->message(array_merge($globalMsg, $message));

        return $validate;
    }
}
