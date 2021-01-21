/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-01-21 16:09:17
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_coefficient_hdr`
-- ----------------------------
DROP TABLE IF EXISTS `sal_coefficient_hdr`;
CREATE TABLE `sal_coefficient_hdr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(11) NOT NULL,
  `city` char(5) NOT NULL,
  `start_dt` datetime NOT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_coefficient_hdr
-- ----------------------------
