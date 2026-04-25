<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\LoginLogService;
use App\Services\OperationLogService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

class LogController extends BaseController
{
    protected LoginLogService $loginLogService;
    protected OperationLogService $operationLogService;

    protected function initialize(): void
    {
        $this->loginLogService = new LoginLogService();
        $this->operationLogService = new OperationLogService();
    }

    // ==================== 登录日志 ====================

    #[Route(path: '/api/core/logs/getLoginLogPageList', methods: ['GET'], name: 'log.login.list')]
    #[Auth(required: true)]
    #[Permission('core:logs:login')]
    public function getLoginLogPageList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        
        // 兼容 pageSize 和 limit 参数
        if (isset($params['pageSize']) && !isset($params['limit'])) {
            $params['limit'] = $params['pageSize'];
        }
        
        $result = $this->loginLogService->getPageList($params);
        return $this->success($result);
    }

    #[Route(path: '/api/core/logs/deleteLoginLog', methods: ['DELETE'], name: 'log.login.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:logs:deleteLogin')]
    public function deleteLoginLog(Request $request): BaseJsonResponse
    {
        $ids = $this->parseIds($request);
        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        try {
            $count = $this->loginLogService->delete($ids);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 操作日志 ====================

    #[Route(path: '/api/core/logs/getOperLogPageList', methods: ['GET'], name: 'log.operation.list')]
    #[Auth(required: true)]
    #[Permission('core:logs:Oper')]
    public function getOperLogPageList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        
        // 兼容 pageSize 和 limit 参数
        if (isset($params['pageSize']) && !isset($params['limit'])) {
            $params['limit'] = $params['pageSize'];
        }
        
        $result = $this->operationLogService->getPageList($params);
        return $this->success($result);
    }

    #[Route(path: '/api/core/logs/deleteOperLog', methods: ['DELETE'], name: 'log.operation.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:logs:deleteOper')]
    public function deleteOperLog(Request $request): BaseJsonResponse
    {
        $ids = $this->parseIds($request);
        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        try {
            $count = $this->operationLogService->delete($ids);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 辅助方法 ====================

    private function parseIds(Request $request): array
    {
        $body = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }
        $all = array_merge($request->request->all(), $body);

        if (!empty($all['ids']) && is_array($all['ids'])) {
            return array_map('intval', $all['ids']);
        }
        if (!empty($all['id'])) {
            return [(int)$all['id']];
        }
        return [];
    }
}
