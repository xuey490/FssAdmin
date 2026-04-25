<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginInterface.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Plugin;

/**
 * 插件接口
 *
 * 所有插件必须实现此接口，定义插件的生命周期方法。
 *
 * @package Framework\Plugin
 */
interface PluginInterface
{
    /**
     * 获取插件名称
     *
     * @return string 插件唯一标识符
     */
    public function getName(): string;

    /**
     * 获取插件版本
     *
     * @return string 语义化版本号（如 1.0.0）
     */
    public function getVersion(): string;

    /**
     * 获取插件标题
     *
     * @return string 插件显示名称
     */
    public function getTitle(): string;

    /**
     * 获取插件描述
     *
     * @return string 插件功能描述
     */
    public function getDescription(): string;

    /**
     * 插件安装时调用
     *
     * 执行插件的初始化操作，如：
     * - 创建数据库表
     * - 初始化配置
     * - 注册权限
     * - 创建默认数据
     *
     * @return bool 安装是否成功
     */
    public function install(): bool;

    /**
     * 插件卸载时调用
     *
     * 执行插件的清理操作，如：
     * - 删除数据库表
     * - 清理配置
     * - 移除权限
     * - 清理上传文件
     *
     * @return bool 卸载是否成功
     */
    public function uninstall(): bool;

    /**
     * 插件启用时调用
     *
     * 执行启用时的初始化操作，如：
     * - 清除缓存
     * - 注册定时任务
     * - 发送启用通知
     *
     * @return bool 启用是否成功
     */
    public function enable(): bool;

    /**
     * 插件禁用时调用
     *
     * 执行禁用时的清理操作，如：
     * - 清除缓存
     * - 移除定时任务
     * - 发送禁用通知
     *
     * @return bool 禁用是否成功
     */
    public function disable(): bool;

    /**
     * 插件升级时调用
     *
     * @param string $oldVersion 旧版本号
     * @param string $newVersion 新版本号
     * @return bool 升级是否成功
     */
    public function upgrade(string $oldVersion, string $newVersion): bool;
}
