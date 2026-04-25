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

namespace Framework\Utils;

/**
 * 高性能雪花 ID 生成器（优化版）
 * - 使用 hrtime(true) 作为时间源（纳秒级，更稳定）
 * - 修复自旋等待导致的 CPU 100% 问题
 * - 所有位移/常量预计算，提高性能
 */
class Snowflake
{
    public const EPOCH = 1704038400000;

    public const WORKER_ID_BITS = 5;
    public const DATA_CENTER_ID_BITS = 5;
    public const SEQUENCE_BITS = 13;

    public const MAX_WORKER_ID = (1 << self::WORKER_ID_BITS) - 1;
    public const MAX_DATA_CENTER_ID = (1 << self::DATA_CENTER_ID_BITS) - 1;
    public const MAX_SEQUENCE = (1 << self::SEQUENCE_BITS) - 1;

    // 预计算移位（性能提升）
    private const WORKER_SHIFT = self::SEQUENCE_BITS;
    private const DATACENTER_SHIFT = self::SEQUENCE_BITS + self::WORKER_ID_BITS;
    private const TIMESTAMP_SHIFT = self::SEQUENCE_BITS + self::WORKER_ID_BITS + self::DATA_CENTER_ID_BITS;

    private int $workerId;
    private int $dataCenterId;

    private int $sequence = 0;
    private int $lastTimestamp = -1;

    public function __construct(int $workerId, int $dataCenterId)
    {
        if ($workerId < 0 || $workerId > self::MAX_WORKER_ID) {
            throw new \InvalidArgumentException("Worker ID must be between 0 and " . self::MAX_WORKER_ID);
        }
        if ($dataCenterId < 0 || $dataCenterId > self::MAX_DATA_CENTER_ID) {
            throw new \InvalidArgumentException("Data Center ID must be between 0 and " . self::MAX_DATA_CENTER_ID);
        }

        $this->workerId = $workerId;
        $this->dataCenterId = $dataCenterId;
    }

    /**
     * 生成一个全局唯一雪花 ID
     */
    public function nextId(): int
    {
        $timestamp = $this->timeGen();

        // 系统时钟回拨
        if ($timestamp < $this->lastTimestamp) {
            throw new \RuntimeException(
                'Clock moved backwards by ' . ($this->lastTimestamp - $timestamp) . ' ms'
            );
        }

        // 同毫秒内自增
        if ($timestamp === $this->lastTimestamp) {
            $this->sequence = ($this->sequence + 1) & self::MAX_SEQUENCE;

            if ($this->sequence === 0) {
                // 溢出，等待下一个毫秒
                $timestamp = $this->tilNextMillis($timestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        $delta = $timestamp - self::EPOCH;

        // 高性能位运算拼接
        return ($delta << self::TIMESTAMP_SHIFT)
            | ($this->dataCenterId << self::DATACENTER_SHIFT)
            | ($this->workerId << self::WORKER_SHIFT)
            | $this->sequence;
    }

    /**
     * 固定长度 ID
     */
    public function nextFixedLengthId(int $length): string
    {
        return str_pad((string)$this->nextId(), $length, '0', STR_PAD_LEFT);
    }

    /**
     * 使用 hrtime() 生成更精准的毫秒时间戳
     */
    private function timeGen(): int
    {
        return intval(hrtime(true) / 1_000_000);
    }

    /**
     * 等待下一毫秒（避免 CPU 自旋）
     */
    private function tilNextMillis(int $lastTimestamp): int
    {
        $timestamp = $this->timeGen();

        while ($timestamp <= $lastTimestamp) {
            // 避免空转导致 CPU 100%
            usleep(50);
            $timestamp = $this->timeGen();
        }

        return $timestamp;
    }
}
