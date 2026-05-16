<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Kernel.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use ErrorException;
use Framework\Core\Exception\Handler as ExceptionHandler;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

class Kernel
{
    private ContainerInterface $container;

    private bool $booted = false;

    public function __construct(ContainerInterface $container)
    {
        // 确保容器是可编译的（Symfony ContainerBuilder）
        if (! $container instanceof ContainerInterface) {
            throw new InvalidArgumentException('容器必须是 ContainerInterface 实例');
        }
        $this->container = $container;
    }

    /**
     * 启动内核：初始化核心服务、注册事件、设置异常处理.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return; // 防止重复启动
        }

        $this->setupContainerAlias();

        $this->setupTimezone();

        $this->setupExceptionHandling();

        $this->booted = true;
    }

    /**
     * 检查内核是否已启动.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    // 1. 设置全局容器入口（供助手函数使用）
    private function setupContainerAlias(): void
    {
        App::setContainer($this->container);
    }

    // 2. 初始化时区（从配置获取）
    private function setupTimezone(): void
    {
        $timezone = (string) config('app.time_zone', 'UTC');
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = 'UTC';
        }
        date_default_timezone_set($timezone);
    }

    /**
     * 4.设置异常处理机制（统一接管错误与异常）.
     */
    private function setupExceptionHandling(): void
    {
        // 1. 注册异常处理器
        $exceptionHandler = $this->container->get(ExceptionHandler::class);

        set_exception_handler(function (Throwable $e) use ($exceptionHandler) {
            $exceptionHandler->report($e);
            $exceptionHandler->render($e); // ->send();
            exit(1); // 异常后终止程序
        });

        // 2. 注册错误处理器（将错误转为异常）
        set_error_handler(function ($severity, $message, $file, $line) {
            // 忽略非用户级错误（如E_STRICT）
            if (! (error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        // 3. 注册致命错误处理器
        register_shutdown_function(function () use ($exceptionHandler) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $e = new ErrorException(
                    $error['message'] ?? '致命错误',
                    0,
                    $error['type'] ?? E_ERROR,
                    $error['file'] ?? '未知文件',
                    $error['line'] ?? 0
                );
                $exceptionHandler->report($e);
                exit(1);
            }
        });
    }
}
