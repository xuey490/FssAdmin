<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: UserActionMiddleware.php
 * @Date: 2025-12-17
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\UserAction;

class UserActionMiddleware
{
    /**
     * 处理用户行为日志
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // 1. 先执行控制器逻辑 (先拿到结果)
        /** @var Response $response */
        $response = $next($request);

        // 2. 只有当业务逻辑执行成功 (HTTP 200-299) 时才记录日志
        // 如果注册失败或登录密码错误（返回4xx），通常不需要记入“成功操作表”
        if (!$response->isSuccessful()) {
            return $response;
        }

        try {
            $this->logAction($request, $response);
        } catch (\Throwable $e) {
            // ⚠️ 极其重要：日志记录失败绝不能影响主业务返回
            // 可以在这里记录系统错误日志 file_put_contents(...)
        }

        return $response;
    }

    /**
     * 执行日志写入逻辑
     */
    private function logAction(Request $request, Response $response): void
    {
        // 获取注解配置
		$attributes = $request->attributes->get('_attributes', []);
		
		$authAttr = $attributes[UserAction::class] ?? null;
	
		if ($authAttr->type == null ) return;
		
		$type = $authAttr->type;

        // --- 核心：尝试获取用户ID ---
        $userId = 111;

        // 策略1：尝试从 Auth 系统获取（适用于 Login 成功后，Session 已建立）
        // if (app('auth')->check()) { $userId = app('auth')->id(); }
        
        // 策略2：如果没登录（比如注册接口），尝试从 Response JSON 中解析
        // 假设你的接口返回格式是: { "code": 200, "data": { "user_id": 123, ... } }
        if (!$userId && $response->headers->contains('Content-Type', 'application/json')) {
            $content = json_decode($response->getContent(), true);
            $userId = $content['data']['user_id'] 
                   ?? $content['data']['id'] 
                   ?? $content['user_id'] 
                   ?? null;
        }

        // 如果最终没找到 ID（可能是匿名操作），设为 0 或 null
        $userId = $userId ?? 0;
		
		/*
        // --- 写入数据库 ---
        // 假设表结构：user_logs (user_id, action, ip, user_agent, created_at)
        app('db')->table('user_logs')->insert([
            'user_id'    => $userId,
            'action'     => $authAttr->type, // 例如 'register' 或 'login'
            'ip'         => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'created_at' => date('Y-m-d H:i:s'),
            // 可选：记录请求参数
            // 'details' => json_encode($request->request->all())
        ]);
		*/
    }
}