/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : fssoa

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2026-04-25 20:06:00
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `casbin_rule`
-- ----------------------------
DROP TABLE IF EXISTS `casbin_rule`;
CREATE TABLE `casbin_rule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ptype` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '策略类型: p(权限) / g(角色继承) / g2(部门继承)',
  `v0` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '第1个参数: 用户ID/角色ID/部门ID',
  `v1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '第2个参数: 资源/角色/部门',
  `v2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '第3个参数: 操作/动作',
  `v3` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '第4个参数: 扩展字段',
  `v4` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '第5个参数: 扩展字段',
  `v5` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '第6个参数: 扩展字段',
  PRIMARY KEY (`id`),
  KEY `idx_ptype` (`ptype`),
  KEY `idx_v0` (`v0`),
  KEY `idx_v1` (`v1`),
  KEY `idx_v0_v1` (`v0`,`v1`)
) ENGINE=InnoDB AUTO_INCREMENT=1321 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Casbin权限规则表';

-- ----------------------------
-- Records of casbin_rule
-- ----------------------------
INSERT INTO `casbin_rule` VALUES ('886', 'p', '1', '/api/api/core/system/user', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1123', 'p', '10', '/api/core/console', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1124', 'p', '10', '/api/core/console/list', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1125', 'p', '10', '/api/core/console/*', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1196', 'p', 'ceo', '/api/core/console', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1197', 'p', 'ceo', '/api/core/console/list', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1198', 'p', 'ceo', '/api/core/console/*', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1201', 'g', '2', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1202', 'g', '105', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1203', 'g', '104', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1204', 'g', '101', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1254', 'g', '10', 'staff', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1259', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1260', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1261', 'p', '100', '/api/core/user', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1262', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1263', 'p', '100', '/api/core/user', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1264', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1265', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1266', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1267', 'p', '100', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1268', 'p', '100', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1269', 'p', '100', '/api/core/role', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1270', 'p', '100', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1271', 'p', '100', '/api/core/role', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1272', 'p', '100', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1273', 'p', '100', '/api/tool/crontab', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1274', 'p', '100', '/api/tool/crontab', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1275', 'p', '100', '/api/tool/crontab', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1276', 'p', '100', '/api/tool/code', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1277', 'p', '100', '/api/tool/code', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1278', 'p', 'JTCEO', '/api/core/console', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1279', 'p', 'JTCEO', '/api/core/console/list', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1280', 'p', 'JTCEO', '/api/core/console/*', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1281', 'p', 'JTCEO', '/api/tool/crontab', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1282', 'p', 'JTCEO', '/api/tool/crontab', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1283', 'p', 'JTCEO', '/api/tool/crontab', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1284', 'p', 'JTCEO', '/api/tool/code', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1285', 'p', 'JTCEO', '/api/tool/code', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1301', 'g', '100', 'JTCEO', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1302', 'g', '100', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1319', 'g', '1', 'super_admin', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1320', 'g', '1', 'ceo', '', '', '', '');

-- ----------------------------
-- Table structure for `plugin_migrations`
-- ----------------------------
DROP TABLE IF EXISTS `plugin_migrations`;
CREATE TABLE `plugin_migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `migration_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `migration_file` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_plugin_name` (`plugin_name`),
  KEY `idx_migration_name` (`migration_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件安装表';


DROP TABLE IF EXISTS `sa_system_login_log`;
CREATE TABLE `sa_system_login_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(20) DEFAULT NULL COMMENT '用户名',
  `ip` varchar(45) DEFAULT NULL COMMENT '登录IP地址',
  `ip_location` varchar(255) DEFAULT NULL COMMENT 'IP所属地',
  `os` varchar(50) DEFAULT NULL COMMENT '操作系统',
  `browser` varchar(50) DEFAULT NULL COMMENT '浏览器',
  `status` smallint(6) DEFAULT '1' COMMENT '登录状态 (1成功 2失败)',
  `message` varchar(50) DEFAULT NULL COMMENT '提示消息',
  `login_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '登录时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `idx_create_time` (`create_time`) USING BTREE,
  KEY `idx_login_time` (`login_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=342 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='登录日志表';

DROP TABLE IF EXISTS `sa_system_oper_log`;
CREATE TABLE `sa_system_oper_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(20) DEFAULT NULL COMMENT '用户名',
  `app` varchar(50) DEFAULT NULL COMMENT '应用名称',
  `method` varchar(20) DEFAULT NULL COMMENT '请求方式',
  `router` varchar(500) DEFAULT NULL COMMENT '请求路由',
  `service_name` varchar(30) DEFAULT NULL COMMENT '业务名称',
  `ip` varchar(45) DEFAULT NULL COMMENT '请求IP地址',
  `ip_location` varchar(255) DEFAULT NULL COMMENT 'IP所属地',
  `request_data` text COMMENT '请求数据',
  `duration` varchar(20) DEFAULT NULL COMMENT '耗时',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `idx_create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=541 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='操作日志表';


-- ----------------------------
-- Records of plugin_migrations
-- ----------------------------

-- ----------------------------
-- Table structure for `sa_article_category`
-- ----------------------------
DROP TABLE IF EXISTS `sa_article_category`;
CREATE TABLE `sa_article_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `category_name` varchar(255) NOT NULL COMMENT '分类标题',
  `describe` varchar(255) DEFAULT NULL COMMENT '分类简介',
  `image` varchar(255) DEFAULT NULL COMMENT '分类图片',
  `sort` int(10) unsigned DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章分类表';

-- ----------------------------
-- Records of sa_article_category
-- ----------------------------
INSERT INTO `sa_article_category` VALUES ('1', '0', '大国科技', '', null, '100', '1', '1', '1', '2024-06-02 22:50:51', '2026-01-06 18:03:07', null);
INSERT INTO `sa_article_category` VALUES ('2', '0', '数字经济', '', null, '100', '1', '1', '1', '2024-06-02 22:50:56', '2026-01-09 16:54:05', null);
INSERT INTO `sa_article_category` VALUES ('3', '0', '科技快讯', '', null, '100', '1', '1', '1', '2024-06-02 22:51:01', '2026-01-07 01:03:37', null);
INSERT INTO `sa_article_category` VALUES ('4', '0', '低空经济', '', null, '100', '1', '1', '1', '2024-06-02 22:51:16', '2026-01-06 18:03:14', null);

-- ----------------------------
-- Table structure for `sa_system_attachment`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_attachment`;
CREATE TABLE `sa_system_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `category_id` int(11) DEFAULT '0' COMMENT '文件分类',
  `storage_mode` smallint(6) DEFAULT '1' COMMENT '存储模式 (1 本地 2 阿里云 3 七牛云 4 腾讯云)',
  `origin_name` varchar(255) DEFAULT NULL COMMENT '原文件名',
  `object_name` varchar(50) DEFAULT NULL COMMENT '新文件名',
  `hash` varchar(64) DEFAULT NULL COMMENT '文件hash',
  `mime_type` varchar(255) DEFAULT NULL COMMENT '资源类型',
  `storage_path` varchar(100) DEFAULT NULL COMMENT '存储目录',
  `suffix` varchar(10) DEFAULT NULL COMMENT '文件后缀',
  `size_byte` bigint(20) DEFAULT NULL COMMENT '字节数',
  `size_info` varchar(50) DEFAULT NULL COMMENT '文件大小',
  `url` varchar(255) DEFAULT NULL COMMENT 'url地址',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `hash` (`hash`) USING BTREE,
  KEY `idx_url` (`url`) USING BTREE,
  KEY `idx_create_time` (`create_time`) USING BTREE,
  KEY `idx_category_id` (`category_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='附件信息表';

-- ----------------------------
-- Records of sa_system_attachment
-- ----------------------------
INSERT INTO `sa_system_attachment` VALUES ('14', '1', '1', '2205be50f7884aa2ad8c9fb214460729_weixin_36343299.jpg', '69c7a15ee50ff0.57272058.jpg', '4a5a5cae301074163209b84a5442659a', 'image/jpeg', 'uploads/2026/03/28', 'jpg', '8776', '8.57 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a15ee50ff0.57272058.jpg', null, '1', '1', '2026-03-28 17:37:34', '2026-03-28 17:37:34', null);
INSERT INTO `sa_system_attachment` VALUES ('15', '1', '1', 'cat.webp', '69c7a39ee92e22.73142230.webp', '5cb0bcbecf611c2dfdf9dd4071265ad5', 'image/webp', 'uploads/2026/03/28', 'webp', '6914', '6.75 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a39ee92e22.73142230.webp', null, '1', '1', '2026-03-28 17:47:10', '2026-03-28 17:47:10', null);
INSERT INTO `sa_system_attachment` VALUES ('16', '1', '1', 'mjc.88aab0a2.png', '69c7a3a1cae578.20110121.png', '9955a9a409100c212f218f6570ae5c5d', 'image/png', 'uploads/2026/03/28', 'png', '6108', '5.96 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3a1cae578.20110121.png', null, '1', '1', '2026-03-28 17:47:13', '2026-03-28 17:47:13', null);
INSERT INTO `sa_system_attachment` VALUES ('17', '1', '1', 'pic.webp', '69c7a3a453f785.28306567.webp', '40dc7c6d3b8e14cfa28df6a96124c6f8', 'image/webp', 'uploads/2026/03/28', 'webp', '2364', '2.31 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3a453f785.28306567.webp', null, '1', '1', '2026-03-28 17:47:16', '2026-03-28 17:47:16', null);
INSERT INTO `sa_system_attachment` VALUES ('18', '1', '1', 'ScreenShot_2026-03-28_171541_015.png', '69c7a3a67d5e50.12092222.png', 'b65216028a95a8503f997a62bf3ee969', 'image/png', 'uploads/2026/03/28', 'png', '16427', '16.04 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3a67d5e50.12092222.png', null, '1', '1', '2026-03-28 17:47:18', '2026-03-28 17:47:18', null);
INSERT INTO `sa_system_attachment` VALUES ('19', '1', '1', 'ScreenShot_2026-03-28_171553_082.png', '69c7a3a83f9f14.20042528.png', '48d654f3fb0c398ad12845b26d7c8145', 'image/png', 'uploads/2026/03/28', 'png', '21050', '20.56 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3a83f9f14.20042528.png', null, '1', '1', '2026-03-28 17:47:20', '2026-03-28 17:47:20', null);
INSERT INTO `sa_system_attachment` VALUES ('20', '1', '1', 'ScreenShot_2026-03-28_171606_033.png', '69c7a3a9e6c335.87540530.png', 'df0009a61f16774fa228c2779c4c5d54', 'image/png', 'uploads/2026/03/28', 'png', '16632', '16.24 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3a9e6c335.87540530.png', null, '1', '1', '2026-03-28 17:47:21', '2026-03-28 17:47:21', null);
INSERT INTO `sa_system_attachment` VALUES ('21', '1', '1', 'ScreenShot_2026-03-28_171614-602.png', '69c7a3acd6b581.21163358.png', '30dc6ef573fcafd0420c0ddf409756b4', 'image/png', 'uploads/2026/03/28', 'png', '6887', '6.73 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3acd6b581.21163358.png', null, '1', '1', '2026-03-28 17:47:24', '2026-03-28 20:42:49', null);
INSERT INTO `sa_system_attachment` VALUES ('22', '1', '1', 'secpw8Be3MLmuTFCaQSVDENOn7K6Hz7GZBRJxZ69.webp', '69c7a3af23d945.66099869.webp', 'f80bcc04c839d890928006ec9d598e88', 'image/webp', 'uploads/2026/03/28', 'webp', '13724', '13.4 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3af23d945.66099869.webp', null, '1', '1', '2026-03-28 17:47:27', '2026-03-28 17:47:27', null);
INSERT INTO `sa_system_attachment` VALUES ('23', '1', '1', 'vip.webp', '69c7a3b2058589.44968839.webp', 'df757eb87d81ac74faf2a04b1260fb5d', 'image/webp', 'uploads/2026/03/28', 'webp', '10204', '9.96 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3b2058589.44968839.webp', null, '1', '1', '2026-03-28 17:47:30', '2026-03-28 17:47:30', null);
INSERT INTO `sa_system_attachment` VALUES ('24', '2', '1', 'wel_tips.5624828.png', '69c7a3b48aab47.53936592.png', '5624828dcc8975b34dd8af3c5b6229d5', 'image/png', 'uploads/2026/03/28', 'png', '16937', '16.54 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3b48aab47.53936592.png', null, '1', '1', '2026-03-28 17:47:32', '2026-04-21 01:05:18', null);
INSERT INTO `sa_system_attachment` VALUES ('25', '1', '1', 'cilixian.org.txt', '69c7e30c6d3b81.92728200.txt', '285db3e41cd89dcd20c383ae42b10244', 'text/plain', 'uploads/2026/03/28', 'txt', '3470', '3.39 KB', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7e30c6d3b81.92728200.txt', null, '1', '1', '2026-03-28 22:17:48', '2026-03-28 23:48:12', '2026-03-28 23:48:12');

-- ----------------------------
-- Table structure for `sa_system_category`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_category`;
CREATE TABLE `sa_system_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `parent_id` int(11) NOT NULL DEFAULT '0' COMMENT '父id',
  `level` varchar(255) DEFAULT NULL COMMENT '组集关系',
  `category_name` varchar(100) NOT NULL DEFAULT '' COMMENT '分类名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pid` (`parent_id`) USING BTREE,
  KEY `sort` (`sort`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='附件分类表';

-- ----------------------------
-- Records of sa_system_category
-- ----------------------------
INSERT INTO `sa_system_category` VALUES ('1', '0', '0,', '全部分类', '100', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_category` VALUES ('2', '1', '0,1,', '图片分类', '100', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-04-21 01:04:51', null);
INSERT INTO `sa_system_category` VALUES ('3', '1', '0,1,', '文件分类', '100', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-03-23 21:54:20', null);
INSERT INTO `sa_system_category` VALUES ('4', '1', '0,1,', '系统图片', '100', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_category` VALUES ('5', '1', '0,1,', '其他分类', '100', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-03-28 23:51:54', null);
INSERT INTO `sa_system_category` VALUES ('6', '1', '0,1,', 'ces', '100', '1', '', '1', '1', '2026-03-23 21:52:33', '2026-03-23 21:52:35', '2026-03-23 21:52:35');
INSERT INTO `sa_system_category` VALUES ('7', '2', '0,1,2,', '测试', '100', '1', '', '1', '1', '2026-03-23 21:52:46', '2026-03-23 22:02:52', '2026-03-23 22:02:52');

-- ----------------------------
-- Table structure for `sa_system_config`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_config`;
CREATE TABLE `sa_system_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `group_id` int(11) DEFAULT NULL COMMENT '组id',
  `key` varchar(32) NOT NULL COMMENT '配置键名',
  `value` text COMMENT '配置值',
  `name` varchar(255) DEFAULT NULL COMMENT '配置名称',
  `input_type` varchar(32) DEFAULT NULL COMMENT '数据输入类型',
  `config_select_data` varchar(500) DEFAULT NULL COMMENT '配置选项数据',
  `sort` smallint(5) unsigned DEFAULT '0' COMMENT '排序',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建人',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`,`key`) USING BTREE,
  KEY `group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='参数配置信息表';

-- ----------------------------
-- Records of sa_system_config
-- ----------------------------
INSERT INTO `sa_system_config` VALUES ('1', '1', 'site_copyright', 'Copyright © 2026 FSSPHP Team', '版权信息', 'textarea', null, '96', '', '1', '1', '2026-01-01 00:00:00', '2026-03-22 20:34:45', null);
INSERT INTO `sa_system_config` VALUES ('2', '1', 'site_desc', '基于Vue3 + FSSPHP 的极速开发框架', '网站描述', 'textarea', null, '97', null, '1', '1', '2026-01-01 00:00:00', '2026-04-23 21:36:30', null);
INSERT INTO `sa_system_config` VALUES ('3', '1', 'site_keywords', 'FSSPHP, Workerman，symfony，Thinkphp，后台管理系统', '网站关键字', 'input', null, '98', null, '1', '1', '2026-01-01 00:00:00', '2026-04-23 21:36:23', null);
INSERT INTO `sa_system_config` VALUES ('4', '1', 'site_name', 'FssAdmin后台管理系统', '网站名称', 'input', null, '99', null, '1', '1', '2026-01-01 00:00:00', '2026-04-23 21:36:24', null);
INSERT INTO `sa_system_config` VALUES ('5', '1', 'site_record_number', '9527', '网站备案号', 'input', null, '95', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('6', '2', 'upload_allow_file', 'txt,doc,docx,xls,xlsx,ppt,pptx,rar,zip,7z,gz,pdf,wps,md,jpg,png,jpeg,mp4,pem,crt', '文件类型', 'input', null, '0', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('7', '2', 'upload_allow_image', 'jpg,jpeg,png,gif,svg,bmp', '图片类型', 'input', null, '0', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('8', '2', 'upload_mode', '1', '上传模式', 'select', '[{\"label\":\"本地上传\",\"value\":\"1\"},{\"label\":\"阿里云OSS\",\"value\":\"2\"},{\"label\":\"七牛云\",\"value\":\"3\"},{\"label\":\"腾讯云COS\",\"value\":\"4\"},{\"label\":\"亚马逊S3\",\"value\":\"5\"}]', '99', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('10', '2', 'upload_size', '52428800', '上传大小', 'input', null, '88', '单位Byte,1MB=1024*1024Byte', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('11', '2', 'local_root', 'public/storage/', '本地存储路径', 'input', null, '0', '本地存储文件路径', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('12', '2', 'local_domain', 'http://127.0.0.1:8000', '本地存储域名', 'input', null, '0', 'http://127.0.0.1:8787', '1', '1', '2026-01-01 00:00:00', '2026-03-22 20:07:02', null);
INSERT INTO `sa_system_config` VALUES ('13', '2', 'local_uri', '/storage/', '本地访问路径', 'input', null, '0', '访问是通过domain + uri', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('14', '2', 'qiniu_accessKey', '', '七牛key', 'input', null, '0', '七牛云存储secretId', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('15', '2', 'qiniu_secretKey', '', '七牛secret', 'input', null, '0', '七牛云存储secretKey', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('16', '2', 'qiniu_bucket', '', '七牛bucket', 'input', null, '0', '七牛云存储bucket', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('17', '2', 'qiniu_dirname', '', '七牛dirname', 'input', null, '0', '七牛云存储dirname', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('18', '2', 'qiniu_domain', '', '七牛domain', 'input', null, '0', '七牛云存储domain', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('19', '2', 'cos_secretId', '', '腾讯Id', 'input', null, '0', '腾讯云存储secretId', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('20', '2', 'cos_secretKey', '', '腾讯key', 'input', null, '0', '腾讯云secretKey', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('21', '2', 'cos_bucket', '', '腾讯bucket', 'input', null, '0', '腾讯云存储bucket', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('22', '2', 'cos_dirname', '', '腾讯dirname', 'input', null, '0', '腾讯云存储dirname', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('23', '2', 'cos_domain', '', '腾讯domain', 'input', null, '0', '腾讯云存储domain', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('24', '2', 'cos_region', '', '腾讯region', 'input', null, '0', '腾讯云存储region', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('25', '2', 'oss_accessKeyId', '', '阿里Id', 'input', null, '0', '阿里云存储accessKeyId', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('26', '2', 'oss_accessKeySecret', '', '阿里Secret', 'input', null, '0', '阿里云存储accessKeySecret', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('27', '2', 'oss_bucket', '', '阿里bucket', 'input', null, '0', '阿里云存储bucket', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('28', '2', 'oss_dirname', '', '阿里dirname', 'input', null, '0', '阿里云存储dirname', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('29', '2', 'oss_domain', '', '阿里domain', 'input', null, '0', '阿里云存储domain', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('30', '2', 'oss_endpoint', '', '阿里endpoint', 'input', null, '0', '阿里云存储endpoint', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('31', '3', 'Host', 'smtp.qq.com', 'SMTP服务器', 'input', '', '100', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('32', '3', 'Port', '465', 'SMTP端口', 'input', '', '100', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('33', '3', 'Username', '', 'SMTP用户名', 'input', '', '100', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('34', '3', 'Password', '', 'SMTP密码', 'input', '', '100', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('35', '3', 'SMTPSecure', 'ssl', 'SMTP验证方式', 'radio', '[\r\n    {\"label\":\"ssl\",\"value\":\"ssl\"},\r\n    {\"label\":\"tsl\",\"value\":\"tsl\"}\r\n]', '100', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('36', '3', 'From', '', '默认发件人', 'input', '', '100', '默认发件的邮箱地址', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('37', '3', 'FromName', '账户注册', '默认发件名称', 'input', '', '100', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('38', '3', 'CharSet', 'UTF-8', '编码', 'input', '', '100', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('39', '3', 'SMTPDebug', '1', '调试模式', 'radio', '[\r\n    {\"label\":\"关闭\",\"value\":\"0\"},\r\n    {\"label\":\"client\",\"value\":\"1\"},\r\n    {\"label\":\"server\",\"value\":\"2\"}\r\n]', '100', '', '1', '1', '2026-01-01 00:00:00', '2026-03-22 20:06:26', null);
INSERT INTO `sa_system_config` VALUES ('40', '2', 's3_key', '', 'key', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('41', '2', 's3_secret', '', 'secret', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('42', '2', 's3_bucket', '', 'bucket', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('43', '2', 's3_dirname', '', 'dirname', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('44', '2', 's3_domain', '', 'domain', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('45', '2', 's3_region', '', 'region', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('46', '2', 's3_version', '', 'version', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('47', '2', 's3_use_path_style_endpoint', '', 'path_style_endpoint', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('48', '2', 's3_endpoint', '', 'endpoint', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('49', '2', 's3_acl', '', 'acl', 'input', '', '0', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config` VALUES ('50', '1', 'ggg', '', 'ggg', 'uploadImage', null, '100', '', '1', '1', '2026-03-22 20:11:57', '2026-03-22 20:36:34', '2026-03-22 20:36:34');
INSERT INTO `sa_system_config` VALUES ('52', '1', 'Logo', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3acd6b581.21163358.png', 'Logo', 'uploadImage', null, '100', '', '1', '1', '2026-03-25 21:30:52', '2026-03-28 23:36:48', '2026-03-28 23:36:48');
INSERT INTO `sa_system_config` VALUES ('54', '1', 'file', '', 'file', 'uploadFile', '[]', '100', '', '1', '1', '2026-03-28 22:17:22', '2026-03-28 22:17:32', '2026-03-28 22:17:32');
INSERT INTO `sa_system_config` VALUES ('55', '1', 'file', '', 'file', 'uploadFile', '[]', '100', '', '1', '1', '2026-03-28 22:17:23', '2026-03-28 22:17:36', '2026-03-28 22:17:36');
INSERT INTO `sa_system_config` VALUES ('56', '1', 'file', '', 'file', 'uploadFile', '[]', '100', '', '1', '1', '2026-03-28 22:17:28', '2026-03-28 23:36:48', '2026-03-28 23:36:48');

-- ----------------------------
-- Table structure for `sa_system_config_group`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_config_group`;
CREATE TABLE `sa_system_config_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) DEFAULT NULL COMMENT '字典名称',
  `code` varchar(100) DEFAULT NULL COMMENT '字典标示',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建人',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新人',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='参数配置分组表';

-- ----------------------------
-- Records of sa_system_config_group
-- ----------------------------
INSERT INTO `sa_system_config_group` VALUES ('1', '站点配置', 'site_config', '111', '1', '1', '2026-01-01 00:00:00', '2026-03-31 00:31:21', null);
INSERT INTO `sa_system_config_group` VALUES ('2', '上传配置', 'upload_config', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config_group` VALUES ('3', '邮件服务', 'email_config', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_config_group` VALUES ('4', '333', '333', '', '1', '1', '2026-03-22 20:36:51', '2026-03-22 20:43:05', '2026-03-22 20:43:05');
INSERT INTO `sa_system_config_group` VALUES ('7', '325235', '235235', '', '1', '1', '2026-03-28 20:36:57', '2026-03-28 20:38:44', '2026-03-28 20:38:44');

-- ----------------------------
-- Table structure for `sa_system_dept`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_dept`;
CREATE TABLE `sa_system_dept` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT '0' COMMENT '父级ID，0为根节点',
  `name` varchar(64) NOT NULL COMMENT '部门名称',
  `code` varchar(64) DEFAULT NULL COMMENT '部门编码',
  `leader_id` bigint(20) unsigned DEFAULT NULL COMMENT '部门负责人ID',
  `level` varchar(255) DEFAULT '' COMMENT '祖级列表，格式: 0,1,5, (便于查询子孙节点)',
  `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '所属租户ID，0表示系统级',
  `sort` int(11) DEFAULT '0' COMMENT '排序，数字越小越靠前',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态: 1启用, 0禁用',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_parent_id` (`parent_id`) USING BTREE,
  KEY `idx_path` (`level`) USING BTREE,
  KEY `idx_tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='部门表';

-- ----------------------------
-- Records of sa_system_dept
-- ----------------------------
INSERT INTO `sa_system_dept` VALUES ('1', '0', '腾讯集团', 'GROUP', '100', '0,', '1', '0', '1', '00', '1', '1', '2026-01-01 00:00:00', '2026-03-23 23:27:09', null);
INSERT INTO `sa_system_dept` VALUES ('2', '0', '总办', 'GMO', '0', '0,1,', '1', '1', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-03-22 11:20:13', null);
INSERT INTO `sa_system_dept` VALUES ('5', '0', '技术部A', null, null, '', '1', '0', '1', null, null, null, '2026-04-24 12:34:04', '2026-04-24 12:34:04', null);
INSERT INTO `sa_system_dept` VALUES ('8', '0', '销售部B', null, null, '', '2', '0', '1', null, null, null, '2026-04-24 12:34:04', '2026-04-24 12:34:04', null);
INSERT INTO `sa_system_dept` VALUES ('10', '0', '微信事业群', 'WXG', '0', '0,1,', '1', '1', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-03-22 11:38:29', null);
INSERT INTO `sa_system_dept` VALUES ('11', '0', '互动娱乐事业群', 'IEG', '0', '0,1,', '1', '1', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-03-22 11:39:34', null);
INSERT INTO `sa_system_dept` VALUES ('12', '1', '云与智慧产业事业群', 'CSIG', '10', '0,1,', '1', '400', '1', '122', '1', '1', '2026-01-01 00:00:00', '2026-03-31 00:40:35', null);
INSERT INTO `sa_system_dept` VALUES ('100', '0', '测试部门', null, null, '', '10', '0', '1', null, null, null, '2026-04-24 12:40:17', '2026-04-24 12:40:17', null);
INSERT INTO `sa_system_dept` VALUES ('101', '10', '微信基础产品部', 'WX_BASE', null, '0,1,10,', '1', '100', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dept` VALUES ('102', '10', '微信支付线', 'WX_PAY', null, '0,1,10,', '1', '200', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-03-22 11:37:14', null);
INSERT INTO `sa_system_dept` VALUES ('110', '0', '父部门', null, null, '', '10', '0', '1', null, null, null, '2026-04-24 12:40:19', '2026-04-24 12:40:19', null);
INSERT INTO `sa_system_dept` VALUES ('111', '11', '天美工作室群', 'TIMI', null, '0,1,11,', '1', '100', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dept` VALUES ('112', '11', '光子工作室群', 'LIGHT', null, '0,1,11,', '1', '200', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-03-22 22:00:41', null);
INSERT INTO `sa_system_dept` VALUES ('120', '0', '权限测试部门', null, null, '', '10', '0', '1', null, null, null, '2026-04-24 12:40:19', '2026-04-24 12:40:19', null);
INSERT INTO `sa_system_dept` VALUES ('121', '12', '腾讯云事业部1', 'CLOUD', '5', '0,1,12,', '1', '100', '1', '111', '1', '1', '2026-01-01 00:00:00', '2026-03-22 22:44:34', null);
INSERT INTO `sa_system_dept` VALUES ('122', '111', '王者荣耀项目组', 'HOK', '101', '0,1,11,111,', '1', '100', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-03-22 13:01:35', null);
INSERT INTO `sa_system_dept` VALUES ('123', '111', 'QQ飞车项目组', 'QQ_SPEED', '5', '0,1,11,111,', '0', '200', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dept` VALUES ('127', '0', '111233', '11111', '100', '0,', '1', '100', '1', '11', '1', '1', '2026-03-22 12:06:27', '2026-03-31 19:30:50', '2026-03-31 19:30:50');
INSERT INTO `sa_system_dept` VALUES ('128', '0', '11', '111', '10', '0,', '0', '100', '1', '', '1', '1', '2026-03-22 12:27:44', '2026-03-22 12:48:51', null);
INSERT INTO `sa_system_dept` VALUES ('129', '0', '5555', 'aa', '0', '0,', '2', '100', '1', '', '100', '10', '2026-04-19 00:30:03', '2026-04-25 11:46:18', null);

-- ----------------------------
-- Table structure for `sa_system_dict_data`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_dict_data`;
CREATE TABLE `sa_system_dict_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type_id` int(11) unsigned DEFAULT NULL COMMENT '字典类型ID',
  `label` varchar(50) DEFAULT NULL COMMENT '字典标签',
  `value` varchar(100) DEFAULT NULL COMMENT '字典值',
  `color` varchar(50) DEFAULT NULL COMMENT '字典颜色',
  `code` varchar(100) DEFAULT NULL COMMENT '字典标示',
  `sort` smallint(5) unsigned DEFAULT '0' COMMENT '排序',
  `status` smallint(6) DEFAULT '1' COMMENT '状态 (1正常 2停用)',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `type_id` (`type_id`) USING BTREE,
  KEY `idx_code` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='字典数据表';

-- ----------------------------
-- Records of sa_system_dict_data
-- ----------------------------
INSERT INTO `sa_system_dict_data` VALUES ('2', '2', '本地存储', '1', '#60c041', 'upload_mode', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-23 23:00:39', null);
INSERT INTO `sa_system_dict_data` VALUES ('3', '2', '阿里云OSS', '2', '#f9901f', 'upload_mode', '98', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('4', '2', '七牛云', '3', '#00ced1', 'upload_mode', '97', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('5', '2', '腾讯云COS', '4', '#1d84ff', 'upload_mode', '96', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('6', '2', '亚马逊S3', '5', '#b48df3', 'upload_mode', '95', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-23 21:19:51', null);
INSERT INTO `sa_system_dict_data` VALUES ('7', '3', '正常', '1', '#60c041', 'data_status', '0', '1', '1为正常', '1', '1', '2026-01-01 00:00:00', '2026-03-24 23:16:13', null);
INSERT INTO `sa_system_dict_data` VALUES ('8', '3', '停用', '0', '#ff4d4f', 'data_status', '0', '1', '0为停用1', '1', '1', '2026-01-01 00:00:00', '2026-03-28 17:53:02', null);
INSERT INTO `sa_system_dict_data` VALUES ('9', '4', '统计页面', 'statistics', '#00ced1', 'dashboard', '100', '1', '管理员用', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('10', '4', '工作台', 'work', '#ff8c00', 'dashboard', '50', '1', '员工使用', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('11', '5', '男', '1', '#5d87ff', 'gender', '0', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('12', '5', '女', '2', '#ff4500', 'gender', '0', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('13', '5', '未知', '3', '#b48df3', 'gender', '0', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('16', '12', '图片', 'image', '#60c041', 'attachment_type', '10', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('17', '12', '文档', 'text', '#1d84ff', 'attachment_type', '9', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('18', '12', '音频', 'audio', '#00ced1', 'attachment_type', '8', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('19', '12', '视频', 'video', '#ff4500', 'attachment_type', '7', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('20', '12', '应用程序', 'application', '#ff8c00', 'attachment_type', '6', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('21', '13', '目录', '1', '#909399', 'menu_type', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('22', '13', '菜单', '2', '#1e90ff', 'menu_type', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('23', '13', '按钮', '3', '#ff4500', 'menu_type', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('24', '13', '外链', '4', '#00ced1', 'menu_type', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('25', '14', '是', '1', '#60c041', 'yes_or_no', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('26', '14', '否', '0', '#ff4500', 'yes_or_no', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-23 21:16:53', null);
INSERT INTO `sa_system_dict_data` VALUES ('47', '20', 'URL任务GET', '1', '#5d87ff', 'crontab_task_type', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('48', '20', 'URL任务POST', '2', '#00ced1', 'crontab_task_type', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-28 23:11:23', null);
INSERT INTO `sa_system_dict_data` VALUES ('49', '20', '类任务', '3', '#ff8c00', 'crontab_task_type', '100', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_data` VALUES ('50', '5', 'renn', '4', '#5d87ff', null, '100', '1', '', '1', '1', '2026-03-23 21:20:40', '2026-03-23 21:20:44', '2026-03-23 21:20:44');
INSERT INTO `sa_system_dict_data` VALUES ('51', '5', '11', '111', '#5d87ff', null, '100', '0', '', '1', '1', '2026-03-23 21:20:49', '2026-03-23 21:24:19', '2026-03-23 21:24:19');
INSERT INTO `sa_system_dict_data` VALUES ('52', '5', '11', '123', '#5d87ff', null, '100', '1', '', '1', '1', '2026-03-23 21:24:27', '2026-03-23 21:24:30', '2026-03-23 21:24:30');

-- ----------------------------
-- Table structure for `sa_system_dict_type`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_dict_type`;
CREATE TABLE `sa_system_dict_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) DEFAULT NULL COMMENT '字典名称',
  `code` varchar(100) DEFAULT NULL COMMENT '字典标示',
  `status` smallint(6) DEFAULT '1' COMMENT '状态 (1正常 2停用)',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_code` (`code`) USING BTREE,
  KEY `idx_name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='字典类型表';

-- ----------------------------
-- Records of sa_system_dict_type
-- ----------------------------
INSERT INTO `sa_system_dict_type` VALUES ('2', '存储模式', 'upload_mode', '1', '上传文件存储模式111', '1', '1', '2026-01-01 00:00:00', '2026-03-23 21:19:39', null);
INSERT INTO `sa_system_dict_type` VALUES ('3', '数据状态', 'data_status', '1', '通用数据状态', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_type` VALUES ('4', '后台首页', 'dashboard', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_type` VALUES ('5', '性别', 'gender', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-23 21:24:56', null);
INSERT INTO `sa_system_dict_type` VALUES ('12', '附件类型', 'attachment_type', '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_type` VALUES ('13', '菜单类型', 'menu_type', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_dict_type` VALUES ('14', '是否', 'yes_or_no', '1', '11', '1', '1', '2026-01-01 00:00:00', '2026-03-28 18:03:01', null);
INSERT INTO `sa_system_dict_type` VALUES ('20', '定时任务类型', 'crontab_task_type', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-31 00:30:35', null);
INSERT INTO `sa_system_dict_type` VALUES ('21', '111', '111', '1', '', '1', '1', '2026-03-23 21:25:10', '2026-03-23 22:48:47', '2026-03-23 22:48:47');

-- ----------------------------
-- Table structure for `sa_system_mail`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_mail`;
CREATE TABLE `sa_system_mail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `gateway` varchar(50) DEFAULT NULL COMMENT '网关',
  `from` varchar(50) DEFAULT NULL COMMENT '发送人',
  `email` varchar(50) DEFAULT NULL COMMENT '接收人',
  `code` varchar(20) DEFAULT NULL COMMENT '验证码',
  `content` varchar(500) DEFAULT NULL COMMENT '邮箱内容',
  `status` varchar(20) DEFAULT NULL COMMENT '发送状态',
  `response` varchar(500) DEFAULT NULL COMMENT '返回结果',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_create_time` (`create_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='邮件记录';

-- ----------------------------
-- Records of sa_system_mail
-- ----------------------------
INSERT INTO `sa_system_mail` VALUES ('1', 'mail.163.com', 'test@qq.com', 'admin@qq.com', '869', 'hello', 'success', 'data', '2026-03-23 19:53:53', null, null);
INSERT INTO `sa_system_mail` VALUES ('2', 'mail.qq.com', 'admin@test.com', 'admin@qq.com', '456', 'test', 'failure', 'data', '2026-03-23 19:52:49', '2026-03-28 18:41:38', null);

-- ----------------------------
-- Table structure for `sa_system_menu`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_menu`;
CREATE TABLE `sa_system_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT '0' COMMENT '父级ID',
  `name` varchar(64) NOT NULL COMMENT '菜单名称',
  `code` varchar(64) DEFAULT NULL COMMENT '组件名称',
  `slug` varchar(100) DEFAULT NULL COMMENT '权限标识，如 user:list, user:add',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型: 1目录, 2菜单, 3按钮/API',
  `path` varchar(255) DEFAULT NULL COMMENT '路由地址(前端)或API路径(后端)',
  `component` varchar(255) DEFAULT NULL COMMENT '前端组件路径，如 layout/User',
  `method` varchar(10) DEFAULT NULL COMMENT '请求方式',
  `icon` varchar(64) DEFAULT NULL COMMENT '图标',
  `sort` int(11) DEFAULT '100' COMMENT '排序',
  `link_url` varchar(255) DEFAULT NULL COMMENT '外部链接',
  `is_iframe` tinyint(1) DEFAULT '2' COMMENT '是否iframe',
  `is_keep_alive` tinyint(1) DEFAULT '2' COMMENT '是否缓存',
  `is_hidden` tinyint(1) DEFAULT '2' COMMENT '是否隐藏',
  `is_fixed_tab` tinyint(1) DEFAULT '2' COMMENT '是否固定标签页',
  `is_full_page` tinyint(1) DEFAULT '2' COMMENT '是否全屏',
  `generate_id` int(11) DEFAULT '0' COMMENT '生成id',
  `generate_key` varchar(255) DEFAULT NULL COMMENT '生成key',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `remark` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_parent_id` (`parent_id`) USING BTREE,
  KEY `idx_slug` (`slug`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='菜单权限表';

-- ----------------------------
-- Records of sa_system_menu
-- ----------------------------
INSERT INTO `sa_system_menu` VALUES ('1', '0', '仪表盘', 'Dashboard', null, '1', '/dashboard', '', null, 'ri:pie-chart-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('2', '1', '工作台', 'Console', '', '2', 'console', '/dashboard/console', null, 'ri:home-smile-2-line', '100', '', '2', '2', '2', '1', '2', '0', null, '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-24 22:06:42', null);
INSERT INTO `sa_system_menu` VALUES ('3', '0', '系统管理', 'System', null, '1', '/system', '', null, 'ri:user-3-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('4', '3', '用户管理', 'User', null, '2', 'user', '/system/user', null, 'ri:user-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('5', '3', '部门管理', 'Dept', null, '2', 'dept', '/system/dept', null, 'ri:node-tree', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('6', '3', '角色管理', 'Role', null, '2', 'role', '/system/role', null, 'ri:admin-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('7', '3', '岗位管理', 'Post', '', '2', 'post', '/system/post', null, 'ri:signpost-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('8', '3', '菜单管理', 'Menu', null, '2', 'menu', '/system/menu', null, 'ri:menu-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('10', '0', '运维管理', 'Safeguard', null, '1', '/safeguard', '', null, 'ri:shield-check-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('11', '10', '缓存管理', 'Cache', '', '2', 'cache', '/safeguard/cache', null, 'ri:keyboard-box-line', '80', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('12', '10', '数据字典', 'Dict', null, '2', 'dict', '/safeguard/dict', null, 'ri:database-2-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('13', '10', '附件管理', 'Attachment', '', '2', 'attachment', '/safeguard/attachment', null, 'ri:file-cloud-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('14', '10', '数据表维护', 'Database', '', '2', 'database', '/safeguard/database', null, 'ri:database-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('15', '10', '登录日志', 'LoginLog', '', '2', 'login-log', '/safeguard/login-log', null, 'ri:login-circle-line', '50', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('16', '10', '操作日志', 'OperLog', '', '2', 'oper-log', '/safeguard/oper-log', null, 'ri:shield-keyhole-line', '50', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('17', '10', '邮件日志', 'EmailLog', '', '2', 'email-log', '/safeguard/email-log', null, 'ri:mail-line', '50', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('18', '3', '系统设置', 'Config', null, '2', 'config', '/system/config', null, 'ri:settings-4-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('19', '0', '官方文档', 'Document', '', '4', '', '', null, 'ri:file-copy-2-fill', '101', 'https://saithink.top', '1', '2', '2', '2', '2', '0', null, '0', '', '1', '1', '2026-01-01 00:00:00', '2026-03-29 20:40:13', null);
INSERT INTO `sa_system_menu` VALUES ('20', '4', '数据列表', '', 'core:user:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('21', '1', '个人中心', 'UserCenter', '', '2', 'user-center', '/dashboard/user-center/index', null, 'ri:user-2-line', '100', '', '2', '2', '1', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('22', '4', '添加', '', 'core:user:save', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('23', '4', '修改', '', 'core:user:update', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('24', '4', '读取', '', 'core:user:read', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('25', '4', '删除', '', 'core:user:destroy', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('26', '4', '重置密码', '', 'core:user:password', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('27', '4', '清理缓存', '', 'core:user:cache', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('28', '4', '设置工作台', '', 'core:user:home', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('29', '5', '数据列表', '', 'core:dept:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('30', '5', '添加', '', 'core:dept:save', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('31', '5', '修改', '', 'core:dept:update', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('32', '5', '读取', '', 'core:dept:read', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('33', '5', '删除', '', 'core:dept:destroy', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('34', '6', '添加', '', 'core:role:save', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('35', '6', '数据列表', '', 'core:role:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('36', '6', '修改', '', 'core:role:update', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('37', '6', '读取', '', 'core:role:read', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('38', '6', '删除', '', 'core:role:destroy', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('39', '6', '菜单权限', '', 'core:role:menu', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('41', '7', '数据列表', '', 'core:post:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('42', '7', '添加', '', 'core:post:save', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('43', '7', '修改', '', 'core:post:update', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('44', '7', '读取', '', 'core:post:read', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('45', '7', '删除', '', 'core:post:destroy', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('46', '7', '导入', '', 'core:post:import', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('47', '7', '导出', '', 'core:post:export', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('48', '8', '数据列表', '', 'core:menu:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('49', '8', '读取', '', 'core:menu:read', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('50', '8', '添加', '', 'core:menu:save', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('51', '8', '修改', '', 'core:menu:update', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('52', '8', '删除', '', 'core:menu:destroy', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('53', '18', '数据列表', '', 'core:config:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('54', '18', '管理', '', 'core:config:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('55', '18', '修改', '', 'core:config:update', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('56', '12', '数据列表', '', 'core:dict:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('57', '12', '管理', '', 'core:dict:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('58', '13', '数据列表', '', 'core:attachment:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('59', '13', '管理', '', 'core:attachment:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('60', '14', '数据表列表', '', 'core:database:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('61', '14', '数据表维护', '', 'core:database:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('62', '14', '回收站数据', '', 'core:recycle:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('63', '14', '回收站管理', '', 'core:recycle:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('64', '15', '数据列表', '', 'core:logs:login', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('65', '15', '删除', '', 'core:logs:deleteLogin', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('66', '16', '数据列表', '', 'core:logs:Oper', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('67', '16', '删除', '', 'core:logs:deleteOper', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('68', '17', '数据列表', '', 'core:email:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('69', '17', '删除', '', 'core:email:destroy', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('70', '10', '服务监控', 'Server', '', '2', 'server', '/safeguard/server', null, 'ri:server-line', '90', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('71', '70', '数据列表', '', 'core:server:monitor', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('72', '11', '数据列表', '', 'core:server:cache', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('73', '11', '缓存清理', '', 'core:server:clear', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('74', '2', '登录数据统计', '', 'core:console:list', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('75', '0', '附加权限', 'Permission', '', '1', 'permission', '', null, 'ri:apps-2-ai-line', '100', '', '2', '2', '1', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('76', '75', '上传图片', '', 'core:system:uploadImage', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('77', '75', '上传文件', '', 'core:system:uploadFile', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('78', '75', '附件列表', '', 'core:system:resource', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('79', '75', '用户列表', '', 'core:system:user', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('80', '0', '开发工具', 'Tool', '', '1', '/tool', '', null, 'ri:tools-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-23 23:03:00', null);
INSERT INTO `sa_system_menu` VALUES ('81', '80', '代码生成', 'Code', '', '2', 'code', '/tool/code', null, 'ri:code-s-slash-line', '100', '', '2', '2', '2', '2', '2', '0', null, '0', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('82', '80', '定时任务', 'Crontab', '', '2', 'crontab', '/tool/crontab', null, 'ri:time-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('83', '82', '数据列表', '', 'tool:crontab:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('84', '82', '管理', '', 'tool:crontab:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('85', '82', '运行任务', '', 'tool:crontab:run', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('86', '81', '数据列表', '', 'tool:code:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('87', '81', '管理', '', 'tool:code:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('88', '0', '插件市场', 'Plugin', '', '2', '/plugin', '/plugin/saipackage/install/index', null, 'ri:apps-2-ai-line', '100', '', '2', '2', '2', '2', '2', '0', null, '0', '', '1', '1', '2026-01-01 00:00:00', '2026-03-29 20:40:18', null);
INSERT INTO `sa_system_menu` VALUES ('92', '4', '分配菜单', '', 'core:user:menu', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '0', '', '1', '1', '2026-03-24 22:56:49', '2026-03-24 22:56:49', null);
INSERT INTO `sa_system_menu` VALUES ('93', '1', '分析页', 'Analysis', '', '2', 'analysis', '/dashboard/analysis', null, 'ri:file-music-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-03-25 20:28:34', '2026-03-29 14:28:06', null);
INSERT INTO `sa_system_menu` VALUES ('94', '1', '电子商务', 'Ecommerce', '', '2', 'ecommerce', '/dashboard/ecommerce', null, 'ri:bootstrap-fill', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-03-25 20:42:29', '2026-03-29 14:39:56', null);
INSERT INTO `sa_system_menu` VALUES ('95', '80', '表单示例', 'Form', '', '2', 'form', '/tool/form', null, 'ri:article-line', '100', '', '2', '2', '2', '2', '2', '0', null, '0', '', '1', '1', '2026-03-25 20:47:03', '2026-03-25 20:47:44', null);
INSERT INTO `sa_system_menu` VALUES ('96', '88', '111', null, 'chajian:market:add', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-03-28 19:41:47', '2026-03-28 20:05:26', '2026-03-28 20:05:26');
INSERT INTO `sa_system_menu` VALUES ('97', '5', '菜单树', null, 'core:dept:tree', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-03-29 18:03:52', '2026-03-29 18:03:52', null);
INSERT INTO `sa_system_menu` VALUES ('98', '3', '租户管理', 'Tenant', '', '2', 'tenant', '/system/tenant', null, 'ri:dashboard-horizontal-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-06 21:24:21', '2026-04-06 21:36:53', null);
INSERT INTO `sa_system_menu` VALUES ('99', '98', '数据列表', null, 'core:tenant:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-06 21:30:15', '2026-04-06 21:30:15', null);
INSERT INTO `sa_system_menu` VALUES ('100', '98', '读取', '', 'core:tenant:read', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-06 22:00:15', '2026-04-06 22:00:15', null);
INSERT INTO `sa_system_menu` VALUES ('101', '98', '添加', '', 'core:tenant:save', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-06 22:01:03', '2026-04-06 22:01:03', null);
INSERT INTO `sa_system_menu` VALUES ('102', '98', '修改', '', 'core:tenant:update', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-06 22:01:24', '2026-04-06 22:01:24', null);
INSERT INTO `sa_system_menu` VALUES ('103', '98', '删除', '', 'core:tenant:destroy', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-06 22:01:53', '2026-04-06 22:01:53', null);
INSERT INTO `sa_system_menu` VALUES ('104', '10', 'Redis监控', 'Redis', 'core:server:redis', '2', 'redis', '/safeguard/redis', null, 'ri:exchange-cny-fill', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-20 22:44:29', '2026-04-21 00:33:07', null);
INSERT INTO `sa_system_menu` VALUES ('105', '0', '文章管理', 'Article', '', '1', 'article', '', null, 'ri:book-line', '100', '', '2', '2', '2', '2', '2', '0', null, '0', '', '1', '1', '2026-04-23 22:18:18', '2026-04-23 22:18:18', null);
INSERT INTO `sa_system_menu` VALUES ('106', '105', '文章列表', 'ArticleList', '', '2', '/article/index', '/article', null, 'ri:code-block', '100', '', '2', '2', '2', '2', '2', '0', null, '0', '', '1', '1', '2026-04-23 22:19:39', '2026-04-23 22:34:28', null);

-- ----------------------------
-- Table structure for `sa_system_post`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_post`;
CREATE TABLE `sa_system_post` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) DEFAULT NULL COMMENT '岗位名称',
  `code` varchar(100) DEFAULT NULL COMMENT '岗位代码',
  `sort` smallint(5) unsigned DEFAULT '0' COMMENT '排序',
  `status` smallint(6) DEFAULT '1' COMMENT '状态 (1正常 2停用)',
  `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '所属租户ID，0表示系统级',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='岗位信息表';

-- ----------------------------
-- Records of sa_system_post
-- ----------------------------
INSERT INTO `sa_system_post` VALUES ('1', '司机岗', 'driver', '100', '1', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-31 19:21:16', null);
INSERT INTO `sa_system_post` VALUES ('2', '保安岗', 'security', '100', '1', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-21 23:02:35', null);
INSERT INTO `sa_system_post` VALUES ('3', '11', '11', '100', '1', '1', '1233', '1', '1', '2026-03-22 12:12:55', '2026-03-22 12:13:12', '2026-03-22 12:13:12');
INSERT INTO `sa_system_post` VALUES ('4', '111', '111', '100', '2', '1', '11111', '1', '1', '2026-03-22 12:47:53', '2026-03-22 13:33:02', '2026-03-22 13:33:02');
INSERT INTO `sa_system_post` VALUES ('5', 'aa', 'aaa', '100', '1', '1', '', '1', '1', '2026-03-27 21:45:50', '2026-03-28 19:08:12', '2026-03-28 19:08:12');
INSERT INTO `sa_system_post` VALUES ('6', '111', '11111', '100', '1', '1', '', '1', '1', '2026-03-28 19:32:14', '2026-03-28 19:32:23', '2026-03-28 19:32:23');

-- ----------------------------
-- Table structure for `sa_system_role`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_role`;
CREATE TABLE `sa_system_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父角色ID，0表示顶级角色',
  `name` varchar(64) NOT NULL COMMENT '角色名称',
  `code` varchar(64) NOT NULL COMMENT '角色标识(英文唯一)，如: hr_manager',
  `level` int(11) DEFAULT '1' COMMENT '角色级别(1-100)：用于行政控制，不可操作级别>=自己的角色',
  `data_scope` tinyint(4) DEFAULT '1' COMMENT '数据范围: 1全部, 2本部门及下属, 3本部门, 4仅本人, 5自定义',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `sort` int(11) DEFAULT '100',
  `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '所属租户ID，0表示系统级',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态: 1启用, 0禁用',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_slug` (`code`) USING BTREE,
  KEY `idx_tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='角色表';

-- ----------------------------
-- Records of sa_system_role
-- ----------------------------
INSERT INTO `sa_system_role` VALUES ('1', '0', '超级管理员', 'super_admin', '100', '1', '系统维护者，拥有所有权限', '100', '1', '1', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_role` VALUES ('2', '0', '集团总裁', 'ceo', '1', '5', '查看全集团数据11', '100', '1', '1', '1', '1', '2026-01-01 00:00:00', '2026-04-24 22:36:43', null);
INSERT INTO `sa_system_role` VALUES ('3', '0', 'BG总裁', 'bg_president', '1', '2', '', '100', '1', '1', '1', '1', '2026-01-01 00:00:00', '2026-03-22 18:19:47', null);
INSERT INTO `sa_system_role` VALUES ('4', '0', '部门总经理1', 'gm', '60', '2', '', '100', '1', '1', '1', '1', '2026-01-01 00:00:00', '2026-03-22 12:48:24', null);
INSERT INTO `sa_system_role` VALUES ('5', '0', '组长', 'team_leader', '30', '3', '', '100', '1', '1', '1', '1', '2026-01-01 00:00:00', '2026-03-21 23:24:29', null);
INSERT INTO `sa_system_role` VALUES ('6', '0', '普通员工', 'staff', '10', '6', '自定义部门权限', '100', '1', '1', '1', '1', '2026-01-01 00:00:00', '2026-04-25 09:25:14', null);
INSERT INTO `sa_system_role` VALUES ('7', '0', '111', '11', '1', '1', '11', '100', '1', '1', '1', '1', '2026-03-22 20:38:38', '2026-03-22 20:38:41', '2026-03-22 20:38:41');
INSERT INTO `sa_system_role` VALUES ('8', '0', 'ad', 'dddd', '1', '6', '', '100', '1', '1', '1', '1', '2026-03-27 21:45:30', '2026-03-29 10:48:38', null);
INSERT INTO `sa_system_role` VALUES ('204', '0', '集团CEO', 'JTCEO', '1', '1', '', '100', '2', '1', '1', '1', '2026-04-24 21:46:40', '2026-04-24 21:46:40', null);

-- ----------------------------
-- Table structure for `sa_system_role_dept`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_role_dept`;
CREATE TABLE `sa_system_role_dept` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `dept_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_role_id` (`role_id`) USING BTREE,
  KEY `idx_dept_id` (`dept_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='角色-自定义数据权限关联';

-- ----------------------------
-- Records of sa_system_role_dept
-- ----------------------------
INSERT INTO `sa_system_role_dept` VALUES ('17', '8', '10');
INSERT INTO `sa_system_role_dept` VALUES ('18', '8', '101');
INSERT INTO `sa_system_role_dept` VALUES ('19', '6', '2');
INSERT INTO `sa_system_role_dept` VALUES ('20', '6', '10');
INSERT INTO `sa_system_role_dept` VALUES ('21', '6', '101');

-- ----------------------------
-- Table structure for `sa_system_role_menu`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_role_menu`;
CREATE TABLE `sa_system_role_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `menu_id` bigint(20) unsigned NOT NULL,
  `tenant_id` bigint(20) unsigned DEFAULT '0' COMMENT '租户id',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `idx_role_menu_tenant` (`role_id`,`menu_id`,`tenant_id`) USING BTREE,
  KEY `idx_menu_id` (`menu_id`) USING BTREE,
  KEY `idx_role_id` (`role_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=519 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='角色权限关联';

-- ----------------------------
-- Records of sa_system_role_menu
-- ----------------------------
INSERT INTO `sa_system_role_menu` VALUES ('322', '3', '1', '1', '1', '1', '2026-03-28 20:09:15', '2026-03-28 20:09:15', null);
INSERT INTO `sa_system_role_menu` VALUES ('323', '3', '2', '1', '1', '1', '2026-03-28 20:09:15', '2026-03-28 20:09:15', null);
INSERT INTO `sa_system_role_menu` VALUES ('324', '3', '74', '1', '1', '1', '2026-03-28 20:09:15', '2026-03-28 20:09:15', null);
INSERT INTO `sa_system_role_menu` VALUES ('325', '3', '21', '1', '1', '1', '2026-03-28 20:09:15', '2026-03-28 20:09:15', null);
INSERT INTO `sa_system_role_menu` VALUES ('326', '3', '93', '1', '1', '1', '2026-03-28 20:09:15', '2026-03-28 20:09:15', null);
INSERT INTO `sa_system_role_menu` VALUES ('327', '3', '94', '1', '1', '1', '2026-03-28 20:09:15', '2026-03-28 20:09:15', null);
INSERT INTO `sa_system_role_menu` VALUES ('402', '204', '10', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('403', '204', '15', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('404', '204', '64', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('405', '204', '65', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('406', '204', '16', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('407', '204', '66', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('408', '204', '67', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('409', '204', '17', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('410', '204', '68', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('411', '204', '69', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('412', '204', '11', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('413', '204', '72', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('414', '204', '73', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('415', '204', '70', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('416', '204', '71', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('417', '204', '12', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('418', '204', '56', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('419', '204', '57', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('420', '204', '13', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('421', '204', '58', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('422', '204', '59', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('423', '204', '14', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('424', '204', '60', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('425', '204', '61', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('426', '204', '62', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('427', '204', '63', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('428', '204', '104', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('429', '204', '105', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('430', '204', '106', '1', '1', '1', '2026-04-24 23:01:03', '2026-04-24 23:01:03', null);
INSERT INTO `sa_system_role_menu` VALUES ('431', '6', '105', '1', '1', '1', '2026-04-25 09:25:26', '2026-04-25 09:25:26', null);
INSERT INTO `sa_system_role_menu` VALUES ('432', '6', '106', '1', '1', '1', '2026-04-25 09:25:26', '2026-04-25 09:25:26', null);
INSERT INTO `sa_system_role_menu` VALUES ('497', '2', '2', '1', '1', '1', '2026-04-25 10:08:16', '2026-04-25 10:08:16', null);
INSERT INTO `sa_system_role_menu` VALUES ('498', '2', '1', '1', '1', '1', '2026-04-25 10:08:16', '2026-04-25 10:08:16', null);
INSERT INTO `sa_system_role_menu` VALUES ('499', '2', '74', '1', '1', '1', '2026-04-25 10:08:16', '2026-04-25 10:08:16', null);
INSERT INTO `sa_system_role_menu` VALUES ('500', '2', '21', '1', '1', '1', '2026-04-25 10:08:16', '2026-04-25 10:08:16', null);
INSERT INTO `sa_system_role_menu` VALUES ('501', '2', '105', '1', '1', '1', '2026-04-25 10:08:16', '2026-04-25 10:08:16', null);
INSERT INTO `sa_system_role_menu` VALUES ('502', '2', '106', '1', '1', '1', '2026-04-25 10:08:16', '2026-04-25 10:08:16', null);
INSERT INTO `sa_system_role_menu` VALUES ('503', '204', '1', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('504', '204', '2', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('505', '204', '74', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('506', '204', '21', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('507', '204', '93', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('508', '204', '94', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('509', '204', '80', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('510', '204', '81', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('511', '204', '86', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('512', '204', '87', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('513', '204', '82', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('514', '204', '83', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('515', '204', '84', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('516', '204', '85', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('517', '204', '105', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);
INSERT INTO `sa_system_role_menu` VALUES ('518', '204', '106', '2', '1', '1', '2026-04-25 17:13:02', '2026-04-25 17:13:02', null);

-- ----------------------------
-- Table structure for `sa_system_tenant`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_tenant`;
CREATE TABLE `sa_system_tenant` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '租户ID',
  `tenant_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '租户名称',
  `tenant_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '租户编码（唯一）',
  `contact_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '联系人姓名',
  `contact_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '联系人电话',
  `contact_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '联系人邮箱',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '租户地址',
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '租户Logo URL',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0=禁用 1=启用',
  `expire_time` timestamp NULL DEFAULT NULL COMMENT '过期时间',
  `max_users` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大用户数，0=无限制',
  `max_depts` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大部门数，0=无限制',
  `max_roles` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大角色数，0=无限制',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `created_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `updated_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新人ID',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_code` (`tenant_code`),
  KEY `idx_status` (`status`),
  KEY `idx_expire_time` (`expire_time`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户信息表';

-- ----------------------------
-- Records of sa_system_tenant
-- ----------------------------
INSERT INTO `sa_system_tenant` VALUES ('1', '租户1', 'Tenant1', '张三丰', '88888888', 'admin@qq.com', '测试地址', '', '1', null, '0', '0', '0', '', '0', '100', null, '2026-04-22 22:34:39', null);
INSERT INTO `sa_system_tenant` VALUES ('2', '租户2', '88888', null, null, null, null, null, '1', null, '0', '0', '0', null, '1', '100', '2026-04-06 22:21:00', '2026-04-22 22:34:31', null);
INSERT INTO `sa_system_tenant` VALUES ('3', '租户3', '8888', null, null, null, null, null, '0', null, '0', '0', '0', null, '1', '1', '2026-04-06 22:31:31', '2026-04-19 00:23:16', null);
INSERT INTO `sa_system_tenant` VALUES ('10', '单租户测试', 'tenant_10', null, null, null, null, null, '1', null, '0', '0', '0', null, '0', '0', '2026-04-24 12:40:17', '2026-04-24 12:40:17', null);

-- ----------------------------
-- Table structure for `sa_system_user`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_user`;
CREATE TABLE `sa_system_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL COMMENT '登录账号',
  `password` varchar(255) NOT NULL COMMENT '加密密码',
  `realname` varchar(64) DEFAULT NULL COMMENT '真实姓名',
  `gender` varchar(10) DEFAULT NULL COMMENT '性别',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `email` varchar(128) DEFAULT NULL COMMENT '邮箱',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `signed` varchar(255) DEFAULT NULL COMMENT '个性签名',
  `dashboard` varchar(255) DEFAULT 'work' COMMENT '工作台',
  `dept_id` bigint(20) unsigned DEFAULT NULL COMMENT '主归属部门',
  `is_super` tinyint(1) DEFAULT '0' COMMENT '是否超级管理员: 1是(跳过权限检查), 0否',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态: 1启用, 0禁用',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `login_time` timestamp NULL DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(45) DEFAULT NULL COMMENT '最后登录IP',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uk_username` (`username`) USING BTREE,
  KEY `idx_dept_id` (`dept_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户表';

-- ----------------------------
-- Records of sa_system_user
-- ----------------------------
INSERT INTO `sa_system_user` VALUES ('1', 'admin', '$2y$10$wnixh48uDnaW/6D9EygDd.OHJK0vQY/4nHaTjMKBCVDBP2NiTatqS', '冷月如霜', '1', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a15ee50ff0.57272058.jpg', 'fssphp@admin.com', '15888888888', 'FSSADMIN是兼具设计美学与高效开发的后台系统!11', 'statistics', '1', '1', '1', null, '2026-04-25 20:03:36', '127.0.0.1', '1', '1', '2026-01-01 00:00:00', '2026-04-25 20:03:37', null);
INSERT INTO `sa_system_user` VALUES ('2', 'martin', '$2y$10$J3EkwRH8rNkveaanx1.j.ebRiBpnnVUGWa.i2MS3aNpb9ydAOolmm', '刘炽平', '2', 'https://static.wandongli.com/static/pc/images/png.png', 'martin@163.com', '15888888888', null, 'work', '2', '0', '1', '', null, null, '1', '1', '2026-01-01 00:00:00', '2026-03-22 11:51:35', null);
INSERT INTO `sa_system_user` VALUES ('3', 'allen', '$2y$10$H8d7riOjOiwPSopguEQ1fuKZz.fA0A54OvuzTqgJlbG1N3uOxEwM.', '张小龙', '1', 'https://static.wandongli.com/static/pc/images/png.png', '', '15888888888', null, 'work', '10', '0', '1', '', null, null, '1', '1', '2026-01-01 00:00:00', '2026-03-21 23:24:03', null);
INSERT INTO `sa_system_user` VALUES ('4', 'mark', '$2y$10$sY/4StKVV.N/8Ock8J8kdeIOK4jS4tAUoYjkzvB8Tzy0fLh.wA2KS', '任宇昕', '2', 'https://static.wandongli.com/static/pc/images/png.png', null, '15888888888', null, 'work', '11', '0', '1', null, null, null, '1', '1', '2026-01-01 00:00:00', '2026-03-21 23:20:40', null);
INSERT INTO `sa_system_user` VALUES ('5', 'dowson', '$2y$10$sY/4StKVV.N/8Ock8J8kdeIOK4jS4tAUoYjkzvB8Tzy0fLh.wA2KS', '汤道生', '1', 'https://static.wandongli.com/static/pc/images/png.png', null, '15888888888', null, 'work', '12', '0', '1', null, null, null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_user` VALUES ('10', 'timi_boss', '$2y$10$a9S4v4i6ZpDEJQ1qgWsWnuifsq4dgGdVFZDearta9.mOz.IpcBWzK', '姚晓光', '2', 'https://static.wandongli.com/static/pc/images/png.png', '', '15888888888', null, 'work', '111', '1', '1', '', '2026-04-25 16:57:51', '127.0.0.1', '1', '1', '2026-01-01 00:00:00', '2026-04-25 16:57:51', null);
INSERT INTO `sa_system_user` VALUES ('100', 'devwang', '$2y$10$vbnOKDkbm9hIEd8JWXITpO5pcRtl/KTBqswojEkuaP7vdGB5tPzES', '王程序员', '1', 'https://static.wandongli.com/static/pc/images/png.png', '888888@qq.com', '15888888888', null, 'work', '12', '0', '1', '1', '2026-04-25 17:34:23', '127.0.0.1', '1', '1', '2026-01-01 00:00:00', '2026-04-25 17:34:23', null);
INSERT INTO `sa_system_user` VALUES ('101', 'devli', '$2y$10$2fuWT7n6E8kyG357FbNrouRyRvulmTYXpmFE71bHOH3PQAgpPItW.', '李策划', '1', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3a67d5e50.12092222.png', null, '15888881234', null, 'work', '2', '0', '1', '1111', null, null, '1', '1', '2026-01-01 00:00:00', '2026-03-28 17:50:57', null);
INSERT INTO `sa_system_user` VALUES ('102', 'test', '$2y$10$wnixh48uDnaW/6D9EygDd.OHJK0vQY/4nHaTjMKBCVDBP2NiTatqS', 'admin1', '1', '', '', '13512344567', null, 'work', '2', '0', '1', '', null, null, null, null, '2026-03-22 21:42:54', '2026-03-22 21:54:46', '2026-03-22 21:54:46');
INSERT INTO `sa_system_user` VALUES ('104', 'test2', '$2y$10$AQjV0REYZumtiT4sDpmKtODXLQtIL.9ralALUFKZGvrxKWcpHS9ii', 'test2', '2', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3a83f9f14.20042528.png', '', '', null, 'work', '2', '0', '1', '', '2026-04-22 22:07:17', '127.0.0.1', null, '1', '2026-03-22 21:58:24', '2026-04-22 22:07:17', null);
INSERT INTO `sa_system_user` VALUES ('105', 'test3', '$2y$10$LRECF.G/1CS14NMujqelpucMJ4kQX.OuLdk5D5DT3EjgS8.CnePHy', 'test3', '1', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3a9e6c335.87540530.png', '', '', null, 'work', '101', '0', '1', '1', null, null, null, '1', '2026-03-25 21:58:53', '2026-03-28 17:50:39', null);
INSERT INTO `sa_system_user` VALUES ('106', 'test3test3', '$2y$10$lDmHlmRsw/pPF4Yd/HcP7eE0xGpkqsNVVhC4TrAz31K7XckECcaQu', 'test3test3', '2', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3acd6b581.21163358.png', '', '', null, 'work', '102', '0', '1', '', null, null, null, '1', '2026-03-25 21:59:21', '2026-03-28 17:50:29', null);
INSERT INTO `sa_system_user` VALUES ('116', 'testtesttest', '$2y$10$0GmhEW3QFGv.pHltuKQIUuNKAaACDiDARMt3QLjRVZ0y.iolKIDyK', 'testtest', '1', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3b2058589.44968839.webp', '', '', null, 'work', '2', '0', '1', '', null, null, '1', '1', '2026-03-27 21:12:03', '2026-03-28 17:49:22', null);
INSERT INTO `sa_system_user` VALUES ('117', 'test1311', '$2y$10$wxvQ16EIyNTrarFQ3fM1Euvd1rzgeZPbEgMsRbpFdiWxC3pX9UOpW', '11231', '2', '', '', '', null, 'work', '2', '0', '1', '', null, null, '1', '1', '2026-03-27 21:29:06', '2026-03-28 23:09:35', '2026-03-28 23:09:35');
INSERT INTO `sa_system_user` VALUES ('118', 'ddd', '$2y$10$GA/p/o7CH5FiJJxPGh6pDuAMhGNUoKYiSWBRhQwdB30aacnVwqC.W', 'ddd', '', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3b48aab47.53936592.png', '', '', null, 'statistics', '1', '0', '1', '', null, null, '1', '1', '2026-03-27 22:32:06', '2026-03-28 19:07:29', null);

-- ----------------------------
-- Table structure for `sa_system_user_dept`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_user_dept`;
CREATE TABLE `sa_system_user_dept` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tenant_id` bigint(20) unsigned NOT NULL COMMENT '租户ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `dept_id` bigint(20) unsigned NOT NULL COMMENT '部门ID',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建人ID',
  `updated_by` bigint(20) unsigned DEFAULT NULL COMMENT '更新人ID',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_tenant` (`user_id`,`tenant_id`) USING BTREE COMMENT '用户租户唯一索引',
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_tenant_id` (`tenant_id`) USING BTREE,
  KEY `idx_dept_id` (`dept_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sa_system_user_dept
-- ----------------------------
INSERT INTO `sa_system_user_dept` VALUES ('5', '2', '100', '129', '1', '1', null, '2026-04-24 21:47:04');
INSERT INTO `sa_system_user_dept` VALUES ('6', '1', '100', '12', '1', '1', '2026-04-24 21:52:00', '2026-04-24 21:52:24');
INSERT INTO `sa_system_user_dept` VALUES ('7', '1', '1', '1', '1', '1', null, null);
INSERT INTO `sa_system_user_dept` VALUES ('8', '1', '10', '1', '1', '1', '2026-04-25 09:26:06', '2026-04-25 09:26:06');

-- ----------------------------
-- Table structure for `sa_system_user_menu`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_user_menu`;
CREATE TABLE `sa_system_user_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `menu_id` bigint(20) unsigned NOT NULL COMMENT '菜单ID',
  `tenant_id` bigint(20) unsigned DEFAULT '0' COMMENT '租户id',
  `created_by` bigint(20) unsigned DEFAULT '0' COMMENT '创建人ID',
  `updated_by` bigint(20) unsigned DEFAULT '0' COMMENT '更新人ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0=禁用 1=启用',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_menu` (`user_id`,`menu_id`,`tenant_id`) USING BTREE,
  KEY `idx_user_id` (`user_id`),
  KEY `idx_menu_id` (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户菜单关联表';

-- ----------------------------
-- Records of sa_system_user_menu
-- ----------------------------
INSERT INTO `sa_system_user_menu` VALUES ('49', '118', '2', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('50', '118', '74', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('51', '118', '85', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('52', '118', '1', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('53', '118', '80', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('54', '118', '82', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('117', '100', '1', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('118', '100', '2', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('119', '100', '74', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('120', '100', '21', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('121', '100', '93', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('122', '100', '94', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('123', '100', '5', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('124', '100', '3', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('125', '100', '29', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('126', '100', '30', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('127', '100', '31', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('128', '100', '32', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('129', '100', '33', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('130', '100', '97', '1', '1', '1', '1', '2026-04-25 16:57:00', '2026-04-25 16:57:00', null);
INSERT INTO `sa_system_user_menu` VALUES ('131', '100', '4', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('132', '100', '3', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('133', '100', '20', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('134', '100', '22', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('135', '100', '23', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('136', '100', '24', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('137', '100', '25', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('138', '100', '26', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('139', '100', '27', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('140', '100', '28', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('141', '100', '6', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('142', '100', '34', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('143', '100', '35', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('144', '100', '36', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('145', '100', '37', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('146', '100', '38', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('147', '100', '39', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('148', '100', '80', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('149', '100', '81', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('150', '100', '86', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('151', '100', '87', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('152', '100', '82', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('153', '100', '83', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('154', '100', '84', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);
INSERT INTO `sa_system_user_menu` VALUES ('155', '100', '85', '2', '1', '1', '1', '2026-04-25 17:11:59', '2026-04-25 17:11:59', null);

-- ----------------------------
-- Table structure for `sa_system_user_post`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_user_post`;
CREATE TABLE `sa_system_user_post` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户主键',
  `post_id` bigint(20) unsigned NOT NULL COMMENT '岗位主键',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0=禁用 1=启用',
  `created_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `updated_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新人ID',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '租户上下文ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_post_id` (`post_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户与岗位关联表';

-- ----------------------------
-- Records of sa_system_user_post
-- ----------------------------
INSERT INTO `sa_system_user_post` VALUES ('1', '1', '2', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('4', '1', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('8', '2', '2', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('32', '116', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('33', '106', '2', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('34', '105', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('35', '104', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('36', '101', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('45', '10', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('48', '100', '1', '1', '0', '0', null, null, null, '1');

-- ----------------------------
-- Table structure for `sa_system_user_role`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_user_role`;
CREATE TABLE `sa_system_user_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：0=禁用 1=启用',
  `tenant_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '租户上下文ID',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_role_id` (`role_id`) USING BTREE,
  KEY `idx_user_id` (`user_id`) USING BTREE,
  KEY `idx_tenant_id` (`tenant_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户角色关联';

-- ----------------------------
-- Records of sa_system_user_role
-- ----------------------------
INSERT INTO `sa_system_user_role` VALUES ('1', '1', '1', '1', '1', null, null, null, null, null);
INSERT INTO `sa_system_user_role` VALUES ('12', '1', '2', '1', '1', null, null, null, null, null);
INSERT INTO `sa_system_user_role` VALUES ('14', '2', '2', '1', '1', '1', '1', '2026-03-22 11:51:35', '2026-03-22 11:51:35', null);
INSERT INTO `sa_system_user_role` VALUES ('43', '116', '8', '1', '1', '1', '1', '2026-03-28 17:49:22', '2026-03-28 17:49:22', null);
INSERT INTO `sa_system_user_role` VALUES ('44', '106', '4', '1', '1', '1', '1', '2026-03-28 17:50:29', '2026-03-28 17:50:29', null);
INSERT INTO `sa_system_user_role` VALUES ('45', '105', '2', '1', '1', '1', '1', '2026-03-28 17:50:39', '2026-03-28 17:50:39', null);
INSERT INTO `sa_system_user_role` VALUES ('46', '104', '2', '1', '1', '1', '1', '2026-03-28 17:50:47', '2026-03-28 17:50:47', null);
INSERT INTO `sa_system_user_role` VALUES ('47', '101', '2', '1', '1', '1', '1', '2026-03-28 17:50:57', '2026-03-28 17:50:57', null);
INSERT INTO `sa_system_user_role` VALUES ('63', '100', '204', '1', '2', '1', '1', '2026-04-24 21:47:04', '2026-04-24 21:47:04', null);
INSERT INTO `sa_system_user_role` VALUES ('68', '10', '6', '1', '1', '1', '1', '2026-04-25 09:46:09', '2026-04-25 09:46:09', null);
INSERT INTO `sa_system_user_role` VALUES ('72', '100', '2', '1', '1', '1', '1', '2026-04-25 16:42:55', '2026-04-25 16:42:55', null);

-- ----------------------------
-- Table structure for `sa_system_user_tenant`
-- ----------------------------
DROP TABLE IF EXISTS `sa_system_user_tenant`;
CREATE TABLE `sa_system_user_tenant` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户ID',
  `tenant_id` bigint(20) unsigned NOT NULL COMMENT '租户ID',
  `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认租户：0=否 1=是',
  `join_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '加入时间',
  `is_super` tinyint(4) NOT NULL DEFAULT '0' COMMENT '租户管理员',
  `created_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `updated_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '更新人ID',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_tenant` (`user_id`,`tenant_id`),
  KEY `idx_tenant_user` (`tenant_id`,`user_id`),
  KEY `idx_user_default` (`user_id`,`is_default`),
  KEY `idx_join_time` (`join_time`),
  KEY `idx_is_super` (`is_super`),
  CONSTRAINT `fk_sut_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `sa_system_tenant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户-租户关联表';

-- ----------------------------
-- Records of sa_system_user_tenant
-- ----------------------------
INSERT INTO `sa_system_user_tenant` VALUES ('1', '1', '1', '1', '2026-03-21 02:38:46', '0', '0', '0', null, '2026-04-25 17:54:42', null);
INSERT INTO `sa_system_user_tenant` VALUES ('3', '104', '1', '0', '2026-03-22 21:58:24', '0', '1', '1', '2026-03-22 21:58:24', '2026-04-18 23:46:42', null);
INSERT INTO `sa_system_user_tenant` VALUES ('5', '106', '1', '0', '2026-03-25 21:59:21', '0', '1', '1', '2026-03-25 21:59:21', '2026-04-19 00:12:53', null);
INSERT INTO `sa_system_user_tenant` VALUES ('6', '116', '1', '0', '2026-03-27 21:12:03', '0', '1', '1', '2026-03-27 21:12:03', '2026-04-19 00:12:51', null);
INSERT INTO `sa_system_user_tenant` VALUES ('9', '3', '3', '0', '2026-04-06 22:31:46', '0', '1', '1', '2026-04-06 22:31:46', '2026-04-06 22:31:46', null);
INSERT INTO `sa_system_user_tenant` VALUES ('10', '100', '1', '1', '2026-04-18 23:27:30', '1', '1', '1', '2026-04-18 23:27:30', '2026-04-25 17:35:00', null);
INSERT INTO `sa_system_user_tenant` VALUES ('11', '100', '2', '0', '2026-04-22 22:11:29', '1', '1', '1', '2026-04-22 22:11:29', '2026-04-25 17:34:59', null);
INSERT INTO `sa_system_user_tenant` VALUES ('12', '1', '2', '0', '2026-04-23 23:37:22', '0', '1', '1', '2026-04-23 23:37:22', '2026-04-25 17:54:42', null);
INSERT INTO `sa_system_user_tenant` VALUES ('14', '10', '1', '0', '2026-04-25 09:23:56', '0', '1', '1', '2026-04-25 09:23:56', '2026-04-25 09:24:25', null);
INSERT INTO `sa_system_user_tenant` VALUES ('15', '10', '2', '1', '2026-04-25 09:24:22', '0', '1', '1', '2026-04-25 09:24:22', '2026-04-25 09:24:25', null);

-- ----------------------------
-- Table structure for `sa_tool_crontab`
-- ----------------------------
DROP TABLE IF EXISTS `sa_tool_crontab`;
CREATE TABLE `sa_tool_crontab` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(100) DEFAULT NULL COMMENT '任务名称',
  `type` smallint(6) DEFAULT '4' COMMENT '任务类型',
  `target` varchar(500) DEFAULT NULL COMMENT '调用任务字符串',
  `parameter` varchar(1000) DEFAULT NULL COMMENT '调用任务参数',
  `task_style` tinyint(1) DEFAULT NULL COMMENT '执行类型',
  `rule` varchar(32) DEFAULT NULL COMMENT '任务执行表达式',
  `singleton` smallint(6) DEFAULT '1' COMMENT '是否单次执行 (1 是 2 不是)',
  `status` smallint(6) DEFAULT '1' COMMENT '状态 (1正常 2停用)',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='定时任务信息表';

-- ----------------------------
-- Records of sa_tool_crontab
-- ----------------------------
INSERT INTO `sa_tool_crontab` VALUES ('1', '访问官网', '1', 'https://www.baidu.com', '?hot=1', '1', '0 0 9 * * *', '2', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-03-23 23:47:50', null);
INSERT INTO `sa_tool_crontab` VALUES ('2', '登录gitee', '2', 'https://gitee.com/check_user_login', '{\"user_login\": \"saiadmin\"}', '1', '0 0 10 * * *', '2', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_tool_crontab` VALUES ('3', '定时执行任务', '3', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '5', '0 0 */12 * * *', '2', '1', '', '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_tool_crontab` VALUES ('4', 'aaabb', '1', 'asadd', '', '1', '0 1 1 * * *', '1', '0', '', '1', '1', '2026-03-23 23:48:16', '2026-03-28 23:13:57', null);
INSERT INTO `sa_tool_crontab` VALUES ('5', 'test', '1', 'test', '1', '1', '', '1', '1', '', '1', '1', '2026-03-28 23:02:02', '2026-03-28 23:12:41', '2026-03-28 23:12:41');

-- ----------------------------
-- Table structure for `sa_tool_crontab_log`
-- ----------------------------
DROP TABLE IF EXISTS `sa_tool_crontab_log`;
CREATE TABLE `sa_tool_crontab_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `crontab_id` int(11) unsigned DEFAULT NULL COMMENT '任务ID',
  `name` varchar(255) DEFAULT NULL COMMENT '任务名称',
  `target` varchar(500) DEFAULT NULL COMMENT '任务调用目标字符串',
  `parameter` varchar(1000) DEFAULT NULL COMMENT '任务调用参数',
  `exception_info` varchar(2000) DEFAULT NULL COMMENT '异常信息',
  `status` smallint(6) DEFAULT '1' COMMENT '执行状态 (1成功 2失败)',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='定时任务执行日志表';

-- ----------------------------
-- Records of sa_tool_crontab_log
-- ----------------------------
INSERT INTO `sa_tool_crontab_log` VALUES ('1', '1', '访问官网', 'https://saithink.top', '', '', '1', '2026-03-23 23:46:44', '2026-03-23 23:46:44', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('2', '1', '访问官网', 'https://saithink.top', '?hot=1', '', '1', '2026-03-23 23:47:08', '2026-03-28 23:00:52', '2026-03-28 23:00:52');
INSERT INTO `sa_tool_crontab_log` VALUES ('3', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:47:56', '2026-03-23 23:47:56', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('4', '4', 'aaa', 'asadd', '', 'GET 请求失败: Could not resolve host: asadd', '2', '2026-03-23 23:48:23', '2026-03-28 23:12:47', '2026-03-28 23:12:47');
INSERT INTO `sa_tool_crontab_log` VALUES ('5', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:51:03', '2026-03-23 23:51:03', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('6', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:23', '2026-03-23 23:56:23', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('7', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:25', '2026-03-31 00:41:41', '2026-03-31 00:41:41');
INSERT INTO `sa_tool_crontab_log` VALUES ('8', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:27', '2026-03-23 23:56:27', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('9', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:29', '2026-03-23 23:56:29', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('10', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:38', '2026-03-23 23:56:38', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('11', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:40', '2026-03-23 23:56:40', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('12', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:42', '2026-03-23 23:56:42', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('13', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:45', '2026-03-23 23:56:45', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('14', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-23 23:56:48', '2026-03-23 23:56:48', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('15', '1', '访问官网', 'https://www.baidu.com', '?hot=1', '', '1', '2026-03-28 23:00:42', '2026-03-28 23:00:42', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('16', '4', 'aaa', 'asadd', '', 'GET 请求失败: Could not resolve host: asadd', '2', '2026-03-28 23:01:10', '2026-03-28 23:01:10', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('17', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-28 23:01:14', '2026-03-28 23:01:14', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('18', '4', 'aaa', 'asadd', '', 'GET 请求失败: Could not resolve host: asadd', '2', '2026-03-28 23:12:57', '2026-03-28 23:12:57', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('19', '1', '访问官网', 'https://www.baidu.com', '?hot=1', '', '1', '2026-03-28 23:13:07', '2026-03-28 23:13:07', null);
INSERT INTO `sa_tool_crontab_log` VALUES ('20', '3', '定时执行任务', '\\plugin\\saiadmin\\process\\Test', '{\"type\":\"1\"}', '类不存在: \\plugin\\saiadmin\\process\\Test', '2', '2026-03-31 00:39:48', '2026-03-31 00:39:48', null);
