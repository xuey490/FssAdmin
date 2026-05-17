<?php

declare(strict_types=1);

/**
 * 代码生成控制器
 *
 * 路由对应前端 web/src/api/api/tool/generate.ts 中的全部 API：
 *   GET    /api/tool/code/index          → index()           分页列表
 *   GET    /api/tool/code/read           → read()            读取表详情+字段
 *   PUT    /api/tool/code/update         → update()          更新配置（含字段）
 *   DELETE /api/tool/code/destroy        → destroy()         批量删除
 *   GET    /api/tool/code/getTableColumns→ getTableColumns() 获取字段列表
 *   POST   /api/tool/code/loadTable      → loadTable()       装载数据表
 *   POST   /api/tool/code/sync           → sync()            同步表结构
 *   GET    /api/tool/code/preview        → preview()         预览代码
 *   POST   /api/tool/code/generate       → generate()        生成代码 ZIP 下载
 *   POST   /api/tool/code/generateFile   → generateFile()    生成代码到项目文件
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-29
 */

namespace App\Controllers;

use App\Services\ToolGenerateService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

class GenerateController extends BaseController
{
    protected ToolGenerateService $generateService;

    protected function initialize(): void
    {
        $this->generateService = new ToolGenerateService();
    }

    // ==================== 列表 ====================

    /**
     * 分页列表
     * GET /api/tool/code/index
     * 参数: table_name, source, page, limit
     */
    #[Route(path: '/api/tool/code/index', methods: ['GET'], name: 'generate.index')]
    #[Auth(required: true)]
    #[Permission('tool:code:index')]
    public function index(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->generateService->getPageList($params);
        return $this->success($result);
    }

    // ==================== 详情 ====================

