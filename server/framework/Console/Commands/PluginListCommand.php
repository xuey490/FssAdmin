<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginListCommand.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Console\Commands;

use Framework\Plugin\PluginManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 插件列表命令
 *
 * 列出所有已发现和已安装的插件。
 *
 * 使用方法：
 *   php novaphp plugin:list
 *
 * @package Framework\Console\Commands
 */
class PluginListCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'plugin:list';

    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName)
             ->setDescription('列出所有插件')
             ->setHelp('此命令列出所有已发现和已安装的插件，包括其状态、版本等信息。');
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

        // 加载插件配置
        $configFile = BASE_PATH . '/config/plugin/plugins.php';
        if (!file_exists($configFile)) {
            $io->error('插件配置文件不存在: ' . $configFile);
            return Command::FAILURE;
        }

        $config = require $configFile;
        $manager = new PluginManager($config);
        $manager->discover();

        $manifests = $manager->getManifests();
        $installed = $config['installed'] ?? [];

        if (empty($manifests)) {
            $io->note('未发现任何插件');
            return Command::SUCCESS;
        }

        // 构建表格数据
        $rows = [];
        foreach ($manifests as $name => $manifest) {
            $isInstalled = isset($installed[$name]);
            $isEnabled = $installed[$name]['enabled'] ?? false;
            $version = $installed[$name]['version'] ?? $manifest->version;

            $status = '未安装';
            if ($isInstalled) {
                $status = $isEnabled ? '<info>已启用</info>' : '<comment>已禁用</comment>';
            }

            $rows[] = [
                $name,
                $manifest->title,
                $version,
                $status,
                $manifest->author,
            ];
        }

        $io->title('插件列表');

        $table = new Table($output);
        $table->setHeaders(['名称', '标题', '版本', '状态', '作者'])
              ->setRows($rows);
        $table->render();

        $io->newLine();
        $io->text(sprintf('共发现 <info>%d</info> 个插件', count($manifests)));

        return Command::SUCCESS;
    }
}
