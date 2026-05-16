<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginUninstallCommand.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Console\Commands;

use Framework\Plugin\Migration\MigrationRunner;
use Framework\Plugin\PluginManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 插件卸载命令
 *
 * 卸载指定的插件。
 *
 * 使用方法：
 *   php novaphp plugin:uninstall blog
 *   php novaphp plugin:uninstall blog --force
 *
 * @package Framework\Console\Commands
 */
class PluginUninstallCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'plugin:uninstall';

    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName)
             ->setDescription('卸载插件')
             ->addArgument('name', InputArgument::REQUIRED, '插件名称')
             ->addOption('force', 'f', InputOption::VALUE_NONE, '强制卸载（忽略依赖检查）')
             ->setHelp('此命令卸载指定的插件，包括回滚数据库迁移和清理插件配置。');
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
        $name = $input->getArgument('name');
        $force = $input->getOption('force');

        // 加载插件配置
        $configFile = BASE_PATH . '/config/plugin/plugins.php';
        if (!file_exists($configFile)) {
            $io->error('插件配置文件不存在: ' . $configFile);
            return Command::FAILURE;
        }

        $config = require $configFile;
        $manager = new PluginManager($config);
        $manager->discover();

        // 设置迁移执行器
        $migrationRunner = new MigrationRunner();
        $manager->setMigrationRunner($migrationRunner);

        // 检查插件是否已安装
        if (!$manager->isInstalled($name)) {
            $io->warning("插件 '{$name}' 未安装");
            return Command::SUCCESS;
        }

        $manifest = $manager->getManifest($name);

        $io->title("卸载插件: " . ($manifest ? $manifest->title : $name));

        // 检查是否有其他插件依赖
        $dependents = $manager->getDependents($name);
        if (!empty($dependents) && !$force) {
            $io->error("无法卸载：以下插件依赖此插件: " . implode(', ', $dependents));
            $io->text('请先卸载依赖插件，或使用 --force 强制卸载。');
            return Command::FAILURE;
        }

        if (!empty($dependents) && $force) {
            $io->warning('强制卸载模式：忽略依赖检查');
            $io->text('以下插件可能无法正常工作: ' . implode(', ', $dependents));
        }

        // 确认卸载
        if (!$force && !$io->confirm('确定要卸载此插件吗？这将删除所有相关数据。', false)) {
            $io->text('取消卸载');
            return Command::SUCCESS;
        }

        // 执行卸载
        $io->newLine();
        $io->section('执行卸载');

        try {
            $result = $manager->uninstall($name, $force);

            if ($result['success']) {
                $io->success($result['message']);
            } else {
                $io->error($result['message']);
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $io->error("卸载失败: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
