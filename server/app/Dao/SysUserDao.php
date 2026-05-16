<?php

declare(strict_types=1);

/**
 * 系统用户DAO
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Dao;

use App\Models\SysUser;
use Framework\Basic\BaseDao;

/**
 * SysUserDao 用户数据访问层
 *
 * 封装用户相关的数据查询操作
 */
class SysUserDao extends BaseDao
{
    /**
     * 设置模型类
     *
     * @return string
     */
    protected function setModel(): string
    {
        return SysUser::class;
    }

    /**
     * 根据用户名查找用户
     *
     * @param string $username 用户名
     * @return SysUser|null
     */
    public function findByUsername(string $username): ?SysUser
    {
        return $this->getOne(['username' => $username]);
    }

    /**
     * 根据手机号查找用户
     *
     * @param string $mobile 手机号
     * @return SysUser|null
     */
    public function findByMobile(string $mobile): ?SysUser
    {
        return $this->getOne(['phone' => $mobile]);
    }

    /**
     * 根据邮箱查找用户
     *
     * @param string $email 邮箱
     * @return SysUser|null
     */
    public function findByEmail(string $email): ?SysUser
    {
        return $this->getOne(['email' => $email]);
    }

    /**
     * 根据部门ID获取用户列表
     *
     * @param int   $deptId 部门ID
     * @param array $where  额外条件
     * @param int   $page   页码
     * @param int   $limit  每页数量
     * @return array
     */
    public function getListByDeptId(int $deptId, array $where = [], int $page = 1, int $limit = 20): array
    {
        $where['dept_id'] = $deptId;
        return $this->selectList($where, '*', $page, $limit, 'id desc')->toArray();
    }

    /**
     * 获取启用的用户列表
     *
     * @param int $page  页码
     * @param int $limit 每页数量
     * @return array
     */
    public function getEnabledList(int $page = 1, int $limit = 20): array
    {
        return $this->selectList(['status' => SysUser::STATUS_ENABLED], '*', $page, $limit, 'id desc')->toArray();
    }

    /**
     * 检查用户名是否存在
     *
     * @param string $username  用户名
     * @param int    $excludeId 排除的用户ID
     * @return bool
     */
    public function isUsernameExists(string $username, int $excludeId = 0): bool
    {
        $where = ['username' => $username];
        if ($excludeId > 0) {
            return $this->be($where) && $this->value($where, 'id') != $excludeId;
        }
        return $this->be($where);
    }

    /**
     * 检查手机号是否存在
     *
     * @param string $mobile    手机号
     * @param int    $excludeId 排除的用户ID
     * @return bool
     */
    public function isMobileExists(string $mobile, int $excludeId = 0): bool
    {
        $where = ['phone' => $mobile];
        if ($excludeId > 0) {
            return $this->be($where) && $this->value($where, 'id') != $excludeId;
        }
        return $this->be($where);
    }

    /**
     * 更新用户状态
     *
     * @param int $userId 用户ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $userId, int $status): bool
    {
        return $this->update($userId, ['status' => $status]);
    }

    /**
     * 更新最后登录信息
     *
     * @param int    $userId 用户ID
     * @param string $ip     登录IP
     * @return bool
     */
    public function updateLoginInfo(int $userId, string $ip): bool
    {
        return $this->update($userId, [
            'login_ip' => $ip,
            'login_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 重置密码
     *
     * @param int    $userId   用户ID
     * @param string $password 新密码(明文)
     * @return bool
     */
    public function resetPassword(int $userId, string $password): bool
    {
        return $this->update($userId, ['password' => $password]);
    }

    /**
     * 获取用户总数
     *
     * @param array $where 条件
     * @return int
     */
    public function getUserCount(array $where = []): int
    {
        return $this->count($where);
    }

    /**
     * 获取部门下的用户ID列表
     *
     * @param int $deptId 部门ID
     * @return array
     */
    public function getUserIdsByDeptId(int $deptId): array
    {
        return $this->getColumn(['dept_id' => $deptId], 'id');
    }
}
