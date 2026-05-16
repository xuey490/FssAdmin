<?php

declare(strict_types=1);

/**
 * 定时任务DAO
 *
 * @package App\Dao
 */

namespace App\Dao;

use App\Models\ToolCrontab;
use Framework\Basic\BaseDao;

class ToolCrontabDao extends BaseDao
{
    protected function setModel(): string
    {
        return ToolCrontab::class;
    }
}
