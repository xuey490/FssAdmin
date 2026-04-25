<?php

namespace Framework\Composer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * Composer 脚本处理器
 *
 * 用于处理 Composer 安装后自动复制框架配置文件和模板文件到项目目录。
 * 该类在 composer.json 的 scripts 部分被引用，实现框架初始化时的文件自动部署。
 *
 * @package Framework\Composer
 */
class ScriptHandler
{
    /**
     * 复制默认配置文件到项目目录
     *
     * 此方法在 Composer 安装/更新后自动执行，完成以下任务：
     * 1. 复制 config 目录到项目根目录
     * 2. 复制 view/errors 错误模板到 resource/view/errors
     * 3. 复制 .env 环境配置文件到项目根目录
     *
     * 所有复制操作都会检查目标是否存在，避免覆盖已有配置。
     *
     * @return void
     */
    public static function copyDefaultConfig(): void
    {
        $filesystem = new Filesystem();

        // 获取当前包（框架）的根目录
        $packageRoot = dirname(__DIR__, 2); // 指向 vendor/blue2004/novaphp 目录
        $projectRoot = getcwd(); // 用户项目根目录（与 composer.json 同级）

        try {
            // 1. 复制 config 目录
            $sourceConfig = $packageRoot . '/config';
            $targetConfig = $projectRoot . '/config';

            if (!$filesystem->exists($targetConfig) && $filesystem->exists($sourceConfig)) {
                $filesystem->mirror($sourceConfig, $targetConfig);
                echo "✅ Copied config files to: $targetConfig\n";
            } else {
                echo "ℹ️ Config directory already exists or source missing. Skipping.\n";
            }

            // 2. 复制 view/errors 到 resource 目录
            $sourceView = $packageRoot . '/view/errors';
            $targetView = $projectRoot . '/resource/view/errors';

            // 确保目标目录存在（即使源目录为空也创建目录结构）
            if (!$filesystem->exists($targetView)) {
                $filesystem->mkdir($targetView, 0755);
            }

            if ($filesystem->exists($sourceView)) {
                $filesystem->mirror($sourceView, $targetView);
                echo "✅ Copied view files to: $targetView\n";
            } else {
                echo "ℹ️ View source directory missing. Skipping.\n";
            }

            // 3. 新增：复制 .env 文件到项目根目录
            $sourceEnv = $packageRoot . '/.env';
            $targetEnv = $projectRoot . '/.env';

            if ($filesystem->exists($sourceEnv) && !$filesystem->exists($targetEnv)) {
                $filesystem->copy($sourceEnv, $targetEnv);
                echo "✅ Copied .env file to: $targetEnv\n";
            } else {
                if ($filesystem->exists($targetEnv)) {
                    echo "ℹ️ .env file already exists. Skipping.\n";
                } else {
                    echo "ℹ️ .env source file missing. Skipping.\n";
                }
            }

        } catch (IOExceptionInterface $e) {
            echo "❌ Error copying files: " . $e->getMessage() . "\n";
        }
    }
}
