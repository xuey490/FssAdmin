<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginCacheManager.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Plugin;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Routing\RouteCollection;
use RuntimeException;

/**
 * 插件缓存管理器
 *
 * 管理插件相关的缓存：
 * - 路由缓存
 * - 插件清单缓存
 * - 插件配置缓存
 *
 * @package Framework\Plugin
 */
class PluginCacheManager
{
    /**
     * 缓存键前缀
     */
    private const CACHE_PREFIX = 'plugin_';

    /**
     * 路由缓存文件
     */
    private const ROUTE_CACHE_FILE = BASE_PATH . '/storage/cache/routes.php';

    /**
     * 插件缓存目录
     */
    private const PLUGIN_CACHE_DIR = BASE_PATH . '/storage/cache/plugins';

    /**
     * PSR-16 缓存实例
     *
     * @var CacheInterface|null
     */
    private ?CacheInterface $cache = null;

    /**
     * 是否启用缓存
     *
     * @var bool
     */
    private bool $cacheEnabled = true;

    /**
     * 默认缓存有效期（秒）
     *
     * @var int
     */
    private int $defaultTtl = 3600;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->ensureCacheDirectory();
    }

    /**
     * 设置缓存实例
     *
     * @param CacheInterface $cache
     * @return self
     */
    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * 设置是否启用缓存
     *
     * @param bool $enabled
     * @return self
     */
    public function setCacheEnabled(bool $enabled): self
    {
        $this->cacheEnabled = $enabled;
        return $this;
    }

    /**
     * 设置默认缓存有效期
     *
     * @param int $ttl
     * @return self
     */
    public function setDefaultTtl(int $ttl): self
    {
        $this->defaultTtl = $ttl;
        return $this;
    }

    /**
     * 清除所有插件相关缓存
     *
     * @return bool
     */
    public function clearAll(): bool
    {
        $success = true;

        // 清除路由缓存
        if (!$this->clearRouteCache()) {
            $success = false;
        }

        // 清除插件清单缓存
        if (!$this->clearManifestCache()) {
            $success = false;
        }

        // 清除插件配置缓存
        if (!$this->clearConfigCache()) {
            $success = false;
        }

        // 清除 PSR-16 缓存
        if ($this->cache !== null) {
            try {
                // 只清除插件相关的缓存
                $keys = ['manifests', 'controller_dirs'];
                foreach ($keys as $key) {
                    $this->cache->delete(self::CACHE_PREFIX . $key);
                }
            } catch (\Throwable $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 清除路由缓存
     *
     * @return bool
     */
    public function clearRouteCache(): bool
    {
        if (file_exists(self::ROUTE_CACHE_FILE)) {
            return unlink(self::ROUTE_CACHE_FILE);
        }
        return true;
    }

    /**
     * 清除插件清单缓存
     *
     * @return bool
     */
    public function clearManifestCache(): bool
    {
        $manifestCacheFile = self::PLUGIN_CACHE_DIR . '/manifests.php';
        if (file_exists($manifestCacheFile)) {
            return unlink($manifestCacheFile);
        }

        // 也清除 PSR-16 缓存
        if ($this->cache !== null) {
            try {
                $this->cache->delete(self::CACHE_PREFIX . 'manifests');
            } catch (\Throwable $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * 清除配置缓存
     *
     * @param string|null $pluginName 插件名称，null 清除全部
     * @return bool
     */
    public function clearConfigCache(?string $pluginName = null): bool
    {
        $configCacheDir = self::PLUGIN_CACHE_DIR . '/configs';

        if ($pluginName !== null) {
            // 清除指定插件的配置缓存
            $cacheFile = "{$configCacheDir}/{$pluginName}.php";
            if (file_exists($cacheFile)) {
                return unlink($cacheFileFile);
            }
        } else {
            // 清除所有配置缓存
            if (is_dir($configCacheDir)) {
                $files = glob($configCacheDir . '/*.php');
                foreach ($files ?: [] as $file) {
                    unlink($file);
                }
            }
        }

        return true;
    }

    /**
     * 获取缓存的路由集合
     *
     * @return RouteCollection|null
     */
    public function getCachedRoutes(): ?RouteCollection
    {
        if (!file_exists(self::ROUTE_CACHE_FILE)) {
            return null;
        }

        try {
            $serialized = file_get_contents(self::ROUTE_CACHE_FILE);
            if ($serialized === false) {
                return null;
            }

            $routes = unserialize($serialized);
            return $routes instanceof RouteCollection ? $routes : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * 缓存路由集合
     *
     * @param RouteCollection $routes
     * @return bool
     */
    public function cacheRoutes(RouteCollection $routes): bool
    {
        $this->ensureCacheDirectory(dirname(self::ROUTE_CACHE_FILE));

        try {
            $serialized = serialize($routes);
            return file_put_contents(self::ROUTE_CACHE_FILE, $serialized) !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 获取缓存的插件清单
     *
     * @return array|null
     */
    public function getCachedManifests(): ?array
    {
        // 优先使用 PSR-16 缓存
        if ($this->cache !== null && $this->cacheEnabled) {
            try {
                $cached = $this->cache->get(self::CACHE_PREFIX . 'manifests');
                if (is_array($cached)) {
                    return $cached;
                }
            } catch (\Throwable $e) {
                // 回退到文件缓存
            }
        }

        // 文件缓存
        $cacheFile = self::PLUGIN_CACHE_DIR . '/manifests.php';
        if (file_exists($cacheFile)) {
            try {
                $data = file_get_contents($cacheFile);
                if ($data === false) {
                    return null;
                }
                return unserialize($data) ?: null;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * 缓存插件清单
     *
     * @param array $manifests
     * @return bool
     */
    public function cacheManifests(array $manifests): bool
    {
        $serialized = serialize($manifests);

        // PSR-16 缓存
        if ($this->cache !== null && $this->cacheEnabled) {
            try {
                $this->cache->set(self::CACHE_PREFIX . 'manifests', $manifests, $this->defaultTtl);
            } catch (\Throwable $e) {
                // 继续使用文件缓存
            }
        }

        // 文件缓存
        $cacheFile = self::PLUGIN_CACHE_DIR . '/manifests.php';
        $this->ensureCacheDirectory(dirname($cacheFile));

        return file_put_contents($cacheFile, $serialized) !== false;
    }

    /**
     * 获取缓存的控制器目录
     *
     * @return array|null
     */
    public function getCachedControllerDirs(): ?array
    {
        if ($this->cache !== null && $this->cacheEnabled) {
            try {
                $cached = $this->cache->get(self::CACHE_PREFIX . 'controller_dirs');
                if (is_array($cached)) {
                    return $cached;
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * 缓存控制器目录
     *
     * @param array $dirs
     * @return bool
     */
    public function cacheControllerDirs(array $dirs): bool
    {
        if ($this->cache !== null && $this->cacheEnabled) {
            try {
                return $this->cache->set(self::CACHE_PREFIX . 'controller_dirs', $dirs, $this->defaultTtl);
            } catch (\Throwable $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * 获取插件配置缓存
     *
     * @param string $pluginName
     * @return array|null
     */
    public function getCachedPluginConfig(string $pluginName): ?array
    {
        if ($this->cache !== null && $this->cacheEnabled) {
            try {
                $cached = $this->cache->get(self::CACHE_PREFIX . "config_{$pluginName}");
                if (is_array($cached)) {
                    return $cached;
                }
            } catch (\Throwable $e) {
                // 回退到文件缓存
            }
        }

        // 文件缓存
        $cacheFile = self::PLUGIN_CACHE_DIR . "/configs/{$pluginName}.php";
        if (file_exists($cacheFile)) {
            try {
                $data = file_get_contents($cacheFile);
                if ($data === false) {
                    return null;
                }
                return unserialize($data) ?: null;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * 缓存插件配置
     *
     * @param string $pluginName
     * @param array $config
     * @return bool
     */
    public function cachePluginConfig(string $pluginName, array $config): bool
    {
        $serialized = serialize($config);

        // PSR-16 缓存
        if ($this->cache !== null && $this->cacheEnabled) {
            try {
                $this->cache->set(self::CACHE_PREFIX . "config_{$pluginName}", $config, $this->defaultTtl);
            } catch (\Throwable $e) {
                // 继续使用文件缓存
            }
        }

        // 文件缓存
        $configCacheDir = self::PLUGIN_CACHE_DIR . '/configs';
        $this->ensureCacheDirectory($configCacheDir);

        $cacheFile = "{$configCacheDir}/{$pluginName}.php";
        return file_put_contents($cacheFile, $serialized) !== false;
    }

    /**
     * 确保缓存目录存在
     *
     * @param string|null $dir
     */
    private function ensureCacheDirectory(?string $dir = null): void
    {
        $dir = $dir ?? self::PLUGIN_CACHE_DIR;

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException("无法创建缓存目录: {$dir}");
            }
        }
    }

    /**
     * 获取缓存统计信息
     *
     * @return array
     */
    public function getStats(): array
    {
        $stats = [
            'route_cache_exists' => file_exists(self::ROUTE_CACHE_FILE),
            'route_cache_size' => 0,
            'manifest_cache_exists' => file_exists(self::PLUGIN_CACHE_DIR . '/manifests.php'),
            'config_cache_count' => 0,
            'config_cache_size' => 0,
        ];

        if ($stats['route_cache_exists']) {
            $stats['route_cache_size'] = filesize(self::ROUTE_CACHE_FILE) ?: 0;
        }

        $configCacheDir = self::PLUGIN_CACHE_DIR . '/configs';
        if (is_dir($configCacheDir)) {
            $files = glob($configCacheDir . '/*.php') ?: [];
            $stats['config_cache_count'] = count($files);
            foreach ($files as $file) {
                $stats['config_cache_size'] += filesize($file) ?: 0;
            }
        }

        return $stats;
    }
}
