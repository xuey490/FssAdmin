#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * server.php
 * Workerman wrapper for NovaFrame
 *
 * Usage:
 *  - CLI/Workerman mode: php server.php start|stop|restart|reload -d
 *  - Traditional FPM: don't run this script; your existing index.php / front controller remains unchanged.
 *
 * Notes:
 *  - This script initializes Framework per worker process on demand and uses Reflection
 *    to invoke framework internal flow for each request, avoiding modifying Framework.php / Kernel.php.
 */

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;
use Workerman\Protocols\Http\Response as WorkermanResponse;
use Workerman\Timer;
use Workerman\Protocols\Http;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Dotenv\Dotenv;
use Framework\Core\Framework;
#use Symfony\Component\HttpFoundation\Session\Session;
#use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Framework\Schema\SchemaWarmup;
use Framework\Schema\SchemaRegistry;


if (php_sapi_name() !== 'cli') {
    // 如果不是 CLI（即通过 FPM 被包含），什么也不做 -- 以保证 FPM 传统模式兼容
    return;
}

define('WORKERMAN_ENV' , true);


require_once __DIR__ . '/vendor/autoload.php';

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    (new Dotenv())->load($envFile);
}

define('BASE_PATH', __DIR__);
define('APP_ROOT', __DIR__);
define('LOG_DIR', APP_ROOT . '/storage/workerman');
define('HEALTH_FILE', LOG_DIR . '/health.json');

$appDebug = filter_var($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN);
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', $appDebug);
}

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', LOG_DIR . '/php-error.log');

$watchDirs = [
    __DIR__ . '/app',
    __DIR__ . '/framework',
    __DIR__ . '/config',
    __DIR__ . '/routes'
];

if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}

const MEMORY_LIMIT_MB = 256;   // 允许的最大内存（单位 MB）
const MEMORY_CHECK_INTERVAL = 10; // 检查周期（秒）

Worker::$logFile = LOG_DIR .'/workerman.log';



#ini_set('session.save_handler', 'redis');
#ini_set('session.save_path', 'tcp://127.0.0.1:6379');
#session_name('PHPSESSID_');
#session_start();


// ----------------------------------------------------------------------
// 日志工具
// ----------------------------------------------------------------------
function log_info(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents(LOG_DIR . '/server.log', $line, FILE_APPEND);
	//Worker::log($line);
}

function drain_unexpected_output(string $scene): void
{
    if (ob_get_level() <= 0) {
        return;
    }
    $output = ob_get_clean();
    if ($output !== false && trim($output) !== '') {
        log_info("[UnexpectedOutput][$scene] " . trim($output));
    }
}


// Symfony Request / Response 转换
function convert_to_workerman_response(SymfonyResponse $res): WorkermanResponse {
    $headers = [];

    foreach ($res->headers->allPreserveCase() as $name => $values) {
        if (strtolower($name) === 'set-cookie') {
            $headers[$name] = $values;
        } else {
            $headers[$name] = is_array($values) ? implode(', ', $values) : $values;
        }
    }

    $content = $res->getContent();

    // 【修复 Duplicate Content-Length 问题】
    // Workerman 会在 __toString() 中自动添加 Content-Length（如果没有 Transfer-Encoding）
    // 这里不需要手动添加，否则会导致重复的 Content-Length 头
    // 保留 Transfer-Encoding 头（如果有），让 Workerman 正确处理
    // $headers['Transfer-Encoding'] = 'chunked'; // 如果需要分块传输，可以取消注释

    // 移除可能存在的 Content-Length 头，让 Workerman 自动计算
    if (isset($headers['content-length'])) {
        unset($headers['content-length']);
    }

    return new WorkermanResponse($res->getStatusCode(), $headers, $content);
}

/**
 * 修复核心：正确解析 Workerman 上传文件并转换为 Symfony UploadedFile 格式
 */
