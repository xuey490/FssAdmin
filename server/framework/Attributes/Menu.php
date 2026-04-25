<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Menu.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;

use Attribute;

/**
 * @Menu
 * 用于声明控制器或方法在后台菜单中的显示。
 *
 * 示例：
 * #[Menu(title: '用户管理', icon: 'users', order: 10)]
 * #[Menu(title: '编辑用户', parent: '用户管理', hidden: true)]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Menu
{
    /**
     * @param string      $title  菜单标题
     * @param null|string $icon   图标名称
     * @param int         $order  排序
     * @param null|string $parent 父级菜单名称
     * @param bool        $hidden 是否在菜单中隐藏
     */
    public function __construct(
        public string $title,
        public ?string $icon = null,
        public int $order = 0,
        public ?string $parent = null,
        public bool $hidden = false
    ) {
    }
}
