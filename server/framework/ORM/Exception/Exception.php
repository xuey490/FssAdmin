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

namespace Framework\ORM\Exception;

/**
 * ORM异常类
 * 
 * 用于ORM模块的自定义异常处理，继承自RuntimeException。
 * 提供统一的异常错误码和消息格式。
 */
class Exception extends \RuntimeException
{
    /**
     * 构造函数
     * 
     * 初始化ORM异常实例，设置异常消息、错误码和前一个异常。
     * 
     * @param string         $message  异常消息内容
     * @param int            $code     错误码，默认为400
     * @param \Throwable|null $previous 前一个异常实例，用于异常链追踪
     */
    public function __construct($message, $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