function convert_to_symfony_request(WorkermanRequest $request): SymfonyRequest
{
    $method = strtoupper($request->method());
    $uri = $request->uri();
    $rawBody = $request->rawBody();
    $remoteIp = $request->connection?->getRemoteIp() ?? '127.0.0.1';
    $remotePort = $request->connection?->getRemotePort() ?? 0;

    $uriParts = parse_url($uri);
    $pathInfo = $uriParts['path'] ?? '/';
    $queryString = $uriParts['query'] ?? '';

    $get = $request->get() ?? [];
    if (!empty($queryString)) {
        parse_str($queryString, $queryParams);
        $get = array_merge($queryParams, $get);
    }

    $post = $request->post() ?? [];
    $cookies = $request->cookie() ?? [];
    
    // ========== 核心修复1：正确解析 Workerman 上传文件 ==========
    $symfonyFiles = [];
    $wmFiles = $request->file() ?? [];
    //error_log('1.Workerman files: ' . print_r($wmFiles, true));
    
    #$wmFiles = $request->file() ?? [];
    #$symfonyFiles = [];

    foreach ($wmFiles as $field => $fileInfo) {

        // 情况1：单文件
        if (isset($fileInfo['tmp_name'])) {

            if (!empty($fileInfo['tmp_name']) && file_exists($fileInfo['tmp_name'])) {

                $symfonyFiles[$field] = new UploadedFile(
                    $fileInfo['tmp_name'],
                    $fileInfo['name'] ?? '',
                    $fileInfo['type'] ?? null,
                    $fileInfo['error'] ?? UPLOAD_ERR_OK,
                    true
                );
            }

            continue;
        }

        // 情况2：多文件（Workerman格式）
        if (is_array($fileInfo)) {

            $files = [];

            foreach ($fileInfo as $index => $item) {

                if (
                    !isset($item['tmp_name']) ||
                    empty($item['tmp_name']) ||
                    !file_exists($item['tmp_name'])
                ) {
                    continue;
                }

                $files[$index] = new UploadedFile(
                    $item['tmp_name'],
                    $item['name'] ?? '',
                    $item['type'] ?? null,
                    $item['error'] ?? UPLOAD_ERR_OK,
                    true
                );
            }

            if ($files) {
                $symfonyFiles[$field] = $files;
            }
        }
    }
    //dump($symfonyFiles);

    $headers = $request->header() ?? [];

    $parameters = array_merge($get, $post);

    $server = [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI' => $uri,
        'PATH_INFO' => $pathInfo,
        'QUERY_STRING' => $queryString,
        'REMOTE_ADDR' => $remoteIp,
        'REMOTE_PORT' => $remotePort,
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'HTTP_HOST' => $headers['host'] ?? 'localhost',
        'CONTENT_LENGTH' => $headers['content-length'] ?? strlen($rawBody),
        'CONTENT_TYPE' => $headers['content-type'] ?? '',
        'PHP_SELF' => $pathInfo,
        'SCRIPT_NAME' => $pathInfo,
        'SCRIPT_FILENAME' => '',
    ];

    foreach ($headers as $name => $value) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $server[$key] = is_array($value) ? implode(', ', $value) : $value;
    }

    if (!isset($server['HTTP_X_FORWARDED_FOR'])) {
        $server['HTTP_X_FORWARDED_FOR'] = $remoteIp;
    }

    if (in_array($method, ['PUT', 'DELETE', 'PATCH']) && empty($post) && !empty($rawBody)) {
        parse_str($rawBody, $parsedPost);
        $post = array_merge($post, $parsedPost);
    }

    // ========== 核心修复2：传入解析后的 Symfony 文件数组 ==========
    $symfonyRequest = new SymfonyRequest(
        $get,
        $post,
        [],
        $cookies,
        $symfonyFiles, // 替换原来的 $request->file()
        $server,
        $rawBody
    );
    #$session = app('session');
    #$symfonyRequest->setSession($session);

    //error_log('Symfony Request files->all(): ' . print_r($symfonyRequest->files->all(), true));
    //dump($symfonyRequest->files->all());
    return $symfonyRequest;
}


