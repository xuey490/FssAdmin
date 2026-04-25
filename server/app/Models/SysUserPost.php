<?php

declare(strict_types=1);

/**
 * 用户岗位关联模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;

/**
 * SysUserPost 用户岗位关联模型
 *
 * 用户与岗位的多对多中间表模型
 *
 * @property int $user_id 用户ID
 * @property int $post_id 岗位ID
 */
class SysUserPost extends BaseLaORMModel
{
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';
    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_user_post';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 是否自增主键
     * @var bool
     */
    public $incrementing = true;

    /**
     * 是否包含时间戳
     * @var bool
     */
    public $timestamps = true;

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'user_id',
        'post_id',
        'tenant_id',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'post_id' => 'integer',
        'tenant_id' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    // ==================== 业务方法 ====================

    /**
     * 根据用户ID获取岗位ID列表
     *
     * @param int $userId 用户ID
     * @return array
     */
    public static function getPostIdsByUser(int $userId): array
    {
        return self::where('user_id', $userId)->pluck('post_id')->toArray();
    }

    /**
     * 根据岗位ID获取用户ID列表
     *
     * @param int $postId 岗位ID
     * @return array
     */
    public static function getUserIdsByPost(int $postId): array
    {
        return self::where('post_id', $postId)->pluck('user_id')->toArray();
    }

    /**
     * 批量保存用户岗位关联
     *
     * @param int   $userId  用户ID
     * @param array $postIds 岗位ID列表
     * @return void
     */
    public static function saveUserPosts(int $userId, array $postIds, int $tenantId, int $operator = 0): void
    {
        // 先删除原有关联
        self::where('user_id', $userId)->delete();

        // 批量插入新关联
        if (!empty($postIds)) {
            $data = array_map(function ($postId) use ($userId, $tenantId, $operator) {
                return [
                    'user_id' => $userId,
                    'post_id' => $postId,
                    'tenant_id' => $tenantId,
                    'created_by' => $operator,
                    'updated_by' => $operator,
                ];
            }, $postIds);

            self::insert($data);
        }
    }

    /**
     * 批量保存岗位用户关联
     *
     * @param int   $postId  岗位ID
     * @param array $userIds 用户ID列表
     * @return void
     */
    public static function savePostUsers(int $postId, array $userIds, int $tenantId, int $operator = 0): void
    {
        // 先删除原有关联
        self::where('post_id', $postId)->delete();

        // 批量插入新关联
        if (!empty($userIds)) {
            $data = array_map(function ($userId) use ($postId, $tenantId, $operator) {
                return [
                    'user_id' => $userId,
                    'post_id' => $postId,
                    'tenant_id' => $tenantId,
                    'created_by' => $operator,
                    'updated_by' => $operator,
                ];
            }, $userIds);

            self::insert($data);
        }
    }

    /**
     * 检查用户是否拥有指定岗位
     *
     * @param int $userId 用户ID
     * @param int $postId 岗位ID
     * @return bool
     */
    public static function hasPost(int $userId, int $postId, int $tenantId): bool
    {
        return self::where('user_id', $userId)
            ->where('post_id', $postId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }
}
