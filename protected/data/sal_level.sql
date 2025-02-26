/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-01-25 10:58:28
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_level`
-- ----------------------------
DROP TABLE IF EXISTS `sal_level`;
CREATE TABLE `sal_level` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(200) DEFAULT NULL COMMENT '段位',
  `start_fraction` int(11) DEFAULT NULL COMMENT '开始分数',
  `end_fraction` int(11) DEFAULT NULL COMMENT '终止分数',
  `new_level` varchar(200) DEFAULT NULL COMMENT '新赛季段位',
  `new_fraction` int(11) DEFAULT NULL COMMENT '新赛季分数',
  `reward` int(11) DEFAULT NULL COMMENT '奖励',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_level
-- ----------------------------
INSERT INTO `sal_level` VALUES ('1', '珍爱1段', '540000', '100000000', '钻石1段', '350000', '1700');
