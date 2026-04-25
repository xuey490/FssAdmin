<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-11
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;

/**
 * 时间戳类型转换器
 *
 * 用于在 Eloquent 模型中自动转换时间戳字段。
 * 将数据库中存储的整数时间戳转换为 Carbon 对象，反之亦然。
 * 支持多种时间格式的输入，包括整数时间戳、DateTimeInterface 对象和时间字符串。
 *
 * 使用示例：
 * protected $casts = [
 *     'created_at' => TimestampCast::class,
 *     'updated_at' => TimestampCast::class,
 * ];
 *
 * @package Framework\Basic\Casts
 */
class TimestampCast implements CastsAttributes
{
    /**
     * 将数据库存储的时间戳转换为 Carbon 对象
     *
     * 从数据库读取数据时调用此方法，将整数时间戳转换为可读的时间对象。
     * 如果值无效或为零，则返回原始值不做转换。
     *
     * @param \Illuminate\Database\Eloquent\Model $model 模型实例
     * @param string $key 字段名称
     * @param mixed $value 数据库中的原始值（整数时间戳）
     * @param array $attributes 模型所有属性的数组
     * @return Carbon|mixed 返回 Carbon 时间对象，如果值无效则返回原始值
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_numeric($value) && (int)$value > 0) {
            return Carbon::createFromTimestamp((int)$value);
        }
        return $value;
    }

    /**
     * 将时间值转换为 datetime 格式字符串用于数据库存储
     *
     * 保存到数据库时调用此方法，支持多种输入格式：
     * - null 或空字符串：返回 null
     * - DateTimeInterface 对象：格式化为 Y-m-d H:i:s
     * - 数值型：从时间戳转换为 datetime 格式
     * - 字符串：使用 strtotime() 解析后格式化
     *
     * @param \Illuminate\Database\Eloquent\Model $model 模型实例
     * @param string $key 字段名称
     * @param mixed $value 要存储的值（可以是时间字符串、时间戳、DateTime 对象等）
     * @param array $attributes 模型所有属性的数组
     * @return string|null 返回 datetime 格式字符串，如果无法转换则返回 null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value)->format('Y-m-d H:i:s');
        }

        if (is_string($value)) {
            $ts = strtotime($value);
            return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
        }

        return null;
    }
}
