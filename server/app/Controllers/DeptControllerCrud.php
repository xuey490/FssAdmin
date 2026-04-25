<?php

declare(strict_types=1);

/**
 * 部门管理控制器（使用 CrudActionTrait 示例）
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Controllers;

use App\Dao\SysDeptDao;
use App\Services\SysDeptService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * DeptControllerCrud 部门管理控制器（CRUD Trait 示例）
 *
 * 演示如何使用 CrudActionTrait 快速实现 CRUD 操作。
 *
 * 使用方式：
 * 1. 定义 $serviceClass 或 $daoClass
 * 2. 可选定义 $validatorClass 进行数据验证
 * 3. 使用 insertInput() 获取并过滤表单数据
 * 4. 使用 input() 获取单个请求参数
 * 5. 使用 success()/fail() 返回响应
 */
class DeptControllerCrud extends BaseController
{
    /**
     * 业务服务类名
     * 指定后 BaseController 会自动实例化
     * @var string
     */
    protected string $serviceClass = SysDeptService::class;

    /**
     * 数据访问对象类名（如果没有 Service）
     * 会自动使用 GenericService 包装
     * @var string
     */
    // protected string $daoClass = SysDeptDao::class;

    /**
     * 验证器类名（可选）
     * @var string
     */
    // protected string $validatorClass = DeptValidator::class;

    // 注意：$service 属性由基类 BaseController 自动注入
    // 基类定义为 protected object $service
    // 如需使用具体类型，可通过类型转换或 IDE 注解获得代码提示
    // @var SysDeptService $this->service

    // ==================== 基础 CRUD（使用 Trait 方法）====================

    /**
     * 获取部门列表
     *
     * 使用 CrudActionTrait::index() 方法
     * 自动处理分页、排序、过滤
     *
     * @param Request $request
     * @return mixed
     */
    #[Route(path: '/api/system/dept-crud/list', methods: ['GET'], name: 'deptCrud.list')]
    ///[Auth(required: true)]
    public function index(Request $request)
    {
        // 调用 Trait 的 index 方法
        // 会自动调用 selectInput() 获取查询参数
        return parent::index($request);
    }

    /**
     * 获取部门详情
     *
     * 使用 CrudActionTrait::show() 方法
     *
     * @param Request $request
     * @return mixed
     */
    #[Route(path: '/api/system/dept-crud/detail/{id}', methods: ['GET'], name: 'deptCrud.detail')]
    #[Auth(required: true)]
    public function show(Request $request)
    {
        return parent::show($request);
    }

    /**
     * 创建部门
     *
     * 使用 CrudActionTrait::store() 方法
     * 自动使用 insertInput() 获取并过滤数据
     *
     * @param Request $request
     * @return mixed
     */
    #[Route(path: '/api/system/dept-crud/create', methods: ['POST'], name: 'deptCrud.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function store(Request $request)
    {
        return parent::store($request);
    }

    /**
     * 更新部门
     *
     * 使用 CrudActionTrait::update() 方法
     *
     * @param Request $request
     * @return mixed
     */
    #[Route(path: '/api/system/dept-crud/update/{id}', methods: ['PUT'], name: 'deptCrud.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function update(Request $request)
    {
        return parent::update($request);
    }

    /**
     * 删除部门
     *
     * 使用 CrudActionTrait::destroy() 方法
     *
     * @param Request $request
     * @return mixed
     */
    #[Route(path: '/api/system/dept-crud/delete/{id}', methods: ['DELETE'], name: 'deptCrud.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function destroy(Request $request)
    {
        return parent::destroy($request);
    }

    // ==================== 自定义方法（覆盖或扩展）====================

