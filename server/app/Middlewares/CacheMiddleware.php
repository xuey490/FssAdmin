<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: CacheMiddleware.php
 * @Date: 2025-12-17
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Cache;

class CacheMiddleware
{
    /**
     * 处理缓存逻辑
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // 1. 获取 Cache 注解配置
        // 假设 Router 已将注解注入 request attributes
        /** @var Cache|null $cacheAttr */
        //$cacheAttr = $request->attributes->get(Cache::class);
		
        $cacheAttr = $request->attributes->get('_attributes', []); // $request->attributes->get(Role::class);
		
		$attr = $cacheAttr[Cache::class] ?? null;
		
		

        // 如果没有配置注解（理论上不会进来，但为了健壮性）或者是非 GET 请求，通常不缓存
        if (!$attr || !$request->isMethod('GET')) {
            return $next($request);
        }

        // 2. 生成缓存 Key
        // 如果注解没指定 Key，则使用 Request URI (包含查询参数) 的 Hash
        $cacheKey = $attr->key ?: 'route:cache:' . md5($request->getUri());

        // 3. [尝试读取缓存]
        $cachedData = app('cache')->get($cacheKey);



        if ($cachedData) {
            // ✅ 命中缓存：直接构造 Response 返回，不再执行控制器
            // 假设存入的结构是 ['content' => ..., 'headers' => ..., 'status' => ...]
            return new Response(
                $cachedData['content'],
                $cachedData['status'],
                $cachedData['headers']
            );
        }

        // 4. [缓存未命中] 执行后续控制器逻辑
        /** @var Response $response */
        $response = $next($request);

        // 5. 存入缓存
        // 只有成功的响应（200 OK）才缓存，避免缓存报错页面
        if ($response->isSuccessful()) {
            $dataToCache = [
                'content' => $response->getContent(),
                'status'  => $response->getStatusCode(),
                'headers' => $response->headers->all(), // 保存 Content-Type 等
            ];

            app('cache')->set($cacheKey, $dataToCache, $attr->ttl);
            
            // 可选：在响应头里加个标记，调试用
            $response->headers->set('X-Cache-Status', 'MISS');
        }

        return $response;
    }
}