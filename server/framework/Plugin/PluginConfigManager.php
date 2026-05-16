<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginConfigManager.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Plugin;

use RuntimeException;

/**
 * 插件配置管理器
 *
 * 负责插件配置的读取、合并和管理。
 *
 * 配置优先级：
 * 1. 运行时配置: config/plugin/{name}.php
 * 2. 插件默认配置: plugins/{name}/config/config.php
 * 3. 全局默认配置: config/plugin/plugins.php -> defaults
 *
 * @package Framework\Plugin
 */
class PluginConfigManager
{
    /**
     * 配置缓存
     *
     * @var array
     */
    private array $configCache = [];

    /**
     * 获取插件配置
     *
     * @param string $pluginName 插件名称
     * @param string|null $key 配置键（支持点语法），null 返回全部配置
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $pluginName, ?string $key = null, mixed $default = null): mixed
    {
        $config = $this->loadPluginConfig($pluginName);

        if ($key === null) {
            return $config;
        }

        return $this->getNestedValue($config, $key, $default);
    }

    /**
     * 设置插件配置（运行时）
     *
     * 注意：这只是运行时修改，不会持久化到文件。
     *
     * @param string $pluginName 插件名称
     * @param string $key 配置键
     * @param mixed $value 配置值
     * @return self
     */
    public function set(string $pluginName, string $key, mixed $value): self
    {
        if (!isset($this->configCache[$pluginName])) {
            $this->loadPluginConfig($pluginName);
        }

        $this->setNestedValue($this->configCache[$pluginName], $key, $value);

        return $this;
    }

    /**
     * 持久化插件配置到文件
     *
     * @param string $pluginName 插件名称
     * @param array $config 完整配置
     * @return bool
     */
    public function save(string $pluginName, array $config): bool
    {
        $configFile = BASE_PATH . "/config/plugin/{$pluginName}.php";

        $content = "<?php\n\ndeclare(strict_types=1);\n\n/**\n * {$pluginName} 插件配置文件\n */\n\nreturn " . $this->varExport($config) . ";\n";

        $result = file_put_contents($configFile, $content);

        // 更新缓存
        $this->configCache[$pluginName] = $config;

        return $result !== false;
    }

    /**
     * 加载插件配置（合并策略）
     *
     * @param string $pluginName
     * @return array
     */
    private function loadPluginConfig(string $pluginName): array
    {
        // 检查缓存
        if (isset($this->configCache[$pluginName])) {
            return $this->configCache[$pluginName];
        }

        // 1. 读取全局默认配置
        $globalDefaults = $this->getGlobalDefaults();

        // 2. 读取插件默认配置
        $pluginDefaults = $this->getPluginDefaults($pluginName);

        // 3. 读取运行时配置
        $runtimeConfig = $this->getRuntimeConfig($pluginName);

        // 4. 合并配置（优先级：运行时 > 插件默认 > 全局默认）
        $merged = array_replace_recursive($globalDefaults, $pluginDefaults, $runtimeConfig);

        // 缓存
        $this->configCache[$pluginName] = $merged;

        return $merged;
    }

    /**
     * 获取全局默认配置
     *
     * @return array
     */
    private function getGlobalDefaults(): array
    {
        if (function_exists('config')) {
            return config('plugin.plugins.defaults', []);
        }

        // 直接加载配置文件
        $configFile = BASE_PATH . '/config/plugin/plugins.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            return $config['defaults'] ?? [];
        }

        return [];
    }

    /**
     * 获取插件默认配置
     *
     * @param string $pluginName
     * @return array
     */
    private function getPluginDefaults(string $pluginName): array
    {
        // 尝试从 manifest 获取插件路径
        $manifestFile = BASE_PATH . "/plugins/{$pluginName}/plugin.json";
        if (file_exists($manifestFile)) {
            $json = file_get_contents($manifestFile);
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $pluginPath = dirname($manifestFile);

            $configFile = $pluginPath . '/config/config.php';
            if (file_exists($configFile)) {
                return require $configFile;
            }
        }

        return [];
    }

    /**
     * 获取运行时配置
     *
     * @param string $pluginName
     * @return array
     */
    private function getRuntimeConfig(string $pluginName): array
    {
        if (function_exists('config')) {
            $config = config("plugin.{$pluginName}");
            if (is_array($config)) {
                return $config;
            }
        }

        // 直接加载配置文件
        $configFile = BASE_PATH . "/config/plugin/{$pluginName}.php";
        if (file_exists($configFile)) {
            return require $configFile;
        }

        return [];
    }

    /**
     * 获取嵌套值（支持点语法）
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getNestedValue(array $array, string $key, mixed $default): mixed
    {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * 设置嵌套值（支持点语法）
     *
     * @param array &$array
     * @param string $key
     * @param mixed $value
     */
    private function setNestedValue(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    /**
     * 格式化数组为 PHP 代码
     *
     * @param array $array
     * @param int $indent
     * @return string
     */
    private function varExport(array $array, int $indent = 0): string
    {
        $spaces = str_repeat('    ', $indent);
        $lines = ["["];

        foreach ($array as $key => $value) {
            $keyStr = is_string($key) ? "'{$key}'" : $key;

            if (is_array($value)) {
                $valueStr = $this->varExport($value, $indent + 1);
            } elseif (is_bool($value)) {
                $valueStr = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $valueStr = 'null';
            } elseif (is_string($value)) {
                $valueStr = "'{$value}'";
            } else {
                $valueStr = $value;
            }

            $lines[] = "{$spaces}    {$keyStr} => {$valueStr},";
        }

        $lines[] = "{$spaces}]";

        return implode("\n", $lines);
    }

    /**
     * 清除配置缓存
     *
     * @param string|null $pluginName 插件名称，null 清除全部
     * @return self
     */
    public function clearCache(?string $pluginName = null): self
    {
        if ($pluginName === null) {
            $this->configCache = [];
        } else {
            unset($this->configCache[$pluginName]);
        }

        return $this;
    }
}
