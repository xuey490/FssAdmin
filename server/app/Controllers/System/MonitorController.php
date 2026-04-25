<?php

declare(strict_types=1);

/**
 * 服务器监控控制器
 *
 * @package App\Controllers\System
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers\System;

use App\Services\ServerMonitorService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * MonitorController 服务器监控控制器
 */
class MonitorController extends BaseController
{
    /**
     * 服务器监控服务
     * @var ServerMonitorService
     */
    protected ServerMonitorService $monitorService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->monitorService = new ServerMonitorService();
    }

    /**
     * 获取完整监控信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/monitor/full', methods: ['GET'], name: 'monitor.full')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function full(Request $request): BaseJsonResponse
    {
        $result = $this->monitorService->getFullInfo();
        return $this->success($result);
    }

    /**
     * 获取服务器信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/monitor/server', methods: ['GET'], name: 'monitor.server')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function server(Request $request): BaseJsonResponse
    {
        $result = $this->monitorService->getServerInfo();
        return $this->success($result);
    }

    /**
     * 获取PHP信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/monitor/php', methods: ['GET'], name: 'monitor.php')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function php(Request $request): BaseJsonResponse
    {
        $result = $this->monitorService->getPhpInfo();
        return $this->success($result);
    }

    /**
     * 获取CPU信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/monitor/cpu', methods: ['GET'], name: 'monitor.cpu')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function cpu(Request $request): BaseJsonResponse
    {
        $result = $this->monitorService->getCpuInfo();
        return $this->success($result);
    }

    /**
     * 获取内存信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/monitor/memory', methods: ['GET'], name: 'monitor.memory')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function memory(Request $request): BaseJsonResponse
    {
        $result = $this->monitorService->getMemoryInfo();
        return $this->success($result);
    }

    /**
     * 获取磁盘信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/monitor/disk', methods: ['GET'], name: 'monitor.disk')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function disk(Request $request): BaseJsonResponse
    {
        $result = $this->monitorService->getDiskInfo();
        return $this->success($result);
    }
}
