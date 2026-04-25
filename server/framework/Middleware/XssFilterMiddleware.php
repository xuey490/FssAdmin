<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class XssFilterMiddleware
{
    private bool $enabled = true;
    private ?\HTMLPurifier $purifier = null;
    
    // 默认不处理的字段，例如密码字段不应被修改
    private array $except = [
        'password',
        'password_confirmation',
        '_token'
    ];

    public function __construct(bool $enabled = true, array $allowedHtml = [])
    {
        $this->enabled = $enabled;

        // 只有当明确指定了允许的 HTML 标签时，才初始化重型的 Purifier
        if ($enabled && !empty($allowedHtml)) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', sys_get_temp_dir());
            $config->set('HTML.Allowed', implode(',', array_map(
                fn($tag) => $tag . '[*]', // 允许标签及其属性，具体属性可更细致配置
                $allowedHtml
            )));
            // 允许常见协议
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            $this->purifier = new \HTMLPurifier($config);
        }
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->enabled) {
            return $next($request);
        }

        // 1. 过滤 GET 参数
        if ($request->query->count() > 0) {
            $request->query = new InputBag($this->filterArray($request->query->all()));
        }

        // 2. 过滤 JSON Body (优先处理，因为 request->all() 可能依赖它)
        $this->filterJsonBody($request);

        // 3. 过滤 POST 参数
        if ($request->request->count() > 0) {
            $request->request = new InputBag($this->filterArray($request->request->all()));
        }

        // 4. 过滤 FILES
        // 注意：通常不建议直接修改 files bag，因为这涉及到底层文件流
        // 但如果确实要重命名上传的文件名，可以保留此逻辑
        if ($request->files->count() > 0) {
            $filteredFiles = $this->filterFiles($request->files->all());
            $request->files->replace($filteredFiles);
        }

        return $next($request);
    }

    private function filterJsonBody(Request $request): void
    {
        $contentType = $request->headers->get('Content-Type');
        if ($contentType && str_contains($contentType, 'application/json')) {
            $content = $request->getContent();
            if ($content !== '') {
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    // 过滤数据
                    $filteredData = $this->filterArray($data);
                    // 将过滤后的数据合并到 request (POST) 中，方便控制器读取
                    // 注意：这里替换了 Json 内容对应的 ParameterBag
                    $request->request->replace($filteredData);
                }
            }
        }
    }

    private function filterFiles(array $files): array
    {
        $filteredFiles = [];
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $filteredFiles[$key] = $this->filterFiles($file);
            } elseif ($file instanceof UploadedFile) {
                // 清洗文件名
                $sanitizedName = $this->sanitizeFileName($file->getClientOriginalName());
                
                // 只有当文件名发生变化时才重新创建对象，节省开销
                if ($sanitizedName !== $file->getClientOriginalName()) {
                    $filteredFiles[$key] = new UploadedFile(
                        $file->getPathname(),
                        $sanitizedName,
                        $file->getMimeType(),
                        $file->getError(),
                        true
                    );
                } else {
                    $filteredFiles[$key] = $file;
                }
            }
        }
        return $filteredFiles;
    }

    private function sanitizeFileName(string $fileName): string
    {
        $fileName = basename($fileName);
        // 允许 Unicode 字符 (中文等)，只过滤掉控制字符和非法的文件系统字符
        // \p{L} 匹配任何语言的字母，\p{N} 匹配数字
        // 也可以简单地只过滤掉危险字符： / \ : * ? " < > |
        $fileName = preg_replace('/[^\p{L}\p{N}\.\-\_\(\)\s]/u', '', $fileName);
        
        // 限制长度，防止文件名过长
        return mb_substr($fileName, 0, 255);
    }

    private function filterArray(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            // 跳过不需要过滤的字段（如 password）
            if (in_array($key, $this->except, true)) {
                $clean[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = $this->filterArray($value);
            } elseif (is_string($value)) {
                $clean[$key] = $this->sanitize($value);
            } else {
                $clean[$key] = $value;
            }
        }
        return $clean;
    }

    /**
     * 核心清洗逻辑
     */
    private function sanitize(string $input): string
    {
        // 1. 如果配置了 Purifier (允许部分 HTML)，则使用它
        if ($this->purifier) {
            return $this->purifier->purify($input);
        }

        // 2. 如果没有配置允许的 HTML，则默认视为纯文本
        // 策略：彻底移除标签，保留内容。
        // 不做 htmlspecialchars，防止双重转义。
        $input = strip_tags($input);

        // 3. 移除不可见的控制字符 (ASCII 0-31, 127)，保留换行符
        // 这种过滤是安全的，可以防止一些二进制注入或截断攻击
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);

        return trim($input);
    }
}