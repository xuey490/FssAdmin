<?php

declare(strict_types=1);

namespace App\Services;

use Framework\Plugin\Migration\MigrationRunner;
use Framework\Plugin\PluginCacheManager;
use Framework\Plugin\PluginConfigManager;
use Framework\Plugin\PluginManager;
use RuntimeException;

class PluginService
{
    /**
     * 插件诊断信息
     * @return array<array-key, mixed>
     */
    public function doctor(?string $pluginName = null): array
    {
        $configFile = BASE_PATH . '/config/plugin/plugins.php';
        $rawConfig = file_exists($configFile) ? (require $configFile) : [];
        $normalizedConfig = $this->loadPluginConfig();

        $rawPaths = is_array($rawConfig['paths'] ?? null) ? $rawConfig['paths'] : [];
        $effectivePaths = is_array($normalizedConfig['paths'] ?? null) ? $normalizedConfig['paths'] : [];

        $manager = $this->createManager();
        $manifests = $manager->getManifests();

        $plugins = [];
        foreach ($manifests as $name => $manifest) {
            if ($pluginName !== null && $pluginName !== '' && $pluginName !== $name) {
                continue;
            }

            $controllerDir = $manifest->getControllerDir();
            $controllerFiles = $this->scanPhpFiles($controllerDir);
            $controllers = [];

            foreach ($controllerFiles as $file) {
                $className = $this->controllerClassFromFile($manifest->namespace, $controllerDir, $file);
                $actualPath = realpath($file) ?: $file;

                if (class_exists($className)) {
                    try {
                        $ref = new \ReflectionClass($className);
                        $actualPath = $ref->getFileName() ?: $actualPath;
                    } catch (\Throwable $e) {
                        // ignore and fallback
                    }
                }

                $controllers[] = [
                    'class' => $className,
                    'file' => $actualPath,
                ];
            }

            $plugins[] = [
                'name' => $name,
                'namespace' => $manifest->namespace,
                'plugin_path' => $manifest->path,
                'manifest_file' => $manifest->path . DIRECTORY_SEPARATOR . 'plugin.json',
                'controller_dir' => $controllerDir,
                'controller_count' => count($controllers),
                'controllers' => $controllers,
            ];
        }

        return [
            'base_path' => BASE_PATH,
            'config_file' => $configFile,
            'configured_paths_raw' => $rawPaths,
            'effective_paths' => $effectivePaths,
            'existing_paths' => array_values(array_filter($effectivePaths, static fn (string $p): bool => is_dir($p))),
            'missing_paths' => array_values(array_filter($rawPaths, static fn (string $p): bool => !is_dir($p))),
            'plugin_count' => count($plugins),
            'plugins' => $plugins,
        ];
    }

