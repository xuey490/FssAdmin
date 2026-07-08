<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-25
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugMiddleware implements MiddlewareInterface
{
    /** @var bool 是否启用调试输出 */
    protected bool $debug;

    public function __construct(bool $debug = true)
    {
        $this->debug = $debug;
    }

    /**
     * 中间件入口.
     */
    public function handle(Request $request, callable $next): Response
    {
        $requestDebugInfo = '';
        if ($this->debug) {
            $requestDebugInfo = $this->dumpRequest($request);
        }
        // === 执行下一个中间件 / 控制器 ===
        $response = $next($request);

        // === 响应阶段 ===
        $responseDebugInfo  = '';
        $frameworkDebugInfo = '';
        
        if ($this->debug) {
            // 检测是否为 Ajax 请求
            $isAjax = $request->isXmlHttpRequest() || 
                      str_contains($request->headers->get('Accept', ''), 'application/json');

            // 如果是 Ajax 请求，直接返回，不注入调试信息
            if ($isAjax) {
                return $response;
            }

            // 收集响应信息
            $responseDebugInfo = $this->dumpResponse($response);

            // 收集框架运行时信息
            $frameworkDebugInfo = $this->dumpFrameworkInfo();

            $body = (string) $response->getContent();

            // 更可靠的 HTML 检测
            $isHtml = false;
            $contentType = $response->headers->get('Content-Type', '');
            if (stripos($contentType, 'application/json') !== false) {
                $isHtml = false;
            } elseif (
                stripos($body, '<html')      !== false
                || stripos($body, '</body>') !== false
                || stripos($body, '<div')    !== false
                || stripos($body, '<h')      !== false
                || stripos($body, '<span')   !== false
            ) {
                $isHtml = true;
            }

            if ($isHtml && ($requestDebugInfo || $responseDebugInfo || $frameworkDebugInfo)) {
                // 构建带开关的Tab切换调试面板
                $debugHtml = $this->buildDebugPanel($requestDebugInfo, $responseDebugInfo, $frameworkDebugInfo);

                // 注入到 </body> 标签前
                $pos = strripos($body, '</body>');
                if ($pos !== false) {
                    $body = substr_replace($body, $debugHtml . '</body>', $pos, strlen('</body>'));
                } else {
                    $body .= $debugHtml; // 回退
                }

                $response->setContent($body);
            }
        }

        return $response;
    }

    /**
     * 构建带开关的Tab切换模式Debug面板
     */
    protected function buildDebugPanel(string $requestInfo, string $responseInfo, string $frameworkInfo): string
    {
        // 内联CSS样式（新增开关按钮样式+折叠逻辑）
        $styles = <<<CSS
        <style>
            /* 调试面板开关按钮 */
            .debug-toggle-btn {
                position: fixed;
                bottom: 0;
                right: 5px;
                z-index: 99999;
                padding: 8px 15px;
                background-color: #007acc;
                color: white;
                border: none;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
                cursor: pointer;
                font-family: Consolas, Menlo, Courier, monospace;
                font-weight: bold;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);
                transition: background-color 0.2s ease;
            }

            .debug-toggle-btn:hover {
                background-color: #005ea6;
            }

            /* 调试面板容器 - 固定在底部，默认隐藏 */
            .debug-panel-container {
                clear: both;
                background-color: #1e1e1e;
                border-top: 3px solid #007acc;
                font-family: Consolas, Menlo, Courier, monospace;
                font-size: 13px;
                z-index: 99998;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                line-height: 1.6;
                text-align: left;
                max-height: 80vh;
                box-sizing: border-box;
                display: none; /* 默认隐藏 */
            }

            /* 面板展开时显示 */
            .debug-panel-container.show {
                display: block;
            }

            /* Tab导航栏 */
            .debug-tabs {
                display: flex;
                background-color: #333337;
                border-bottom: 1px solid #444;
                overflow-x: auto;
                white-space: nowrap;
                scrollbar-width: thin;
            }

            /* Tab按钮 */
            .debug-tab {
                padding: 10px 20px;
                cursor: pointer;
                border: none;
                background: none;
                color: #a0a0a0;
                font-weight: bold;
                font-family: inherit;
                font-size: 14px;
                position: relative;
                transition: color 0.2s ease;
            }

            .debug-tab:hover {
                color: #00a3ff;
            }

            /* 激活的Tab样式 */
            .debug-tab.active {
                color: #00a3ff;
            }

            .debug-tab.active::after {
                content: '';
                position: absolute;
                bottom: -1px;
                left: 0;
                right: 0;
                height: 2px;
                background-color: #007acc;
            }

            /* Tab内容区域 */
            .debug-tab-content {
                display: none;
                padding: 15px;
                background-color: #1e1e1e;
                color: #d4d4d4;
                max-height: calc(20vh - 15px);
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #444 #1e1e1e;
            }

            /* 激活的内容显示 */
            .debug-tab-content.active {
                display: block;
            }

            /* 代码样式 */
            .debug-pre {
                padding: 15px;
                margin: 0;
                background-color: #252526;
                white-space: pre-wrap;
                word-wrap: break-word;
                border-radius: 4px;
                border: 1px solid #444;
            }

            /* 关闭按钮样式 */
            .debug-close-btn {
                position: absolute;
                top: 10px;
                right: 10px;
                padding: 5px 10px;
                background-color: #333337;
                color: #ff6b6b;
                border: 1px solid #444;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
                transition: background-color 0.2s ease;
            }

            .debug-close-btn:hover {
                background-color: #444;
            }

            /* 滚动条样式优化 */
            .debug-tab-content::-webkit-scrollbar,
            .debug-tabs::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            .debug-tab-content::-webkit-scrollbar-track,
            .debug-tabs::-webkit-scrollbar-track {
                background: #252526;
            }

            .debug-tab-content::-webkit-scrollbar-thumb,
            .debug-tabs::-webkit-scrollbar-thumb {
                background-color: #444;
                border-radius: 4px;
            }
        </style>
        CSS;

        // Tab切换+折叠开关核心JS
        $script = <<<JS
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 元素获取
                const toggleBtn = document.getElementById('debug-toggle');
                const debugPanel = document.querySelector('.debug-panel-container');
                const closeBtn = document.getElementById('debug-close');
                const tabs = document.querySelectorAll('.debug-tab');
                const contents = document.querySelectorAll('.debug-tab-content');
                
                // 默认激活第一个Tab（仅在面板展开时生效）
                const activateFirstTab = () => {
                    if (tabs.length > 0) {
                        tabs.forEach(t => t.classList.remove('active'));
                        contents.forEach(c => c.classList.remove('active'));
                        tabs[0].classList.add('active');
                        contents[0].classList.add('active');
                    }
                };

                // 展开面板逻辑
                toggleBtn.addEventListener('click', function() {
                    debugPanel.classList.add('show');
                    activateFirstTab();
                    // 按钮移到面板内，避免遮挡
                    toggleBtn.style.display = 'none';
                });

                // 关闭面板逻辑
                closeBtn.addEventListener('click', function() {
                    debugPanel.classList.remove('show');
                    toggleBtn.style.display = 'block';
                });

                // Tab切换逻辑
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        // 移除所有激活状态
                        tabs.forEach(t => t.classList.remove('active'));
                        contents.forEach(c => c.classList.remove('active'));
                        
                        // 激活当前Tab
                        this.classList.add('active');
                        const target = this.getAttribute('data-target');
                        document.getElementById(target).classList.add('active');
                    });
                });
            });
        </script>
        JS;

        // 构建各个Tab的内容
        $tabs = [];
        $contents = [];
        
        // 请求信息Tab
        if ($requestInfo) {
            $tabs[] = '<button class="debug-tab" data-target="debug-request">Request Info</button>';
            $contents[] = <<<HTML
            <div id="debug-request" class="debug-tab-content">
                <pre class="debug-pre">{$this->escapeHtml($requestInfo)}</pre>
            </div>
            HTML;
        }

        // 框架信息Tab
        if ($frameworkInfo) {
            $tabs[] = '<button class="debug-tab" data-target="debug-framework">Framework Runtime</button>';
            $contents[] = <<<HTML
            <div id="debug-framework" class="debug-tab-content">
                <pre class="debug-pre">{$this->escapeHtml($frameworkInfo)}</pre>
            </div>
            HTML;
        }

        // 响应信息Tab
        if ($responseInfo) {
            $tabs[] = '<button class="debug-tab" data-target="debug-response">Response Info</button>';
            $contents[] = <<<HTML
            <div id="debug-response" class="debug-tab-content">
                <pre class="debug-pre">{$this->escapeHtml($responseInfo)}</pre>
            </div>
            HTML;
        }

        // 拼接最终HTML（新增开关按钮+关闭按钮）
        $debugHtml = $styles . <<<HTML
        <!-- 调试面板开关按钮 -->
        <button id="debug-toggle" class="debug-toggle-btn">🚀 Debug Panel</button>

        <!-- 调试面板容器 -->
        <div class="debug-panel-container">
            <!-- 关闭按钮 -->
            <button id="debug-close" class="debug-close-btn">× Close</button>
            
            <div class="debug-tabs">
                {$this->joinHtml($tabs)}
            </div>
            <div class="debug-tab-contents">
                {$this->joinHtml($contents)}
            </div>
        </div>
        {$script}
        HTML;

        return $debugHtml;
    }

    /**
     * HTML转义辅助方法
     */
    protected function escapeHtml(string $content): string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }

    /**
     * HTML拼接辅助方法
     * @param array<mixed> $parts
 */
    protected function joinHtml(array $parts): string
    {
        return implode("\n", $parts);
    }

    /**
     * 收集并格式化框架运行时信息
     */
    protected function dumpFrameworkInfo(): string
    {
        $output = "================= [FRAMEWORK RUNTIME] =================\n";

        // 1. 包含的文件
        $includedFiles = get_included_files();
        $output .= 'Included Files Count: ' . count($includedFiles) . "\n\n";

        // 2. 加载的类
        $loadedClasses        = get_declared_classes();
        $userClasses          = [];
        $internalClassesCount = 0;

        foreach ($loadedClasses as $class) {
            try {
                $ref = new \ReflectionClass($class);
                if ($ref->isInternal()) {
                    ++$internalClassesCount;
                } else {
                    $userClasses[] = $class;
                }
            } catch (\Throwable $e) {
                ++$internalClassesCount;
            }
        }

        $userClassesCount  = count($userClasses);
        $totalClassesCount = $userClassesCount + $internalClassesCount;

        $output .= 'Total Loaded Classes: ' . $totalClassesCount . "\n";
        $output .= 'User-Defined Classes: ' . $userClassesCount . "\n";
        $output .= 'PHP Internal Classes: ' . $internalClassesCount . "\n";

        // 3. 列出用户定义的类
        $output .= "\n--- User-Defined Class List (" . $userClassesCount . ") ---\n";

        if (empty($userClasses)) {
            $output .= "(none)\n";
        } else {
            sort($userClasses);
            $output .= "(hidden for brevity)\n"; // 如需显示类列表，替换为 implode("\n", $userClasses) . "\n"
        }

        $output .= "==========================================================\n\n";
        return $output;
    }

    /**
     * 打印请求信息
     */
    protected function dumpRequest(Request $request): string
    {
        $output = "==================== [REQUEST DEBUG] ====================\n";
        $output .= 'Method: ' . $request->getMethod() . "\n";
        $output .= 'Path:   ' . $request->getPathInfo() . "\n";
        $output .= 'Client: ' . $request->getClientIp() . "\n";
        $output .= "\n--- Headers ---\n";
        foreach ($request->headers->all() as $key => $values) {
            $output .= sprintf("%s: %s\n", $key, implode(', ', $values));
        }
        $output .= "\n--- Query Params ---\n";
        $output .= $request->query->all() ? print_r($request->query->all(), true) : "(none)\n";
        $output .= "\n--- POST Body ---\n";
        if ($request->request->all()) {
            $output .= print_r($request->request->all(), true);
        } elseif ($raw = $request->getContent()) {
            $output .= $raw . "\n";
        } else {
            $output .= "(empty)\n";
        }
        $output .= "==========================================================\n\n";
        return $output;
    }

    /**
     * 打印响应信息
     */
    protected function dumpResponse(Response $response): string
    {
        $output = "\n==================== [RESPONSE DEBUG] ====================\n";
        $output .= 'Status: ' . $response->getStatusCode() . "\n";
        $output .= "\n--- Headers ---\n";
        foreach ($response->headers->allPreserveCase() as $key => $values) {
            foreach ($values as $v) {
                $output .= sprintf("%s: %s\n", $key, $v);
            }
        }
        $output .= "==========================================================\n\n";
        return $output;
    }
}