<?php

declare(strict_types=1);

/**
 * 操作日志模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SysOperationLog 操作日志模型
 *
 * @property int         $id               日志ID
 * @property int         $user_id          操作用户ID
 * @property string      $username         操作用户名
 * @property string      $module           模块名称
 * @property string      $business_type    业务类型
 * @property string      $method           请求方法
 * @property string      $url              请求URL
 * @property string      $route_name       路由名称
 * @property string      $operation_ip     操作IP
 * @property string      $operation_location 操作地点
 * @property \DateTime   $operation_time   操作时间
 * @property string      $request_params   请求参数
 * @property string      $response_result  响应结果
 * @property int         $status           操作状态
 * @property string      $error_msg        错误信息
 * @property int         $duration         执行时长(毫秒)
 * @property string      $browser          浏览器
 * @property string      $os               操作系统
 * @property string      $user_agent       用户代理
 * @property \DateTime   $created_at       创建时间
 */
class SysOperationLog extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sa_system_oper_log';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * 自定义时间戳字段名
     */
    const CREATED_AT = 'create_time';
    const DELETED_AT = 'delete_time';

    /**
     * 是否自增主键
     * @var bool
     */
    public $incrementing = true;

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'username',
        'app',
        'method',
        'router',
        'service_name',
        'ip',
        'ip_location',
        'request_data',
		'duration',
        'remark',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
    ];

    /**
     * 是否自动维护时间戳
     * @var bool
     */
    public $timestamps = true;

    // ==================== 状态常量 ====================

    /** @var int 操作失败 */
    public const STATUS_FAIL = 0;

    /** @var int 操作成功 */
    public const STATUS_SUCCESS = 1;

    // ==================== 业务类型常量 ====================

    /** @var string 新增 */
    public const TYPE_INSERT = '新增';

    /** @var string 修改 */
    public const TYPE_UPDATE = '修改';

    /** @var string 删除 */
    public const TYPE_DELETE = '删除';

    /** @var string 查询 */
    public const TYPE_SELECT = '查询';

    /** @var string 导出 */
    public const TYPE_EXPORT = '导出';

    /** @var string 导入 */
    public const TYPE_IMPORT = '导入';

    /** @var string 登录 */
    public const TYPE_LOGIN = '登录';

    /** @var string 登出 */
    public const TYPE_LOGOUT = '登出';

    /** @var string 其他 */
    public const TYPE_OTHER = '其他';

    // ==================== 业务方法 ====================

    /**
     * 检查是否操作成功
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * 记录操作日志
     *
     * @param array $data 日志数据
     * @return static
     */
    public static function record(array $data): static
    {
        return self::create(array_merge([
            'create_time' => date('Y-m-d H:i:s'),
        ], $data));
    }

    /**
     * 根据请求方法获取业务类型
     *
     * @param string $method HTTP方法
     * @return string
     */
    public static function getBusinessTypeByMethod(string $method): string
    {
        return match (strtoupper($method)) {
            'POST' => self::TYPE_INSERT,
            'PUT', 'PATCH' => self::TYPE_UPDATE,
            'DELETE' => self::TYPE_DELETE,
            'GET' => self::TYPE_SELECT,
            default => self::TYPE_OTHER,
        };
    }

    /**
     * 获取操作状态文本
     *
     * @return string
     */
    public function getStatusText(): string
    {
        return $this->isSuccess() ? '成功' : '失败';
    }

    /**
     * 获取指定用户的最近操作记录
     *
     * @param int $userId 用户ID
     * @param int $limit  数量
     * @return array
     */
    public static function getRecentByUserId(int $userId, int $limit = 10): array
    {
        return self::where('created_by', $userId)
            ->orderBy('create_time', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * 清理指定天数之前的日志
     *
     * @param int $days 天数
     * @return int 删除数量
     */
    public static function cleanOldLogs(int $days = 30): int
    {
        $date = date('Y-m-d H:i:s' ,strtotime("-$days days"));
        return self::where('create_time', '<', $date)->delete();
    }
}
