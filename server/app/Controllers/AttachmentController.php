<?php

declare(strict_types=1);

/**
 * 附件管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysAttachmentService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

/**
 * AttachmentController 附件管理控制器
 */
class AttachmentController extends BaseController
{
    protected SysAttachmentService $attachmentService;

    protected function initialize(): void
    {
        $this->attachmentService = new SysAttachmentService();
    }

    // ==================== 附件管理 ====================

    /**
     * 附件列表
     */
    #[Route(path: '/api/system/attachment/list', methods: ['GET'], name: 'attachment.list')]
    #[Auth(required: true)]
    #[Permission('core:attachment:index')]
    public function list(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->attachmentService->getList($params);
        return $this->success($result);
    }

    /**
     * 附件详情
     */
    #[Route(path: '/api/system/attachment/detail/{id}', methods: ['GET'], name: 'attachment.detail')]
    #[Auth(required: true)]
    public function detail(Request $request): BaseJsonResponse
    {
        $id     = (int)$request->attributes->get('id');
        $result = $this->attachmentService->getDetail($id);

        if (!$result) {
            return $this->fail('附件不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 上传附件
     */
    #[Route(path: '/api/system/attachment/upload', methods: ['POST'], name: 'attachment.upload')]
    #[Auth(required: true)]
    public function upload(Request $request): BaseJsonResponse
    {
        $file       = $request->files->get('file');
        $categoryId = (int)($request->request->get('category_id') ?: 1);
        $operator   = $this->getOperatorId($request);

        if (!$file) {
            return $this->fail('请选择要上传的文件');
        }

        try {
            $result = $this->attachmentService->upload($file, $categoryId, $operator, 'image');
            return $this->success($result, '上传成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新附件名称
     */
    #[Route(path: '/api/system/attachment/update/{id}', methods: ['PUT'], name: 'attachment.update')]
    #[Auth(required: true)]
    #[Permission('core:attachment:edit')]
    public function update(Request $request): BaseJsonResponse
    {
        $id   = (int)$request->attributes->get('id');
        $body = $this->parseBody($request);

        $originName = $body['origin_name'] ?? '';
        if (empty($originName)) {
            return $this->fail('文件名不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $this->attachmentService->updateName($id, $originName, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除附件（单条）
     */
    #[Route(path: '/api/system/attachment/delete/{id}', methods: ['DELETE'], name: 'attachment.delete')]
    #[Auth(required: true)]
    #[Permission('core:attachment:edit')]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        try {
            $this->attachmentService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 批量删除附件
     */
    #[Route(path: '/api/system/attachment/batchDelete', methods: ['DELETE'], name: 'attachment.batchDelete')]
    #[Auth(required: true)]
    #[Permission('core:attachment:edit')]
    public function batchDelete(Request $request): BaseJsonResponse
    {
        $ids = $this->parseIds($request);
        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        try {
            $count = $this->attachmentService->batchDelete($ids);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 移动附件到分类
     */
    #[Route(path: '/api/system/attachment/move', methods: ['PUT'], name: 'attachment.move')]
    #[Auth(required: true)]
    public function move(Request $request): BaseJsonResponse
    {
        $body       = $this->parseBody($request);
        $ids        = array_map('intval', $body['ids'] ?? []);
        $categoryId = (int)($body['category_id'] ?? 0);

        if (empty($ids)) {
            return $this->fail('请选择要移动的文件');
        }

        if (empty($categoryId)) {
            return $this->fail('请选择目标分类');
        }

        $operator = $this->getOperatorId($request);

        try {
            $count = $this->attachmentService->moveToCategory($ids, $categoryId, $operator);
            return $this->success(['count' => $count], '移动成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 下载附件
     */
    #[Route(path: '/api/system/attachment/download/{id}', methods: ['GET'], name: 'attachment.download')]
    #[Auth(required: true)]
    public function download(Request $request)
    {
        $id         = (int)$request->attributes->get('id');
        $attachment = \App\Models\SysAttachment::find($id);

        if (!$attachment) {
            return $this->fail('附件不存在', 404);
        }

        $filePath = base_path('public/' . $attachment->storage_path . '/' . $attachment->object_name);

        if (!file_exists($filePath)) {
            return $this->fail('文件不存在', 404);
        }

        return new \Symfony\Component\HttpFoundation\BinaryFileResponse(
            $filePath,
            200,
            ['Content-Disposition' => 'attachment; filename="' . rawurlencode($attachment->origin_name) . '"']
        );
    }

    /**
     * 存储统计
     */
    #[Route(path: '/api/system/attachment/stats', methods: ['GET'], name: 'attachment.stats')]
    #[Auth(required: true)]
    public function stats(Request $request): BaseJsonResponse
    {
        $result = $this->attachmentService->getStorageStats();
        return $this->success($result);
    }

    // ==================== 分类管理 ====================

    /**
     * 分类列表（支持 ?tree=true 返回树形）
     */
    #[Route(path: '/api/system/attachment-category/list', methods: ['GET'], name: 'attachmentCategory.list')]
    #[Auth(required: true)]
    public function categoryList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->attachmentService->getCategoryList($params);
        return $this->success($result);
    }

    /**
     * 分类详情
     */
    #[Route(path: '/api/system/attachment-category/detail/{id}', methods: ['GET'], name: 'attachmentCategory.detail')]
    #[Auth(required: true)]
    public function categoryDetail(Request $request): BaseJsonResponse
    {
        $id     = (int)$request->attributes->get('id');
        $result = $this->attachmentService->getCategoryDetail($id);

        if (!$result) {
            return $this->fail('分类不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建分类
     */
    #[Route(path: '/api/system/attachment-category/create', methods: ['POST'], name: 'attachmentCategory.create')]
    #[Auth(required: true)]
    #[Permission('core:attachment:edit')]
    public function categoryCreate(Request $request): BaseJsonResponse
    {
        $body = $this->parseBody($request);

        $data = [
            'parent_id'     => (int)($body['parent_id'] ?? 0),
            'category_name' => $body['category_name'] ?? '',
            'sort'          => (int)($body['sort'] ?? 100),
            'status'        => (int)($body['status'] ?? 1),
            'remark'        => $body['remark'] ?? '',
        ];

        if (empty($data['category_name'])) {
            return $this->fail('分类名称不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $category = $this->attachmentService->createCategory($data, $operator);
            return $this->success(['id' => $category->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新分类
     */
    #[Route(path: '/api/system/attachment-category/update/{id}', methods: ['PUT'], name: 'attachmentCategory.update')]
    #[Auth(required: true)]
    #[Permission('core:attachment:edit')]
    public function categoryUpdate(Request $request): BaseJsonResponse
    {
        $id   = (int)$request->attributes->get('id');
        $body = $this->parseBody($request);

        $data = array_filter([
            'parent_id'     => isset($body['parent_id']) ? (int)$body['parent_id'] : null,
            'category_name' => $body['category_name'] ?? null,
            'sort'          => isset($body['sort']) ? (int)$body['sort'] : null,
            'status'        => isset($body['status']) ? (int)$body['status'] : null,
            'remark'        => $body['remark'] ?? null,
        ], fn($v) => $v !== null);

        $operator = $this->getOperatorId($request);

        try {
            $this->attachmentService->updateCategory($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除分类
     */
    #[Route(path: '/api/system/attachment-category/delete/{id}', methods: ['DELETE'], name: 'attachmentCategory.delete')]
    #[Auth(required: true)]
    #[Permission('core:attachment:edit')]
    public function categoryDelete(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        try {
            $this->attachmentService->deleteCategory($id);
            return $this->success([], '删除成功');
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
        $body    = [];
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
