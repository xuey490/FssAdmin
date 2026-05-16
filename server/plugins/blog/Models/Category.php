<?php

declare(strict_types=1);

/**
 * 博客分类模型
 *
 * @package Plugins\Blog\Models
 */

namespace Plugins\Blog\Models;

use Framework\Utils\BaseModel;

/**
 * 分类模型
 */
class Category extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'blog_categories';

    /**
     * 可批量赋值的字段
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
    ];

    /**
     * 字段类型转换
     *
     * @var array
     */
    protected $casts = [
        'parent_id' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * 关联文章
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'category_id', 'id');
    }

    /**
     * 关联父分类
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    /**
     * 关联子分类
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
