<?php

declare(strict_types=1);

namespace Framework\Attributes;

use Attribute;

/**
 * #[Permission]
 * 用于定义控制器或方法的权限标识。
 *
 * 示例：
 * #[Permission('core:user:index')]  // 字符串格式 - 单个权限
 * #[Permission(['core:user:index'])]  // 数组格式 - 单个权限
 * #[Permission(['core:user:index', 'core:dept:index'])]  // 数组格式 - OR模式（默认）
 * #[Permission(['core:user:index', 'core:dept:index'], mode: 'OR')]  // 显式OR模式
 * #[Permission(['core:user:index', 'core:dept:index'], mode: 'AND')]  // AND模式
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Permission
{
    public array $slugs;
    public string $mode;

    /**
     * @param string|array<string> $permissions 权限标识（字符串或数组）
     * @param string $mode 匹配模式：'OR' (满足其一) 或 'AND' (必须全部满足)
     */
    public function __construct(
        string|array $permissions,
        string $mode = 'OR'
    ) {
        // 将字符串格式标准化为数组格式
        $this->slugs = is_string($permissions) ? [$permissions] : $permissions;
        $this->mode = strtoupper($mode);
    }
}
