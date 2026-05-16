<?php

declare(strict_types=1);

/**
 * 博客文章模型
 *
 * @package Plugins\Blog\Models
 */

namespace Plugins\Blog\Models;

use Framework\Utils\BaseModel;

/**
 * 文章模型
 */
class Post extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'blog_posts';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 可批量赋值的字段
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'category_id',
        'author_id',
        'status',
        'view_count',
        'is_top',
        'published_at',
    ];

    /**
     * 字段类型转换
     *
     * @var array
     */
    protected $casts = [
        'category_id' => 'integer',
        'author_id' => 'integer',
        'view_count' => 'integer',
        'is_top' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * 状态常量
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    /**
     * 获取状态列表
     *
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_DRAFT => '草稿',
            self::STATUS_PUBLISHED => '已发布',
            self::STATUS_ARCHIVED => '已归档',
        ];
    }

    /**
     * 关联分类
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|object|null
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * 关联标签
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'blog_post_tags',
            'post_id',
            'tag_id'
        );
    }

    /**
     * 作用域：已发布
     *
     * @param mixed $query
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * 作用域：置顶
     *
     * @param mixed $query
     * @return mixed
     */
    public function scopeTop($query)
    {
        return $query->where('is_top', true);
    }
}
