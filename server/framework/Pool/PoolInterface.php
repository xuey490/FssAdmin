<?php

declare(strict_types=1);

/**
 * @Filename: PoolInterface.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Pool;

/**
 * 连接池统一契约
 *
 * 所有连接池（Redis、MySQL 等）均需实现此接口，
 * 确保 borrow/release/healthCheck 语义一致，便于上层统一管理。
 */
interface PoolInterface
{
    /**
     * 从池中借出一个连接
     *
     * 若池中有空闲连接则立即返回；
     * 若池已满则阻塞等待（超时后抛出 RuntimeException）；
     * 若池未达最大值则新建连接并返回。
     *
     * @return object 连接对象
     * @throws \RuntimeException 连接池耗尽或等待超时
     */
    public function borrow(): object;

    /**
     * 归还连接到池中
     *
     * 归还前会执行健康检查，不健康的连接直接丢弃并补充新连接。
     *
     * @param object $connection 需要归还的连接对象
     * @return void
     */
    public function release(object $connection): void;

    /**
     * 检查连接是否健康
     *
     * @param object $connection 需要检查的连接对象
     * @return bool 健康返回 true，否则返回 false
     */
    public function healthCheck(object $connection): bool;

    /**
     * 获取当前池的统计信息
     *
     * @return array{idle: int, active: int, total: int, max: int}
     */
    public function stats(): array;

    /**
     * 关闭连接池，释放所有连接
     *
     * @return void
     */
    public function close(): void;
}
