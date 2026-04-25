<?php

declare(strict_types=1);

/**
 * 博客数据库迁移 - 创建文章表
 *
 * @package Plugins\Blog\Database\Migrations
 */

namespace Plugins\Blog\Database\Migrations;

use Framework\Plugin\Migration\Migration;

/**
 * 创建博客文章表
 */
class CreateBlogPostsTable extends Migration
{
    /**
     * 执行迁移
     *
     * @return void
     */
    public function up(): void
    {
        $config = require BASE_PATH . '/config/database.php';
        $engine = $config['engine'] ?? 'thinkORM';

        if ($engine === 'laravelORM') {
            // Laravel ORM 方式（使用 SchemaBuilder，避免 Facade 依赖）
            $this->schema()->create('blog_posts', function ($table) {
                $table->id();
                $table->string('title', 255)->comment('文章标题');
                $table->string('slug', 191)->unique()->comment('URL别名');
                $table->string('summary', 500)->nullable()->comment('文章摘要');
                $table->text('content')->comment('文章内容');
                $table->string('cover_image', 255)->nullable()->comment('封面图片');
                $table->unsignedBigInteger('category_id')->nullable()->comment('分类ID');
                $table->unsignedBigInteger('author_id')->nullable()->comment('作者ID');
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->comment('状态');
                $table->unsignedInteger('view_count')->default(0)->comment('浏览量');
                $table->boolean('is_top')->default(false)->comment('是否置顶');
                $table->timestamp('published_at')->nullable()->comment('发布时间');
                $table->timestamps();
                $table->softDeletes();

                $table->index('category_id');
                $table->index('author_id');
                $table->index('status');
                $table->index('published_at');
            });
        } else {
            // ThinkORM / 原生 SQL 方式
            $this->statement("
                CREATE TABLE IF NOT EXISTS `blog_posts` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(255) NOT NULL COMMENT '文章标题',
                    `slug` VARCHAR(191) NOT NULL UNIQUE COMMENT 'URL别名',
                    `summary` VARCHAR(500) NULL COMMENT '文章摘要',
                    `content` TEXT NOT NULL COMMENT '文章内容',
                    `cover_image` VARCHAR(255) NULL COMMENT '封面图片',
                    `category_id` BIGINT UNSIGNED NULL COMMENT '分类ID',
                    `author_id` BIGINT UNSIGNED NULL COMMENT '作者ID',
                    `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft' COMMENT '状态',
                    `view_count` INT UNSIGNED DEFAULT 0 COMMENT '浏览量',
                    `is_top` TINYINT(1) DEFAULT 0 COMMENT '是否置顶',
                    `published_at` TIMESTAMP NULL COMMENT '发布时间',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `deleted_at` TIMESTAMP NULL,
                    INDEX `idx_category_id` (`category_id`),
                    INDEX `idx_author_id` (`author_id`),
                    INDEX `idx_status` (`status`),
                    INDEX `idx_published_at` (`published_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='博客文章表';
            ");
        }
    }

    /**
     * 回滚迁移
     *
     * @return void
     */
    public function down(): void
    {
        $this->dropTable('blog_posts');
    }
}
