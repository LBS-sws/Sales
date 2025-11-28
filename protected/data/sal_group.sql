/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-08-20 18:11:16
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_group
-- ----------------------------
DROP TABLE IF EXISTS `sal_group`;
CREATE TABLE `sal_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_name` varchar(255) NOT NULL COMMENT '组织名称',
  `employee_id` int(11) NOT NULL,
  `prev_id` int(11) NOT NULL DEFAULT '0' COMMENT '上级的id',
  `z_index` int(5) NOT NULL DEFAULT '1' COMMENT '层级，数字越低层级越高',
  `z_display` int(11) NOT NULL DEFAULT '1' COMMENT '1:显示',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='人员组织架构表';

-- ----------------------------
-- Table structure for sal_main_lbs
-- ----------------------------
DROP TABLE IF EXISTS `sal_main_lbs`;
CREATE TABLE `sal_main_lbs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '主体公司',
  `sh_code` varchar(100) DEFAULT NULL COMMENT '统一社会信用代码',
  `mh_code` varchar(255) DEFAULT NULL COMMENT '门户网站标识',
  `city` varchar(50) NOT NULL COMMENT '所在城市',
  `show_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:本地 2：全国 3：部分城市',
  `show_city` varchar(255) DEFAULT NULL COMMENT '显示城市',
  `z_display` int(2) NOT NULL DEFAULT '1' COMMENT '1：显示 0：隐藏',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='主体公司表';
