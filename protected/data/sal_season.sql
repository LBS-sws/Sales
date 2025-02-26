/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-01-25 10:56:10
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_season`
-- ----------------------------
DROP TABLE IF EXISTS `sal_season`;
CREATE TABLE `sal_season` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `season` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_season
-- ----------------------------
