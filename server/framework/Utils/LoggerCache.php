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

namespace Framework\Utils;

/*
快速测试日志类
*/
class LoggerCache
{
    protected string $channel;

    protected string $logFile;

    public function __construct(string $channel = 'app', string $logFile = BASE_PATH . '/storage/app.log')
    {
        $this->channel = $channel;
        $this->logFile = $logFile;
    }

    public function log(string $message): void
    {
        file_put_contents($this->logFile, '[' . $this->channel . '] ' . $message . PHP_EOL, FILE_APPEND);
    }
}
