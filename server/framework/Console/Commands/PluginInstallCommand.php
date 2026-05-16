<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginInstallCommand.php
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
 * 插件安装命令
 *
 * 安装指定的插件。
 *
 * 使用方法：
 *   php novaphp plugin:install blog
 *   php novaphp plugin:install blog --force
 *
 * @package Framework\Console\Commands
 */
class PluginInstallCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'plugin:install';

    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName)
             ->setDescription('安装插件')
             ->addArgument('name', InputArgument::REQUIRED, '插件名称')
             ->addOption('force', 'f', InputOption::VALUE_NONE, '强制重新安装')
             ->setHelp('此命令安装指定的插件，包括执行数据库迁移和注册插件服务。');
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

        // 检查插件是否存在
        $manifest = $manager->getManifest($name);
        if ($manifest === null) {
            $io->error("插件 '{$name}' 不存在");
            return Command::FAILURE;
        }

        $io->title("安装插件: {$manifest->title}");

        // 检查是否已安装
        if ($manager->isInstalled($name) && !$force) {
            $io->warning("插件 '{$name}' 已安装。使用 --force 强制重新安装。");
            return Command::SUCCESS;
        }

        // 如果强制安装，先卸载
        if ($force && $manager->isInstalled($name)) {
            $io->text('强制重新安装，先卸载现有插件...');
            $manager->uninstall($name, true);
        }

        // 显示插件信息
        $io->definitionList(
            ['名称' => $manifest->name],
            ['版本' => $manifest->version],
            ['作者' => $manifest->author],
            ['描述' => $manifest->description]
        );

        // 检查运行环境要求
        $requirements = $manifest->checkRequirements();
        if (!$requirements['satisfied']) {
            $io->error('运行环境要求不满足:');
            foreach ($requirements['errors'] as $error) {
                $io->text("  - {$error}");
            }
            return Command::FAILURE;
        }

        // 检查依赖
        $dependencies = $manager->checkDependencies($name);
        if (!$dependencies['satisfied']) {
            $io->error('依赖检查失败:');
            foreach ($dependencies['errors'] as $error) {
                $io->text("  - {$error}");
            }
            $io->newLine();
            $io->text('请先安装依赖插件后再试。');
            return Command::FAILURE;
        }

        // 执行安装
        $io->newLine();
        $io->section('执行安装');

        try {
            $result = $manager->install($name);

            if ($result['success']) {
                $io->success($result['message']);

                if (!empty($result['migrations'])) {
                    $io->text('已执行的数据库迁移:');
                    foreach ($result['migrations'] as $migration) {
                        $io->text("  - {$migration}");
                    }
                }

                $io->newLine();
                $io->success("插件 '{$name}' 安装完成！");
            } else {
                $io->error($result['message']);
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $io->error("安装失败: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