    /**
     * 读取表详情（含 columns 字段配置）
     * GET /api/tool/code/read?id=
     */
    #[Route(path: '/api/tool/code/read', methods: ['GET'], name: 'generate.read')]
    #[Auth(required: true)]
    #[Permission('tool:code:index')]
    public function read(Request $request): BaseJsonResponse
    {
        $id = (int)$request->query->get('id', 0);
        if ($id <= 0) {
            return $this->fail('参数 id 不能为空');
        }

        try {
            $result = $this->generateService->getDetail($id);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 更新 ====================

    /**
     * 更新代码生成配置（含 columns 数组）
     * PUT /api/tool/code/update
     * Body: 表配置字段 + columns 数组 + relations（存入 options）
     */
    #[Route(path: '/api/tool/code/update', methods: ['PUT'], name: 'generate.update')]
    #[Auth(required: true)]
    #[Permission('tool:code:edit')]
    public function update(Request $request): BaseJsonResponse
    {
        $data = $this->inputAll($request);
        $id   = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            return $this->fail('参数 id 不能为空');
        }

        $operatorId = (int)($request->attributes->get('user')['id'] ?? 0);

        try {
            $this->generateService->updateConfig($id, $data, $operatorId);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 删除 ====================

    /**
     * 批量删除
     * DELETE /api/tool/code/destroy
     * Body: { ids: [1, 2, 3] }
     */
    #[Route(path: '/api/tool/code/destroy', methods: ['DELETE'], name: 'generate.destroy')]
    #[Auth(required: true)]
    #[Permission('tool:code:edit')]
    public function destroy(Request $request): BaseJsonResponse
    {
        $data = $this->inputAll($request);
        $ids  = [];

        if (!empty($data['ids']) && is_array($data['ids'])) {
            $ids = array_map('intval', $data['ids']);
        } elseif (!empty($data['id'])) {
            $ids = [(int)$data['id']];
        }

        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        $operatorId = (int)($request->attributes->get('user')['id'] ?? 0);

        try {
            $count = $this->generateService->deleteByIds($ids, $operatorId);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 字段查询 ====================

    /**
     * 获取字段列表
     * GET /api/tool/code/getTableColumns?table_id=
     */
    #[Route(path: '/api/tool/code/getTableColumns', methods: ['GET'], name: 'generate.getTableColumns')]
    #[Auth(required: true)]
    #[Permission('tool:code:index')]
    public function getTableColumns(Request $request): BaseJsonResponse
    {
        $tableId = (int)$request->query->get('table_id', 0);
        if ($tableId <= 0) {
            return $this->fail('参数 table_id 不能为空');
        }

        try {
            $columns = $this->generateService->getColumns($tableId);
            return $this->success($columns);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 装载数据表 ====================

    /**
     * 装载数据表（从数据库读取表结构写入代码生成配置）
     * POST /api/tool/code/loadTable
     * Body: { source: 'mysql', names: [{name, comment, sourceName}] }
     */
    #[Route(path: '/api/tool/code/loadTable', methods: ['POST'], name: 'generate.loadTable')]
    #[Auth(required: true)]
    #[Permission('tool:code:edit')]
    public function loadTable(Request $request): BaseJsonResponse
    {
        $data  = $this->inputAll($request);
        $source= $data['source'] ?? 'default';
        $names = $data['names']  ?? [];

        if (empty($names) || !is_array($names)) {
            return $this->fail('请选择要装载的数据表');
        }

        $operatorId = (int)($request->attributes->get('user')['id'] ?? 0);

        try {
            $result = $this->generateService->loadTable($source, $names, $operatorId);
            $msg    = empty($result['failed'])
                ? '装载成功，共 ' . count($result['success']) . ' 张表'
                : '部分装载成功：' . count($result['success']) . ' 成功，' . count($result['failed']) . ' 失败';
            return $this->success($result, $msg);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 同步表结构 ====================

    /**
     * 同步表结构（将数据库最新字段同步回字段配置，保留已有配置属性）
     * POST /api/tool/code/sync
     * Body: { id: 1 } 或 { ids: [1,2] }
     */
    #[Route(path: '/api/tool/code/sync', methods: ['POST'], name: 'generate.sync')]
    #[Auth(required: true)]
    #[Permission('tool:code:edit')]
    public function sync(Request $request): BaseJsonResponse
    {
        $data = $this->inputAll($request);
        $ids  = [];

        if (!empty($data['ids']) && is_array($data['ids'])) {
            $ids = array_map('intval', $data['ids']);
        } elseif (!empty($data['id'])) {
            $ids = [(int)$data['id']];
        }

        if (empty($ids)) {
            return $this->fail('参数 id 不能为空');
        }

        $operatorId = (int)($request->attributes->get('user')['id'] ?? 0);

        $success = [];
        $failed  = [];
        foreach ($ids as $id) {
            try {
                $this->generateService->syncTable($id, $operatorId);
                $success[] = $id;
            } catch (\Throwable $e) {
                $failed[] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        if (!empty($failed) && empty($success)) {
            return $this->fail('同步失败：' . ($failed[0]['reason'] ?? '未知错误'));
        }

        return $this->success(['success' => $success, 'failed' => $failed], '同步完成');
    }

    // ==================== 预览代码 ====================

    /**
     * 预览代码
     * GET /api/tool/code/preview?id=
     * 返回: [{name, tab_name, code, lang}]
     */
    #[Route(path: '/api/tool/code/preview', methods: ['GET'], name: 'generate.preview')]
    #[Auth(required: true)]
    #[Permission('tool:code:index')]
    public function preview(Request $request): BaseJsonResponse
    {
        $id = (int)$request->query->get('id', 0);
        if ($id <= 0) {
            return $this->fail('参数 id 不能为空');
        }

        try {
            $files = $this->generateService->previewCode($id);
            return $this->success($files);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 生成代码（ZIP 下载） ====================

    /**
     * 生成代码压缩包下载
     * POST /api/tool/code/generate
     * Body: { ids: [1, 2] }
     * 响应: application/zip 二进制流
     */
    #[Route(path: '/api/tool/code/generate', methods: ['POST'], name: 'generate.generate')]
    #[Auth(required: true)]
    #[Permission('tool:code:edit')]
    public function generate(Request $request): BaseJsonResponse|Response
    {
        $data = $this->inputAll($request);
        $ids  = [];

        if (!empty($data['ids']) && is_array($data['ids'])) {
            $ids = array_map('intval', $data['ids']);
        } elseif (!empty($data['id'])) {
            $ids = [(int)$data['id']];
        }

        if (empty($ids)) {
            return $this->fail('请选择要生成的记录');
        }

        try {
            $zipContent = $this->generateService->generateZip($ids);

            if (empty($zipContent)) {
                return $this->fail('代码生成失败，请检查配置');
            }

            $filename = 'generate_' . date('YmdHis') . '.zip';

            $response = new Response($zipContent);
            $response->headers->set('Content-Type', 'application/zip');
            $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");
            $response->headers->set('Content-Length', (string)strlen($zipContent));
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

            return $response;
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }

    // ==================== 生成到项目文件 ====================

    /**
     * 生成代码到项目目录
     * POST /api/tool/code/generateFile
     * Body: { ids: [1, 2] }
     */
    #[Route(path: '/api/tool/code/generateFile', methods: ['POST'], name: 'generate.generateFile')]
    #[Auth(required: true)]
    #[Permission('tool:code:edit')]
    public function generateFile(Request $request): BaseJsonResponse
    {
        $data = $this->inputAll($request);
        $ids  = [];

        if (!empty($data['ids']) && is_array($data['ids'])) {
            $ids = array_map('intval', $data['ids']);
        } elseif (!empty($data['id'])) {
            $ids = [(int)$data['id']];
        }

        if (empty($ids)) {
            return $this->fail('请选择要生成的记录');
        }

        $operatorId = (int)($request->attributes->get('user')['id'] ?? 0);

        try {
            $result = $this->generateService->generateFile($ids, $operatorId);
            $msg    = '生成成功，共 ' . count($result['success']) . ' 个文件';
            if (!empty($result['failed'])) {
                $msg .= '，' . count($result['failed']) . ' 个失败';
            }
            return $this->success($result, $msg);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
