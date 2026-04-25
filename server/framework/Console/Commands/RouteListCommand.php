<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: RouteListCommand.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Console\Commands;

use Framework\Core\AttributeRouteLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * 路由列表命令
 *
 * 显示系统中所有注册的路由，包括：
 * - 手动路由 (config/routes.php)
 * - 主应用注解路由 (app/Controllers)
 * - 插件注解路由 (plugins/Controllers)
 *
 * 使用方法：
 *   php novaphp route:list
 *   php novaphp route:list --method=GET
 *   php novaphp route:list --path=/api
 *   php novaphp route:list --name=user
 *
 * @package Framework\Console\Commands
 */
class RouteListCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'route:list';

    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this->setName('route:list') // ✅ 关键修复
             ->setDescription('列出所有路由')
             ->setHelp('此命令显示系统中所有注册的路由，包括手动路由、注解路由和插件路由。')
             ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, '按 HTTP 方法筛选')
             ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, '按路径前缀筛选')
             ->addOption('name', null, InputOption::VALUE_OPTIONAL, '按路由名称筛选')
             ->addOption('json', 'j', InputOption::VALUE_NONE, '以 JSON 格式输出');
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

        // 获取筛选条件
        $methodFilter = strtoupper($input->getOption('method') ?? '');
        $pathFilter = $input->getOption('path');
        $nameFilter = $input->getOption('name');
        $jsonOutput = $input->getOption('json');

        // 收集所有路由
        $allRoutes = [];

        // 1. 加载手动路由
        $manualRoutes = $this->loadManualRoutes();
        foreach ($manualRoutes as $name => $route) {
            $allRoutes[] = $this->formatRoute($name, $route, '手动路由');
        }

        // 2. 加载主应用注解路由
        $mainRoutes = $this->loadAnnotatedRoutes(
            BASE_PATH . '/app/Controllers',
            'App\Controllers'
        );
        foreach ($mainRoutes as $name => $route) {
            $allRoutes[] = $this->formatRoute($name, $route, '主应用');
        }

        // 3. 加载插件路由
        $pluginRoutes = $this->loadPluginRoutes();
        foreach ($pluginRoutes as $name => $route) {
            $allRoutes[] = $this->formatRoute($name, $route, '插件');
        }

        // 4. 应用筛选
        $allRoutes = $this->filterRoutes($allRoutes, $methodFilter, $pathFilter, $nameFilter);

        // 5. 排序（按路径）
        usort($allRoutes, fn($a, $b) => strcmp($a['path'], $b['path']));

        // 6. 输出
        if ($jsonOutput) {
            $output->writeln(json_encode($allRoutes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->renderTable($output, $allRoutes);
        }

        $io->newLine();
        $io->text(sprintf('共 <info>%d</info> 条路由', count($allRoutes)));

        return Command::SUCCESS;
    }

    /**
     * 加载手动路由
     *
     * @return array
     */
    private function loadManualRoutes(): array
    {
        $routesFile = BASE_PATH . '/config/routes.php';
        if (!file_exists($routesFile)) {
            return [];
        }

        $routes = require $routesFile;
        if (!($routes instanceof RouteCollection)) {
            return [];
        }

        $result = [];
        foreach ($routes->all() as $name => $route) {
            $result[$name] = $route;
        }

        return $result;
    }

    /**
     * 加载注解路由
     *
     * @param string $controllerDir
     * @param string $namespace
     * @return array
     */
    private function loadAnnotatedRoutes(string $controllerDir, string $namespace): array
    {
        if (!is_dir($controllerDir)) {
            return [];
        }

        $loader = new AttributeRouteLoader($controllerDir, $namespace);
        $routes = $loader->loadRoutes();

        $result = [];
        foreach ($routes->all() as $name => $route) {
            $result[$name] = $route;
        }

        return $result;
    }

    /**
     * 加载插件路由
     *
     * @return array
     */
    private function loadPluginRoutes(): array
    {
        $pluginDir = BASE_PATH . '/plugins';
        if (!is_dir($pluginDir)) {
            return [];
        }

        $result = [];

        // 扫描插件目录
        $directories = scandir($pluginDir);
        if ($directories === false) {
            return [];
        }

        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $manifestPath = "{$pluginDir}/{$dir}/plugin.json";
            if (!file_exists($manifestPath)) {
                continue;
            }

            // 解析插件获取命名空间
            $json = file_get_contents($manifestPath);
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $namespace = $data['namespace'] ?? "Plugins\\{$dir}";

            // 加载插件控制器路由
            $controllerDir = "{$pluginDir}/{$dir}/Controllers";
            if (is_dir($controllerDir)) {
                $routes = $this->loadAnnotatedRoutes($controllerDir, $namespace . '\\Controllers');
                foreach ($routes as $name => $route) {
                    $result[$name] = $route;
                }
            }
        }

        return $result;
    }

    /**
     * 格式化路由信息
     *
     * @param string $name
     * @param Route $route
     * @param string $source
     * @return array
     */
    private function formatRoute(string $name, Route $route, string $source): array
    {
        $methods = $route->getMethods();
        if (empty($methods)) {
            $methods = ['ANY'];
        }

        return [
            'name' => $name,
            'path' => $route->getPath(),
            'methods' => implode(', ', $methods),
            'controller' => $route->getDefault('_controller') ?? '-',
            'source' => $source,
        ];
    }

    /**
     * 筛选路由
     *
     * @param array $routes
     * @param string|null $method
     * @param string|null $path
     * @param string|null $name
     * @return array
     */
    private function filterRoutes(array $routes, ?string $method, ?string $path, ?string $name): array
    {
        return array_filter($routes, function ($route) use ($method, $path, $name) {
            // 方法筛选
            if ($method && !str_contains($route['methods'], $method)) {
                return false;
            }

            // 路径筛选
            if ($path && !str_contains($route['path'], $path)) {
                return false;
            }

            // 名称筛选
            if ($name && !str_contains($route['name'], $name)) {
                return false;
            }

            return true;
        });
    }

    /**
     * 渲染表格
     *
     * @param OutputInterface $output
     * @param array $routes
     */
    private function renderTable(OutputInterface $output, array $routes): void
    {
        $table = new Table($output);
        $table->setHeaders(['名称', '路径', '方法', '控制器', '来源']);
        $table->setColumnWidths([30, 40, 10, 45, 10]);

        foreach ($routes as $route) {
            $table->addRow([
                $this->truncate($route['name'], 28),
                $this->truncate($route['path'], 38),
                $route['methods'],
                $this->truncate($route['controller'], 43),
                $route['source'],
            ]);
        }

        $table->render();
    }

    /**
     * 截断字符串
     *
     * @param string $str
     * @param int $length
     * @return string
     */
    private function truncate(string $str, int $length): string
    {
        if (strlen($str) <= $length) {
            return $str;
        }
        return substr($str, 0, $length - 3) . '...';
    }
}