// ----------------------------------------------------------------------
// 健康信息与日志轮转
// ----------------------------------------------------------------------
function update_health(): void {
    $health = [
        'pid'     => getmypid(),
        'memory'  => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'time'    => date('Y-m-d H:i:s'),
        'uptime'  => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2) . ' s',
        'php'     => PHP_VERSION,
        'os'      => PHP_OS,
    ];
    file_put_contents(HEALTH_FILE, json_encode($health, JSON_PRETTY_PRINT));
}

function rotate_logs(): void {
    $file = LOG_DIR . '/server.log';
    if (file_exists($file) && filesize($file) > 2 * 1024 * 1024) {
        $new = LOG_DIR . '/server-' . date('Ymd_His') . '.log';
        rename($file, $new);
        log_info("[LogRotate] Rotated to $new");
    }
}

// ----------------------------------------------------------------------
// 检查文件是否变更（带过滤）
// ----------------------------------------------------------------------
function checkFilesChange(array $watchDirs, array &$lastMtimes): bool
{
    $excludeSuffixes = ['.tmp', '.swp', '.bak', '.~', '.part', '.log', '.lock'];
    $excludeDirs = ['\\.git\\', '\\.idea\\', '\\vendor\\', '\\runtime\\', '\\storage\\', '\\node_modules\\'];

    foreach ($watchDirs as $dir) {
        $dir = str_replace('/', '\\', $dir);
        if (!is_dir($dir)) continue;

        try {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
                )
            );
        } catch (Exception $e) {
            continue; // 跳过无法读取的目录
        }

        foreach ($it as $file) {
            if (!$file->isFile()) continue;

            $path = str_replace('/', '\\', $file->getRealPath());
            if (!$path) continue;

            $filename = $file->getFilename();
            $filepath = str_replace('/', '\\', $file->getPath());

            // 跳过临时文件
            foreach ($excludeSuffixes as $suffix) {
                if (str_ends_with($filename, $suffix)) {
                    continue 2;
                }
            }

            // 跳过排除目录
            foreach ($excludeDirs as $exDir) {
                if (stripos($filepath, $exDir) !== false) {
                    continue 2;
                }
            }

            $mtime = $file->getMTime();
            if (!isset($lastMtimes[$path])) {
                $lastMtimes[$path] = $mtime;
                continue;
            }

            if ($mtime !== $lastMtimes[$path]) {
                $lastMtimes[$path] = $mtime;
                echo "[HotReload] File changed: {$path}\r\n";
                return true;
            }
        }
    }

    return false;
}

// ----------------------------------------------------------------------
// 启动工作进程
// ----------------------------------------------------------------------
function startWorkerProcess() {
    $script = $_SERVER['argv'][0] ?? __FILE__;
    $cmd = '"' . PHP_BINARY . '" "' . $script . '" --worker';
    $descriptorspec = [STDIN, STDOUT, STDOUT];
    $resource = proc_open($cmd, $descriptorspec, $pipes, null, null, ['bypass_shell' => true]);
    
    if (!$resource) {
        exit("Can not execute $cmd\r\n");
    }
    
    return $resource;
}

/**
 * 获取文件的 MIME 类型
 * @param string $filePath 文件路径
 * @return string MIME 类型
 */