    /**
     * 获取插件列表（已发现 + 已安装状态）
     * @return array<array-key, mixed>
     */
    public function getList(): array
    {
        $manager = $this->createManager();
        $installed = $this->loadPluginConfig()['installed'] ?? [];

        $list = [];
        foreach ($manager->getManifests() as $name => $manifest) {
            $itemInstalled = isset($installed[$name]);
            $itemEnabled = (bool)($installed[$name]['enabled'] ?? false);

            $list[] = [
                'name' => $name,
                'title' => $manifest->title,
                'version' => $installed[$name]['version'] ?? $manifest->version,
                'description' => $manifest->description,
                'author' => $manifest->author,
                'installed' => $itemInstalled,
                'enabled' => $itemEnabled,
                'status_text' => !$itemInstalled ? '未安装' : ($itemEnabled ? '已启用' : '已禁用'),
                'dependencies' => $manifest->dependencies,
                'requires' => $manifest->requires,
            ];
        }

        usort($list, static fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $list;
    }

    /**
     * 获取插件详情
     */
    /**
     * @return array<array-key, mixed>
     */
    public function getDetail(string $name): array
    {
        $manager = $this->createManager();
        $manifest = $manager->getManifest($name);

        if ($manifest === null) {
            throw new RuntimeException("插件不存在: {$name}");
        }

        $config = $this->loadPluginConfig();
        $installed = $config['installed'][$name] ?? [];

        return array_merge($manifest->toArray(), [
            'installed' => isset($config['installed'][$name]),
            'enabled' => (bool)($installed['enabled'] ?? false),
            'installed_info' => $installed,
            'dependents' => $manager->getDependents($name),
        ]);
    }

    /**
     * 创建本地插件骨架
     * @return array<array-key, mixed>
     * @param array<array-key, mixed> $payload
     */
    public function createPlugin(array $payload): array
    {
        $name = strtolower(trim((string)($payload['name'] ?? '')));
        $title = trim((string)($payload['title'] ?? $name));
        $description = trim((string)($payload['description'] ?? ''));
        $author = trim((string)($payload['author'] ?? 'Local Developer'));

        if ($name === '' || !preg_match('/^[a-z][a-z0-9_-]*$/', $name)) {
            return ['success' => false, 'message' => '插件名称不合法'];
        }

        $pluginDir = BASE_PATH . '/plugins/' . $name;
        if (is_dir($pluginDir)) {
            return ['success' => false, 'message' => "插件目录已存在: {$name}"];
        }

        $className = $this->toClassName($name);

        $dirs = [
            $pluginDir . '/Controllers',
            $pluginDir . '/Models',
            $pluginDir . '/Services',
            $pluginDir . '/Hooks',
            $pluginDir . '/database/migrations',
            $pluginDir . '/config',
            $pluginDir . '/resources/views',
        ];
        foreach ($dirs as $dir) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                return ['success' => false, 'message' => "创建目录失败: {$dir}"];
            }
        }

        $manifest = [
            'name' => $name,
            'title' => $title,
            'version' => '1.0.0',
            'description' => $description,
            'author' => $author,
            'namespace' => "Plugins\\{$className}",
            'requires' => [
                'php' => '^8.3',
                'Fssphp' => '^0.8.0',
            ],
            'dependencies' => new \stdClass(),
            'autoload' => [
                'psr-4' => [
                    "Plugins\\{$className}\\" => '',
                ],
            ],
            'routes' => [
                'prefix' => "/api/{$name}",
            ],
        ];

        file_put_contents(
            $pluginDir . '/plugin.json',
            (string) json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $controller = <<<PHP
<?php

declare(strict_types=1);

namespace Plugins\\{$className}\\Controllers;

use Framework\\Attributes\\Route;
use Framework\\Basic\\BaseController;
use Framework\\Basic\\BaseJsonResponse;
use Symfony\\Component\\HttpFoundation\\Request;

class IndexController extends BaseController
{
    #[Route(path: '/api/{$name}', methods: ['GET'], name: '{$name}.index')]
    /**
     */
    public function index(Request \$request): BaseJsonResponse
    {
        return \$this->success([
            'name' => '{$name}',
            'message' => '{$title} 插件运行中',
        ]);
    }
}
PHP;
        file_put_contents($pluginDir . '/Controllers/IndexController.php', $controller);

        $defaultConfig = <<<PHP
<?php

declare(strict_types=1);

return [
    'enabled' => true,
];
PHP;
        file_put_contents($pluginDir . '/config/config.php', $defaultConfig);

        (new PluginCacheManager())->clearManifestCache();

        return ['success' => true, 'message' => "插件 {$name} 创建成功"];
    }

    /**
     * 安装插件（可自动安装依赖）
     */
        /**
     * @return array<array-key, mixed>
         */
    public function install(string $name, bool $autoInstallDependencies = true, bool $force = false): array
    {
        $manager = $this->createManager();

        if ($force && $manager->isInstalled($name)) {
            $rollback = $manager->uninstall($name, true);
            if (!$rollback['success']) {
                return $rollback;
            }
        }

        if ($autoInstallDependencies) {
            $resolved = [];
            $result = $this->installWithDependencies($manager, $name, $resolved, []);
            if (!$result['success']) {
                return $result;
            }
            return $result;
        }

        return $manager->install($name);
    }

