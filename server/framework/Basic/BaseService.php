<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: BaseService.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Framework\ORM\Trait\ServicesTrait;
use Illuminate\Support\Facades\DB as LaravelDb;
use think\facade\DB as ThinkDb;
use Framework\DI\Injectable;
use Closure;
use Throwable;

/**
 * BaseService - 泛型服务基类
 *
 * 提供以下核心功能：
 * - DAO 注入与代理
 * - 框架无关的事务处理
 * - 分页参数规范化
 *
 * 子类可指定具体 DAO 类型：
 * @template T of BaseDao
 * @method getModel()
 *
 * @package Framework\Basic
 */
abstract class BaseService
{
    use ServicesTrait;
    use Injectable;

    /**
     * 模型注入：使用泛型类型
     * @var ?T
     */
    protected ?BaseDao $dao = null;

    /**
     * 当前使用的事务连接
     * 用于支持嵌套事务和连接复用
     * @var mixed|null
     */
    protected mixed $transactionConnection = null;

    /**
     * 事务嵌套级别
     * @var int
     */
    protected int $transactionLevel = 0;

    /**
     * 构造函数
     *
     * 初始化依赖注入和服务。
     */
    public function __construct()
    {
        $db = app('db');
        $this->inject();
        $this->initialize();
    }

    /**
     * 子类初始化钩子
     *
     * 子类可根据需要覆盖此方法进行初始化操作。
     *
     * @return void
     */
    protected function initialize(): void
    {
    }

    // =========================================================================
    //  事务处理
    // =========================================================================

    /**
     * 执行事务
     *
     * 支持多种 ORM 框架的事务处理，自动检测当前使用的框架。
     * 支持事务嵌套，使用 savepoint 机制。
     *
     * @param Closure $closure 事务内执行的闭包
     * @param bool $isTran 是否启用事务（默认 true）
     * @param string|null $framework 指定框架：'thinkORM' 或 'laravelORM'，null 则自动检测
     * @return mixed 闭包返回值
     * @throws \Exception 事务失败时抛出异常
     *
     * @example
     * // 基本用法
     * $result = $this->transaction(function() {
     *     $this->create($data1);
     *     $this->create($data2);
     *     return true;
     * });
     *
     * // 不使用事务
     * $result = $this->transaction($closure, false);
     *
     * // 指定框架
     * $result = $this->transaction($closure, true, 'laravelORM');
     */
    public function transaction(Closure $closure, bool $isTran = true, ?string $framework = null): mixed
    {
        // 不启用事务，直接执行闭包
        if (!$isTran) {
            return $closure();
        }

        // 确定使用的框架
        $framework = $framework ?? config('database.engine', 'thinkORM');

        return match ($framework) {
            'thinkORM'   => $this->executeThinkOrmTransaction($closure),
            'laravelORM' => $this->executeLaravelOrmTransaction($closure),
            default      => throw new \InvalidArgumentException("Unsupported framework: {$framework}"),
        };
    }

    /**
     * 执行 ThinkORM 事务
     *
     * 使用 ThinkPHP 的闭包事务方式，自动处理提交和回滚。
     *
     * @param Closure $closure 事务内执行的闭包
     * @return mixed 闭包返回值
     * @throws Throwable
     */
    protected function executeThinkOrmTransaction(Closure $closure): mixed
    {
        return ThinkDb::transaction(function () use ($closure) {
            return $closure();
        });
    }

    /**
     * 执行 Laravel ORM 事务
     *
     * 使用 Laravel 的闭包事务方式，自动处理提交和回滚。
     *
     * @param Closure $closure 事务内执行的闭包
     * @return mixed 闭包返回值
     * @throws Throwable
     */
    protected function executeLaravelOrmTransaction(Closure $closure): mixed
    {
        return \Illuminate\Database\Capsule\Manager::connection()->transaction(function () use ($closure) {
            return $closure();
        });
    }

    /**
     * 手动开始事务
     *
     * 用于需要手动控制事务边界的场景。
     * 支持嵌套事务。
     *
     * @param string|null $framework 指定框架
     * @return void
     */
    public function beginTransaction(?string $framework = null): void
    {
        $framework = $framework ?? config('database.engine', 'thinkORM');

        if ($this->transactionLevel === 0) {
            match ($framework) {
                'thinkORM'   => ThinkDb::startTrans(),
                'laravelORM' => \Illuminate\Database\Capsule\Manager::connection()->beginTransaction(),
            };
        }

        $this->transactionLevel++;
    }

    /**
     * 提交事务
     *
     * 与 beginTransaction 配对使用。
     *
     * @param string|null $framework 指定框架
     * @return void
     */
    public function commit(?string $framework = null): void
    {
        $framework = $framework ?? config('database.engine', 'thinkORM');

        $this->transactionLevel = max(0, $this->transactionLevel - 1);

        if ($this->transactionLevel === 0) {
            match ($framework) {
                'thinkORM'   => ThinkDb::commit(),
                'laravelORM' => \Illuminate\Database\Capsule\Manager::connection()->commit(),
            };
        }
    }

