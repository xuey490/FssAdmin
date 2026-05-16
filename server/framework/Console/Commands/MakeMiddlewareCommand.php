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
 * 创建中间件命令
 *
 * 该命令用于通过命令行快速创建新的中间件类文件。
 * 自动生成包含标准 handle 方法的中间件模板，支持请求前后处理逻辑。
 *
 * 使用方法：
 *   php console make:middleware Auth
 *
 * @package Framework\Console\Commands
 */
class MakeMiddlewareCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'make:middleware';

    /**
     * 命令描述
     *
     * @var string
     */
    protected static $defaultDescription = '创建一个新的中间件类';

    /**
     * 配置命令参数和选项
     *
     * 设置命令名称、描述以及必需的中间件名称参数。
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            // 确保命令名称被正确设置
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                '中间件名称（例如：Auth 会生成 Auth 中间件）'
            );
    }

    /**
     * 执行命令
     *
     * 创建中间件文件，包含以下步骤：
     * 1. 解析中间件名称并构建文件路径
     * 2. 检查文件是否已存在
     * 3. 创建目标目录（如不存在）
     * 4. 生成中间件内容并写入文件
     *
     * @param InputInterface  $input  输入接口，用于获取命令参数
     * @param OutputInterface $output 输出接口，用于输出结果信息
     *
     * @return int 命令执行状态码（Command::SUCCESS 或 Command::FAILURE）
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $middlewareName = $input->getArgument('name');
        $className      = ucfirst($middlewareName);
        $directory      = __DIR__ . '/../../../app/Middlewares';
        $filePath       = $directory . '/' . $className . '.php';

        // 检查文件是否已存在
        if (file_exists($filePath)) {
            $output->writeln("<error>错误：中间件 {$className} 已存在于 {$filePath}</error>");
            return Command::FAILURE;
        }

        // 创建文件系统实例
        $filesystem = new Filesystem();

        try {
            // 确保目录存在
            $filesystem->mkdir($directory);

            // 生成中间件内容
            $content = $this->generateMiddlewareContent($className);

            // 写入文件
            $filesystem->dumpFile($filePath, $content);

            $output->writeln('<info>成功生成中间件：</info>');
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
     * 生成中间件类内容
     *
     * 创建包含标准 handle 方法的中间件类模板。
     * 模板支持在请求处理前后执行自定义逻辑。
     *
     * @param string $className 中间件类名
     *
     * @return string 生成的中间件 PHP 代码
     */
    private function generateMiddlewareContent(string $className): string
    {
        return <<<PHP
<?php

namespace App\\Middlewares;

use Symfony\\Component\\HttpFoundation\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

class {$className}
{
    /**
     * 处理请求
     *
     * @param  Request  \$request
     * @param  \\Closure  \$next
     * @return Response
     */
    public function handle(Request \$request, \\Closure \$next): Response
    {
        // 在请求处理前执行的逻辑
        // 例如：身份验证、权限检查等

        \$response = \$next(\$request);

        // 在请求处理后执行的逻辑
        // 例如：添加响应头、日志记录等

        return \$response;
    }
}
PHP;
    }
}
