<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: BaseJsonResponse.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * BaseJsonResponse - JSON 响应封装类
 *
 * 提供统一的 JSON 响应格式，便于前端处理：
 *
 * 成功响应格式：
 * {
 *   "code": 0,        // 0 表示成功
 *   "msg": "ok",
 *   "data": {}
 * }
 *
 * 失败响应格式：
 * {
 *   "code": 1,        // 非 0 表示业务失败
 *   "msg": "error",
 *   "data": {}
 * }
 *
 * @package Framework\Basic
 */
class BaseJsonResponse extends JsonResponse
{
    /**
     * 返回业务成功响应
     *
     * HTTP 状态码为 200，业务码为 0。
     *
     * @param mixed $data 响应数据
     * @param string $msg 响应消息，默认 'ok'
     * @return static JSON 响应实例
     */
    public static function success(
        mixed $data = [],
        string $msg = 'ok'
    ): static {
        return new static([
            'code' => 200, // 与前端 ApiStatus.success 对齐
            'msg' => $msg,
            'message' => $msg,
            'data' => $data,
        ], self::HTTP_OK);
    }

    /**
     * 返回业务失败响应
     *
     * HTTP 状态码为 200，业务码为非 0。
     * 用于业务逻辑验证失败等场景。
     *
     * @param string $msg 错误消息
     * @param mixed $data 附加数据
     * @param int $code 业务错误码，默认 1
     * @return static JSON 响应实例
     */
    public static function fail(
        string $msg = 'fail',
        mixed $data = [],
        int $code = 1
    ): static {
        return new static([
            'code' => $code,
            'msg' => $msg,
            'message' => $msg,
            'data' => $data,
        ], self::HTTP_OK);
    }

    /**
     * 返回 HTTP 错误响应
     *
     * 用于服务器错误、权限不足等 HTTP 层面错误。
     *
     * @param string $msg 错误消息
     * @param int $httpStatus HTTP 状态码，默认 500
     * @param int $code 业务错误码，默认 1
     * @return static JSON 响应实例
     */
    public static function error(
        string $msg,
        int $httpStatus = self::HTTP_INTERNAL_SERVER_ERROR,
        int $code = 1
    ): static {
        return new static([
            'code' => $code,
            'msg' => $msg,
            'message' => $msg,
            'data' => [],
        ], $httpStatus);
    }

    /**
     * 返回未认证响应
     *
     * 用于 JWT 失效或未登录场景。
     *
     * @param string $msg 错误消息，默认 'Unauthenticated'
     * @return static JSON 响应实例
     */
    public static function unauthorized(
        string $msg = 'Unauthenticated'
    ): static {
        return new static([
            'code' => 401,
            'msg' => $msg,
            'message' => $msg,
            'data' => [],
        ], self::HTTP_UNAUTHORIZED);
    }

    /**
     * 返回 CSRF 校验失败响应
     *
     * 用于 CSRF Token 不匹配场景。
     *
     * @param string $msg 错误消息，默认 'CSRF token mismatch'
     * @return static JSON 响应实例
     */
    public static function csrfExpired(
        string $msg = 'CSRF token mismatch'
    ): static {
        return new static([
            'code' => 403,
            'msg' => $msg,
            'message' => $msg,
            'data' => [],
        ], self::HTTP_FORBIDDEN);
    }
}
