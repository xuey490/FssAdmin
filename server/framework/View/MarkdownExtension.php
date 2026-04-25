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

namespace Framework\View;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    private MarkdownConverter $converter;

    public function __construct()
    {
        // 1. 配置解析环境
        $config = [
            'html_input'         => 'strip', // 安全处理：剥离输入中的 HTML
            'allow_unsafe_links' => false,
        ];

        // 2. 创建环境并加载核心扩展
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        // 3. 创建 Markdown 转换器实例
        $this->converter = new MarkdownConverter($environment);
    }

    public function getFilters(): array
    {
        return [
            // 注册一个名为 'markdown_to_html' 的 Twig 过滤器
            new TwigFilter('markdown_to_html', [$this, 'convertMarkdownToHtml'], [
                'is_safe' => ['html'], // 告诉 Twig 这个过滤器的输出是安全的 HTML，可以直接渲染
            ]),
        ];
    }

    /**
     * 将 Markdown 文本转换为 HTML.
     *
     * @param  string $markdown the Markdown text to convert
     * @return string the converted HTML
     */
    public function convertMarkdownToHtml(string $markdown): string
    {
        // 如果输入为空，则直接返回空字符串
        if (empty(trim($markdown))) {
            return '';
        }

        return $this->converter->convert($markdown)->getContent();
    }
}
