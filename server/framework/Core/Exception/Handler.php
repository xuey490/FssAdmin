<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core\Exception;

use Framework\Config\ConfigService;
use Psr\Log\LoggerInterface;

class Handler
{
    protected bool $debug;

    private string $requestId;

    public function __construct(ConfigService $config)
    {
        // 从配置服务中读取 debug 模式，默认 false
        $this->debug = $config->get('app.debug', false);

        // 如果是 Web 请求，尝试从 $_SERVER 获取
        $incoming        = $_SERVER['HTTP_X_REQUEST_ID'] ?? null;
        $this->requestId = $incoming ?: generateRequestId();
    }

    /**
     * 报告异常：记录日志.
     */
    public function report(\Throwable $e): void
    {
        /** @var LoggerInterface $logger */
        $logger = app('log');
        // 你可以根据异常类型选择 error、critical 等级别
        $logger->error('Uncaught Exception', [
            'class'      => get_class($e),
            'message'    => $e->getMessage(),
            'file'       => $e->getFile(),
            'line'       => $e->getLine(),
            'trace'      => $e->getTraceAsString(),
            'request_id'	=> $this->requestId,
        ]);
    }

    /**
     * 渲染异常：友好错误，输出给用户.
     */
    public function render(\Throwable $e): void
    {
        if ($this->debug) {
            // 裁剪文件路径
            $file         = $e->getFile();
            $originalFile = $file;
            if (defined('BASE_PATH') && str_starts_with($file, BASE_PATH)) {
                // $file = substr($file, strlen(BASE_PATH));
                $file = ltrim($file, '/\\');
            }

            $line        = $e->getLine();
            $codeSnippet = $this->getCodeSnippet($originalFile, $line);

            // 转义安全内容
            $class   = htmlspecialchars(get_class($e));
            // $message = htmlspecialchars($e->getMessage());
            $displayFile = htmlspecialchars($file);
            $reqId       = htmlspecialchars($this->requestId);

            $message = htmlspecialchars(str_replace(BASE_PATH, '/\\', $e->getMessage()));
            // $fullTrace   = htmlspecialchars(str_replace(BASE_PATH , '' ,$e->getTraceAsString()) );

            $fullTrace = htmlspecialchars($e->getTraceAsString());

            // 默认显示前 N 行（例如 10 行）
            $traceLines   = explode("\n", $fullTrace);
            $previewLines = array_slice($traceLines, 0, 10);
            $hasMore      = count($traceLines) > 10;
            $previewTrace = implode("\n", $previewLines);

            echo <<<HTML
	<style>
	.exception-container {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, monospace;
		max-width: 1000px;
		margin: 20px auto;
		color: #333;
	}
	.exception-header {
		background: #ffebee;
		color: #c62828;
		padding: 16px;
		text-align: center;
		font-weight: bold;
		font-size: 18px;
		border-radius: 4px 4px 0 0;
	}
	.exception-table {
		width: 100%;
		border-collapse: collapse;
		background: #fff;
		box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	}
	.exception-table th,
	.exception-table td {
		padding: 14px 16px;
		text-align: left;
		vertical-align: top;
		border-bottom: 1px solid #eee;
	}
	.exception-table th {
		width: 120px;
		color: #d32f2f;
		font-weight: 600;
	}
	.exception-table pre {
		margin: 0;
		white-space: pre-wrap;
		word-break: break-all;
		font-size: 13px;
		line-height: 1.4;
	}
	.code-snippet {
		background: #f6f8fa;
		border: 1px solid #e1e4e8;
		border-radius: 4px;
		overflow: auto;
		font-family: ui-monospace, SFMono-Regular, 'SF Mono', Consolas, monospace;
		font-size: 12px;
	}
	.code-line {
		display: block;
		padding: 0 12px;
		line-height: 1.5;
	}
	.code-line.highlight {
		background-color: #fffbdd;
		border-left: 3px solid #d9b300;
		color: #000;
		font-weight: bold;
	}
	.code-line-number {
		display: inline-block;
		width: 40px;
		color: #999;
		text-align: right;
		margin-right: 12px;
	}
	.trace-toggle {
		margin-top: 8px;
		font-size: 13px;
		color: #1976d2;
		background: none;
		border: none;
		cursor: pointer;
		text-decoration: underline;
	}
	.trace-toggle:hover {
		color: #0d47a1;
	}
	</style>

	<div class="exception-container">
		<div class="exception-header">FrameWork Run: {$class}</div>
		<table class="exception-table">
			<tr>
				<th>Message</th>
				<td>{$message}</td>
			</tr>
			<tr>
				<th>File</th>
				<td>{$displayFile} (Line: {$line})</td>
			</tr>
			<tr>
				<th>Request ID</th>
				<td><b><code>{$reqId}</code></b></td>
			</tr>
			<tr>
				<th>Code</th>
				<td>
					<pre class="code-snippet">{$codeSnippet}</pre>
				</td>
			</tr>
			<tr>
				<th>Trace</th>
				<td>
					<pre id="trace-preview">{$previewTrace}</pre>
	HTML;

            if ($hasMore) {
                echo <<<HTML
					<button class="trace-toggle" onclick="
						const full = `{$fullTrace}`;
						const pre = document.getElementById('trace-preview');
						pre.textContent = full;
						this.style.display = 'none';
					">Show full trace</button>
	HTML;
            }

            echo <<<'HTML'
				</td>
			</tr>
		</table>
	</div>
	HTML;
        } else {
            http_response_code(500);
            echo '<h1>Server Error</h1>';
            echo "<p>We're sorry, something went wrong on our end.</p>";
            echo '<p><small>Request-ID: <code>' . htmlspecialchars($this->requestId) . '</code></small></p>';
        }
    }

    /**
     * 获取错误行附近的代码片段（带行号和高亮）.
     */
    private function getCodeSnippet(string $filePath, int $errorLine, int $context = 5): string
    {
        if (! file_exists($filePath)) {
            return "<span class='code-line'>// File not found: " . htmlspecialchars($filePath) . '</span>';
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $total = count($lines);

        // 确保行号有效（PHP 行号从 1 开始）
        $errorLine = max(1, min($errorLine, $total));
        $start     = max(0, $errorLine - 1 - $context); // 数组索引从 0 开始
        $end       = min($total, $errorLine + $context);

        $output = '';
        for ($i = $start; $i < $end; ++$i) {
            $lineNum = $i + 1;
            $content = htmlspecialchars($lines[$i]);

            $isErrorLine = ($lineNum === $errorLine);
            $class       = $isErrorLine ? 'code-line highlight' : 'code-line';

            $output .= "<span class='{$class}'>";
            $output .= "<span class='code-line-number'>{$lineNum}</span>";
            $output .= "{$content}";
            $output .= "</span>\n";
        }

        return $output;
    }
}
