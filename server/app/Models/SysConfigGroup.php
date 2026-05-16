<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SysConfigGroup 系统配置组模型
 *
 * @property int         $id             配置组ID
 * @property string      $group_name     配置组名称
 * @property string      $group_code     配置组编码
 * @property int         $sort           排序
 * @property string      $remark         备注
 * @property int         $created_by     创建人ID
 * @property int         $updated_by     更新人ID
 * @property \DateTime   $created_at     创建时间
 * @property \DateTime   $updated_at     更新时间
 */
class SysConfigGroup extends BaseLaORMModel
{
    use SoftDeletes;

    protected $table = 'sa_system_config_group';
    protected $primaryKey = 'id';
    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $fillable = [
        'name',
        'code',
        'sort',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'sort' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    /**
     * 配置组下的配置项
     */
    public function configs(): HasMany
    {
        return $this->hasMany(SysConfig::class, 'group_id', 'id');
    }
}
