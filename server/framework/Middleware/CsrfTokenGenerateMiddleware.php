<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use Framework\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CSRF Token 生成 / 同步中间件（SPA 友好版）
 *
 * 设计说明（非常重要）：
 *
 * 1. CSRF Token 是「会话级别」的安全标识，而不是一次性验证码
 * 2. 在 SPA 场景下，Token 必须在 Session 生命周期内保持稳定
 * 3. 本中间件【只负责】：
 *    - 在 Session 中生成 Token（若不存在）
 *    - 将 Token 同步到 Cookie（XSRF-TOKEN）
 *
 * 4. 本中间件【不负责】：
 *    - 校验 CSRF（应由独立的 Verify 中间件完成）
 *    - 每次请求刷新 Token（禁止！会导致并发请求失败）
 *
 * 前后端 CSRF 工作流：
 *
 * 请求前：
 *   - 后端生成 CSRF Token（仅首次）
 *   - 写入 Cookie：XSRF-TOKEN（非 HttpOnly）
 *   - Axios 从 Cookie 读取，并写入 Header：X-CSRF-TOKEN
 *
 * 请求中：
 *   - 后端校验 Header 中的 X-CSRF-TOKEN
 *   - 与 Session 中的 csrf_token 比对
 *
 * 失效时：
 *   - 返回 419（或 403）
 *   - 前端可选择重新拉取 Token 或引导重新登录
 */
class CsrfTokenGenerateMiddleware
{
    public function __construct(
        private CsrfTokenManager $tokenManager,
        private string $cookieName = 'XSRF-TOKEN',
        private string $tokenId = 'default'
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        $path   = $request->getPathInfo();
        $method = strtoupper($request->getMethod());

        /**
         * ❗ 特殊接口跳过 CSRF 生成
         * 例如：
         * - /login/getCsrfToken
         * - /auth/login
         *
         * 这些接口通常用于“尚未建立 Session”的阶段
         */
        if ($path === '/login/getCsrfToken' || $path === '/login/captcha' || $path === '/login/getCaptchaOpenFlag'  ) {
            return $next($request);
        }

        /**
         * 1️⃣ 从 Session 中获取 CSRF Token
         * - 若已存在：直接返回（不会刷新）
         * - 若不存在：生成并写入 Session
         */
        $token = $this->tokenManager->getToken($this->tokenId);

        /** @var Response $response */
        $response = $next($request);

        /**
         * 2️⃣ 同步 Token 到 Cookie
         *
         * 设计原则：
         * - Cookie 只是「前端读取通道」
         * - Session 才是 Token 的唯一可信来源
         *
         * 仅在以下情况写 Cookie：
         * - Cookie 不存在
         * - Cookie 与 Session Token 不一致
         */
        $cookieToken = $request->cookies->get($this->cookieName);
		
		$request->headers->set('X-CSRF-TOKEN' , $token);

        if ($cookieToken !== $token) {
            $response->headers->setCookie(
                new Cookie(
                    $this->cookieName,
                    $token,
                    0,                  // Session Cookie
                    '/',
                    null,
                    false,              // https 可按需开启
                    false,              // ❗ 必须允许 JS 读取
                    false,
                    Cookie::SAMESITE_LAX
                )
            );
        }

        return $response;
    }
}
