<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\DI\ContextBag;
use Framework\Tenant\TenantContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ContextInitMiddleware implements MiddlewareInterface
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        // 🔥 核心：初始化 TenantContext 的 RequestStack（仅需一次）
        TenantContext::setRequestStack($this->requestStack);
    }

    public function handle(Request $request, callable $next): Response
    {
        // 🔥 核心：将当前请求 push 到 RequestStack（Workerman/Swoole 必需）
        $this->requestStack->push($request);

        // 把 request 放入上下文容器，key 必须和注解里的 Context('request') 一致
        ContextBag::set('request', $request);

        try {
            return $next($request);
        } finally {
            // 🔥 请求结束后清理（Workerman/Swoole 常驻进程必须清理）
            // 1. 从 RequestStack 中弹出当前请求
            $this->requestStack->pop();

            // 2. 清理 TenantContext 租户上下文
            TenantContext::clear();

            // 3. 清理 ContextBag
            ContextBag::clear();
        }
    }
}