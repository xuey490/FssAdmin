<?php

declare(strict_types=1);

/**
 * 代码生成业务表 Model
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-29
 
 * @property mixed $table_name
 * @property mixed $table_comment
 * @property mixed $stub
 * @property mixed $template
 * @property mixed $namespace
 * @property mixed $package_name
 * @property mixed $business_name
 * @property mixed $class_name
 * @property mixed $menu_name
 * @property int $belong_menu_id
 * @property mixed $tpl_category
 * @property int $generate_type
 * @property mixed $generate_path
 * @property int $generate_model
 * @property mixed $generate_menus
 * @property int $build_menu
 * @property int $component_type
 * @property mixed $options
 * @property int $form_width
 * @property int $is_full
 * @property mixed $remark
 * @property mixed $source
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

/**
 * @package App\Models
 * @property mixed $table_name
 */
class ToolGenerateTable extends BaseLaORMModel
{
    use SoftDeletes;

    // ========== 基础配置 ==========

    /**
     * @return mixed
     */
    protected $table = 'sa_tool_generate_tables';

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
        'table_name',
        'table_comment',
        'stub',
        'template',
        'namespace',
        'package_name',
        'business_name',
        'class_name',
        'menu_name',
        'belong_menu_id',
        'tpl_category',
        'generate_type',
        'generate_path',
        'generate_model',
        'generate_menus',
        'build_menu',
        'component_type',
        'options',
        'form_width',
        'is_full',
        'remark',
        'source',
        'created_by',
        'updated_by',
    ];

    // ========== 类型转换 ==========

    /** @var array<string, string> */
    protected $casts = [
        'id'             => 'integer',
        'belong_menu_id' => 'integer',
        'generate_type'  => 'integer',
        'generate_model' => 'integer',
        'build_menu'     => 'integer',
        'component_type' => 'integer',
        'form_width'     => 'integer',
        'is_full'        => 'integer',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'create_time'    => 'datetime',
        'update_time'    => 'datetime',
        'delete_time'    => 'datetime',
    ];

    // ========== 生成类型常量 ==========

    /** 压缩包下载 */
    const GENERATE_TYPE_ZIP  = 1;
    /** 生成到模块 */
    const GENERATE_TYPE_FILE = 2;

    /** 软删除模型 */
    const GENERATE_MODEL_SOFT   = 1;
    /** 非软删除模型 */
    const GENERATE_MODEL_NORMAL = 2;

    /** 单表CRUD */
    const TPL_CATEGORY_SINGLE = 'single';
    /** 树表CRUD */
    const TPL_CATEGORY_TREE   = 'tree';

    // ========== 关联关系 ==========

    /**
     * 关联字段配置（一对多）
     */
    public function columns(): mixed
    {
        return $this->hasMany(ToolGenerateColumn::class, 'table_id', 'id');
    }
}