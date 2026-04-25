<?php
/**
 * Symfony组件加载统计脚本
 * 使用 get_included_files() 和 get_declared_classes() 分析加载情况
 */

function getSymfonyStats() {
    $includedFiles = get_included_files();
    $declaredClasses = get_declared_classes();

    $stats = [];

    // 按文件分析
    foreach ($includedFiles as $file) {
        if (preg_match('#/vendor/symfony/([^/]+)/#', $file, $matches)) {
            $component = $matches[1];
            if (!isset($stats[$component])) {
                $stats[$component] = ['files' => 0, 'classes' => 0];
            }
            $stats[$component]['files']++;
        }
    }

    // 按类分析
    foreach ($declaredClasses as $class) {
        if (str_starts_with($class, 'Symfony\\')) {
            $parts = explode('\\', $class);
            $component = strtolower($parts[1] ?? 'unknown');
            if (!isset($stats[$component])) {
                $stats[$component] = ['files' => 0, 'classes' => 0];
            }
            $stats[$component]['classes']++;
        }
    }

    return $stats;
}

function printSymfonyStats($stats) {
    echo str_pad("Component", 25)
        . str_pad("Files", 10)
        . str_pad("Classes", 10)
        . PHP_EOL;
    echo str_repeat("-", 45) . PHP_EOL;

    foreach ($stats as $component => $info) {
        echo str_pad($component, 25)
            . str_pad($info['files'], 10)
            . str_pad($info['classes'], 10)
            . PHP_EOL;
    }
}

// 调用统计
$stats = getSymfonyStats();
printSymfonyStats($stats);
