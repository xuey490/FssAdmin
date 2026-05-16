<?php

declare(strict_types=1);

/**
 * 博客安装钩子
 *
 * @package Plugins\Blog\Hooks
 */

namespace Plugins\Blog\Hooks;

/**
 * 安装钩子
 *
 * 在插件安装时执行。
 */
class InstallHook
{
    /**
     * 处理安装
     *
     * @return void
     */
    public function handle(): void
    {
        // 安装时的额外操作
        // 例如：创建默认分类、初始化配置等
        
        $this->createDefaultCategory();
        
        // 记录日志
        error_log('[Blog Plugin] Install hook executed');
    }

    /**
     * 创建默认分类
     *
     * @return void
     */
    private function createDefaultCategory(): void
    {
        // 这里可以创建默认分类
        // 实际实现需要根据 ORM 类型调整
    }
}
