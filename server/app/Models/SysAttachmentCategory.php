<?php

declare(strict_types=1);

/**
 * 附件分类模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SysAttachmentCategory 附件分类模型
 *
 * @property int         $id            分类ID
 * @property int         $parent_id     父分类ID
 * @property string      $level         组集关系
 * @property string      $category_name 分类名称
 * @property int         $sort          排序
 * @property int         $status        状态
 * @property string      $remark        备注
 * @property int         $created_by    创建人ID
 * @property int         $updated_by    更新人ID
 */
class SysAttachmentCategory extends BaseLaORMModel
{
    use SoftDeletes;

    protected $table = 'sa_system_category';
    protected $primaryKey = 'id';

    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';

    protected $fillable = [
        'parent_id',
        'level',
        'category_name',
        'sort',
        'status',
        'remark',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id'          => 'integer',
        'parent_id'   => 'integer',
        'sort'        => 'integer',
        'status'      => 'integer',
        'created_by'  => 'integer',
        'updated_by'  => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED  = 1;

    // ==================== 关联关系 ====================

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SysAttachmentCategory::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(SysAttachmentCategory::class, 'parent_id', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SysAttachment::class, 'category_id', 'id');
    }

    // ==================== 业务方法 ====================

    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    public function hasChildren(): bool
    {
        return self::where('parent_id', $this->id)->whereNull('delete_time')->exists();
    }

    public function hasAttachments(): bool
    {
        return SysAttachment::where('category_id', $this->id)->whereNull('delete_time')->exists();
    }

    /**
     * 构建分类树（带 label 字段供前端 el-tree 使用）
     */
    public static function buildTree(int $parentId = 0): array
    {
        $items = self::where('parent_id', $parentId)
            ->whereNull('delete_time')
            ->orderBy('sort')
            ->get();

        $tree = [];
        foreach ($items as $item) {
            $node = $item->toArray();
            $node['label'] = $item->category_name;
            $node['value'] = $item->id;
            $children = self::buildTree($item->id);
            if (!empty($children)) {
                $node['children'] = $children;
            }
            $tree[] = $node;
        }

        return $tree;
    }
}