    /**
     * 卸载插件
     */
        /**
     * @return array<array-key, mixed>
         */
    public function uninstall(string $name, bool $force = false): array
    {
        $manager = $this->createManager();
        return $manager->uninstall($name, $force);
    }

    /**
     * 启用插件
     */
    /**
     * @return array<array-key, mixed>
     */
    public function enable(string $name): array
    {
        $manager = $this->createManager();
        return $manager->enable($name);
    }

    /**
     * 禁用插件
     * @return array<array-key, mixed>
     */
    public function disable(string $name): array
    {
        $manager = $this->createManager();
        return $manager->disable($name);
    }

    /**
     * 获取插件配置
     */
        /**
     * @return array<array-key, mixed>
         */
    public function getConfig(string $name): array
    {
        $manager = $this->createManager();
        if ($manager->getManifest($name) === null) {
            throw new RuntimeException("插件不存在: {$name}");
        }

        return (new PluginConfigManager())->get($name);
    }

    /**
     * 更新插件配置
     */
        /**
     * @return array<array-key, mixed>
     * @param array<array-key, mixed> $config
         */
    public function updateConfig(string $name, array $config): array
    {
        $manager = $this->createManager();
        if ($manager->getManifest($name) === null) {
            return ['success' => false, 'message' => "插件不存在: {$name}"];
        }

        $ok = (new PluginConfigManager())->save($name, $config);
        if (!$ok) {
            return ['success' => false, 'message' => '配置保存失败'];
        }

        (new PluginCacheManager())->clearConfigCache($name);

        return ['success' => true, 'message' => '插件配置已更新'];
    }

