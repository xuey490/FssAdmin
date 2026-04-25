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

/*
改成符合psr-4规则
*/

namespace Framework\View;

use think\Template;

class ThinkTemplateFactory
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function create(): Template
    {
        // 1. 创建模板引擎实例
        $template = new Template($this->config);

        // 2. 注册自定义函数（通过 assign 模拟函数注入）
        $template->assign([
            'hello'          => 'tpTemplateHello',
            'formatDate'     => 'tpTemplateFormatDate',
            'web_csrf_field' => 'WebCsrfField',
            'api_csrf_field' => 'APICsrfField',
        ]);

        // 3. 可扩展：注册更多自定义功能（如标签、过滤器等）
        // 示例：$template->registerFunction('myFunc', 'MyClass::myFunc');

        return $template;
    }
}
