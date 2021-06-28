/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2021-06-28 14:33:19
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_search
-- ----------------------------
DROP TABLE IF EXISTS `sal_search`;
CREATE TABLE `sal_search` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `employee_code` varchar(255) DEFAULT NULL,
  `employee_name` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `search_date` date DEFAULT NULL COMMENT '查詢的日期',
  `search_num` int(11) DEFAULT '0' COMMENT '查詢總次數',
  `search_json` text COMMENT '查詢的字符串（json）',
  `search_str` text COMMENT '查詢的字符串 逗號分割',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='銷售人員查詢史偉莎客戶的記錄表';
