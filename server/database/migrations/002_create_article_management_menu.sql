-- ============================================================================
-- Migration: Create Article Management Menu and Permissions
-- Description: Create menu structure and permission configuration for article management module
-- Date: 2026-01-10
-- Related Requirements: Menu configuration for article management
-- ============================================================================

-- Step 1: Insert parent menu - Article Management
INSERT INTO `sa_system_menu` (`parent_id`, `name`, `path`, `component`, `icon`, `type`, `sort`, `status`, `permission`, `remark`, `create_time`, `update_time`) 
VALUES 
(0, '文章管理', '/article', 'Layout', 'article', 1, 50, 1, '', '文章管理模块', NOW(), NOW());

-- Get the parent menu ID
SET @article_menu_id = LAST_INSERT_ID();

-- Step 2: Insert child menu - Article List
INSERT INTO `sa_system_menu` (`parent_id`, `name`, `path`, `component`, `icon`, `type`, `sort`, `status`, `permission`, `remark`, `create_time`, `update_time`) 
VALUES 
(@article_menu_id, '文章列表', 'list', 'article/index', 'list', 2, 1, 1, 'article:list', '文章列表页面', NOW(), NOW());

-- Get the article list menu ID
SET @article_list_menu_id = LAST_INSERT_ID();

-- Step 3: Insert button permissions for Article List
INSERT INTO `sa_system_menu` (`parent_id`, `name`, `path`, `component`, `icon`, `type`, `sort`, `status`, `permission`, `remark`, `create_time`, `update_time`) 
VALUES 
(@article_list_menu_id, '查看文章', '', '', '', 3, 1, 1, 'article:read', '查看文章详情', NOW(), NOW()),
(@article_list_menu_id, '新增文章', '', '', '', 3, 2, 1, 'article:create', '新增文章', NOW(), NOW()),
(@article_list_menu_id, '编辑文章', '', '', '', 3, 3, 1, 'article:update', '编辑文章', NOW(), NOW()),
(@article_list_menu_id, '删除文章', '', '', '', 3, 4, 1, 'article:delete', '删除文章', NOW(), NOW()),
(@article_list_menu_id, '更新状态', '', '', '', 3, 5, 1, 'article:status', '更新文章状态', NOW(), NOW());

-- ============================================================================
-- Menu Structure:
-- 文章管理 (article)
-- └── 文章列表 (article:list)
--     ├── 查看文章 (article:read)
--     ├── 新增文章 (article:create)
--     ├── 编辑文章 (article:update)
--     ├── 删除文章 (article:delete)
--     └── 更新状态 (article:status)
-- ============================================================================
-- Migration completed successfully
-- ============================================================================
