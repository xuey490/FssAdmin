<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-10
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Traits;

use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * CRUD 操作 Trait
 *
 * 为控制器提供标准的增删改查（CRUD）操作方法。
 * 封装了列表查询、详情查看、新增、更新、删除等常用操作，
 * 支持多种数据格式输出（普通列表、下拉选择、树形结构等）。
 *
 * 使用前提：
 * - 控制器需定义 $service 属性（服务层实例）
 * - 控制器需实现 selectInput() 和 insertInput() 方法
 * - 可选定义 $validator 属性进行数据验证
 * - 需实现 formatSelect()、formatTree()、formatNormal() 方法
 *
 * 使用示例：
 * class UserController {
 *     use CrudActionTrait;
 *
 *     protected UserService $service;
 *     protected UserValidator $validator;
 * }
 *
 * @package Framework\Basic\Traits
 */
trait CrudActionTrait
{
    /**
     * 获取列表数据
     *
     * 支持多种输出格式：
     * - normal: 普通分页列表
     * - select: 下拉选择格式
     * - tree: 树形结构
     * - table_tree: 表格树形结构
     *
     * @param Request $request HTTP 请求对象
     * @return \Illuminate\Http\JsonResponse 返回 JSON 格式的列表数据
     */
    public function index(Request $request)
    {
        try {
            [$where, $format, $limit, $field, $order, $page] = $this->selectInput($request);

            $total = $this->service->getCount($where);
            $list  = $this->service->selectList($where, $field, $page, $limit, $order);

            $methodMap = [
                'select'     => 'formatSelect',
                'tree'       => 'formatTree',
                'table_tree' => 'formatTree',
                'normal'     => 'formatNormal',
            ];

            $method = $methodMap[$format] ?? 'formatNormal';
            return $this->$method($list, $total);
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取单条数据详情
     *
     * 根据 ID 获取数据详情，如果数据不存在则返回错误信息。
     *
     * @param Request $request HTTP 请求对象，需包含 id 参数
     * @return \Illuminate\Http\JsonResponse 返回 JSON 格式的详情数据
     */
    public function show(Request $request)
    {
        try {
            $id   = $request->get('id');
            $data = $this->service->get($id);

            if (! $data) {
                return $this->fail('数据不存在');
            }

            return $this->success($data->toArray(), 'ok');
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 新增数据
     *
     * 创建新的数据记录。如果设置了验证器，会先进行数据验证。
     * 验证场景为 'store'。
     *
     * @param Request $request HTTP 请求对象，包含新增数据
     * @return \Illuminate\Http\JsonResponse 返回 JSON 格式的操作结果，成功时包含新创建的数据
     */
    public function store(Request $request)
    {
        try {
            $data = $this->insertInput($request);

            if ($this->validator && ! $this->validator->scene('store')->check($data)) {
                return $this->fail($this->validator->getError());
            }

            $model = $this->service->save($data);
            return $this->success($model->toArray(), 'ok');
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新数据
     *
     * 根据 ID 更新已有数据记录。如果设置了验证器，会先进行数据验证。
     * 验证场景为 'update'。
     *
     * @param Request $request HTTP 请求对象，需在 attributes 中包含 id，以及更新数据
     * @return \Illuminate\Http\JsonResponse 返回 JSON 格式的操作结果
     */
    public function update(Request $request)
    {
        try {
            $id   = $request->attributes->get('id');
            $data = $this->insertInput($request);

            if ($this->validator && ! $this->validator->scene('update')->check($data)) {
                return $this->fail($this->validator->getError());
            }

            $this->service->update($id, $data);
            return $this->success();
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除数据
     *
     * 根据 ID 删除数据记录。
     *
     * @param Request $request HTTP 请求对象，需在 attributes 中包含 id
     * @return \Illuminate\Http\JsonResponse 返回 JSON 格式的操作结果
     */
    public function destroy(Request $request)
    {
        try {
            $id = $request->attributes->get('id');

            $this->service->delete($id);
            return $this->success();
        } catch (Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }
}
