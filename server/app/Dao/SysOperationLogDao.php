<?php

declare(strict_types=1);

/**
 * 操作日志DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Dao;

use App\Models\SysOperationLog;
use Framework\Basic\BaseDao;

/**
 * SysOperationLogDao 操作日志数据访问层
 */
class SysOperationLogDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysOperationLog::class;
    }

    /**
     * 获取用户操作日志列表
     *
     * @param int   $userId 用户ID
     * @param int   $page   页码
     * @param int   $limit  每页数量
     * @return array
     */
    public function getListByUserId(int $userId, int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['created_by' => $userId], '*', $page, $limit, 'create_time desc')->toArray();
    }

    /**
     * 获取模块操作日志列表
     *
     * @param string $module 模块名称
     * @param int    $page   页码
     * @param int    $limit  每页数量
     * @return array
     */
    public function getListByModule(string $module, int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['app' => $module], '*', $page, $limit, 'create_time desc')->toArray();
    }

    /**
     * 统计用户操作次数
     *
     * @param int $userId 用户ID
     * @return int
     */
    public function countByUserId(int $userId): int
    {
        return $this->count(['created_by' => $userId]);
    }

    /**
     * 统计今日操作次数
     *
     * @return int
     */
    public function countToday(): int
    {
        $today = date('Y-m-d');
        return $this->count([
            ['create_time', '>=', $today . ' 00:00:00'],
            ['create_time', '<=', $today . ' 23:59:59'],
        ]);
    }

    /**
     * 获取今日操作日志
     *
     * @param int $page  页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getTodayList(int $page = 1, int $limit = 20): array
    {
        $today = date('Y-m-d');
        return $this->selectList([
            ['create_time', '>=', $today . ' 00:00:00'],
            ['create_time', '<=', $today . ' 23:59:59'],
        ], '*', $page, $limit, 'create_time desc')->toArray();
    }
}
