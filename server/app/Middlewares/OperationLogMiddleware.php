<?php

declare(strict_types=1);

/**
 * 操作日志中间件
 * 记录所有非白名单的 POST/PUT/PATCH/DELETE 请求
 *
 * @package App\Middlewares
 */

namespace App\Middlewares;

use App\Models\SysOperationLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OperationLogMiddleware
{
    /**
     * 白名单路径前缀（不记录操作日志）
     */
    protected array $whitelist = [
        '/api/core/login',
        '/api/core/logout',
        '/api/core/refresh',
        '/api/core/captcha',
        '/api/core/logs/',
        '/api/system/monitor',
        '/api/system/redis',
        '/api/system/database',
    ];

    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);
        $response  = $next($request);

        // 只记录写操作
        $method = strtoupper($request->getMethod());
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        // 白名单跳过
        $path = $request->getPathInfo();
        foreach ($this->whitelist as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $response;
            }
        }
		$duration = round((microtime(true) - $startTime) * 1000, 2);
        $this->writeLog($request, $response, $duration);

        return $response;
    }

    protected function writeLog(Request $request, Response $response, $duration): void
    {
        try {
            $user      = $request->attributes->get('user', []);
            $userAgent = $request->headers->get('User-Agent', '');
            $ip        = $request->getClientIp() ?? '';

            // 获取请求数据（合并 POST body 和 JSON body）
            $params = $request->request->all();
            if (empty($params)) {
                $raw = $request->getContent();
                if (!empty($raw)) {
                    $decoded = json_decode($raw, true);
                    $params  = is_array($decoded) ? $decoded : [];
                }
            }
            // 脱敏：移除密码字段
            unset($params['password'], $params['old_password'], $params['new_password']);
            $requestData = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (strlen($requestData) > 2000) {
                $requestData = substr($requestData, 0, 2000) . '...';
            }

            // 解析路由名称作为 service_name
            $routeInfo   = $request->attributes->get('_route');
            $routeName   = '';
            if (is_array($routeInfo)) {
                $routeName = $routeInfo['params']['_route_name'] ?? ($routeInfo['name'] ?? '');
            } elseif (is_string($routeInfo)) {
                $routeName = $routeInfo;
            }

            SysOperationLog::record([
                'username'     => $user['username'] ?? ($user['name'] ?? ''),
                'app'          => 'system',
                'method'       => strtoupper($request->getMethod()),
                'router'       => $request->getPathInfo(),
                'service_name' => $routeName,
                'ip'           => $ip,
                'ip_location'  => '',
                'request_data' => $requestData,
                'created_by'   => $user['id'] ?? 0,
				'duration'	   => $duration,
            ]);
        } catch (\Throwable $e) {
            // 日志写入失败不影响主流程，开发环境下输出错误
            if (env('APP_DEBUG', false)) {
                error_log('[OperationLogMiddleware] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }
        }
    }
}
