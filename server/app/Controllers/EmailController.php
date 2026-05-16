<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\MailLogService;
use Framework\Attributes\Auth;
use Framework\Attributes\Route;
use Framework\Attributes\Permission;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EmailController extends BaseController
{
    protected MailLogService $mailLogService;

    protected function initialize(): void
    {
        $this->mailLogService = new MailLogService();
    }

    #[Route(path: '/api/core/email/index', methods: ['GET'], name: 'core.email.index')]
    #[Auth(required: true)]
    #[Permission('core:email:index')]
    public function index(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        if (isset($params['pageSize']) && !isset($params['limit'])) {
            $params['limit'] = $params['pageSize'];
        }

        $result = $this->mailLogService->getPageList($params);
        return $this->success($result);
    }

    #[Route(path: '/api/core/email/destroy', methods: ['DELETE'], name: 'core.email.destroy')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:email:destroy')]
    public function destroy(Request $request): BaseJsonResponse
    {
        $ids = $this->parseIds($request);
        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        try {
            $count = $this->mailLogService->delete($ids);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

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
