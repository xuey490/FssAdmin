<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Action.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;

use Attribute;

/**
 * Action - 控制器方法动作注解
 *
 * 用于声明控制器方法的访问方式和暴露状态。
 * 可指定允许的 HTTP 方法列表以及是否对外暴露。
 *
 * 示例：
 * #[Action] // 默认暴露，不限制方法
 * #[Action(methods: ['GET', 'POST'])] // 仅允许 GET 和 POST
 * #[Action(expose: false)] // 不对外暴露
 *
 * @package Framework\Attributes
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Action
{
    /**
     * 构造函数
     *
     * @param array $methods 允许的 HTTP 方法列表，空数组表示不限制
     * @param bool $expose 是否对外暴露该动作，默认 true
     */
    public function __construct(
        public array $methods = [],
        public bool $expose = true,
    ) {}
}
