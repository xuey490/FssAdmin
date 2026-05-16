<?php

declare(strict_types=1);

/**
 * Redis监控控制器
 *
 * @package App\Controllers\System
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers\System;

use App\Services\RedisMonitorService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * RedisController Redis监控控制器
 */
class RedisController extends BaseController
{
    /**
     * Redis监控服务
     * @var RedisMonitorService
     */
    protected RedisMonitorService $redisService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->redisService = new RedisMonitorService();
    }

    /**
     * 获取完整Redis信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/redis/full', methods: ['GET'], name: 'redis.full')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function full(Request $request): BaseJsonResponse
    {
        $result = $this->redisService->getFullInfo();
        return $this->success($result);
    }

    /**
     * 检查连接状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/redis/status', methods: ['GET'], name: 'redis.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function status(Request $request): BaseJsonResponse
    {
        $connected = $this->redisService->isConnected();
        return $this->success([
            'connected' => $connected,
            'status' => $connected ? 'online' : 'offline',
        ]);
    }

    /**
     * 获取服务器信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/redis/server', methods: ['GET'], name: 'redis.server')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function server(Request $request): BaseJsonResponse
    {
        $result = $this->redisService->getServerInfo();
        return $this->success($result);
    }

    /**
     * 获取内存信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/redis/memory', methods: ['GET'], name: 'redis.memory')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function memory(Request $request): BaseJsonResponse
    {
        $result = $this->redisService->getMemoryInfo();
        return $this->success($result);
    }

    /**
     * 获取统计信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/redis/stats', methods: ['GET'], name: 'redis.stats')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function stats(Request $request): BaseJsonResponse
    {
        $result = $this->redisService->getStatsInfo();
        return $this->success($result);
    }

    /**
     * 获取命令统计
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/redis/commands', methods: ['GET'], name: 'redis.commands')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function commands(Request $request): BaseJsonResponse
    {
        $result = $this->redisService->getCommandStats();
        return $this->success($result);
    }

    /**
     * 清空所有缓存
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/redis/flush', methods: ['POST'], name: 'redis.flush')]
    #[Auth(required: true, roles: ['super_admin'])]
    public function flush(Request $request): BaseJsonResponse
    {
        $result = $this->redisService->flushAll();
        return $result
            ? $this->success([], '缓存已清空')
            : $this->fail('清空缓存失败');
    }
}
