<?php

declare(strict_types=1);

/**
 * 系统部门服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysDept;
use App\Models\SysUser;
use App\Dao\SysDeptDao;
use Framework\Basic\BaseService;
use Framework\Tenant\TenantContext;
use Symfony\Component\HttpFoundation\Request;
/**
 * SysDeptService 部门服务
 *
 * 处理部门相关的业务逻辑
 */
class SysDeptService extends BaseService
{
    /**
     * DAO 实例
     * @var SysDeptDao
     */
    protected SysDeptDao $deptDao;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->deptDao = new SysDeptDao();
        $this->setDao($this->deptDao);
    }

    /**
     * 获取当前请求（每次调用时获取，避免 Workerman 环境下的上下文污染）
     *
     * @return Request|null
     */
    protected function getCurrentRequest(): ?Request
    {
        return app('request');
    }

    /**
     * 获取当前用户
     *
     * @return SysUser|null
     */
    protected function getCurrentUser(): ?SysUser
    {
        $request = $this->getCurrentRequest();
        return $request ? $request->attributes->get('current_user') : null;
    }

    /**
     * 获取当前租户ID
     *
     * @return int|null
     */
    protected function getCurrentTenantId(): ?int
    {
        return TenantContext::getTenantId();
    }

    /**
     * 检查当前用户是否为超级管理员
     *
     * @return bool
     */
    protected function isSuperAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->isSuperAdmin();
    }

    /**
     * 获取所有启用的部门（扁平列表，用于下拉选择）
     *
     * @return array
     */
    public function getAllEnabled(): array
    {
        $query = SysDept::query()
            ->where('status', SysDept::STATUS_ENABLED)
            ->whereNull('delete_time')
            ->orderBy('sort');

        // 如果不是超级管理员，只查询当前租户的部门
        if (!$this->isSuperAdmin()) {
            $tenantId = $this->getCurrentTenantId();
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
        }

        return $query->get(['id', 'name', 'parent_id', 'code'])->toArray();
    }

    /**
     * 获取部门列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $deptName = $params['name'] ?? ($params['dept_name'] ?? '');
        $deptCode = $params['code'] ?? ($params['dept_code'] ?? '');
        $status = $params['status'] ?? '';

        // 无搜索条件时，直接构建并返回树
        if ($deptName === '' && $deptCode === '' && (string)$status === '') {
            return $this->getDeptTree();
        }

        // 有搜索条件：获取全量数据构建树，再从树中递归过滤
        $query = SysDept::query()
            ->with('leader');

        // 如果不是超级管理员，只查询当前租户的部门
        if (!$this->isSuperAdmin()) {
            $tenantId = $this->getCurrentTenantId();
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
        }

        $list = $query->orderBy('sort')->get()->toArray();
        $tree = $this->buildTree($list);

        // 从树中递归搜索，保留匹配节点及其祖先链
        $filtered = $this->filterTree($tree, $deptName, $deptCode, $status);

        return $filtered;
    }

    /**
     * 从树中递归过滤，保留匹配节点及其所有子节点
     *
     * @param array  $tree      树形数据
     * @param string $deptName  部门名称关键词
     * @param string $deptCode  部门编码关键词
     * @param string $status    状态
     * @return array
     */
    protected function filterTree(array $tree, string $deptName, string $deptCode, mixed $status): array
    {
        $result = [];
        foreach ($tree as $item) {
            $nameMatch = ($deptName !== '' && stripos($item['name'], $deptName) !== false);
            $codeMatch = ($deptCode !== '' && stripos($item['code'], $deptCode) !== false);

            // 当前节点匹配，保留它及其所有子节点
            if ($nameMatch || $codeMatch) {
                $result[] = $item;
                continue;
            }

            // 递归检查子节点
            $children = $item['children'] ?? [];
            if (!empty($children)) {
                $filteredChildren = $this->filterTree($children, $deptName, $deptCode, $status);
                if (!empty($filteredChildren)) {
                    $item['children'] = $filteredChildren;
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

    /**
     * 构建树形结构
     *
     * @param array $list   扁平列表
     * @param int   $parentId 父级ID
     * @return array
     */
    protected function buildTree(array $list, int $parentId = 0): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ((int)($item['parent_id'] ?? 0) === $parentId) {
                $children = $this->buildTree($list, (int)$item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $item['status_text'] = ($item['status'] ?? 0) === SysDept::STATUS_ENABLED ? '启用' : '禁用';
                // 数据库值映射到字典值：DB 1=启用 0=禁用 → 字典 1=正常 2=停用
               // $item['status'] = ($item['status'] ?? 0) === 0 ? 2 : 1;
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 获取部门树（管理列表用，显示所有状态）
     *
     * @return array
     */
    public function getDeptTree(): array
    {
        // 如果是超级管理员，查询所有部门（不过滤租户）
        if ($this->isSuperAdmin()) {
            return SysDept::getDeptTree(0, null, false);
        }
        
        // 普通用户只能查询当前租户的部门
        $tenantId = $this->getCurrentTenantId();
        return SysDept::getDeptTree(0, $tenantId, false);
    }


    /**
     * 获取部门选择树（带 label 字段，适配前端 ElTreeSelect）
     *
     * @return array
     */
    public function getSelectTree(): array
    {
        $query = SysDept::where('status', SysDept::STATUS_ENABLED)
            ->orderBy('sort');

        // 如果不是超级管理员，只查询当前租户的部门
        if (!$this->isSuperAdmin()) {
            $tenantId = $this->getCurrentTenantId();
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
        }

        $depts = $query->get()->toArray();

        return $this->buildSelectTree($depts, 0);
    }

    /**
     * 构建选择树结构
     *
     * @param array $list     部门列表
     * @param int   $parentId 父ID
     * @return array
     */
    protected function buildSelectTree(array $list, int $parentId = 0): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ((int)($item['parent_id'] ?? 0) === $parentId) {
                $children = $this->buildSelectTree($list, (int)$item['id']);
                $node = [
                    'id' => $item['id'],
                    'value' => $item['id'],
                    'label' => $item['name'],
                    'name' => $item['name'],
                    'code' => $item['code'] ?? '',
                ];
                if (!empty($children)) {
                    $node['children'] = $children;
                }
                $tree[] = $node;
            }
        }
        return $tree;
    }

    /**
     * 获取可访问的部门树（带 label 字段，适配前端 ElTree）
     *
     * @param int $parentId 父部门ID
     * @return array
     */
    public function getAccessDeptTree(int $parentId = 0): array
    {
        $query = SysDept::where('parent_id', $parentId)
            ->where('status', SysDept::STATUS_ENABLED)
            ->orderBy('sort');

        // 如果不是超级管理员，只查询当前租户的部门
        if (!$this->isSuperAdmin()) {
            $tenantId = $this->getCurrentTenantId();
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
        }

        $depts = $query->get();

        $tree = [];
        foreach ($depts as $dept) {
            $node = [
                'id'       => $dept->id,
                'value'    => $dept->id,
                'label'    => $dept->name,
                'name'     => $dept->name,
                'code'     => $dept->code,
                'parent_id'=> $dept->parent_id,
                'children' => $this->getAccessDeptTree($dept->id),
            ];
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * 获取部门详情
     *
     * @param int $deptId 部门ID
     * @return array|null
     */
    public function getDetail(int $deptId): ?array
    {
        $dept = SysDept::find($deptId);

        if (!$dept) {
            return null;
        }

        $data = $this->formatDept($dept);
        $data['path'] = $dept->getPath();

        // 获取子部门数量
        $data['children_count'] = SysDept::where('parent_id', $deptId)->count();

        // 获取部门用户数量
        $data['user_count'] = SysUser::where('dept_id', $deptId)->count();

        return $data;
    }

    /**
     * 创建部门
     *
     * @param array $data     部门数据
     * @param int   $operator 操作人ID
     * @return SysDept|null
     */
    public function create(array $data, int $operator = 0): ?SysDept
    {
        // 检查部门编码是否存在
        if ($this->deptDao->isDeptCodeExists($data['code'])) {
            throw new \Exception('部门编码已存在');
        }

        // 计算 level 字段：祖级列表
        $parentId = (int)($data['parent_id'] ?? 0);
        if ($parentId > 0) {
            $parentDept = SysDept::find($parentId);
            if ($parentDept) {
                $data['level'] = $parentDept->level . $parentId . ',';
            } else {
                $data['level'] = '0,';
            }
        } else {
            $data['level'] = '0,';
        }

        // 设置审计字段
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        return SysDept::create($data);
    }

    /**
     * 更新部门
     *
     * @param int   $deptId   部门ID
     * @param array $data     部门数据
     * @param int   $operator 操作人ID
     * @return bool
     */
    public function update(int $deptId, array $data, int $operator = 0): bool
    {
        $dept = SysDept::find($deptId);
        if (!$dept) {
            throw new \Exception('部门不存在');
        }

        // 检查部门编码是否重复
        if (isset($data['code']) && $data['code'] !== $dept->code) {
            if ($this->deptDao->isDeptCodeExists($data['code'], $deptId)) {
                throw new \Exception('部门编码已存在');
            }
        }

        // 检查父部门是否有效
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            if ($data['parent_id'] == $deptId) {
                throw new \Exception('父部门不能是自己');
            }

            // 检查父部门是否存在
            if (!SysDept::where('id', $data['parent_id'])->exists()) {
                throw new \Exception('父部门不存在');
            }
        }

        // 重新计算 level 字段（如果 parent_id 发生变化）
        if (isset($data['parent_id'])) {
            $parentId = (int)$data['parent_id'];
            if ($parentId > 0) {
                $parentDept = SysDept::find($parentId);
                if ($parentDept) {
                    $data['level'] = $parentDept->level . $parentId . ',';
                } else {
                    $data['level'] = '0,';
                }
            } else {
                $data['level'] = '0,';
            }
        }

        // 设置审计字段
        $data['updated_by'] = $operator;

        $dept->fill($data);
        return $dept->save();
    }

    /**
     * 删除部门
     *
     * @param int $deptId 部门ID
     * @return bool
     */
    public function delete(int|string $deptId): bool
    {
        $dept = SysDept::find($deptId);
        if (!$dept) {
            return false;
        }

        // 检查是否有子部门
        if ($dept->hasChildren()) {
            throw new \Exception('该部门下存在子部门，无法删除');
        }

        // 检查是否有用户
        if ($dept->hasUsers()) {
            throw new \Exception('该部门下存在用户，无法删除');
        }

        // 软删除部门
        return $dept->delete();
    }

    /**
     * 更新部门状态
     *
     * @param int $deptId 部门ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $deptId, int $status): bool
    {
        return $this->deptDao->updateStatus($deptId, $status);
    }

    /**
     * 获取所有子部门ID (包含自己)
     *
     * @param int $deptId 部门ID
     * @return array
     */
    public function getAllChildIds(int $deptId): array
    {
        return SysDept::getAllChildIds($deptId);
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化部门数据
     *
     * @param SysDept|array $dept 部门
     * @return array
     */
    protected function formatDept(SysDept|array $dept): array
    {
        if ($dept instanceof SysDept) {
            $data = $dept->toArray();
        } else {
            $data = $dept;
        }

        // 格式化时间
        if (isset($data['created_at'])) {
            $data['created_at'] = is_string($data['created_at'])
                ? $data['created_at']
                : $data['created_at']?->format('Y-m-d H:i:s');
        }

        if (isset($data['updated_at'])) {
            $data['updated_at'] = is_string($data['updated_at'])
                ? $data['updated_at']
                : $data['updated_at']?->format('Y-m-d H:i:s');
        }

        // 数据库值映射到字典值：DB 1=启用 0=禁用 → 字典 1=正常 2=停用
        if (isset($data['status'])) {
          //  $data['status'] = $data['status'] === 0 ? 2 : 1;
        }

        // 状态文本
        $data['status_text'] = $data['status'] === 1 ? '启用' : '禁用';

        return $data;
    }
}
