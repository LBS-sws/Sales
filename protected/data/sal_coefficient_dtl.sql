/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-01-21 16:09:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_coefficient_dtl`
-- ----------------------------
DROP TABLE IF EXISTS `sal_coefficient_dtl`;
CREATE TABLE `sal_coefficient_dtl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hdr_id` int(10) unsigned NOT NULL,
  `name` varchar(11) NOT NULL,
  `operator` char(2) NOT NULL,
  `criterion` decimal(11,2) NOT NULL DEFAULT '0.00',
  `bonus` decimal(5,2) NOT NULL DEFAULT '0.00',
  `coefficient` decimal(5,3) NOT NULL DEFAULT '0.000',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_service_rate_dtl_01` (`hdr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_coefficient_dtl
-- ----------------------------
