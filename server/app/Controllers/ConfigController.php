<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SysConfigService;
use App\Services\SysConfigGroupService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

class ConfigController extends BaseController
{
    protected SysConfigService $configService;
    protected SysConfigGroupService $configGroupService;

    protected function initialize(): void
    {
        $this->configService = new SysConfigService();
        $this->configGroupService = new SysConfigGroupService();
    }

    // ==================== 配置组管理 ====================

    #[Route(path: '/api/core/configGroup/list', methods: ['GET'], name: 'config.group.list')]
    #[Auth(required: true)]
    #[Permission('core:config:index')]
    public function groupList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->configGroupService->getList($params);
        return $this->success($result);
    }

    #[Route(path: '/api/core/configGroup/save', methods: ['POST'], name: 'config.group.save')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:config:edit')]
    public function groupSave(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);

        if (empty($data['name']) || empty($data['code'])) {
            return $this->fail('配置组名称和编码不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $id = $this->configGroupService->save($data, $operator);
            return $this->success(['id' => $id], '保存成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/configGroup/update/{id}', methods: ['PUT'], name: 'config.group.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:config:edit')]
    public function groupUpdate(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $data = $this->parseBody($request);
        $operator = $this->getOperatorId($request);

        try {
            $this->configGroupService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/configGroup/delete/{id}', methods: ['DELETE'], name: 'config.group.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:config:edit')]
    public function groupDelete(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        try {
            $this->configGroupService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/configGroup/testEmail', methods: ['POST'], name: 'config.group.testEmail')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function testEmail(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);
        $result = $this->configGroupService->testEmail($data);
        
        return $result['success'] 
            ? $this->success($result, $result['message'])
            : $this->fail($result['message']);
    }

    // ==================== 配置项管理 ====================

    #[Route(path: '/api/core/config/list', methods: ['GET'], name: 'config.list')]
    #[Auth(required: true)]
    #[Permission('core:config:index')]
    public function configList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->configService->getList($params);
        
        // 如果是前端按照 group_id 直接获取配置项（不分页），则返回 list 数组本身
        if (isset($params['group_id']) && !isset($params['page'])) {
            return $this->success($result['list'] ?? []);
        }

        return $this->success($result);
    }

    #[Route(path: '/api/core/config/save', methods: ['POST'], name: 'config.save')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:config:edit')]
    public function configSave(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);

        if (empty($data['key']) || empty($data['name'])) {
            return $this->fail('配置键和配置名称不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $id = $this->configService->save($data, $operator);
            return $this->success(['id' => $id], '保存成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/config/update/{id}', methods: ['PUT'], name: 'config.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:config:update')]
    public function configUpdate(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');
        $data = $this->parseBody($request);
        $operator = $this->getOperatorId($request);

        try {
            $this->configService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/config/delete', methods: ['DELETE'], name: 'config.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:config:edit')]
    public function configDelete(Request $request): BaseJsonResponse
    {
        $ids = $this->parseIds($request);
        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        try {
            $count = $this->configService->delete($ids);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[Route(path: '/api/core/config/batchUpdate', methods: ['POST'], name: 'config.batchUpdate')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:config:update')]
    public function batchUpdate(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);
        $configs = $data['config'] ?? $data['configs'] ?? [];

        if (empty($configs)) {
            return $this->fail('配置数据不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $this->configService->batchUpdate($configs, $operator);
            return $this->success([], '批量更新成功');
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
