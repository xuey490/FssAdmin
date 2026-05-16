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

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MethodOverrideMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // dump('--- 进入 MiddlewareMethodOverride (中间件) ---');
        // 检查是否是 POST 请求，并且包含 _method 参数
        if ($request->isMethod('POST') && $request->request->has('_method')) {
            $method = strtoupper($request->request->get('_method'));
            // 允许的 HTTP 方法
            $allowedMethods = ['PUT', 'DELETE', 'PATCH'];

            if (in_array($method, $allowedMethods)) {
                // 重写请求方法
                $request->setMethod($method);
                // 从请求参数中移除 _method
                $request->request->remove('_method');
            }
        }

        return $next($request);
        // dump('--- 退出 MiddlewareMethodOverride (中间件) ---');

        // 继续执行下一个中间件或控制器
    }
}
