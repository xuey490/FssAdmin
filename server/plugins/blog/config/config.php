<?php
/**
 * 博客插件默认配置
 *
 * 此配置文件会与 config/plugin/blog.php 合并
 * 优先级：运行时配置 > 此文件 > 全局默认配置
 */

return [
    'posts_per_page' => 10,
    'enable_comments' => true,
    'enable_tags' => true,
    'enable_categories' => true,
    'upload_path' => 'uploads/blog',
    'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'max_image_size' => 2048,
    'generate_sitemap' => true,
    'url_prefix' => '/blog',
    'enable_review' => false,
    'default_status' => 'draft',
];
