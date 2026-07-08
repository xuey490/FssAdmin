<?php

declare(strict_types=1);

/**
 * 代码生成 Service
 *
 * @package App\Services
 * @author  yl_chen
 * @date    2026-03-29
 */

namespace App\Services;

use App\Dao\ToolGenerateColumnDao;
use App\Dao\ToolGenerateTableDao;
use App\Models\ToolGenerateColumn;
use App\Models\ToolGenerateTable;
use Framework\Basic\BaseService;

/**
 * @extends BaseService<ToolGenerateColumnDao>
 */
class ToolGenerateService extends BaseService
{
    protected ToolGenerateTableDao  $tableDao;
    protected ToolGenerateColumnDao $columnDao;
    protected DatabaseMaintainService $dbService;

    public function __construct()
    {
        parent::__construct();
        $this->tableDao  = new ToolGenerateTableDao();
        $this->columnDao = new ToolGenerateColumnDao();
        $this->dbService = new DatabaseMaintainService();
    }

    // ==================== 基础 CRUD ====================

    /**
     * 分页列表
     *
     * @param array<array-key, mixed> $params
     * @return array<array-key, mixed> {items, total}
     */
    public function getPageList(array $params): array
    {
        $result = $this->tableDao->getPageList($params);
        // 格式化时间字段
        $items = array_map([$this, 'formatTableRow'], $result['list']);
        return ['items' => $items, 'total' => $result['total']];
    }

    /**
     * 读取表详情（含 columns 数组）
     *
     * @param int $id
     * @return array<array-key, mixed>
     * @throws \Exception
     */
    public function getDetail(int $id): array
    {
        $table = $this->tableDao->findById($id);
        if (!$table) {
            throw new \Exception('记录不存在');
        }

        $tableArr = $this->formatTableRow($table->toArray());

        // 解析 options
        $options = [];
        if (!empty($tableArr['options'])) {
            $decoded = is_array($tableArr['options'])
                ? $tableArr['options']
                : json_decode($tableArr['options'], true);
            $options = is_array($decoded) ? $decoded : [];
        }
        $tableArr['options'] = $options;

        // 获取字段配置
        $columns = $this->columnDao->getByTableId($id);
        $tableArr['columns'] = array_map([$this, 'formatColumnRow'], $columns);

        return $tableArr;
    }

