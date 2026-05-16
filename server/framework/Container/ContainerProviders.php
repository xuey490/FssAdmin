<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Container;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * 容器服务提供者管理器
 *
 * 该类负责自动扫描并加载服务提供者类。
 * 支持同时加载核心服务提供者（框架内部）和应用服务提供者（用户自定义）。
 *
 * 工作流程：
 * 1. 扫描指定目录下的所有 Provider 文件
 * 2. 通过反射验证是否实现 ServiceProviderInterface 接口
 * 3. 调用 register() 方法注册服务定义
 * 4. 在容器编译后调用 boot() 方法执行初始化逻辑
 *
 * @package Framework\Container
 */
class ContainerProviders
{
    /**
     * 已加载的服务提供者实例列表
     *
     * @var array
     */
    protected array $loadedProviders = [];

    /**
     * 待引导的服务提供者列表
     *
     * 在 ContainerConfigurator 阶段暂时存储，
     * 等待 ContainerBuilder 阶段再执行 boot 方法。
     *
     * @var array
     */
    protected array $pendingBoot = [];

    /**
     * 扫描并注册所有服务提供者（核心 + 应用）
     *
     * 同时加载框架核心服务提供者和应用自定义服务提供者。
     * 核心服务提供者通过 Composer 自动映射定位。
     *
     * @param ContainerConfigurator $configurator   容器配置器
     * @param string                $namespaceBase   应用提供者的命名空间基础
     * @param string|null           $appProviderDir 应用服务提供者目录路径（可选）
     *
     * @return void
     */
    public function loadAll(ContainerConfigurator $configurator, string $namespaceBase, ?string $appProviderDir = null): void
    {
        // 1️⃣ 核心 Provider（通过 Composer 自动映射）
        $coreNamespace = 'Framework\Providers\\';
        $corePath      = $this->getComposerNamespacePath($coreNamespace);

        if ($corePath && is_dir($corePath)) {
            $this->loadFromDirectory($configurator, $corePath, $coreNamespace);
        }

        // 2️⃣ 应用 Provider（可选）
        if ($appProviderDir && is_dir($appProviderDir)) {
            $this->loadFromDirectory($configurator, $appProviderDir, $namespaceBase);
        }
    }