    /**
     * 回滚事务
     *
     * 与 beginTransaction 配对使用。
     *
     * @param string|null $framework 指定框架
     * @return void
     */
    public function rollback(?string $framework = null): void
    {
        $framework = $framework ?? config('database.engine', 'thinkORM');

        $this->transactionLevel = max(0, $this->transactionLevel - 1);

        if ($this->transactionLevel === 0) {
            match ($framework) {
                'thinkORM'   => ThinkDb::rollback(),
                'laravelORM' => \Illuminate\Database\Capsule\Manager::connection()->rollBack(),
            };
        }
    }

    /**
     * 使用 try-catch-finally 模式执行事务
     *
     * 更明确的事务控制方式，适合复杂业务场景。
     *
     * @param Closure $closure 事务内执行的闭包
     * @param string|null $framework 指定框架
     * @return mixed 闭包返回值
     * @throws Throwable
     */
    public function transactionWithTry(Closure $closure, ?string $framework = null): mixed
    {
        $framework = $framework ?? config('database.engine', 'thinkORM');

        $this->beginTransaction($framework);

        try {
            $result = $closure();
            $this->commit($framework);
            return $result;
        } catch (Throwable $e) {
            $this->rollback($framework);
            throw $e;
        }
    }

    // =========================================================================
    //  DAO 管理
    // =========================================================================

    /**
     * 设置 DAO 实例
     *
     * @param BaseDao $dao DAO 实例
     * @return void
     */
    public function setDao(BaseDao $dao): void
    {
        $this->dao = $dao;
    }

    /**
     * 获取 DAO 实例
     *
     * @return BaseDao|null DAO 实例
     */
    public function getDao(): ?BaseDao
    {
        return $this->dao;
    }

    // =========================================================================
    //  方法代理
    // =========================================================================

    /**
     * 代理 DAO 调用
     *
     * 当调用不存在的方法时，自动转发给 DAO 处理。
     *
     * @param string $name 方法名
     * @param array $arguments 方法参数
     * @return mixed 方法返回值
     * @throws \RuntimeException DAO 未初始化时抛出
     * @throws \BadMethodCallException 方法不存在时抛出
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!$this->dao) {
            throw new \RuntimeException("BaseService: DAO not initialized in service.");
        }

        try {
            // 支持返回引用调用语法，也支持 PHP 8 call
            return $this->dao->{$name}(...$arguments);
        } catch (\BadMethodCallException $e) {
            // DAO 及其适配器都不支持该方法
            throw new \BadMethodCallException(
                "BaseService: Method {$name} not found in DAO adapter (" . get_class($this->dao) . " / adapter: " . get_class($this->dao->instance) . "): " . $e->getMessage()
            );
        } catch (\Throwable $e) {
            // 其它异常（例如 ORM 内部抛出的），直接向上抛或包装
            throw $e;
        }
    }

    // =========================================================================
    //  分页处理
    // =========================================================================

    /**
     * 规范化分页参数
     *
     * 从数组或请求中获取分页参数，支持多种输入格式。
     *
     * @param array|null $params 分页参数，支持以下格式：
     *                           - null: 使用默认值
     *                           - 关联数组: ['page' => 1, 'limit' => 10] 或 ['p' => 1, 'per_page' => 10]
     *                           - 索引数组: [0 => page, 1 => limit]
     * @param int $defaultLimit 默认每页条数
     * @return array [page, limit, offset]
     *
     * @example
     * // 无参数
     * [$page, $limit, $offset] = $this->PageParams(null);
     *
     * // 关联数组
     * [$page, $limit, $offset] = $this->PageParams(['page' => 2, 'limit' => 20]);
     *
     * // 索引数组
     * [$page, $limit, $offset] = $this->PageParams([2, 20]);
     */
    protected function PageParams(?array $params = null, int $defaultLimit = 10): array
    {
        $page = 1;
        $limit = $defaultLimit;

        if ($params === null) {
            return [$page, $limit, 0];
        }

        // 支持关联数组或索引数组
        if (array_is_list($params)) {
            $page = (int)($params[0] ?? 1);
            $limit = (int)($params[1] ?? $defaultLimit);
        } else {
            $page = (int)($params['page'] ?? $params['p'] ?? 1);
            $limit = (int)($params['limit'] ?? $params['per_page'] ?? $defaultLimit);
        }

        // 边界保护
        $page = max(1, $page);
        $limit = max(1, min($limit, 1000)); // 限制最大 1000 条

        $offset = ($page - 1) * $limit;
        return [$page, $limit, $offset];
    }

    /**
     * 计算分页偏移量
     *
     * @param int $page 页码
     * @param int $limit 每页条数
     * @return int 偏移量
     */
    protected function calculateOffset(int $page, int $limit): int
    {
        return max(0, ($page - 1) * $limit);
    }

    /**
     * 构建分页结果
     *
     * @param array $items 数据列表
     * @param int $total 总记录数
     * @param int $page 当前页码
     * @param int $limit 每页条数
     * @return array 分页结果
     */
    protected function buildPaginateResult(array $items, int $total, int $page, int $limit): array
    {
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => (int)ceil($total / $limit),
        ];
    }
}
