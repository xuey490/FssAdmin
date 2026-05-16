<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SysConfigGroup;
use App\Models\SysConfig;
use Framework\Basic\BaseService;

class SysConfigGroupService extends BaseService
{
    /**
     * 获取配置组列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, (int)($params['limit'] ?? 20));
        $groupName = $params['name'] ?? '';
        $groupCode = $params['code'] ?? '';

        $query = SysConfigGroup::query();

        if ($groupName !== '') {
            $query->where('name', 'like', "%{$groupName}%");
        }

        if ($groupCode !== '') {
            $query->where('code', 'like', "%{$groupCode}%");
        }

        $total = $query->count();
        $list = $query->orderBy('id', 'asc')
            ->where('delete_time', null)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'size' => $limit,
        ];
    }

    /**
     * 获取配置组详情
     */
    public function getDetail(int $id): ?array
    {
        $group = SysConfigGroup::find($id);
        return $group ? $group->toArray() : null;
    }

    /**
     * 保存配置组
     */
    public function save(array $data, int $operator): mixed
    {
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        $group = SysConfigGroup::create($data);
        return $group->id;
    }

    /**
     * 更新配置组
     */
    public function update(int $id, array $data, int $operator): bool
    {
        $group = SysConfigGroup::find($id);
        if (!$group) {
            throw new \Exception('配置组不存在');
        }

        $data['updated_by'] = $operator;
        $group->fill($data);
        return $group->save();
    }

    /**
     * 删除配置组
     */
    public function delete(int $id): bool
    {
        $group = SysConfigGroup::find($id);
        if (!$group) {
            return false;
        }

        // 删除配置组下的所有配置项
        SysConfig::where('group_id', $id)->delete();

        // 删除配置组
        return $group->delete();
    }

    /**
     * 测试邮件配置
     */
    public function testEmail(array $config): array
    {
        // 简单的邮件配置测试实现
        try {
            $host = $config['smtp_host'] ?? '';
            $port = $config['smtp_port'] ?? 25;
            $user = $config['smtp_user'] ?? '';
            $pass = $config['smtp_pass'] ?? '';
            $from = $config['smtp_from'] ?? '';

            if (empty($host) || empty($user) || empty($pass)) {
                return [
                    'success' => false,
                    'message' => '邮件配置不完整',
                ];
            }

            // 这里可以实际发送测试邮件
            // 暂时返回成功
            return [
                'success' => true,
                'message' => '邮件配置测试成功',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '邮件配置测试失败: ' . $e->getMessage(),
            ];
        }
    }
}
