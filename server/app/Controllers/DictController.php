<?php

declare(strict_types=1);

/**
 * 数据字典控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysDictService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

/**
 * DictController 数据字典控制器
 */
class DictController extends BaseController
{
    /**
     * 字典服务
     * @var SysDictService
     */
    protected SysDictService $dictService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->dictService = new SysDictService();
    }

    // ==================== 字典类型 ====================

    /**
     * 获取字典类型列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/list', methods: ['GET'], name: 'dict.type.list')]
    #[Auth(required: true)]
    #[Permission('core:dict:index')]
    public function typeList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->dictService->getTypeList($params);

        return $this->success($result);
    }

    /**
     * 获取字典类型详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/detail/{id}', methods: ['GET'], name: 'dict.type.detail')]
    #[Auth(required: true)]
    #[Permission('core:dict:index')]
    public function typeDetail(Request $request): BaseJsonResponse
    {
        $id = $request->attributes->get('id');
        $result = $this->dictService->getTypeDetail($id);

        if (!$result) {
            return $this->fail('字典类型不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建字典类型
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/create', methods: ['POST'], name: 'dict.type.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function typeCreate(Request $request): BaseJsonResponse
    {
        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        $data = [
            'name'   => $all['name'] ?? '',
            'code'   => $all['code'] ?? '',
            'status' => (int)($all['status'] ?? 1),
            'remark' => $all['remark'] ?? '',
        ];

        if (empty($data['name']) || empty($data['code'])) {
            return $this->fail('字典名称和编码不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $dictType = $this->dictService->createType($data, $operator);
            return $this->success(['id' => $dictType->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新字典类型
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/update/{id}', methods: ['PUT'], name: 'dict.type.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function typeUpdate(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        $data = [
            'name'   => $all['name'] ?? null,
            'code'   => $all['code'] ?? null,
            'status' => isset($all['status']) && $all['status'] !== '' ? (int)$all['status'] : null,
            'remark' => $all['remark'] ?? null,
        ];

        $data = array_filter($data, fn($v) => $v !== null);

        $operator = $this->getOperatorId($request);

        try {
            $this->dictService->updateType($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除字典类型
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/delete/{id}', methods: ['DELETE'], name: 'dict.type.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function typeDelete(Request $request): BaseJsonResponse
    {
        $id = $request->attributes->get('id');
        try {
            $this->dictService->deleteType($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新字典类型状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/status/{id}', methods: ['PUT'], name: 'dict.type.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function typeUpdateStatus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->request->all(), $jsonBody);
        $status = (int)($all['status'] ?? 1);

        $result = $this->dictService->updateTypeStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    // ==================== 字典数据 ====================

    /**
     * 获取字典数据列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/list', methods: ['GET'], name: 'dict.data.list')]
    #[Auth(required: true)]
    #[Permission('core:dict:index')]
    public function dataList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->dictService->getDataList($params);

        return $this->success($result);
    }

    /**
     * 根据字典编码获取字典数据
     *
     * @param Request $request 请求对象
     * @param string  $dictCode 字典编码
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/code/{dictCode}', methods: ['GET'], name: 'dict.data.byCode')]
    #[Permission(['core:dict:index'])]
    public function dataByCode(Request $request, string $dictCode): BaseJsonResponse
    {
        $data = $this->dictService->getDictDataByCode($dictCode);

        return $this->success($data);
    }

    /**
     * 获取字典数据详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/detail/{id}', methods: ['GET'], name: 'dict.data.detail')]
    #[Auth(required: true)]
    #[Permission('core:dict:index')]
    public function dataDetail(Request $request): BaseJsonResponse
    {
        $id = $request->attributes->get('id');
        $result = $this->dictService->getDataDetail($id);

        if (!$result) {
            return $this->fail('字典数据不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建字典数据
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/create', methods: ['POST'], name: 'dict.data.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function dataCreate(Request $request): BaseJsonResponse
    {
        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        $data = [
            'type_id' => (int)($all['type_id'] ?? 0),
            'label'   => $all['label'] ?? '',
            'value'   => $all['value'] ?? '',
            'sort'    => (int)($all['sort'] ?? 100),
            'color'   => $all['color'] ?? '',
            'status'  => (int)($all['status'] ?? 1),
            'remark'  => $all['remark'] ?? '',
        ];

        if (empty($data['label']) || empty($data['value'])) {
            return $this->fail('字典标签和字典值不能为空');
        }

        if (empty($data['type_id'])) {
            return $this->fail('字典类型不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $dictData = $this->dictService->createData($data, $operator);
            return $this->success(['id' => $dictData->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新字典数据
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/update/{id}', methods: ['PUT'], name: 'dict.data.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function dataUpdate(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->query->all(), $request->request->all(), $jsonBody);

        $data = [
            'label'  => $all['label'] ?? null,
            'value'  => $all['value'] ?? null,
            'sort'   => isset($all['sort']) ? (int)$all['sort'] : null,
            'color'  => $all['color'] ?? null,
            'status' => isset($all['status']) && $all['status'] !== '' ? (int)$all['status'] : null,
            'remark' => $all['remark'] ?? null,
        ];

        $data = array_filter($data, fn($v) => $v !== null);

        $operator = $this->getOperatorId($request);

        try {
            $this->dictService->updateData($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 批量删除字典数据
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/batchDelete', methods: ['DELETE'], name: 'dict.data.batchDelete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function dataBatchDelete(Request $request): BaseJsonResponse
    {
        $ids = $this->parseIds($request);
        if (empty($ids)) {
            return $this->fail('请选择要删除的记录');
        }

        try {
            $count = $this->dictService->batchDeleteData($ids);
            return $this->success(['count' => $count], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除字典数据
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/delete/{id}', methods: ['DELETE'], name: 'dict.data.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function dataDelete(Request $request): BaseJsonResponse
    {
        $id = $request->attributes->get('id');
        try {
            $this->dictService->deleteData($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新字典数据状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/status/{id}', methods: ['PUT'], name: 'dict.data.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    #[Permission('core:dict:edit')]
    public function dataUpdateStatus(Request $request): BaseJsonResponse
    {
        $id = (int)$request->attributes->get('id');

        $jsonBody = [];
        $content = $request->getContent();
        if (!empty($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $jsonBody = $decoded;
            }
        }
        $all = array_merge($request->request->all(), $jsonBody);
        $status = (int)($all['status'] ?? 1);

        $result = $this->dictService->updateDataStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取操作人ID
     *
     * @param Request $request 请求对象
     * @return int
     */
    protected function getOperatorId(Request $request): int
    {
        $user = $request->attributes->get('user');
        return $user['id'] ?? 0;
    }

    /**
     * 从请求中解析 ID 列表
     * 支持 { ids: [1,2] } 或 { id: 1 } 两种格式
     *
     * @param Request $request
     * @return array
     */
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
