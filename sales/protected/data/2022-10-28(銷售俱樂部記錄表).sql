/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2022-10-28 13:44:35
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_club
-- ----------------------------
DROP TABLE IF EXISTS `sal_club`;
CREATE TABLE `sal_club` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `month_type` int(2) NOT NULL,
  `sales_elite` text,
  `sales_elite_display` int(1) NOT NULL DEFAULT '0' COMMENT '0:不顯示 1：顯示',
  `sales_forward` text,
  `sales_forward_display` int(1) DEFAULT '0' COMMENT '0:不顯示 1：顯示',
  `sales_out` text,
  `sales_out_display` int(1) DEFAULT '0' COMMENT '0:不顯示 1：顯示',
  `sales_visit` text,
  `sales_visit_display` int(1) DEFAULT '0' COMMENT '0:不顯示 1：顯示',
  `sales_rec` text,
  `sales_rec_display` int(1) DEFAULT '0' COMMENT '0:不顯示 1：顯示',
  `lcu` varchar(100) DEFAULT NULL,
  `luu` varchar(100) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='銷售俱樂部保存每年數據';
