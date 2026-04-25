<?php

declare(strict_types=1);

/**
 * 定时任务服务
 *
 * @package App\Services
 */

namespace App\Services;

use App\Models\ToolCrontab;
use App\Models\ToolCrontabLog;
use Framework\Basic\BaseService;

class ToolCrontabService extends BaseService
{
    /**
     * 获取定时任务分页列表
     */
    public function getPageList(array $params): array
    {
        $page     = max(1, (int)($params['page'] ?? 1));
        $pageSize = max(1, (int)($params['limit'] ?? 20));

        $query = ToolCrontab::query();

        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        if (isset($params['type']) && $params['type'] !== '' && $params['type'] !== null) {
            $query->where('type', (int)$params['type']);
        }
        if (isset($params['status']) && $params['status'] !== '' && $params['status'] !== null) {
            $query->where('status', (int)$params['status']);
        }

        $orderField = in_array($params['orderField'] ?? '', ['id', 'create_time', 'update_time'])
            ? $params['orderField'] : 'id';
        $orderType  = strtolower($params['orderType'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $total = $query->count();
        $list  = $query->orderBy($orderField, $orderType)
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();

        return ['list' => $list, 'total' => $total, 'page' => $page, 'size' => $pageSize];
    }

    /**
     * 获取任务详情
     */
    public function getDetail(int $id): ?array
    {
        $crontab = ToolCrontab::find($id);
        return $crontab ? $crontab->toArray() : null;
    }

    /**
     * 创建定时任务
     */
    public function create(array $data, int $operator = 0): ToolCrontab
    {
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;
        return ToolCrontab::create($data);
    }

    /**
     * 更新定时任务
     */
    public function update(int $id, array $data, int $operator = 0): bool
    {
        $crontab = ToolCrontab::find($id);
        if (!$crontab) {
            throw new \Exception('任务不存在');
        }
        $data['updated_by'] = $operator;
        $crontab->fill($data);
        return $crontab->save();
    }

    /**
     * 删除定时任务（支持批量）
     */
    public function delete(array $ids): int
    {
        return ToolCrontab::whereIn('id', $ids)->delete();
    }

    /**
     * 执行定时任务
     */
    public function run(int $id): array
    {
        $crontab = ToolCrontab::find($id);
        if (!$crontab) {
            throw new \Exception('任务不存在');
        }

        $startTime = microtime(true);
        $status    = ToolCrontabLog::STATUS_SUCCESS;
        $exception = '';

        try {
            switch ($crontab->type) {
                case ToolCrontab::TYPE_URL_GET:
                    $this->executeUrlGet($crontab->target, $crontab->parameter);
                    break;
                case ToolCrontab::TYPE_URL_POST:
                    $this->executeUrlPost($crontab->target, $crontab->parameter);
                    break;
                case ToolCrontab::TYPE_CLASS:
                    $this->executeClass($crontab->target, $crontab->parameter);
                    break;
                default:
                    throw new \Exception('未知任务类型: ' . $crontab->type);
            }
        } catch (\Throwable $e) {
            $status    = ToolCrontabLog::STATUS_FAIL;
            $exception = $e->getMessage();
        }

        // 记录执行日志
        ToolCrontabLog::create([
            'crontab_id'     => $crontab->id,
            'name'           => $crontab->name,
            'target'         => $crontab->target,
            'parameter'      => $crontab->parameter,
            'exception_info' => $exception,
            'status'         => $status,
        ]);

        $elapsed = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'status'    => $status,
            'elapsed'   => $elapsed . 'ms',
            'exception' => $exception,
        ];
    }

    /**
     * 执行 URL GET 请求
     */
    protected function executeUrlGet(string $url, ?string $parameter): void
    {
        $params = [];
        if (!empty($parameter)) {
            $decoded = json_decode($parameter, true);
            if (is_array($decoded)) {
                $params = $decoded;
            }
        }
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new \RuntimeException("GET 请求失败: {$error}");
        }
    }

    /**
     * 执行 URL POST 请求
     */
    protected function executeUrlPost(string $url, ?string $parameter): void
    {
        $params = [];
        if (!empty($parameter)) {
            $decoded = json_decode($parameter, true);
            if (is_array($decoded)) {
                $params = $decoded;
            }
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($params),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new \RuntimeException("POST 请求失败: {$error}");
        }
    }

    /**
     * 执行类任务
     */
    protected function executeClass(string $target, ?string $parameter): void
    {
        // 将反斜杠路径转为命名空间
        $className = str_replace('/', '\\', $target);

        if (!class_exists($className)) {
            throw new \RuntimeException("类不存在: {$className}");
        }

        $params = [];
        if (!empty($parameter)) {
            $decoded = json_decode($parameter, true);
            if (is_array($decoded)) {
                $params = $decoded;
            }
        }

        $instance = new $className();
        if (!method_exists($instance, 'execute')) {
            throw new \RuntimeException("类 {$className} 未实现 execute() 方法");
        }
        $instance->execute($params);
    }

    // ==================== 日志相关 ====================

    /**
     * 获取执行日志分页列表
     */
    public function getLogPageList(array $params): array
    {
        $page     = max(1, (int)($params['page'] ?? 1));
        $pageSize = max(1, (int)($params['limit'] ?? 20));

        $query = ToolCrontabLog::query();

        if (!empty($params['crontab_id'])) {
            $query->where('crontab_id', (int)$params['crontab_id']);
        }
        if (!empty($params['create_time']) && is_array($params['create_time'])) {
            if (!empty($params['create_time'][0])) {
                $query->where('create_time', '>=', $params['create_time'][0]);
            }
            if (!empty($params['create_time'][1])) {
                $query->where('create_time', '<=', $params['create_time'][1]);
            }
        }

        $orderType = strtolower($params['orderType'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $total = $query->count();
        $list  = $query->orderBy('create_time', $orderType)
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();

        return ['list' => $list, 'total' => $total, 'page' => $page, 'size' => $pageSize];
    }

    /**
     * 删除执行日志（支持批量）
     */
    public function deleteLog(array $ids): int
    {
        return ToolCrontabLog::whereIn('id', $ids)->delete();
    }
}
