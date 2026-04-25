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

/**
 * 统一 JSON 响应工具类
 * 兼容旧代码，同时遵循 BaseJsonResponse 接口规范：
 * {
 *     "code": 0,      // 0 成功, 1 失败或自定义
 *     "msg": "...",   // 提示信息
 *     "data": { ... } // 业务数据
 * }
 *
 * 支持长整型自动转字符串
 */

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\Response;

/**
 * JSON 响应工具类
 *
 * 提供统一的 JSON 响应格式，遵循 BaseJsonResponse 接口规范：
 * {
 *     "code": 0,      // 0 成功, 1 失败或自定义
 *     "msg": "...",   // 提示信息
 *     "data": { ... } // 业务数据
 * }
 *
 * 支持长整型自动转字符串，避免 JavaScript 精度丢失问题。
 *
 * @package Framework\Utils
 */
class Json
{
    /**
     * 默认 HTTP 状态码
     *
     * @var int
     */
    private static int $defaultHttpCode = 200;

    /**
     * 核心构造方法
     *
     * 构建 JSON 响应对象的基础方法，返回 Symfony Response 对象。
     *
     * @param int        $code     业务状态码
     * @param string     $msg      提示信息
     * @param array|null $data     业务数据
     * @param int|null   $httpCode HTTP 状态码，默认 200
     *
     * @return Response Symfony Response 对象
     */
    public static function make(int $code, string $msg, ?array $data = null, ?int $httpCode = null): Response
    {
        $res = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data ?? [],
        ];

        // 默认 HTTP 状态码 200
        $httpCode = $httpCode ?? self::$defaultHttpCode;

        return new Response(
            json_encode($res, JSON_UNESCAPED_UNICODE),
            $httpCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * 业务成功响应
     *
     * 返回业务成功的 JSON 响应，自动将长整型数字转换为字符串。
     *
     * @param mixed    $data     业务数据，可以是数组或对象
     * @param string   $msg      提示信息，默认为 'success'
     * @param int|null $httpCode HTTP 状态码，默认 200
     *
     * @return Response Symfony Response 对象
     */
    public static function success(mixed $data = [], string $msg = 'success', ?int $httpCode = 200): Response
    {
        if (is_array($data) || is_object($data)) {
            $data = self::convertLongNumbersToString($data);
        }
        return self::make(0, $msg, (array)$data, $httpCode);
    }

    /**
     * 业务失败
     * $msg 错误提示
     * $code 错误码（默认 1）
     * $data 可选数据
     */
    public static function fail(string $msg = 'fail', ?array $data = null, int $code = 1, ?int $httpCode = 200): Response
    {
        return self::make($code, $msg, $data, $httpCode);
    }

    /**
     * 系统异常
     */
    public static function error(string $msg, int $httpCode = 500): Response
    {
        return self::make(1, $msg, [], $httpCode);
    }

    /**
     * 通用状态包装
     */
    public static function status(string $status, string $msg = 'success', array $data = []): Response
    {
        return self::make(0, $msg, array_merge(['status' => strtoupper($status)], $data));
    }

    /**
     * 避免 long 型雪花 ID 转换丢失精度（自动转字符串）
     */
    public static function convertLongNumbersToString(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::convertLongNumbersToString($v);
            }
        } elseif (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                $value = self::convertLongNumbersToString($value->toArray());
            }
        } elseif (is_numeric($value) && strlen((string)$value) > 15) {
            $value = (string)$value;
        }
        return $value;
    }
}
