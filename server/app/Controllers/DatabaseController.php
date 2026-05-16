<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\DatabaseMaintainService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

class DatabaseController extends BaseController
{
    protected DatabaseMaintainService $databaseService;

    public function __construct()
    {
        $this->databaseService = new DatabaseMaintainService();
    }

    // ==================== 数据表管理 ====================

    #[Route(path: '/api/core/database/table/list', methods: ['GET'], name: 'database.table.list')]
    #[Auth(required: true)]
    #[Permission('core:database:index')]
    public function tableList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $list = $this->databaseService->getTables($params);
        return $this->success(['data' => $list, 'total' => count($list)]);
    }

    #[Route(path: '/api/core/database/table/dataSource', methods: ['GET'], name: 'database.table.dataSource')]
    #[Auth(required: true)]
    #[Permission('core:database:index')]
    public function dataSource(Request $request): BaseJsonResponse
    {
        $list = $this->databaseService->getDataSource();
        return $this->success($list);
    }

    #[Route(path: '/api/core/database/table/detailed', methods: ['GET'], name: 'database.table.detailed')]
    #[Auth(required: true)]
    #[Permission('core:database:index')]
    public function tableDetailed(Request $request): BaseJsonResponse
    {
        $tableName = $request->query->get('table');
        
        if (empty($tableName)) {
            return $this->fail('表名不能为空');
        }

        // 验证表名，防止SQL注入
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            return $this->fail('表名格式不正确');
        }

        $columns = $this->databaseService->getTableDetailed($tableName);
        return $this->success(['columns' => $columns]);
    }

    #[Route(path: '/api/core/database/table/createSql', methods: ['GET'], name: 'database.table.createSql')]
    #[Auth(required: true)]
    #[Permission('core:database:index')]
    public function createSql(Request $request): BaseJsonResponse
    {
        $tableName = $request->query->get('table');
        
        if (empty($tableName)) {
            return $this->fail('表名不能为空');
        }

        // 验证表名，防止SQL注入
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            return $this->fail('表名格式不正确');
        }

        $result = $this->databaseService->getCreateTableSql($tableName);
        return $this->success($result);
    }

    #[Route(path: '/api/core/database/table/optimize', methods: ['POST'], name: 'database.table.optimize')]
    #[Auth(required: true)]
    #[Permission('core:database:edit')]
    public function optimizeTable(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);
        $tables = $data['tables'] ?? [];

        if (empty($tables)) {
            return $this->fail('请选择要优化的表');
        }

        // 验证表名
        foreach ($tables as $table) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
                return $this->fail('表名格式不正确: ' . $table);
            }
        }

        $results = $this->databaseService->optimizeTables($tables);
        return $this->success($results, '优化完成');
    }

    #[Route(path: '/api/core/database/table/fragment', methods: ['POST'], name: 'database.table.fragment')]
    #[Auth(required: true)]
    #[Permission('core:database:edit')]
    public function cleanFragment(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);
        $tables = $data['tables'] ?? [];

        if (empty($tables)) {
            return $this->fail('请选择要清理的表');
        }

        // 验证表名
        foreach ($tables as $table) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
                return $this->fail('表名格式不正确: ' . $table);
            }
        }

        $results = $this->databaseService->cleanTableFragment($tables);
        return $this->success($results, '清理完成');
    }

    // ==================== 回收站管理 ====================

    #[Route(path: '/api/core/database/recycle/list', methods: ['GET'], name: 'database.recycle.list')]
    #[Auth(required: true)]
    #[Permission('core:recycle:index')]
    public function recycleList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->databaseService->getRecycleData($params);
        return $this->success($result);
    }

    #[Route(path: '/api/core/database/recycle/destroy', methods: ['POST'], name: 'database.recycle.destroy')]
    #[Auth(required: true)]
    #[Permission('core:recycle:edit')]
    public function destroyRecycle(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);
        $table = $data['table'] ?? '';
        $ids = $data['ids'] ?? [];

        if (empty($table) || empty($ids)) {
            return $this->fail('参数不完整');
        }

        // 验证表名
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return $this->fail('表名格式不正确');
        }

        $result = $this->databaseService->destroyRecycleData($table, $ids);
        return $result 
            ? $this->success([], '销毁成功')
            : $this->fail('销毁失败');
    }

    #[Route(path: '/api/core/database/recycle/recovery', methods: ['POST'], name: 'database.recycle.recovery')]
    #[Auth(required: true)]
    #[Permission('core:recycle:edit')]
    public function recoveryRecycle(Request $request): BaseJsonResponse
    {
        $data = $this->parseBody($request);
        $table = $data['table'] ?? '';
        $ids = $data['ids'] ?? [];

        if (empty($table) || empty($ids)) {
            return $this->fail('参数不完整');
        }

        // 验证表名
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return $this->fail('表名格式不正确');
        }

        $result = $this->databaseService->recoveryRecycleData($table, $ids);
        return $result 
            ? $this->success([], '恢复成功')
            : $this->fail('恢复失败');
    }

    // ==================== 辅助方法 ====================

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
}
