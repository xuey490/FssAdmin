<?php

declare(strict_types=1);

/**
 * 数据字典服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysDictType;
use App\Models\SysDictData;
use App\Dao\SysDictTypeDao;
use App\Dao\SysDictDataDao;
use Framework\Basic\BaseService;


/**
 * SysDictService 数据字典服务
 */
class SysDictService extends BaseService
{
    /**
     * 字典类型DAO
     * @var SysDictTypeDao
     */
    protected SysDictTypeDao $dictTypeDao;

    /**
     * 字典数据DAO
     * @var SysDictDataDao
     */
    protected SysDictDataDao $dictDataDao;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->dictTypeDao = new SysDictTypeDao();
        $this->dictDataDao = new SysDictDataDao();
    }

    // ==================== 字典类型管理 ====================

    /**
     * 获取字典类型列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getTypeList(array $params): array
    {
        $page  = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $name  = $params['name'] ?? '';
        $code  = $params['code'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysDictType::query();

        if ($name !== '') {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($code !== '') {
            $query->where('code', 'like', "%{$code}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $total = $query->count();
        $list  = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'size'  => $limit,
        ];
    }

    /**
     * 获取字典类型详情
     *
     * @param int $id 字典类型ID
     * @return array|null
     */
    public function getTypeDetail(int $id): ?array
    {
        $dictType = SysDictType::find($id);
        return $dictType ? $dictType->toArray() : null;
    }

    /**
     * 创建字典类型
     *
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return SysDictType|null
     */
    public function createType(array $data, int $operator = 0): ?SysDictType
    {
        // 检查编码是否存在
        if ($this->dictTypeDao->isDictCodeExists($data['code'])) {
            throw new \Exception('字典编码已存在');
        }

        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        $dictType = SysDictType::create($data);
        $this->clearDictCache($data['code']);

        return $dictType;
    }

    /**
     * 更新字典类型
     *
     * @param int   $id       字典类型ID
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return bool
     */
    public function updateType(int $id, array $data, int $operator = 0): bool
    {
        $dictType = SysDictType::find($id);
        if (!$dictType) {
            throw new \Exception('字典类型不存在');
        }

        // 检查编码是否重复
        if (isset($data['code']) && $data['code'] !== $dictType->code) {
            if ($this->dictTypeDao->isDictCodeExists($data['code'], $id)) {
                throw new \Exception('字典编码已存在');
            }
        }

        $data['updated_by'] = $operator;
        $dictType->fill($data);
        $result = $dictType->save();

        $this->clearDictCache($dictType->code);

        return $result;
    }

    /**
     * 删除字典类型
     *
     * @param int $id 字典类型ID
     * @return bool
     */
    public function deleteType(int|string $id): bool
    {
        $dictType = SysDictType::find($id);
        if (!$dictType) {
            return false;
        }

        // 删除字典数据
        SysDictData::where('type_id', $id)->delete();

        $code = $dictType->code;

        // 删除字典类型
        $dictType->delete();

        $this->clearDictCache($code);

        return true;
    }

    /**
     * 更新字典类型状态
     *
     * @param int $id     字典类型ID
     * @param int $status 状态
     * @return bool
     */
    public function updateTypeStatus(int $id, int $status): bool
    {
        $dictType = SysDictType::find($id);
        if (!$dictType) {
            return false;
        }

        $dictType->status = $status;
        $result = $dictType->save();

        $this->clearDictCache($dictType->code);

        return $result;
    }

    // ==================== 字典数据管理 ====================

    /**
     * 获取字典数据列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getDataList(array $params): array
    {
        $page   = (int)($params['page'] ?? 1);
        $limit  = (int)($params['limit'] ?? 20);
        $typeId = $params['type_id'] ?? '';
        $label  = $params['label'] ?? '';
        $value  = $params['value'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysDictData::query();

        if ($typeId !== '') {
            $query->where('type_id', (int)$typeId);
        }

        if ($label !== '') {
            $query->where('label', 'like', "%{$label}%");
        }

        if ($value !== '') {
            $query->where('value', 'like', "%{$value}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $total = $query->count();
        $list  = $query->orderBy('sort')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'size'  => $limit,
        ];
    }

    /**
     * 获取字典数据详情
     *
     * @param int $id 字典数据ID
     * @return array|null
     */
    public function getDataDetail(int $id): ?array
    {
        $dictData = SysDictData::find($id);
        return $dictData ? $dictData->toArray() : null;
    }

    /**
     * 创建字典数据
     *
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return SysDictData|null
     */
    public function createData(array $data, int $operator = 0): ?SysDictData
    {
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        $dictData = SysDictData::create($data);

        // 清除缓存
        $dictType = SysDictType::find($data['type_id']);
        if ($dictType) {
            $this->clearDictCache($dictType->code);
        }

        return $dictData;
    }

    /**
     * 更新字典数据
     *
     * @param int   $id       字典数据ID
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return bool
     */
    public function updateData(int $id, array $data, int $operator = 0): bool
    {
        $dictData = SysDictData::find($id);
        if (!$dictData) {
            throw new \Exception('字典数据不存在');
        }

        $data['updated_by'] = $operator;
        $dictData->fill($data);
        $result = $dictData->save();

        // 清除缓存
        $dictType = SysDictType::find($dictData->type_id);
        if ($dictType) {
            $this->clearDictCache($dictType->code);
        }

        return $result;
    }

    /**
     * 批量删除字典数据
     *
     * @param array $ids 字典数据ID列表
     * @return int 删除数量
     */
    public function batchDeleteData(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            if ($this->deleteData($id)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 删除字典数据
     *
     * @param int $id 字典数据ID
     * @return bool
     */
    public function deleteData(int|string $id): bool
    {
        $dictData = SysDictData::find($id);
        if (!$dictData) {
            return false;
        }

        $dictTypeId = $dictData->type_id;
        $dictData->delete();

        // 清除缓存
        $dictType = SysDictType::find($dictTypeId);
        if ($dictType) {
            $this->clearDictCache($dictType->code);
        }

        return true;
    }

    /**
     * 更新字典数据状态
     *
     * @param int $id     字典数据ID
     * @param int $status 状态
     * @return bool
     */
    public function updateDataStatus(int $id, int $status): bool
    {
        $dictData = SysDictData::find($id);
        if (!$dictData) {
            return false;
        }

        $dictData->status = $status;
        $result = $dictData->save();

        // 清除缓存
        $dictType = SysDictType::find($dictData->type_id);
        if ($dictType) {
            $this->clearDictCache($dictType->code);
        }

        return $result;
    }

    // ==================== 字典获取 ====================

    /**
     * 根据字典编码获取字典数据
     *
     * @param string $dictCode 字典编码
     * @return array
     */
    public function getDictDataByCode(string $dictCode): array
    {
        $cacheKey = "dict:{$dictCode}";
        $cached = app('cache')->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $data = SysDictType::getDataByCode($dictCode);
        app('cache')->set($cacheKey, $data, 3600);

        return $data;
    }

    /**
     * 根据字典编码获取字典标签
     *
     * @param string $dictCode  字典编码
     * @param string $dictValue 字典值
     * @return string
     */
    public function getDictLabel(string $dictCode, string $dictValue): string
    {
        $data = $this->getDictDataByCode($dictCode);

        foreach ($data as $item) {
            if ($item['value'] === $dictValue) {
                return $item['label'];
            }
        }

        return '';
    }

    /**
     * 清除字典缓存
     *
     * @param string $dictCode 字典编码
     * @return void
     */
    protected function clearDictCache(string $dictCode): void
    {
        app('cache')->delete("dict:{$dictCode}");
    }

    /**
     * 获取所有字典数据
     *
     * @return array
     */
    public function getAllData(): array
    {
        $dictTypes = SysDictType::query()
            ->where('status', 1)
            ->get()
            ->toArray();

        $result = [];
        foreach ($dictTypes as $dictType) {
            $dictDataList = SysDictData::query()
                ->where('type_id', $dictType['id'])
                ->where('status', 1)
                ->orderBy('sort')
                ->get()
                ->toArray();

            // 格式化为前端期望的 DictItem[] 结构：{ id, label, value, color }
            $items = [];
            foreach ($dictDataList as $item) {
                $items[] = [
                    'id'       => $item['id'],
                    'label'    => $item['label'],
                    'value'    => $item['value'],
                    'color'    => $item['color'] ?? '',
                    'disabled' => $item['status'] != 1,
                ];
            }

            $result[$dictType['code']] = $items;
        }

        return $result;
    }
}
