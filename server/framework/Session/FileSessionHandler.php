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

namespace Framework\Session;

# use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface,

/**
 * 文件系统 Session 处理器.
 *
 * 该类实现了 PHP 的 SessionHandlerInterface 接口，提供基于文件系统的 Session 存储功能。
 * 支持自定义存储路径和文件前缀，适用于单机部署或共享文件存储场景。
 *
 * 主要功能：
 * - Session 数据的文件存储
 * - 支持自定义存储路径和文件前缀
 * - 自动创建存储目录
 * - Session 垃圾回收机制
 *
 * @package Framework\Session
 * @implements \SessionHandlerInterface
 */
class FileSessionHandler implements \SessionHandlerInterface
{
    /**
     * Session 文件存储路径.
     *
     * @var string
     */
    private string $savePath;

    /**
     * Session 文件名前缀.
     *
     * @var string
     */
    private string $setPrefix;

    /**
     * 构造函数，初始化文件 Session 处理器.
     *
     * 存储路径初始为空，后续通过 setSavePath 方法设置。
     */
    public function __construct()
    {
        // 初始路径可以为空，后续通过 setSavePath 设置
        $this->savePath = '';
    }

    /**
     * 设置 Session 文件存储路径.
     *
     * @param string $savePath 存储目录的绝对路径
     *
     * @return void
     */
    public function setSavePath(string $savePath): void
    {
        $this->savePath = $savePath;
    }

    /**
     * 设置 Session 文件名前缀.
     *
     * 前缀用于区分不同应用或环境的 Session 文件。
     *
     * @param string $setPrefix 文件名前缀
     *
     * @return void
     */
    public function setPrefix(string $setPrefix): void
    {
        $this->setPrefix = $setPrefix;
    }

    /**
     * 打开 Session 存储连接.
     *
     * 初始化存储路径，如果目录不存在则自动创建（权限 0755）。
     * 优先使用 setSavePath 设置的路径，否则使用 PHP 默认的 session_save_path。
     *
     * @param string $savePath    存储路径（由 PHP Session 机制传入）
     * @param string $sessionName Session 名称（由 PHP Session 机制传入）
     *
     * @return bool 始终返回 true
     */
    public function open(string $savePath, string $sessionName): bool
    {
        // 如果通过 setSavePath 设置了路径，就用它；否则用传入的 $savePath
        $path = $this->savePath ?: $savePath;
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return true;
    }

    /**
     * 关闭 Session 存储连接.
     *
     * 对于文件存储，无需执行额外的关闭操作。
     *
     * @return bool 始终返回 true
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * 读取指定 Session ID 的数据.
     *
     * 从文件中读取 Session 数据，如果文件不存在则返回空字符串。
     *
     * @param string $id Session ID
     *
     * @return string Session 数据内容，不存在时返回空字符串
     */
    public function read(string $id): string
    {
        $path = $this->getSessionFile($id);
        return is_file($path) ? file_get_contents($path) : '';
    }

    /**
     * 写入 Session 数据到文件.
     *
     * 将 Session 数据序列化后写入指定 ID 对应的文件。
     *
     * @param string $id   Session ID
     * @param string $data 序列化的 Session 数据
     *
     * @return bool 写入成功返回 true，失败返回 false
     */
    public function write(string $id, string $data): bool
    {
        $path = $this->getSessionFile($id);
        return file_put_contents($path, $data) !== false;
    }

    /**
     * 销毁指定 Session.
     *
     * 删除指定 Session ID 对应的文件。
     *
     * @param string $id Session ID
     *
     * @return bool 始终返回 true
     */
    public function destroy(string $id): bool
    {
        $path = $this->getSessionFile($id);
        if (is_file($path)) {
            unlink($path);
        }
        return true;
    }

    /**
     * 垃圾回收，清理过期的 Session 文件.
     *
     * 遍历存储目录，删除修改时间超过最大生命周期的文件。
     *
     * @param int $maxlifetime Session 最大生命周期（秒）
     *
     * @return int 被清理的文件数量
     */
    public function gc(int $maxlifetime): int
    {
        $count = 0;
        $path  = $this->savePath ?: session_save_path();
        if (! is_dir($path)) {
            return 0;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isFile() && $file->getMTime() + $maxlifetime < time()) {
                unlink($file->getPathname());
                ++$count;
            }
        }
        return $count;
    }

    /**
     * 根据 Session ID 生成完整的文件路径.
     *
     * 路径格式：{savePath}/{prefix}_{sessionId}
     *
     * @param string $id Session ID
     *
     * @return string Session 文件的完整路径
     */
    private function getSessionFile(string $id): string
    {
        $path = $this->savePath ?: session_save_path();
        return rtrim($path, '/\\') . '/' . $this->setPrefix . '_' . $id;
    }
}
