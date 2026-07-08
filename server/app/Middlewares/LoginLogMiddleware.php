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
     * @return mixed
     */
    protected IpLocationService $ipLocationService;

    /**
     * 需要记录登录日志的路径
     * @var array<array-key, mixed>
     * @return mixed
     */
    protected array $trackedPaths = [
        '/api/core/login',
    ];

    /**
     * 构造函数
     * @return mixed
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
        // 先执行主流程，避免日志异常影响请求
        $response = $next($request);

        $path = $request->getPathInfo();
        foreach ($this->trackedPaths as $trackedPath) {
            if (str_starts_with($path, $trackedPath)) {
                $this->recordLoginLog($request, $response);
                break;
            }
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
        try {
            $content = json_decode((string) $response->getContent(), true);
            if (!is_array($content)) {
                $content = [];
            }

            $code = (int)($content['code'] ?? $response->getStatusCode());
            $success = in_array($code, [0, 200], true);

            $username = $this->resolveUsername($request, $content);
            $message = (string)($content['message'] ?? ($success ? '登录成功' : '登录失败'));

            $userAgent = $request->headers->get('User-Agent', '');
            $clientInfo = $this->parseUserAgent($userAgent);

            $ip = $this->resolveClientIp($request);
            $location = $this->ipLocationService->getLocation($ip);

            SysLoginLog::record([
                'username' => $username !== '' ? $username : '未知用户',
                'ip' => $ip,
                'ip_location' => $location,
                'os' => $clientInfo['os'],
                'browser' => $clientInfo['browser'],
                'status' => $success ? SysLoginLog::STATUS_SUCCESS : SysLoginLog::STATUS_FAIL,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            app('log')->error('LoginLogMiddleware recordLoginLog failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 解析User-Agent
     *
     * @param string $userAgent User-Agent字符串
     * @return array<array-key, mixed>
     */
    protected function parseUserAgent(string $userAgent): array
    {
        $browser = 'Other';
        $os = 'Other';

        foreach (['Edg', 'Edge', 'Chrome', 'Firefox', 'Safari', 'MSIE', 'Trident'] as $item) {
            if (stripos($userAgent, $item) !== false) {
                if ($item === 'Trident' || $item === 'MSIE') {
                    $browser = 'IE';
                } elseif ($item === 'Edg') {
                    $browser = 'Edge';
                } else {
                    $browser = $item;
                }
                break;
            }
        }

        foreach (['Windows', 'Mac', 'Linux', 'Android', 'iPhone', 'iPad', 'iOS'] as $item) {
            if (stripos($userAgent, $item) !== false) {
                $os = in_array($item, ['iPhone', 'iPad', 'iOS'], true) ? 'iOS' : $item;
                break;
            }
        }

        return [
            'browser' => $browser,
            'os' => $os,
        ];
    }

    /**
     * 解析请求中的用户名
     *
     * @param array<array-key, mixed> $responseBody 响应体
     * @return string
     */
    protected function resolveUsername(Request $request, array $responseBody): string
    {
        $requestBody = $this->extractRequestBody($request);
        $username = (string)($requestBody['username'] ?? '');
        if ($username !== '') {
            return $username;
        }

        $user = $responseBody['data']['user']['username'] ?? null;
        return is_string($user) ? $user : '';
    }

    /**
     * 提取请求体内容
     *
     * @param Request $request 请求对象
     * @return array<array-key, mixed>
     */
    protected function extractRequestBody(Request $request): array
    {
        $body = [];
        $content = $request->getContent();
        if ($content !== '') {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        return array_merge($request->query->all(), $request->request->all(), $body);
    }

    protected function resolveClientIp(Request $request): string
    {
        $candidateIps = [];

        $cfIp = trim((string) $request->headers->get('CF-Connecting-IP', ''));
        if ($cfIp !== '') {
            $candidateIps[] = $cfIp;
        }

        $xForwardedFor = trim((string) $request->headers->get('X-Forwarded-For', ''));
        if ($xForwardedFor !== '') {
            $candidateIps[] = trim(explode(',', $xForwardedFor)[0]);
        }

        $xRealIp = trim((string) $request->headers->get('X-Real-IP', ''));
        if ($xRealIp !== '') {
            $candidateIps[] = $xRealIp;
        }

        $clientIp = trim((string) $request->getClientIp());
        if ($clientIp !== '') {
            $candidateIps[] = $clientIp;
        }

        foreach ($candidateIps as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '0.0.0.0';
    }
}