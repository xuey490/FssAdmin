<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Users - 用户模型别名（与 SysUser 共享 sa_system_user 表）。
 *
 * 部分 DAO/Service 通过 App\Models\Users 引用用户模型，
 * 此处直接继承 SysUser 以复用其字段、关联关系与查询构造器类型，
 * 避免重复定义。
 *
 * @package App\Models
 */
class Users extends SysUser
{
}
