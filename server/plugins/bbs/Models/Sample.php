<?php

declare(strict_types=1);

namespace Plugins\Bbs\Models;

use Framework\Utils\BaseModel;

/**
 * 示例模型
 *
 * 请根据实际需求修改此模型。
 */
class Sample extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'bbs_samples';

    /**
     * 可批量赋值的字段
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * 字段类型转换
     *
     * @var array
     */
    protected $casts = [
        'status' => 'integer',
    ];
}