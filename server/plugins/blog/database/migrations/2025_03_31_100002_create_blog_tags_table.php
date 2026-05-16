<?php

declare(strict_types=1);

/**
 * 博客数据库迁移 - 创建标签表
 *
 * @package Plugins\Blog\Database\Migrations
 */

namespace Plugins\Blog\Database\Migrations;

use Framework\Plugin\Migration\Migration;

/**
 * 创建博客标签表
 */
class CreateBlogTagsTable extends Migration
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
            $this->schema()->create('blog_tags', function ($table) {
                $table->id();
                $table->string('name', 50)->comment('标签名称');
                $table->string('slug', 50)->unique()->comment('URL别名');
                $table->timestamps();
            });

            // 文章标签关联表
            $this->schema()->create('blog_post_tags', function ($table) {
                $table->unsignedBigInteger('post_id');
                $table->unsignedBigInteger('tag_id');
                $table->primary(['post_id', 'tag_id']);

                $table->foreign('post_id')->references('id')->on('blog_posts')->onDelete('cascade');
                $table->foreign('tag_id')->references('id')->on('blog_tags')->onDelete('cascade');
            });
        } else {
            $this->statement("
                CREATE TABLE IF NOT EXISTS `blog_tags` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(50) NOT NULL COMMENT '标签名称',
                    `slug` VARCHAR(50) NOT NULL UNIQUE COMMENT 'URL别名',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='博客标签表';
            ");

            $this->statement("
                CREATE TABLE IF NOT EXISTS `blog_post_tags` (
                    `post_id` BIGINT UNSIGNED NOT NULL,
                    `tag_id` BIGINT UNSIGNED NOT NULL,
                    PRIMARY KEY (`post_id`, `tag_id`),
                    INDEX `idx_tag_id` (`tag_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章标签关联表';
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
        $this->dropTable('blog_post_tags');
        $this->dropTable('blog_tags');
    }
}
