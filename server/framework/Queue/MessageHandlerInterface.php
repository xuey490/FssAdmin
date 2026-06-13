<?php

declare(strict_types=1);

/**
 * @Filename: MessageHandlerInterface.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Queue;

/**
 * 消息处理契约
 *
 * 所有队列消费处理器均需实现此接口。
 * 实现类应保证幂等性：同一消息多次投递产生相同结果。
 *
 * 示例：
 * ```php
 * class SendEmailHandler implements MessageHandlerInterface
 * {
 *     public function handle(array|string $payload): void
 *     {
 *         $data = is_string($payload) ? json_decode($payload, true) : $payload;
 *         // 发送邮件逻辑...
 *     }
 * }
 * ```
 */
interface MessageHandlerInterface
{
    /**
     * 处理消息
     *
     * @param array<string, mixed>|string $payload 消息内容（数组或 JSON 字符串）
     * @return void
     * @throws \Throwable 抛出异常时消费者将触发重试/死信策略
     */
    public function handle(array|string $payload): void;
}
