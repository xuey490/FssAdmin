<?php

declare(strict_types=1);

/**
 * 定时任务日志DAO
 *
 * @package App\Dao
 */

namespace App\Dao;

use App\Models\ToolCrontabLog;
use Framework\Basic\BaseDao;

class ToolCrontabLogDao extends BaseDao
{
    protected function setModel(): string
    {
        return ToolCrontabLog::class;
    }
}
