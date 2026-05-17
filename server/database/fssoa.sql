/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50726
Source Host           : localhost:3306
Source Database       : fssoa

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2026-05-17 09:39:22
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
) ENGINE=InnoDB AUTO_INCREMENT=2431 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Casbin权限规则表';

-- ----------------------------
-- Records of casbin_rule
-- ----------------------------
INSERT INTO `casbin_rule` VALUES ('886', 'p', '1', '/api/api/core/system/user', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1196', 'p', 'ceo', '/api/core/console', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1197', 'p', 'ceo', '/api/core/console/list', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1198', 'p', 'ceo', '/api/core/console/*', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1202', 'g', '105', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1204', 'g', '101', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1278', 'p', 'JTCEO', '/api/core/console', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1279', 'p', 'JTCEO', '/api/core/console/list', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1280', 'p', 'JTCEO', '/api/core/console/*', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1281', 'p', 'JTCEO', '/api/tool/crontab', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1282', 'p', 'JTCEO', '/api/tool/crontab', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1283', 'p', 'JTCEO', '/api/tool/crontab', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1284', 'p', 'JTCEO', '/api/tool/code', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1285', 'p', 'JTCEO', '/api/tool/code', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1544', 'p', '2', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1545', 'p', '2', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1546', 'p', '2', '/api/core/user', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1547', 'p', '2', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1548', 'p', '2', '/api/core/user', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1549', 'p', '2', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1550', 'p', '2', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1551', 'p', '2', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1552', 'p', '2', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1553', 'p', '2', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1554', 'p', '2', '/api/core/dept', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1555', 'p', '2', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1556', 'p', '2', '/api/core/dept', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1557', 'p', '2', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1558', 'p', '2', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1559', 'p', '2', '/api/core/role', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1560', 'p', '2', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1561', 'p', '2', '/api/core/role', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1562', 'p', '2', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1563', 'p', '2', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1564', 'p', '2', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1565', 'p', '2', '/api/core/post', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1566', 'p', '2', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1567', 'p', '2', '/api/core/post', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1568', 'p', '2', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1569', 'p', '2', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1570', 'p', '2', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1571', 'p', '2', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1572', 'p', '2', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1573', 'p', '2', '/api/core/menu', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1574', 'p', '2', '/api/core/menu', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1575', 'p', '2', '/api/core/config', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1576', 'p', '2', '/api/core/config', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1577', 'p', '2', '/api/core/config', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1578', 'p', '2', '/api/core/dict', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1579', 'p', '2', '/api/core/dict', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1580', 'p', '2', '/api/core/attachment', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1581', 'p', '2', '/api/core/attachment', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1582', 'p', '2', '/api/core/database', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1583', 'p', '2', '/api/core/database', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1584', 'p', '2', '/api/core/recycle', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1585', 'p', '2', '/api/core/recycle', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1586', 'p', '2', '/api/core/logs', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1587', 'p', '2', '/api/core/logs', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1588', 'p', '2', '/api/core/logs', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1589', 'p', '2', '/api/core/logs', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1590', 'p', '2', '/api/core/email', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1591', 'p', '2', '/api/core/email', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1592', 'p', '2', '/api/core/server', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1593', 'p', '2', '/api/core/server', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1594', 'p', '2', '/api/core/server', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1595', 'p', '2', '/api/core/console', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1596', 'p', '2', '/api/core/console/list', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1597', 'p', '2', '/api/core/console/*', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1598', 'p', '2', '/api/tool/crontab', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1599', 'p', '2', '/api/tool/crontab', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1600', 'p', '2', '/api/tool/crontab', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1601', 'p', '2', '/api/tool/code', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1602', 'p', '2', '/api/tool/code', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1603', 'p', '2', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1604', 'p', '2', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1605', 'p', '2', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1606', 'p', '2', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1607', 'p', '2', '/api/core/tenant', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1608', 'p', '2', '/api/core/tenant', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1609', 'p', '2', '/api/core/server', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1610', 'p', '2', '/flow', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1611', 'p', '2', '/api/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1612', 'p', '2', '/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1613', 'p', '2', '/flow/category/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1614', 'p', '2', '/api/flow/template', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1615', 'p', '2', '/flow/template', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1616', 'p', '2', '/flow/template/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1617', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1618', 'p', '2', '/flow/instance/my-started', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1619', 'p', '2', '/flow/instance/my-started/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1620', 'p', '2', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1621', 'p', '2', '/flow/task/pending', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1622', 'p', '2', '/flow/task/pending/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1623', 'p', '2', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1624', 'p', '2', '/flow/task/completed', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1625', 'p', '2', '/flow/task/completed/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1626', 'p', '2', '/api/flow/template', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1627', 'p', '2', '/flow/template/design/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1628', 'p', '2', '/flow/template/design/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1629', 'p', '2', '/api/flow/template', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1630', 'p', '2', '/flow/template/form-design/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1631', 'p', '2', '/flow/template/form-design/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1632', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1633', 'p', '2', '/flow/instance/start/:templateId', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1634', 'p', '2', '/flow/instance/start/:templateId/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1635', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1636', 'p', '2', '/flow/instance/detail/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1637', 'p', '2', '/flow/instance/detail/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1638', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1639', 'p', '2', '/flow/form/leave-request', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1640', 'p', '2', '/flow/form/leave-request/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1641', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1642', 'p', '2', '/flow/form/expense-claim', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1643', 'p', '2', '/flow/form/expense-claim/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1644', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1645', 'p', '2', '/flow/form/purchase-request', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1646', 'p', '2', '/flow/form/purchase-request/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1647', 'p', '2', '/api/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1648', 'p', '2', '/api/flow/category', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1649', 'p', '2', '/api/flow/category', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1650', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1651', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1652', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1653', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1654', 'p', '2', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1655', 'p', '2', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1656', 'p', '2', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1657', 'p', '2', '/flow/task/copy-me', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1658', 'p', '2', '/flow/task/copy-me/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1659', 'g', '2', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1791', 'g', '119', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1792', 'g', '120', 'bg_president', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1793', 'g', '121', 'gm', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1839', 'g', '123', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('1840', 'g', '122', 'bg_president', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2111', 'g', '10', 'staff', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2132', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2133', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2134', 'p', '100', '/api/core/user', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2135', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2136', 'p', '100', '/api/core/user', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2137', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2138', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2139', 'p', '100', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2140', 'p', '100', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2141', 'p', '100', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2142', 'p', '100', '/api/core/dept', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2143', 'p', '100', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2144', 'p', '100', '/api/core/dept', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2145', 'p', '100', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2146', 'p', '100', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2147', 'p', '100', '/api/core/role', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2148', 'p', '100', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2149', 'p', '100', '/api/core/role', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2150', 'p', '100', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2151', 'p', '100', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2152', 'p', '100', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2153', 'p', '100', '/api/core/post', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2154', 'p', '100', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2155', 'p', '100', '/api/core/post', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2156', 'p', '100', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2157', 'p', '100', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2158', 'p', '100', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2159', 'p', '100', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2160', 'p', '100', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2161', 'p', '100', '/api/core/menu', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2162', 'p', '100', '/api/core/menu', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2163', 'p', '100', '/api/core/config', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2164', 'p', '100', '/api/core/config', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2165', 'p', '100', '/api/core/config', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2166', 'p', '100', '/api/core/console', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2167', 'p', '100', '/api/core/console/list', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2168', 'p', '100', '/api/core/console/*', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2169', 'p', '100', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2170', 'p', '100', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2171', 'p', '100', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2172', 'p', '100', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2173', 'p', '100', '/api/core/tenant', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2174', 'p', '100', '/api/core/tenant', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2175', 'p', '100', '/template', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2176', 'p', '100', '/flow', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2177', 'p', '100', '/api/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2178', 'p', '100', '/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2179', 'p', '100', '/flow/category/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2180', 'p', '100', '/api/flow/template', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2181', 'p', '100', '/flow/template', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2182', 'p', '100', '/flow/template/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2183', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2184', 'p', '100', '/flow/instance/my-started', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2185', 'p', '100', '/flow/instance/my-started/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2186', 'p', '100', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2187', 'p', '100', '/flow/task/pending', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2188', 'p', '100', '/flow/task/pending/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2189', 'p', '100', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2190', 'p', '100', '/flow/task/completed', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2191', 'p', '100', '/flow/task/completed/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2192', 'p', '100', '/api/flow/template', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2193', 'p', '100', '/flow/template/design/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2194', 'p', '100', '/flow/template/design/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2195', 'p', '100', '/api/flow/template', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2196', 'p', '100', '/flow/template/form-design/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2197', 'p', '100', '/flow/template/form-design/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2198', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2199', 'p', '100', '/flow/instance/start/:templateId', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2200', 'p', '100', '/flow/instance/start/:templateId/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2201', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2202', 'p', '100', '/flow/instance/detail/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2203', 'p', '100', '/flow/instance/detail/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2204', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2205', 'p', '100', '/flow/form/leave-request', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2206', 'p', '100', '/flow/form/leave-request/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2207', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2208', 'p', '100', '/flow/form/expense-claim', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2209', 'p', '100', '/flow/form/expense-claim/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2210', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2211', 'p', '100', '/flow/form/purchase-request', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2212', 'p', '100', '/flow/form/purchase-request/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2213', 'p', '100', '/api/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2214', 'p', '100', '/api/flow/category', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2215', 'p', '100', '/api/flow/category', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2216', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2217', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2218', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2219', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2220', 'p', '100', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2221', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2222', 'p', '100', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2223', 'p', '100', '/flow/task/copy-me', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2224', 'p', '100', '/flow/task/copy-me/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2225', 'p', '100', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2226', 'p', '100', '/flow/task/reverted-canceled', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2227', 'p', '100', '/flow/task/reverted-canceled/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2228', 'p', '100', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2229', 'p', '100', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2230', 'p', '100', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2231', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2232', 'p', '100', '/flow/instance/resubmit/:instanceId', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2233', 'p', '100', '/flow/instance/resubmit/:instanceId/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2234', 'p', '100', '/api/flow/center', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2235', 'p', '100', '/flow/center', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2236', 'p', '100', '/flow/center/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2237', 'p', '100', '/api/flow/delegate', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2238', 'p', '100', '/flow/delegate', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2239', 'p', '100', '/flow/delegate/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2240', 'p', '100', '/api/flow/delegate', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2241', 'p', '100', '/api/flow/delegate', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2242', 'p', '100', '/api/flow/delegate', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2243', 'p', '100', '/api/flow/delegate', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2244', 'p', '100', '/api/flow/message', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2245', 'p', '100', '/flow/message', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2246', 'p', '100', '/flow/message/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2247', 'p', '100', '/api/flow/message', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2248', 'p', '100', '/api/flow/message', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2249', 'p', '100', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2250', 'p', '100', '/flow/instance/template/:templateId', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2251', 'p', '100', '/flow/instance/template/:templateId/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2252', 'p', '10', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2253', 'p', '10', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2254', 'p', '10', '/api/core/user', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2255', 'p', '10', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2256', 'p', '10', '/api/core/user', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2257', 'p', '10', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2258', 'p', '10', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2259', 'p', '10', '/api/core/user', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2260', 'p', '10', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2261', 'p', '10', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2262', 'p', '10', '/api/core/dept', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2263', 'p', '10', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2264', 'p', '10', '/api/core/dept', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2265', 'p', '10', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2266', 'p', '10', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2267', 'p', '10', '/api/core/role', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2268', 'p', '10', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2269', 'p', '10', '/api/core/role', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2270', 'p', '10', '/api/core/role', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2271', 'p', '10', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2272', 'p', '10', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2273', 'p', '10', '/api/core/post', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2274', 'p', '10', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2275', 'p', '10', '/api/core/post', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2276', 'p', '10', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2277', 'p', '10', '/api/core/post', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2278', 'p', '10', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2279', 'p', '10', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2280', 'p', '10', '/api/core/menu', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2281', 'p', '10', '/api/core/menu', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2282', 'p', '10', '/api/core/menu', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2283', 'p', '10', '/api/core/config', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2284', 'p', '10', '/api/core/config', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2285', 'p', '10', '/api/core/config', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2286', 'p', '10', '/api/core/console', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2287', 'p', '10', '/api/core/console/list', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2288', 'p', '10', '/api/core/console/*', 'GET', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2289', 'p', '10', '/api/core/dept', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2290', 'p', '10', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2291', 'p', '10', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2292', 'p', '10', '/api/core/tenant', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2293', 'p', '10', '/api/core/tenant', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2294', 'p', '10', '/api/core/tenant', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2295', 'p', '10', '/template', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2296', 'p', '10', '/flow', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2297', 'p', '10', '/api/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2298', 'p', '10', '/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2299', 'p', '10', '/flow/category/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2300', 'p', '10', '/api/flow/template', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2301', 'p', '10', '/flow/template', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2302', 'p', '10', '/flow/template/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2303', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2304', 'p', '10', '/flow/instance/my-started', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2305', 'p', '10', '/flow/instance/my-started/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2306', 'p', '10', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2307', 'p', '10', '/flow/task/pending', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2308', 'p', '10', '/flow/task/pending/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2309', 'p', '10', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2310', 'p', '10', '/flow/task/completed', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2311', 'p', '10', '/flow/task/completed/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2312', 'p', '10', '/api/flow/template', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2313', 'p', '10', '/flow/template/design/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2314', 'p', '10', '/flow/template/design/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2315', 'p', '10', '/api/flow/template', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2316', 'p', '10', '/flow/template/form-design/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2317', 'p', '10', '/flow/template/form-design/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2318', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2319', 'p', '10', '/flow/instance/start/:templateId', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2320', 'p', '10', '/flow/instance/start/:templateId/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2321', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2322', 'p', '10', '/flow/instance/detail/:id', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2323', 'p', '10', '/flow/instance/detail/:id/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2324', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2325', 'p', '10', '/flow/form/leave-request', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2326', 'p', '10', '/flow/form/leave-request/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2327', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2328', 'p', '10', '/flow/form/expense-claim', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2329', 'p', '10', '/flow/form/expense-claim/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2330', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2331', 'p', '10', '/flow/form/purchase-request', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2332', 'p', '10', '/flow/form/purchase-request/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2333', 'p', '10', '/api/flow/category', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2334', 'p', '10', '/api/flow/category', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2335', 'p', '10', '/api/flow/category', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2336', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2337', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2338', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2339', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2340', 'p', '10', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2341', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2342', 'p', '10', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2343', 'p', '10', '/flow/task/copy-me', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2344', 'p', '10', '/flow/task/copy-me/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2345', 'p', '10', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2346', 'p', '10', '/flow/task/reverted-canceled', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2347', 'p', '10', '/flow/task/reverted-canceled/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2348', 'p', '10', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2349', 'p', '10', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2350', 'p', '10', '/api/flow/task', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2351', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2352', 'p', '10', '/flow/instance/resubmit/:instanceId', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2353', 'p', '10', '/flow/instance/resubmit/:instanceId/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2354', 'p', '10', '/api/flow/center', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2355', 'p', '10', '/flow/center', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2356', 'p', '10', '/flow/center/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2357', 'p', '10', '/api/flow/delegate', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2358', 'p', '10', '/flow/delegate', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2359', 'p', '10', '/flow/delegate/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2360', 'p', '10', '/api/flow/delegate', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2361', 'p', '10', '/api/flow/delegate', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2362', 'p', '10', '/api/flow/delegate', 'PUT', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2363', 'p', '10', '/api/flow/delegate', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2364', 'p', '10', '/api/flow/message', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2365', 'p', '10', '/flow/message', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2366', 'p', '10', '/flow/message/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2367', 'p', '10', '/api/flow/message', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2368', 'p', '10', '/api/flow/message', 'DELETE', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2369', 'p', '10', '/api/flow/instance', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2370', 'p', '10', '/flow/instance/template/:templateId', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2371', 'p', '10', '/flow/instance/template/:templateId/*', '*', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2398', 'g', '104', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2423', 'g', '100', 'JTCEO', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2424', 'g', '100', 'ceo', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2429', 'g', '1', 'super_admin', '', '', '', '');
INSERT INTO `casbin_rule` VALUES ('2430', 'g', '1', 'ceo', '', '', '', '');

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

-- ----------------------------
-- Records of plugin_migrations
-- ----------------------------

-- ----------------------------
-- Table structure for `sa_article`
-- ----------------------------
DROP TABLE IF EXISTS `sa_article`;
CREATE TABLE `sa_article` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `category_id` int(10) NOT NULL COMMENT '分类id',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '文章标题',
  `author` varchar(255) DEFAULT NULL COMMENT '文章作者',
  `dept_id` mediumint(10) DEFAULT '0' COMMENT '部门id',
  `tenant_id` mediumint(10) DEFAULT NULL COMMENT '租户id',
  `image` varchar(1000) DEFAULT '' COMMENT '文章图片',
  `describe` varchar(1000) NOT NULL COMMENT '文章简介',
  `content` text NOT NULL COMMENT '文章内容',
  `views` int(11) DEFAULT '0' COMMENT '浏览次数',
  `sort` int(10) unsigned DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态',
  `is_link` tinyint(1) DEFAULT '2' COMMENT '是否外链',
  `link_url` varchar(255) DEFAULT NULL COMMENT '链接地址',
  `is_hot` tinyint(1) unsigned DEFAULT '2' COMMENT '是否热门',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_category_id` (`category_id`) USING BTREE,
  KEY `idx_tenant_dept` (`tenant_id`,`dept_id`) USING BTREE,
  KEY `idx_created_by` (`created_by`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章表';

-- ----------------------------
-- Records of sa_article
-- ----------------------------
INSERT INTO `sa_article` VALUES ('1', '1', '科技为农业强国建设插上腾飞之翼', '新华网', '3', '1', 'https://www.news.cn/tech/20251203/51066a5dc41545fa849d49423770ad70/2025120351066a5dc41545fa849d49423770ad70_202512037a03214ec26c4f029d6e1599d07c3779.png', '“十四五”规划提出，完善农业科技创新体系，创新农技推广服务方式，建设智慧农业。5年来，在科技创新的强劲支撑下，14亿人的饭碗端得更牢、农业现代化水平显著提升、产业新动能持续增强，农业强国建设迈上新台阶。', '<p style=\"text-align: justify;\"> &nbsp; &nbsp; &nbsp; &nbsp;“平均亩产1209.1公斤，这标志着全国首个两百万亩玉米‘吨粮田’成功创建。”金秋时节，新疆伊犁哈萨克自治州传来喜讯。这一纪录的诞生，离不开中国农业科学院研发的“玉米密植高产精准调控技术”支撑。依托该技术，位于伊犁的200余万亩玉米高产田亩保苗株数从传统的不足5000株提升到7000—8000株，玉米收获穗数大幅提升。</p><p style=\"text-align: justify;\">　　这只是我国科技强农、粮食增产增收的一个缩影。“十四五”以来，我国粮食总产量始终保持在1.3万亿斤以上。2024年粮食总产量更是首次突破1.4万亿斤，比2020年增产740亿斤。</p><p style=\"text-align: justify;\">　　习近平总书记强调，发展现代农业，建设农业强国，必须依靠科技进步，让科技为农业现代化插上腾飞的翅膀。</p><p style=\"text-align: justify;\">　　“十四五”规划提出，完善农业科技创新体系，创新农技推广服务方式，建设智慧农业。5年来，在科技创新的强劲支撑下，14亿人的饭碗端得更牢、农业现代化水平显著提升、产业新动能持续增强，农业强国建设迈上新台阶。</p><p style=\"text-align: justify;\">　　科技铸“芯”，夯实大国粮仓之基</p><p style=\"text-align: justify;\">　　国以农为本，农以种为先，种子被誉为农业的“芯片”。前不久，四川省富顺县水稻百亩超高产攻关片进行实割实测，再生稻亩产达到494.81公斤，加上此前测产中稻亩产807.13公斤，合计亩产突破1300公斤。取得这一成绩的背后，是“甬优4949”等高产突破性品种的选育和“中稻+再生稻”生产模式的推广。</p><p style=\"text-align: justify;\">　　水稻是我国第一大口粮。“十四五”时期全国多地选育出一批水稻突破性品种：安徽农业大学水稻栽培团队推广自育水稻品种，帮助当地农户水稻亩产增至800公斤；湖南杂交水稻研究中心选育出“西子3号”，推动解决部分受重金属污染地区“镉大米”问题；国家耐盐碱水稻技术创新中心培育出“箐两优3261”，填补了我国华南滨海盐碱区暂无强耐盐、多抗、优质杂交稻品种的空白……</p><p style=\"text-align: justify;\">　　习近平总书记指出，中国人的饭碗要牢牢端在自己手中，就必须把种子牢牢攥在自己手里。</p><p style=\"text-align: justify;\">　　作为我国另一大口粮，小麦育种的创新步伐也不断提速。2025年，西北农林科技大学一次性通过国家审定12个新品种，覆盖半冬性、冬性、春性类型，在抗倒伏等方面实现全面突破。这些为不同生态区“量身定制”的品种，在丰富我国小麦品种的同时，也大幅提升了小麦产能潜力。截至目前，西农小麦系列品种累计推广面积已达18亿亩，为保障国家粮食安全提供了坚实的种源支撑。</p><p style=\"text-align: justify;\">　　“十四五”以来，我国深入实施种业振兴行动，育成了一批生产急需的重大品种，选育出优质高产水稻、节水抗病小麦、机收籽粒玉米、高油高产大豆等急需品种，农作物自主选育品种面积占比超过了95%，做到了“中国粮”主要用“中国种”。</p><p style=\"text-align: justify;\">　　“去年全国粮食亩产394.7公斤，比‘十三五’末提高了12.5公斤，单产提升对我国粮食产量增长的贡献超过60%，有些年份会超过80%。”农业农村部党组书记、部长韩俊表示，“十四五”以来，农业农村部深入实施国家粮食安全战略，“以我为主、立足国内、确保产能、适度进口、科技支撑”，坚持产量产能、生产生态、增产增收一起抓，强化藏粮于地、藏粮于技，全方位夯实粮食安全根基。</p><p style=\"text-align: justify;\">　　智慧提“效”，驱动耕作方式变革</p><p style=\"text-align: justify;\">　　气象墒情传感器、智能虫情测报站等设备如同“千里眼”，与空中无人机巡航、地面机器狗巡检形成立体监测网络。这是日前科技日报记者在北京市昌平区的天汇园果园见到的一幕。</p><p style=\"text-align: justify;\">　　“目前，该果园环境和土壤墒情覆盖10余项指标，虫情识别准确率达90%，种植生产信息化率超过95%，同时土壤成分快检技术能在30分钟内完成土壤成分‘体检’，辅助实现果园虫情和灾情等早预警、早干预。”北京市智慧农业创新团队岗位专家吴建伟介绍，该果园管理从“经验驱动”转向“数据驱动”，为果树生长提供了全天候守护。</p><p style=\"text-align: justify;\">　　在四川省成都市新都区稻菜现代农业园区，当地自主研发的农业巡检机器人已代替人工开展巡检工作；在浙江省衢州市龙游县田间地头，一架植保无人机3小时就能完成300亩农田的喷药流程，相当于40多个人整整一天的工作量……</p><p style=\"text-align: justify;\">　　“十四五”以来，类似的农业新场景新模式不断涌现，现代农业设施装备持续普及应用。我国先后支持建设国家智慧农业创新应用项目116个，深入开展国产化智慧农业技术的中试熟化、推广应用，探索形成了一批信息技术与农机农艺相融合的节本增产增效技术模式。</p><p style=\"text-align: justify;\">　　习近平总书记指出，农业科技创新要着力提升创新体系整体效能，农业科技工作要突出应用导向，把论文写在大地上。</p><p style=\"text-align: justify;\">　　5年来，我国农业科技创新体系整体效能显著提升。我国充分利用物联网、大数据、人工智能等现代信息技术发展智慧农业，并研制出一批先进智能适用的农机装备。</p><p style=\"text-align: justify;\">　　“随着智能农机加快推广，全国安装北斗终端的农机约200万台，植保无人机年作业面积超过4.1亿亩。人工智能、农业机器人等新技术与农业生产经营加速融合，精准播种、变量施肥、智慧灌溉、精准饲喂、环境控制等逐渐普及。”农业农村部市场与信息化司司长雷刘功介绍。</p><p style=\"text-align: justify;\">　　这些前沿技术的落地应用，正是农业科技现代化推动农业现代化的生动实践。“十四五”以来，我国坚持用现代设施装备武装农业，用现代科学技术服务农业，推动农业现代化水平不断提高。2024年底，农业科技进步贡献率已经达到了63.2%，农作物耕种收综合机械化率超过75%。</p><p style=\"text-align: justify;\">　　创新延“链”，拓宽食物供给版图</p><p style=\"text-align: justify;\">　　近日，蒙牛集团携多款产品参加第八届中国国际进口博览会，展示其发展新质生产力的最新成果。“我们打造的全球液态奶行业首座‘灯塔工厂’，已成为全球乳业最高人效比的新标杆，是中国乳业抢占全球智能制造新高地的生动写照。”中粮集团副总经理、蒙牛乳业董事长庆立军介绍。这座“灯塔工厂”通过实施30多项第四次工业革命技术，实现了“百人百亿”的极致人效比——100名员工，年产能达百万吨，创造产值百亿元。</p><p style=\"text-align: justify;\">　　今天，科研创新已成为发展现代化海洋牧场的强大引擎。南方海洋实验室研发“珠海琴”等多功能融合的新型组合式结构加强型养殖平台，为海洋养殖带来新变革；珠海市海洋集团形成海工型养殖装备设计、建造、施工和运维等全产业链条，成功研发“格盛一号”养殖平台，订单水体总量相当于新开拓28.25万亩耕地。</p><p style=\"text-align: justify;\">　　习近平总书记指出，要树立大农业观、大食物观，农林牧渔并举，构建多元化食物供给体系。</p><p style=\"text-align: justify;\">　　“十四五”以来，我国突出科技支撑，强化要素保障，努力向森林要食物，向草原要食物，向江河湖海要食物，向设施农业要食物，向植物动物微生物要热量、要蛋白，多元化食物供给体系加快构建。</p><p style=\"text-align: justify;\">　　一组数据表明，农业科技创新正通过看得见的方式，让老百姓的餐桌品类变得愈发丰富——2024年，我国肉蛋奶等畜产品总量达到1.75亿吨，比2020年增加2778万吨，增长18.8%；水产品总产量达到7358万吨，比2020年增长12.3%，水产品总产量持续36年居全球第一。</p><p style=\"text-align: justify;\">　　党的二十届四中全会审议通过的《中共中央关于制定国民经济和社会发展第十五个五年规划的建议》提出，“统筹发展科技农业、绿色农业、质量农业、品牌农业，把农业建成现代化大产业”。科技创新能够催生新产业、新模式、新动能，是发展新质生产力的核心要素。韩俊表示，加快建设农业强国，必须清醒认识到农业科技国际竞争新形势，把农业科技创新放在更加突出的位置，紧盯世界农业科技前沿，加快突破农业关键核心技术，努力抢占农业科技创新制高点，塑造农业农村发展新动能新优势，培育壮大农业新质生产力。</p>', '5', '100', '1', '2', '', '2', '100', '1', '2024-06-02 22:55:25', '2026-01-10 11:13:25', null);
INSERT INTO `sa_article` VALUES ('2', '1', '商业航天稳步快跑 “太空旅游”渐行渐近', '新华网', '3', '1', 'https://www.news.cn/tech/20251124/c7cb9d4e405c4c82b78a8f861889cb22/20251124c7cb9d4e405c4c82b78a8f861889cb22_20251124044f95bbab864da2b0c30861aa41279b.png', '业界普遍认为，以可复用火箭为代表的核心技术突破是商业航天提速的关键支撑。据统计，2025年底至2026年初，我国可复用火箭技术将进入密集首飞期，包括蓝箭航天“朱雀三号”、中科宇航“力箭二号”、星际荣耀“双曲线三号”和星河动力“智神星一号”在内的多款可复用火箭将迎来首飞。', '<p style=\"text-align: justify;\"> &nbsp; &nbsp; &nbsp; &nbsp;可搭载7名乘客穿越卡门线，体验约4分钟失重体验……记者从11月22日在京开幕的第四届中国空间科学大会上了解到我国太空旅游的最新进展。与会专家学者认为，随着产业链条不断完善、核心技术持续突破，我国商业航天已迈入稳步快跑的发展新阶段，曾经遥不可及的“太空旅游”正加速走进现实。</p><p style=\"text-align: justify;\">  记者在第四届中国空间科学大会同期举行的“航天新技术、新成果展”上看到，我国首型面向太空旅游的可重复使用飞行器力鸿二号的模型吸引了众多参观者。中科宇航展台工作人员告诉记者，力鸿二号将采用“箭船分离”的方式将乘客送上太空：飞到既定高度之后，载人舱与火箭分离，继续飞越100公里的卡门线，开始约4分钟的失重段，之后返回地面，以伞降的方式着陆，火箭也将垂直着陆回收。“我们的目标是让力鸿二号可重复使用超30次，这样就能把飞行成本降下来，让更多的人体验太空旅游。”</p><p style=\"text-align: justify;\">  我国商业航天的快速发展让太空旅游渐行渐近。业界普遍认为，以可复用火箭为代表的核心技术突破是商业航天提速的关键支撑。据统计，2025年底至2026年初，我国可复用火箭技术将进入密集首飞期，包括蓝箭航天“朱雀三号”、中科宇航“力箭二号”、星际荣耀“双曲线三号”和星河动力“智神星一号”在内的多款可复用火箭将迎来首飞。</p><p style=\"text-align: justify;\">  不仅火箭研制加速突破，卫星应用也在不断拓展。此次展会上，微纳星空等卫星企业也带来了最新的研发成果。微纳星空品牌总监刘晓光介绍，即将发射的“全天候卫士”MN200S-2（01B）星是公司自主研制的商业X波段相控阵雷达成像领域的技术标杆型卫星，可广泛应用于应急救灾、海洋维权、国土安全、生态监测、智慧城市建设等场景，并可实现多星高密度堆叠发射，为后续卫星规模化组网编队提供关键技术验证与工程实践依据。“随着国家低轨卫星互联网的能力建设牵引，微纳星空已经开启批量化、低成本的卫星制造。”</p><p style=\"text-align: justify;\">  业界认为，目前我国已形成覆盖火箭研制、卫星制造、发射服务、地面应用的完整商业航天产业链，产业集群效应逐步显现。在北京，“南箭北星”的产业格局已显露雏形：亦庄新城正在打造全国首个商业航天共性科研生产基地——火箭大街，海淀区作为“北星”的核心承载区，已集聚涵盖商业卫星制造、测运控、运营及数据应用的近200家相关企业。“在此基础上，海淀正全力推进卫星小镇‘两区一平台’的建设：先导区目前已有40余家商业航天企业聚集；紧邻航天城的卫星小镇核心区54万平方米空间预计2026年6月竣备，将重点引入卫星上下游企业；同时，卫星小镇拟建公共服务平台，提供卫星整星及组部件的力学、热真空、抗辐射等多种测试服务。”卫星小镇核心区对接人段叶叶介绍。</p><p style=\"text-align: justify;\">  “我国发展商业航天的优势是人多、力量大、竞争强，技术和产品能够快速迭代，紧跟国际趋势。”中国科学院微小卫星创新研究院副院长张永合在接受记者专访时表示，但目前我国商业航天企业和人才大多集中在制造领域，“还需要更多能创造任务的人，有非常前沿的想法，有改变当前航天模式的颠覆性路径。”</p><p style=\"text-align: justify;\">  张永合认为，商业航天关键是要创造需求，“比如太空旅游就是商业航天创造的需求，将人们日常生活中的旅游延伸到太空中去，在产业上就属于增量。”未来，低空经济、空间互联网等也将打开想象空间。“有了坚实的技术底座，新的产业形态就会自然而然生长出来。”</p><p style=\"text-align: justify;\">  不过，业内专家也指出，我国商业航天发展仍面临体制机制创新不足、部分核心技术有待突破等挑战。从政策层面来看，近年来国家持续加大对商业航天的支持力度，相关扶持政策和行业规范正在逐步完善，旨在优化市场环境、加大核心技术研发支持，为商业航天高质量发展营造良好生态，推动太空旅游等新业态逐步走向成熟。</p><p style=\"text-align: justify;\">  业内普遍认为，商业航天已成为航天强国建设的重要增长点。从运载火箭重复使用技术突破到卫星应用场景拓展，随着技术持续成熟、产业链不断完善和政策环境优化，未来“上太空”有望从专业探索逐步走向大众体验，中国商业航天也将在全球太空经济格局中占据重要地位。</p><p><br></p>', '1', '100', '1', '2', '', '2', '100', '1', '2024-06-02 22:56:47', '2026-01-10 11:13:47', null);
INSERT INTO `sa_article` VALUES ('3', '2', '以数字经济为引擎加快推进中国式现代化', '新华网', '12', '1', 'https://www.news.cn/tech/20251023/0cb8f0bcb7874992b8d431abdd7331a9/202510230cb8f0bcb7874992b8d431abdd7331a9_2025102332abb363b12744eb9f725ce395f16e4a.png', 'The Athletic报道，阿森纳理疗师乔丹-里斯即将加盟曼联，成为红魔的首席理疗师。曼联首席理疗师罗宾-萨德勒已于今年一月离开俱乐部', '<p style=\"text-align: justify;\"> &nbsp; &nbsp; &nbsp; &nbsp;随着中国式现代化不断向前推进，中国迎来了数字经济发展的新机遇。在数字经济快速发展的背景下，中国式现代化的内涵得以拓展，现代化动力得以重塑，现代化新动能得以培育，现代化新优势得以形成。数字技术创新、实体经济与数字经济融合、产业数字化、数字产业化成为推进中国式现代化的重要驱动力量。</p><p style=\"text-align: justify;\">  在数字经济推动下，现代化由工业经济时代的现代化向数字经济时代的现代化转变，在这一大背景下需要在理论上研究数字经济赋能中国式现代化的逻辑和机制，需要深入探讨中国式现代化如何紧紧抓住数字经济发展带来的新机遇，以数字化的知识和信息作为关键生产要素，以数字技术为核心驱动力，在数据要素和数字技术的双轮驱动下推动中国式现代化走上新征程。</p><p style=\"text-align: justify;\">  南京大学数字经济与管理学院任保平教授的专著《数字经济赋能中国式现代化》于2025年在江苏人民出版社出版，全书共17章，35.8万字。该书立足世界范围内数字化浪潮下的经济现代化背景，从理论与实践两个方面研究了数字经济发展对中国式现代化的赋能作用。</p><p style=\"text-align: justify;\">  在理论层面，该书研究了数字经济发展对中国式现代化的影响、数字经济与中国式现代化的有机衔接，数字经济背景下中国式现代化目标的重塑、数字经济与中国式现代化深度融合的逻辑机制，数字经济背景下中国式现代化的延伸和拓展。在实践层面，从中国式现代化的不同方面具体研究了数字经济的赋能作用，具体包括数字经济赋能中国式新型工业化、新型城镇化、科技现代化、农业农村现代化、产业现代化和科技现代化。</p><p style=\"text-align: justify;\">  该书的核心观点主要有以下方面。一是，中国式现代化战略在数字化转型背景下发生的一系列拓展。促进工业化与信息化的融合发展，以数字化带动工业化发展，加大数字技术研发力度，大力发展数字产业。以数字化带动农业现代化，补足中国式现代化短板。协同匹配数字经济时代的创新供求，提升产业技术创新能力。促进企业数字化转型，引领数字经济发展。协调产业数字化与数字产业化，推进产业基础现代化。加快新型基础设施建设，提升基础设施支撑能力。构建数字平台体系，打造现代化经济新形态。</p><p style=\"text-align: justify;\">  二是，以数字经济发展培育中国式现代化新优势。针对数字经济带来的现代化新变化，研究了数字经济对中国式现代化的引擎作用，认为目前中国式现代化正处于数字经济蓬勃发展带来无数新机遇的时代，我们要抓住数字经济发展带来的新机遇，以数字经济推动中国式现代化的新发展。</p><p style=\"text-align: justify;\">  三是，阐释数字经济赋能中国式现代化的逻辑。在理论上深刻阐释数字经济如何成为中国式现代化的新引擎，数字经济作为新引擎对中国式现代化赋能的驱动机制和路径，论证数字经济发展赋能中国式现代化在目标、路径和战略上的延伸和拓展，为数字经济赋能中国式现代化提供了一个理论框架。</p><p style=\"text-align: justify;\">  四是，研究数字经济全面赋能中国式现代化的机制。中国式经济现代化涉及多方面内容，包括科技现代化、工业现代化、农业现代化、服务业现代化、产业链现代化、城市现代化、区域现代化、城市现代化、生态现代化、企业现代化、人的现代化和治理现代化，数字经济应该从上述方面赋能中国式现代化。</p><p style=\"text-align: justify;\">  五是，提出了以数字经济培育中国式现代化新优势的路径。数字经济培育中国式现代化的新优势包括需求端的动力新优势、供给端的效率新优势等。需要从数字化转型的创新能力、基础设施的供给能力、数字化转型的战略支撑能力，数字化转型的保障能力等方面研究数字经济发展培育中国式现代化新优势的实现路径。而且，需要从效率变革机制、动力变革机制和质量变革机制等方面研究数字经济赋能中国式现代化新优势培育的机制，从数字产业化、产业数字化、产学研协同创新、劳动力质量和相关配套制度等方面实现数字经济培育中国式现代化的新优势，全面展示数字经济赋能中国式现代化中的应用场景。</p>', '2', '100', '1', '2', '', '2', '1', '1', '2024-06-02 22:58:41', '2026-01-10 11:13:01', null);
INSERT INTO `sa_article` VALUES ('4', '2', '2025腾讯全球数字生态大会在深圳举行', '新华网', '8', '2', 'https://www.news.cn/tech/20250918/a8a0f6e1a6d740188db7752e247518bb/20250918a8a0f6e1a6d740188db7752e247518bb_202509184f78f2904fa2456db9537d878cb89166.jpg', '5月26日晚上18：00，中超第14轮，深圳新鹏城主场迎战上海申花，上半场马莱莱补射斩获赛季第6球，半场战罢，申花暂1-0新鹏城', '<p><br></p><div data-w-e-type=\"video\" data-w-e-is-void>\n<video poster=\"\" controls=\"true\" width=\"auto\" height=\"auto\"><source src=\"https://vodpub6.v.news.cn/yqfbzx-original/20250918/20250918a8a0f6e1a6d740188db7752e247518bb_XxjfceC000090_20250917_CBVFN0A001.mp4\" type=\"video/mp4\"/></video>\n</div><p><span style=\"color: rgb(0, 0, 0);\"> &nbsp; &nbsp; &nbsp; &nbsp;9月16日，2025腾讯全球数字生态大会在深圳举行，会上公布多项AI技术和产品最新进展，并宣布全面开放腾讯AI落地能力及优势场景，助力“好用的AI”在千行百业中加速落地。</span></p><p><br></p>', '3', '100', '1', '2', '', '2', '1', '1', '2024-06-02 22:59:41', '2026-01-10 13:42:34', null);
INSERT INTO `sa_article` VALUES ('5', '3', '秀我中国丨中国小机器人“勇闯”美国CES', '新华网', '121', '1', 'https://www.news.cn/tech/20260109/b2c43e2b0d1e43a98840c33e37fbbc73/20260109896bd0b56c18435987243f0f5dc01d67_202601099d0953f9999949a9b55e9d212d7bf773.jpg', '2026年美国拉斯维加斯消费电子展（CES）6日至9日举行，首次亮相海外展会的中国小机器人“启元Q1”刚一登场就成为焦点，凭借其出色表现“圈粉”海外。', '<p><br></p><div data-w-e-type=\"video\" data-w-e-is-void>\n<video poster=\"https://vodpub6.v.news.cn/yqfbzx-original/20260109/image/2ff2c0d5-4060-400d-8640-b41a0da5af1f.jpg\" controls=\"true\" width=\"360\" height=\"640\"><source src=\"https://vodpub6.v.news.cn/yqfbzx-original/20260109/20260109896bd0b56c18435987243f0f5dc01d67_XxjfceC000165_20260109_CBVFN0A001.mp4\" type=\"video/mp4\"/></video>\n</div><p style=\"text-align: left;\"><span style=\"color: rgb(0, 0, 0);\"> &nbsp; &nbsp; &nbsp; &nbsp;2026年美国拉斯维加斯消费电子展（CES）6日至9日举行，首次亮相海外展会的中国小机器人“启元Q1”刚一登场就成为焦点，凭借其出色表现“圈粉”海外。</span></p>', '3', '100', '1', '2', '', '2', '1', '1', '2024-06-02 23:01:17', '2026-01-10 13:42:24', null);
INSERT INTO `sa_article` VALUES ('6', '3', 'AI助力药物虚拟筛选提速百万倍 开启后AlphaFold时代创新药', '新华网', '1', '1', 'https://www.news.cn/tech/20260109/2e0f65d6733a4e2588a97dfe96593a09/202601092e0f65d6733a4e2588a97dfe96593a09_202601090012b088f5604e22a77ae70f8656f466.jpg', '团队与清华大学闫创业教授团队合作，在去甲肾上腺素转运体（NET）的临床相关靶点上开展了系列生物实验验证。', '<p><span style=\"color: rgb(0, 0, 0);\"> &nbsp; &nbsp; &nbsp; &nbsp;1月9日，清华大学智能产业研究院（AIR）联合清华大学生命学院、清华大学化学系在《科学》杂志发表论文《深度对比学习实现基因组级别药物虚拟筛选》。该论文研发了一个AI驱动的超高通量药物虚拟筛选平台DrugCLIP, 筛选速度对比传统方法实现百万倍提升，同时在预测准确率上也取得显著突破。依托该平台，团队打通了从AlphaFold结构预测到药物发现的关键通道，首次完成了覆盖人类基因组规模的药物虚拟筛选，为后AlphaFold时代的创新药物发现带来新可能性。</span></p><p><img src=\"https://www.news.cn/tech/20260109/2e0f65d6733a4e2588a97dfe96593a09/202601092e0f65d6733a4e2588a97dfe96593a09_2026010932fb993ce4734583aa3e4e861e536cff.png\" alt=\"\" data-href=\"\" style=\"\"/></p><p style=\"text-align: justify;\"> &nbsp; &nbsp;长期以来，药物研发面临“高风险、高投入、低成功率”的难题，在靶点发现与先导化合物筛选阶段，受限于传统工具的计算能力，绝大多数潜在靶点和化合物仍未被充分探索。如何在广阔的生物与化学空间中精准高效地发现活性化合物，是当前创新药物研发面临的核心挑战。</p><p style=\"text-align: justify;\">  据了解，为突破虚拟筛选规模瓶颈，DrugCLIP创新性地构建了蛋白口袋与小分子的“向量化结合空间”，将传统基于物理对接的筛选流程转化为高效的向量检索问题。该模型结合对比学习、3D结构预训练与多模态编码技术，能在三维结构层面精准建模蛋白-配体间的相互作用。训练后的高潜力分子将自然聚集于目标蛋白口袋的向量邻域，能够有效支撑快速的大规模虚拟筛选。依托这一机制，DrugCLIP在128核CPU+8张GPU的计算节点上，能实现毫秒级打分与万亿级日吞吐能力，筛选100万个候选分子仅需0.02秒，日处理能力达31万亿次，对比传统方法实现了百万倍提升。</p><p style=\"text-align: justify;\"><img src=\"https://www.news.cn/tech/20260109/2e0f65d6733a4e2588a97dfe96593a09/202601092e0f65d6733a4e2588a97dfe96593a09_2026010902fd55e1493f4741a2f10b4480ee398e.png\" alt=\"\" data-href=\"\" style=\"\"></p><p style=\"text-align: justify;\"> &nbsp; &nbsp;团队与清华大学闫创业教授团队合作，在去甲肾上腺素转运体（NET）的临床相关靶点上开展了系列生物实验验证。团队使用DrugCLIP模型从160万个候选分子中筛选出约100个高评分分子，同位素配体转运实验检测显示，其中15%为有效抑制剂，其中12个分子结合能力优于现有抗抑郁药物安非他酮。相关复合物结构已通过冷冻电镜解析，进一步验证了DrugCLIP筛选结果的生物学可信度。</p><p style=\"text-align: justify;\">  值得关注的是，DrugCLIP支持对AlphaFold预测的蛋白结构和apo（无配体）状态下的蛋白口袋进行筛选，扩大了其在真实药物发现场景中的适用性。团队和清华大学刘磊教授团队合作，针对E3泛素连接酶TRIP12（thyroid hormone receptor interactor 12）进行了虚拟筛选与实验验证。过往研究发现，TRIP12是多种肿瘤、帕金森综合征的潜在靶点，但是TRIP12缺少已知的小分子配体和复合物结构。团队使用DrugCLIP模型，从160万个候选分子中高通量筛选出约50个高评分分子，SPR实验证实，其中10个分子与TRIP12有结合能力，两个亲和力较高的分子也对TRIP12的泛素连接酶活性有一定的抑制活性。</p><p style=\"text-align: justify;\">  此外，依托DrugCLIP，团队首次完成了人类基因组规模的虚拟筛选项目，覆盖约1万个蛋白靶点、2万个结合口袋，分析超过5亿个小分子，富集出200万余个高潜力活性分子，构建了目前已知最大规模的蛋白-配体筛选数据库。该数据库已面向全球科研社区开放，为基础研究与早期药物发现提供了强大数据支持。</p><p style=\"text-align: justify;\">  DrugCLIP平台现已免费开放，用户无需本地部署，通过网页上传蛋白结构即可启动筛选任务。平台集成口袋/分子编码、向量检索、可视化与结果分析等功能，支持多种分子库调用与自定义上传，广泛适用于科研机构与企业用户。</p><p style=\"text-align: justify;\">  未来，DrugCLIP将与科研产业生态合作伙伴深度合作，在抗癌、传染病、罕见病等方向加速新靶点与First-in-class药物的发现。团队将持续优化引擎性能、拓展支持模态，助力构建一个更智能、高效与普惠的全球药物创新生态。</p>', '4', '100', '1', '2', '', '2', '1', '1', '2024-06-02 23:02:40', '2026-01-10 13:38:51', null);
INSERT INTO `sa_article` VALUES ('7', '4', '高度重视低空经济为哪般', '新华网', '4', '1', 'https://www.news.cn/tech/20250312/c0453593a495424780c5424c054a1d4d/20250312c0453593a495424780c5424c054a1d4d_2025031215d8945b560d4d169997f7745d0ef56f.jpg', '当前，我国低空经济正处于市场培育初期，关键技术的实用性和商业价值仅得到初步验证，但已彰显出广阔的增长空间', '<p style=\"text-align: justify;\"> &nbsp; &nbsp; &nbsp; &nbsp;近年来，低空经济成为全球发达经济体角逐的重要方向。虽然世界范围内低空经济还处于培育初期阶段，但是美国、日本、欧盟等国家和地区已经重点围绕场景开发应用、交通管理能力、运行技术验证、系统标准体系等方面积极出台和完善相关政策，加快发展低空经济。</p><p style=\"text-align: justify;\">  低空经济是依托低空飞行活动牵引串联的一系列相互关联的产业经济活动，不仅包括上游生产制造飞行器所必需的材料、零部件及分系统的行业企业，还包括中下游低空飞行器组装集成制造和测试试飞、设施配套及低空服务等领域。低空经济产业链条长、产业关联性强、应用场景丰富，具有战略引领性、高增长潜力等显著特征，既可以推动现代农牧业、先进制造业、现代服务业深度融合发展，又能够扩大有效投资、提振消费需求、提升创新能力。世界主要国家高度重视低空经济发展，就是因为看好其发展前景。</p><p style=\"text-align: justify;\">  当前，我国低空经济正处于市场培育初期，关键技术的实用性和商业价值仅得到初步验证，但已彰显出广阔的增长空间。未来随着技术迭代升级和商业模式逐步成熟，低空经济的高增长潜力将会进一步释放，更容易实现相关产业企业的群体性爆发成长，有望成为拉动经济增长的新引擎。</p><p style=\"text-align: justify;\">  一方面，低空飞行器的产业规模体量加快增长、产业生态持续完善。目前，我国无人机制造国际竞争力逐步增强，消费级无人机世界领先优势突出。截至2023年底，我国民用无人机研制企业已超过2300家，量产的无人机产品超过1000款。2023年，我国民用无人机产业规模达到1174.3亿元，同比增长32%。同时，新一代信息技术、新材料、新能源加速与航空科学技术融合发展，推动低空飞行器动力装备及系统、传感器、飞控系统等相关技术加速迭代，绿色高效、安全低噪的飞行器设计、制造与验证技术也持续更新升级。</p><p style=\"text-align: justify;\">  另一方面，体量巨大、类型多样的应用场景持续涌现，牵引低空服务快速释放动能。运营航空器大幅增加，《2023—2024中国民用无人驾驶航空发展报告》显示，截至2024年8月底，我国无人机实名登记数达198.7万架，比2023年底增加72万架；共颁发无人机驾驶员执照22万本，比2023年底增加13.9%。随着影视航拍、航空运动、空中观光游览等低空文旅应用场景快速发展，低空经济能为满足人民群众美好生活需求提供新供给。2023年，横店“航空＋影视＋旅游”交旅融合案例入选第一批交通运输与旅游融合发展十佳案例；2024年，敦煌“飞天”通用航空项目等航空旅游产品案例入选第二批交通运输与旅游融合发展示范案例。低空旅游市场潜力开始显现。</p><p style=\"text-align: justify;\">  同时，低空经济在农业植保、现代物流等行业领域的发展应用不断深入。随着无人机应用技术不断成熟和应用场景持续丰富，“农林牧副渔”多场景作业不断拓展，农业无人机服务市场规模呈蓬勃发展态势。2024年，全国植保无人机的保有量达到25.1万架，作业面积更是高达26.7亿亩次，同比增长近25%。从全球看，上世纪80年代以来，美国农业植保无人机作业渗透率超过50％，日本60％的稻田采用无人机进行植保作业。相较而言，我国农业无人机作业渗透率还比较低，有很大发展空间。在低空物流领域，以无人机为载运工具的无人化配送成为优化城市物流的重要方向，这能有效解决传统物流配送模式面临的劳动力成本、运输成本大幅攀升以及物资配送流通效率低下等诸多问题。在“低空+”领域，低空经济赋能社会治理成效突出，促进巡检、应急救援、城市管理、森林防火、医疗救护等公共服务快速发展。实践中，北京延庆、湖北武汉等地已采用电力线路无人机智能巡检，有效降低了巡检成本，提升了巡检效率。</p><p style=\"text-align: justify;\">  但也要看到，我国低空经济发展还存在一些问题，如统筹发展和安全有短板、产业融合化发展不足、空域管理协同机制尚不健全、基础设施建设相对滞后等。对此，要从突出集群融合、强化科技创新、加强设施建设等方面综合施策，将低空经济的发展潜力充分释放出来。</p><p style=\"text-align: justify;\">  一是突出集群融合，加快培育壮大低空经济产业集群，以市场需求为牵引、以科技创新为驱动，积极完善产业生态、谋划应用场景，推进低空制造业集群化发展。二是强化科技创新，聚焦低空经济创新链薄弱环节，加大科技创新投入，加快提升低空技术支撑能力。三是加强设施建设，构建低空经济基础设施综合保障体系，坚持绿色发展、节约集约，统筹推进通用机场、电动垂直起降飞行器起降场、固定运营基地、飞行服务站等地面配套基础设施建设，推进低空飞行通信、导航、气象监测等信息基础设施建设，加速低空经济智联网络设施建设。此外，还要统筹发展和安全，加强低空飞行器监控防护，强化低空安全技术攻关，提升空域精细化管理能力。坚持包容审慎的安全风险管控理念，建设监管服务体系，建立灵活调配、动态高效的低空空域管理使用机制，增强管理的协同性与联动性。</p>', '11', '100', '1', '2', '', '2', '1', '1', '2024-06-02 23:04:23', '2026-01-10 13:43:44', null);
INSERT INTO `sa_article` VALUES ('8', '4', '国家发改委成立低空经济发展司', '新华网', '1', '1', 'https://www.news.cn/tech/20241231/3f5396024a9749ee863292c04c7119dc/202412313f5396024a9749ee863292c04c7119dc_2024123101c42d384b83467f835ffd286af095d4.jpg', '近日，低空经济发展司召开推动低空基础设施建设座谈会和推动低空智能网联系统建设专题座谈会..', '<p style=\"text-align: justify;\"> &nbsp; &nbsp; &nbsp; 记者从国家发展和改革委员会官方网站获悉，低空经济发展司已正式成立。</p><p style=\"text-align: justify;\">　　低空经济发展司的具体职责是拟订并组织实施低空经济发展战略、中长期发展规划，提出有关政策建议，协调有关重大问题等。</p><p style=\"text-align: justify;\">　　近日，低空经济发展司召开推动低空基础设施建设座谈会和推动低空智能网联系统建设专题座谈会。</p><p style=\"text-align: justify;\">　　在推动低空基础设施建设座谈会上，低空经济发展司负责同志同自然资源部、生态环境部等部委和有关中央企业进行座谈，了解相关领域低空经济典型场景应用和相关基础设施建设发展情况，并就推动低空基础设施有序规划建设进行交流。</p><p style=\"text-align: justify;\">　　在推动低空智能网联系统建设专题座谈会上，低空经济发展司负责同志与通信、导航方面有关专家进行座谈，就低空智能网联系统建设进行交流。</p>', '6', '100', '1', '2', '', '2', '1', '1', '2024-06-02 23:04:23', '2026-04-23 23:21:09', null);
INSERT INTO `sa_article` VALUES ('9', '1', '多租户系统架构与权限设计探讨', 'admin', '1', '2', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3b2058589.44968839.webp', '多租户系统架构与权限设计探讨', '<p>多租户系统架构与权限设计探讨多租户系统架构与权限设计探讨</p><p>多租户系统架构与权限设计探讨</p><p>多租户系统架构与权限设计探讨</p>', '0', '100', '1', '2', '', '2', '1', '1', '2026-04-23 22:22:26', '2026-04-23 23:21:00', null);
INSERT INTO `sa_article` VALUES ('10', '2', '切尔西官宣41岁主帅下课！带队107天+英超遭5连败 解约金1200万镑', 'admin', '1', '2', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3acd6b581.21163358.png', '切尔西官宣41岁主帅下课！带队107天+英超遭5连败 解约金1200万镑', '<p><br></p><p style=\"text-align: justify;\">北京时间4月23日，英超豪门切尔西俱乐部官方宣布，因近期比赛结果和球队表现未能达到应有标准，41岁的主帅罗塞尼尔正式下课。</p><p style=\"text-align: justify;\">切尔西官方向罗塞尼尔及其教练组，在俱乐部效力期间所付出的所有努力，表示衷心的感谢，并祝愿他在未来一切顺利。</p><p style=\"text-align: center;\"><img src=\"https://nimg.ws.126.net/?url=http%3A%2F%2Fdingyue.ws.126.net%2F2026%2F0423%2Fd77d2db1j00tdwmbr00p7d000qo00gcm.jpg&amp;thumbnail=660x2147483647&amp;quality=80&amp;type=jpg\" alt=\"\" data-href=\"\" style=\"\"><br><br></p><p style=\"text-align: justify;\">切尔西还同时宣布，助教卡勒姆·麦克法兰将担任一线队临时主教练，带领球队直至本赛季结束，并由俱乐部现有教练组成员协助。</p><p style=\"text-align: justify;\">切尔西官方还表示，俱乐部未来会在寻求新帅稳定人选的过程中，进行自我反思，以做出正确的长期任命。</p><p style=\"text-align: center;\"><img src=\"https://nimg.ws.126.net/?url=http%3A%2F%2Fdingyue.ws.126.net%2F2026%2F0423%2Fc5090293j00tdwmbr008dd000qo00g8m.jpg&amp;thumbnail=660x2147483647&amp;quality=80&amp;type=jpg\" alt=\"\" data-href=\"\" style=\"\"><br><br></p><p style=\"text-align: justify;\">今年1月8日，罗塞尼尔从清湖集团旗下的另一支球队斯特拉斯堡，“内部调遣”来到切尔西，双方签下了长达6年半的合同。</p><p style=\"text-align: justify;\">上任之初，他带领切尔西踢出了不错的成绩，前9场比赛球队只在英联杯两负阿森纳，赢下了剩余7场。</p><p style=\"text-align: center;\"><img src=\"https://nimg.ws.126.net/?url=http%3A%2F%2Fdingyue.ws.126.net%2F2026%2F0423%2F52e72206j00tdwmbq0017d000qo00hmm.jpg&amp;thumbnail=660x2147483647&amp;quality=80&amp;type=jpg\" alt=\"\" data-href=\"\" style=\"\"><br><br></p><p style=\"text-align: justify;\">但随后，切尔西的状况开始急转直下，最近8场比赛，切尔西1胜7负，唯一的一胜还是在足总杯赛场战胜第三级别联赛球队维尔港。</p><p style=\"text-align: justify;\">最近5场联赛，切尔西遭遇5连败且一球未进，目前切尔西在本轮先赛的情况下，已经落后欧冠区7分之多。</p><p style=\"text-align: justify;\">最终，罗塞尼尔仅执教切尔西107天后，便黯然下课。他带队打了23场比赛，战绩为11胜2平10负，胜率不到5成。</p><p style=\"text-align: center;\"><img src=\"https://nimg.ws.126.net/?url=http%3A%2F%2Fdingyue.ws.126.net%2F2026%2F0423%2F869822dfj00tdwmbr00bbd000e600hvm.jpg&amp;thumbnail=660x2147483647&amp;quality=80&amp;type=jpg\" alt=\"\" data-href=\"\" style=\"\"><br><br></p><p style=\"text-align: justify;\">而据多家媒体确认，罗塞尼尔在切尔西剩余未被支付的薪水高达2400万镑。而队报记者表示，切尔西不会支付所有剩余款项，将支付1000万-1200万镑解雇他。</p><p style=\"text-align: justify;\"><br></p>', '0', '100', '1', '2', '', '2', '1', '1', '2026-04-23 23:39:01', '2026-04-23 23:39:18', null);
INSERT INTO `sa_article` VALUES ('11', '1', 'Dromara Warm-Flow，国产的工作流引擎1', 'Dromara', '1', '1', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3af23d945.66099869.webp', 'Dromara Warm-Flow，国产的工作流引擎，以其简洁轻量、五脏俱全、灵活扩展性强的特点，成为了众多开发者的首选。它不仅可以通过jar包快速集成设计器，同时原生支持经典和仿钉钉双模式，还具备以下显著优势：', '<blockquote style=\"text-align: start;\">Dromara Warm-Flow，国产的工作流引擎，以其简洁轻量、五脏俱全、灵活扩展性强的特点，成为了众多开发者的首选。它不仅可以通过jar包快速集成设计器，同时原生支持经典和仿钉钉双模式，还具备以下显著优势：</blockquote><ul><li style=\"text-align: start;\"><strong>简洁易用</strong>‌：仅包含7张表，代码量少，上手和集成速度快。</li><li style=\"text-align: start;\"><strong>审批功能全面</strong>‌：支持通过、退回、撤销、拿回、任意跳转、终止、转办、票签、委派和加减签、互斥、并行、自动审批、远程访问和脚本执行服务等多种审批操作，以及条件表达式、办理人表达和监听器等高级功能。</li><li style=\"text-align: start;\"><strong>流程设计器</strong>‌：通过jar包形式快速集成到项目，支持节点属性扩展，原生支持经典和仿钉钉双模式。</li><li style=\"text-align: start;\"><strong>流程图</strong>‌：自带流程图，通过jar包快速集，功能扩展，原生支持经典和仿钉钉双模式。</li><li style=\"text-align: start;\"><strong>条件表达式</strong>‌：内置常见的和spel条件表达式，支持自定义扩展。</li><li style=\"text-align: start;\"><strong>办理人变量表达式</strong>‌：内置${handler}和spel格式的表达式，满足不同场景需求，灵活可扩展。</li><li style=\"text-align: start;\"><strong>监听器</strong>‌：提供四种监听器，支持不同作用范围和spel表达式，参数传递灵活，支持动态权限。</li><li style=\"text-align: start;\"><strong>流程变量</strong>‌：在整个流程办理过程起到重要的角色，如办理人表达式中，传入变量进行动态指定办理人。</li><li style=\"text-align: start;\"><strong>ORM框架支持</strong>‌：支持MyBatis、Mybatis-Plus、Mybatis-Flex、Jpa、Easy-Query和BeetlSql，后续将扩展支持其他框架</li><li style=\"text-align: start;\"><strong>数据库支持</strong>‌：支持MySQL、Oracle、PostgreSQL和SQL Server，其他数据库只需要转换表结构即可支持。</li><li style=\"text-align: start;\"><strong>多租户与软删除</strong>‌：流程引擎自身维护多租户和软删除实现，也可使用对应ORM框架的实现方式。</li><li style=\"text-align: start;\"><strong>兼容性</strong>‌：同时支持Spring和Solon，兼容Java8、Java17、Java21。</li><li style=\"text-align: start;\"><strong>实战项目</strong>‌：官方提供基于Ruoyi-Vue封装的实战项目，极具参考价值。</li><li style=\"text-align: start;\"></li><li style=\"text-align: start;\"> <a href=\"https://www.warm-flow.com/master/introduction/introduction.html\" target=\"_blank\">https://www.warm-flow.com/master/introduction/introduction.html</a> </li><li style=\"text-align: start;\"> <a href=\"https://juejin.cn/post/7205873584339009596\" target=\"_blank\">https://juejin.cn/post/7205873584339009596</a> </li></ul>', '0', '100', '1', '2', '', '2', '1', '1', '2026-04-26 17:09:16', '2026-05-03 22:33:34', null);

-- ----------------------------
-- Table structure for `sa_article_banner`
-- ----------------------------
DROP TABLE IF EXISTS `sa_article_banner`;
CREATE TABLE `sa_article_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `banner_type` int(11) DEFAULT NULL COMMENT '类型',
  `image` varchar(1000) DEFAULT NULL COMMENT '图片地址',
  `is_href` tinyint(1) DEFAULT '1' COMMENT '是否链接',
  `url` varchar(255) DEFAULT NULL COMMENT '链接地址',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `remark` varchar(255) DEFAULT NULL COMMENT '描述',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章轮播图';

-- ----------------------------
-- Records of sa_article_banner
-- ----------------------------
INSERT INTO `sa_article_banner` VALUES ('1', '1', 'https://picsum.photos/id/490/640/360', '1', '/blog/1', '探索亚洲的烹饪奇迹', '1', '100', '有一系列名为“新加坡传统烹饪”的食谱，探索了新加坡的美食和文化。它包括新加坡华人、马来人、印度人、欧亚人和土生华人（海峡华人）的美食', '1', '1', '2024-06-02 23:06:37', '2026-01-09 21:51:50', null);
INSERT INTO `sa_article_banner` VALUES ('2', '1', 'https://picsum.photos/id/29/640/360', '1', '/blog/2', '探索雄伟的山峰', '1', '100', '攀登这座风景如画的山峰的最佳方式是乘坐御在所索道，乘坐15 分钟即可将游客带入空中，欣赏周围一览无余的景观', '1', '1', '2024-06-02 23:06:49', '2026-01-09 21:51:54', null);
INSERT INTO `sa_article_banner` VALUES ('3', '1', 'https://picsum.photos/id/903/640/360', '1', '/blog/3', '揭秘奇迹', '1', '100', '极光是地球磁场与太阳风相互作用的产物，当太阳风中的带电粒子与地球高层大气中的原子、分子碰撞时，会产生发光现象，形成美丽的极光', '1', '1', '2024-06-02 23:06:56', '2026-01-09 21:53:32', null);

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='附件信息表';

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
INSERT INTO `sa_system_attachment` VALUES ('26', '1', '1', 'mac_mtm3u8.txt', '69ff2c7a325e50.13047637.txt', '0bd6e5ae19ba6bec8b418098cc319c1e', 'text/plain', 'uploads/2026/05/09', 'txt', '968', '968 B', 'http://127.0.0.1:8000/uploads/2026/05/09/69ff2c7a325e50.13047637.txt', null, '1', '1', '2026-05-09 20:45:46', '2026-05-16 17:09:28', '2026-05-16 17:09:28');
INSERT INTO `sa_system_attachment` VALUES ('27', '1', '1', '新建文本文档 (2).txt', '69ff2d5ac201f3.70935436.txt', '699fcb3697b574eacb0ece1a150ba873', 'text/plain', 'uploads/2026/05/09', 'txt', '4719', '4.61 KB', 'http://127.0.0.1:8000/uploads/2026/05/09/69ff2d5ac201f3.70935436.txt', null, '1', '1', '2026-05-09 20:49:30', '2026-05-16 17:09:33', '2026-05-16 17:09:33');
INSERT INTO `sa_system_attachment` VALUES ('28', '1', '1', 'MP_verify_MCykXGaAFZK3w7LS.txt', '69ff2d5ad6f9d1.36600390.txt', '82844c241bb0ee0ed7a8ab04f9930231', 'text/plain', 'uploads/2026/05/09', 'txt', '16', '16 B', 'http://127.0.0.1:8000/uploads/2026/05/09/69ff2d5ad6f9d1.36600390.txt', null, '1', '1', '2026-05-09 20:49:30', '2026-05-16 17:09:31', '2026-05-16 17:09:31');

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
-- Table structure for `sa_system_login_log`
-- ----------------------------
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
) ENGINE=InnoDB AUTO_INCREMENT=501 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='登录日志表';

-- ----------------------------
-- Records of sa_system_login_log
-- ----------------------------
INSERT INTO `sa_system_login_log` VALUES ('453', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-14 23:13:56', null, null, null, '2026-05-14 23:13:56', '2026-05-14 23:13:56', null);
INSERT INTO `sa_system_login_log` VALUES ('454', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 00:14:50', null, null, null, '2026-05-15 00:14:50', '2026-05-15 00:14:50', null);
INSERT INTO `sa_system_login_log` VALUES ('455', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 00:15:08', null, null, null, '2026-05-15 00:15:08', '2026-05-15 00:15:08', null);
INSERT INTO `sa_system_login_log` VALUES ('456', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 00:15:48', null, null, null, '2026-05-15 00:15:48', '2026-05-15 00:15:48', null);
INSERT INTO `sa_system_login_log` VALUES ('457', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 00:17:29', null, null, null, '2026-05-15 00:17:29', '2026-05-15 00:17:29', null);
INSERT INTO `sa_system_login_log` VALUES ('458', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 07:37:22', null, null, null, '2026-05-15 07:37:22', '2026-05-15 07:37:22', null);
INSERT INTO `sa_system_login_log` VALUES ('459', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 07:49:43', null, null, null, '2026-05-15 07:49:43', '2026-05-15 07:49:43', null);
INSERT INTO `sa_system_login_log` VALUES ('460', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 07:50:37', null, null, null, '2026-05-15 07:50:37', '2026-05-15 07:50:37', null);
INSERT INTO `sa_system_login_log` VALUES ('461', 'timi_boss', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 07:51:18', null, null, null, '2026-05-15 07:51:18', '2026-05-15 07:51:18', null);
INSERT INTO `sa_system_login_log` VALUES ('462', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 07:52:20', null, null, null, '2026-05-15 07:52:20', '2026-05-15 07:52:20', null);
INSERT INTO `sa_system_login_log` VALUES ('463', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 07:55:54', null, null, null, '2026-05-15 07:55:54', '2026-05-15 07:55:54', null);
INSERT INTO `sa_system_login_log` VALUES ('464', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 07:56:58', null, null, null, '2026-05-15 07:56:58', '2026-05-15 07:56:58', null);
INSERT INTO `sa_system_login_log` VALUES ('465', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 07:59:50', null, null, null, '2026-05-15 07:59:50', '2026-05-15 07:59:50', null);
INSERT INTO `sa_system_login_log` VALUES ('466', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 08:03:37', null, null, null, '2026-05-15 08:03:37', '2026-05-15 08:03:37', null);
INSERT INTO `sa_system_login_log` VALUES ('467', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 08:04:07', null, null, null, '2026-05-15 08:04:07', '2026-05-15 08:04:07', null);
INSERT INTO `sa_system_login_log` VALUES ('468', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 08:04:48', null, null, null, '2026-05-15 08:04:48', '2026-05-15 08:04:48', null);
INSERT INTO `sa_system_login_log` VALUES ('469', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 18:15:04', null, null, null, '2026-05-15 18:15:04', '2026-05-15 18:15:04', null);
INSERT INTO `sa_system_login_log` VALUES ('470', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 18:38:58', null, null, null, '2026-05-15 18:38:58', '2026-05-15 18:38:58', null);
INSERT INTO `sa_system_login_log` VALUES ('471', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 18:39:13', null, null, null, '2026-05-15 18:39:13', '2026-05-15 18:39:13', null);
INSERT INTO `sa_system_login_log` VALUES ('472', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 18:40:14', null, null, null, '2026-05-15 18:40:14', '2026-05-15 18:40:14', null);
INSERT INTO `sa_system_login_log` VALUES ('473', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 18:45:10', null, null, null, '2026-05-15 18:45:10', '2026-05-15 18:45:10', null);
INSERT INTO `sa_system_login_log` VALUES ('474', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 19:04:11', null, null, null, '2026-05-15 19:04:11', '2026-05-15 19:04:11', null);
INSERT INTO `sa_system_login_log` VALUES ('475', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 19:04:54', null, null, null, '2026-05-15 19:04:54', '2026-05-15 19:04:54', null);
INSERT INTO `sa_system_login_log` VALUES ('476', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 19:05:46', null, null, null, '2026-05-15 19:05:46', '2026-05-15 19:05:46', null);
INSERT INTO `sa_system_login_log` VALUES ('477', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 22:02:25', null, null, null, '2026-05-15 22:02:25', '2026-05-15 22:02:25', null);
INSERT INTO `sa_system_login_log` VALUES ('478', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 22:50:32', null, null, null, '2026-05-15 22:50:32', '2026-05-15 22:50:32', null);
INSERT INTO `sa_system_login_log` VALUES ('479', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 22:51:12', null, null, null, '2026-05-15 22:51:12', '2026-05-15 22:51:12', null);
INSERT INTO `sa_system_login_log` VALUES ('480', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 22:52:08', null, null, null, '2026-05-15 22:52:08', '2026-05-15 22:52:08', null);
INSERT INTO `sa_system_login_log` VALUES ('481', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 22:54:01', null, null, null, '2026-05-15 22:54:01', '2026-05-15 22:54:01', null);
INSERT INTO `sa_system_login_log` VALUES ('482', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 23:16:58', null, null, null, '2026-05-15 23:16:58', '2026-05-15 23:16:58', null);
INSERT INTO `sa_system_login_log` VALUES ('483', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-15 23:25:40', null, null, null, '2026-05-15 23:25:40', '2026-05-15 23:25:40', null);
INSERT INTO `sa_system_login_log` VALUES ('484', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 16:55:56', null, null, null, '2026-05-16 16:55:56', '2026-05-16 16:55:56', null);
INSERT INTO `sa_system_login_log` VALUES ('485', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 18:45:26', null, null, null, '2026-05-16 18:45:26', '2026-05-16 18:45:26', null);
INSERT INTO `sa_system_login_log` VALUES ('486', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 18:45:52', null, null, null, '2026-05-16 18:45:52', '2026-05-16 18:45:52', null);
INSERT INTO `sa_system_login_log` VALUES ('487', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 18:53:37', null, null, null, '2026-05-16 18:53:37', '2026-05-16 18:53:37', null);
INSERT INTO `sa_system_login_log` VALUES ('488', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 18:53:58', null, null, null, '2026-05-16 18:53:58', '2026-05-16 18:53:58', null);
INSERT INTO `sa_system_login_log` VALUES ('489', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 19:00:54', null, null, null, '2026-05-16 19:00:54', '2026-05-16 19:00:54', null);
INSERT INTO `sa_system_login_log` VALUES ('490', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 19:01:19', null, null, null, '2026-05-16 19:01:19', '2026-05-16 19:01:19', null);
INSERT INTO `sa_system_login_log` VALUES ('491', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 19:02:30', null, null, null, '2026-05-16 19:02:30', '2026-05-16 19:02:30', null);
INSERT INTO `sa_system_login_log` VALUES ('492', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 19:03:54', null, null, null, '2026-05-16 19:03:54', '2026-05-16 19:03:54', null);
INSERT INTO `sa_system_login_log` VALUES ('493', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 19:05:43', null, null, null, '2026-05-16 19:05:43', '2026-05-16 19:05:43', null);
INSERT INTO `sa_system_login_log` VALUES ('494', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 19:06:44', null, null, null, '2026-05-16 19:06:44', '2026-05-16 19:06:44', null);
INSERT INTO `sa_system_login_log` VALUES ('495', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 20:39:58', null, null, null, '2026-05-16 20:39:58', '2026-05-16 20:39:58', null);
INSERT INTO `sa_system_login_log` VALUES ('496', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 20:40:57', null, null, null, '2026-05-16 20:40:57', '2026-05-16 20:40:57', null);
INSERT INTO `sa_system_login_log` VALUES ('497', 'devwang', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 20:41:40', null, null, null, '2026-05-16 20:41:40', '2026-05-16 20:41:40', null);
INSERT INTO `sa_system_login_log` VALUES ('498', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 20:42:35', null, null, null, '2026-05-16 20:42:35', '2026-05-16 20:42:35', null);
INSERT INTO `sa_system_login_log` VALUES ('499', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-16 21:33:30', null, null, null, '2026-05-16 21:33:30', '2026-05-16 21:33:30', null);
INSERT INTO `sa_system_login_log` VALUES ('500', 'admin', '127.0.0.1', '', 'Windows', 'Chrome', '1', '登录成功', '2026-05-17 09:28:27', null, null, null, '2026-05-17 09:28:27', '2026-05-17 09:28:27', null);

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
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='菜单权限表';

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
INSERT INTO `sa_system_menu` VALUES ('19', '0', '官方文档', 'Document', '', '4', '', '', null, 'ri:file-copy-2-fill', '102', 'https://v3.phpframe.org', '1', '2', '2', '2', '2', '0', null, '0', '', '1', '1', '2026-01-01 00:00:00', '2026-05-16 18:00:47', null);
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
INSERT INTO `sa_system_menu` VALUES ('81', '80', '代码生成', 'Code', '', '2', 'code', '/tool/code', null, 'ri:code-s-slash-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-01-01 00:00:00', '2026-04-26 17:08:04', null);
INSERT INTO `sa_system_menu` VALUES ('82', '80', '定时任务', 'Crontab', '', '2', 'crontab', '/tool/crontab', null, 'ri:time-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('83', '82', '数据列表', '', 'tool:crontab:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('84', '82', '管理', '', 'tool:crontab:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('85', '82', '运行任务', '', 'tool:crontab:run', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('86', '81', '数据列表', '', 'tool:code:index', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('87', '81', '管理', '', 'tool:code:edit', '3', '', '', null, '', '100', '', '2', '2', '2', '2', '2', '0', null, '1', null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_menu` VALUES ('88', '80', '插件市场', 'Plugin', '', '2', '/plugin', '/system/plugin/index', null, 'ri:apps-2-ai-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-01-01 00:00:00', '2026-05-16 17:06:49', null);
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
INSERT INTO `sa_system_menu` VALUES ('105', '0', '文章管理', 'Article', '', '1', 'article', '', null, 'ri:book-line', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-23 22:18:18', '2026-04-26 17:07:50', null);
INSERT INTO `sa_system_menu` VALUES ('106', '105', '文章列表', 'ArticleList', '', '2', '/article/index', '/article', null, 'ri:code-block', '100', '', '2', '2', '2', '2', '2', '0', null, '1', '', '1', '1', '2026-04-23 22:19:39', '2026-04-26 17:08:18', null);
INSERT INTO `sa_system_menu` VALUES ('173', '1', 'HRM看板', 'Hrm', '', '2', 'hrm', '/dashboard/hrm', null, 'ri:team-line', '100', null, '2', '2', '2', '2', '2', '0', null, '1', '人力资源看板', '1', '1', '2026-05-06 23:15:10', '2026-05-06 23:15:10', null);
INSERT INTO `sa_system_menu` VALUES ('183', '1', '赞助支持', 'DashboardSponsor', '', '2', 'sponsor', '/dashboard/sponsor', null, 'ri:hand-heart-line', '110', null, '2', '2', '2', '2', '2', '0', null, '1', '项目赞助说明页', '1', '1', '2026-05-16 21:08:42', '2026-05-16 21:08:42', null);

-- ----------------------------
-- Table structure for `sa_system_oper_log`
-- ----------------------------
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
) ENGINE=InnoDB AUTO_INCREMENT=1285 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='操作日志表';

-- ----------------------------
-- Records of sa_system_oper_log
-- ----------------------------
INSERT INTO `sa_system_oper_log` VALUES ('1134', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":24,\"instance_name\":\"冷月如霜 发起 [指定表单]\",\"form_data\":{\"item_name\":\"345345\",\"quantity\":4,\"purchaseMoney\":333,\"expected_date\":\"2026-05-29\",\"remark\":\"\"},\"approver_assignments\":{\"EKO8K71\":[1]},\"copyer_assignments\":[]}', '168.76', null, '1', null, '2026-05-14 23:25:32', '2026-05-14 23:25:32', null);
INSERT INTO `sa_system_oper_log` VALUES ('1135', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]}}}}', '66.5', null, '1', null, '2026-05-14 23:50:38', '2026-05-14 23:50:38', null);
INSERT INTO `sa_system_oper_log` VALUES ('1136', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":50,\"occur_date\":\"2026-04-30\",\"remark\":\"11111\"},\"approver_assignments\":{\"L9AX181\":[1]},\"copyer_assignments\":[]}', '94.62', null, '1', null, '2026-05-14 23:50:54', '2026-05-14 23:50:54', null);
INSERT INTO `sa_system_oper_log` VALUES ('1137', 'admin', 'system', 'DELETE', '/api/flow/delegate/delete/1', '', '127.0.0.1', '', '[]', '29.61', null, '1', null, '2026-05-14 23:51:18', '2026-05-14 23:51:18', null);
INSERT INTO `sa_system_oper_log` VALUES ('1138', 'admin', 'system', 'POST', '/api/flow/instance/change-signer', '', '127.0.0.1', '', '{\"instance_id\":118,\"from_user_id\":123,\"to_user_id\":1,\"opinion\":\"\"}', '37.98', null, '1', null, '2026-05-14 23:51:30', '2026-05-14 23:51:30', null);
INSERT INTO `sa_system_oper_log` VALUES ('1139', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":198,\"approve_result\":1,\"approve_opinion\":\"ok\",\"attachment_urls\":[],\"copy_user_ids\":[],\"notify_channels\":[\"site\"]}', '90.2', null, '1', null, '2026-05-14 23:51:49', '2026-05-14 23:51:49', null);
INSERT INTO `sa_system_oper_log` VALUES ('1140', 'admin', 'system', 'PUT', '/api/flow/message/read/2', '', '127.0.0.1', '', '[]', '32.94', null, '1', null, '2026-05-14 23:52:06', '2026-05-14 23:52:06', null);
INSERT INTO `sa_system_oper_log` VALUES ('1141', 'admin', 'system', 'PUT', '/api/flow/message/read/1', '', '127.0.0.1', '', '[]', '36.16', null, '1', null, '2026-05-14 23:52:21', '2026-05-14 23:52:21', null);
INSERT INTO `sa_system_oper_log` VALUES ('1142', 'admin', 'system', 'PUT', '/api/flow/message/read-all', '', '127.0.0.1', '', '[]', '5.88', null, '1', null, '2026-05-14 23:52:36', '2026-05-14 23:52:36', null);
INSERT INTO `sa_system_oper_log` VALUES ('1143', 'admin', 'system', 'POST', '/api/flow/instance/copy', '', '127.0.0.1', '', '{\"instance_id\":117,\"target_user_ids\":[1],\"opinion\":\"超送\"}', '64.27', null, '1', null, '2026-05-14 23:54:06', '2026-05-14 23:54:06', null);
INSERT INTO `sa_system_oper_log` VALUES ('1144', 'admin', 'system', 'PUT', '/api/system/menu/update/179', '', '127.0.0.1', '', '{\"id\":179,\"parent_id\":138,\"type\":2,\"component\":\"/flow/message/index\",\"name\":\"消息中心\",\"slug\":\"flow:message:index\",\"path\":\"/flow/message\",\"icon\":\"ri:message-ai-3-line\",\"code\":\"FlowMessage\",\"remark\":\"流程消息中心\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":1,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":9,\"status\":1}', '42.84', null, '1', null, '2026-05-14 23:56:09', '2026-05-14 23:56:09', null);
INSERT INTO `sa_system_oper_log` VALUES ('1145', 'admin', 'system', 'POST', '/api/flow/instance/change-signer', '', '127.0.0.1', '', '{\"instance_id\":109,\"from_user_id\":100,\"to_user_id\":123,\"opinion\":\"\"}', '55.07', null, '1', null, '2026-05-15 00:01:55', '2026-05-15 00:01:55', null);
INSERT INTO `sa_system_oper_log` VALUES ('1146', 'admin', 'system', 'POST', '/api/flow/instance/change-signer', '', '127.0.0.1', '', '{\"instance_id\":116,\"from_user_id\":119,\"to_user_id\":123,\"opinion\":\"\"}', '42.4', null, '1', null, '2026-05-15 00:02:22', '2026-05-15 00:02:22', null);
INSERT INTO `sa_system_oper_log` VALUES ('1147', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":30,\"occur_date\":\"2026-05-01\",\"remark\":\"111\"},\"approver_assignments\":{\"L9AX181\":[123,1]},\"copyer_assignments\":[]}', '121.24', null, '1', null, '2026-05-15 00:03:00', '2026-05-15 00:03:00', null);
INSERT INTO `sa_system_oper_log` VALUES ('1148', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":30,\"occur_date\":\"2026-05-06\",\"remark\":\"23123\"},\"approver_assignments\":{\"L9AX181\":[1,2]},\"copyer_assignments\":[]}', '89.3', null, '1', null, '2026-05-15 00:12:20', '2026-05-15 00:12:20', null);
INSERT INTO `sa_system_oper_log` VALUES ('1149', 'admin', 'system', 'POST', '/api/flow/instance/add-signer', '', '127.0.0.1', '', '{\"instance_id\":120,\"target_user_id\":123,\"opinion\":\"\"}', '47.84', null, '1', null, '2026-05-15 00:14:14', '2026-05-15 00:14:14', null);
INSERT INTO `sa_system_oper_log` VALUES ('1150', 'admin', 'system', 'POST', '/api/flow/instance/add-signer', '', '127.0.0.1', '', '{\"instance_id\":120,\"target_user_id\":100,\"opinion\":\"\"}', '33.76', null, '1', null, '2026-05-15 00:14:34', '2026-05-15 00:14:34', null);
INSERT INTO `sa_system_oper_log` VALUES ('1151', 'admin', 'system', 'PUT', '/api/system/user/menus/100', '', '127.0.0.1', '', '{\"menu_ids\":[1,2,74,21,93,94,173,3,4,20,22,23,24,25,26,27,28,5,29,30,31,32,33,97,6,34,35,36,37,38,39,7,41,42,43,44,45,46,47,8,48,49,50,51,52,18,53,54,55,98,99,100,101,102,103,138,172,139,151,152,153,140,141,160,161,162,163,142,164,165,168,169,170,143,166,167,171,174,175,176,177,178,179,180,181,144,145,146,147,148,149,150]}', '3240.31', null, '1', null, '2026-05-15 00:15:37', '2026-05-15 00:15:37', null);
INSERT INTO `sa_system_oper_log` VALUES ('1152', '', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"all\",\"role_ids\":[],\"user_ids\":[],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]}}}}', '1.8', null, '0', null, '2026-05-15 00:16:11', '2026-05-15 00:16:11', null);
INSERT INTO `sa_system_oper_log` VALUES ('1153', '', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"all\",\"role_ids\":[],\"user_ids\":[],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]}}}}', '1.23', null, '0', null, '2026-05-15 00:16:12', '2026-05-15 00:16:12', null);
INSERT INTO `sa_system_oper_log` VALUES ('1154', '', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"all\",\"role_ids\":[],\"user_ids\":[],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]}}}}', '1.28', null, '0', null, '2026-05-15 00:16:12', '2026-05-15 00:16:12', null);
INSERT INTO `sa_system_oper_log` VALUES ('1155', '', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"all\",\"role_ids\":[],\"user_ids\":[],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]}}}}', '1.4', null, '0', null, '2026-05-15 00:16:13', '2026-05-15 00:16:13', null);
INSERT INTO `sa_system_oper_log` VALUES ('1156', 'devwang', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":27,\"instance_name\":\"王程序员 发起 [多条件]\",\"form_data\":{\"textarea88599\":\"24234234234\",\"radio27309\":3,\"select53353\":2,\"timerange27142\":null,\"select35490\":1},\"approver_assignments\":{\"DCKKP71\":[100]},\"copyer_assignments\":{\"KYGKP71\":[123],\"G0LKP71\":[1]}}', '176.11', null, '100', null, '2026-05-15 00:17:01', '2026-05-15 00:17:01', null);
INSERT INTO `sa_system_oper_log` VALUES ('1157', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":203,\"approve_result\":1,\"approve_opinion\":\"ok\",\"attachment_urls\":[],\"copy_user_ids\":[],\"notify_channels\":[\"site\"]}', '63.9', null, '1', null, '2026-05-15 00:18:04', '2026-05-15 00:18:04', null);
INSERT INTO `sa_system_oper_log` VALUES ('1158', 'admin', 'system', 'PUT', '/api/flow/message/read-all', '', '127.0.0.1', '', '{\"message_type\":\"\"}', '47.38', null, '1', null, '2026-05-15 00:19:42', '2026-05-15 00:19:42', null);
INSERT INTO `sa_system_oper_log` VALUES ('1159', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2,18,21],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]}}}}', '37.61', null, '1', null, '2026-05-15 07:40:09', '2026-05-15 07:40:09', null);
INSERT INTO `sa_system_oper_log` VALUES ('1160', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":10,\"occur_date\":\"2026-05-02\",\"remark\":\"\"},\"approver_assignments\":{\"L9AX181\":[1]},\"copyer_assignments\":[]}', '202.96', null, '1', null, '2026-05-15 07:40:26', '2026-05-15 07:40:26', null);
INSERT INTO `sa_system_oper_log` VALUES ('1161', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":208,\"approve_result\":3,\"approve_opinion\":\"不支持\",\"return_type\":\"current_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '114.17', null, '1', null, '2026-05-15 07:40:49', '2026-05-15 07:40:49', null);
INSERT INTO `sa_system_oper_log` VALUES ('1162', 'admin', 'system', 'POST', '/api/flow/instance/resubmit', '', '127.0.0.1', '', '{\"instance_id\":122,\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":40,\"occur_date\":\"2026-05-02\",\"remark\":\"\"}}', '64.96', null, '1', null, '2026-05-15 07:41:38', '2026-05-15 07:41:38', null);
INSERT INTO `sa_system_oper_log` VALUES ('1163', 'admin', 'system', 'POST', '/api/flow/task/add-sign', '', '127.0.0.1', '', '{\"task_id\":209,\"target_user_ids\":[100],\"approve_opinion\":\"\"}', '314.74', null, '1', null, '2026-05-15 07:42:01', '2026-05-15 07:42:01', null);
INSERT INTO `sa_system_oper_log` VALUES ('1164', 'admin', 'system', 'POST', '/api/flow/task/transfer', '', '127.0.0.1', '', '{\"task_id\":209,\"target_user_id\":100,\"approve_opinion\":\"\"}', '45.96', null, '1', null, '2026-05-15 07:42:58', '2026-05-15 07:42:58', null);
INSERT INTO `sa_system_oper_log` VALUES ('1165', 'admin', 'system', 'POST', '/api/flow/task/transfer', '', '127.0.0.1', '', '{\"task_id\":209,\"target_user_id\":10,\"approve_opinion\":\"\"}', '55.89', null, '1', null, '2026-05-15 07:43:07', '2026-05-15 07:43:07', null);
INSERT INTO `sa_system_oper_log` VALUES ('1166', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":30,\"occur_date\":\"2026-05-01\",\"remark\":\"aa\"},\"approver_assignments\":{\"L9AX181\":[123,1]},\"copyer_assignments\":[]}', '68.01', null, '1', null, '2026-05-15 07:45:21', '2026-05-15 07:45:21', null);
INSERT INTO `sa_system_oper_log` VALUES ('1167', 'admin', 'system', 'POST', '/api/flow/instance/copy', '', '127.0.0.1', '', '{\"instance_id\":123,\"target_user_ids\":[1],\"opinion\":\"\"}', '80.81', null, '1', null, '2026-05-15 07:47:48', '2026-05-15 07:47:48', null);
INSERT INTO `sa_system_oper_log` VALUES ('1168', 'admin', 'system', 'POST', '/api/flow/instance/remove-signer', '', '127.0.0.1', '', '{\"instance_id\":123,\"remove_user_id\":123,\"opinion\":\"\"}', '47.48', null, '1', null, '2026-05-15 07:49:02', '2026-05-15 07:49:02', null);
INSERT INTO `sa_system_oper_log` VALUES ('1169', 'admin', 'system', 'POST', '/api/flow/instance/add-signer', '', '127.0.0.1', '', '{\"instance_id\":123,\"target_user_id\":100,\"opinion\":\"\"}', '34.27', null, '1', null, '2026-05-15 07:49:30', '2026-05-15 07:49:30', null);
INSERT INTO `sa_system_oper_log` VALUES ('1170', 'admin', 'system', 'POST', '/api/flow/instance/change-signer', '', '127.0.0.1', '', '{\"instance_id\":123,\"from_user_id\":100,\"to_user_id\":10,\"opinion\":\"\"}', '38.75', null, '1', null, '2026-05-15 07:50:58', '2026-05-15 07:50:58', null);
INSERT INTO `sa_system_oper_log` VALUES ('1171', '', 'system', 'POST', '/api/core/switch-tenant', '', '127.0.0.1', '', '{\"tenant_id\":1}', '94.59', null, '0', null, '2026-05-15 07:51:40', '2026-05-15 07:51:40', null);
INSERT INTO `sa_system_oper_log` VALUES ('1172', 'admin', 'system', 'POST', '/api/flow/task/add-sign', '', '127.0.0.1', '', '{\"task_id\":212,\"target_user_ids\":[100],\"approve_opinion\":\"\"}', '64.75', null, '1', null, '2026-05-15 07:55:18', '2026-05-15 07:55:18', null);
INSERT INTO `sa_system_oper_log` VALUES ('1173', 'admin', 'system', 'POST', '/api/flow/instance/remove-signer', '', '127.0.0.1', '', '{\"instance_id\":123,\"remove_user_id\":100,\"opinion\":\"\"}', '35.74', null, '1', null, '2026-05-15 07:57:33', '2026-05-15 07:57:33', null);
INSERT INTO `sa_system_oper_log` VALUES ('1174', 'admin', 'system', 'POST', '/api/flow/task/add-sign', '', '127.0.0.1', '', '{\"task_id\":212,\"target_user_ids\":[100],\"approve_opinion\":\"\"}', '48.07', null, '1', null, '2026-05-15 07:59:41', '2026-05-15 07:59:41', null);
INSERT INTO `sa_system_oper_log` VALUES ('1175', 'admin', 'system', 'POST', '/api/flow/instance/remove-signer', '', '127.0.0.1', '', '{\"instance_id\":123,\"remove_user_id\":100,\"opinion\":\"\"}', '34.8', null, '1', null, '2026-05-15 08:03:49', '2026-05-15 08:03:49', null);
INSERT INTO `sa_system_oper_log` VALUES ('1176', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2,18,21],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\",\"remove_sign\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]}}}}', '42.43', null, '1', null, '2026-05-15 08:10:27', '2026-05-15 08:10:27', null);
INSERT INTO `sa_system_oper_log` VALUES ('1177', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":40,\"occur_date\":\"2026-05-01\",\"remark\":\"\"},\"approver_assignments\":{\"L9AX181\":[119,120,1]},\"copyer_assignments\":[]}', '66.23', null, '1', null, '2026-05-15 08:10:45', '2026-05-15 08:10:45', null);
INSERT INTO `sa_system_oper_log` VALUES ('1178', 'admin', 'system', 'POST', '/api/flow/instance/remove-signer', '', '127.0.0.1', '', '{\"instance_id\":124,\"remove_user_id\":1,\"opinion\":\"\"}', '56.25', null, '1', null, '2026-05-15 08:11:00', '2026-05-15 08:11:00', null);
INSERT INTO `sa_system_oper_log` VALUES ('1179', 'admin', 'system', 'PUT', '/api/flow/message/read/17', '', '127.0.0.1', '', '[]', '32.6', null, '1', null, '2026-05-15 08:11:28', '2026-05-15 08:11:28', null);
INSERT INTO `sa_system_oper_log` VALUES ('1180', 'admin', 'system', 'PUT', '/api/system/menu/update/110', '', '127.0.0.1', '', '{\"id\":110,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"流程设置\",\"slug\":\"template\",\"path\":\"/template\",\"icon\":\"ri:home-gear-fill\",\"code\":\"Template\",\"remark\":\"流程设置\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":101,\"status\":1}', '42.37', null, '1', null, '2026-05-15 18:19:43', '2026-05-15 18:19:43', null);
INSERT INTO `sa_system_oper_log` VALUES ('1181', 'admin', 'system', 'PUT', '/api/system/menu/update/19', '', '127.0.0.1', '', '{\"id\":19,\"parent_id\":0,\"type\":4,\"component\":\"\",\"name\":\"官方文档\",\"slug\":\"\",\"path\":\"\",\"icon\":\"ri:file-copy-2-fill\",\"code\":\"Document\",\"remark\":\"\",\"link_url\":\"https://saithink.top\",\"is_iframe\":1,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":102,\"status\":0}', '25.86', null, '1', null, '2026-05-15 18:19:57', '2026-05-15 18:19:57', null);
INSERT INTO `sa_system_oper_log` VALUES ('1182', 'admin', 'system', 'PUT', '/api/system/user/menus/100', '', '127.0.0.1', '', '{\"menu_ids\":[1,2,74,21,93,94,173,3,4,20,22,23,24,25,26,27,28,5,29,30,31,32,33,97,6,34,35,36,37,38,39,7,41,42,43,44,45,46,47,8,48,49,50,51,52,18,53,54,55,98,99,100,101,102,103,138,172,141,160,161,162,163,142,164,165,168,169,170,143,166,167,171,179,180,181,144,145,146,147,148,149,182,150,110,139,151,152,153,140,174,175,176,177,178]}', '3290.73', null, '1', null, '2026-05-15 18:39:40', '2026-05-15 18:39:40', null);
INSERT INTO `sa_system_oper_log` VALUES ('1183', 'admin', 'system', 'PUT', '/api/system/user/menus/10', '', '127.0.0.1', '', '{\"menu_ids\":[2,74,21,93,94,3,4,20,22,23,24,25,26,27,28,5,29,30,31,32,33,97,6,34,35,36,37,38,39,7,41,42,43,44,45,46,47,8,48,49,50,51,52,18,53,54,55,98,99,100,101,102,103,138,172,141,160,161,162,163,142,164,165,168,169,170,143,166,167,171,179,180,181,144,145,146,147,148,149,182,150,110,139,151,152,153,140,174,175,176,177,178,1]}', '3264.62', null, '1', null, '2026-05-15 18:40:06', '2026-05-15 18:40:06', null);
INSERT INTO `sa_system_oper_log` VALUES ('1184', 'devwang', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":9,\"instance_name\":\"王程序员 发起 [8888]\",\"form_data\":{\"leaveHour\":\"asdasdasd\",\"textarea40375\":\"asdasdasd\",\"purchaseMoney\":\"\"},\"approver_assignments\":{\"PYTC071\":[1,123],\"EWCC071\":[]},\"copyer_assignments\":[]}', '138.93', null, '100', null, '2026-05-15 18:41:08', '2026-05-15 18:41:08', null);
INSERT INTO `sa_system_oper_log` VALUES ('1185', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":220,\"approve_result\":1,\"approve_opinion\":\"hhh\",\"attachment_urls\":[],\"copy_user_ids\":[],\"notify_channels\":[\"site\"]}', '55.37', null, '1', null, '2026-05-15 19:03:40', '2026-05-15 19:03:40', null);
INSERT INTO `sa_system_oper_log` VALUES ('1186', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":212,\"approve_result\":1,\"approve_opinion\":\"ok\",\"attachment_urls\":[],\"copy_user_ids\":[],\"notify_channels\":[\"site\"]}', '64.16', null, '1', null, '2026-05-15 22:43:04', '2026-05-15 22:43:04', null);
INSERT INTO `sa_system_oper_log` VALUES ('1187', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":212,\"approve_result\":1,\"approve_opinion\":\"ok\",\"attachment_urls\":[],\"copy_user_ids\":[],\"notify_channels\":[\"site\"]}', '7.82', null, '1', null, '2026-05-15 22:43:08', '2026-05-15 22:43:08', null);
INSERT INTO `sa_system_oper_log` VALUES ('1188', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":196,\"approve_result\":1,\"approve_opinion\":\"ok\",\"attachment_urls\":[],\"copy_user_ids\":[],\"notify_channels\":[\"site\"]}', '97.46', null, '1', null, '2026-05-15 22:43:22', '2026-05-15 22:43:22', null);
INSERT INTO `sa_system_oper_log` VALUES ('1189', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":3,\"instance_name\":\"冷月如霜 发起 [出差审批]\",\"form_data\":{\"input12931\":\"124141\",\"switch96070\":true,\"input23031\":\"141241241\",\"textarea21654\":\"124124\",\"input113152\":\"124124\",\"checkbox63174\":[2],\"input40240\":\"14124124\",\"input78584\":\"124124\",\"timerange47503\":[\"22:44:17\",\"23:44:17\"],\"slider54714\":30,\"textarea64794\":\"1241241\"},\"approver_assignments\":{\"SIUMR61\":[1],\"DPWMR61\":[122]},\"copyer_assignments\":[]}', '137.45', null, '1', null, '2026-05-15 22:44:47', '2026-05-15 22:44:47', null);
INSERT INTO `sa_system_oper_log` VALUES ('1190', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2,18,21],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\",\"remove_sign\"],\"receivers\":[\"starter\",\"current_node_approvers\",\"all_approved\",\"transferee\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]}}}}', '44.65', null, '1', null, '2026-05-15 22:49:59', '2026-05-15 22:49:59', null);
INSERT INTO `sa_system_oper_log` VALUES ('1191', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":40,\"occur_date\":\"2026-05-15\",\"remark\":\"\"},\"approver_assignments\":{\"L9AX181\":[10,100]},\"copyer_assignments\":[]}', '70.89', null, '1', null, '2026-05-15 22:50:15', '2026-05-15 22:50:15', null);
INSERT INTO `sa_system_oper_log` VALUES ('1192', 'devwang', 'system', 'POST', '/api/flow/task/add-sign', '', '127.0.0.1', '', '{\"task_id\":224,\"target_user_ids\":[1],\"approve_opinion\":\"\"}', '157.5', null, '100', null, '2026-05-15 22:50:52', '2026-05-15 22:50:52', null);
INSERT INTO `sa_system_oper_log` VALUES ('1193', 'devwang', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":224,\"approve_result\":1,\"approve_opinion\":\"好的\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '7.67', null, '100', null, '2026-05-15 22:51:01', '2026-05-15 22:51:01', null);
INSERT INTO `sa_system_oper_log` VALUES ('1194', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":225,\"approve_result\":1,\"approve_opinion\":\"好的\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '152.96', null, '1', null, '2026-05-15 22:51:28', '2026-05-15 22:51:28', null);
INSERT INTO `sa_system_oper_log` VALUES ('1195', 'admin', 'system', 'PUT', '/api/flow/message/read/24', '', '127.0.0.1', '', '[]', '51.73', null, '1', null, '2026-05-15 22:58:02', '2026-05-15 22:58:02', null);
INSERT INTO `sa_system_oper_log` VALUES ('1196', 'admin', 'system', 'POST', '/api/system/plugin/install', '', '127.0.0.1', '', '{\"name\":\"bbs\",\"auto_install_dependencies\":true}', '122.41', null, '1', null, '2026-05-16 17:02:32', '2026-05-16 17:02:32', null);
INSERT INTO `sa_system_oper_log` VALUES ('1197', 'admin', 'system', 'POST', '/api/system/plugin/uninstall', '', '127.0.0.1', '', '{\"name\":\"bbs\"}', '29.7', null, '1', null, '2026-05-16 17:02:34', '2026-05-16 17:02:34', null);
INSERT INTO `sa_system_oper_log` VALUES ('1198', 'admin', 'system', 'POST', '/api/system/plugin/install', '', '127.0.0.1', '', '{\"name\":\"blog\",\"auto_install_dependencies\":true}', '995.51', null, '1', null, '2026-05-16 17:02:37', '2026-05-16 17:02:37', null);
INSERT INTO `sa_system_oper_log` VALUES ('1199', 'admin', 'system', 'POST', '/api/system/plugin/install', '', '127.0.0.1', '', '{\"name\":\"blog\",\"auto_install_dependencies\":true}', '28.88', null, '1', null, '2026-05-16 17:02:41', '2026-05-16 17:02:41', null);
INSERT INTO `sa_system_oper_log` VALUES ('1200', 'admin', 'system', 'POST', '/api/system/plugin/uninstall', '', '127.0.0.1', '', '{\"name\":\"blog\"}', '145.27', null, '1', null, '2026-05-16 17:02:44', '2026-05-16 17:02:44', null);
INSERT INTO `sa_system_oper_log` VALUES ('1201', 'admin', 'system', 'PUT', '/api/system/menu/update/138', '', '127.0.0.1', '', '{\"id\":138,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"工作流管理\",\"slug\":\"flow\",\"path\":\"/flow\",\"icon\":\"ri:flow-chart\",\"code\":\"Flow\",\"remark\":\"工作流管理系统\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":100,\"status\":0}', '80.45', null, '1', null, '2026-05-16 17:06:24', '2026-05-16 17:06:24', null);
INSERT INTO `sa_system_oper_log` VALUES ('1202', 'admin', 'system', 'PUT', '/api/system/menu/update/110', '', '127.0.0.1', '', '{\"id\":110,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"流程设置\",\"slug\":\"template\",\"path\":\"/template\",\"icon\":\"ri:home-gear-fill\",\"code\":\"Template\",\"remark\":\"流程设置\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":101,\"status\":0}', '38.57', null, '1', null, '2026-05-16 17:06:27', '2026-05-16 17:06:27', null);
INSERT INTO `sa_system_oper_log` VALUES ('1203', 'admin', 'system', 'PUT', '/api/system/menu/update/88', '', '127.0.0.1', '', '{\"id\":88,\"parent_id\":80,\"type\":2,\"component\":\"/system/plugin/index\",\"name\":\"插件市场\",\"slug\":\"\",\"path\":\"/plugin\",\"icon\":\"ri:apps-2-ai-line\",\"code\":\"Plugin\",\"remark\":\"\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":100,\"status\":1}', '52.66', null, '1', null, '2026-05-16 17:06:49', '2026-05-16 17:06:49', null);
INSERT INTO `sa_system_oper_log` VALUES ('1204', 'admin', 'system', 'DELETE', '/api/system/attachment/delete/26', '', '127.0.0.1', '', '[]', '34.86', null, '1', null, '2026-05-16 17:09:28', '2026-05-16 17:09:28', null);
INSERT INTO `sa_system_oper_log` VALUES ('1205', 'admin', 'system', 'DELETE', '/api/system/attachment/delete/28', '', '127.0.0.1', '', '[]', '38.56', null, '1', null, '2026-05-16 17:09:31', '2026-05-16 17:09:31', null);
INSERT INTO `sa_system_oper_log` VALUES ('1206', 'admin', 'system', 'DELETE', '/api/system/attachment/delete/27', '', '127.0.0.1', '', '[]', '37.32', null, '1', null, '2026-05-16 17:09:33', '2026-05-16 17:09:33', null);
INSERT INTO `sa_system_oper_log` VALUES ('1207', '', 'system', 'POST', '/api/core/switch-tenant', '', '127.0.0.1', '', '{\"tenant_id\":2}', '221.87', null, '0', null, '2026-05-16 17:12:23', '2026-05-16 17:12:23', null);
INSERT INTO `sa_system_oper_log` VALUES ('1208', '', 'system', 'POST', '/api/core/switch-tenant', '', '127.0.0.1', '', '{\"tenant_id\":1}', '146.52', null, '0', null, '2026-05-16 17:12:27', '2026-05-16 17:12:27', null);
INSERT INTO `sa_system_oper_log` VALUES ('1209', 'admin', 'system', 'PUT', '/api/system/user/update/104', '', '127.0.0.1', '', '{\"id\":104,\"avatar\":\"http://127.0.0.1:8000/uploads/2026/03/28/69c7a3b2058589.44968839.webp\",\"username\":\"test2\",\"password_confirm\":\"\",\"realname\":\"test2\",\"dept_id\":12,\"phone\":\"\",\"email\":\"\",\"role_ids\":[2],\"post_ids\":[1],\"status\":1,\"gender\":\"2\",\"remark\":\"\"}', '559.07', null, '1', null, '2026-05-16 17:13:51', '2026-05-16 17:13:51', null);
INSERT INTO `sa_system_oper_log` VALUES ('1210', 'admin', 'system', 'PUT', '/api/system/menu/update/138', '', '127.0.0.1', '', '{\"id\":138,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"工作流管理\",\"slug\":\"flow\",\"path\":\"/flow\",\"icon\":\"ri:flow-chart\",\"code\":\"Flow\",\"remark\":\"工作流管理系统\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":100,\"status\":1}', '39.89', null, '1', null, '2026-05-16 18:00:31', '2026-05-16 18:00:31', null);
INSERT INTO `sa_system_oper_log` VALUES ('1211', 'admin', 'system', 'PUT', '/api/system/menu/update/110', '', '127.0.0.1', '', '{\"id\":110,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"流程设置\",\"slug\":\"template\",\"path\":\"/template\",\"icon\":\"ri:home-gear-fill\",\"code\":\"Template\",\"remark\":\"流程设置\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":101,\"status\":1}', '39.12', null, '1', null, '2026-05-16 18:00:35', '2026-05-16 18:00:35', null);
INSERT INTO `sa_system_oper_log` VALUES ('1212', 'admin', 'system', 'PUT', '/api/system/menu/update/19', '', '127.0.0.1', '', '{\"id\":19,\"parent_id\":0,\"type\":4,\"component\":\"\",\"name\":\"官方文档\",\"slug\":\"\",\"path\":\"\",\"icon\":\"ri:file-copy-2-fill\",\"code\":\"Document\",\"remark\":\"\",\"link_url\":\"https://v3.phpframe.org\",\"is_iframe\":1,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":102,\"status\":0}', '32.4', null, '1', null, '2026-05-16 18:00:47', '2026-05-16 18:00:47', null);
INSERT INTO `sa_system_oper_log` VALUES ('1213', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":{\"nodeId\":\"YX2BI81\",\"nodeName\":\"审核人2\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":2,\"signUpType\":2},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2,18,21],\"viewPage\":[0]},\"notifyConfig\":{\"enabled\":0,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\"},\"error\":false,\"property\":{\"afterSignUpWay\":2,\"signUpType\":2},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2,18,21],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\",\"remove_sign\"],\"receivers\":[\"starter\",\"current_node_approvers\",\"all_approved\",\"transfe...', '65.17', null, '1', null, '2026-05-16 18:01:54', '2026-05-16 18:01:54', null);
INSERT INTO `sa_system_oper_log` VALUES ('1214', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":60,\"occur_date\":\"2026-05-16\",\"remark\":\"1111\"},\"approver_assignments\":{\"L9AX181\":[1],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '170.91', null, '1', null, '2026-05-16 18:02:26', '2026-05-16 18:02:26', null);
INSERT INTO `sa_system_oper_log` VALUES ('1215', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":226,\"approve_result\":1,\"approve_opinion\":\"OK\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '83.13', null, '1', null, '2026-05-16 18:03:05', '2026-05-16 18:03:05', null);
INSERT INTO `sa_system_oper_log` VALUES ('1216', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":227,\"approve_result\":3,\"approve_opinion\":\"不支持\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '39.22', null, '1', null, '2026-05-16 18:03:27', '2026-05-16 18:03:27', null);
INSERT INTO `sa_system_oper_log` VALUES ('1217', 'admin', 'system', 'POST', '/api/flow/instance/resubmit', '', '127.0.0.1', '', '{\"instance_id\":128,\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":60,\"occur_date\":\"2026-05-16\",\"remark\":\"1111\"}}', '90.46', null, '1', null, '2026-05-16 18:04:00', '2026-05-16 18:04:00', null);
INSERT INTO `sa_system_oper_log` VALUES ('1218', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":228,\"approve_result\":1,\"approve_opinion\":\"OK\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '91.96', null, '1', null, '2026-05-16 18:04:53', '2026-05-16 18:04:53', null);
INSERT INTO `sa_system_oper_log` VALUES ('1219', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":229,\"approve_result\":1,\"approve_opinion\":\"已核实\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '43.15', null, '1', null, '2026-05-16 18:06:09', '2026-05-16 18:06:09', null);
INSERT INTO `sa_system_oper_log` VALUES ('1220', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":40,\"occur_date\":\"2026-05-16\",\"remark\":\"\"},\"approver_assignments\":{\"L9AX181\":[1],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '114.33', null, '1', null, '2026-05-16 18:18:09', '2026-05-16 18:18:09', null);
INSERT INTO `sa_system_oper_log` VALUES ('1221', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"L9AX181\",\"nodeName\":\"审核人1\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":{\"nodeId\":\"YX2BI81\",\"nodeName\":\"审核人2\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":2,\"signUpType\":2},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2,18,21],\"viewPage\":[0]},\"notifyConfig\":{\"enabled\":0,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\"},\"error\":false,\"property\":{\"afterSignUpWay\":2,\"signUpType\":2},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2,18,21],\"viewPage\":[0]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\",\"notifyConfig\":{\"enabled\":1,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\",\"add_sign\",\"remove_sign\"],\"receivers\":[\"starter\",\"current_node_approvers\",\"all_approved\",\"transfe...', '66.77', null, '1', null, '2026-05-16 18:36:26', '2026-05-16 18:36:26', null);
INSERT INTO `sa_system_oper_log` VALUES ('1222', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":50,\"occur_date\":\"2026-05-16\",\"remark\":\"111\"},\"approver_assignments\":{\"L9AX181\":[1],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '132.09', null, '1', null, '2026-05-16 18:36:48', '2026-05-16 18:36:48', null);
INSERT INTO `sa_system_oper_log` VALUES ('1223', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":231,\"approve_result\":1,\"approve_opinion\":\"通过\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '94.21', null, '1', null, '2026-05-16 18:36:58', '2026-05-16 18:36:58', null);
INSERT INTO `sa_system_oper_log` VALUES ('1224', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":232,\"approve_result\":3,\"approve_opinion\":\"无法核实\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '39.81', null, '1', null, '2026-05-16 18:37:16', '2026-05-16 18:37:16', null);
INSERT INTO `sa_system_oper_log` VALUES ('1225', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":60,\"occur_date\":\"2026-05-16\",\"remark\":\"\"},\"approver_assignments\":{\"L9AX181\":[1],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '95.44', null, '1', null, '2026-05-16 18:42:41', '2026-05-16 18:42:41', null);
INSERT INTO `sa_system_oper_log` VALUES ('1226', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":233,\"approve_result\":1,\"approve_opinion\":\"好的\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '100.69', null, '1', null, '2026-05-16 18:42:56', '2026-05-16 18:42:56', null);
INSERT INTO `sa_system_oper_log` VALUES ('1227', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":234,\"approve_result\":3,\"approve_opinion\":\"不支持\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '49.12', null, '1', null, '2026-05-16 18:43:16', '2026-05-16 18:43:16', null);
INSERT INTO `sa_system_oper_log` VALUES ('1228', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":40,\"occur_date\":\"2026-05-16\",\"remark\":\"\"},\"approver_assignments\":{\"L9AX181\":[100],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '77.35', null, '1', null, '2026-05-16 18:45:11', '2026-05-16 18:45:11', null);
INSERT INTO `sa_system_oper_log` VALUES ('1229', 'devwang', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":235,\"approve_result\":1,\"approve_opinion\":\"同意\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '129.74', null, '100', null, '2026-05-16 18:45:40', '2026-05-16 18:45:40', null);
INSERT INTO `sa_system_oper_log` VALUES ('1230', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":236,\"approve_result\":3,\"approve_opinion\":\"不支持\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '39.8', null, '1', null, '2026-05-16 18:46:34', '2026-05-16 18:46:34', null);
INSERT INTO `sa_system_oper_log` VALUES ('1231', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":50,\"occur_date\":\"2026-05-15\",\"remark\":\"\"},\"approver_assignments\":{\"L9AX181\":[1],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '116.95', null, '1', null, '2026-05-16 18:50:11', '2026-05-16 18:50:11', null);
INSERT INTO `sa_system_oper_log` VALUES ('1232', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":237,\"approve_result\":1,\"approve_opinion\":\"通过\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '75.54', null, '1', null, '2026-05-16 18:50:24', '2026-05-16 18:50:24', null);
INSERT INTO `sa_system_oper_log` VALUES ('1233', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":238,\"approve_result\":3,\"approve_opinion\":\"不支持\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '60.74', null, '1', null, '2026-05-16 18:51:03', '2026-05-16 18:51:03', null);
INSERT INTO `sa_system_oper_log` VALUES ('1234', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":239,\"approve_result\":1,\"approve_opinion\":\"OK\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '147.31', null, '1', null, '2026-05-16 18:51:26', '2026-05-16 18:51:26', null);
INSERT INTO `sa_system_oper_log` VALUES ('1235', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":240,\"approve_result\":3,\"approve_opinion\":\"不支持\",\"return_type\":\"starter\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '39.5', null, '1', null, '2026-05-16 18:51:45', '2026-05-16 18:51:45', null);
INSERT INTO `sa_system_oper_log` VALUES ('1236', 'admin', 'system', 'POST', '/api/flow/instance/resubmit', '', '127.0.0.1', '', '{\"instance_id\":133,\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":50,\"occur_date\":\"2026-05-15\",\"remark\":\"\"}}', '78.1', null, '1', null, '2026-05-16 18:52:16', '2026-05-16 18:52:16', null);
INSERT INTO `sa_system_oper_log` VALUES ('1237', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":241,\"approve_result\":1,\"approve_opinion\":\"OK\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '126.35', null, '1', null, '2026-05-16 18:52:28', '2026-05-16 18:52:28', null);
INSERT INTO `sa_system_oper_log` VALUES ('1238', 'admin', 'system', 'POST', '/api/flow/instance/add-signer', '', '127.0.0.1', '', '{\"instance_id\":133,\"target_user_id\":100,\"opinion\":\"\"}', '39.71', null, '1', null, '2026-05-16 18:52:59', '2026-05-16 18:52:59', null);
INSERT INTO `sa_system_oper_log` VALUES ('1239', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":242,\"approve_result\":1,\"approve_opinion\":\"同意\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '42.73', null, '1', null, '2026-05-16 18:53:10', '2026-05-16 18:53:10', null);
INSERT INTO `sa_system_oper_log` VALUES ('1240', 'devwang', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":243,\"approve_result\":3,\"approve_opinion\":\"不符合要求\",\"return_type\":\"current_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '32.64', null, '100', null, '2026-05-16 18:53:51', '2026-05-16 18:53:51', null);
INSERT INTO `sa_system_oper_log` VALUES ('1241', 'admin', 'system', 'PUT', '/api/flow/instance/cancel/133', '', '127.0.0.1', '', '[]', '154.4', null, '1', null, '2026-05-16 18:59:13', '2026-05-16 18:59:13', null);
INSERT INTO `sa_system_oper_log` VALUES ('1242', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":80,\"occur_date\":\"2026-05-15\",\"remark\":\"\"},\"approver_assignments\":{\"L9AX181\":[1],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '81.77', null, '1', null, '2026-05-16 18:59:49', '2026-05-16 18:59:49', null);
INSERT INTO `sa_system_oper_log` VALUES ('1243', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":244,\"approve_result\":1,\"approve_opinion\":\"okkkk\",\"attachment_urls\":[],\"copy_user_ids\":[],\"notify_channels\":[\"site\"]}', '72.25', null, '1', null, '2026-05-16 18:59:58', '2026-05-16 18:59:58', null);
INSERT INTO `sa_system_oper_log` VALUES ('1244', 'admin', 'system', 'POST', '/api/flow/task/add-sign', '', '127.0.0.1', '', '{\"task_id\":245,\"target_user_ids\":[100],\"approve_opinion\":\"\"}', '77.76', null, '1', null, '2026-05-16 19:00:24', '2026-05-16 19:00:24', null);
INSERT INTO `sa_system_oper_log` VALUES ('1245', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":245,\"approve_result\":1,\"approve_opinion\":\"通过\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '58.06', null, '1', null, '2026-05-16 19:00:45', '2026-05-16 19:00:45', null);
INSERT INTO `sa_system_oper_log` VALUES ('1246', 'devwang', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":246,\"approve_result\":3,\"approve_opinion\":\"需要补充材料\",\"return_type\":\"current_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '67.87', null, '100', null, '2026-05-16 19:01:11', '2026-05-16 19:01:11', null);
INSERT INTO `sa_system_oper_log` VALUES ('1247', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":230,\"approve_result\":1,\"approve_opinion\":\"OK2222222\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '76.24', null, '1', null, '2026-05-16 19:01:47', '2026-05-16 19:01:47', null);
INSERT INTO `sa_system_oper_log` VALUES ('1248', 'devwang', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":247,\"approve_result\":1,\"approve_opinion\":\"已核实\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '43.4', null, '100', null, '2026-05-16 19:02:56', '2026-05-16 19:02:56', null);
INSERT INTO `sa_system_oper_log` VALUES ('1249', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":1111,\"occur_date\":\"2026-05-15\",\"remark\":\"1111\"},\"approver_assignments\":{\"L9AX181\":[1],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '78.69', null, '1', null, '2026-05-16 19:04:21', '2026-05-16 19:04:21', null);
INSERT INTO `sa_system_oper_log` VALUES ('1250', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":249,\"approve_result\":1,\"approve_opinion\":\"2222\",\"attachment_urls\":[],\"copy_user_ids\":[],\"notify_channels\":[\"site\"]}', '79.88', null, '1', null, '2026-05-16 19:04:37', '2026-05-16 19:04:37', null);
INSERT INTO `sa_system_oper_log` VALUES ('1251', 'admin', 'system', 'POST', '/api/flow/instance/add-signer', '', '127.0.0.1', '', '{\"instance_id\":135,\"target_user_id\":100,\"opinion\":\"\"}', '41.01', null, '1', null, '2026-05-16 19:05:06', '2026-05-16 19:05:06', null);
INSERT INTO `sa_system_oper_log` VALUES ('1252', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":250,\"approve_result\":1,\"approve_opinion\":\"kkkkkkkkkkkkkkkkkkk\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '39.16', null, '1', null, '2026-05-16 19:05:18', '2026-05-16 19:05:18', null);
INSERT INTO `sa_system_oper_log` VALUES ('1253', 'devwang', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":251,\"approve_result\":3,\"approve_opinion\":\"需要补充材料\",\"return_type\":\"current_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '46.07', null, '100', null, '2026-05-16 19:06:10', '2026-05-16 19:06:10', null);
INSERT INTO `sa_system_oper_log` VALUES ('1254', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":248,\"approve_result\":3,\"approve_opinion\":\"不支持\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '63.33', null, '1', null, '2026-05-16 20:33:26', '2026-05-16 20:33:26', null);
INSERT INTO `sa_system_oper_log` VALUES ('1255', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":80,\"occur_date\":\"2026-05-01\",\"remark\":\"88888888888\"},\"approver_assignments\":{\"L9AX181\":[1],\"YX2BI81\":[1]},\"copyer_assignments\":[]}', '136.62', null, '1', null, '2026-05-16 20:37:33', '2026-05-16 20:37:33', null);
INSERT INTO `sa_system_oper_log` VALUES ('1256', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":253,\"approve_result\":1,\"approve_opinion\":\"通过.................\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '79.57', null, '1', null, '2026-05-16 20:37:48', '2026-05-16 20:37:48', null);
INSERT INTO `sa_system_oper_log` VALUES ('1257', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":254,\"approve_result\":3,\"approve_opinion\":\"有异议,返回审核人1\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '41.73', null, '1', null, '2026-05-16 20:38:24', '2026-05-16 20:38:24', null);
INSERT INTO `sa_system_oper_log` VALUES ('1258', 'admin', 'system', 'POST', '/api/flow/instance/resubmit', '', '127.0.0.1', '', '{\"instance_id\":136,\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":80,\"occur_date\":\"2026-05-01\",\"remark\":\"88888888888\"}}', '68.64', null, '1', null, '2026-05-16 20:38:30', '2026-05-16 20:38:30', null);
INSERT INTO `sa_system_oper_log` VALUES ('1259', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":255,\"approve_result\":1,\"approve_opinion\":\"同意，进入审核人2\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '90.7', null, '1', null, '2026-05-16 20:39:00', '2026-05-16 20:39:00', null);
INSERT INTO `sa_system_oper_log` VALUES ('1260', 'admin', 'system', 'POST', '/api/flow/instance/add-signer', '', '127.0.0.1', '', '{\"instance_id\":136,\"target_user_id\":100,\"opinion\":\"\"}', '41.82', null, '1', null, '2026-05-16 20:39:24', '2026-05-16 20:39:24', null);
INSERT INTO `sa_system_oper_log` VALUES ('1261', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":256,\"approve_result\":1,\"approve_opinion\":\"已核实\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '39.23', null, '1', null, '2026-05-16 20:39:36', '2026-05-16 20:39:36', null);
INSERT INTO `sa_system_oper_log` VALUES ('1262', 'devwang', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":257,\"approve_result\":3,\"approve_opinion\":\"无法核实,当前节点重新审核\",\"return_type\":\"current_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '81.04', null, '100', null, '2026-05-16 20:40:38', '2026-05-16 20:40:38', null);
INSERT INTO `sa_system_oper_log` VALUES ('1263', 'admin', 'system', 'POST', '/api/flow/instance/resubmit', '', '127.0.0.1', '', '{\"instance_id\":136,\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":80,\"occur_date\":\"2026-05-09\",\"remark\":\"88888888888\"}}', '86.41', null, '1', null, '2026-05-16 20:41:17', '2026-05-16 20:41:17', null);
INSERT INTO `sa_system_oper_log` VALUES ('1264', 'devwang', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":258,\"approve_result\":1,\"approve_opinion\":\"已核实\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '51.79', null, '100', null, '2026-05-16 20:42:10', '2026-05-16 20:42:10', null);
INSERT INTO `sa_system_oper_log` VALUES ('1265', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"QQCFG81\",\"nodeName\":\"网关\",\"nodeType\":2,\"nodeFrom\":\"\",\"nodeTo\":[],\"childNode\":{\"nodeId\":\"PRIFG81\",\"nodeName\":\"审核人3\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4],\"viewPage\":[0]},\"notifyConfig\":{\"enabled\":0,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\"},\"isDynamicCondition\":false,\"isParallel\":false,\"error\":true,\"property\":null,\"conditionNodes\":[{\"nodeId\":\"YRCFG81\",\"nodeName\":\"条件1\",\"nodeDisplayName\":\"报销金额 ≥ 1000\",\"nodeType\":3,\"nodeFrom\":\"\",\"nodeTo\":[],\"priorityLevel\":1,\"conditionList\":[{\"formId\":\"2\",\"columnId\":\"2\",\"showType\":\"1\",\"type\":2,\"showName\":\"报销金额\",\"optType\":\"5\",\"zdy1\":\"1000\",\"opt1\":\"\",\"zdy2\":\"\",\"opt2\":\"\",\"columnDbname\":\"amount\",\"columnType\":\"Double\",\"fixedDownBoxValue\":\"\"}],\"nodeApproveList\":[],\"error\":false,\"childNode\":{\"nodeId\":\"L9AX1...', '34.92', null, '1', null, '2026-05-16 20:43:27', '2026-05-16 20:43:27', null);
INSERT INTO `sa_system_oper_log` VALUES ('1266', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":60,\"occur_date\":\"2026-05-16\",\"remark\":\"2222\"},\"approver_assignments\":{\"PRIFG81\":[1],\"L9AX181\":[],\"NHFFG81\":[1]},\"copyer_assignments\":[]}', '96.99', null, '1', null, '2026-05-16 20:43:52', '2026-05-16 20:43:52', null);
INSERT INTO `sa_system_oper_log` VALUES ('1267', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":259,\"approve_result\":1,\"approve_opinion\":\"已核实\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '36.94', null, '1', null, '2026-05-16 20:44:08', '2026-05-16 20:44:08', null);
INSERT INTO `sa_system_oper_log` VALUES ('1268', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"QQCFG81\",\"nodeName\":\"网关\",\"nodeType\":2,\"nodeFrom\":\"\",\"nodeTo\":[],\"childNode\":{\"nodeId\":\"PRIFG81\",\"nodeName\":\"审核人3\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2],\"viewPage\":[0]},\"notifyConfig\":{\"enabled\":0,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\"},\"isDynamicCondition\":false,\"isParallel\":false,\"error\":true,\"property\":null,\"conditionNodes\":[{\"nodeId\":\"YRCFG81\",\"nodeName\":\"条件1\",\"nodeDisplayName\":\"报销金额 ≥ 1000\",\"nodeType\":3,\"nodeFrom\":\"\",\"nodeTo\":[],\"priorityLevel\":1,\"conditionList\":[{\"formId\":\"2\",\"columnId\":\"2\",\"showType\":\"1\",\"type\":2,\"showName\":\"报销金额\",\"optType\":\"5\",\"zdy1\":\"1000\",\"opt1\":\"\",\"zdy2\":\"\",\"opt2\":\"\",\"columnDbname\":\"amount\",\"columnType\":\"Double\",\"fixedDownBoxValue\":\"\"}],\"nodeApproveList\":[],\"error\":false,\"childNode\":{\"nodeId\":\"L9A...', '39.32', null, '1', null, '2026-05-16 20:44:42', '2026-05-16 20:44:42', null);
INSERT INTO `sa_system_oper_log` VALUES ('1269', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":1000,\"occur_date\":\"2026-05-15\",\"remark\":\"10000\"},\"approver_assignments\":{\"PRIFG81\":[1],\"L9AX181\":[1],\"NHFFG81\":[]},\"copyer_assignments\":[]}', '70.45', null, '1', null, '2026-05-16 20:45:07', '2026-05-16 20:45:07', null);
INSERT INTO `sa_system_oper_log` VALUES ('1270', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":261,\"approve_result\":3,\"approve_opinion\":\"无法核实\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '65.29', null, '1', null, '2026-05-16 20:45:22', '2026-05-16 20:45:22', null);
INSERT INTO `sa_system_oper_log` VALUES ('1271', 'admin', 'system', 'POST', '/api/flow/instance/resubmit', '', '127.0.0.1', '', '{\"instance_id\":138,\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"travel\",\"amount\":1000,\"occur_date\":\"2026-05-15\",\"remark\":\"10000\"}}', '91.08', null, '1', null, '2026-05-16 20:45:31', '2026-05-16 20:45:31', null);
INSERT INTO `sa_system_oper_log` VALUES ('1272', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":262,\"approve_result\":1,\"approve_opinion\":\"已核实\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '71.13', null, '1', null, '2026-05-16 20:45:40', '2026-05-16 20:45:40', null);
INSERT INTO `sa_system_oper_log` VALUES ('1273', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":263,\"approve_result\":1,\"approve_opinion\":\"已核实\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '36.15', null, '1', null, '2026-05-16 20:46:02', '2026-05-16 20:46:02', null);
INSERT INTO `sa_system_oper_log` VALUES ('1274', 'admin', 'system', 'PUT', '/api/flow/designer/template/29', '', '127.0.0.1', '', '{\"base_setting\":{\"template_code\":\"wuzishenq1\",\"template_name\":\"物资申请111\",\"category_id\":4,\"status\":1,\"remark\":\"\",\"icon\":\"/flowIcon/A23.png\",\"form_type\":2,\"fixed_form_key\":\"expense_claim\",\"start_permission\":{\"type\":\"user\",\"role_ids\":[],\"user_ids\":[123,122],\"dept_ids\":[]}},\"form_json\":[],\"process_json\":{\"confId\":35,\"nodeId\":\"Gb2\",\"nodeType\":1,\"nodeProperty\":1,\"nodePropertyName\":null,\"nodeFrom\":\"\",\"nodeFroms\":null,\"prevId\":[],\"batchStatus\":0,\"approvalStandard\":2,\"nodeName\":\"发起人\",\"nodeDisplayName\":\"所有人\",\"annotation\":null,\"isDeduplication\":0,\"isSignUp\":0,\"orderedNodeType\":null,\"remark\":\"\",\"isDel\":0,\"nodeTo\":[\"4FIHMN\"],\"property\":null,\"params\":null,\"buttons\":{\"startPage\":[],\"approvalPage\":[2],\"viewPage\":null},\"templateVos\":null,\"approveRemindVo\":null,\"conditionNodes\":[],\"childNode\":{\"nodeId\":\"QQCFG81\",\"nodeName\":\"网关\",\"nodeType\":2,\"nodeFrom\":\"\",\"nodeTo\":[],\"childNode\":{\"nodeId\":\"PRIFG81\",\"nodeName\":\"审核人3\",\"nodeType\":4,\"nodeFrom\":\"\",\"nodeTo\":[],\"setType\":7,\"directorLevel\":1,\"signType\":1,\"noHeaderAction\":2,\"childNode\":null,\"error\":false,\"property\":{\"afterSignUpWay\":1,\"signUpType\":1},\"buttons\":{\"startPage\":[1],\"approvalPage\":[3,4,2],\"viewPage\":[0]},\"notifyConfig\":{\"enabled\":0,\"channels\":[\"site\"],\"events\":[\"approve\",\"reject\",\"return\",\"transfer\"],\"receivers\":[\"starter\"],\"users\":[],\"roles\":[],\"usersMeta\":[],\"rolesMeta\":[]},\"nodeApproveList\":[],\"fallbackRoleId\":0,\"fallbackRoleName\":\"\",\"fallbackRoleCode\":\"\",\"nodeDisplayName\":\"发起人自选\"},\"isDynamicCondition\":false,\"isParallel\":false,\"error\":true,\"property\":null,\"conditionNodes\":[{\"nodeId\":\"YRCFG81\",\"nodeName\":\"条件1\",\"nodeDisplayName\":\"报销金额 ≥ 1000\",\"nodeType\":3,\"nodeFrom\":\"\",\"nodeTo\":[],\"priorityLevel\":1,\"conditionList\":[{\"formId\":\"2\",\"columnId\":\"2\",\"showType\":\"1\",\"type\":2,\"showName\":\"报销金额\",\"optType\":\"5\",\"zdy1\":\"1000\",\"opt1\":\"\",\"zdy2\":\"\",\"opt2\":\"\",\"columnDbname\":\"amount\",\"columnType\":\"Double\",\"fixedDownBoxValue\":\"\"}],\"nodeApproveList\":[],\"error\":false,\"childNode\":{\"nodeId\":\"L9A...', '41.71', null, '1', null, '2026-05-16 20:48:48', '2026-05-16 20:48:48', null);
INSERT INTO `sa_system_oper_log` VALUES ('1275', 'admin', 'system', 'POST', '/api/flow/instance/start', '', '127.0.0.1', '', '{\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":50,\"occur_date\":\"2026-05-16\",\"remark\":\"\"},\"approver_assignments\":{\"PRIFG81\":[1],\"L9AX181\":[],\"NHFFG81\":[1]},\"copyer_assignments\":[]}', '125.48', null, '1', null, '2026-05-16 20:49:09', '2026-05-16 20:49:09', null);
INSERT INTO `sa_system_oper_log` VALUES ('1276', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":264,\"approve_result\":3,\"approve_opinion\":\"不支持\",\"return_type\":\"previous_node\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '62.75', null, '1', null, '2026-05-16 20:49:30', '2026-05-16 20:49:30', null);
INSERT INTO `sa_system_oper_log` VALUES ('1277', 'admin', 'system', 'POST', '/api/flow/instance/resubmit', '', '127.0.0.1', '', '{\"instance_id\":139,\"template_id\":29,\"instance_name\":\"冷月如霜 发起 [物资申请111]\",\"form_data\":{\"category\":\"traffic\",\"amount\":50,\"occur_date\":\"2026-05-16\",\"remark\":\"\"}}', '80.63', null, '1', null, '2026-05-16 20:49:53', '2026-05-16 20:49:53', null);
INSERT INTO `sa_system_oper_log` VALUES ('1278', 'admin', 'system', 'POST', '/api/flow/task/approve', '', '127.0.0.1', '', '{\"task_id\":265,\"approve_result\":1,\"approve_opinion\":\"通过\",\"attachment_urls\":[],\"copy_user_ids\":[]}', '70.03', null, '1', null, '2026-05-16 20:50:06', '2026-05-16 20:50:06', null);
INSERT INTO `sa_system_oper_log` VALUES ('1279', 'admin', 'system', 'PUT', '/api/system/menu/update/138', '', '127.0.0.1', '', '{\"id\":138,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"工作流管理\",\"slug\":\"flow\",\"path\":\"/flow\",\"icon\":\"ri:flow-chart\",\"code\":\"Flow\",\"remark\":\"工作流管理系统\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":100,\"status\":0}', '46.4', null, '1', null, '2026-05-17 09:36:15', '2026-05-17 09:36:15', null);
INSERT INTO `sa_system_oper_log` VALUES ('1280', 'admin', 'system', 'PUT', '/api/system/menu/update/110', '', '127.0.0.1', '', '{\"id\":110,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"流程设置\",\"slug\":\"template\",\"path\":\"/template\",\"icon\":\"ri:home-gear-fill\",\"code\":\"Template\",\"remark\":\"流程设置\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":101,\"status\":0}', '47.73', null, '1', null, '2026-05-17 09:36:18', '2026-05-17 09:36:18', null);
INSERT INTO `sa_system_oper_log` VALUES ('1281', 'admin', 'system', 'PUT', '/api/system/menu/update/108', '', '127.0.0.1', '', '{\"id\":108,\"parent_id\":0,\"type\":2,\"component\":\"/flow/template/index\",\"name\":\"流程模板\",\"slug\":\"\",\"path\":\"/template\",\"icon\":\"ri:arrow-left-box-fill\",\"code\":\"FlowTemplate\",\"remark\":\"\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":100,\"status\":1}', '34.37', null, '1', null, '2026-05-17 09:36:39', '2026-05-17 09:36:39', null);
INSERT INTO `sa_system_oper_log` VALUES ('1282', 'admin', 'system', 'PUT', '/api/system/menu/update/108', '', '127.0.0.1', '', '{\"id\":108,\"parent_id\":0,\"type\":2,\"component\":\"/flow/template/index\",\"name\":\"流程模板\",\"slug\":\"\",\"path\":\"/template\",\"icon\":\"ri:arrow-left-box-fill\",\"code\":\"FlowTemplate\",\"remark\":\"\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":100,\"status\":0}', '34.51', null, '1', null, '2026-05-17 09:36:43', '2026-05-17 09:36:43', null);
INSERT INTO `sa_system_oper_log` VALUES ('1283', 'admin', 'system', 'PUT', '/api/system/menu/update/138', '', '127.0.0.1', '', '{\"id\":138,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"工作流管理\",\"slug\":\"flow\",\"path\":\"/flow\",\"icon\":\"ri:flow-chart\",\"code\":\"Flow\",\"remark\":\"工作流管理系统\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":100,\"status\":1}', '36.57', null, '1', null, '2026-05-17 09:36:48', '2026-05-17 09:36:48', null);
INSERT INTO `sa_system_oper_log` VALUES ('1284', 'admin', 'system', 'PUT', '/api/system/menu/update/110', '', '127.0.0.1', '', '{\"id\":110,\"parent_id\":0,\"type\":1,\"component\":\"/index/index\",\"name\":\"流程设置\",\"slug\":\"template\",\"path\":\"/template\",\"icon\":\"ri:home-gear-fill\",\"code\":\"Template\",\"remark\":\"流程设置\",\"link_url\":\"\",\"is_iframe\":2,\"is_keep_alive\":2,\"is_hidden\":2,\"is_fixed_tab\":2,\"is_full_page\":2,\"sort\":101,\"status\":1}', '69.37', null, '1', null, '2026-05-17 09:36:51', '2026-05-17 09:36:51', null);

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
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户表';

-- ----------------------------
-- Records of sa_system_user
-- ----------------------------
INSERT INTO `sa_system_user` VALUES ('1', 'admin', '$2y$10$wnixh48uDnaW/6D9EygDd.OHJK0vQY/4nHaTjMKBCVDBP2NiTatqS', '冷月如霜', '1', '/uploads/2026/03/28/69c7a15ee50ff0.57272058.jpg', 'fssphp@admin.com', '15888888888', 'FSSADMIN是兼具设计美学与高效开发的后台系统!11', 'statistics', '1', '1', '1', null, '2026-05-17 09:28:26', '127.0.0.1', '1', '1', '2026-01-01 00:00:00', '2026-05-17 09:28:27', null);
INSERT INTO `sa_system_user` VALUES ('2', 'martin', '$2y$10$L3sEraye7Q8XPenbx5M8JOW6j9BP1B/E2Iox6o6mVYB1MjML942N2', '刘炽平', '2', 'https://static.wandongli.com/static/pc/images/png.png', 'martin@163.com', '15888888888', null, 'work', '2', '0', '1', '', '2026-05-03 23:23:33', '127.0.0.1', '1', '1', '2026-01-01 00:00:00', '2026-05-03 23:23:33', null);
INSERT INTO `sa_system_user` VALUES ('3', 'allen', '$2y$10$H8d7riOjOiwPSopguEQ1fuKZz.fA0A54OvuzTqgJlbG1N3uOxEwM.', '张小龙', '1', 'https://static.wandongli.com/static/pc/images/png.png', '', '15888888888', null, 'work', '10', '0', '1', '', null, null, '1', '1', '2026-01-01 00:00:00', '2026-03-21 23:24:03', null);
INSERT INTO `sa_system_user` VALUES ('4', 'mark', '$2y$10$sY/4StKVV.N/8Ock8J8kdeIOK4jS4tAUoYjkzvB8Tzy0fLh.wA2KS', '任宇昕', '2', 'https://static.wandongli.com/static/pc/images/png.png', null, '15888888888', null, 'work', '11', '0', '1', null, null, null, '1', '1', '2026-01-01 00:00:00', '2026-03-21 23:20:40', null);
INSERT INTO `sa_system_user` VALUES ('5', 'dowson', '$2y$10$sY/4StKVV.N/8Ock8J8kdeIOK4jS4tAUoYjkzvB8Tzy0fLh.wA2KS', '汤道生', '1', 'https://static.wandongli.com/static/pc/images/png.png', null, '15888888888', null, 'work', '12', '0', '1', null, null, null, '1', '1', '2026-01-01 00:00:00', '2026-01-01 00:00:00', null);
INSERT INTO `sa_system_user` VALUES ('10', 'timi_boss', '$2y$10$a9S4v4i6ZpDEJQ1qgWsWnuifsq4dgGdVFZDearta9.mOz.IpcBWzK', '姚晓光', '2', 'https://static.wandongli.com/static/pc/images/png.png', '', '15888888888', null, 'work', '111', '1', '1', '', '2026-05-15 07:51:18', '127.0.0.1', '1', '1', '2026-01-01 00:00:00', '2026-05-15 07:51:18', null);
INSERT INTO `sa_system_user` VALUES ('100', 'devwang', '$2y$10$vbnOKDkbm9hIEd8JWXITpO5pcRtl/KTBqswojEkuaP7vdGB5tPzES', '王程序员', '1', '', '888888@qq.com', '15888888888', null, 'work', '12', '0', '1', '1', '2026-05-16 20:41:40', '127.0.0.1', '1', '1', '2026-01-01 00:00:00', '2026-05-16 20:41:40', null);
INSERT INTO `sa_system_user` VALUES ('101', 'devli', '$2y$10$2fuWT7n6E8kyG357FbNrouRyRvulmTYXpmFE71bHOH3PQAgpPItW.', '李策划', '1', '/uploads/2026/03/28/69c7a3a67d5e50.12092222.png', null, '15888881234', null, 'work', '2', '0', '1', '1111', null, null, '1', '1', '2026-01-01 00:00:00', '2026-03-28 17:50:57', null);
INSERT INTO `sa_system_user` VALUES ('102', 'test', '$2y$10$wnixh48uDnaW/6D9EygDd.OHJK0vQY/4nHaTjMKBCVDBP2NiTatqS', 'admin1', '1', '', '', '13512344567', null, 'work', '2', '0', '1', '', null, null, null, null, '2026-03-22 21:42:54', '2026-03-22 21:54:46', '2026-03-22 21:54:46');
INSERT INTO `sa_system_user` VALUES ('104', 'test2', '$2y$10$AQjV0REYZumtiT4sDpmKtODXLQtIL.9ralALUFKZGvrxKWcpHS9ii', 'test2', '2', 'http://127.0.0.1:8000/uploads/2026/03/28/69c7a3b2058589.44968839.webp', '', '', null, 'work', '2', '0', '1', '', '2026-04-22 22:07:17', '127.0.0.1', null, '1', '2026-03-22 21:58:24', '2026-05-16 17:13:50', null);
INSERT INTO `sa_system_user` VALUES ('105', 'test3', '$2y$10$LRECF.G/1CS14NMujqelpucMJ4kQX.OuLdk5D5DT3EjgS8.CnePHy', 'test3', '1', '/uploads/2026/03/28/69c7a3a9e6c335.87540530.png', '', '', null, 'work', '101', '0', '1', '1', null, null, null, '1', '2026-03-25 21:58:53', '2026-03-28 17:50:39', null);
INSERT INTO `sa_system_user` VALUES ('106', 'test3test3', '$2y$10$lDmHlmRsw/pPF4Yd/HcP7eE0xGpkqsNVVhC4TrAz31K7XckECcaQu', 'test3test3', '2', '/uploads/2026/03/28/69c7a3acd6b581.21163358.png', '', '', null, 'work', '102', '0', '1', '', null, null, null, '1', '2026-03-25 21:59:21', '2026-03-28 17:50:29', null);
INSERT INTO `sa_system_user` VALUES ('116', 'testtesttest', '$2y$10$0GmhEW3QFGv.pHltuKQIUuNKAaACDiDARMt3QLjRVZ0y.iolKIDyK', 'testtest', '1', '/uploads/2026/03/28/69c7a3b2058589.44968839.webp', '', '', null, 'work', '2', '0', '1', '', null, null, '1', '1', '2026-03-27 21:12:03', '2026-03-28 17:49:22', null);
INSERT INTO `sa_system_user` VALUES ('117', 'test1311', '$2y$10$wxvQ16EIyNTrarFQ3fM1Euvd1rzgeZPbEgMsRbpFdiWxC3pX9UOpW', '11231', '2', '', '', '', null, 'work', '2', '0', '1', '', null, null, '1', '1', '2026-03-27 21:29:06', '2026-03-28 23:09:35', '2026-03-28 23:09:35');
INSERT INTO `sa_system_user` VALUES ('118', 'ddd', '$2y$10$GA/p/o7CH5FiJJxPGh6pDuAMhGNUoKYiSWBRhQwdB30aacnVwqC.W', 'ddd', '', '/uploads/2026/03/28/69c7a3b48aab47.53936592.png', '', '', null, 'statistics', '1', '0', '1', '', null, null, '1', '1', '2026-03-27 22:32:06', '2026-03-28 19:07:29', null);
INSERT INTO `sa_system_user` VALUES ('119', 'zhangsanfeng', '$2y$10$JH3nWeQNuRYPvmRH.Wouauaxme3pmcdjJFjdoJ0bZxI6oO2kJyLma', '张三丰', '1', '/uploads/2026/03/28/69c7a3acd6b581.21163358.png', '', '', null, 'work', null, '0', '1', '', null, null, '1', '1', '2026-05-05 18:02:28', '2026-05-05 18:02:28', null);
INSERT INTO `sa_system_user` VALUES ('120', 'qiaofeng', '$2y$10$0G0vZyI3r69vFZtlMfXjx.8fGuDM.5zaUAujf.DxetToTSPwRPNbK', '乔峰', '1', '/uploads/2026/03/28/69c7a3a83f9f14.20042528.png', '', '', null, 'work', null, '0', '1', '', null, null, '1', '1', '2026-05-05 18:04:51', '2026-05-05 18:04:51', null);
INSERT INTO `sa_system_user` VALUES ('121', 'yangguo', '$2y$10$0ruAFh29.cyxm6vTubHHcOP6xanbRTXp86nUZMyeA7Xp2M/1XLDri', '杨过', '1', '/uploads/2026/03/28/69c7a3a1cae578.20110121.png', '', '', null, 'work', null, '0', '1', '', null, null, '1', '1', '2026-05-05 18:06:55', '2026-05-05 18:06:55', null);
INSERT INTO `sa_system_user` VALUES ('122', 'xiaolongnv', '$2y$10$nKMfot7pTjwYguTFq.4bz.rLWGPKYRr/jBJms3uY4qzm9e6ClCZIC', '小龙女', '2', '', '', '', null, 'work', null, '0', '1', '', null, null, '1', '1', '2026-05-05 18:07:27', '2026-05-07 23:37:58', null);
INSERT INTO `sa_system_user` VALUES ('123', 'guojing', '$2y$10$/CnyvA4nkw.Nx0SLTobdWOSgn1qOO6TEge6LELcz4sPNXc/.CyjvS', '郭靖', '1', '', '', '', null, 'work', null, '0', '1', '', null, null, '1', '1', '2026-05-05 18:08:14', '2026-05-07 23:37:46', null);

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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sa_system_user_dept
-- ----------------------------
INSERT INTO `sa_system_user_dept` VALUES ('5', '2', '100', '129', '1', '1', null, '2026-04-24 21:47:04');
INSERT INTO `sa_system_user_dept` VALUES ('6', '1', '100', '12', '1', '1', '2026-04-24 21:52:00', '2026-04-24 21:52:24');
INSERT INTO `sa_system_user_dept` VALUES ('7', '1', '1', '1', '1', '1', null, null);
INSERT INTO `sa_system_user_dept` VALUES ('8', '1', '10', '1', '1', '1', '2026-04-25 09:26:06', '2026-04-25 09:26:06');
INSERT INTO `sa_system_user_dept` VALUES ('9', '1', '119', '5', '1', '1', '2026-05-05 18:02:28', '2026-05-05 18:02:28');
INSERT INTO `sa_system_user_dept` VALUES ('10', '1', '120', '2', '1', '1', '2026-05-05 18:04:51', '2026-05-05 18:04:51');
INSERT INTO `sa_system_user_dept` VALUES ('11', '1', '121', '101', '1', '1', '2026-05-05 18:06:55', '2026-05-05 18:06:55');
INSERT INTO `sa_system_user_dept` VALUES ('12', '1', '122', '102', '1', '1', '2026-05-05 18:07:27', '2026-05-05 18:07:27');
INSERT INTO `sa_system_user_dept` VALUES ('13', '1', '123', '5', '1', '1', '2026-05-05 18:08:14', '2026-05-05 18:08:14');
INSERT INTO `sa_system_user_dept` VALUES ('14', '1', '104', '12', '1', '1', '2026-05-16 17:13:51', '2026-05-16 17:13:51');

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
) ENGINE=InnoDB AUTO_INCREMENT=789 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户菜单关联表';

-- ----------------------------
-- Records of sa_system_user_menu
-- ----------------------------
INSERT INTO `sa_system_user_menu` VALUES ('49', '118', '2', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('50', '118', '74', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('51', '118', '85', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('52', '118', '1', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('53', '118', '80', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
INSERT INTO `sa_system_user_menu` VALUES ('54', '118', '82', '1', '1', '1', '1', '2026-03-28 23:39:06', '2026-03-28 23:39:06', null);
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
INSERT INTO `sa_system_user_menu` VALUES ('229', '2', '1', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('230', '2', '2', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('231', '2', '74', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('232', '2', '21', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('233', '2', '93', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('234', '2', '94', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('235', '2', '3', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('236', '2', '4', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('237', '2', '20', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('238', '2', '22', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('239', '2', '23', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('240', '2', '24', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('241', '2', '25', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('242', '2', '26', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('243', '2', '27', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('244', '2', '28', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('245', '2', '5', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('246', '2', '29', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('247', '2', '30', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('248', '2', '31', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('249', '2', '32', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('250', '2', '33', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('251', '2', '97', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('252', '2', '6', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('253', '2', '34', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('254', '2', '35', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('255', '2', '36', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('256', '2', '37', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('257', '2', '38', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('258', '2', '39', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('259', '2', '7', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('260', '2', '41', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('261', '2', '42', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('262', '2', '43', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('263', '2', '44', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('264', '2', '45', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('265', '2', '46', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('266', '2', '47', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('267', '2', '8', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('268', '2', '48', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('269', '2', '49', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('270', '2', '50', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('271', '2', '51', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('272', '2', '52', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('273', '2', '18', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('274', '2', '53', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('275', '2', '54', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('276', '2', '55', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('277', '2', '98', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('278', '2', '99', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('279', '2', '100', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('280', '2', '101', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('281', '2', '102', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('282', '2', '103', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('283', '2', '10', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('284', '2', '15', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('285', '2', '64', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('286', '2', '65', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('287', '2', '16', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('288', '2', '66', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('289', '2', '67', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('290', '2', '17', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('291', '2', '68', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('292', '2', '69', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('293', '2', '11', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('294', '2', '72', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('295', '2', '73', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('296', '2', '70', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('297', '2', '71', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('298', '2', '12', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('299', '2', '56', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('300', '2', '57', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('301', '2', '13', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('302', '2', '58', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('303', '2', '59', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('304', '2', '14', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('305', '2', '60', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('306', '2', '61', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('307', '2', '62', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('308', '2', '63', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('309', '2', '104', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('310', '2', '80', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('311', '2', '81', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('312', '2', '86', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('313', '2', '87', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('314', '2', '82', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('315', '2', '83', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('316', '2', '84', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('317', '2', '85', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('318', '2', '88', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('319', '2', '105', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('320', '2', '106', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('321', '2', '138', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('322', '2', '139', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('323', '2', '151', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('324', '2', '152', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('325', '2', '153', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('326', '2', '140', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('327', '2', '141', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('328', '2', '160', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('329', '2', '161', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('330', '2', '162', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('331', '2', '163', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('332', '2', '142', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('333', '2', '164', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('334', '2', '165', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('335', '2', '143', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('336', '2', '166', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('337', '2', '144', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('338', '2', '145', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('339', '2', '146', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('340', '2', '147', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('341', '2', '148', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('342', '2', '149', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('343', '2', '150', '1', '1', '1', '1', '2026-05-03 23:23:18', '2026-05-03 23:23:18', null);
INSERT INTO `sa_system_user_menu` VALUES ('602', '100', '1', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('603', '100', '2', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('604', '100', '74', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('605', '100', '21', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('606', '100', '93', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('607', '100', '94', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('608', '100', '173', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('609', '100', '3', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('610', '100', '4', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('611', '100', '20', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('612', '100', '22', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('613', '100', '23', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('614', '100', '24', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('615', '100', '25', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('616', '100', '26', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('617', '100', '27', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('618', '100', '28', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('619', '100', '5', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('620', '100', '29', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('621', '100', '30', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('622', '100', '31', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('623', '100', '32', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('624', '100', '33', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('625', '100', '97', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('626', '100', '6', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('627', '100', '34', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('628', '100', '35', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('629', '100', '36', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('630', '100', '37', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('631', '100', '38', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('632', '100', '39', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('633', '100', '7', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('634', '100', '41', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('635', '100', '42', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('636', '100', '43', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('637', '100', '44', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('638', '100', '45', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('639', '100', '46', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('640', '100', '47', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('641', '100', '8', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('642', '100', '48', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('643', '100', '49', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('644', '100', '50', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('645', '100', '51', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('646', '100', '52', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('647', '100', '18', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('648', '100', '53', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('649', '100', '54', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('650', '100', '55', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('651', '100', '98', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('652', '100', '99', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('653', '100', '100', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('654', '100', '101', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('655', '100', '102', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('656', '100', '103', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('657', '100', '138', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('658', '100', '172', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('659', '100', '141', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('660', '100', '160', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('661', '100', '161', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('662', '100', '162', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('663', '100', '163', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('664', '100', '142', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('665', '100', '164', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('666', '100', '165', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('667', '100', '168', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('668', '100', '169', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('669', '100', '170', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('670', '100', '143', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('671', '100', '166', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('672', '100', '167', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('673', '100', '171', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('674', '100', '179', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('675', '100', '180', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('676', '100', '181', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('677', '100', '144', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('678', '100', '145', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('679', '100', '146', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('680', '100', '147', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('681', '100', '148', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('682', '100', '149', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('683', '100', '182', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('684', '100', '150', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('685', '100', '110', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('686', '100', '139', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('687', '100', '151', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('688', '100', '152', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('689', '100', '153', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('690', '100', '140', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('691', '100', '174', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('692', '100', '175', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('693', '100', '176', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('694', '100', '177', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('695', '100', '178', '1', '1', '1', '1', '2026-05-15 18:39:37', '2026-05-15 18:39:37', null);
INSERT INTO `sa_system_user_menu` VALUES ('696', '10', '2', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('697', '10', '1', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('698', '10', '74', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('699', '10', '21', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('700', '10', '93', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('701', '10', '94', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('702', '10', '3', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('703', '10', '4', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('704', '10', '20', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('705', '10', '22', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('706', '10', '23', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('707', '10', '24', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('708', '10', '25', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('709', '10', '26', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('710', '10', '27', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('711', '10', '28', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('712', '10', '5', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('713', '10', '29', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('714', '10', '30', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('715', '10', '31', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('716', '10', '32', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('717', '10', '33', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('718', '10', '97', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('719', '10', '6', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('720', '10', '34', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('721', '10', '35', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('722', '10', '36', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('723', '10', '37', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('724', '10', '38', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('725', '10', '39', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('726', '10', '7', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('727', '10', '41', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('728', '10', '42', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('729', '10', '43', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('730', '10', '44', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('731', '10', '45', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('732', '10', '46', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('733', '10', '47', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('734', '10', '8', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('735', '10', '48', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('736', '10', '49', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('737', '10', '50', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('738', '10', '51', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('739', '10', '52', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('740', '10', '18', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('741', '10', '53', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('742', '10', '54', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('743', '10', '55', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('744', '10', '98', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('745', '10', '99', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('746', '10', '100', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('747', '10', '101', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('748', '10', '102', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('749', '10', '103', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('750', '10', '138', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('751', '10', '172', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('752', '10', '141', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('753', '10', '160', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('754', '10', '161', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('755', '10', '162', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('756', '10', '163', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('757', '10', '142', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('758', '10', '164', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('759', '10', '165', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('760', '10', '168', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('761', '10', '169', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('762', '10', '170', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('763', '10', '143', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('764', '10', '166', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('765', '10', '167', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('766', '10', '171', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('767', '10', '179', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('768', '10', '180', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('769', '10', '181', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('770', '10', '144', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('771', '10', '145', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('772', '10', '146', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('773', '10', '147', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('774', '10', '148', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('775', '10', '149', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('776', '10', '182', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('777', '10', '150', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('778', '10', '110', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('779', '10', '139', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('780', '10', '151', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('781', '10', '152', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('782', '10', '153', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('783', '10', '140', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('784', '10', '174', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('785', '10', '175', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('786', '10', '176', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('787', '10', '177', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);
INSERT INTO `sa_system_user_menu` VALUES ('788', '10', '178', '1', '1', '1', '1', '2026-05-15 18:40:03', '2026-05-15 18:40:03', null);

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
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户与岗位关联表';

-- ----------------------------
-- Records of sa_system_user_post
-- ----------------------------
INSERT INTO `sa_system_user_post` VALUES ('1', '1', '2', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('4', '1', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('8', '2', '2', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('32', '116', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('33', '106', '2', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('34', '105', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('36', '101', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('45', '10', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('50', '100', '1', '1', '0', '0', null, null, null, '1');
INSERT INTO `sa_system_user_post` VALUES ('51', '104', '1', '1', '0', '0', null, null, null, '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户角色关联';

-- ----------------------------
-- Records of sa_system_user_role
-- ----------------------------
INSERT INTO `sa_system_user_role` VALUES ('1', '1', '1', '1', '1', null, null, null, null, null);
INSERT INTO `sa_system_user_role` VALUES ('12', '1', '2', '1', '1', null, null, null, null, null);
INSERT INTO `sa_system_user_role` VALUES ('14', '2', '2', '1', '1', '1', '1', '2026-03-22 11:51:35', '2026-03-22 11:51:35', null);
INSERT INTO `sa_system_user_role` VALUES ('43', '116', '8', '1', '1', '1', '1', '2026-03-28 17:49:22', '2026-03-28 17:49:22', null);
INSERT INTO `sa_system_user_role` VALUES ('44', '106', '4', '1', '1', '1', '1', '2026-03-28 17:50:29', '2026-03-28 17:50:29', null);
INSERT INTO `sa_system_user_role` VALUES ('45', '105', '2', '1', '1', '1', '1', '2026-03-28 17:50:39', '2026-03-28 17:50:39', null);
INSERT INTO `sa_system_user_role` VALUES ('47', '101', '2', '1', '1', '1', '1', '2026-03-28 17:50:57', '2026-03-28 17:50:57', null);
INSERT INTO `sa_system_user_role` VALUES ('63', '100', '204', '1', '2', '1', '1', '2026-04-24 21:47:04', '2026-04-24 21:47:04', null);
INSERT INTO `sa_system_user_role` VALUES ('68', '10', '6', '1', '1', '1', '1', '2026-04-25 09:46:09', '2026-04-25 09:46:09', null);
INSERT INTO `sa_system_user_role` VALUES ('73', '119', '2', '1', '1', '1', '1', '2026-05-05 18:02:28', '2026-05-05 18:02:28', null);
INSERT INTO `sa_system_user_role` VALUES ('74', '120', '3', '1', '1', '1', '1', '2026-05-05 18:04:51', '2026-05-05 18:04:51', null);
INSERT INTO `sa_system_user_role` VALUES ('75', '121', '4', '1', '1', '1', '1', '2026-05-05 18:06:55', '2026-05-05 18:06:55', null);
INSERT INTO `sa_system_user_role` VALUES ('79', '100', '2', '1', '1', '1', '1', '2026-05-07 23:30:43', '2026-05-07 23:30:43', null);
INSERT INTO `sa_system_user_role` VALUES ('81', '123', '2', '1', '1', '1', '1', '2026-05-07 23:37:46', '2026-05-07 23:37:46', null);
INSERT INTO `sa_system_user_role` VALUES ('82', '122', '3', '1', '1', '1', '1', '2026-05-07 23:37:58', '2026-05-07 23:37:58', null);
INSERT INTO `sa_system_user_role` VALUES ('83', '104', '2', '1', '1', '1', '1', '2026-05-16 17:13:50', '2026-05-16 17:13:50', null);

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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户-租户关联表';

-- ----------------------------
-- Records of sa_system_user_tenant
-- ----------------------------
INSERT INTO `sa_system_user_tenant` VALUES ('1', '1', '1', '1', '2026-03-21 02:38:46', '0', '0', '0', null, '2026-05-16 17:12:27', null);
INSERT INTO `sa_system_user_tenant` VALUES ('3', '104', '1', '0', '2026-03-22 21:58:24', '0', '1', '1', '2026-03-22 21:58:24', '2026-04-18 23:46:42', null);
INSERT INTO `sa_system_user_tenant` VALUES ('5', '106', '1', '0', '2026-03-25 21:59:21', '0', '1', '1', '2026-03-25 21:59:21', '2026-04-19 00:12:53', null);
INSERT INTO `sa_system_user_tenant` VALUES ('6', '116', '1', '0', '2026-03-27 21:12:03', '0', '1', '1', '2026-03-27 21:12:03', '2026-04-19 00:12:51', null);
INSERT INTO `sa_system_user_tenant` VALUES ('9', '3', '3', '0', '2026-04-06 22:31:46', '0', '1', '1', '2026-04-06 22:31:46', '2026-04-06 22:31:46', null);
INSERT INTO `sa_system_user_tenant` VALUES ('10', '100', '1', '1', '2026-04-18 23:27:30', '1', '1', '1', '2026-04-18 23:27:30', '2026-05-11 19:41:36', null);
INSERT INTO `sa_system_user_tenant` VALUES ('11', '100', '2', '0', '2026-04-22 22:11:29', '1', '1', '1', '2026-04-22 22:11:29', '2026-05-11 19:41:36', null);
INSERT INTO `sa_system_user_tenant` VALUES ('12', '1', '2', '0', '2026-04-23 23:37:22', '0', '1', '1', '2026-04-23 23:37:22', '2026-05-16 17:12:27', null);
INSERT INTO `sa_system_user_tenant` VALUES ('14', '10', '1', '1', '2026-04-25 09:23:56', '0', '1', '1', '2026-04-25 09:23:56', '2026-05-15 07:51:40', null);
INSERT INTO `sa_system_user_tenant` VALUES ('15', '10', '2', '0', '2026-04-25 09:24:22', '0', '1', '1', '2026-04-25 09:24:22', '2026-05-15 07:51:40', null);
INSERT INTO `sa_system_user_tenant` VALUES ('16', '2', '1', '0', '2026-05-03 23:21:43', '0', '0', '0', null, null, null);
INSERT INTO `sa_system_user_tenant` VALUES ('17', '119', '1', '1', '2026-05-05 18:02:28', '0', '1', '1', '2026-05-05 18:02:28', '2026-05-05 18:02:28', null);
INSERT INTO `sa_system_user_tenant` VALUES ('18', '120', '1', '1', '2026-05-05 18:04:51', '0', '1', '1', '2026-05-05 18:04:51', '2026-05-05 18:04:51', null);
INSERT INTO `sa_system_user_tenant` VALUES ('19', '121', '1', '1', '2026-05-05 18:06:55', '0', '1', '1', '2026-05-05 18:06:55', '2026-05-05 18:06:55', null);
INSERT INTO `sa_system_user_tenant` VALUES ('20', '122', '1', '1', '2026-05-05 18:07:27', '0', '1', '1', '2026-05-05 18:07:27', '2026-05-05 18:07:27', null);
INSERT INTO `sa_system_user_tenant` VALUES ('21', '123', '1', '1', '2026-05-05 18:08:14', '0', '1', '1', '2026-05-05 18:08:14', '2026-05-05 18:08:14', null);

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

-- ----------------------------
-- Table structure for `sa_tool_generate_columns`
-- ----------------------------
DROP TABLE IF EXISTS `sa_tool_generate_columns`;
CREATE TABLE `sa_tool_generate_columns` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `table_id` int(11) unsigned DEFAULT NULL COMMENT '所属表ID',
  `column_name` varchar(200) DEFAULT NULL COMMENT '字段名称',
  `column_comment` varchar(255) DEFAULT NULL COMMENT '字段注释',
  `column_type` varchar(50) DEFAULT NULL COMMENT '字段类型',
  `default_value` varchar(50) DEFAULT NULL COMMENT '默认值',
  `is_pk` smallint(6) DEFAULT '1' COMMENT '1 非主键 2 主键',
  `is_required` smallint(6) DEFAULT '1' COMMENT '1 非必填 2 必填',
  `is_insert` smallint(6) DEFAULT '1' COMMENT '1 非插入字段 2 插入字段',
  `is_edit` smallint(6) DEFAULT '1' COMMENT '1 非编辑字段 2 编辑字段',
  `is_list` smallint(6) DEFAULT '1' COMMENT '1 非列表显示字段 2 列表显示字段',
  `is_query` smallint(6) DEFAULT '1' COMMENT '1 非查询字段 2 查询字段',
  `is_sort` smallint(6) DEFAULT '1' COMMENT '1 非排序 2 排序',
  `query_type` varchar(100) DEFAULT 'eq' COMMENT '查询方式 eq 等于, neq 不等于, gt 大于, lt 小于, like 范围',
  `view_type` varchar(100) DEFAULT 'text' COMMENT '页面控件,text, textarea, password, select, checkbox, radio, date, upload, ma-upload(封装的上传控件)',
  `dict_type` varchar(200) DEFAULT NULL COMMENT '字典类型',
  `allow_roles` varchar(255) DEFAULT NULL COMMENT '允许查看该字段的角色',
  `options` varchar(1000) DEFAULT NULL COMMENT '字段其他设置',
  `sort` tinyint(3) unsigned DEFAULT '0' COMMENT '排序',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=463 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='代码生成业务字段表';

-- ----------------------------
-- Records of sa_tool_generate_columns
-- ----------------------------
INSERT INTO `sa_tool_generate_columns` VALUES ('409', '2', 'id', '编号', 'int(10)', null, '2', '1', '1', '1', '1', '1', '1', 'eq', 'input', null, null, null, '0', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('410', '2', 'category_id', '分类id', 'int(10)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '1', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('411', '2', 'title', '文章标题', 'varchar(255)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '2', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('412', '2', 'author', '文章作者', 'varchar(255)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '3', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('413', '2', 'image', '文章图片', 'varchar(1000)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'imagePicker', null, null, '{\"multiple\":true,\"limit\":10}', '4', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('414', '2', 'describe', '文章简介', 'varchar(1000)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '5', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('415', '2', 'content', '文章内容', 'text', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'textarea', null, null, null, '6', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('416', '2', 'views', '浏览次数', 'int(11)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '7', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('417', '2', 'sort', '排序', 'int(10) unsigned', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '8', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('418', '2', 'status', '状态', 'tinyint(1) unsigned', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'select', null, null, null, '9', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('419', '2', 'is_link', '是否外链', 'tinyint(1)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'select', null, null, null, '10', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('420', '2', 'link_url', '链接地址', 'varchar(255)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '11', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('421', '2', 'is_hot', '是否热门', 'tinyint(1) unsigned', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'select', null, null, null, '12', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('422', '2', 'created_by', '创建者', 'int(11)', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'input', null, null, null, '13', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('423', '2', 'updated_by', '更新者', 'int(11)', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'input', null, null, null, '14', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('424', '2', 'create_time', '创建时间', 'datetime', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'date', null, null, null, '15', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('425', '2', 'update_time', '修改时间', 'datetime', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'date', null, null, null, '16', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('426', '2', 'delete_time', '删除时间', 'datetime', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'date', null, null, null, '17', null, '1', '1', '2026-03-30 23:20:55', '2026-03-30 23:20:59', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('451', '3', 'id', '编号', 'int(11) unsigned', null, '2', '1', '1', '1', '1', '1', '1', 'eq', 'input', null, null, null, '0', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('452', '3', 'parent_id', '父级ID', 'int(11)', null, '1', '1', '2', '2', '2', '2', '2', 'eq', 'input', null, null, null, '1', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('453', '3', 'category_name', '分类标题', 'varchar(255)', null, '1', '1', '2', '2', '2', '2', '2', 'eq', 'input', null, null, null, '2', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('454', '3', 'describe', '分类简介', 'varchar(255)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '3', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('455', '3', 'image', '分类图片', 'varchar(255)', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '4', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('456', '3', 'sort', '排序', 'int(10) unsigned', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'input', null, null, null, '5', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('457', '3', 'status', '状态', 'tinyint(1) unsigned', null, '1', '1', '2', '2', '2', '1', '1', 'eq', 'select', null, null, null, '6', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('458', '3', 'created_by', '创建者', 'int(11)', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'input', null, null, null, '7', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('459', '3', 'updated_by', '更新者', 'int(11)', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'input', null, null, null, '8', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('460', '3', 'create_time', '创建时间', 'datetime', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'date', null, null, null, '9', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('461', '3', 'update_time', '修改时间', 'datetime', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'date', null, null, null, '10', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);
INSERT INTO `sa_tool_generate_columns` VALUES ('462', '3', 'delete_time', '删除时间', 'datetime', null, '1', '1', '1', '1', '1', '1', '1', 'eq', 'date', null, null, null, '11', null, '1', '1', '2026-03-30 23:39:05', '2026-03-30 23:39:05', null);

-- ----------------------------
-- Table structure for `sa_tool_generate_tables`
-- ----------------------------
DROP TABLE IF EXISTS `sa_tool_generate_tables`;
CREATE TABLE `sa_tool_generate_tables` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `table_name` varchar(200) DEFAULT NULL COMMENT '表名称',
  `table_comment` varchar(500) DEFAULT NULL COMMENT '表注释',
  `stub` varchar(50) DEFAULT NULL COMMENT 'stub类型',
  `template` varchar(50) DEFAULT NULL COMMENT '模板名称',
  `namespace` varchar(255) DEFAULT NULL COMMENT '命名空间',
  `package_name` varchar(100) DEFAULT NULL COMMENT '控制器包名',
  `business_name` varchar(50) DEFAULT NULL COMMENT '业务名称',
  `class_name` varchar(50) DEFAULT NULL COMMENT '类名称',
  `menu_name` varchar(100) DEFAULT NULL COMMENT '生成菜单名',
  `belong_menu_id` int(11) DEFAULT NULL COMMENT '所属菜单',
  `tpl_category` varchar(100) DEFAULT NULL COMMENT '生成类型,single 单表CRUD,tree 树表CRUD,parent_sub父子表CRUD',
  `generate_type` smallint(6) DEFAULT '1' COMMENT '1 压缩包下载 2 生成到模块',
  `generate_path` varchar(100) DEFAULT 'saiadmin-artd' COMMENT '前端根目录',
  `generate_model` smallint(6) DEFAULT '1' COMMENT '1 软删除 2 非软删除',
  `generate_menus` varchar(255) DEFAULT NULL COMMENT '生成菜单列表',
  `build_menu` smallint(6) DEFAULT '1' COMMENT '是否构建菜单',
  `component_type` smallint(6) DEFAULT '1' COMMENT '组件显示方式',
  `options` varchar(1500) DEFAULT NULL COMMENT '其他业务选项',
  `form_width` int(11) DEFAULT '800' COMMENT '表单宽度',
  `is_full` tinyint(1) DEFAULT '1' COMMENT '是否全屏',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `source` varchar(255) DEFAULT NULL COMMENT '数据源',
  `created_by` int(11) DEFAULT NULL COMMENT '创建者',
  `updated_by` int(11) DEFAULT NULL COMMENT '更新者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '修改时间',
  `delete_time` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='代码生成业务表';

-- ----------------------------
-- Records of sa_tool_generate_tables
-- ----------------------------
INSERT INTO `sa_tool_generate_tables` VALUES ('2', 'sa_article', '文章表', 'eloquent', 'app', 'App', 'Article', 'article', 'Article', '文章表', '0', 'single', '1', 'web-admin', '1', 'list,add,edit,delete', '1', '2', '{\"relations\":[{\"name\":\"category\",\"type\":\"belongsTo\",\"model\":\"ArticleCategory\",\"foreignKey\":\"id\",\"localKey\":\"category_id\",\"table\":\"\"}],\"tree_id\":\"id\",\"tree_name\":\"category_name\",\"tree_parent_id\":\"category_id\"}', '800', '1', '', 'mysql', '1', '1', '2026-03-30 00:36:12', '2026-03-30 23:20:55', null);
INSERT INTO `sa_tool_generate_tables` VALUES ('3', 'sa_article_category', '文章分类表', 'eloquent', 'app', 'App', 'ArticleCategory', 'articleCategory', 'ArticleCategory', '文章分类表', '0', 'tree', '1', 'web-admin', '1', 'list,add,edit,delete', '1', '1', '{\"relations\":[],\"tree_id\":\"id\",\"tree_parent_id\":\"category_id\",\"tree_name\":\"category_name\"}', '800', '1', '', 'mysql', '1', '1', '2026-03-30 22:33:53', '2026-03-30 22:34:42', null);
