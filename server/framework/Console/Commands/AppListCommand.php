<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * 多应用诊断命令
 *
 * 显示 config/apps.php 中所有已配置的应用，包括：
 * - 命名空间和目录映射
 * - PSR-4 自动加载状态
 * - 域名绑定配置
 * - 控制器文件发现情况
 *
 * 使用方法：
 *   php novaphp app:list
 *   php novaphp app:list --check-autoload   # 检查 PSR-4 映射详情
 *
 * @package Framework\Console\Commands
 */

namespace Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppListCommand extends Command
{
    protected static ?string $defaultName = 'app:list';

    protected function configure(): void
    {
        $this->setName('app:list')
             ->setDescription('列出所有已配置的应用及其自动加载状态')
             ->setHelp('显示 config/apps.php 中所有应用配置、PSR-4 注册状态、域名绑定和控制器发现情况。')
             ->addOption('check-autoload', null, InputOption::VALUE_NONE, '详细检查 PSR-4 命名空间映射');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('多应用配置诊断');

        // ================================================================
        // 1. 读取 apps.php 配置
        // ================================================================
        $configFile = BASE_PATH . '/config/apps.php';
        if (!file_exists($configFile)) {
            $io->error("配置文件不存在: $configFile");
            return Command::FAILURE;
        }

        $apps = require $configFile;
        if (!is_array($apps) || empty($apps)) {
            $io->warning('config/apps.php 中未配置任何应用');
            return Command::SUCCESS;
        }

        // ================================================================
        // 2. 查找 Composer ClassLoader
        // ================================================================
        $composerLoader = null;
        foreach (spl_autoload_functions() as $func) {
            if (is_array($func) && $func[0] instanceof \Composer\Autoload\ClassLoader) {
                $composerLoader = $func[0];
                break;
            }
        }

        // ================================================================
        // 3. 构建应用信息表
        // ================================================================
        $rows = [];
        foreach ($apps as $key => $app) {
            $namespace = $app['namespace'] ?? '-';
            $dir       = $app['dir'] ?? '-';
            $prefix    = $app['prefix'] ?? ($key === 'default' ? '(无)' : $key);
            $domain    = $app['domain'] ?? '';

            // 目录有效性
            $dirExists = ($dir !== '-' && is_dir($dir));
            $dirStatus = $dirExists ? '✅ 存在' : '❌ 不存在';
            if ($dir !== '-' && !$dirExists) {
                $dirStatus .= "\n(expected: $dir)";
            }

            // 控制器发现
            $controllerCount = 0;
            if ($dirExists) {
                $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
                foreach ($rii as $file) {
                    if ($file->isFile() && $file->getExtension() === 'php') {
                        $controllerCount++;
                    }
                }
            }

            // PSR-4 注册状态
            $nsStatus = $this->checkPsr4Registration($composerLoader, $namespace);

            // 域名信息
            $domainDisplay = $domain ?: '(未绑定)' . ($key !== 'default' ? "\n→ 通过 /$prefix/ 访问" : '');

            $rows[] = [
                $key,
                $namespace,
                $dirStatus,
                "{$controllerCount} 个文件",
                $nsStatus,
                $domainDisplay,
            ];
        }

        // ================================================================
        // 4. 渲染表格
        // ================================================================
        $table = new Table($output);
        $table->setHeaders(['应用 Key', '命名空间', '目录', '控制器', 'PSR-4 状态', '域名/访问']);
        $table->setRows($rows);
        $table->render();

        // ================================================================
        // 5. 详细 PSR-4 检查
        // ================================================================
        if ($input->getOption('check-autoload') && $composerLoader) {
            $io->section('PSR-4 命名空间映射详情 (Composer)');
            $prefixRows = [];
            $prefixes = $composerLoader->getPrefixesPsr4();
            ksort($prefixes);
            foreach ($prefixes as $prefix => $dirs) {
                foreach ($dirs as $dir) {
                    $prefixRows[] = [$prefix, $dir];
                }
            }
            if (!empty($prefixRows)) {
                $nsTable = new Table($output);
                $nsTable->setHeaders(['命名空间前缀', '映射目录']);
                $nsTable->setRows($prefixRows);
                $nsTable->render();
            } else {
                $io->text('未找到 PSR-4 映射');
            }
        } elseif ($input->getOption('check-autoload') && !$composerLoader) {
            $io->warning('未找到 Composer ClassLoader，无法检查 PSR-4 注册状态');
        }

        // ================================================================
        // 6. 确认动态注册是否有效
        // ================================================================
        if ($composerLoader) {
            $io->section('动态注册验证');
            $registeredByFramework = [];
            foreach ($apps as $key => $app) {
                if ($key === 'default') continue;
                $ns = rtrim($app['namespace'] ?? '', '\\') . '\\';
                $dir = $app['dir'] ?? '';
                if ($ns !== '\\' && $dir !== '' && is_dir($dir)) {
                    // 检查是否已被注册（可能是 Framework 构造函数中已注册）
                    $prefixes = $composerLoader->getPrefixesPsr4();
                    $found = false;
                    foreach ($prefixes as $p => $dirs) {
                        if ($p === $ns) {
                            $found = true;
                            break;
                        }
                    }
                    $registeredByFramework[] = [
                        $key,
                        $ns,
                        $dir,
                        $found ? '✅ 已注册' : '⚠️ 未注册（请检查 Framework::registerAppAutoloadNamespaces）',
                    ];
                }
            }
            if (!empty($registeredByFramework)) {
                $regTable = new Table($output);
                $regTable->setHeaders(['应用', '命名空间', '目录', '注册状态']);
                $regTable->setRows($registeredByFramework);
                $regTable->render();
            }
        }

        return Command::SUCCESS;
    }

    /**
     * 检查命名空间是否已注册到 PSR-4 自动加载器
     */
    private function checkPsr4Registration(?\Composer\Autoload\ClassLoader $loader, string $namespace): string
    {
        if ($loader === null) {
            return '⚠️ 未检测到 ClassLoader';
        }

        $namespace = rtrim($namespace, '\\') . '\\';
        if ($namespace === '\\') {
            return '⚠️ 命名空间无效';
        }

        $prefixes = $loader->getPrefixesPsr4();

        // 精确匹配
        if (isset($prefixes[$namespace])) {
            return '✅ 已注册';
        }

        // 前缀匹配（例如 App\ 匹配 App\Admin\Controllers）
        foreach ($prefixes as $prefix => $dirs) {
            if (str_starts_with($namespace, $prefix)) {
                return "✅ 由前缀 \"$prefix\" 覆盖";
            }
        }

        return '❌ 未注册';
    }
}
