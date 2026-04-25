<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 */

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieConsentMiddleware implements MiddlewareInterface
{
    // API 路径通常不需要处理，静态资源也应跳过
    private array $excludedPaths = ['/api/*']; 

    public function handle(Request $request, callable $next): Response
    {
        // 1. 如果是 API 请求或匹配排除路径，直接放行
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $response = $next($request);

        // 2. 核心修复：确保 response 是 Response 对象
        // 并且 Content-Type 必须包含 text/html (兼容 'text/html; charset=UTF-8')
        $contentType = $response->headers->get('Content-Type', 'text/html');
        
        // 【错误修复点】：原代码逻辑是 "如果是 HTML 则跳过"，这里改为 "如果不是 HTML 则跳过"
        if (! $response instanceof Response || strpos($contentType, 'text/html') === false) {
            return $response;
        }

        // 3. 检查用户 Cookie 是否已同意
        // 注意：前端 JS 设置的 cookie 能够在这里被读取
        $hasConsented = $request->cookies->get('cookie_consent') === 'accepted';

        if (! $hasConsented) {
            $bannerHtml = $this->renderConsentBanner();
            $content    = $response->getContent();

            // 4. 将 HTML 注入到 </body> 标签之前
            // 使用 str_ireplace 大小写不敏感替换，比正则稍微快一点且不容易出错
            $pos = strripos($content, '</body>');
            if ($pos !== false) {
                $content = substr_replace($content, $bannerHtml . '</body>', $pos, 7);
                $response->setContent($content);
            }
        }

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        // 如果是 AJAX 请求 (XHR) 通常也不显示 banner
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        foreach ($this->excludedPaths as $path) {
            if (fnmatch($path, $request->getPathInfo())) {
                return true;
            }
        }
        return false;
    }

    private function renderConsentBanner(): string
    {
        // 优化点：
        // 1. 使用纯 JS 设置 Cookie (document.cookie)，无需发请求到服务器。
        // 2. max-age=31536000 代表一年有效期。
        // 3. 增加了淡出动画效果。
        return <<<'HTML'
			<style>
				#cookie-banner {
					position: fixed;
					bottom: 0;
					left: 0;
					width: 100%;
					background-color: #2a2a2a;
					color: #fff;
					padding: 15px;
					text-align: center;
					z-index: 9999;
					box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
					font-family: sans-serif;
					transition: opacity 0.5s ease;
				}
				#cookie-banner a {
					color: #4dd;
					text-decoration: underline;
				}
				#cookie-banner button {
					margin-left: 15px;
					background-color: #0d8;
					color: white;
					border: none;
					padding: 8px 20px;
					border-radius: 4px;
					cursor: pointer;
					font-weight: bold;
				}
				#cookie-banner button:hover {
					background-color: #0b7;
				}
			</style>
			<div id="cookie-banner">
				我们使用 Cookie 来优化您的浏览体验。
				<a href="/privacy" target="_blank">隐私政策</a>
				<button onclick="acceptCookies()">我同意</button>
			</div>
			<script>
			function acceptCookies() {
				// 1. 设置 Cookie (有效期1年, 根路径)
				document.cookie = "cookie_consent=accepted; path=/; max-age=31536000; SameSite=Lax";
				
				// 2. 隐藏 Banner
				var banner = document.getElementById('cookie-banner');
				if (banner) {
					banner.style.opacity = '0';
					setTimeout(function() {
						banner.remove();
					}, 500);
				}
			}
			</script>
			HTML;
    }
}