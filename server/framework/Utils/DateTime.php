<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use DateTime as PhpDateTime;
use DateTimeZone;
use Exception;

/**
 * 日期时间工具类
 *
 * 提供常用的日期时间处理方法，包括时间戳与字符串转换、
 * 时间差计算、格式化输出等功能，支持多种日期格式的智能解析。
 *
 * @package Framework\Utils
 */
class DateTime
{

    /**
     * 将时间戳转换为字符串日期
     *
     * 根据指定的格式将时间戳转换为日期字符串，如果时间戳为空则返回空字符串。
     *
     * @param int|string|null $timestamp 时间戳
     * @param string          $format    日期格式，默认为 Y-m-d H:i:s
     *
     * @return string 格式化后的日期字符串，时间戳为空时返回空字符串
     */
    public static function timestampToString(int|string|null $timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        if (empty($timestamp)) {
            return '';
        }
        return date($format, $timestamp);
    }

    /**
     * 将日期时间字符串转换为时间戳
     *
     * @param string      $dateTimeStr 日期时间字符串
     * @param string|null $format      日期时间格式，可选。默认为 null，将使用 strtotime() 进行解析
     *
     * @return int|false 时间戳，如果解析失败则返回 false
     */
    public static function dateTimeStringToTimestamp(string $dateTimeStr, string $format = null): bool|int
    {
        if ($format === null) {
            // 不提供格式，使用 strtotime() 尝试解析
            $timestamp = strtotime($dateTimeStr);
            // 检查 strtotime() 是否成功解析了日期时间字符串
            if ($timestamp === false) {
                // 解析失败，返回 false
                return false;
            }
            // 返回解析成功的时间戳
            return $timestamp;
        } else {
            // 提供了格式，使用 DateTime::createFromFormat() 解析
            $dateTime = DateTime::createFromFormat($format, $dateTimeStr);
            if ($dateTime === false) {
                // 解析失败，返回 false
                return false;
            }
            // 返回解析成功的时间戳
            return $dateTime->getTimestamp();
        }
    }
	
    /**
     * 获取当前时间字符串
     *
     * 根据指定的格式和时区获取当前时间的格式化字符串。
     *
     * @param string      $format   日期格式，默认为 Y-m-d H:i:s
     * @param string|null $timezone 时区，默认使用系统配置
     *
     * @return string 格式化后的当前时间字符串
     */
    public static function now(string $format = 'Y-m-d H:i:s', ?string $timezone = null): string
    {
        $tz = $timezone ? new DateTimeZone($timezone) : null;
        return (new PhpDateTime('now', $tz))->format($format);
    }

    /**
     * 将日期时间字符串转换为时间戳
     *
     * 使用 strtotime() 函数解析日期时间字符串并返回时间戳。
     *
     * @param string $time 日期时间字符串
     *
     * @return int 时间戳
     */
    public static function toTimestamp(string $time): int
    {
        return strtotime($time);
    }

    /**
     * 获取当前毫秒时间戳
     *
     * 返回当前时间的毫秒级时间戳。
     *
     * @return int 毫秒时间戳
     */
    public static function ms(): int
    {
        return (int) round(microtime(true) * 1000);
    }

    /**
     * 格式化时间戳
     *
     * 将时间戳按照指定格式转换为日期字符串。
     *
     * @param int    $timestamp 时间戳
     * @param string $format    日期格式，默认为 Y-m-d H:i:s
     *
     * @return string 格式化后的日期字符串
     */
    public static function format(int $timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, $timestamp);
    }

    /**
     * 计算两个时间的时间差
     *
     * 计算结束时间与开始时间之间的秒数差。
     *
     * @param string $start 开始时间字符串
     * @param string $end   结束时间字符串
     *
     * @return int 时间差（秒）
     */
    public static function diff(string $start, string $end): int
    {
        return strtotime($end) - strtotime($start);
    }

    /**
     * 解析任意字符串为 DateTime 对象
     *
     * 尝试将任意日期时间字符串解析为 DateTime 对象，
     * 如果解析失败则抛出异常。
     *
     * @param string $time 日期时间字符串
     *
     * @return PhpDateTime 解析后的 DateTime 对象
     *
     * @throws \InvalidArgumentException 日期时间字符串无效时抛出异常
     */
    public static function parse(string $time): PhpDateTime
    {
        try {
            return new PhpDateTime($time);
        } catch (Exception) {
            throw new \InvalidArgumentException("Invalid datetime string: {$time}");
        }
    }

    /**
     * 转换日期时间为指定格式
     *
     * 智能解析日期时间字符串并转换为指定格式输出。
     *
     * @param string $time   日期时间字符串
     * @param string $format 目标格式，默认为 Y-m-d H:i:s
     *
     * @return string 格式化后的日期时间字符串
     */
    public static function convert(string $time, string $format = 'Y-m-d H:i:s'): string
    {
        $dt = self::parse($time);
        return $dt->format($format);
    }
}
