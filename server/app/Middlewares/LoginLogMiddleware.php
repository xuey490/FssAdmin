<?php

declare(strict_types=1);

/**
 * 登录日志中间件
 *
 * @package App\Middlewares
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Middlewares;

use App\Models\SysLoginLog;
use App\Services\IpLocationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LoginLogMiddleware 登录日志中间件
 */
class LoginLogMiddleware
{
    /**
     * IP地理位置服务
     * @var IpLocationService
     */
    protected IpLocationService $ipLocationService;

    /**
     * 不记录日志的路径
     * @var array
     */
    protected array $exceptPaths = [
        '/api/auth/login',
        '/api/auth/logout',
        '/api/auth/refresh',
    ];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->ipLocationService = new IpLocationService();
    }

    /**
     * 处理请求
     *
     * @param Request  $request 请求对象
     * @param callable $next    下一个处理器
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // 获取请求路径
        $path = $request->getPathInfo();

        // 检查是否在排除列表中
        foreach ($this->exceptPaths as $exceptPath) {
            if (str_starts_with($path, $exceptPath)) {
                return $next($request);
            }
        }

        // 执行请求并获取响应
        $response = $next($request);

        // 记录登录日志 (登录接口)
        if (in_array($path, ['/api/auth/login', '/api/auth/logout'])) {
            $this->recordLoginLog($request, $response);
        }

        return $response;
    }

    /**
     * 记录登录日志
     *
     * @param Request  $request  请求对象
     * @param Response $response 响应对象
     * @return void
     */
    protected function recordLoginLog(Request $request, Response $response): void
    {
        // 获取用户信息
        $user = $request->attributes->get('user');
        $username = $this->input('username', '');
        $userId = $user['id'] ?? 0;

        // 解析响应内容
        $content = json_decode($response->getContent(), true);
        $loginStatus = ($content['code'] ?? 0) === 0 ? 0 : 1;
        $loginMessage = $content['message'] ?? '';

        // 获取客户端信息
        $userAgent = $request->headers->get('User-Agent', '');
        $clientInfo = $this->parseUserAgent($userAgent);

        // 获取IP地理位置
        $ip = $request->getClientIp() ?? '';
        $location = $this->ipLocationService->getLocation($ip);

        // 记录日志
        SysLoginLog::record([
            'user_id' => $userId,
            'username' => $username,
            'login_status' => $loginStatus,
            'login_message' => $loginMessage,
            'login_ip' => $ip,
            'login_location' => $location,
            'browser' => $clientInfo['browser'],
            'os' => $clientInfo['os'],
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * 解析User-Agent
     *
     * @param string $userAgent User-Agent字符串
     * @return array
     */
    protected function parseUserAgent(string $userAgent): array
    {
        $browser = 'Unknown';
        $os = 'Unknown';

        // 解析浏览器
        if (preg_match('/(Chrome|Firefox|Safari|Edge|MSIE|Trident)/i', $userAgent, $matches)) {
            $browser = $matches[1];
        }

        // 解析操作系统
        if (preg_match('/(Windows|Mac|Linux|Android|iOS)/i', $userAgent, $matches)) {
            $os = $matches[1];
        }

        return [
            'browser' => $browser,
            'os' => $os,
        ];
    }
}
