<?php

declare(strict_types=1);

/**
 * This file is part of Fssphp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: MakePluginCommand.php
 * @Date: 2025-03-31
 * @Developer: Fssphp Team
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

/**
 * 创建插件骨架命令
 *
 * 快速生成一个新插件的目录结构和基础文件。
 *
 * 使用方法：
 *   php novaphp make:plugin blog
 *
 * @package Framework\Console\Commands
 */
class MakePluginCommand extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected static $defaultName = 'make:plugin';

    /**
     * 文件系统实例
     *
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this->setName(self::$defaultName)
             ->setDescription('创建插件骨架')
             ->addArgument('name', InputArgument::REQUIRED, '插件名称')
             ->setHelp('此命令创建一个新插件的目录结构和基础文件。');
    }

    /**
     * 执行命令
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        // 验证插件名称
        if (!preg_match('/^[a-z][a-z0-9_-]*$/i', $name)) {
            $io->error("无效的插件名称 '{$name}'：必须以字母开头，只能包含字母、数字、下划线和短横线");
            return Command::FAILURE;
        }

        $this->filesystem = new Filesystem();
        $pluginDir = BASE_PATH . '/plugins/' . $name;

        // 检查插件是否已存在
        if ($this->filesystem->exists($pluginDir)) {
            $io->error("插件 '{$name}' 已存在");
            return Command::FAILURE;
        }

        $io->title("创建插件: {$name}");

        try {
            // 创建目录结构
            $this->createDirectoryStructure($pluginDir);

            // 创建插件清单
            $this->createPluginManifest($pluginDir, $name);

            // 创建基础控制器
            $this->createBaseController($pluginDir, $name);

            // 创建示例模型
            $this->createSampleModel($pluginDir, $name);

            // 创建示例服务
            $this->createSampleService($pluginDir, $name);

            // 创建迁移目录
            $this->filesystem->mkdir($pluginDir . '/database/migrations');

            // 创建配置目录
            $this->filesystem->mkdir($pluginDir . '/config');

            // 创建资源目录
            $this->filesystem->mkdir($pluginDir . '/resources/views', 0755);

            $io->newLine();
            $io->success("插件 '{$name}' 创建成功！");
            $io->text([
                '插件目录: ' . $pluginDir,
                '',
                '下一步：',
                "  1. 编辑 plugin.json 配置插件信息",
                "  2. 在 Controllers 目录创建控制器",
                "  3. 在 database/migrations 目录创建迁移文件",
                "  4. 运行 php novaphp plugin:install {$name} 安装插件",
            ]);

        } catch (\Throwable $e) {
            $io->error("创建插件失败: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 创建目录结构
     *
     * @param string $pluginDir
     */
    private function createDirectoryStructure(string $pluginDir): void
    {
        $directories = [
            $pluginDir . '/Controllers',
            $pluginDir . '/Models',
            $pluginDir . '/Services',
            $pluginDir . '/Providers',
            $pluginDir . '/database/migrations',
            $pluginDir . '/config',
            $pluginDir . '/resources/views',
        ];

        foreach ($directories as $dir) {
            $this->filesystem->mkdir($dir, 0755);
        }
    }

    /**
     * 创建插件清单文件
     *
     * @param string $pluginDir
     * @param string $name
     */
    private function createPluginManifest(string $pluginDir, string $name): void
    {
        $className = $this->toClassName($name);
        $content = [
            'name' => $name,
            'title' => $className . ' Plugin',
            'version' => '1.0.0',
            'description' => $className . ' plugin for Fssphp',
            'author' => 'Your Name',
            'namespace' => "Plugins\\{$className}",
            'requires' => [
                'php' => '^8.3',
                'Fssphp' => '^0.8.0',
            ],
            'dependencies' => [],
            'autoload' => [
                'psr-4' => [
                    "Plugins\\{$className}\\" => '',
                ],
            ],
            'routes' => [
                'prefix' => '/' . $name,
            ],
        ];

        $json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->filesystem->dumpFile($pluginDir . '/plugin.json', $json);
    }

    /**
     * 创建基础控制器
     *
     * @param string $pluginDir
     * @param string $name
     */
    private function createBaseController(string $pluginDir, string $name): void
    {
        $className = $this->toClassName($name);
        $controllerContent = <<<PHP
<?php

declare(strict_types=1);

namespace Plugins\\{$className}\\Controllers;

use Framework\\Basic\\BaseController;
use Framework\\Basic\\BaseJsonResponse;
use Framework\\Attributes\\Route;
use Symfony\\Component\\HttpFoundation\\Request;

class IndexController extends BaseController
{
    #[Route(path: '/{$name}', methods: ['GET'], name: '{$name}.index')]
    public function index(Request \$request): BaseJsonResponse
    {
        return \$this->success([
            'message' => '{$className} Plugin is working!',
        ]);
    }
}
PHP;

        $this->filesystem->dumpFile($pluginDir . '/Controllers/IndexController.php', $controllerContent);
    }

    /**
     * 创建示例模型
     *
     * @param string $pluginDir
     * @param string $name
     */
    private function createSampleModel(string $pluginDir, string $name): void
    {
        $className = $this->toClassName($name);
        $modelContent = <<<PHP
<?php

declare(strict_types=1);

namespace Plugins\\{$className}\\Models;

use Framework\\Utils\\BaseModel;

/**
 * 示例模型
 *
 * 请根据实际需求修改此模型。
 */
class Sample extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected \$table = '{$name}_samples';

    /**
     * 可批量赋值的字段
     *
     * @var array
     */
    protected \$fillable = [
        'name',
        'status',
    ];

    /**
     * 字段类型转换
     *
     * @var array
     */
    protected \$casts = [
        'status' => 'integer',
    ];
}
PHP;

        $this->filesystem->dumpFile($pluginDir . '/Models/Sample.php', $modelContent);
    }

    /**
     * 创建示例服务
     *
     * @param string $pluginDir
     * @param string $name
     */
    private function createSampleService(string $pluginDir, string $name): void
    {
        $className = $this->toClassName($name);
        $serviceContent = <<<PHP
<?php

declare(strict_types=1);

namespace Plugins\\{$className}\\Services;

use Framework\\Basic\\BaseService;

/**
 * 示例服务
 *
 * 请根据实际需求修改此服务。
 */
class SampleService extends BaseService
{
    /**
     * 获取示例数据
     *
     * @return array
     */
    public function getSamples(): array
    {
        return [
            ['id' => 1, 'name' => 'Sample 1'],
            ['id' => 2, 'name' => 'Sample 2'],
        ];
    }
}
PHP;

        $this->filesystem->dumpFile($pluginDir . '/Services/SampleService.php', $serviceContent);
    }

    /**
     * 将插件名称转换为类名格式
     *
     * @param string $name
     * @return string
     */
    private function toClassName(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
    }
}
