<?php

declare(strict_types=1);

/**
 * 附件模型
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
 * SysAttachment 附件模型
 *
 * @property int         $id             附件ID
 * @property int         $category_id    分类ID
 * @property int         $storage_mode   存储模式 (1 本地 2 阿里云 3 七牛云 4 腾讯云)
 * @property string      $origin_name    原文件名
 * @property string      $object_name    新文件名
 * @property string      $hash           文件hash
 * @property string      $mime_type      资源类型
 * @property string      $storage_path   存储目录
 * @property string      $suffix         文件后缀
 * @property int         $size_byte      字节数
 * @property string      $size_info      文件大小
 * @property string      $url            url地址
 * @property string      $remark         备注
 * @property int         $created_by     创建人ID
 * @property int         $updated_by     更新人ID
 * @property \DateTime   $created_at     创建时间
 * @property \DateTime   $updated_at     更新时间
 * @property \DateTime   $deleted_at     删除时间
 *
 * @property-read SysAttachmentCategory $category 所属分类
 */
class SysAttachment extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_attachment';

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
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'delete_time',
    ];

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'category_id',
        'storage_mode',
        'origin_name',
        'object_name',
        'hash',
        'mime_type',
        'storage_path',
        'suffix',
        'size_byte',
        'size_info',
        'url',
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
        'category_id' => 'integer',
        'storage_mode' => 'integer',
        'size_byte' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    // ==================== 状态常量 ====================

    // ==================== 存储驱动常量 ====================

    /** @var int 本地存储 */
    public const STORAGE_LOCAL = 1;

    /** @var int 阿里云OSS */
    public const STORAGE_OSS = 2;

    /** @var int 七牛云 */
    public const STORAGE_QINIU = 3;

    /** @var int 腾讯云COS */
    public const STORAGE_COS = 4;

    // ==================== 关联关系 ====================

    /**
     * 所属分类
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SysAttachmentCategory::class, 'category_id', 'id');
    }

    // ==================== 业务方法 ==================== 

    /**
     * 获取格式化的文件大小
     *
     * @return string
     */
    public function getFormattedSize(): string
    {
        if (!empty($this->size_info)) {
            return $this->size_info;
        }

        $size = $this->size_byte ?? 0;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * 获取文件图标
     *
     * @return string
     */
    public function getFileIcon(): string
    {
        $ext = strtolower($this->suffix ?? '');

        return match ($ext) {
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg' => 'image',
            'pdf' => 'pdf',
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'ppt', 'pptx' => 'ppt',
            'zip', 'rar', '7z', 'tar', 'gz' => 'zip',
            'mp3', 'wav', 'flac', 'aac' => 'audio',
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv' => 'video',
            'txt', 'log' => 'text',
            'php', 'js', 'html', 'css', 'json', 'xml', 'sql' => 'code',
            default => 'file',
        };
    }

    /**
     * 判断是否为图片
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return in_array(strtolower($this->suffix ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']);
    }

    /**
     * 判断是否为视频
     *
     * @return bool
     */
    public function isVideo(): bool
    {
        return in_array(strtolower($this->suffix ?? ''), ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv']);
    }

    /**
     * 判断是否为音频
     *
     * @return bool
     */
    public function isAudio(): bool
    {
        return in_array(strtolower($this->suffix ?? ''), ['mp3', 'wav', 'flac', 'aac']);
    }

    /**
     * 获取可用的存储驱动列表
     *
     * @return array
     */
    public static function getAvailableDrivers(): array
    {
        return [
            self::STORAGE_LOCAL => '本地存储',
            self::STORAGE_OSS => '阿里云OSS',
            self::STORAGE_QINIU => '七牛云',
            self::STORAGE_COS => '腾讯云COS',
        ];
    }
}
