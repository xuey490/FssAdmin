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

use Symfony\Component\HttpFoundation\Response;

/*
$response = new Response('ok', 200);

// 重置内容
app('response')->setContent('Hello FSSPHP!');

// 设置单个头
$response->headers->set('Authorization', 'Bearer 123');

// 添加多个头
$response->headers->add([
    'X-Token-Refresh' => 'xxx',
    'Cache-Control'   => 'no-store',
]);

// 删除头
$response->headers->remove('Authorization');
*/

class ResponseFactory
{
    public static function create(): Response
    {
        return new Response('', Response::HTTP_OK, []);
    }
}
