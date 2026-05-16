<?php

declare(strict_types=1);

/**
 * 系统通用控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-20
 */

namespace App\Controllers;

use App\Services\SysUserService;
use App\Services\LoginLogService;
use App\Services\OperationLogService;
use App\Services\SysAttachmentService;
use App\Services\SysDictService;
use App\Services\ServerMonitorService;
use App\Services\RedisMonitorService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;

/**
 * SystemController 系统通用控制器
 *
 * 处理登录日志、操作日志、验证码、上传等接口
 */
class SystemController extends BaseController
{
    /**
     * 用户服务
     * @var SysUserService
     */
    protected SysUserService $userService;

    /**
     * 登录日志服务
     * @var LoginLogService
     */
    protected LoginLogService $loginLogService;

    /**
     * 操作日志服务
     * @var OperationLogService
     */
    protected OperationLogService $operationLogService;

    /**
     * 附件服务
     * @var SysAttachmentService
     */
    protected SysAttachmentService $attachmentService;

    /**
     * 字典服务
     * @var SysDictService
     */
    protected SysDictService $dictService;

    /**
     * 服务器监控服务
     * @var ServerMonitorService
     */
    protected ServerMonitorService $serverMonitorService;

    /**
     * Redis 监控服务
     * @var RedisMonitorService
     */
    protected RedisMonitorService $redisMonitorService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->userService = new SysUserService();
        $this->loginLogService = new LoginLogService();
        $this->operationLogService = new OperationLogService();
        $this->attachmentService = new SysAttachmentService();
        $this->dictService = new SysDictService();
        $this->serverMonitorService = new ServerMonitorService();
        $this->redisMonitorService = new RedisMonitorService();
    }

    /**
     * 获取验证码
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/captcha', methods: ['GET'], name: 'system.captcha')]
    public function captcha(Request $request): BaseJsonResponse
    {
        // 使用 PHP 内置的 GD 库生成验证码
        $captcha = $this->generateCaptcha();

        // 将验证码存储到 session
        if ($request->hasSession()) {
            $request->getSession()->set('captcha_code', $captcha['code']);
            $request->getSession()->set('captcha_uuid', $captcha['key']);
        }

        return $this->success([
            'uuid' => $captcha['key'],
            'image' => 'data:image/png;base64,' . base64_encode($captcha['image'])
        ]);
    }

    /**
     * 验证验证码
     *
     * @param string $code 用户输入的验证码
     * @param Request $request 请求对象
     * @return bool
     */
    protected function verifyCaptcha(string $code, Request $request): bool
    {
        if (!$request->hasSession()) {
            return false;
        }

        $sessionCode = $request->getSession()->get('captcha_code');

        // 验证后清除
        $request->getSession()->remove('captcha_code');

        return strtolower($code) === strtolower($sessionCode);
    }

    /**
     * 生成验证码
     *
     * @return array
     */
    protected function generateCaptcha(): array
    {
        $width = 120;
        $height = 40;
        $length = 4;

        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $bgColor);

        // 生成随机验证码
        $code = '';
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }

        // 绘制验证码
        for ($i = 0; $i < $length; $i++) {
            $textColor = imagecolorallocate($image, rand(0, 100), rand(0, 100), rand(0, 100));
            imagestring($image, 5, 20 + $i * 25, 10, $code[$i], $textColor);
        }

        // 添加干扰线
        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, rand(150, 200), rand(150, 200), rand(150, 200));
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }

        // 将图像转换为字符串
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return [
            'code' => $code,
            'key' => md5($code . time()),
            'image' => $imageData
        ];
    }

    /**
     * 获取登录日志列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/getLoginLogList', methods: ['GET'], name: 'system.loginLogList')]
    #[Auth(required: true)]
    #[Permission(['core:logs:login'])]
    public function getLoginLogList(Request $request): BaseJsonResponse
    {
        $operator = $this->getOperatorId($request);

        $params = [
            'page' => (int)$this->input('page', 1, true ,$request),
            'limit' => (int)$this->input('limit', 5 , true ,$request),
            'username' => $this->input('username', '', true ,$request),
            'login_status' => $this->input('login_status', '', true ,$request),
            'start_time' => $this->input('start_time', '', true ,$request),
            'end_time' => $this->input('end_time', '', true ,$request),
        ];

        $result = $this->loginLogService->getPageList($params);

        return $this->success($result);
    }

    /**
     * 获取操作日志列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/getOperationLogList', methods: ['GET'], name: 'system.operationLogList')]
    #[Auth(required: true)]
    #[Permission(['core:logs:Oper'])]
    public function getOperationLogList(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int)$this->input('page', 1 , true ,$request),
            'limit' => (int)$this->input('limit', 5 , true ,$request),
            'username' => $this->input('username', '', true ,$request),
            'module' => $this->input('module', '', true ,$request),
            'business_type' => $this->input('business_type', '', true ,$request),
            'status' => $this->input('status', '', true ,$request),
            'start_time' => $this->input('start_time', '', true ,$request),
            'end_time' => $this->input('end_time', '', true ,$request),
        ];
        $operator = $this->getOperatorId($request);

        $result = $this->operationLogService->getPageList($params);

        return $this->success($result);
    }

    /**
     * 上传图片
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/uploadImage', methods: ['POST'], name: 'system.uploadImage')]
    #[Auth(required: true)]
    public function uploadImage(Request $request): BaseJsonResponse
    {
        $file = $request->files->get('file');
        $categoryId = (int)$this->input('category_id', 1);

        if (!$file) {
            return $this->fail('请选择要上传的文件');
        }

        $operator = $this->getOperatorId($request);

        try {
            $result = $this->attachmentService->upload($file, $categoryId, $operator, 'image');
            return $this->success($result, '上传成功');
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 上传文件
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/uploadFile', methods: ['POST'], name: 'system.uploadFile')]
    #[Auth(required: true)]
    public function uploadFile(Request $request): BaseJsonResponse
    {
        $file = $request->files->get('file');
        $categoryId = (int)$this->input('category_id', 0);

        if (!$file) {
            return $this->fail('请选择要上传的文件');
        }

        $operator = $this->getOperatorId($request);

        try {
            $result = $this->attachmentService->upload($file, $categoryId, $operator, 'file');
            return $this->success($result, '上传成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 切片上传
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/chunkUpload', methods: ['POST'], name: 'system.chunkUpload')]
    #[Auth(required: true)]
    public function chunkUpload(Request $request): BaseJsonResponse
    {
        $chunkNumber = (int)$this->input('chunk_number', 1);
        $totalChunks = (int)$this->input('total_chunks', 1);
        $file = $request->files->get('file');
        $fileHash = $this->input('file_hash', '');
        $fileName = $this->input('file_name', '');
        $fileSize = (int)$this->input('file_size', 0);
        $categoryId = (int)$this->input('category_id', 0);

        if (!$file) {
            return $this->fail('请选择要上传的文件');
        }

        // 这里简化处理，实际需要实现分片合并逻辑
        $operator = $this->getOperatorId($request);

        try {
            // 简化：直接上传
            $result = $this->attachmentService->upload($file, $categoryId, $operator, 'file');
            return $this->success([
                'chunk_number' => $chunkNumber,
                'total_chunks' => $totalChunks,
                'file_info' => $result,
                'uploaded' => true
            ], '上传成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取资源分类
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/getResourceCategory', methods: ['GET'], name: 'system.getResourceCategory')]
    #[Auth(required: true)]
    #[Permission(['core:system:resource', 'core:attachment:index'], mode: 'OR')]
    public function getResourceCategory(Request $request): BaseJsonResponse
    {
        $params = [
            'category_name' => $this->input('category_name', ''),
            'status' => $this->input('status', ''),
        ];

        $result = $this->attachmentService->getCategoryList($params);

        return $this->success($result);
    }

    /**
     * 获取资源列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/getResourceList', methods: ['GET'], name: 'system.getResourceList')]
    #[Auth(required: true)]
    #[Permission(['core:system:resource', 'core:attachment:index'], mode: 'OR')]
    public function getResourceList(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int)$this->input('page', 1 , true ,$request),
            'limit' => (int)$this->input('limit', 20 , true ,$request),
            'category_id' => $this->input('category_id', '' , true ,$request),
            'origin_name' => $this->input('origin_name', $this->input('object_name', '', true, $request) , true ,$request),
            'file_ext' => $this->input('file_ext', '' , true ,$request),
        ];

        $result = $this->attachmentService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取用户列表（用于 Auth 中的接口）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/getUserList', methods: ['GET'], name: 'system.getUserList')]
    #[Auth(required: true)]
    #[Permission(['core:system:user', 'core:user:index'], mode: 'OR')]
    public function getUserList(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int)$this->input('page', 1),
            'limit' => (int)$this->input('limit', 20),
            'keyword' => $this->input('keyword', ''),
            'username' => $this->input('username', ''),
            'status' => $this->input('status', ''),
            'dept_id' => $this->input('dept_id', ''),
        ];

        $result = $this->userService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取所有字典数据
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/dictAll', methods: ['GET'], name: 'system.dictAll')]
    //#[Auth(required: false)]
    public function dictAll(Request $request): BaseJsonResponse
    {
        $result = $this->dictService->getAllData();

        return $this->success($result);
    }

    /**
     * 清理所有缓存
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/clearAllCache', methods: ['GET'], name: 'system.clearAllCache')]
    #[Auth(required: true)]
    public function clearAllCache(Request $request): BaseJsonResponse
    {
        try {

            // 清理文件缓存
            $cacheDir = BASE_PATH . '/storage/cache';
            if (is_dir($cacheDir)) {
                $files = glob($cacheDir . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            return $this->success([], '缓存清理成功');
        } catch (\Exception $e) {
            return $this->fail('缓存清理失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取操作人ID
     *
     * @param Request $request 请求对象
     * @return int
     */
    protected function getOperatorId(Request $request): int
    {
        $user = $request->attributes->get('user');
        return $user['id'] ?? 0;
    }

    // ==================== 服务器监控 ====================

    /**
     * 服务监控
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/server/monitor', methods: ['GET'], name: 'system.serverMonitor')]
    #[Auth(required: true)]
    #[Permission(['core:server:monitor'])]
    public function monitor(Request $request): BaseJsonResponse
    {
        $result = $this->serverMonitorService->getServerInfo();
        return $this->success($result);
    }

    /**
     * 缓存列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/server/cache', methods: ['GET'], name: 'system.serverCache')]
    #[Auth(required: true)]
    #[Permission(['core:server:cache'])]
    public function cache(Request $request): BaseJsonResponse
    {
        $result = $this->serverMonitorService->getCacheInfo();
        return $this->success($result);
    }

    /**
     * 清理缓存
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/server/clear', methods: ['POST'], name: 'system.serverClear')]
    #[Auth(required: true)]
    #[Permission(['core:server:clear'])]
    public function clearServerCache(Request $request): BaseJsonResponse
    {
        $result = $this->serverMonitorService->clearCache();
        return $this->success($result);
    }

    // ==================== Redis 监控 ====================

    /**
     * Redis 信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/redis/info', methods: ['GET'], name: 'system.redisInfo')]
    #[Auth(required: true)]
    public function redisInfo(Request $request): BaseJsonResponse
    {
        $result = $this->redisMonitorService->getRedisInfo();
        return $this->success($result);
    }

    /**
     * Redis 操作列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/redis/operations', methods: ['GET'], name: 'system.redisOperations')]
    #[Auth(required: true)]
    public function redisOperations(Request $request): BaseJsonResponse
    {
        $result = $this->redisMonitorService->getOperations();
        return $this->success($result);
    }

    /**
     * Redis 键值列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/redis/keys', methods: ['GET'], name: 'system.redisKeys')]
    #[Auth(required: true)]
    public function redisKeys(Request $request): BaseJsonResponse
    {
        $pattern = $this->input('pattern', '*');
        $result = $this->redisMonitorService->getKeys($pattern);
        return $this->success($result);
    }

    /**
     * 删除 Redis 键
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/redis/deleteKeys', methods: ['DELETE'], name: 'system.redisDeleteKeys')]
    #[Auth(required: true)]
    public function deleteRedisKeys(Request $request): BaseJsonResponse
    {
        $keys = $this->input('keys', []);
        $result = $this->redisMonitorService->deleteKeys($keys);
        return $this->success($result);
    }

    // ==================== 控制台统计 ====================

    /**
     * 基础数据统计（用户数、附件数、登录数、操作数）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/statistics', methods: ['GET'], name: 'system.statistics')]
    #[Auth(required: true)]
    #[Permission(['core:console:list'])]
    public function statistics(Request $request): BaseJsonResponse
    {
        $userCount    = \App\Models\SysUser::count();
        $attachCount  = \App\Models\SysAttachment::count();
        $loginCount   = \App\Models\SysLoginLog::count();
        $operateCount = \App\Models\SysOperationLog::count();

        return $this->success([
            'user'    => $userCount,
            'attach'  => $attachCount,
            'login'   => $loginCount,
            'operate' => $operateCount,
        ]);
    }

    /**
     * 近30天每日登录折线图数据
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/loginChart', methods: ['GET'], name: 'system.loginChart')]
    #[Auth(required: true)]
    #[Permission(['core:console:list'])]
    public function loginChart(Request $request): BaseJsonResponse
    {
        $days = 30;
        $loginDate  = [];
        $loginCount = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $count = \App\Models\SysLoginLog::whereDate('login_time', $date)->count();
            $loginDate[]  = $date;
            $loginCount[] = $count;
        }

        return $this->success([
            'login_date'  => $loginDate,
            'login_count' => $loginCount,
        ]);
    }

    /**
     * 近12个月登录柱状图数据
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/core/system/loginBarChart', methods: ['GET'], name: 'system.loginBarChart')]
    #[Auth(required: true)]
    #[Permission(['core:console:list'])]
    public function loginBarChart(Request $request): BaseJsonResponse
    {
        $months = 12;
        $loginMonth = [];
        $loginCount = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month      = date('Y-m', strtotime("-{$i} months"));
            $startDate  = $month . '-01';
            $endDate    = date('Y-m-t', strtotime($startDate));
            $count = \App\Models\SysLoginLog::where('login_time', '>=', $startDate . ' 00:00:00')
                ->where('login_time', '<=', $endDate . ' 23:59:59')
                ->count();
            $loginMonth[] = $month;
            $loginCount[] = $count;
        }

        return $this->success([
            'login_month' => $loginMonth,
            'login_count' => $loginCount,
        ]);
    }
}
