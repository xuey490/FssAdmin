<?php

declare(strict_types=1);

/**
 * 代码生成业务字段表 Model
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-29
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ToolGenerateColumn extends BaseLaORMModel
{
    //use SoftDeletes;

    // ========== 基础配置 ==========

    protected $table = 'sa_tool_generate_columns';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    // ========== 可填字段 ==========

    protected $fillable = [
        'table_id',
        'column_name',
        'column_comment',
        'column_type',
        'default_value',
        'is_pk',
        'is_required',
        'is_insert',
        'is_edit',
        'is_list',
        'is_query',
        'is_sort',
        'query_type',
        'view_type',
        'dict_type',
        'allow_roles',
        'options',
        'sort',
        'remark',
        'created_by',
        'updated_by',
    ];

    // ========== 类型转换 ==========

    protected $casts = [
        'id'         => 'integer',
        'table_id'   => 'integer',
        'is_pk'      => 'integer',
        'is_required'=> 'integer',
        'is_insert'  => 'integer',
        'is_edit'    => 'integer',
        'is_list'    => 'integer',
        'is_query'   => 'integer',
        'is_sort'    => 'integer',
        'sort'       => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time'=> 'datetime',
        'update_time'=> 'datetime',
        'delete_time'=> 'datetime',
    ];

    // ========== 常量定义 ==========

    /** 非主键 */
    const IS_PK_NO  = 1;
    /** 是主键 */
    const IS_PK_YES = 2;

    /** 非必填 */
    const REQUIRED_NO  = 1;
    /** 必填 */
    const REQUIRED_YES = 2;

    /** 不参与 */
    const FLAG_NO  = 1;
    /** 参与 */
    const FLAG_YES = 2;

    // ========== 关联关系 ==========

    /**
     * 所属业务表
     */
    public function table()
    {
        return $this->belongsTo(ToolGenerateTable::class, 'table_id', 'id');
    }
}
