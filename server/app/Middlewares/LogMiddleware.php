<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: LogMiddleware.php
 * @Date: 2025-12-17
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Middlewares;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Log; // 引入注解类以便获取反射信息(如果有必要)

class LogMiddleware
{
    /**
     * 处理请求
     * 
     * @param Request $request Symfony Request 对象
     * @param callable $next 下一步处理
     * @return Response Symfony Response 对象
     */
    public function handle(Request $request, callable $next): Response
    {
		//dump($request);
			
        // 1. 获取所有业务注解
        $attributes = $request->attributes->get('_attributes', []);
		
		$authAttr = $attributes[Log::class] ?? null;
		
		
		$level = $authAttr->level ?? 'info';
		
        // 1. 记录开始时间
        $startTime = microtime(true);

        // 2. 执行后续业务逻辑，获取响应对象
        /** @var Response $response */
        $response = $next($request);

        // 3. 计算耗时 (毫秒)
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // 4. 获取用户代理字符串
        $userAgent = $request->headers->get('User-Agent', '');

        // 5. 组装日志上下文数据
        $logContext = [
            'ip'          => $request->getClientIp(), // Symfony 自动处理代理头
            'method'      => $request->getMethod(),
            'uri'         => $request->getPathInfo(),
            'os'          => $this->getOS($userAgent),
            'browser'     => $this->getBrowser($userAgent),
            'duration'    => $duration . 'ms',
            'code' 		  => $response->getStatusCode(),
            'params'      => $this->getFilteredParams($request), // 获取并脱敏参数
        ];

        // 6. 构造日志消息
        // 如果你的路由层将 Attribute 实例注入到了 request attributes 中，
        // 可以通过 $request->attributes->get(Log::class) 获取 description
        // 这里默认使用请求路径作为消息标题
        $message = sprintf('Access Log: %s', $request->getPathInfo());

        // 7. 调用日志助手记录
        // 假设 Log 注解可以通过某种方式传递 level，这里默认用 info
        // 如果想动态调用，可以使用 app('log')->{$level}($message, $logContext)
        //app('log')->info($message, $logContext);
		app('log')->{$level}($message, $logContext);

        return $response;
    }

    /**
     * 获取并过滤请求参数 (支持 Query, Post, Json)
     */
    private function getFilteredParams(Request $request): array
    {
        // 获取所有参数 (Query + Request)
        $params = $request->query->all() + $request->request->all();

        // 处理 JSON 请求体 (Symfony Request 不会自动合并 JSON 到 request 属性中，除非特定配置)
        if ($request->getContentTypeFormat() === 'json') {
            try {
                $json = $request->toArray(); // Symfony 方法，自动解析 JSON
                $params = array_merge($params, $json);
            } catch (\Exception $e) {
                // JSON 解析失败忽略
            }
        }

        // 敏感字段脱敏
        $sensitiveKeys = ['password', 'pwd', 'secret', 'token', 'authorization', 'card_number'];
        
        foreach ($params as $key => &$value) {
            if (in_array(strtolower((string)$key), $sensitiveKeys)) {
                $value = '******';
            }
        }

        return $params;
    }

    /**
     * 解析操作系统
     */
    private function getOS(string $userAgent): string
    {
        if (empty($userAgent)) return 'Unknown';

        return match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS')  => 'Mac OS',
            str_contains($userAgent, 'Linux')   => 'Linux',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            default => 'Other OS'
        };
    }

    /**
     * 解析浏览器
     */
    private function getBrowser(string $userAgent): string
    {
        if (empty($userAgent)) return 'Unknown';

        // 注意顺序：Edge/Chrome 包含 Chrome 字符串，所以 Chrome 放后面判断
        if (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/')) return 'Internet Explorer';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Edg'))     return 'Edge';
        if (str_contains($userAgent, 'Chrome'))  return 'Chrome';
        if (str_contains($userAgent, 'Safari'))  return 'Safari';
        if (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR')) return 'Opera';

        // 简单的兜底逻辑
        if (str_contains($userAgent, 'Bot') || str_contains($userAgent, 'Spider')) return 'Robot/Spider';
        if (str_contains($userAgent, 'Postman')) return 'Postman';
        if (str_contains($userAgent, 'curl'))    return 'cURL';

        return 'Other Browser';
    }
}
