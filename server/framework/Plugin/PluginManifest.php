<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: PluginManifest.php
 * @Date: 2025-03-31
 * @Developer: NovaFrame Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Plugin;

use InvalidArgumentException;
use JsonException;

/**
 * 插件清单解析器
 *
 * 解析 plugin.json 文件，封装插件元数据。
 *
 * @package Framework\Plugin
 */
class PluginManifest
{
    /**
     * 构造函数
     *
     * @param string $name 插件名称（唯一标识符）
     * @param string $title 插件显示标题
     * @param string $version 插件版本
     * @param string $description 插件描述
     * @param string $author 插件作者
     * @param string $namespace 插件命名空间
     * @param string $path 插件目录路径
     * @param array $requires 运行环境要求（PHP版本、框架版本等）
     * @param array $dependencies 插件依赖（其他插件）
     * @param array $hooks 生命周期钩子
     * @param array $routes 路由配置
     * @param array $autoload 自动加载配置
     * @param array $extra 额外配置
     */
    public function __construct(
        public readonly string $name,
        public readonly string $title,
        public readonly string $version,
        public readonly string $description,
        public readonly string $author,
        public readonly string $namespace,
        public readonly string $path,
        public readonly array $requires = [],
        public readonly array $dependencies = [],
        public readonly array $hooks = [],
        public readonly array $routes = [],
        public readonly array $autoload = [],
        public readonly array $extra = []
    ) {}

    /**
     * 从 plugin.json 文件解析插件清单
     *
     * @param string $jsonPath plugin.json 文件路径
     * @return self
     * @throws InvalidArgumentException 文件不存在或格式错误
     */
    public static function fromFile(string $jsonPath): self
    {
        if (!file_exists($jsonPath)) {
            throw new InvalidArgumentException("Plugin manifest file not found: {$jsonPath}");
        }

        $json = file_get_contents($jsonPath);
        if ($json === false) {
            throw new InvalidArgumentException("Failed to read plugin manifest: {$jsonPath}");
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException("Invalid JSON in plugin manifest: {$jsonPath} - " . $e->getMessage());
        }

        return self::fromArray($data, dirname($jsonPath));
    }

    /**
     * 从数组创建插件清单
     *
     * @param array $data 插件数据
     * @param string $path 插件目录路径
     * @return self
     * @throws InvalidArgumentException 缺少必需字段
     */
    public static function fromArray(array $data, string $path): self
    {
        // 验证必需字段
        $required = ['name', 'version'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field '{$field}' in plugin manifest");
            }
        }

        // 验证插件名称格式
        $name = $data['name'];
        if (!preg_match('/^[a-z][a-z0-9_-]*$/i', $name)) {
            throw new InvalidArgumentException("Invalid plugin name '{$name}': must start with a letter and contain only letters, numbers, underscores, and hyphens");
        }

        // 验证版本号格式
        $version = $data['version'];
        if (!preg_match('/^\d+\.\d+\.\d+(-[a-zA-Z0-9]+)?$/', $version)) {
            throw new InvalidArgumentException("Invalid version format '{$version}': expected semantic version (e.g., 1.0.0)");
        }

