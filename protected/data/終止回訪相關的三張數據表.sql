/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2022-05-17 17:01:33
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_stop_back
-- ----------------------------
DROP TABLE IF EXISTS `sal_stop_back`;
CREATE TABLE `sal_stop_back` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) NOT NULL DEFAULT '0' COMMENT '日報表系統的客戶服務id',
  `bold_service` int(1) NOT NULL DEFAULT '0' COMMENT '是否重點客戶 1：是 0：否',
  `customer_name` varchar(255) DEFAULT NULL COMMENT '客戶姓名',
  `staff_id` int(11) DEFAULT NULL COMMENT '轉移後的員工',
  `back_date` date DEFAULT NULL COMMENT '回訪日期',
  `back_type` int(11) DEFAULT NULL COMMENT '客戶狀態',
  `back_remark` text COMMENT '備註',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_stop_back
-- ----------------------------

-- ----------------------------
-- Table structure for sal_stop_site
-- ----------------------------
DROP TABLE IF EXISTS `sal_stop_site`;
CREATE TABLE `sal_stop_site` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stop_month` int(3) NOT NULL DEFAULT '6' COMMENT '终止时间超过6个月',
  `month_money` int(11) NOT NULL DEFAULT '2000' COMMENT '月金额',
  `year_money` int(11) NOT NULL DEFAULT '24000' COMMENT '年金额',
  `city` varchar(255) DEFAULT NULL COMMENT '客戶姓名',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_stop_site
-- ----------------------------
INSERT INTO `sal_stop_site` VALUES ('14', '6', '2000', '24000', 'HK', null, 'shenchao', '2022-05-16 12:08:48', '2022-05-16 12:19:35');

-- ----------------------------
-- Table structure for sal_stop_type
-- ----------------------------
DROP TABLE IF EXISTS `sal_stop_type`;
CREATE TABLE `sal_stop_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL COMMENT '客戶回訪狀態名稱',
  `z_index` int(11) DEFAULT NULL,
  `display` int(1) NOT NULL DEFAULT '1' COMMENT '0：不顯示  1：顯示',
  `city` varchar(255) DEFAULT NULL COMMENT '客戶姓名',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_stop_type
-- ----------------------------
