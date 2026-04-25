<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginCacheCommand.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Console\Commands;

use Framework\Plugin\PluginCacheManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 插件缓存命令
 *
 * 管理插件相关缓存。
 *
 * 使用方法：
 *   php novaphp plugin:cache:clear
 *   php novaphp plugin:cache:clear --routes
 *   php novaphp plugin:cache:clear --config=blog
 *   php novaphp plugin:cache:stats
 *
 * @package Framework\Console\Commands
 */
class PluginCacheCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'plugin:cache:clear';

    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName)
             ->setDescription('清除插件缓存')
             ->setHelp('此命令清除插件相关的缓存，包括路由缓存、清单缓存、配置缓存等。')
             ->addOption('routes', 'r', InputOption::VALUE_NONE, '仅清除路由缓存')
             ->addOption('manifests', 'm', InputOption::VALUE_NONE, '仅清除清单缓存')
             ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, '清除配置缓存，可指定插件名称')
             ->addOption('stats', 's', InputOption::VALUE_NONE, '显示缓存统计信息');
    }

    /**
     * 执行命令
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $manager = new PluginCacheManager();

        // 显示统计信息
        if ($input->getOption('stats')) {
            return $this->showStats($io, $manager);
        }

        $routesOnly = $input->getOption('routes');
        $manifestsOnly = $input->getOption('manifests');
        $configPlugin = $input->getOption('config');

        // 仅清除路由缓存
        if ($routesOnly) {
            if ($manager->clearRouteCache()) {
                $io->success('路由缓存已清除');
            } else {
                $io->error('路由缓存清除失败');
                return Command::FAILURE;
            }
            return Command::SUCCESS;
        }

        // 仅清除清单缓存
        if ($manifestsOnly) {
            if ($manager->clearManifestCache()) {
                $io->success('插件清单缓存已清除');
            } else {
                $io->error('插件清单缓存清除失败');
                return Command::FAILURE;
            }
            return Command::SUCCESS;
        }

        // 清除配置缓存
        if ($configPlugin !== null) {
            if ($manager->clearConfigCache($configPlugin === '' ? null : $configPlugin)) {
                $pluginText = $configPlugin === '' ? '所有' : "'{$configPlugin}'";
                $io->success("{$pluginText}插件的配置缓存已清除");
            } else {
                $io->error('配置缓存清除失败');
                return Command::FAILURE;
            }
            return Command::SUCCESS;
        }

        // 清除所有缓存
        if ($manager->clearAll()) {
            $io->success('所有插件缓存已清除');
        } else {
            $io->warning('部分缓存清除失败，请检查权限');
        }

        return Command::SUCCESS;
    }

    /**
     * 显示缓存统计信息
     *
     * @param SymfonyStyle $io
     * @param PluginCacheManager $manager
     * @return int
     */
    private function showStats(SymfonyStyle $io, PluginCacheManager $manager): int
    {
        $stats = $manager->getStats();

        $io->title('插件缓存统计');

        $io->definitionList(
            ['路由缓存' => $stats['route_cache_exists'] ? '存在' : '不存在'],
            ['路由缓存大小' => $this->formatBytes($stats['route_cache_size'])],
            ['清单缓存' => $stats['manifest_cache_exists'] ? '存在' : '不存在'],
            ['配置缓存数量' => $stats['config_cache_count'] . ' 个'],
            ['配置缓存总大小' => $this->formatBytes($stats['config_cache_size'])]
        );

        return Command::SUCCESS;
    }

    /**
     * 格式化字节数
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