function get_mime_type(string $filePath): string
{
    // 基于文件扩展名的 MIME 类型映射
    $mimeTypes = [
        // 图片
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'bmp'  => 'image/bmp',
        
        // 视频
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'ogg'  => 'video/ogg',
        
        // 音频
        'mp3'  => 'audio/mpeg',
        'wav'  => 'audio/wav',
        'ogg'  => 'audio/ogg',
        
        // 文档
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        
        // 文本
        'txt'  => 'text/plain',
        'html' => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        
        // 压缩文件
        'zip'  => 'application/zip',
        'rar'  => 'application/vnd.rar',
        '7z'   => 'application/x-7z-compressed',
        'tar'  => 'application/x-tar',
        'gz'   => 'application/gzip',
        
        // 字体
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
    ];
    
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

// ----------------------------------------------------------------------
// 主监控循环
// ----------------------------------------------------------------------
function startMonitorLoop($watchDirs): void {
    echo "[Monitor] Starting server with hot reload...\n";
    echo "Watching directories:\n";
    foreach ($watchDirs as $dir) {
        echo "  - {$dir}\n";
    }
    echo "Access: http://localhost:8000\n";
    echo "Health: http://localhost:8000/_health\n";
    echo "Press Ctrl+C to stop\n\n";
    
    $lastMtimes = [];
    $resource = startWorkerProcess();

    // 初始化文件时间戳
    checkFilesChange($watchDirs, $lastMtimes);
    
    while (true) {
        sleep(1);
        
        // 检查文件变化
        if (checkFilesChange($watchDirs, $lastMtimes)) {
            echo "[Monitor] File changes detected, restarting worker...\n";
            log_info("[Monitor] File changes detected, restarting worker...");
            
            // 杀死旧进程
            $status = proc_get_status($resource);
            $pid = $status['pid'];
            
            if ($pid && $status['running']) {
                if (stripos(PHP_OS, 'WIN') !== false) {
                    shell_exec("taskkill /F /T /PID $pid >nul 2>&1");
                } else {
                    posix_kill($pid, SIGKILL);
                }
				log_info("[Monitor] Killed worker process: $pid\n");
                echo "[Monitor] Killed worker process: $pid\n";
            }
            log_info("[Monitor] Worker Restart\n");
            proc_close($resource);
            
            // 启动新进程
            $resource = startWorkerProcess();
            echo "[Monitor] Started new worker process\n";
            log_info("[Monitor] Started new worker process");
        }
        
        // 检查工作进程是否意外退出
        $status = proc_get_status($resource);
        if (!$status['running']) {
            echo "[Monitor] Worker process died, restarting...\n";
            log_info("[Monitor] Worker process died, restarting...");
            proc_close($resource);
            $resource = startWorkerProcess();
            echo "[Monitor] Restarted worker process\n";
        }
        
        update_health();
    }
}

// ----------------------------------------------------------------------
// 判断是否工作进程
// ----------------------------------------------------------------------
function isWorkerProcess(): bool {
    global $argv;
    return isset($argv[1]) && $argv[1] === '--worker';
}

// ----------------------------------------------------------------------
// 主程序入口
// ----------------------------------------------------------------------

// 如果是工作进程模式，启动 Worker
if (isWorkerProcess()) {
    $worker = new Worker('http://0.0.0.0:8000');
    $worker->name = 'NovaFrame-Worker';
    $worker->count = 1;
    $framework = null;

    $worker->onWorkerStart = function(Worker $worker) use (&$framework) {
		

		
        log_info("[Worker] PID " . getmypid() . " started");
		Worker::log("[Worker] PID " . getmypid() . " started");
        update_health();

        $framework = Framework::getInstance();

		if (defined('WORKERMAN_ENV')) {
			// 设置扫描目录和命名空间
			SchemaWarmup::setScanPath(base_path('app/Models'), 'App\Models');

			// 可选：忽略某些模型
			SchemaWarmup::ignore([
				\App\Models\TempView::class,
			]);

			// 启动时自动扫描 warmup
			SchemaWarmup::warmupAll();

			// 冻结 schema，防止 runtime 注册新表
			SchemaRegistry::freeze();

			// 调试：打印已注册表
			//dump(array_keys(SchemaRegistry::all()));
		}
			
        // 定时任务：监控内存 + 日志轮转 + 健康记录
	
		Timer::add(MEMORY_CHECK_INTERVAL, function() use ($worker) {
			#print_r($worker);
			update_health();
            rotate_logs();
			
			$pid = getmypid();
			$memory = memory_get_usage(true) / 1024 / 1024; // MB
			$time = date('Y-m-d H:i:s');
			#Worker::log(base_path());
			Worker::log("[{$time}] [Memory] Worker #{$worker->id} PID {$pid} uses {$memory} MB\n" );

			// 如果超出阈值，则安全重启当前 worker
			if ($memory > MEMORY_LIMIT_MB) {
				Worker::log( "[{$time}] [Warning] Worker #{$worker->id} PID {$pid} memory exceeded limit ({$memory} MB > " . MEMORY_LIMIT_MB . " MB), reloading...\n" );

				// 使用 stopAll 仅关闭当前进程
				$worker->stop();

				// 给操作系统一点时间释放资源
				usleep(500000); // 0.5 秒
				
				exit(1); // 退出让监控进程重启

				// 自动拉起新 worker（Workerman 会自动 fork 新进程）
				posix_kill(posix_getppid(), SIGUSR1);
			}
		});

    };

    $worker->onMessage = function(TcpConnection $connection, WorkermanRequest $req) use (&$framework) {
        if (ob_get_level() === 0) {
            ob_start();
        }
        try {
            // ==================== 静态文件处理 ====================
            $uri = $req->uri();
            $pathInfo = parse_url($uri, PHP_URL_PATH);
            
            // 检查是否是静态文件请求
            $staticDirs = ['/uploads', '/assets', '/css', '/js', '/images', '/favicon.ico'];
            $isStaticFile = false;
            
            foreach ($staticDirs as $dir) {
                if (strpos($pathInfo, $dir) === 0) {
                    $isStaticFile = true;
                    break;
                }
            }
            
            if ($isStaticFile) {
                $filePath = __DIR__ . '/public' . $pathInfo;
                
                // 安全检查：防止路径遍历攻击
                $realPath = realpath($filePath);
                $publicDir = realpath(__DIR__ . '/public');
                
                if ($realPath && strpos($realPath, $publicDir) === 0 && is_file($realPath)) {
                    // 返回静态文件
                    $contentType = get_mime_type($realPath);
                    $fileContent = file_get_contents($realPath);
                    
                    // 添加缓存控制头
                    $headers = [
                        'Content-Type' => $contentType,
                        'Cache-Control' => 'public, max-age=86400', // 缓存1天
                    ];
                    
                    // 图片等静态文件添加更长的缓存时间
                    if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $realPath)) {
                        $headers['Cache-Control'] = 'public, max-age=2592000'; // 缓存30天
                    }
                    
                    $connection->send(new WorkermanResponse(200, $headers, $fileContent));
                    drain_unexpected_output('static_file_success');
                    return;
                }
                
                // 文件不存在，返回 404
                $connection->send(new WorkermanResponse(404, ['Content-Type' => 'text/plain'], 'File Not Found'));
                drain_unexpected_output('static_file_404');
                return;
            }
            // ==================== 静态文件处理结束 ====================

            if ($req->path() === '/_health') {
                update_health();
                $data = file_get_contents(HEALTH_FILE);
                $response = new SymfonyResponse($data, 200, ['Content-Type' => 'application/json']);
                $connection->send(convert_to_workerman_response($response));
                drain_unexpected_output('health_check');
                return;
            }

            $symReq = convert_to_symfony_request($req);
            $symRes = $framework->handleRequest($symReq);
			
			// ✅ 关键：在 send 之前关闭 session，触发写入 Redis
			if ($symReq->hasSession()) {
				$symReq->getSession()->save(); // 或 ->save() / ->close()
			}
			
			// ✅ 如果在业务逻辑里 queueCookie() 了 Cookie，统一发送
			app('cookie')->sendQueuedCookies($symRes);
           
			$connection->send(convert_to_workerman_response($symRes));
            drain_unexpected_output('request_success');

        } catch (Throwable $e) {
            $error = "[Error] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}";
            log_info($error);
            drain_unexpected_output('request_exception');
            $connection->send(new WorkermanResponse(500, [], "Internal Error: {$e->getMessage()}"));
		} finally {
			// 可选：清理
			if (isset($symReq) && $symReq->hasSession()) {
				//$symReq->getSession()->clear(); // 避免内存泄漏
			}
            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
			unset($symReq , $symRes);
			gc_collect_cycles();
		}
    };


    Worker::runAll();
    exit(0);
}

// 主进程：启动监控循环
startMonitorLoop($watchDirs);