    /**
     * 根据目录扫描并注册服务提供者
     *
     * 递归遍历指定目录，查找所有以 "Provider.php" 结尾的文件，
     * 解析类名并注册到容器中。
     *
     * @param ContainerConfigurator $configurator  容器配置器
     * @param string                $directory     要扫描的目录路径
     * @param string                $namespaceBase 命名空间基础（如 'Framework\Providers\'）
     *
     * @return void
     */
    public function loadFromDirectory(ContainerConfigurator $configurator, string $directory, string $namespaceBase): void
    {
        if (! is_dir($directory)) {
            // 目录不存在直接跳过
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && preg_match('/Provider\.php$/i', $file->getFilename())) {
                // 解析类名
                $className = $this->resolveClassName($file->getRealPath(), $directory, $namespaceBase);

                if (! class_exists($className)) {
                    // 不再 require_once，Composer autoload 会自动加载
                    continue;
                }

                $this->registerProvider($configurator, $className);
            }
        }
    }

    /**
     * 注册单个服务提供者
     *
     * 验证服务提供者是否实现了 ServiceProviderInterface 接口，
     * 然后调用其 register() 方法注册服务定义。
     * 防止重复注册同一服务提供者。
     *
     * @param ContainerConfigurator $configurator 容器配置器
     * @param string                $className    服务提供者的完整类名
     *
     * @return void
     */
    public function registerProvider(ContainerConfigurator $configurator, string $className): void
    {
        // 防止重复注册
        foreach ($this->loadedProviders as $p) {
            if (get_class($p) === $className) {
                return;
            }
        }

        $ref = new \ReflectionClass($className);
        if (! $ref->implementsInterface(ServiceProviderInterface::class)) {
            return;
        }

        /** @var ServiceProviderInterface $provider */
        $provider = $ref->newInstance();

        // 调用 register 方法
        if (method_exists($provider, 'register')) {
            $provider->register($configurator);
        }

        $this->loadedProviders[] = $provider;
    }

    /**
     * 启动所有服务提供者的 boot 方法
     *
     * 在容器编译后执行，用于初始化服务逻辑。
     * 如果容器是 ContainerConfigurator，则推迟 boot 到 ContainerBuilder 阶段。
     *
     * @param ContainerBuilder|ContainerConfigurator|\Framework\Container\Container $container 容器实例
     *
     * @return void
     */
    public function bootProviders($container): void
    {
        foreach ($this->loadedProviders as $provider) {
            // 如果是 ContainerConfigurator，则推迟 boot
            if ($container instanceof ContainerConfigurator) {
                // 暂存，等待 ContainerBuilder 阶段再执行
                $this->pendingBoot[] = $provider;
                continue;
            }

            // 真正 boot（Builder 阶段）
            if ($container instanceof ContainerBuilder) {
                $provider->boot($container);
            }
        }
    }

    /**
     * 自动获取 Composer 的 PSR-4 映射路径
     *
     * 解析 Composer 自动加载配置，获取指定命名空间对应的目录路径。
     *
     * @param string $namespace 命名空间（如 'Framework\Providers\'）
     *
     * @return string|null 对应的目录路径，如果未找到返回 null
     */
    protected function getComposerNamespacePath(string $namespace): ?string
    {
        $autoloadFile = BASE_PATH . '/vendor/composer/autoload_psr4.php';
        if (! file_exists($autoloadFile)) {
            return null;
        }

        $map = require $autoloadFile;

        $namespace = trim($namespace, '\\') . '\\';

        foreach ($map as $nsPrefix => $paths) {
            if (str_starts_with($namespace, $nsPrefix)) {
                $path = is_array($paths) ? $paths[0] : $paths;
                return rtrim($path, '/');
            }
        }

        return null;
    }

    /**
     * 根据文件路径解析命名空间类名
     *
     * 将文件系统路径转换为完整的类名（FQCN）。
     * 支持子目录结构，健壮地处理各种路径格式。
     *
     * @param string $filePath      文件完整路径（realpath）
     * @param string $baseDir       传入的扫描目录（例如 .../framework/Providers 或 .../app/Providers）
     * @param string $namespaceBase 命名空间基准（例如 'Framework\\Providers\\' 或 'App\\Providers\\'）
     *
     * @return string 完整类名（FQCN）
     */
    protected function resolveClassName(string $filePath, string $baseDir, string $namespaceBase): string
    {
        // 标准化目录分隔符
        $baseDir  = rtrim(str_replace('\\', '/', $baseDir), '/');
        $filePath = str_replace('\\', '/', $filePath);

        // 规范 namespaceBase（以单个反斜杠结尾，去多余）
        $namespaceBase = trim($namespaceBase, '\\') . '\\';

        // 取出 namespaceBase 最后的段（例如 'Providers'）
        $nsParts     = explode('\\', trim($namespaceBase, '\\'));
        $lastSegment = end($nsParts);

        // 试图在文件路径中找到最后那部分（/Providers/），以此作为相对路径起点（更可靠）
        $needle = '/' . $lastSegment . '/';
        $pos    = stripos($filePath, $needle);

        if ($pos !== false) {
            // 从该段开始取相对部分（去掉前导 slash）
            $relative = ltrim(substr($filePath, $pos + 1), '/');
        } else {
            // 回退：把 baseDir 从 filePath 中移除（你原来的方法）
            $relative = ltrim(str_replace($baseDir, '', $filePath), '/');
        }

        // 转换为命名空间的反斜杠并去掉 .php
        $relative = str_replace('/', '\\', $relative);
        $relative = preg_replace('/\.php$/i', '', $relative);

        // 如果 relative 以 lastSegment 开头（例如 Providers\...），则不要再次拼接 namespaceBase 中的 Providers
        $prefix = $lastSegment . '\\';
        if (str_starts_with($relative, $prefix)) {
            // 去掉开头的 lastSegment\
            $relative = preg_replace('/^' . preg_quote($prefix, '/') . '/i', '', $relative);
            $fqcn     = trim($namespaceBase . $relative, '\\');
        } else {
            $fqcn = trim($namespaceBase . $relative, '\\');
        }

        return $fqcn;
    }
}
