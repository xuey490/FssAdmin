<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginMarketCommand.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Console\Commands;

use Framework\Plugin\PluginMarketService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 插件市场命令
 *
 * 从远程市场搜索和安装插件。
 *
 * 使用方法：
 *   php novaphp plugin:market search blog
 *   php novaphp plugin:market detail blog
 *   php novaphp plugin:market install blog
 *   php novaphp plugin:market install blog --plugin-version=1.2.0
 *
 * @package Framework\Console\Commands
 */
class PluginMarketCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'plugin:market';

    /**
     * 市场服务
     *
     * @var PluginMarketService|null
     */
    private ?PluginMarketService $marketService = null;

    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName)
             ->setDescription('插件市场操作')
             ->setHelp('从远程市场搜索、查看和安装插件。')
             ->addArgument('action', InputArgument::REQUIRED, '操作: search, detail, install, markets')
             ->addArgument('name', InputArgument::OPTIONAL, '插件名称')
             ->addOption('plugin-version', null, InputOption::VALUE_OPTIONAL, '指定插件版本')
             ->addOption('page', 'p', InputOption::VALUE_OPTIONAL, '页码', 1)
             ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, '每页数量', 20)
             ->addOption('market', 'm', InputOption::VALUE_OPTIONAL, '指定市场地址');
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
        $action = $input->getArgument('action');
        $name = $input->getArgument('name');

        $this->marketService = new PluginMarketService();

        return match ($action) {
            'search' => $this->search($input, $output, $io, $name ?? ''),
            'detail' => $this->detail($input, $output, $io, $name),
            'install' => $this->install($input, $output, $io, $name),
            'markets' => $this->listMarkets($output, $io),
            default => $this->showHelp($io, $action),
        };
    }

    /**
     * 搜索插件
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @param string $keyword
     * @return int
     */
    private function search(InputInterface $input, OutputInterface $output, SymfonyStyle $io, string $keyword): int
    {
        $page = (int) $input->getOption('page');
        $limit = (int) $input->getOption('limit');
        $market = $input->getOption('market');

        $io->title("搜索插件: {$keyword}");

        try {
            $result = $this->marketService->search($keyword, $page, $limit, $market);

            if (!isset($result['data']) || empty($result['data']['list'])) {
                $io->note('未找到匹配的插件');
                return Command::SUCCESS;
            }

            $table = new Table($output);
            $table->setHeaders(['名称', '标题', '最新版本', '作者', '下载量']);

            foreach ($result['data']['list'] as $plugin) {
                $table->addRow([
                    $plugin['name'],
                    $plugin['title'],
                    $plugin['latest_version'] ?? '-',
                    $plugin['author'] ?? '-',
                    $plugin['downloads'] ?? 0,
                ]);
            }

            $table->render();

            $io->newLine();
            $io->text(sprintf(
                '共 %d 个插件，当前第 %d 页',
                $result['data']['total'] ?? 0,
                $page
            ));

        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 获取插件详情
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @param string|null $name
     * @return int
     */
    private function detail(InputInterface $input, OutputInterface $output, SymfonyStyle $io, ?string $name): int
    {
        if ($name === null) {
            $io->error('请指定插件名称');
            return Command::FAILURE;
        }

        $market = $input->getOption('market');

        $io->title("插件详情: {$name}");

        try {
            $result = $this->marketService->detail($name, $market);

            if (!isset($result['data'])) {
                $io->error('插件不存在');
                return Command::FAILURE;
            }

            $plugin = $result['data'];

            $io->definitionList(
                ['名称' => $plugin['name']],
                ['标题' => $plugin['title']],
                ['最新版本' => $plugin['latest_version'] ?? '-'],
                ['作者' => $plugin['author'] ?? '-'],
                ['描述' => $plugin['description'] ?? '-'],
                ['下载量' => $plugin['downloads'] ?? 0],
                ['评分' => $plugin['rating'] ?? '-']
            );

            // 显示版本列表
            if (isset($plugin['versions']) && !empty($plugin['versions'])) {
                $io->newLine();
                $io->section('可用版本');

                foreach ($plugin['versions'] as $version) {
                    $io->text("- {$version['version']} ({$version['released_at']})");
                }
            }

        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 从市场安装插件
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @param string|null $name
     * @return int
     */
    private function install(InputInterface $input, OutputInterface $output, SymfonyStyle $io, ?string $name): int
    {
        if ($name === null) {
            $io->error('请指定插件名称');
            return Command::FAILURE;
        }

        $version = $input->getOption('plugin-version') ?? 'latest';
        $market = $input->getOption('market');

        $io->title("从市场安装插件: {$name}");

        try {
            // 获取插件详情
            $detail = $this->marketService->detail($name, $market);

            if (!isset($detail['data'])) {
                $io->error('插件不存在');
                return Command::FAILURE;
            }

            $plugin = $detail['data'];
            $io->text("插件: {$plugin['title']} ({$plugin['name']})");

            if ($version !== 'latest') {
                $io->text("版本: {$version}");
            } else {
                $version = $plugin['latest_version'] ?? '1.0.0';
                $io->text("版本: {$version} (最新)");
            }

            if (!$io->confirm('确定要安装此插件吗？', true)) {
                $io->text('取消安装');
                return Command::SUCCESS;
            }

            $io->newLine();
            $io->section('正在下载和安装...');

            $result = $this->marketService->install($name, $version, $market);

            if ($result['success']) {
                $io->success($result['message']);
            } else {
                $io->error($result['message']);
                return Command::FAILURE;
            }

        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 列出所有市场
     *
     * @param OutputInterface $output
     * @param SymfonyStyle $io
     * @return int
     */
    private function listMarkets(OutputInterface $output, SymfonyStyle $io): int
    {
        $io->title('可用市场列表');

        $markets = $this->marketService->getMarkets();

        $table = new Table($output);
        $table->setHeaders(['名称', '地址', '类型']);

        foreach ($markets as $market) {
            $table->addRow([
                $market['name'],
                $market['url'],
                $market['official'] ? '官方' : '第三方',
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }

    /**
     * 显示帮助
     *
     * @param SymfonyStyle $io
     * @param string $action
     * @return int
     */
    private function showHelp(SymfonyStyle $io, string $action): int
    {
        $io->error("未知操作: {$action}");
        $io->text([
            '可用操作:',
            '  search <keyword>  搜索插件',
            '  detail <name>     查看插件详情',
            '  install <name>    安装插件',
            '  markets           列出所有市场',
        ]);

        return Command::FAILURE;
    }
}