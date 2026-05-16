<?php

declare(strict_types=1);

/**
 * 博客标签模型
 *
 * @package Plugins\Blog\Models
 */

namespace Plugins\Blog\Models;

use Framework\Utils\BaseModel;

/**
 * 标签模型
 */
class Tag extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'blog_tags';

    /**
     * 可批量赋值的字段
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * 关联文章
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany(
            Post::class,
            'blog_post_tags',
            'tag_id',
            'post_id'
        );
    }
}
