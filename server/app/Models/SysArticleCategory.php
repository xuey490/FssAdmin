<?php

declare(strict_types=1);

/**
 * 文章分类模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 
*/

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SysArticleCategory 文章分类模型
 *
 * @property int         $id         分类ID
 * @property string      $name       分类名称
 * @property string      $code       分类标识
 * @property int         $parent_id  父级分类ID
 * @property int         $sort       排序
 * @property int         $status     状态
 * @property string      $remark     备注
 * @property int         $created_by 创建人ID
 * @property int         $updated_by 更新人ID
 * @property \DateTime   $created_at 创建时间
 * @property \DateTime   $updated_at 更新时间
 * @property \DateTime   $deleted_at 删除时间
 *
 * @property-read SysArticle[] $articles 文章列表
 
 * @property mixed $tenant_id
 * @property mixed $create_time
 * @property mixed $update_time
 * @property mixed $delete_time
*/
class SysArticleCategory extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     * @return mixed
     */
    protected $table = 'sa_article_category';

    /**
     * 主键
     * @var string
     * @return mixed
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
     * @var array<int, string>
     * @return mixed
     */
    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'sort',
        'status',
        'remark',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array<string, string>
     * @return mixed
     */
    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
    ];

    /**
     * 分类下文章
     *
     * @return HasMany<SysArticle, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(SysArticle::class, 'category_id');
    }
}
