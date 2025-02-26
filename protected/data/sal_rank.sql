/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-03-12 10:45:21
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_rank`
-- ----------------------------
DROP TABLE IF EXISTS `sal_rank`;
CREATE TABLE `sal_rank` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `season` varchar(20) DEFAULT NULL COMMENT '赛季',
  `month` datetime DEFAULT NULL COMMENT '月份',
  `username` varchar(20) DEFAULT NULL,
  `city` varchar(11) DEFAULT NULL,
  `all_score` int(20) DEFAULT NULL COMMENT '当月所有得分乘以倍数后',
  `last_score` int(20) DEFAULT NULL COMMENT '上赛季分数',
  `now_score` int(20) DEFAULT NULL COMMENT '当月总分',
  `initial_score` int(20) DEFAULT NULL COMMENT '初始分数',
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1219 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_rank
-- ----------------------------
INSERT INTO `sal_rank` VALUES ('292', '1', '2021-01-29 00:00:00', 'test', 'SH', '1792', '1', '1793', '4000', '2021-03-12 10:11:47', '2021-03-12 10:11:47');
INSERT INTO `sal_rank` VALUES ('293', '1', '2021-02-11 11:29:00', 'test', 'SH', '256', '1793', '2049', '1000', '2021-03-12 10:13:14', '2021-03-12 10:13:14');
INSERT INTO `sal_rank` VALUES ('1218', '2', '2021-03-01 00:00:00', 'test', 'SH', null, '1800', null, '1000', '2021-03-12 10:14:52', '2021-03-12 10:14:52');