    /**
     * 上传并安装插件 ZIP（供市场安装复用）
     *
     * @param array{name:string,tmp_name:string} $file
     */
/**
     * @return array<array-key, mixed>
     * @param array<array-key, mixed> $file
 */
    public function uploadAndInstall(array $file): array
    {
        $tmpPath = (string)($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !file_exists($tmpPath)) {
            return ['success' => false, 'message' => '插件包不存在'];
        }

        if (!class_exists(\ZipArchive::class)) {
            return ['success' => false, 'message' => 'ZipArchive 扩展未安装'];
        }

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath) !== true) {
            return ['success' => false, 'message' => '插件包解压失败'];
        }

        $extractDir = BASE_PATH . '/storage/tmp/plugins/extract_' . uniqid();
        if (!mkdir($extractDir, 0755, true) && !is_dir($extractDir)) {
            $zip->close();
            return ['success' => false, 'message' => '创建临时目录失败'];
        }

        $zip->extractTo($extractDir);
        $zip->close();

        $pluginName = $this->detectPluginRoot($extractDir);
        if ($pluginName === null) {
            $this->deleteDirectory($extractDir);
            return ['success' => false, 'message' => '未找到 plugin.json'];
        }

        $sourceDir = $extractDir . '/' . $pluginName;
        $targetDir = BASE_PATH . '/plugins/' . $pluginName;

        if (is_dir($targetDir)) {
            $this->deleteDirectory($extractDir);
            return ['success' => false, 'message' => "插件目录已存在: {$pluginName}"];
        }

        if (!rename($sourceDir, $targetDir)) {
            $this->deleteDirectory($extractDir);
            return ['success' => false, 'message' => '移动插件目录失败'];
        }

        $this->deleteDirectory($extractDir);
        (new PluginCacheManager())->clearManifestCache();

        return $this->install($pluginName, true, false);
    }

    /**
     * 递归安装依赖
     *
     * @param array<string, bool> $resolved
     * @param array<string, bool> $stack
     */
            /**
     * @return array<array-key, mixed>
     * @param array<array-key, mixed> $resolved
     * @param array<array-key, mixed> $stack
             */
    private function installWithDependencies(PluginManager $manager, string $name, array &$resolved, array $stack): array
    {
        if (isset($resolved[$name])) {
            return ['success' => true, 'message' => 'ok', 'installed' => []];
        }

        if (isset($stack[$name])) {
            return ['success' => false, 'message' => "检测到循环依赖: {$name}"];
        }

        $manifest = $manager->getManifest($name);
        if ($manifest === null) {
            return ['success' => false, 'message' => "插件不存在: {$name}"];
        }

        $stack[$name] = true;
        $installedChain = [];
        foreach ($manifest->dependencies as $depName => $_depVersion) {
            $depName = (string)$depName;

            if ($manager->getManifest($depName) === null) {
                return ['success' => false, 'message' => "缺少依赖插件: {$depName}"];
            }

            $depResult = $this->installWithDependencies($manager, $depName, $resolved, $stack);
            if (!$depResult['success']) {
                return $depResult;
            }
            if (!empty($depResult['installed'])) {
                $installedChain = array_merge($installedChain, $depResult['installed']);
            }

            if ($manager->isInstalled($depName) && !$manager->isEnabled($depName)) {
                $enabled = $manager->enable($depName);
                if (!$enabled['success']) {
                    return $enabled;
                }
            }
        }

        if (!$manager->isInstalled($name)) {
            $result = $manager->install($name);
            if (!$result['success']) {
                return $result;
            }
            $installedChain[] = $name;
        } elseif (!$manager->isEnabled($name)) {
            $enabled = $manager->enable($name);
            if (!$enabled['success']) {
                return $enabled;
            }
        }

        $resolved[$name] = true;

        return [
            'success' => true,
            'message' => '安装成功',
            'installed' => array_values(array_unique($installedChain)),
        ];
    }

    private function createManager(): PluginManager
    {
        $config = $this->loadPluginConfig();
        $manager = new PluginManager($config);
        $manager->setMigrationRunner(new MigrationRunner());
        $manager->discover();
        return $manager;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function loadPluginConfig(): array
    {
        $file = BASE_PATH . '/config/plugin/plugins.php';
        if (!file_exists($file)) {
            throw new RuntimeException('插件配置文件不存在');
        }

        $config = require $file;
        $paths = is_array($config['paths'] ?? null) ? $config['paths'] : [];
        $defaultPath = BASE_PATH . '/plugins';

        $normalized = [];
        foreach ($paths as $path) {
            if (is_string($path) && $path !== '' && is_dir($path)) {
                $normalized[] = $path;
            }
        }

        if (!in_array($defaultPath, $normalized, true) && is_dir($defaultPath)) {
            $normalized[] = $defaultPath;
        }

        $config['paths'] = $normalized;

        return $config;
    }

    private function toClassName(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
    }

    private function detectPluginRoot(string $extractDir): ?string
    {
        $entries = scandir($extractDir) ?: [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $candidateDir = $extractDir . '/' . $entry;
            $manifestFile = $candidateDir . '/plugin.json';
            if (is_dir($candidateDir) && file_exists($manifestFile)) {
                return $entry;
            }
        }
        return null;
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir) ?: [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /**
     * 扫描目录下全部 PHP 文件（递归）
     *
     * @return array<int, string>
     */
    private function scanPhpFiles(string $dir): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $result = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            if (strtolower($fileInfo->getExtension()) !== 'php') {
                continue;
            }
            $result[] = $fileInfo->getPathname();
        }

        sort($result);
        return $result;
    }

    private function controllerClassFromFile(string $pluginNamespace, string $controllerDir, string $file): string
    {
        $relative = str_replace($controllerDir, '', $file);
        $relative = trim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative), DIRECTORY_SEPARATOR);
        $relativeClass = str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relative);

        return rtrim($pluginNamespace, '\\') . '\\Controllers\\' . $relativeClass;
    }
}