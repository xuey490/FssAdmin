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

namespace Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * 创建控制器命令
 *
 * 该命令用于通过命令行快速创建新的控制器类文件。
 * 自动生成包含标准 RESTful 方法（index, create, save, show, edit, update, delete）的控制器模板。
 *
 * 使用方法：
 *   php console make:controller Users
 *
 * @package Framework\Console\Commands
 */
class MakeControllerCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'make:controller';

    /**
     * 命令描述
     *
     * @var string
     */
    protected static $defaultDescription = '创建一个新的控制器类';

    /**
     * 配置命令参数和选项
     *
     * 设置命令名称、描述以及必需的控制器名称参数。
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            // 命令名称（冗余定义，确保兼容性）
            ->setName(self::$defaultName)
            // 命令描述
            ->setDescription(self::$defaultDescription)
            // 添加控制器名称参数
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                '控制器名称（例如：Users 会生成 UsersController）'
            );
    }

    /**
     * 执行命令
     *
     * 创建控制器文件，包含以下步骤：
     * 1. 解析控制器名称并构建文件路径
     * 2. 检查文件是否已存在
     * 3. 创建目标目录（如不存在）
     * 4. 生成控制器内容并写入文件
     *
     * @param InputInterface  $input  输入接口，用于获取命令参数
     * @param OutputInterface $output 输出接口，用于输出结果信息
     *
     * @return int 命令执行状态码（Command::SUCCESS 或 Command::FAILURE）
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $controllerBaseName = $input->getArgument('name');
        $controllerName     = ucfirst($controllerBaseName) . 'Controller';
        $directory          = __DIR__ . '/../../../app/Controllers';
        $filePath           = $directory . '/' . $controllerName . '.php';

        // 检查文件是否已存在
        if (file_exists($filePath)) {
            $output->writeln("<error>错误：控制器 {$controllerName} 已存在于 {$filePath}</error>");
            return Command::FAILURE;
        }

        // 创建文件系统实例
        $filesystem = new Filesystem();

        try {
            // 确保目录存在
            $filesystem->mkdir($directory);

            // 生成控制器内容
            $content = $this->generateControllerContent($controllerName);

            // 写入文件
            $filesystem->dumpFile($filePath, $content);

            $output->writeln('<info>成功生成控制器：</info>');
            $output->writeln("路径：{$filePath}");
            return Command::SUCCESS;
        } catch (IOExceptionInterface $e) {
            $output->writeln('<error>文件操作错误：' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('<error>发生错误：' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }

    /**
     * 生成控制器类内容
     *
     * 创建包含标准 RESTful 方法模板的控制器类代码。
     * 生成的方法包括：index, create, save, show, edit, update, delete。
     *
     * @param string $controllerName 控制器类名
     *
     * @return string 生成的控制器 PHP 代码
     */
    private function generateControllerContent(string $controllerName): string
    {
        return <<<PHP
<?php

namespace App\\Controllers;

use Symfony\\Component\\HttpFoundation\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

class {$controllerName}
{
    /**
     * 显示资源列表
     */
    public function index(Request \$request): Response
    {
        // 实现列表展示逻辑
        return new Response('{$controllerName} index');
    }

    /**
     * 显示创建资源表单
     */
    public function create(Request \$request): Response
    {
        // 实现创建表单展示逻辑
        return new Response('{$controllerName} create');
    }

    /**
     * 保存新创建的资源
     */
    public function save(Request \$request): Response
    {
        // 实现资源保存逻辑
        return new Response('{$controllerName} save');
    }

    /**
     * 显示指定资源
     */
    public function show(Request \$request): Response
    {
        // 实现资源详情展示逻辑
        return new Response('{$controllerName} show');
    }

    /**
     * 显示编辑资源表单
     */
    public function edit(Request \$request): Response
    {
        // 实现编辑表单展示逻辑
        return new Response('{$controllerName} edit');
    }

    /**
     * 更新指定资源
     */
    public function update(Request \$request): Response
    {
        // 实现资源更新逻辑
        return new Response('{$controllerName} update');
    }

    /**
     * 删除指定资源
     */
    public function delete(Request \$request): Response
    {
        // 实现资源删除逻辑
        return new Response('{$controllerName} delete');
    }
}
PHP;
    }
}