        return new self(
            name: $name,
            title: $data['title'] ?? $name,
            version: $version,
            description: $data['description'] ?? '',
            author: $data['author'] ?? 'Unknown',
            namespace: $data['namespace'] ?? "Plugins\\{$name}",
            path: realpath($path) ?: $path,
            requires: $data['requires'] ?? [],
            dependencies: $data['dependencies'] ?? [],
            hooks: $data['hooks'] ?? [],
            routes: $data['routes'] ?? [],
            autoload: $data['autoload'] ?? [],
            extra: $data['extra'] ?? []
        );
    }

    /**
     * 转换为数组
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'version' => $this->version,
            'description' => $this->description,
            'author' => $this->author,
            'namespace' => $this->namespace,
            'path' => $this->path,
            'requires' => $this->requires,
            'dependencies' => $this->dependencies,
            'hooks' => $this->hooks,
            'routes' => $this->routes,
            'autoload' => $this->autoload,
            'extra' => $this->extra,
        ];
    }

    /**
     * 获取控制器目录
     *
     * @return string
     */
    public function getControllerDir(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'Controllers';
    }

    /**
     * 获取模型目录
     *
     * @return string
     */
    public function getModelDir(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'Models';
    }

    /**
     * 获取服务目录
     *
     * @return string
     */
    public function getServiceDir(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'Services';
    }

    /**
     * 获取迁移目录
     *
     * @return string
     */
    public function getMigrationDir(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
    }

    /**
     * 获取配置目录
     *
     * @return string
     */
    public function getConfigDir(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'config';
    }

    /**
     * 获取资源目录
     *
     * @return string
     */
    public function getResourceDir(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'resources';
    }

    /**
     * 获取视图目录
     *
     * @return string
     */
    public function getViewDir(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * 检查是否满足运行环境要求
     *
     * @return array 包含 'satisfied' => bool, 'errors' => array
     */
    public function checkRequirements(): array
    {
        $errors = [];

        // 检查 PHP 版本
        if (isset($this->requires['php'])) {
            $phpVersion = $this->requires['php'];
            if (!$this->satisfiesVersion(PHP_VERSION, $phpVersion)) {
                $errors[] = "PHP version mismatch: required {$phpVersion}, current " . PHP_VERSION;
            }
        }

        // 检查框架版本（如果定义了常量）
        if (isset($this->requires['Fssphp']) && defined('FSSPHP_VERSION')) {
            $frameworkVersion = $this->requires['Fssphp'];
            if (!$this->satisfiesVersion(FSSPHP_VERSION, $frameworkVersion)) {
                $errors[] = "Framework version mismatch: required {$frameworkVersion}, current " . FSSPHP_VERSION;
            }
        }

        // 检查扩展
        if (isset($this->requires['extensions'])) {
            foreach ($this->requires['extensions'] as $ext) {
                if (!extension_loaded($ext)) {
                    $errors[] = "Required extension not loaded: {$ext}";
                }
            }
        }

        return [
            'satisfied' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * 检查版本是否满足要求
     *
     * @param string $version 当前版本
     * @param string $constraint 版本约束（如 ^1.0.0, >=1.0.0 <2.0.0）
     * @return bool
     */
    private function satisfiesVersion(string $version, string $constraint): bool
    {
        // 简化版本比较，支持 ^, >=, <=, >, < 操作符
        $constraint = trim($constraint);

        // 处理 ^ 操作符（语义化版本兼容）
        if (str_starts_with($constraint, '^')) {
            $requiredVersion = substr($constraint, 1);
            return version_compare($version, $requiredVersion, '>=') &&
                   version_compare($version, $this->getNextMajorVersion($requiredVersion), '<');
        }

        // 处理 >= 操作符
        if (str_starts_with($constraint, '>=')) {
            return version_compare($version, trim(substr($constraint, 2)), '>=');
        }

        // 处理 <= 操作符
        if (str_starts_with($constraint, '<=')) {
            return version_compare($version, trim(substr($constraint, 2)), '<=');
        }

        // 处理 > 操作符
        if (str_starts_with($constraint, '>') && !str_starts_with($constraint, '>=')) {
            return version_compare($version, trim(substr($constraint, 1)), '>');
        }

        // 处理 < 操作符
        if (str_starts_with($constraint, '<') && !str_starts_with($constraint, '<=')) {
            return version_compare($version, trim(substr($constraint, 1)), '<');
        }

        // 默认精确匹配或大于等于
        return version_compare($version, $constraint, '>=');
    }

    /**
     * 获取下一个主版本号
     *
     * @param string $version 当前版本
     * @return string
     */
    private function getNextMajorVersion(string $version): string
    {
        $parts = explode('.', $version);
        $major = (int)($parts[0] ?? 0);
        return ($major + 1) . '.0.0';
    }

    /**
     * 获取安装钩子类
     *
     * @return string|null
     */
    public function getInstallHook(): ?string
    {
        return $this->hooks['install'] ?? null;
    }

    /**
     * 获取卸载钩子类
     *
     * @return string|null
     */
    public function getUninstallHook(): ?string
    {
        return $this->hooks['uninstall'] ?? null;
    }

    /**
     * 获取启用钩子类
     *
     * @return string|null
     */
    public function getEnableHook(): ?string
    {
        return $this->hooks['enable'] ?? null;
    }

    /**
     * 获取禁用钩子类
     *
     * @return string|null
     */
    public function getDisableHook(): ?string
    {
        return $this->hooks['disable'] ?? null;
    }

    /**
     * 获取升级钩子类
     *
     * @return string|null
     */
    public function getUpgradeHook(): ?string
    {
        return $this->hooks['upgrade'] ?? null;
    }
}