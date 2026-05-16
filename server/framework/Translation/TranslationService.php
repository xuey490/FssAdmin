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

namespace Framework\Translation;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * 国际化翻译服务.
 *
 * 基于 Symfony Translation 组件实现的翻译服务，支持多语言切换和 YAML 格式的翻译文件。
 * 自动根据请求参数、Cookie 或浏览器偏好设置语言环境。
 *
 * 主要功能：
 * - 自动检测和设置语言环境（支持 URL 参数、Cookie、浏览器偏好）
 * - 加载 YAML 格式的翻译资源文件
 * - 提供翻译方法，支持参数替换
 *
 * 支持的语言：en（英语）、zh_CN（简体中文）、zh_TW（繁体中文）、ja（日语）
 *
 * @package Framework\Translation
 */
class TranslationService
{
    /**
     * Symfony Translator 实例.
     *
     * @var Translator
     */
    private Translator $translator;

    /**
     * 构造函数，初始化翻译服务.
     *
     * @param RequestStack $requestStack   请求栈，用于获取当前请求
     * @param string       $translationDir 翻译文件目录路径
     */
    public function __construct(
        private RequestStack $requestStack,
        private string $translationDir
    ) {
        $this->translator = $this->buildTranslator();
    }

    /**
     * 翻译文本.
     *
     * 根据当前语言环境翻译指定的消息 ID，支持参数占位符替换。
     *
     * @param string      $id         翻译键（消息 ID）
     * @param array       $parameters 占位符替换参数数组
     * @param string      $domain     翻译域，默认为 'messages'
     * @param string|null $locale     目标语言，为 null 时使用当前语言环境
     *
     * @return string 翻译后的文本
     */
    public function trans(string $id, array $parameters = [], string $domain = 'messages', ?string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    private function buildTranslator(): Translator
    {
        $request   = $this->requestStack->getCurrentRequest();
        $supported = ['en', 'zh_CN', 'zh_TW', 'ja'];

        if ($request) {
            $lang = $request->get('lang');
            if ($lang && in_array($lang, $supported)) {
                $locale = $lang;
                setcookie('user_locale', $locale, time() + 3600 * 24 * 30, '/', '', false, true);
            } elseif (isset($_COOKIE['user_locale']) && in_array($_COOKIE['user_locale'], $supported)) {
                $locale = $_COOKIE['user_locale'];
            } else {
                $locale = $request->getPreferredLanguage($supported) ?: 'en';
            }
        } else {
            $locale = 'en';
        }
        // 可选：将 locale 存入 request attributes，便于后续使用
        $request->attributes->set('_locale', $locale);

        $translator = new Translator($locale);
        $loader     = new YamlFileLoader();
        $translator->addLoader('yaml', $loader);

        foreach ($supported as $loc) {
            $file = $this->translationDir . "/messages.{$loc}.yaml";
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $loc);
            }
        }

        return $translator;
    }
}
