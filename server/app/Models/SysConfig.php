<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SysConfig 系统配置项模型
 *
 * @property int         $id             配置ID
 * @property int         $group_id       配置组ID
 * @property string      $key            配置键
 * @property string      $value          配置值
 * @property string      $name           配置名称
 * @property string      $input_type     输入类型
 * @property string      $config_select_data 配置选项
 * @property int         $sort           排序
 * @property string      $remark         备注
 * @property int         $created_by     创建人ID
 * @property int         $updated_by     更新人ID
 * @property \DateTime   $created_at     创建时间
 * @property \DateTime   $updated_at     更新时间
 */
class SysConfig extends BaseLaORMModel
{
    use SoftDeletes;

    protected $table = 'sa_system_config';
    protected $primaryKey = 'id';
    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $fillable = [
        'group_id',
        'key',
        'value',
        'name',
        'input_type',
        'config_select_data',
        'sort',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'group_id' => 'integer',
        'sort' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    /**
     * 所属配置组
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SysConfigGroup::class, 'group_id', 'id');
    }
}
