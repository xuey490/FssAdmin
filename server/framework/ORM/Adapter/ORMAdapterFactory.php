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

namespace Framework\ORM\Adapter;

use Framework\Core\App;
use Framework\ORM\Exception\Exception;
use Framework\ORM\Factories\LaravelORMFactory;
use Framework\ORM\Factories\ThinkphpORMFactory;

/**
 * ORM适配器工厂类
 * 
 * 用于创建不同ORM框架的适配器实例，支持Laravel Eloquent和ThinkPHP ORM。
 * 采用工厂模式，根据配置动态创建对应的ORM适配器。
 */
class ORMAdapterFactory
{
    /**
     * 创建ORM适配器实例
     * 
     * 根据指定的ORM模式创建对应的工厂实例，支持thinkORM和laravelORM两种模式。
     * 
     * @param  string              $mode  ORM模式，可选值：thinkORM / laravelORM
     * @param  mixed               $model 模型类名或实例，用于初始化ORM工厂
     * @return ORMAdapterInterface 返回对应ORM的适配器实例
     * @throws Exception           当传入无效的ORM类型时抛出异常
     */
    public static function createAdapter(string $mode, mixed $model = null): mixed
    {
        // 对应 ThinkphpORMFactory 构造函数的参数名
        $params = ['model' => $model];

        return match ($mode) {
            'thinkORM'   => App::make(ThinkphpORMFactory::class, $params),
            'laravelORM' => App::make(LaravelORMFactory::class, $params),
            default      => throw new Exception('Invalid ORM type: ' . $mode),
        };
    }
}
