/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-05-05 16:45:26
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_redeem_gifts`
-- ----------------------------
DROP TABLE IF EXISTS `sal_redeem_gifts`;
CREATE TABLE `sal_redeem_gifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gift_name` varchar(255) DEFAULT NULL COMMENT '礼物名称',
  `bonus_point` int(11) DEFAULT NULL COMMENT '扣除积分',
  `inventory` int(11) DEFAULT NULL COMMENT '库存',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `city_name` varchar(255) DEFAULT NULL COMMENT '城市名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_redeem_gifts
-- ----------------------------
INSERT INTO `sal_redeem_gifts` VALUES ('2', 'cehsi', '32', '234', null, null);
INSERT INTO `sal_redeem_gifts` VALUES ('3', 'cehsi', '25', '52', null, null);
INSERT INTO `sal_redeem_gifts` VALUES ('4', 'cehsi', '45', '245', null, null);
INSERT INTO `sal_redeem_gifts` VALUES ('6', '测试01', '20', '119', 'SH', '上海');
INSERT INTO `sal_redeem_gifts` VALUES ('7', '测试02', '50', '499', 'SH', '上海');
