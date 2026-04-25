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

trait ViewRender
{
    // ========== SEO 属性 ==========
    public $page_title = '';

    public $page_keywords = '';

    public $page_description = '';

    public $page_title_suffix = ' - MySite';

    // ========== Layout & Section ==========
    protected $layout;

    private $contentBuffer = '';

    private $sections = []; // ✅ 改为 private

    // ========== 新增：renderPartial 方法 ==========

    /**
     * 渲染局部模板（不走 layout）.
     */
    protected function renderPartial(string $template, array $data = []): string
    {
        $tpl = $this->getTemplateEngine();

        // 🔽 先开启输出缓冲，防止 fetch() 直接输出
        ob_start();

        try {
            // 🔽 assign 后 fetch
            $tpl->assign($data);
            $content = $tpl->fetch($template);

            // 如果 fetch() 返回 false，说明模板不存在
            if ($content === false) {
                $cleaned = ob_get_clean();
                return '局部模板渲染失败: 模板 [' . $template . '] 不存在';
            }

            // 🔽 如果 fetch() 已经输出了内容，这里用缓冲内容兜底
            $output = ob_get_clean();

            // 优先使用 fetch 返回值，否则用缓冲输出
            return $content ?: $output;
        } catch (\Throwable $e) {
            ob_end_clean();
            return '局部模板渲染失败: ' . $e->getMessage();
        }
    }

    // ========== Section 方法 ==========
    protected function section(string $name, string $content)
    {
        $this->sections[$name] = $content;
        return $this;
    }

    protected function appendToSection(string $name, string $content)
    {
        $this->sections[$name] = ($this->sections[$name] ?? '') . $content;
        return $this;
    }

    // ========== SEO & Layout 方法（保持不变）==========
    protected function title(string $title)
    {
        $this->page_title = $title;
        return $this;
    }

    protected function keywords(string $keywords)
    {
        $this->page_keywords = $keywords;
        return $this;
    }

    protected function description(string $description)
    {
        $this->page_description = $description;
        return $this;
    }

    protected function layout(?string $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    // ========== 主渲染流程（保持不变）==========

    protected function render(string $template, array $data = [], ?array $exclude = null): string
    {
        $assignData          = $this->collectViewData($data, $exclude);
        $this->contentBuffer = $this->renderContent($template, $assignData);

        if ($this->layout !== null) {
            return $this->renderWithLayout($this->layout, $assignData);
        }

        return $this->contentBuffer;
    }

    protected function display(string $template, array $data = [], ?array $exclude = null)
    {
        $content = $this->render($template, $data, $exclude);
        return response($content, 200, [], 'html');
    }

    private function collectViewData(array $data, ?array $exclude = null): array
    {
        $defaultExclude = ['contentBuffer', 'layout', 'sections', 'data', 'exclude', 'template'];
        $exclude        = $exclude ? array_merge($defaultExclude, $exclude) : $defaultExclude;

        $publicVars = $this->getPublicProperties();
        $merged     = array_merge($publicVars, $data);

        return array_diff_key($merged, array_flip($exclude));
    }

    private function renderContent(string $template, array $data): string
    {
        $tpl = $this->getTemplateEngine();

        ob_start();

        try {
            $tpl->assign($data);
            $content = $tpl->fetch($template);

            if ($content === false) {
                $cleaned = ob_get_clean();
                return '模板变量渲染失败: 模板 [' . $template . '] 不存在';
            }

            $output = ob_get_clean();
            return $content ?: $output;
        } catch (\Throwable $e) {
            ob_end_clean();
            return '模板变量渲染失败: ' . $e->getMessage();
        }
    }

    private function renderWithLayout(string $layout, array $data): string
    {
        $sectionVars = [];
        foreach ($this->sections as $key => $content) {
            $sectionVars["__SECTION_{$key}__"] = $content;
        }
        $sectionVars['__CONTENT__'] = $this->contentBuffer;

        $this->setupSeo($sectionVars);
        $finalData = array_merge($data, $sectionVars);

        $tpl = $this->getTemplateEngine();

        ob_start();

        try {
            $tpl->assign($finalData);
            $content = $tpl->fetch($layout);

            if ($content === false) {
                $cleaned = ob_get_clean();
                return '布局模板渲染失败: 布局 [' . $layout . '] 不存在';
            }

            $output = ob_get_clean();
            return $content ?: $output;
        } catch (\Throwable $e) {
            ob_end_clean();
            return '布局模板渲染失败: ' . $e->getMessage();
        }
    }

    private function setupSeo(array &$data)
    {
        $defaultTitle = $this->getDefaultTitle();
        $title        = $this->page_title ?: $defaultTitle;
        if ($this->page_title_suffix && strpos($title, $this->page_title_suffix) === false) {
            $title .= $this->page_title_suffix;
        }

        $data['page_title']       = $title;
        $data['page_keywords']    = $this->page_keywords ?: config('app.keywords', 'ThinkPHP,项目');
        $data['page_description'] = $this->page_description ?: config('app.description', '这是一个 ThinkPHP 项目');
    }

    private function getDefaultTitle(): string
    {
        $class = get_called_class();
        $short = substr($class, strrpos($class, '\\') + 1);
        return str_replace('Controller', '', $short);
    }

    private function getPublicProperties(): array
    {
        $reflect = new \ReflectionObject($this);
        $props   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
        $vars    = [];
        foreach ($props as $prop) {
            if (! $prop->isStatic()) {
                $vars[$prop->getName()] = $this->{$prop->getName()};
            }
        }
        return $vars;
    }

    private function getTemplateEngine()
    {
        $engine = app('thinkTemp');
        if (! $engine) {
            throw new \RuntimeException('模板引擎服务 [thinkTemp] 未注册');
        }
        return $engine;
    }
}
