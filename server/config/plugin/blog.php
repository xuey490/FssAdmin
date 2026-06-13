<?php

declare(strict_types=1);

/**
 * blog 插件配置文件
 */

return [
    'posts_per_page' => 10,
    'enable_comments' => true,
    'enable_tags' => true,
    'enable_categories' => true,
    'upload_path' => 'uploads/blog',
    'allowed_image_types' => [
        0 => 'jpg',
        1 => 'jpeg',
        2 => 'png',
        3 => 'gif',
        4 => 'webp',
    ],
    'max_image_size' => 1024,
    'generate_sitemap' => true,
    'url_prefix' => '/blogs',
    'enable_review' => false,
    'default_status' => 'draft',
];
