/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-05-05 16:45:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_redeem_score`
-- ----------------------------
DROP TABLE IF EXISTS `sal_redeem_score`;
CREATE TABLE `sal_redeem_score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(50) DEFAULT NULL,
  `score` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_redeem_score
-- ----------------------------
INSERT INTO `sal_redeem_score` VALUES ('1', '122', '890');
