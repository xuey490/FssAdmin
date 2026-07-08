<?php

declare(strict_types=1);

// PHPStan 分析期引导文件：声明运行时全局常量，避免大量 constant.notFound 误报。
// 该文件仅在静态分析时由 PHPStan 加载，不影响运行时逻辑。
if (!defined('BASE_PATH')) {
    define('BASE_PATH', 'C:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project');
}
