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

namespace Framework\Event;

use Framework\Cache\CacheFactory;
use Framework\Event\Attribute\EventListener;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

class ListenerScanner
{
    private string $listenerDir;

    private CacheFactory $cache;

    private int $cacheTtl = 3600; // 1小时

    private string $cacheKey = 'event.subscribers';

    public function __construct(CacheFactory $cache, ?string $listenerDir = null)
    {
        $this->cache       = $cache;
        $this->listenerDir = $listenerDir ?? BASE_PATH . '/app/Listeners';
    }

    /**
     * 获取监听器（自动缓存 + 自动刷新）.
     */
    public function getSubscribers(): array
    {
        // 开发环境建议禁用缓存（可选）
        // if (APP_ENV === 'dev') {
        //     return $this->scanAndBuild();
        // }

        if (! function_exists('cache_get') || ! function_exists('cache_set')) {
            return $this->scanAndBuild();
        }

        // 1. 尝试从缓存读取
        $cached             = cache_get($this->cacheKey);
        $currentFingerprint = $this->generateFingerprint();

        // 2. 缓存命中且指纹一致 → 直接返回
        if ($cached && is_array($cached) && ($cached['fingerprint'] ?? null) === $currentFingerprint) {
            //app('log')->info('[Event Scan] Subscribers loaded from cache (fingerprint match).');
            return $cached['subscribers'] ?? [];
        }

        // 3. 缓存未命中 或 指纹不一致 → 重新扫描
        //app('log')->info('[Event Expired] Listener files changed or cache expired. Rescanning...');
        $result = $this->scanAndBuild();

        // 4. 更新缓存
        cache_set($this->cacheKey, [
            'fingerprint' => $currentFingerprint,
            'subscribers' => $result,
        ], $this->cacheTtl);

        return $result;
    }

    /**
     * 核心扫描逻辑：支持 Interface 和 Attributes
     */
    private function scanAndBuild(): array
    {
        if (! is_dir($this->listenerDir)) {
            return [];
        }

        $subscribers = [];
        
        // 1. 改为递归扫描，支持子目录
        $dirIterator = new RecursiveDirectoryIterator($this->listenerDir);
        $iterator = new RecursiveIteratorIterator($dirIterator);

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // 假设命名空间符合 PSR-4 规范，这里简单推导（实际项目中建议用 composer classmap 或 symfony finder）
            // 这里为了演示，假设文件名即类名，且都在 App\Listeners 下
            // 更好的方式是解析文件内容获取 namespace
            $className = $this->getClassFromFile($file->getPathname()); 
            
            if (!$className || !class_exists($className)) {
                continue;
            }

            $ref = new ReflectionClass($className);
            if (!$ref->isInstantiable()) {
                continue;
            }

            // 方式 A: 传统的 ListenerInterface
            if ($ref->implementsInterface(ListenerInterface::class)) {
                $subscribers['interface'][] = $className;
                continue; // 如果实现了接口，通常不需要再扫注解，避免重复
            }

            // 方式 B: 扫描 #[EventListener] 注解
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $attributes = $method->getAttributes(EventListener::class);
                foreach ($attributes as $attribute) {
                    $instance = $attribute->newInstance();
                    
                    // 尝试推断事件类型
                    $eventClass = $instance->event;
                    if ($eventClass === null) {
                        // 从方法参数获取: handle(UserLogin $event)
                        $params = $method->getParameters();
                        if (isset($params[0])) {
                            $type = $params[0]->getType();
                            if ($type && !$type->isBuiltin()) {
                                $eventClass = $type->getName();
                            }
                        }
                    }

                    if ($eventClass) {
                        // 结构化存储注解配置
                        $subscribers['attribute'][] = [
                            'class'    => $className,
                            'method'   => $method->getName(),
                            'event'    => $eventClass,
                            'priority' => $instance->priority,
                        ];
                    }
                }
            }
        }

        return $subscribers;
    }

    /**
     * 辅助方法：简单的从文件路径推断类名 (根据你的项目结构调整)
     */
    private function getClassFromFile(string $path): ?string
    {
        // 简单实现：读取文件内容找 namespace 和 class
        $content = file_get_contents($path);
        if (preg_match('/namespace\s+(.+?);/', $content, $matchesNs) && 
            preg_match('/class\s+(\w+)/', $content, $matchesClass)) {
            return $matchesNs[1] . '\\' . $matchesClass[1];
        }
        return null;
    }
	 


    /**
     * 生成监听器目录的指纹（基于文件修改时间）.
     */
    private function generateFingerprint(): string
    {
        $listenerDir = $this->listenerDir;
        if (! is_dir($listenerDir)) {
            return md5('no_dir');
        }

        $files = glob($listenerDir . '/*.php');
        if (! $files) {
            return md5('no_files');
        }

        // 按文件名排序，确保顺序一致
        sort($files);

        $hashParts = [];
        foreach ($files as $file) {
            $hashParts[] = filemtime($file) . ':' . filesize($file); // 更健壮：mtime + size
        }

        return md5(implode('|', $hashParts));
    }
}
