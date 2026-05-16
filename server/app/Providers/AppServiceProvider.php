<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: AppServiceProvider.php
 * @Date: 2026-1-8
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Framework\Container\ServiceProviderInterface;
use InvalidArgumentException;

/**
 * 统一注册 App 目录下的所有服务（带路径合法性检查）
 * 替代原有的 ControllersProvider、MiddlewaresProvider、ModelsProvider
 */
final class AppServiceProvider implements ServiceProviderInterface
{
    /**
     * 定义需要自动注册的模块配置
     * 格式：[命名空间前缀 => 相对路径]
     */
    private const MODULES = [
        'App\\Controllers\\' 			=> '/Controllers',
        'App\\Middlewares\\' 			=> '/Middlewares',
        'App\\Models\\'      			=> '/Models',
        'App\\Dao\\'      	 			=> '/Dao',
        // 如需新增模块，直接在这里添加即可
        'App\\Repository\\' 			=> '/Repository',
        'App\\Services\\'     			=> '/Services',
        'App\\Validate\\'     			=> '/Validate',
    ];

    /**
     * 注册所有 App 目录下的服务（带路径检查）
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        $appDir = \dirname(__DIR__); // 获取 App 目录的绝对路径

        // 验证 App 根目录是否存在
        if (!is_dir($appDir)) {
            throw new InvalidArgumentException("App 根目录不存在: {$appDir}");
        }

        // 遍历所有模块，批量注册服务（跳过不存在的目录）
        foreach (self::MODULES as $namespace => $relativePath) {
            $fullDir = $appDir . $relativePath; // 模块完整目录路径
            $scanPath = $fullDir . '/**/*.php'; // 扫描的文件路径

            // 核心判断：检查目录是否存在且是合法目录
            if (!is_dir($fullDir)) {
                // 可选：开发环境下可以打印提示，生产环境建议注释
                // trigger_error("模块目录不存在，跳过加载: {$fullDir}", E_USER_NOTICE);
                continue; // 跳过不存在的目录，不执行加载
            }

            // 注册当前模块的所有类（仅当目录存在时执行）
            $services->load($namespace, $scanPath)
                ->autowire()      // 自动注入依赖
                ->autoconfigure() // 自动配置（如标签、别名等）
                ->public();       // 标记为公开服务，支持动态获取
        }
    }

    /**
     * 启动回调（如需初始化逻辑可在这里添加）
     */
    public function boot(ContainerInterface $container): void
    {
        // 可选：添加全局启动逻辑
        // echo "[AppServiceProvider] All existing App services are booted.\n";
    }
}