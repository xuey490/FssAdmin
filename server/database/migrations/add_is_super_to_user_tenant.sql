-- 为 sa_system_user_tenant 表添加 is_super 字段
-- 用于标识用户是否为租户管理员

ALTER TABLE `sa_system_user_tenant` 
ADD COLUMN `is_super` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否租户管理员：0=否 1=是' AFTER `is_default`;

-- 为 is_super 字段添加索引以提高查询性能
CREATE INDEX `idx_is_super` ON `sa_system_user_tenant` (`is_super`);
