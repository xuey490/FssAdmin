<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ToolCrontabService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

class CrontabController extends BaseController
{
    protected ToolCrontabService $crontabService;

    protected function initialize(): void
    {
        $this->crontabService = new ToolCrontabService();
    }

    // ==================== 定时任务管理 ====================

    #[Route(path: '/api/tool/crontab/list', methods: ['GET'], name: 'crontab.list')]
    #[Auth(required: true)]
    #[Permission('tool:crontab:index')]
    public function list(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->crontabService->getPageList($params);
        return $this->success($result);
    }

    #[Route(path: '/api/tool/crontab/detail/{id}', methods: ['GET'], name: 'crontab.detail')]
    #[Auth(required: true)]
    #[Permission('tool:crontab:index')]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $result = $this->crontabService->getDetail($id);

        if (!$result) {
            return $this->fail('任务不存在', 404);
        }

        return $this->success($result);
    }

    #[Route(path: '/api/tool/crontab/create', methods: ['POST'], name: 'crontab.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('tool:crontab:edit')]
    public function create(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);

        if (empty($data['name']) || empty($data['target'])) {
            return $this->fail('任务名称和目标不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $crontab = $this->crontabService->create($data, $operator);
            return $this->success(['id' => $crontab->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/tool/crontab/update/{id}', methods: ['PUT'], name: 'crontab.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('tool:crontab:edit')]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $data = $this->parseBody($request);
        $operator = $this->getOperatorId($request);

        try {
            $this->crontabService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/tool/crontab/delete', methods: ['DELETE'], name: 'crontab.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('tool:crontab:edit')]
    public function delete(Request $request): BaseJsonResponse
    {
        $ids = $this->parseIds($request);
        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        try {
            $count = $this->crontabService->delete($ids);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/tool/crontab/run/{id}', methods: ['POST'], name: 'crontab.run')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('tool:crontab:run')]
    public function run(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        try {
            $result = $this->crontabService->run($id);
            return $this->success($result, '执行完成');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 执行日志管理 ====================

    #[Route(path: '/api/tool/crontab/log/list', methods: ['GET'], name: 'crontab.log.list')]
    #[Auth(required: true)]
    #[Permission('tool:crontab:index')]
    public function logList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->crontabService->getLogPageList($params);
        return $this->success($result);
    }

    #[Route(path: '/api/tool/crontab/log/delete', methods: ['DELETE'], name: 'crontab.log.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('tool:crontab:edit')]
    public function logDelete(Request $request): BaseJsonResponse
    {
        $ids = $this->parseIds($request);
        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        try {
            $count = $this->crontabService->deleteLog($ids);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 辅助方法 ====================

    protected function getOperatorId(Request $request): int
    {
        $user = $request->attributes->get('user');
        return $user['id'] ?? 0;
    }

    private function parseBody(Request $request): array
    {
        $body = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }
        return array_merge($request->request->all(), $body);
    }

    private function parseIds(Request $request): array
    {
        $body = $this->parseBody($request);
        if (!empty($body['ids']) && is_array($body['ids'])) {
            return array_map('intval', $body['ids']);
        }
        if (!empty($body['id'])) {
            return [(int)$body['id']];
        }
        return [];
    }
}
