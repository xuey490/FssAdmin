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

namespace Framework\Validation;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(
        public mixed $errors, 
        string $message = "校验失败", 
        int $code = 422
    ) {
        parent::__construct($message, $code);
    }
}