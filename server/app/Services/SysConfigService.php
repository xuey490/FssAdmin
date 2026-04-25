<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SysConfig;
use Framework\Basic\BaseService;

class SysConfigService extends BaseService
{
    /**
     * 获取配置项列表
     */
    public function getList(array $params): array
    {
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, (int)($params['limit'] ?? 60));
        $groupId = $params['group_id'] ?? '';
        $configKey = $params['key'] ?? '';
        $configName = $params['name'] ?? '';

        $query = SysConfig::query();

        if ($groupId !== '') {
            $query->where('group_id', (int)$groupId);
        }

        if ($configKey !== '') {
            $query->where('key', 'like', "%{$configKey}%");
        }

        if ($configName !== '') {
            $query->where('name', 'like', "%{$configName}%");
        }

        $total = $query->count();
        $list = $query->orderBy('sort', 'asc')
            ->where('delete_time', null)
            ->orderBy('id', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                // 转换 JSON 格式的 config_select_data 供前端 ElSelect 等组件使用
                if (!empty($item->config_select_data)) {
                    $item->config_select_data = json_decode($item->config_select_data, true) ?: [];
                } else {
                    $item->config_select_data = [];
                }
                return $item;
            })
            ->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'size' => $limit,
        ];
    }

    /**
     * 获取配置项详情
     */
    public function getDetail(int $id): ?array
    {
        $config = SysConfig::find($id);
        return $config ? $config->toArray() : null;
    }

    /**
     * 保存配置项
     */
    public function save(array $data, int $operator): mixed
    {
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        if (isset($data['config_select_data']) && is_array($data['config_select_data'])) {
            $data['config_select_data'] = json_encode($data['config_select_data'], JSON_UNESCAPED_UNICODE);
        }

        $config = SysConfig::create($data);
        $this->clearConfigCache($data['key']);

        return $config->id;
    }

    /**
     * 更新配置项
     */
    public function update(int $id, array $data, int $operator): bool
    {
        $config = SysConfig::find($id);
        if (!$config) {
            throw new \Exception('配置项不存在');
        }

        if (isset($data['config_select_data']) && is_array($data['config_select_data'])) {
            $data['config_select_data'] = json_encode($data['config_select_data'], JSON_UNESCAPED_UNICODE);
        }

        $data['updated_by'] = $operator;
        $config->fill($data);
        $result = $config->save();

        $this->clearConfigCache($config->key);

        return $result;
    }

    /**
     * 删除配置项
     */
    public function delete(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $config = SysConfig::find($id);
            if ($config) {
                $this->clearConfigCache($config->key);
                $config->delete();
                $count++;
            }
        }
        return $count;
    }

    /**
     * 根据配置键获取配置值
     */
    public function getByKey(string $key): mixed
    {
        $cacheKey = "config:{$key}";
        $cached = app('cache')->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $config = SysConfig::where('key', $key)->first();
        $value = $config ? $config->value : null;

        app('cache')->set($cacheKey, $value, 3600);

        return $value;
    }

    /**
     * 批量更新配置值
     */
    public function batchUpdate(array $configs, int $operator): bool
    {
        foreach ($configs as $item) {
            if (isset($item['id']) && isset($item['value'])) {
                $config = SysConfig::find($item['id']);
                if ($config) {
                    $config->value = $item['value'];
                    $config->updated_by = $operator;
                    $config->save();
                    $this->clearConfigCache($config->key);
                }
            }
        }
        return true;
    }

    /**
     * 清除配置缓存
     */
    protected function clearConfigCache(string $key): void
    {
        app('cache')->delete("config:{$key}");
    }
}
