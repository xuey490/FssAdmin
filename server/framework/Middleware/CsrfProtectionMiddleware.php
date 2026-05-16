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
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CsrfProtectionMiddleware
{
    public function __construct(
        private CsrfTokenManager $tokenManager,

        /**
         * CSRF Token 参数名
         * - 表单提交：_token
         * - SPA/AJAX：Header X-CSRF-TOKEN / X-XSRF-TOKEN
         */
        private string $tokenName = '_token',

        /**
         * 不进行 CSRF 校验的路径（支持通配符）
         */
        private array $except = [],

        /**
         * 校验失败时的错误提示
         */
        private string $errorMessage = 'Invalid CSRF token.',

        /**
         * ❗❗❗ 重要说明 ❗❗❗
         *
         * 在 SPA / AJAX 场景下：
         * - CSRF Token 必须是「会话级别稳定值」
         * - 禁止使用「用后即删」策略
         *
         * removeAfterValidation 只适用于：
         * - 非 SPA
         * - 单请求、强顺序流程
         *
         * 因此这里强制默认为 false
         */
        private bool $removeAfterValidation = false
    ) {}

    public function handle(Request $request, callable $next): Response
    {
        // 安全方法直接放行
        if (in_array($request->getMethod(), ['HEAD', 'OPTIONS', 'TRACE'], true)) {
            return $next($request);
        }

        // GET 请求不做 CSRF 校验
        if ($request->getMethod() === 'GET') {
            return $next($request);
        }

        // 排除路径
        foreach ($this->except as $pattern) {
            if ($this->matchPath($request->getPathInfo(), $pattern)) {
                return $next($request);
            }
        }

        /**
         * CSRF Token 获取优先级说明：
         *
         * 1. 表单字段（传统表单）
         * 2. Header: X-CSRF-TOKEN（推荐）
         * 3. Header: X-XSRF-TOKEN（兼容）
         * 4. Cookie: XSRF-TOKEN（兜底，不推荐）
         */
        $token =
            $request->request->get($this->tokenName)
            ?? $request->headers->get('X-CSRF-TOKEN')
            ?? $request->headers->get('X-XSRF-TOKEN')
            ?? $request->cookies->get('XSRF-TOKEN');

        if (! is_string($token) || ! $this->tokenManager->isTokenValid('default', $token)) {

            /**
             * ❗ 校验失败时，不要删除 Session 中的 CSRF Token
             * 删除 Token 会导致并发请求全部失败
             */

            if ($this->isAjaxRequest($request)) {
				if ($this->isAjaxRequest($request)) {
					return BaseJsonResponse::fail(
						$this->errorMessage ?: 'CSRF token mismatch.',
						419
					);
				}
            }

			return new Response(
				view('errors/csrf_error.html.twig', [
					'status_code' => 403,
					'status_text' => 'Forbidden',
					'message'     => $this->errorMessage ?: 'CSRF token mismatch.',
				]),
				403
			);
        }

        /**
         * ❗ 校验成功后：
         * 在 SPA 场景下【绝对不要】删除 CSRF Token
         *
         * Token 的生命周期应由：
         * - Logout
         * - Session 失效
         * - Refresh Token 成功
         * 统一管理
         */
        if ($this->removeAfterValidation) {
            $this->tokenManager->removeToken('default');
        }

        return $next($request);
    }

    /**
     * 判断是否为 AJAX / SPA 请求
     */
    private function isAjaxRequest(Request $request): bool
    {
        if (strtolower((string) $request->headers->get('X-Requested-With')) === 'xmlhttprequest') {
            return true;
        }

        $accept = strtolower((string) $request->headers->get('Accept', ''));
        if (str_contains($accept, 'application/json')) {
            return true;
        }

        $contentType = strtolower((string) $request->headers->get('Content-Type', ''));
        if (str_contains($contentType, 'application/json')) {
            return true;
        }

        return false;
    }

    private function matchPath(string $path, string $pattern): bool
    {
        $regex = str_replace('\*', '.*', preg_quote($pattern, '#'));
        return (bool) preg_match('#^' . $regex . '$#', $path);
    }
}