    /**
     * 获取部门树
     *
     * 自定义方法，使用 input() 获取参数
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept-crud/tree', methods: ['GET'], name: 'deptCrud.tree')]
    #[Auth(required: true)]
    public function tree(Request $request): BaseJsonResponse
    {
        // 使用 input() 获取单个参数
        $parentId = (int) $this->input('parent_id', 0);
        $status = $this->input('status', '');

        $where = [];
        if ($status !== '') {
            $where['status'] = (int) $status;
        }

        $result = $this->service->getDeptTree($parentId, $where);

        return $this->success($result);
    }

    /**
     * 获取部门下拉选项
     *
     * 自定义方法，使用 selectInput() 获取查询参数
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept-crud/options', methods: ['GET'], name: 'deptCrud.options')]
    #[Auth(required: true)]
    public function options(Request $request): BaseJsonResponse
    {
        // 使用 selectInput() 获取列表查询参数
        [$where, $format, $limit, $field, $order, $page] = $this->selectInput($request);

        // 强制使用 select 格式
        $list = $this->service->selectList($where, $field, $page, $limit, $order);

        return $this->success([
            'items' => $list->map(function ($item) {
                return [
                    'label' => $item->name,
                    'value' => $item->id,
                ];
            }),
            'total' => $this->service->getCount($where),
        ]);
    }

    /**
     * 批量更新部门状态
     *
     * 自定义方法，演示 input() 获取数组参数
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept-crud/batch-status', methods: ['PUT'], name: 'deptCrud.batch_status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function batchUpdateStatus(Request $request): BaseJsonResponse
    {
        // 获取单个参数
        $status = (int) $this->input('status', 1);

        // 从 request 获取数组参数（ids 不在模型字段中，需要特殊处理）
        $ids = $request->request->get('ids', []);

        if (empty($ids)) {
            return $this->fail('请选择要更新的部门');
        }

        $count = $this->service->batchUpdateStatus($ids, $status);

        return $this->success(['count' => $count], '批量更新成功');
    }

    // ==================== 自定义数据获取方法 ====================

    /**
     * 覆盖 insertInput 方法（可选）
     *
     * 如果需要自定义获取数据的逻辑，可以覆盖此方法
     *
     * @param Request $request
     * @return array
     */
    protected function insertInput(Request $request): array
    {
        // 方式1：使用父类方法（获取所有 POST 数据并过滤）
        // return parent::insertInput($request);

        // 方式2：使用 input() 逐个获取字段（推荐，支持 XSS 过滤）
        $fields = [
            'parent_id' => 0,
            'name' => '',
            'code' => '',
            'leader_id' => 0,
            'phone' => '',
            'email' => '',
            'sort' => 0,
            'status' => 1,
            'remark' => '',
        ];

        return $this->insertInputByFields($fields);
    }

    /**
     * 覆盖 selectInput 方法（可选）
     *
     * 自定义列表查询参数获取
     *
     * @param Request $request
     * @return array
     */
    protected function selectInput(Request $request): array
    {
        // 使用 input() 获取查询参数
        $where = [];

        if ($deptName = $this->input('name', '')) {
            $where['name'] = ['like', "%{$deptName}%"];
        }

        if ($deptCode = $this->input('code', '')) {
            $where['code'] = ['like', "%{$deptCode}%"];
        }

        if (($status = $this->input('status', '')) !== '') {
            $where['status'] = (int) $status;
        }

        // 获取分页和排序参数
        $page = (int) $this->input('page', 1);
        $limit = (int) $this->input('limit', 10);
        $order = $this->input('order', 'sort asc');
        $field = $this->input('field', '*');
        $format = $this->input('format', 'normal'); // normal, select, tree

        return [$where, $format, $limit, $field, $order, $page];
    }

    // ==================== 自定义格式化方法 ====================

    /**
     * 覆盖 formatNormal 方法（可选）
     *
     * 自定义普通列表格式
     *
     * @param mixed $list
     * @param int $total
     * @return BaseJsonResponse
     */
    protected function formatNormal($list, int $total)
    {
        // 自定义格式化逻辑
        $formattedList = [];
        foreach ($list as $item) {
            $formattedList[] = [
                'id' => $item->id,
                'dept_name' => $item->name,
                'dept_code' => $item->code,
                'parent_id' => $item->parent_id,
                'leader' => $item->leader,
                'status' => $item->status,
                'status_text' => $item->status === 1 ? '启用' : '禁用',
                'created_at' => $item->create_time?->format('Y-m-d H:i:s'),
            ];
        }

        return $this->success([
            'list' => $formattedList,
            'total' => $total,
        ]);
    }
}
