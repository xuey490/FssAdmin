<?php
declare(strict_types=1);

namespace Framework\Repository\Exceptions;

use RuntimeException;

/**
 * 数据库操作统一异常类
 */
class DatabaseException extends RuntimeException
{
    // 可以根据需要添加错误码常量
    public const ERROR_CREATE = 1001;
    public const ERROR_UPDATE = 1002;
    public const ERROR_DELETE = 1003;
    public const ERROR_QUERY = 1004;

    public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    // 静态工厂方法，方便创建特定类型的异常
    public static function createFailed(string $reason, \Throwable $previous = null): self
    {
        return new self("创建记录失败: {$reason}", self::ERROR_CREATE, $previous);
    }

    public static function updateFailed(string $reason, \Throwable $previous = null): self
    {
        return new self("更新记录失败: {$reason}", self::ERROR_UPDATE, $previous);
    }

    public static function deleteFailed(string $reason, \Throwable $previous = null): self
    {
        return new self("删除记录失败: {$reason}", self::ERROR_DELETE, $previous);
    }

    public static function queryFailed(string $reason, \Throwable $previous = null): self
    {
        return new self("查询记录失败: {$reason}", self::ERROR_QUERY, $previous);
    }
}