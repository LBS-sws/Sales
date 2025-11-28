/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-09-02 13:03:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_pay
-- ----------------------------
DROP TABLE IF EXISTS `sal_pay`;
CREATE TABLE `sal_pay` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '付款名称',
  `zt_code` varchar(255) DEFAULT NULL COMMENT '中台系统标识',
  `z_display` int(2) NOT NULL DEFAULT '1' COMMENT '1：显示 0：隐藏',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='付款类型表';

-- ----------------------------
-- Records of sal_pay
-- ----------------------------
INSERT INTO `sal_pay` VALUES ('1', '线上', 'swsglzxshyxgs', '1', 'shenchao', 'shenchao', '2025-08-20 15:53:54', '2025-08-27 16:56:06');
INSERT INTO `sal_pay` VALUES ('2', '线下', null, '1', null, null, '2025-08-27 16:41:35', '2025-08-27 16:56:10');
