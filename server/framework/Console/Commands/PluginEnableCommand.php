<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginEnableCommand.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Console\Commands;

use Framework\Plugin\PluginManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 插件启用命令
 *
 * 启用指定的已安装插件。
 *
 * 使用方法：
 *   php novaphp plugin:enable blog
 *
 * @package Framework\Console\Commands
 */
class PluginEnableCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'plugin:enable';


    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName)
             ->setDescription('启用插件')
             ->addArgument('name', InputArgument::REQUIRED, '插件名称')
             ->setHelp('此命令启用指定的已安装插件。');
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

        // 加载插件配置
        $configFile = BASE_PATH . '/config/plugin/plugins.php';
        if (!file_exists($configFile)) {
            $io->error('插件配置文件不存在: ' . $configFile);
            return Command::FAILURE;
        }

        $config = require $configFile;
        $manager = new PluginManager($config);
        $manager->discover();

        // 检查插件是否已安装
        if (!$manager->isInstalled($name)) {
            $io->error("插件 '{$name}' 未安装，请先安装");
            return Command::FAILURE;
        }

        // 检查是否已启用
        if ($manager->isEnabled($name)) {
            $io->warning("插件 '{$name}' 已处于启用状态");
            return Command::SUCCESS;
        }

        // 执行启用
        try {
            $result = $manager->enable($name);

            if ($result['success']) {
                $io->success($result['message']);
            } else {
                $io->error($result['message']);
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $io->error("启用失败: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
