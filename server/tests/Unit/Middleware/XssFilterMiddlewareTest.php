<?php

namespace Tests\Unit\Middleware;

use PHPUnit\Framework\TestCase;
use Framework\Middleware\XssFilterMiddleware;

class XssFilterMiddlewareTest extends TestCase
{
    public function test_example(): void
    {
        $this->assertTrue(true);
    }
}
