<?php

declare(strict_types=1);

/**
 * 博客卸载钩子
 *
 * @package Plugins\Blog\Hooks
 */

namespace Plugins\Blog\Hooks;

/**
 * 卸载钩子
 *
 * 在插件卸载时执行。
 */
class UninstallHook
{
    /**
     * 处理卸载
     *
     * @return void
     */
    public function handle(): void
    {
        // 卸载时的清理操作
        // 例如：清理上传的文件、删除缓存等
        
        $this->clearCache();
        
        // 记录日志
        error_log('[Blog Plugin] Uninstall hook executed');
    }

    /**
     * 清理缓存
     *
     * @return void
     */
    private function clearCache(): void
    {
        // 清理插件相关的缓存
        $cacheDir = BASE_PATH . '/storage/cache/blog';
        if (is_dir($cacheDir)) {
            // 递归删除目录
            $this->recursiveDelete($cacheDir);
        }
    }

    /**
     * 递归删除目录
     *
     * @param string $dir
     * @return void
     */
    private function recursiveDelete(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    $path = $dir . '/' . $object;
                    if (is_dir($path)) {
                        $this->recursiveDelete($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
