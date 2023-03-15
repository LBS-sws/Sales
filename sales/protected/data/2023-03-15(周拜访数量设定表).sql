/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2023-03-15 13:08:56
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_sales_min
-- ----------------------------
DROP TABLE IF EXISTS `sal_sales_min`;
CREATE TABLE `sal_sales_min` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` date DEFAULT NULL COMMENT '生效時間',
  `min_num` int(11) NOT NULL DEFAULT '0' COMMENT '最小拜訪數量',
  `only_type` int(11) NOT NULL DEFAULT '1' COMMENT '適配範圍 0：本地 1：全國',
  `city` varchar(255) DEFAULT NULL COMMENT '客戶姓名',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
