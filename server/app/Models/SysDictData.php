<?php

declare(strict_types=1);

/**
 * 数据字典数据模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SysDictData 数据字典数据模型
 *
 * @property int         $id           字典数据ID
 * @property int         $dict_type_id 字典类型ID
 * @property string      $dict_label   字典标签
 * @property string      $dict_value   字典键值
 * @property int         $dict_sort    字典排序
 * @property string      $color        样式颜色
 * @property int         $status       状态
 * @property string      $remark       备注
 * @property int         $created_by   创建人ID
 * @property int         $updated_by   更新人ID
 * @property \DateTime   $created_at   创建时间
 * @property \DateTime   $updated_at   更新时间
 * @property \DateTime   $deleted_at   删除时间
 *
 * @property-read SysDictType $dictType 字典类型
 */
class SysDictData extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_dict_data';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'type_id',
        'label',
        'value',
        'code',
        'sort',
        'color',
        'status',
        'remark',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'type_id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    // ==================== 状态常量 ====================

    /** @var int 禁用状态 */
    public const STATUS_DISABLED = 0;

    /** @var int 启用状态 */
    public const STATUS_ENABLED = 1;

    // ==================== 颜色常量 ====================

    /** @var string 主要颜色 */
    public const COLOR_PRIMARY = 'primary';

    /** @var string 成功颜色 */
    public const COLOR_SUCCESS = 'success';

    /** @var string 警告颜色 */
    public const COLOR_WARNING = 'warning';

    /** @var string 危险颜色 */
    public const COLOR_DANGER = 'danger';

    /** @var string 信息颜色 */
    public const COLOR_INFO = 'info';

    // ==================== 关联关系 ====================

    /**
     * 所属字典类型
     *
     * @return BelongsTo
     */
    public function dictType(): BelongsTo
    {
        return $this->belongsTo(SysDictType::class, 'type_id', 'id');
    }

    // ==================== 业务方法 ====================

    /**
     * 检查是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 获取可用颜色列表
     *
     * @return array
     */
    public static function getAvailableColors(): array
    {
        return [
            self::COLOR_PRIMARY => '主要(蓝色)',
            self::COLOR_SUCCESS => '成功(绿色)',
            self::COLOR_WARNING => '警告(橙色)',
            self::COLOR_DANGER => '危险(红色)',
            self::COLOR_INFO => '信息(灰色)',
        ];
    }
}
