<?php

declare(strict_types=1);

/**
 * 博客数据库迁移 - 创建分类表
 *
 * @package Plugins\Blog\Database\Migrations
 */

namespace Plugins\Blog\Database\Migrations;

use Framework\Plugin\Migration\Migration;

/**
 * 创建博客分类表
 */
class CreateBlogCategoriesTable extends Migration
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
            $this->schema()->create('blog_categories', function ($table) {
                $table->id();
                $table->string('name', 100)->comment('分类名称');
                $table->string('slug', 100)->unique()->comment('URL别名');
                $table->string('description', 500)->nullable()->comment('分类描述');
                $table->unsignedBigInteger('parent_id')->default(0)->comment('父分类ID');
                $table->integer('sort_order')->default(0)->comment('排序');
                $table->timestamps();

                $table->index('parent_id');
                $table->index('sort_order');
            });
        } else {
            $this->statement("
                CREATE TABLE IF NOT EXISTS `blog_categories` (
                    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL COMMENT '分类名称',
                    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL别名',
                    `description` VARCHAR(500) NULL COMMENT '分类描述',
                    `parent_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '父分类ID',
                    `sort_order` INT DEFAULT 0 COMMENT '排序',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_parent_id` (`parent_id`),
                    INDEX `idx_sort_order` (`sort_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='博客分类表';
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
        $this->dropTable('blog_categories');
    }
}
