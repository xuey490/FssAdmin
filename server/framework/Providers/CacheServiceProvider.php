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

namespace Framework\Providers;

use Framework\Cache\CacheFactory;
use Framework\Cache\ThinkAdapter;
use Framework\Cache\ThinkCache;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class CacheServiceProvider implements ServiceProviderInterface
{
    // public function __invoke(ContainerConfigurator $configurator): void
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 定义缓存管理器服务（单例）
        $cacheConfig = require BASE_PATH . '/config/cache.php';

        // 1 注册 ThinkCache 并注入配置
        $services->set(ThinkCache::class)
            // ->arg('$config', require __DIR__ . '/cache.php')
            ->args([$cacheConfig])
            ->public();

        // 2️ 注册 ThinkAdapter（即最终 Cache 服务）
        $services->set(ThinkAdapter::class)
            // 直接调用 ThinkCache::create()
            ->factory([service(ThinkCache::class), 'create'])
            ->public();

        // 3️ 可选：别名方式简化访问
        $services->set('cache', ThinkAdapter::class)
            ->factory([service(ThinkCache::class), 'create'])
            ->public();

        // symfony/cache 注册服务
        $services->set(CacheFactory::class)
            ->args([$cacheConfig])->public();

        // 只注册 TagAwareAdapter 对外使用
        $services->set('sf_cache', TagAwareAdapter::class)
            ->factory([service(CacheFactory::class), 'create'])->public();
    }

    public function boot(ContainerInterface $container): void
    # public function boot(ContainerConfigurator $container): void
    {}
}
