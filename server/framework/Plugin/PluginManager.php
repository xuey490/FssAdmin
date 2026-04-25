<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginManager.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Plugin;

use Composer\Autoload\ClassLoader;
use Framework\Container\Container;
use Framework\Plugin\Migration\MigrationRunner;
use InvalidArgumentException;
use RuntimeException;

/**
 * 插件管理器
 *
 * 负责插件的发现、加载、安装、卸载、启用、禁用等生命周期管理。
 *
 * @package Framework\Plugin
 */
class PluginManager
{
    /**
     * 已发现的插件清单（已解析的 manifest）
     *
     * @var array<string, PluginManifest>
     */
    private array $manifests = [];

    /**
     * 已加载的插件实例
     *
     * @var array<string, PluginManifest>
     */
    private array $loaded = [];

    /**
     * 插件配置
     *
     * @var array
     */
    private array $config;

    /**
     * Composer 自动加载器实例
     *
     * @var ClassLoader|null
     */
    private ?ClassLoader $classLoader = null;

    /**
     * 迁移执行器
     *
     * @var MigrationRunner|null
     */
    private ?MigrationRunner $migrationRunner = null;

    /**
     * 构造函数
     *
     * @param array $config 插件配置
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'installed' => [],
            'paths' => [BASE_PATH . '/plugins'],
            'defaults' => [
                'autoload_providers' => true,
                'autoload_routes' => true,
                'cache_enabled' => true,
                'cache_ttl' => 3600,
            ],
            'namespace_prefix' => 'Plugins\\',
        ], $config);
    }

    /**
     * 设置迁移执行器
     *
     * @param MigrationRunner $runner
     * @return self
     */
    public function setMigrationRunner(MigrationRunner $runner): self
    {
        $this->migrationRunner = $runner;
        return $this;
    }

