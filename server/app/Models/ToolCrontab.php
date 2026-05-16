<?php

declare(strict_types=1);

/**
 * 定时任务模型
 *
 * @package App\Models
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ToolCrontab 定时任务模型
 *
 * @property int         $id          主键
 * @property string|null $name        任务名称
 * @property int         $type        任务类型 (1=URL GET 2=URL POST 3=类任务)
 * @property string|null $target      调用目标
 * @property string|null $parameter   调用参数
 * @property int|null    $task_style  执行类型
 * @property string|null $rule        定时表达式
 * @property int         $singleton   是否单次执行 (1是 2否)
 * @property int         $status      状态 (1正常 2停用)
 * @property string|null $remark      备注
 * @property int|null    $created_by  创建者
 * @property int|null    $updated_by  更新者
 * @property string|null $create_time 创建时间
 * @property string|null $update_time 修改时间
 * @property string|null $delete_time 删除时间
 */
class ToolCrontab extends BaseLaORMModel
{
    use SoftDeletes;

    protected $table = 'sa_tool_crontab';

    protected $primaryKey = 'id';

    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'name', 'type', 'target', 'parameter', 'task_style',
        'rule', 'singleton', 'status', 'remark',
        'created_by', 'updated_by', 'create_time', 'update_time', 'delete_time',
    ];

    protected $casts = [
        'id'         => 'integer',
        'type'       => 'integer',
        'task_style' => 'integer',
        'singleton'  => 'integer',
        'status'     => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /** 状态：正常 */
    public const STATUS_NORMAL = 1;
    /** 状态：停用 */
    public const STATUS_DISABLED = 2;

    /** 任务类型：URL GET */
    public const TYPE_URL_GET = 1;
    /** 任务类型：URL POST */
    public const TYPE_URL_POST = 2;
    /** 任务类型：类任务 */
    public const TYPE_CLASS = 3;

    /**
     * 执行日志关联
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ToolCrontabLog::class, 'crontab_id', 'id');
    }
}
