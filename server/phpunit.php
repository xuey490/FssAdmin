#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$frameworkDir = __DIR__ . '/framework';
$testsDir = __DIR__ . '/tests/Unit';

if (!is_dir($testsDir)) {
    mkdir($testsDir, 0755, true);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($frameworkDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;

    $relativePath = str_replace($frameworkDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
    $className = str_replace(DIRECTORY_SEPARATOR, '\\', substr($relativePath, 0, -4)); // 移除 .php

    // 假设你的框架命名空间是 Framework（请根据实际情况修改！）
    $fullClassName = 'Framework\\' . $className;

    // 生成测试类名
    $testClassName = $className . 'Test';
    $testFilePath = $testsDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $testClassName) . '.php';

    // 创建目录
    $testFileDir = dirname($testFilePath);
    if (!is_dir($testFileDir)) {
        mkdir($testFileDir, 0755, true);
    }

    // 如果测试文件已存在，跳过
    if (file_exists($testFilePath)) {
        echo "Skip: $testFilePath (exists)\n";
        continue;
    }

    // 生成测试类内容
    $namespace = 'Tests\\Unit\\' . str_replace('\\', '\\', dirname($className));
    if ($className === basename($className)) {
        $namespace = 'Tests\\Unit';
    }

    $useStatement = "use {$fullClassName};";

    $content = "<?php\n\n";
    $content .= "namespace {$namespace};\n\n";
    $content .= "use PHPUnit\\Framework\\TestCase;\n";
    $content .= "{$useStatement}\n\n";
    $content .= "class " . basename($testClassName) . " extends TestCase\n";
    $content .= "{\n";
    $content .= "    public function test_example(): void\n";
    $content .= "    {\n";
    $content .= "        \$this->assertTrue(true);\n";
    $content .= "    }\n";
    $content .= "}\n";

    file_put_contents($testFilePath, $content);
    echo "Created: $testFilePath\n";
}

echo "✅ Test files generation completed.\n";