<?php

declare(strict_types=1);

namespace App\Middlewares;

use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestEnvWriteGuardMiddleware
{
    /**
     * 拦截写操作，仅在测试环境生效。
     *
     * 支持配置（config/middleware.php）：
     * middleware.test_env_write_guard.enabled
     * middleware.test_env_write_guard.only_envs
     * middleware.test_env_write_guard.block_methods
     * middleware.test_env_write_guard.whitelist
     */
    public function handle(Request $request, callable $next): Response
    {
        if (!$this->shouldGuard($request)) {
            return $next($request);
        }

        return BaseJsonResponse::fail('测试环境已禁止写操作', 403);
    }

    protected function shouldGuard(Request $request): bool
    {
        $method = strtoupper((string) $request->getMethod());
        if ($method === 'OPTIONS') {
            return false;
        }

        $cfg = config('middleware.test_env_write_guard', []);
        $enabled = (bool) ($cfg['enabled'] ?? true);
        if (!$enabled) {
            return false;
        }

        $currentEnv = (string) (env('APP_ENV') ?: 'local');
        $onlyEnvs = $cfg['only_envs'] ?? ['test', 'testing'];
        if (!in_array($currentEnv, $onlyEnvs, true)) {
            //return false;
        }

        $blockMethods = array_map('strtoupper', $cfg['block_methods'] ?? ['POST', 'PUT', 'PATCH', 'DELETE']);
        if (!in_array($method, $blockMethods, true)) {
            return false;
        }

        return !$this->isWhitelistedPath((string) $request->getPathInfo(), (array) ($cfg['whitelist'] ?? []));
    }

    protected function isWhitelistedPath(string $path, array $whitelist): bool
    {
        foreach ($whitelist as $rule) {
            $rule = (string) $rule;
            if ($rule === '') {
                continue;
            }

            if (str_ends_with($rule, '*')) {
                $prefix = rtrim($rule, '*');
                if ($prefix === '' || str_starts_with($path, $prefix)) {
                    return true;
                }
                continue;
            }

            if ($path === $rule || str_starts_with($path, rtrim($rule, '/').'/')) {
                return true;
            }
        }

        return false;
    }
}
