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

namespace Framework\Cache;

/**
 * 缓存抽象基类
 * 
 * 为缓存组件提供基础实现，实现了 CacheInterface 接口。
 * 提供缓存键前缀支持和默认过期时间配置，适用于 ThinkCache 适配。
 *
 * @package Framework\Cache
 * @author  xuey863toy
 */
abstract class AbstractCache implements CacheInterface
{
    /**
     * 默认缓存过期时间（秒）
     * 
     * @var int
     */
    protected int $defaultTtl = 3600;	// 默认 1 小时

    /**
     * 缓存键前缀
     * 
     * 用于区分不同应用或模块的缓存数据，避免键冲突。
     *
     * @var string
     */
    protected string $prefix = '';

    /**
     * 设置缓存键前缀
     * 
     * 用于为所有缓存键添加统一前缀，便于区分不同应用或环境的缓存。
     *
     * @param string $prefix 缓存键前缀字符串
     * 
     * @return void
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * 格式化缓存键
     * 
     * 将原始键名与配置的前缀组合，生成最终的缓存键。
     *
     * @param string $key 原始缓存键名
     * 
     * @return string 添加前缀后的完整缓存键
     */
    protected function formatKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
