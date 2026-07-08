<?php

declare(strict_types=1);

/**
 * 代码生成业务字段表 Model
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-29
 
 * @property int $table_id
 * @property mixed $column_name
 * @property mixed $column_comment
 * @property mixed $column_type
 * @property mixed $default_value
 * @property int $is_pk
 * @property int $is_required
 * @property int $is_insert
 * @property int $is_edit
 * @property int $is_list
 * @property int $is_query
 * @property int $is_sort
 * @property mixed $query_type
 * @property mixed $view_type
 * @property mixed $dict_type
 * @property mixed $allow_roles
 * @property mixed $options
 * @property int $sort
 * @property mixed $remark
 * @property int $created_by
 * @property int $updated_by
 * @property int $id
 * @property string $create_time
 * @property string $update_time
 * @property string $delete_time
 * @property mixed $tenant_id
 * @property mixed $status
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $deleted_at
*/

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class ToolGenerateColumn extends BaseLaORMModel
{
    //use SoftDeletes;

    // ========== 基础配置 ==========

    /**
     * @return mixed
     */
    protected $table = 'sa_tool_generate_columns';

    /**
     * @return mixed
     */
    public $incrementing = true;

    /**
     * @return mixed
     */
    protected $keyType = 'int';

    /**
     * @return mixed
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    // ========== 可填字段 ==========

    /**
     * @return mixed
     */
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

    /** @var array<string, string> */
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
    public function table(): mixed
    {
        return $this->belongsTo(ToolGenerateTable::class, 'table_id', 'id');
    }
}