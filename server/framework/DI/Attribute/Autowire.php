<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-19
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */
 
namespace Framework\DI\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Autowire
{
    /**
     * @param string $scope 作用域
     */
    public function __construct(
        public string $scope = Scope::SINGLETON
    ) {}
}