    /**
     * 发现所有插件
     *
     * 扫描配置的插件目录，解析所有 plugin.json 文件。
     *
     * @return self
     */
    public function discover(): self
    {
        $paths = $this->config['paths'] ?? [BASE_PATH . '/plugins'];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $directories = scandir($path);
            if ($directories === false) {
                continue;
            }

            foreach ($directories as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }

                $manifestPath = "{$path}/{$dir}/plugin.json";
                if (file_exists($manifestPath)) {
                    try {
                        $manifest = PluginManifest::fromFile($manifestPath);
                        //dump($manifest);
                        $this->manifests[$manifest->name] = $manifest;
                    } catch (InvalidArgumentException $e) {
                        // 记录错误但继续扫描其他插件
                        error_log("[PluginManager] Failed to load manifest: {$manifestPath} - " . $e->getMessage());
                    }
                }
            }
        }

        return $this;
    }

    /**
     * 加载已启用的插件
     *
     * 根据依赖关系排序后依次加载。
     *
     * @return self
     */
    public function loadEnabled(): self
    {
        $installed = $this->config['installed'] ?? [];

        if (empty($installed)) {
            return $this;
        }

        // 拓扑排序确保依赖顺序
        $sortedPlugins = $this->sortByDependencies($installed);

        foreach ($sortedPlugins as $name => $info) {
            // 跳过未启用的插件
            if (!($info['enabled'] ?? false)) {
                continue;
            }

            // 跳过未发现的插件
            if (!isset($this->manifests[$name])) {
                error_log("[PluginManager] Plugin '{$name}' is installed but not found in any path");
                continue;
            }

            $this->load($name);
        }

        return $this;
    }

    /**
     * 加载单个插件
     *
     * @param string $name 插件名称
     * @return self
     * @throws InvalidArgumentException 插件不存在
     * @throws RuntimeException 依赖检查失败
     */
    public function load(string $name): self
    {
        $manifest = $this->manifests[$name] ?? null;
        if (!$manifest) {
            throw new InvalidArgumentException("Plugin not found: {$name}");
        }

        // 检查是否已加载
        if (isset($this->loaded[$name])) {
            return $this;
        }

        // 检查运行环境要求
        $requirements = $manifest->checkRequirements();
        if (!$requirements['satisfied']) {
            throw new RuntimeException(
                "Plugin '{$name}' requirements not satisfied: " . implode(', ', $requirements['errors'])
            );
        }

        // 检查插件依赖
        $dependencies = $this->checkDependencies($name);
        if (!$dependencies['satisfied']) {
            throw new RuntimeException(
                "Plugin '{$name}' dependencies not satisfied: " . implode(', ', $dependencies['errors'])
            );
        }

        // 注册命名空间自动加载
        $this->registerAutoload($manifest);

        // 标记为已加载
        $this->loaded[$name] = $manifest;

        return $this;
    }

    /**
     * 安装插件
     *
     * @param string $name 插件名称
     * @return array{success: bool, message: string, migrations: array}
     */
    public function install(string $name): array
    {
        $manifest = $this->manifests[$name] ?? null;
        if (!$manifest) {
            return ['success' => false, 'message' => "Plugin not found: {$name}", 'migrations' => []];
        }

        // 检查是否已安装
        if ($this->isInstalled($name)) {
            return ['success' => false, 'message' => "Plugin already installed: {$name}", 'migrations' => []];
        }

        // 检查运行环境要求
        $requirements = $manifest->checkRequirements();
        if (!$requirements['satisfied']) {
            return [
                'success' => false,
                'message' => "Requirements not satisfied: " . implode(', ', $requirements['errors']),
                'migrations' => []
            ];
        }

        // 检查依赖
        $dependencies = $this->checkDependencies($name);
        if (!$dependencies['satisfied']) {
            return [
                'success' => false,
                'message' => "Dependencies not satisfied: " . implode(', ', $dependencies['errors']),
                'migrations' => []
            ];
        }

        // 加载插件（注册自动加载）
        $this->load($name);

        $migrations = [];

        try {
            // 执行数据库迁移
            if ($this->migrationRunner !== null && is_dir($manifest->getMigrationDir())) {
                $migrations = $this->migrationRunner->run($name, $manifest->getMigrationDir());
            }

            // 执行安装钩子
            $hookClass = $manifest->getInstallHook();
            if ($hookClass && class_exists($hookClass)) {
                $hook = new $hookClass();
                if (method_exists($hook, 'handle')) {
                    $hook->handle();
                }
            }

            // 更新配置
            $this->updateInstalledConfig($name, [
                'enabled' => true,
                'version' => $manifest->version,
                'installed_at' => date('Y-m-d H:i:s'),
            ]);
            $this->clearRouteCacheBestEffort();

            return [
                'success' => true,
                'message' => "Plugin '{$name}' installed successfully",
                'migrations' => $migrations,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Installation failed: " . $e->getMessage(),
                'migrations' => $migrations,
            ];
        }
    }

    /**
     * 卸载插件
     *
     * @param string $name 插件名称
     * @param bool $force 是否强制卸载（忽略依赖检查）
     * @return array{success: bool, message: string}
     */
    public function uninstall(string $name, bool $force = false): array
    {
        // 检查是否已安装
        if (!$this->isInstalled($name)) {
            return ['success' => false, 'message' => "Plugin not installed: {$name}"];
        }

        // 检查是否有其他插件依赖此插件
        $dependents = $this->getDependents($name);
        if (!empty($dependents) && !$force) {
            return [
                'success' => false,
                'message' => "Cannot uninstall: other plugins depend on it: " . implode(', ', $dependents)
            ];
        }

        $manifest = $this->manifests[$name] ?? null;

        try {
            // 执行卸载钩子
            if ($manifest) {
                $hookClass = $manifest->getUninstallHook();
                if ($hookClass && class_exists($hookClass)) {
                    $hook = new $hookClass();
                    if (method_exists($hook, 'handle')) {
                        $hook->handle();
                    }
                }

                // 回滚数据库迁移
                if ($this->migrationRunner !== null && is_dir($manifest->getMigrationDir())) {
                    $this->migrationRunner->rollback($name, $manifest->getMigrationDir());
                }
            }

            // 从已加载列表移除
            unset($this->loaded[$name]);

            // 更新配置
            $this->removeFromInstalledConfig($name);
            $this->clearRouteCacheBestEffort();

            return ['success' => true, 'message' => "Plugin '{$name}' uninstalled successfully"];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => "Uninstallation failed: " . $e->getMessage()];
        }
    }

    /**
     * 启用插件
     *
     * @param string $name 插件名称
     * @return array{success: bool, message: string}
     */
    public function enable(string $name): array
    {
        if (!$this->isInstalled($name)) {
            return ['success' => false, 'message' => "Plugin not installed: {$name}"];
        }

        if ($this->isEnabled($name)) {
            return ['success' => false, 'message' => "Plugin already enabled: {$name}"];
        }

        $manifest = $this->manifests[$name] ?? null;

        try {
            // 执行启用钩子
            if ($manifest) {
                $hookClass = $manifest->getEnableHook();
                if ($hookClass && class_exists($hookClass)) {
                    $hook = new $hookClass();
                    if (method_exists($hook, 'handle')) {
                        $hook->handle();
                    }
                }
            }

            // 加载插件
            $this->load($name);

            // 更新配置
            $this->updateInstalledConfig($name, ['enabled' => true]);
            $this->clearRouteCacheBestEffort();

            return ['success' => true, 'message' => "Plugin '{$name}' enabled successfully"];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => "Enable failed: " . $e->getMessage()];
        }
    }

    /**
     * 禁用插件
     *
     * @param string $name 插件名称
     * @return array{success: bool, message: string}
     */
    public function disable(string $name): array
    {
        if (!$this->isInstalled($name)) {
            return ['success' => false, 'message' => "Plugin not installed: {$name}"];
        }

        if (!$this->isEnabled($name)) {
            return ['success' => false, 'message' => "Plugin already disabled: {$name}"];
        }

        $manifest = $this->manifests[$name] ?? null;

        try {
            // 执行禁用钩子
            if ($manifest) {
                $hookClass = $manifest->getDisableHook();
                if ($hookClass && class_exists($hookClass)) {
                    $hook = new $hookClass();
                    if (method_exists($hook, 'handle')) {
                        $hook->handle();
                    }
                }
            }

            // 从已加载列表移除
            unset($this->loaded[$name]);

            // 更新配置
            $this->updateInstalledConfig($name, ['enabled' => false]);
            $this->clearRouteCacheBestEffort();

            return ['success' => true, 'message' => "Plugin '{$name}' disabled successfully"];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => "Disable failed: " . $e->getMessage()];
        }
    }

    /**
     * 检查插件是否已安装
     *
     * @param string $name 插件名称
     * @return bool
     */
    public function isInstalled(string $name): bool
    {
        return isset($this->config['installed'][$name]);
    }

    /**
     * 检查插件是否已启用
     *
     * @param string $name 插件名称
     * @return bool
     */
    public function isEnabled(string $name): bool
    {
        return ($this->config['installed'][$name]['enabled'] ?? false) === true;
    }

    /**
     * 检查插件是否已加载
     *
     * @param string $name 插件名称
     * @return bool
     */
    public function isLoaded(string $name): bool
    {
        return isset($this->loaded[$name]);
    }

    /**
     * 获取所有已发现的插件清单
     *
     * @return array<string, PluginManifest>
     */
    public function getManifests(): array
    {
        return $this->manifests;
    }

    /**
     * 获取单个插件清单
     *
     * @param string $name 插件名称
     * @return PluginManifest|null
     */
    public function getManifest(string $name): ?PluginManifest
    {
        return $this->manifests[$name] ?? null;
    }

    /**
     * 获取所有已加载的插件
     *
     * @return array<string, PluginManifest>
     */
    public function getLoaded(): array
    {
        return $this->loaded;
    }

    /**
     * 获取所有已加载插件的控制器目录
     *
     * 返回格式：[namespace => directory]
     *
     * @return array<string, string>
     */
    public function getControllerDirs(): array
    {
        $dirs = [];
        foreach ($this->loaded as $manifest) {
            $controllerDir = $manifest->getControllerDir();
            if (is_dir($controllerDir)) {
                $dirs[$manifest->namespace . '\\Controllers'] = $controllerDir;
            }
        }
        return $dirs;
    }

    /**
     * 获取插件服务
     *
     * @param string $pluginName 插件名称
     * @param string $serviceName 服务名称（不含命名空间）
     * @return object|null
     */
    public function getService(string $pluginName, string $serviceName): ?object
    {
        $manifest = $this->loaded[$pluginName] ?? null;
        if (!$manifest) {
            return null;
        }

        $serviceClass = $manifest->namespace . '\\Services\\' . $serviceName;
        if (class_exists($serviceClass)) {
            return app()->make($serviceClass);
        }

        return null;
    }

    /**
     * 检查插件依赖
     *
     * @param string $name 插件名称
     * @return array{satisfied: bool, errors: array}
     */
    public function checkDependencies(string $name): array
    {
        $manifest = $this->manifests[$name] ?? null;
        if (!$manifest) {
            return ['satisfied' => false, 'errors' => ["Plugin not found: {$name}"]];
        }

        $errors = [];
        $dependencies = $manifest->dependencies;

        //error_log(json_encode($dependencies));

        foreach ($dependencies as $depName => $versionConstraint) {
            // 检查依赖是否已安装
            if (!$this->isInstalled($depName)) {
                $errors[] = "Required plugin not installed: {$depName}";
                continue;
            }

            // 检查依赖是否已启用
            if (!$this->isEnabled($depName)) {
                $errors[] = "Required plugin not enabled: {$depName}";
                continue;
            }

            // 检查版本约束
            $depManifest = $this->manifests[$depName] ?? null;
            if ($depManifest) {
                // 简化版本检查
                // 完整实现应使用 composer/semver 包
            }
        }

        return ['satisfied' => empty($errors), 'errors' => $errors];
    }

    /**
     * 获取依赖指定插件的其他插件
     *
     * @param string $name 插件名称
     * @return array
     */
    public function getDependents(string $name): array
    {
        $dependents = [];

        foreach ($this->manifests as $manifest) {
            if (isset($manifest->dependencies[$name])) {
                $dependents[] = $manifest->name;
            }
        }

        return $dependents;
    }

    /**
     * 按依赖关系排序插件
     *
     * 使用拓扑排序确保依赖的插件先加载。
     *
     * @param array $plugins 插件列表
     * @return array
     */
    private function sortByDependencies(array $plugins): array
    {
        // 构建依赖图
        $graph = [];
        $inDegree = [];

        foreach ($plugins as $name => $info) {
            if (!isset($graph[$name])) {
                $graph[$name] = [];
                $inDegree[$name] = 0;
            }

            $manifest = $this->manifests[$name] ?? null;
            if ($manifest) {
                foreach ($manifest->dependencies as $dep => $version) {
                    // 只有当依赖也是已安装插件时才加入图
                    if (isset($plugins[$dep])) {
                        $graph[$dep][] = $name;
                        $inDegree[$name]++;
                    }
                }
            }
        }

        // Kahn 算法进行拓扑排序
        $queue = [];
        foreach ($inDegree as $node => $degree) {
            if ($degree === 0) {
                $queue[] = $node;
            }
        }

        $sorted = [];
        while (!empty($queue)) {
            $node = array_shift($queue);
            $sorted[$node] = $plugins[$node];

            foreach ($graph[$node] ?? [] as $neighbor) {
                $inDegree[$neighbor]--;
                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }

        // 检测循环依赖
        if (count($sorted) !== count($plugins)) {
            // 存在循环依赖，返回原始顺序
            error_log("[PluginManager] Circular dependency detected in plugins");
            return $plugins;
        }

        return $sorted;
    }

    /**
     * 注册插件命名空间到 Composer 自动加载器
     *
     * @param PluginManifest $manifest
     */
    private function registerAutoload(PluginManifest $manifest): void
    {
        if ($this->classLoader === null) {
            // 获取 Composer 自动加载器实例
            $this->classLoader = include BASE_PATH . '/vendor/autoload.php';
        }

        if ($this->classLoader instanceof ClassLoader) {
            $this->classLoader->addPsr4($manifest->namespace . '\\', $manifest->path);
        }
    }

    /**
     * 更新已安装插件配置
     *
     * @param string $name 插件名称
     * @param array $info 插件信息
     */
    private function updateInstalledConfig(string $name, array $info): void
    {
        $configFile = BASE_PATH . '/config/plugin/plugins.php';

        if (file_exists($configFile)) {
            $config = require $configFile;
            $config['installed'][$name] = array_merge(
                $config['installed'][$name] ?? [],
                $info
            );

            $this->config['installed'] = $config['installed'];

            // 写回配置文件
            $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($configFile, $content);
        }
    }

    /**
     * 从已安装插件配置中移除
     *
     * @param string $name 插件名称
     */
    private function removeFromInstalledConfig(string $name): void
    {
        $configFile = BASE_PATH . '/config/plugin/plugins.php';

        if (file_exists($configFile)) {
            $config = require $configFile;
            unset($config['installed'][$name]);

            $this->config['installed'] = $config['installed'];

            // 写回配置文件
            $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents($configFile, $content);
        }
    }

    /**
     * 尽力清理插件路由缓存，避免安装/卸载后命中旧路由。
     */
    private function clearRouteCacheBestEffort(): void
    {
        try {
            (new PluginCacheManager())->clearRouteCache();
        } catch (\Throwable $e) {
            // 缓存清理失败不阻断主流程
        }
    }
}
