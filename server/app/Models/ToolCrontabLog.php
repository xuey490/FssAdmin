<?php

declare(strict_types=1);

/**
 * 定时任务执行日志模型
 *
 * @package App\Models
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ToolCrontabLog 定时任务执行日志模型
 *
 * @property int         $id             主键
 * @property int|null    $crontab_id     任务ID
 * @property string|null $name           任务名称
 * @property string|null $target         调用目标
 * @property string|null $parameter      调用参数
 * @property string|null $exception_info 异常信息
 * @property int         $status         执行状态 (1成功 2失败)
 * @property string|null $create_time    创建时间
 * @property string|null $update_time    修改时间
 * @property string|null $delete_time    删除时间
 */
class ToolCrontabLog extends BaseLaORMModel
{
    use SoftDeletes;

    protected $table = 'sa_tool_crontab_log';

    protected $primaryKey = 'id';

    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'crontab_id', 'name', 'target', 'parameter',
        'exception_info', 'status', 'create_time', 'update_time', 'delete_time',
    ];

    protected $casts = [
        'id'         => 'integer',
        'crontab_id' => 'integer',
        'status'     => 'integer',
    ];

    /** 执行状态：成功 */
    public const STATUS_SUCCESS = 1;
    /** 执行状态：失败 */
    public const STATUS_FAIL = 2;

    /**
     * 所属任务
     */
    public function crontab(): BelongsTo
    {
        return $this->belongsTo(ToolCrontab::class, 'crontab_id', 'id');
    }
}
