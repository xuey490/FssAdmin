<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SysLoginLog 登录日志模型（对应 sa_system_login_log 表）
 *
 * @property int         $id
 * @property string      $username      用户名
 * @property string      $ip            登录IP
 * @property string      $ip_location   IP所属地
 * @property string      $os            操作系统
 * @property string      $browser       浏览器
 * @property int         $status        登录状态 1成功 2失败
 * @property string      $message       提示消息
 * @property \DateTime   $login_time    登录时间
 * @property string      $remark        备注
 * @property \DateTime   $create_time   创建时间
 
 * @property mixed $created_by
 * @property mixed $updated_by
 * @property string $update_time
 * @property string $delete_time
 * @property mixed $tenant_id
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $deleted_at
*/
class SysLoginLog extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * @return mixed
     */
    protected $table = 'sa_system_login_log';
    /**
     * @return mixed
     */
    protected $primaryKey = 'id';
    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';
    /**
     * @return mixed
     */
    public $incrementing = true;
    /**
     * @return mixed
     */
    public $timestamps = true;

    /** 登录成功 */
    public const STATUS_SUCCESS = 1;
    /** 登录失败 */
    public const STATUS_FAIL = 2;

    /**
     * @return mixed
     */
    protected $fillable = [
        'username',
        'ip',
        'ip_location',
        'os',
        'browser',
        'status',
        'message',
        'login_time',
        'remark',
        'created_by',
        'updated_by',
        'create_time',
        'update_time',
        'delete_time',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id'         => 'integer',
        'status'     => 'integer',
        'login_time' => 'datetime',
        'create_time'=> 'datetime',
        'update_time'=> 'datetime',
        'delete_time'=> 'datetime',
    ];

    /**
     */
    /**
     * 记录登录日志
     * @param array<array-key, mixed> $data
     */
    public static function record(array $data): static
    {
        $now = date('Y-m-d H:i:s');

        return self::create(array_merge([
            'login_time'  => $now,
            'create_time' => $now,
        ], $data));
    }
}