    /**
     * 更新表配置（含 columns 数组）
     *
     * @param int   $id
     * @param array<array-key, mixed> $data
     * @param int   $operatorId
     * @return void
     * @throws \Exception
     */
    public function updateConfig(int $id, array $data, int $operatorId): void
    {
        $table = $this->tableDao->findById($id);
        if (!$table) {
            throw new \Exception('记录不存在');
        }

        $columns  = $data['columns']  ?? null;
        $now      = date('Y-m-d H:i:s');

        // 处理 options（树表配置 + relations）
        $existingOptions = [];
        if (!empty($table->options)) {
            $decoded = is_array($table->options)
                ? $table->options
                : json_decode($table->options, true);
            $existingOptions = is_array($decoded) ? $decoded : [];
        }

        // 合并树表配置
        if (isset($data['options']) && is_array($data['options'])) {
            $existingOptions = array_merge($existingOptions, $data['options']);
        }

        // 单独字段写入 options
        foreach (['tree_id', 'tree_pid', 'tree_name'] as $key) {
            if (isset($data[$key])) {
                $existingOptions[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        // relations 存入 options
        if (isset($data['relations'])) {
            $existingOptions['relations'] = $data['relations'];
            unset($data['relations']);
        }

        // 构建表更新数据
        $updateData = array_intersect_key($data, array_flip([
            'table_comment', 'stub', 'template', 'namespace', 'package_name',
            'business_name', 'class_name', 'menu_name', 'belong_menu_id',
            'tpl_category', 'generate_type', 'generate_path', 'generate_model',
            'generate_menus', 'build_menu', 'component_type', 'form_width',
            'is_full', 'remark', 'source',
        ]));
        
        // 如果 generate_menus 是数组，转换为逗号分隔的字符串
        if (isset($updateData['generate_menus']) && is_array($updateData['generate_menus'])) {
            $updateData['generate_menus'] = implode(',', $updateData['generate_menus']);
        }

        $updateData['options']    = json_encode($existingOptions, JSON_UNESCAPED_UNICODE);
        $updateData['updated_by'] = $operatorId;
        $updateData['update_time']= $now;

        $this->transaction(function () use ($table, $updateData, $columns, $id, $operatorId, $now) {
            $table->update($updateData);

            // 更新字段配置
            if ($columns !== null && is_array($columns)) {
                $this->updateColumns($id, $columns, $operatorId, $now);
            }
        });
    }

    /**
     * 批量删除（级联删除字段）
     *
     * @param array<array-key, mixed> $ids
     * @param int   $operatorId
     * @return int
     * @throws \Exception
     */
    public function deleteByIds(array $ids, int $operatorId): int
    {
        if (empty($ids)) {
            throw new \Exception('请选择要删除的记录');
        }

        $count = 0;
        $this->transaction(function () use ($ids, &$count) {
            // 级联删除字段
            $this->columnDao->deleteByTableIds($ids);
            // 删除主表
            $count = $this->tableDao->batchDeleteByIds($ids);
        });

        return $count;
    }

    /**
     * 获取字段列表
     *
     * @param int $tableId
     * @return array<array-key, mixed>
     * @throws \Exception
     */
    public function getColumns(int $tableId): array
    {
        $table = $this->tableDao->findById($tableId);
        if (!$table) {
            throw new \Exception('记录不存在');
        }
        $columns = $this->columnDao->getByTableId($tableId);
        return array_map([$this, 'formatColumnRow'], $columns);
    }

    /**
     * 装载数据表（从 DB 读取结构写入代码生成配置表）
     *
     * @param string $source
     * @param array<array-key, mixed>  $names   [{name, comment, sourceName}]
     * @param int    $operatorId
     * @return array<array-key, mixed> {success, failed}
     */
    public function loadTable(string $source, array $names, int $operatorId): array
    {
        $success = [];
        $failed  = [];
        $now     = date('Y-m-d H:i:s');

        foreach ($names as $item) {
            $tableName   = $item['name']    ?? '';
            $tableComment= $item['comment'] ?? '';

            if (empty($tableName)) continue;

            if ($this->tableDao->isTableLoaded($tableName, $source)) {
                $failed[] = ['name' => $tableName, 'reason' => '已装载，请勿重复添加'];
                continue;
            }

            try {
                $this->transaction(function () use (
                    $tableName, $tableComment, $source, $operatorId, $now, &$success
                ) {
                    // 1. 创建主表记录
                    $className   = $this->tableNameToClassName($tableName);
                    $businessName= $this->tableNameToBusinessName($tableName);

                    $tableRecord = ToolGenerateTable::query()->create([
                        'table_name'    => $tableName,
                        'table_comment' => $tableComment,
                        'stub'          => 'app',
                        'template'      => 'app',
                        'namespace'     => 'App',
                        'package_name'  => '',
                        'business_name' => $businessName,
                        'class_name'    => $className,
                        'menu_name'     => $tableComment ?: $businessName,
                        'belong_menu_id'=> 0,
                        'tpl_category'  => 'single',
                        'generate_type' => ToolGenerateTable::GENERATE_TYPE_ZIP,
                        'generate_path' => 'web-admin',
                        'generate_model'=> ToolGenerateTable::GENERATE_MODEL_SOFT,
                        'generate_menus'=> 'list,add,edit,delete',
                        'build_menu'    => 1,
                        'component_type'=> 1,
                        'options'       => '{}',
                        'form_width'    => 800,
                        'is_full'       => 1,
                        'remark'        => '',
                        'source'        => $source,
                        'created_by'    => $operatorId,
                        'updated_by'    => $operatorId,
                        'create_time'   => $now,
                        'update_time'   => $now,
                    ]);

                    // 2. 读取数据库表字段结构
                    $dbColumns = $this->dbService->getTableDetailed($tableName);

                    // 3. 批量插入字段配置
                    $rows = [];
                    foreach ($dbColumns as $sort => $col) {
                        $isPk = !empty($col['column_key']) && strtolower($col['column_key']) === 'pri';
                        $row  = ToolGenerateColumnDao::buildDefaultColumn(
                            array_merge($col, ['is_pk' => $isPk]),
                            $tableRecord->id,
                            $sort
                        );
                        $row['created_by'] = $operatorId;
                        $row['updated_by'] = $operatorId;
                        $row['create_time']= $now;
                        $row['update_time']= $now;
                        $rows[]            = $row;
                    }
                    $this->columnDao->batchInsert($rows);

                    $success[] = $tableName;
                });
            } catch (\Throwable $e) {
                $failed[] = ['name' => $tableName, 'reason' => $e->getMessage()];
            }
        }

        return ['success' => $success, 'failed' => $failed];
    }

    /**
     * 同步表结构（从DB重新读取字段，保留现有配置）
     *
     * @param int $id
     * @param int $operatorId
     * @return void
     * @throws \Exception
     */
    public function syncTable(int $id, int $operatorId): void
    {
        $table = $this->tableDao->findById($id);
        if (!$table) {
            throw new \Exception('记录不存在');
        }

        $dbColumns = $this->dbService->getTableDetailed($table->table_name);

        $this->transaction(function () use ($id, $dbColumns, $operatorId) {
            $this->columnDao->syncColumns($id, $dbColumns, $operatorId);
        });
    }

    // ==================== 代码生成 ====================

    /**
     * 预览代码（返回代码数组）
     *
     * @param int $id
     * @return array<array-key, mixed>
     * @throws \Exception
     */
    public function previewCode(int $id): array
    {
        $detail = $this->getDetail($id);
        return $this->renderAllTemplates($detail);
    }

    /**
     * 生成代码压缩包（返回 zip 二进制内容）
     *
     * @param array<array-key, mixed> $ids
     * @return string zip 二进制
     * @throws \Exception
     */
    public function generateZip(array $ids): string
    {
        if (!class_exists('ZipArchive')) {
            throw new \Exception('ZipArchive 扩展未安装，无法生成压缩包');
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'generate_') . '.zip';
        $zip     = new \ZipArchive();

        if ($zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('创建压缩包失败');
        }

        foreach ($ids as $id) {
            try {
                $detail   = $this->getDetail((int)$id);
                $files    = $this->renderAllTemplates($detail);
                $basePath = $this->buildFilePaths($detail);

                foreach ($files as $file) {
                    $name    = $file['name'];
                    $code    = $file['code'];
                    $relPath = $basePath[$name] ?? ($name . '.txt');
                    $zip->addFromString($relPath, $code);
                }
            } catch (\Throwable $e) {
                // 跳过失败的表，继续处理
            }
        }

        $zip->close();
        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return $content !== false ? $content : '';
    }

    /**
     * 生成代码到项目文件
     *
     * @param array<int|string> $ids
     * @param int   $operatorId
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function generateFile(array $ids, int $operatorId): array
    {
        $success = [];
        $failed  = [];
        $baseDir = dirname(__DIR__, 2);  // 项目根目录

        foreach ($ids as $id) {
            try {
                $detail   = $this->getDetail((int)$id);
                $files    = $this->renderAllTemplates($detail);
                $basePaths= $this->buildFilePaths($detail);

                foreach ($files as $file) {
                    $name     = $file['name'];
                    $code     = $file['code'];
                    $relPath  = $basePaths[$name] ?? null;
                    if (!$relPath) continue;

                    $absPath = $baseDir . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $relPath), DIRECTORY_SEPARATOR);
                    $dir     = dirname($absPath);

                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    file_put_contents($absPath, $code);
                    $success[] = $relPath;
                }
            } catch (\Throwable $e) {
                $failed[] = ['id' => $id, 'reason' => $e->getMessage()];
            }
        }

        return ['success' => $success, 'failed' => $failed];
    }

    // ==================== 模板渲染 ====================

    /**
     * 渲染所有代码文件
     *
     * @param array<array-key, mixed> $detail
     * @return array<array-key, mixed>
     */
    protected function renderAllTemplates(array $detail): array
    {
        $className    = $detail['class_name']    ?? $this->tableNameToClassName($detail['table_name'] ?? '');
        $businessName = $detail['business_name'] ?? '';
        $namespace    = rtrim($detail['namespace'] ?? 'App', '\\');
        $packageName  = $detail['package_name']  ?? '';
        $tplCategory  = $detail['tpl_category']  ?? 'single';
        $tableComment = $detail['table_comment'] ?? $className;
        $tableName    = $detail['table_name']    ?? '';
        $columns      = $detail['columns']       ?? [];
        $options      = $detail['options']        ?? [];
        $generateModel = (int)($detail['generate_model'] ?? 1);
        $belongMenuId  = (int)($detail['belong_menu_id'] ?? 0);
        $menuName      = $detail['menu_name']     ?? $tableComment;
        $componentType = (int)($detail['component_type'] ?? 1);
        $formWidth     = $detail['form_width']    ?? '800px';
        $isFull        = (int)($detail['is_full'] ?? 2) === 1;

        $generateMenus = ['index', 'save', 'update', 'read', 'destroy'];
        if (!empty($detail['generate_menus'])) {
            $rawMenus = $detail['generate_menus'];
            if (is_array($rawMenus)) {
                $generateMenus = $rawMenus;
            } elseif (is_string($rawMenus)) {
                // 因为 formatTableRow 已经去除了多余转义字符并转换成了逗号分隔的字符串
                $cleanMenus = str_replace(['"', '[', ']', '\\'], '', $rawMenus);
                $generateMenus = explode(',', $cleanMenus);
            }
        }

        $ctx = compact(
            'className', 'businessName', 'namespace', 'packageName',
            'tplCategory', 'tableComment', 'tableName', 'columns', 'options', 'generateModel',
            'belongMenuId', 'menuName', 'generateMenus', 'componentType', 'formWidth', 'isFull'
        );

        return [
            [
                'name'     => 'controller',
                'tab_name' => "{$className}Controller.php",
                'lang'     => 'php',
                'code'     => $this->renderController($ctx),
            ],
            [
                'name'     => 'service',
                'tab_name' => "{$className}Service.php",
                'lang'     => 'php',
                'code'     => $this->renderService($ctx),
            ],
            [
                'name'     => 'dao',
                'tab_name' => "{$className}Dao.php",
                'lang'     => 'php',
                'code'     => $this->renderDao($ctx),
            ],
            [
                'name'     => 'model',
                'tab_name' => "{$className}.php",
                'lang'     => 'php',
                'code'     => $this->renderModel($ctx),
            ],
            [
                'name'     => 'vue_index',
                'tab_name' => 'index.vue',
                'lang'     => 'vue',
                'code'     => $this->renderVueIndex($ctx),
            ],
            [
                'name'     => 'vue_form',
                'tab_name' => 'form.vue',
                'lang'     => 'vue',
                'code'     => $this->renderVueForm($ctx),
            ],
            [
                'name'     => 'vue_search',
                'tab_name' => 'table-search.vue',
                'lang'     => 'vue',
                'code'     => $this->renderVueSearch($ctx),
            ],
            [
                'name'     => 'sql',
                'tab_name' => 'menu.sql',
                'lang'     => 'sql',
                'code'     => $this->renderMenuSql($ctx),
            ],
        ];
    }

    /**
     * 构建文件路径映射
     *
     * @param array<array-key, mixed> $detail
     * @return array<array-key, mixed>
     */
    protected function buildFilePaths(array $detail): array
    {
        $className    = $detail['class_name']    ?? $this->tableNameToClassName($detail['table_name'] ?? '');
        $packageName  = $detail['package_name']  ?? '';
        $businessName = $detail['business_name'] ?? strtolower($className);
        $generatePath = $detail['generate_path'] ?? 'saiadmin-artd';

        $controllerDir = 'app/Controllers' . ($packageName ? "/{$packageName}" : '');
        $serviceDir    = 'app/Services';
        $daoDir        = 'app/Dao';
        $modelDir      = 'app/Models';
        $vueDir        = "web/src/views/{$businessName}";

        return [
            'controller' => "{$controllerDir}/{$className}Controller.php",
            'service'    => "{$serviceDir}/{$className}Service.php",
            'dao'        => "{$daoDir}/{$className}Dao.php",
            'model'      => "{$modelDir}/{$className}.php",
            'vue_index'  => "{$vueDir}/index.vue",
            'vue_form'   => "{$vueDir}/modules/form.vue",
            'vue_search' => "{$vueDir}/modules/table-search.vue",
            'sql'        => "menu.sql",
        ];
    }

    // ==================== PHP 模板 ====================

    /**
     * 渲染 Controller 代码
     *
     * @param array<string, mixed> $ctx
     */
    protected function renderController(array $ctx): string
    {
        [
            'className'    => $className,
            'businessName' => $businessName,
            'namespace'    => $namespace,
            'tableComment' => $tableComment,
            'packageName'  => $packageName,
        ] = $ctx;

        $serviceClass  = "{$className}Service";
        $controllerNS  = $namespace . '\\Controllers' . ($packageName ? "\\{$packageName}" : '');
        $serviceNS     = $namespace . '\\Services\\' . $serviceClass;
        $routeBase     = '/api/' . strtolower($businessName ?: $this->classNameToRouteName($className));
        $date          = date('Y-m-d');

        return <<<PHP
<?php

declare(strict_types=1);


namespace {$controllerNS};

use {$serviceNS};
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

class {$className}Controller extends BaseController
{
    protected {$serviceClass} \${$className}service;

    protected function initialize(): void
    {
        \$this->{$className}service = new {$serviceClass}();
    }

    /**
     * 列表
     */
    #[Route(path: '{$routeBase}/index', methods: ['GET'], name: '{$businessName}.index')]
    #[Auth(required: true)]
    public function index(Request \$request): BaseJsonResponse
    {
        \$params = \$request->query->all();
        \$result = \$this->{$className}service->getPageList(\$params);
        return \$this->success(\$result);
    }

    /**
     * 详情
     */
    #[Route(path: '{$routeBase}/detail/{id}', methods: ['GET'], name: '{$businessName}.detail')]
    #[Auth(required: true)]
    public function detail(Request \$request): BaseJsonResponse
    {
        \$id = (int)\$request->attributes->get('id');
        try {
            \$result = \$this->{$className}service->getDetail(\$id);
            return \$this->success(\$result);
        } catch (\Exception \$e) {
            return \$this->fail(\$e->getMessage());
        }
    }

    /**
     * 新增
     */
    #[Route(path: '{$routeBase}/create', methods: ['POST'], name: '{$businessName}.create')]
    #[Auth(required: true)]
    public function create(Request \$request): BaseJsonResponse
    {
        \$data = \$this->inputAll(\$request);
        \$operatorId = (int)(\$request->attributes->get('user')['id'] ?? 0);
        try {
            \$result = \$this->{$className}service->create(\$data, \$operatorId);
            return \$this->success(['id' => \$result->id], '创建成功');
        } catch (\Exception \$e) {
            return \$this->fail(\$e->getMessage());
        }
    }

    /**
     * 更新
     */
    #[Route(path: '{$routeBase}/update/{id}', methods: ['PUT'], name: '{$businessName}.update')]
    #[Auth(required: true)]
    public function update(Request \$request): BaseJsonResponse
    {
        \$id   = (int)\$request->attributes->get('id');
        \$data = \$this->inputAll(\$request);
        \$operatorId = (int)(\$request->attributes->get('user')['id'] ?? 0);
        try {
            \$this->{$className}service->update(\$id, \$data, \$operatorId);
            return \$this->success([], '更新成功');
        } catch (\Exception \$e) {
            return \$this->fail(\$e->getMessage());
        }
    }

    /**
     * 删除
     */
    #[Route(path: '{$routeBase}/destroy', methods: ['DELETE'], name: '{$businessName}.destroy')]
    #[Auth(required: true)]
        /**
         */
    public function destroy(Request \$request): BaseJsonResponse
    {
        \$data = \$this->inputAll(\$request);
        \$ids  = isset(\$data['ids']) ? array_map('intval', (array)\$data['ids']) : [];
        if (!empty(\$data['id'])) \$ids[] = (int)\$data['id'];
        if (empty(\$ids)) return \$this->fail('请选择要删除的记录');

        try {
            \$operatorId = (int)(\$request->attributes->get('user')['id'] ?? 0);
            \$count = \$this->{$className}service->deleteByIds(\$ids, \$operatorId);
            return \$this->success(['count' => \$count], '删除成功');
        } catch (\Exception \$e) {
            return \$this->fail(\$e->getMessage());
        }
    }
}
PHP;
    }

    /**
     * 渲染 Service 代码
     *
     * @param array<string, mixed> $ctx
     */
    protected function renderService(array $ctx): string
    {
        [
            'className'    => $className,
            'businessName' => $businessName,
            'namespace'    => $namespace,
            'tableComment' => $tableComment,
            'columns'      => $columns,
        ] = $ctx;

        $daoClass  = "{$className}Dao";
        $serviceNS = $namespace . '\\Services';
        $daoNS     = $namespace . '\\Dao\\' . $daoClass;
        $date      = date('Y-m-d');

        // 构建搜索条件
        $whereLines = [];
        foreach ($columns as $col) {
            if ((int)($col['is_query'] ?? 0) === ToolGenerateColumn::FLAG_YES) {
                $colName = $col['column_name'] ?? '';
                $queryType = $col['query_type'] ?? 'eq';
                
                switch ($queryType) {
                    case 'eq':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', '=', \$params['{$colName}']];";
                        break;
                    case 'neq':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', '<>', \$params['{$colName}']];";
                        break;
                    case 'gt':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', '>', \$params['{$colName}']];";
                        break;
                    case 'gte':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', '>=', \$params['{$colName}']];";
                        break;
                    case 'lt':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', '<', \$params['{$colName}']];";
                        break;
                    case 'lte':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', '<=', \$params['{$colName}']];";
                        break;
                    case 'like':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', 'like', '%' . \$params['{$colName}'] . '%'];";
                        break;
                    case 'in':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', 'in', is_array(\$params['{$colName}']) ? \$params['{$colName}'] : explode(',', \$params['{$colName}'])];";
                        break;
                    case 'notin':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && \$params['{$colName}'] !== '') \$where[] = ['{$colName}', 'not in', is_array(\$params['{$colName}']) ? \$params['{$colName}'] : explode(',', \$params['{$colName}'])];";
                        break;
                    case 'between':
                        $whereLines[] = "        if (isset(\$params['{$colName}']) && is_array(\$params['{$colName}']) && count(\$params['{$colName}']) === 2) \$where[] = ['{$colName}', 'between', \$params['{$colName}']];";
                        break;
                }
            }
        }
        $whereStr = !empty($whereLines) ? implode("\n", $whereLines) : "        // 没有设置搜索字段";

        return <<<PHP
<?php

declare(strict_types=1);


namespace {$serviceNS};

use {$daoNS};
use Framework\Basic\BaseService;

class {$className}Service extends BaseService
{
    protected {$daoClass} \${$className}dao;

    public function __construct()
    {
        parent::__construct();
        \$this->{$className}dao = new {$daoClass}();
    }

    /**
     * 分页列表
     */
    public function getPageList(array \$params): array
    {
        [\$page, \$limit] = \$this->PageParams(\$params);
        \$where = [];
{$whereStr}
        \$result = \$this->{$className}dao->selectList(\$where, '*', \$page, \$limit, 'id desc');
        \$total  = \$this->{$className}dao->count(\$where);
        return ['items' => \$result, 'total' => \$total];
    }

    /**
     * 详情
     */
    public function getDetail(int \$id): array
    {
        \$record = \$this->{$className}dao->get(\$id);
        if (!\$record) throw new \Exception('记录不存在');
        return (array)\$record;
    }

    /**
     * 创建
     */
    public function create(array \$data, int \$operatorId): object
    {
        \$now = date('Y-m-d H:i:s');
        \$data['created_by'] = \$operatorId;
        \$data['updated_by'] = \$operatorId;
        \$data['create_time']= \$now;
        \$data['update_time']= \$now;
        return \$this->{$className}dao->save(\$data);
    }

    /**
     * 更新
     */
        /**
         */
    public function update(int \$id, array \$data, int \$operatorId): void
    {
        \$data['updated_by'] = \$operatorId;
        \$data['update_time']= date('Y-m-d H:i:s');
        \$this->{$className}dao->update(\$id, \$data);
    }

    /**
     * 删除
     */
    public function deleteByIds(array \$ids, int \$operatorId = 0): int
    {
        return \$this->{$className}dao->delete(\$ids);
    }
}
PHP;
    }

    /**
     * 渲染 Dao 代码
     *
     * @param array<string, mixed> $ctx
     */
    protected function renderDao(array $ctx): string
    {
        [
            'className'    => $className,
            'namespace'    => $namespace,
            'tableComment' => $tableComment,
        ] = $ctx;

        $modelNS = $namespace . '\\Models\\' . $className;
        $daoNS   = $namespace . '\\Dao';
        $date    = date('Y-m-d');

        return <<<PHP
<?php

declare(strict_types=1);


namespace {$daoNS};

use {$modelNS};
use Framework\Basic\BaseDao;

class {$className}Dao extends BaseDao
{
    protected function setModel(): string
    {
        return {$className}::class;
    }
}
PHP;
    }

    /**
     * 渲染 Model 代码
     *
     * @param array<string, mixed> $ctx
     */
    protected function renderModel(array $ctx): string
    {
        [
            'className'    => $className,
            'tableName'    => $tableName,
            'namespace'    => $namespace,
            'tableComment' => $tableComment,
            'columns'      => $columns,
            'options'      => $options,
            'tplCategory'  => $tplCategory,
            'generateModel'=> $generateModel,
        ] = $ctx;

        $modelNS    = $namespace . '\\Models';
        $date       = date('Y-m-d');
        $softDelete = $generateModel === ToolGenerateTable::GENERATE_MODEL_SOFT;
        
        $imports = [
            "Framework\\Basic\\BaseLaORMModel" => true,
        ];
        if ($softDelete) {
            $imports["Illuminate\\Database\\Eloquent\\SoftDeletes"] = true;
        }

        $traitStr   = $softDelete ? "\n    use SoftDeletes;\n" : '';
        $deletedAt  = $softDelete ? "\n    const DELETED_AT = 'delete_time';\n" : '';

        // 构建 fillable
        $fillableFields = [];
        $hasSort = false;
        foreach ($columns as $col) {
            $colName = $col['column_name'] ?? '';
            if ($colName === 'sort') {
                $hasSort = true;
            }
            if (in_array($colName, ['id', 'create_time', 'update_time', 'delete_time'])) continue;
            if ((int)($col['is_pk'] ?? 1) === ToolGenerateColumn::IS_PK_YES) continue;
            $fillableFields[] = "        '{$colName}'";
        }
        $fillableStr = implode(",\n", $fillableFields);

        // 构建 casts
        $castLines = [];
        foreach ($columns as $col) {
            $colName = $col['column_name'] ?? '';
            $colType = strtolower($col['column_type'] ?? '');
            if (str_contains($colType, 'int')) {
                $castLines[] = "        '{$colName}' => 'integer'";
            } elseif (str_contains($colType, 'decimal') || str_contains($colType, 'float')) {
                $castLines[] = "        '{$colName}' => 'float'";
            } elseif (str_contains($colType, 'datetime') || str_contains($colType, 'date')) {
                $castLines[] = "        '{$colName}' => 'datetime'";
            }
        }
        $castStr = implode(",\n", $castLines);

        // 关联方法
        $relationMethods = [];
        $relations = is_array($options) ? ($options['relations'] ?? []) : [];
        foreach ($relations as $rel) {
            $type = $rel['type'] ?? '';
            $model = $rel['model'] ?? '';
            $foreignKey = $rel['foreignKey'] ?? '';
            $localKey = $rel['localKey'] ?? '';
            $name = $rel['name'] ?? lcfirst($model);

            if (empty($type) || empty($model)) continue;

            $methodName = $name;
            
            // 补充模型关联的命名空间
            $modelClass = $model;
            if (!str_contains($modelClass, '\\')) {
                $modelClass = "{$modelNS}\\{$model}";
            }

            $returnType = '';
            $methodBody = '';

            switch ($type) {
                case 'hasOne':
                    $returnType = 'HasOne';
                    $methodBody = "return \$this->hasOne(\\{$modelClass}::class, '{$foreignKey}', '{$localKey}');";
                    break;
                case 'hasMany':
                    $returnType = 'HasMany';
                    $methodBody = "return \$this->hasMany(\\{$modelClass}::class, '{$foreignKey}', '{$localKey}');";
                    break;
                case 'belongsTo':
                    $returnType = 'BelongsTo';
                    $methodBody = "return \$this->belongsTo(\\{$modelClass}::class, '{$foreignKey}', '{$localKey}');";
                    break;
                case 'belongsToMany':
                    $table = $rel['table'] ?? '';
                    $returnType = 'BelongsToMany';
                    $methodBody = "return \$this->belongsToMany(\\{$modelClass}::class, '{$table}', '{$foreignKey}', '{$localKey}');";
                    break;
            }

            if ($methodBody) {
                $relationMethods[] = "    public function {$methodName}(): {$returnType}\n    {\n        {$methodBody}\n    }";
                $imports["Illuminate\\Database\\Eloquent\\Relations\\{$returnType}"] = true;
            }
        }

        // 树表结构
        if ($tplCategory === 'tree') {
            $treeId = $options['tree_id'] ?? 'id';
            $treeParentId = $options['tree_parent_id'] ?? 'parent_id';
            $treeName = $options['tree_name'] ?? 'name';
            
            $imports["Illuminate\\Database\\Eloquent\\Relations\\BelongsTo"] = true;
            $imports["Illuminate\\Database\\Eloquent\\Relations\\HasMany"] = true;
            
            $relationMethods[] = <<<PHP
    /*
    BelongsTo 父级分类
    */
    public function parent(): BelongsTo
    {
        return \$this->belongsTo({$className}::class, '{$treeParentId}', '{$treeId}');
    }

    /*
    hasMany 子分类
    */ 
    public function children(): HasMany
    {
        return \$this->hasMany({$className}::class, '{$treeParentId}', '{$treeId}');
    }
PHP;

            $orderField = $hasSort ? 'sort' : $treeId;
            $whereNullStr = $softDelete ? "\n            ->whereNull('delete_time')" : "";
            $relationMethods[] = <<<PHP
    /**
     * 构建分类树（带 label 字段供前端 el-tree 使用）
     */
    public static function buildTree(int \$parentId = 0): array
    {
        \$items = self::where('{$treeParentId}', \$parentId){$whereNullStr}
            ->orderBy('{$orderField}')
            ->get();

        \$tree = [];
        foreach (\$items as \$item) {
            \$node = \$item->toArray();
            \$node['label'] = \$item->{$treeName};
            \$node['value'] = \$item->{$treeId};
            \$children = self::buildTree(\$item->{$treeId});
            if (!empty(\$children)) {
                \$node['children'] = \$children;
            }
            \$tree[] = \$node;
        }

        return \$tree;
    }
PHP;
        }

        $relationsStr = '';
        if (!empty($relationMethods)) {
            $relationsStr = "\n" . implode("\n\n", $relationMethods) . "\n";
        }

        $useLines = [];
        foreach (array_keys($imports) as $import) {
            $useLines[] = "use {$import};";
        }
        $useStr = "\n" . implode("\n", $useLines) . "\n";

        return <<<PHP
<?php

declare(strict_types=1);


namespace {$modelNS};
{$useStr}
class {$className} extends BaseLaORMModel
{{$traitStr}
    protected \$table = '{$tableName}';

    public \$incrementing = true;

    /**
     */
    protected \$keyType = 'int';

    protected \$dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';{$deletedAt}
    protected \$fillable = [
{$fillableStr}
    ];

    protected \$casts = [
{$castStr}
    ];{$relationsStr}
}
PHP;
    }

    /**
     * 渲染 Vue index.vue 代码
     *
     * @param array<string, mixed> $ctx
     */
    protected function renderVueIndex(array $ctx): string
    {
        [
            'className'    => $className,
            'businessName' => $businessName,
            'tableComment' => $tableComment,
            'columns'      => $columns,
        ] = $ctx;

        $routeBase  = '/api/' . strtolower($businessName ?: $this->classNameToRouteName($className));
        $date       = date('Y-m-d');

        // 构建列表列
        $colLines = [];
        foreach ($columns as $col) {
            if ((int)($col['is_list'] ?? 1) !== ToolGenerateColumn::FLAG_YES) continue;
            $colName    = $col['column_name'] ?? '';
            $colComment = $col['column_comment'] ?? $colName;
            $colLines[] = "      { prop: '{$colName}', label: '{$colComment}' }";
        }
        $colStr = implode(",\n", $colLines);

        // 构建搜索表单初始值
        $searchInit = [];
        $hasSearch = false;
        foreach ($columns as $col) {
            if ((int)($col['is_query'] ?? 0) === ToolGenerateColumn::FLAG_YES) {
                $hasSearch = true;
                $colName = $col['column_name'] ?? '';
                $queryType = $col['query_type'] ?? 'eq';
                if ($queryType === 'between') {
                    $searchInit[] = "  {$colName}: []";
                } else {
                    $searchInit[] = "  {$colName}: undefined";
                }
            }
        }
        $searchInitStr = implode(",\n", $searchInit);

        $searchComponentHtml = $hasSearch ? "\n    <TableSearch v-model=\"searchForm\" @search=\"handleSearch\" @reset=\"handleReset\" />" : "";
        $searchImportHtml = $hasSearch ? "\nimport TableSearch from './modules/table-search.vue'" : "";
        $searchLogicHtml = $hasSearch ? <<<LOGIC

// 搜索表单
const searchForm = ref({
{$searchInitStr}
})

const handleSearch = () => {
  page.value = 1
  loadData()
}

const handleReset = () => {
  page.value = 1
  loadData()
}
LOGIC : <<<LOGIC
// 搜索表单
const searchForm = ref({})
LOGIC;

        return <<<VUE
<template>
  <div class="sa-container">{$searchComponentHtml}
    <ArtTableHeader title="{$tableComment}管理">
      <template #right>
        <el-button type="primary" @click="handleAdd">新增</el-button>
      </template>
    </ArtTableHeader>
    <ArtTable
      ref="tableRef"
      :columns="columns"
      :data="tableData"
      :loading="loading"
      :total="total"
      @page-change="handlePageChange"
    >
      <template #action="{ row }">
        <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
        <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
      </template>
    </ArtTable>
    <Form ref="formRef" @success="loadData" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/http'
import Form from './modules/form.vue'{$searchImportHtml}

// ==== 表格列配置 ====
const columns = [
{$colStr},
  { prop: 'create_time', label: '创建时间', width: 180 },
  { prop: 'action', label: '操作', fixed: 'right', width: 160, slot: 'action' }
]

const tableRef = ref()
const formRef  = ref()
const loading  = ref(false)
const tableData= ref([])
const total    = ref(0)
const page     = ref(1)
const limit    = ref(15)
{$searchLogicHtml}

const loadData = async () => {
  loading.value = true
  try {
    const params = { page: page.value, limit: limit.value }
    if (searchForm.value) Object.assign(params, searchForm.value)
    
    const res = await request.get({ url: '{$routeBase}/index', params })
    tableData.value = res?.data?.items ?? []
    total.value     = res?.data?.total ?? 0
  } finally {
    loading.value = false
  }
}

const handleAdd = () => formRef.value?.open()
const handleEdit= (row: any) => formRef.value?.open(row)

const handleDelete = (row: any) => {
  ElMessageBox.confirm('确认删除该记录？', '提示', { type: 'warning' }).then(async () => {
    await request.del({ url: '{$routeBase}/destroy', data: { ids: [row.id] } })
    ElMessage.success('删除成功')
    loadData()
  })
}

const handlePageChange = ({ page: p, limit: l }: any) => {
  page.value  = p
  limit.value = l
  loadData()
}

onMounted(() => loadData())
</script>
VUE;
    }

    /**
     * 渲染 Vue form.vue 代码
     *
     * @param array<string, mixed> $ctx
     */
    protected function renderVueForm(array $ctx): string
    {
        [
            'className'    => $className,
            'businessName' => $businessName,
            'tableComment' => $tableComment,
            'columns'      => $columns,
            'componentType'=> $componentType,
            'formWidth'    => $formWidth,
            'isFull'       => $isFull,
        ] = $ctx;

        $routeBase = '/api/' . strtolower($businessName ?: $this->classNameToRouteName($className));
        $date      = date('Y-m-d');
        
        $containerTag = $componentType == 2 ? 'el-dialog' : 'el-drawer';
        $sizeAttr = '';
        if ($componentType == 2) {
            $sizeAttr = $isFull ? ' fullscreen' : " width=\"{$formWidth}\"";
        } else {
            $sizeAttr = $isFull ? ' size="100%"' : " size=\"{$formWidth}\"";
        }

        // 构建表单项
        $formItems = [];
        $formInit  = [];
        $imports   = [];
        foreach ($columns as $col) {
            $colName    = $col['column_name'] ?? '';
            $colComment = $col['column_comment'] ?? $colName;
            $viewType   = $col['view_type'] ?? 'text';
            $isEdit     = (int)($col['is_edit'] ?? 1) === ToolGenerateColumn::FLAG_YES;
            $isInsert   = (int)($col['is_insert'] ?? 1) === ToolGenerateColumn::FLAG_YES;
            $isRequired = (int)($col['is_required'] ?? 1) === ToolGenerateColumn::REQUIRED_YES;

            if (!$isEdit && !$isInsert) continue;
            if (in_array($colName, ['id', 'created_by', 'updated_by', 'create_time', 'update_time', 'delete_time'])) continue;

            $formInit[] = "    {$colName}: ''";

            $required    = $isRequired ? ' required' : '';
            $requiredRule= $isRequired ? "\n        { required: true, message: '请输入{$colComment}', trigger: 'blur' }" : '';

            $dictType   = $col['dict_type'] ?? '';
            $dictAttr   = $dictType ? " dict=\"{$dictType}\"" : '';

            // 收集需要的自定义组件导入
            switch ($viewType) {
                case 'saSelect':   $imports['SaSelect'] = "import SaSelect from '@/components/sai/sa-select/index.vue'"; break;
                case 'radio':      $imports['SaRadio'] = "import SaRadio from '@/components/sai/sa-radio/index.vue'"; break;
                case 'checkbox':   $imports['SaCheckbox'] = "import SaCheckbox from '@/components/sai/sa-checkbox/index.vue'"; break;
                case 'userSelect': $imports['SaUser'] = "import SaUser from '@/components/sai/sa-user/index.vue'"; break;
                case 'uploadImage':$imports['SaImageUpload'] = "import SaImageUpload from '@/components/sai/sa-image-upload/index.vue'"; break;
                case 'imagePicker':$imports['SaImagePicker'] = "import SaImagePicker from '@/components/sai/sa-image-picker/index.vue'"; break;
                case 'uploadFile': $imports['SaFileUpload'] = "import SaFileUpload from '@/components/sai/sa-file-upload/index.vue'"; break;
                case 'chunkUpload':$imports['SaChunkUpload'] = "import SaChunkUpload from '@/components/sai/sa-chunk-upload/index.vue'"; break;
                case 'editor':     $imports['SaEditor'] = "import SaEditor from '@/components/sai/sa-editor/index.vue'"; break;
            }

            // 解析 options 属性并合并默认值
            $colOptions = [];
            if (!empty($col['options'])) {
                $decoded = is_array($col['options']) ? $col['options'] : json_decode($col['options'], true);
                if (is_array($decoded)) {
                    $colOptions = $decoded;
                }
            }
            $defaultOptions = [];
            if (in_array($viewType, ['uploadImage', 'imagePicker', 'uploadFile', 'chunkUpload'])) {
                $defaultOptions['limit'] = 1;
            }
            $mergedOptions = array_merge($defaultOptions, $colOptions);

            $extraAttr = '';
            foreach ($mergedOptions as $k => $v) {
                if (in_array($k, ['relations', 'tree_id', 'tree_name', 'tree_parent_id'])) continue;
                if (is_bool($v)) {
                    $extraAttr .= " :{$k}=\"" . ($v ? 'true' : 'false') . "\"";
                } elseif (is_numeric($v)) {
                    $extraAttr .= " :{$k}=\"{$v}\"";
                } elseif (is_string($v)) {
                    $extraAttr .= " {$k}=\"{$v}\"";
                }
            }

            $control = match($viewType) {
                'input'      => "<el-input v-model=\"form.{$colName}\" clearable placeholder=\"请输入{$colComment}\"{$extraAttr} />",
                'password'   => "<el-input v-model=\"form.{$colName}\" type=\"password\" clearable show-password placeholder=\"请输入{$colComment}\"{$extraAttr} />",
                'textarea'   => "<el-input v-model=\"form.{$colName}\" type=\"textarea\" :rows=\"3\" placeholder=\"请输入{$colComment}\"{$extraAttr} />",
                'inputNumber'=> "<el-input-number v-model=\"form.{$colName}\" :min=\"0\" style=\"width: 100%\"{$extraAttr} />",
                'inputTag'   => "<el-select v-model=\"form.{$colName}\" multiple filterable allow-create default-first-option style=\"width: 100%\" placeholder=\"请添加{$colComment}\"{$extraAttr}></el-select>",
                'switch'     => "<el-switch v-model=\"form.{$colName}\" :active-value=\"1\" :inactive-value=\"2\"{$extraAttr} />",
                'slider'     => "<el-slider v-model=\"form.{$colName}\" :min=\"0\" :max=\"100\"{$extraAttr} />",
                'select'     => "<el-select v-model=\"form.{$colName}\" clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\"{$extraAttr}>\n            <!-- 静态选项可在此配置 -->\n          </el-select>",
                'saSelect'   => "<sa-select v-model=\"form.{$colName}\"{$dictAttr} clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\"{$extraAttr} />",
                'treeSelect' => "<el-tree-select v-model=\"form.{$colName}\" :data=\"[]\" check-strictly clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\"{$extraAttr} />",
                'radio'      => "<sa-radio v-model=\"form.{$colName}\"{$dictAttr}{$extraAttr} />",
                'checkbox'   => "<sa-checkbox v-model=\"form.{$colName}\"{$dictAttr}{$extraAttr} />",
                'date'       => "<el-date-picker v-model=\"form.{$colName}\" type=\"datetime\" format=\"YYYY-MM-DD HH:mm:ss\" value-format=\"YYYY-MM-DD HH:mm:ss\" clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\"{$extraAttr} />",
                'time'       => "<el-time-picker v-model=\"form.{$colName}\" format=\"HH:mm:ss\" value-format=\"HH:mm:ss\" clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\"{$extraAttr} />",
                'rate'       => "<el-rate v-model=\"form.{$colName}\"{$extraAttr} />",
                'cascader'   => "<el-cascader v-model=\"form.{$colName}\" :options=\"[]\" clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\"{$extraAttr} />",
                'userSelect' => "<sa-user v-model=\"form.{$colName}\"{$extraAttr} />",
                'uploadImage'=> "<sa-image-upload v-model=\"form.{$colName}\"{$extraAttr} />",
                'imagePicker'=> "<sa-image-picker v-model=\"form.{$colName}\"{$extraAttr} />",
                'uploadFile' => "<sa-file-upload v-model=\"form.{$colName}\"{$extraAttr} />",
                'chunkUpload'=> "<sa-chunk-upload v-model=\"form.{$colName}\"{$extraAttr} />",
                'editor'     => "<sa-editor v-model=\"form.{$colName}\"{$extraAttr} />",
                default      => "<el-input v-model=\"form.{$colName}\" clearable placeholder=\"请输入{$colComment}\"{$extraAttr} />",
            };

            $formItems[] = <<<ITEM
      <el-form-item label="{$colComment}" prop="{$colName}"{$required}>
        {$control}
      </el-form-item>
ITEM;
        }

        $formItemsStr = implode("\n", $formItems);
        $formInitStr  = implode(",\n", $formInit);
        $importsStr   = implode("\n", array_values($imports));

        return <<<VUE
<template>
  <{$containerTag} v-model="visible" :title="title"{$sizeAttr} destroy-on-close>
    <el-form ref="formRef" :model="form" :rules="rules" label-width="100px" class="mt-4 pr-4">
{$formItemsStr}
    </el-form>
    <template #footer>
      <el-button @click="visible = false">取消</el-button>
      <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
    </template>
  </{$containerTag}>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import request from '@/utils/http'
{$importsStr}

const emit = defineEmits(['success'])

const visible       = ref(false)
const submitLoading = ref(false)
const isEdit        = ref(false)
const title         = ref('新增{$tableComment}')

const form = reactive<Record<string, any>>({
  id: '',
{$formInitStr}
})

const rules = reactive<Record<string, any>>({})

const formRef = ref()

const open = (row?: any) => {
  isEdit.value = !!row?.id
  title.value  = isEdit.value ? '编辑{$tableComment}' : '新增{$tableComment}'
  if (row) Object.assign(form, row)
  else Object.keys(form).forEach(k => (form[k] = ''))
  visible.value = true
}

const handleSubmit = async () => {
  await formRef.value?.validate()
  submitLoading.value = true
  try {
    if (isEdit.value) {
      await request.put({ url: '{$routeBase}/update/' + form.id, data: form })
    } else {
      await request.post({ url: '{$routeBase}/create', data: form })
    }
    ElMessage.success(isEdit.value ? '更新成功' : '创建成功')
    visible.value = false
    emit('success')
  } finally {
    submitLoading.value = false
  }
}

defineExpose({ open })
</script>
VUE;
    }

    /**
     * 渲染 Vue table-search.vue 代码
     *
     * @param array<string, mixed> $ctx
     */
    protected function renderVueSearch(array $ctx): string
    {
        [
            'columns' => $columns,
        ] = $ctx;

        $formItems = [];
        $imports = [];

        foreach ($columns as $col) {
            if ((int)($col['is_query'] ?? 0) !== ToolGenerateColumn::FLAG_YES) continue;

            $colName = $col['column_name'] ?? '';
            $colComment = $col['column_comment'] ?? $colName;
            $viewType = $col['view_type'] ?? 'text';
            $dictType = $col['dict_type'] ?? '';
            $dictAttr = $dictType ? " dict=\"{$dictType}\"" : '';
            $queryType = $col['query_type'] ?? 'eq';

            switch ($viewType) {
                case 'saSelect':   $imports['SaSelect'] = "import SaSelect from '@/components/sai/sa-select/index.vue'"; break;
                case 'radio':      $imports['SaRadio'] = "import SaRadio from '@/components/sai/sa-radio/index.vue'"; break;
                case 'checkbox':   $imports['SaCheckbox'] = "import SaCheckbox from '@/components/sai/sa-checkbox/index.vue'"; break;
                case 'userSelect': $imports['SaUser'] = "import SaUser from '@/components/sai/sa-user/index.vue'"; break;
            }

            $control = match($viewType) {
                'select', 'saSelect' => "<sa-select v-model=\"formData.{$colName}\"{$dictAttr} clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\" />",
                'radio' => "<sa-radio v-model=\"formData.{$colName}\"{$dictAttr} />",
                'checkbox' => "<sa-checkbox v-model=\"formData.{$colName}\"{$dictAttr} />",
                'date' => $queryType === 'between' 
                    ? "<el-date-picker v-model=\"formData.{$colName}\" type=\"datetimerange\" range-separator=\"至\" start-placeholder=\"开始时间\" end-placeholder=\"结束时间\" format=\"YYYY-MM-DD HH:mm:ss\" value-format=\"YYYY-MM-DD HH:mm:ss\" clearable style=\"width: 100%\" />"
                    : "<el-date-picker v-model=\"formData.{$colName}\" type=\"datetime\" format=\"YYYY-MM-DD HH:mm:ss\" value-format=\"YYYY-MM-DD HH:mm:ss\" clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\" />",
                'time' => $queryType === 'between'
                    ? "<el-time-picker v-model=\"formData.{$colName}\" is-range range-separator=\"至\" start-placeholder=\"开始时间\" end-placeholder=\"结束时间\" format=\"HH:mm:ss\" value-format=\"HH:mm:ss\" clearable style=\"width: 100%\" />"
                    : "<el-time-picker v-model=\"formData.{$colName}\" format=\"HH:mm:ss\" value-format=\"HH:mm:ss\" clearable placeholder=\"请选择{$colComment}\" style=\"width: 100%\" />",
                default => "<el-input v-model=\"formData.{$colName}\" placeholder=\"请输入{$colComment}\" clearable />",
            };

            $formItems[] = <<<ITEM
    <el-col v-bind="setSpan(6)">
      <el-form-item label="{$colComment}" prop="{$colName}">
        {$control}
      </el-form-item>
    </el-col>
ITEM;
        }

        $formItemsStr = implode("\n", $formItems);
        $importsStr   = implode("\n", array_values($imports));

        return <<<VUE
<template>
  <sa-search-bar
    ref="searchBarRef"
    v-model="formData"
    :label-width="'100px'"
    :showExpand="false"
    @reset="handleReset"
    @search="handleSearch"
    @expand="handleExpand"
  >
{$formItemsStr}
  </sa-search-bar>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
{$importsStr}

interface Props {
  modelValue: Record<string, any>
}
interface Emits {
  (e: 'update:modelValue', value: Record<string, any>): void
  (e: 'search', params: Record<string, any>): void
  (e: 'reset'): void
}
const props = defineProps<Props>()
const emit = defineEmits<Emits>()
const isExpanded = ref<boolean>(false)

// 表单数据双向绑定
const searchBarRef = ref()
const formData = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

// 重置
function handleReset() {
  searchBarRef.value?.ref.resetFields()
  emit('reset')
}

// 搜索
function handleSearch() {
  emit('search', formData.value)
}

// 展开/收起
  /**
   */
function handleExpand(expanded: boolean) {
  isExpanded.value = expanded
}

// 栅格占据的列数
const setSpan = (span: number) => {
  return {
    span: span,
    xs: 24,
    sm: span >= 12 ? span : 12,
    md: span >= 8 ? span : 8,
    lg: span,
    xl: span
  }
}
</script>
VUE;
    }

    // ==================== 工具方法 ====================

    /**
     * 更新字段配置（全量替换）
     *
     * @param array<int, array<string, mixed>> $columns
     */
    protected function updateColumns(int $tableId, array $columns, int $operatorId, string $now): void
    {
        // 先删除所有旧字段
        $this->columnDao->deleteByTableId($tableId);

        // 批量插入新字段
        $rows = [];
        foreach ($columns as $sort => $col) {
            $row = [
                'table_id'       => $tableId,
                'column_name'    => $col['column_name']    ?? '',
                'column_comment' => $col['column_comment'] ?? '',
                'column_type'    => $col['column_type']    ?? '',
                'default_value'  => $col['default_value']  ?? null,
                'is_pk'          => $this->boolToFlag($col['is_pk']       ?? false, true),
                'is_required'    => $this->boolToFlag($col['is_required'] ?? false),
                'is_insert'      => $this->boolToFlag($col['is_insert']   ?? true),
                'is_edit'        => $this->boolToFlag($col['is_edit']     ?? true),
                'is_list'        => $this->boolToFlag($col['is_list']     ?? true),
                'is_query'       => $this->boolToFlag($col['is_query']    ?? false),
                'is_sort'        => $this->boolToFlag($col['is_sort']     ?? false),
                'query_type'     => $col['query_type']    ?? 'eq',
                'view_type'      => $col['view_type']     ?? 'text',
                'dict_type'      => $col['dict_type']     ?? null,
                'allow_roles'    => $col['allow_roles']   ?? null,
                'options'        => isset($col['options']) ? (is_array($col['options']) ? json_encode($col['options'], JSON_UNESCAPED_UNICODE) : $col['options']) : null,
                'sort'           => (int)($col['sort']    ?? $sort),
                'remark'         => $col['remark']        ?? null,
                'created_by'     => $operatorId,
                'updated_by'     => $operatorId,
                'create_time'    => $now,
                'update_time'    => $now,
            ];
            $rows[] = $row;
        }

        if (!empty($rows)) {
            $this->columnDao->batchInsert($rows);
        }
    }

    /**
     * boolean / int 统一转换为 smallint 标志（1=否, 2=是）
     */
    protected function boolToFlag(mixed $value, bool $reverseDefault = false): int
    {
        if (is_bool($value)) {
            return $value ? ToolGenerateColumn::FLAG_YES : ToolGenerateColumn::FLAG_NO;
        }
        if (is_int($value) || is_numeric($value)) {
            $int = (int)$value;
            // 已是 1/2 标志
            if ($int === 1 || $int === 2) return $int;
            // 0/1 bool-like
            return $int ? ToolGenerateColumn::FLAG_YES : ToolGenerateColumn::FLAG_NO;
        }
        return $reverseDefault ? ToolGenerateColumn::FLAG_NO : ToolGenerateColumn::FLAG_NO;
    }

    /**
     * 格式化表记录（时间字段处理）
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    protected function formatTableRow(array $row): array
    {
        foreach (['create_time', 'update_time', 'delete_time'] as $field) {
            if (isset($row[$field]) && $row[$field] instanceof \DateTime) {
                $row[$field] = $row[$field]->format('Y-m-d H:i:s');
            }
        }
        
        // 修复 generate_menus 被多次转义 JSON 编码的问题
        if (!empty($row['generate_menus'])) {
            $menusStr = $row['generate_menus'];
            while (is_string($menusStr) && str_starts_with(trim($menusStr), '[') && str_ends_with(trim($menusStr), ']')) {
                $decoded = json_decode($menusStr, true);
                if (is_array($decoded)) {
                    // 如果解开后是一个数组，且数组里只有 1 个元素，且这个元素还是字符串形式的 JSON 数组
                    // 比如 $decoded 是 ['["index","save"]']，就把它提取出来继续解码
                    if (count($decoded) === 1 && is_string($decoded[0]) && str_starts_with(trim($decoded[0]), '[') && str_ends_with(trim($decoded[0]), ']')) {
                        $menusStr = $decoded[0];
                    } else {
                        // 正常的数组，转为逗号分隔的字符串
                        $menusStr = implode(',', $decoded);
                    }
                } else {
                    break;
                }
            }
            // 确保最后去掉可能的引号和外层符号，如果被破坏严重
            $menusStr = str_replace(['"', '[', ']', '\\'], '', $menusStr);
            $row['generate_menus'] = $menusStr;
        }

        return $row;
    }

    /**
     * 格式化字段记录（布尔值转换）
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    protected function formatColumnRow(array $row): array
    {
        foreach (['create_time', 'update_time', 'delete_time'] as $field) {
            if (isset($row[$field]) && $row[$field] instanceof \DateTime) {
                $row[$field] = $row[$field]->format('Y-m-d H:i:s');
            }
        }
        // options 解码
        if (isset($row['options']) && is_string($row['options'])) {
            $decoded = json_decode($row['options'], true);
            $row['options'] = is_array($decoded) ? $decoded : null;
        }
        return $row;
    }

    /**
     * 表名转类名（去前缀，驼峰）
     * sa_system_user -> SysUser, sa_tool_generate_tables -> ToolGenerateTables
     */
    protected function tableNameToClassName(string $tableName): string
    {
        // 去除 sa_ 前缀
        $name = preg_replace('/^sa_/', '', $tableName);
        // 将 system_ 缩写为 Sys，tool_ 保留
        $segments = explode('_', $name);
        return implode('', array_map('ucfirst', $segments));
    }

    /**
     * 表名转业务名（去前缀，小驼峰）
     */
    protected function tableNameToBusinessName(string $tableName): string
    {
        $name     = preg_replace('/^sa_/', '', $tableName);
        $segments = explode('_', $name);
        $first    = array_shift($segments);
        return $first . implode('', array_map('ucfirst', $segments));
    }

    /**
     * 类名转路由段（驼峰转连字符）
     * UserCenter -> user-center
     */
    protected function classNameToRouteName(string $className): string
    {
        return strtolower(preg_replace('/([A-Z])/', '-$1', lcfirst($className)));
    }

    // ==================== 菜单 SQL 语句模板 ====================

    /**
     * 渲染菜单 SQL 语句
     *
     * @param array<string, mixed> $ctx
     */
    protected function renderMenuSql(array $ctx): string
    {
        [
            'className'     => $className,
            'businessName'  => $businessName,
            'tableComment'  => $tableComment,
            'belongMenuId'  => $belongMenuId,
            'menuName'      => $menuName,
            'generateMenus' => $generateMenus,
        ] = $ctx;

        $routeName = strtolower($businessName ?: $this->classNameToRouteName($className));
        $now       = date('Y-m-d H:i:s');
        
        $sql  = "-- ----------------------------\n";
        $sql .= "-- 菜单 SQL 语句 for {$menuName}\n";
        $sql .= "-- ----------------------------\n\n";

        // 主菜单（type = 2 菜单）
        $parentPath = '/' . $routeName;
        $component  = "{$routeName}/index"; 
        $slug       = "{$routeName}:index";
        
        $sql .= "INSERT INTO `sa_system_menu` (`parent_id`, `name`, `type`, `path`, `component`, `slug`, `icon`, `status`, `create_time`, `update_time`) VALUES\n";
        $sql .= "({$belongMenuId}, '{$menuName}', 2, '{$parentPath}', '{$component}', '{$slug}', 'ri:table-line', 1, '{$now}', '{$now}');\n\n";
        
        // 记录刚才插入的父级菜单ID
        $sql .= "-- 将父级菜单ID存储到变量中以便子菜单使用\n";
        $sql .= "SET @parentId = LAST_INSERT_ID();\n\n";

        // 子菜单（按钮 type = 3 按钮）
        $buttons = [];
        
        // 构建按钮权限
        $btnMap = [
            'index'    => '列表',
            'save'     => '新增',
            'update'   => '修改',
            'read'     => '详情',
            'destroy'  => '删除',
            'recycle'  => '回收站',
            'recovery' => '恢复',
        ];

        foreach ($generateMenus as $menuKey) {
            $menuKey = trim($menuKey);
            if (empty($menuKey) || $menuKey === 'index') continue;

            $btnName = $btnMap[$menuKey] ?? ucfirst($menuKey);
            $btnSlug = "{$routeName}:{$menuKey}";
            $buttons[] = "(@parentId, '{$btnName}', 3, '', '', '{$btnSlug}', '', 1, '{$now}', '{$now}')";
        }
        
        if (!empty($buttons)) {
            $sql .= "INSERT INTO `sa_system_menu` (`parent_id`, `name`, `type`, `path`, `component`, `slug`, `icon`, `status`, `create_time`, `update_time`) VALUES\n";
            $sql .= implode(",\n", $buttons) . ";\n";
        }

        return $sql;
    }
}