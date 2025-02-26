/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesuat

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-12-24 10:11:36
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_integral`
-- ----------------------------
DROP TABLE IF EXISTS `sal_integral`;
CREATE TABLE `sal_integral` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `sale_day` int(11) DEFAULT '20' COMMENT '上班天数',
  `point` decimal(5,3) DEFAULT '0.000',
  `username` varchar(20) DEFAULT NULL,
  `hdr_id` int(11) DEFAULT NULL,
  `city` char(11) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_integral
-- ----------------------------
INSERT INTO `sal_integral` VALUES ('1', '2020', '5', '18', '1.000', 'test', null, 'SH', '2020-05-28 15:18:50', '2020-06-03 15:12:31');
INSERT INTO `sal_integral` VALUES ('2', '2020', '6', '20', '0.000', 'hr-bj.bj', null, 'BJ', '2020-06-02 14:05:28', '2020-06-03 14:39:40');
INSERT INTO `sal_integral` VALUES ('3', '2020', '6', '20', '0.000', 'wangjing', null, 'BJ', '2020-06-02 14:05:28', '2020-06-03 14:39:40');
INSERT INTO `sal_integral` VALUES ('4', '2020', '6', '20', '0.000', 'shjkang.bj', null, 'BJ', '2020-06-02 14:05:28', '2020-06-03 14:39:41');
INSERT INTO `sal_integral` VALUES ('5', '2020', '6', '20', '0.000', 'chunhau.bj', null, 'BJ', '2020-06-02 14:05:28', '2020-06-03 14:39:41');
INSERT INTO `sal_integral` VALUES ('6', '2020', '6', '20', '0.000', 'Yoyo.bj', null, 'BJ', '2020-06-02 14:05:28', '2020-06-03 14:39:42');
INSERT INTO `sal_integral` VALUES ('7', '2020', '6', '20', '0.000', 'yulong.bj', null, 'BJ', '2020-06-02 14:05:28', '2020-06-03 14:39:42');
INSERT INTO `sal_integral` VALUES ('8', '2020', '6', '20', '1.000', 'zhuoyang.bj', null, 'BJ', '2020-06-02 14:05:28', '2020-06-03 14:39:43');
INSERT INTO `sal_integral` VALUES ('9', '2020', '6', '20', '1.000', 'cdgm', null, 'CD', '2020-06-03 10:23:33', '2020-06-03 14:39:43');
INSERT INTO `sal_integral` VALUES ('10', '2020', '6', '202', '0.000', 'martin', null, 'CD', '2020-06-02 14:05:28', '2020-06-03 14:39:43');
INSERT INTO `sal_integral` VALUES ('11', '2020', '6', '2', '0.000', 'QCCD', null, 'CD', '2020-06-02 14:05:28', '2020-06-03 14:39:44');
INSERT INTO `sal_integral` VALUES ('12', '2020', '6', '2', '0.000', 'JoeY', null, 'CD', '2020-06-02 14:05:28', '2020-06-03 14:39:44');
INSERT INTO `sal_integral` VALUES ('13', '2020', '6', '2', '0.000', 'yanfei.cd', null, 'CD', '2020-06-02 14:05:28', '2020-06-03 14:39:44');
INSERT INTO `sal_integral` VALUES ('14', '2020', '6', '2', '0.000', 'songlin.cd', null, 'CD', '2020-06-02 14:05:28', '2020-06-03 14:39:44');
INSERT INTO `sal_integral` VALUES ('15', '2020', '6', '0', '0.000', 'test1', null, 'SZ', '2020-06-02 14:05:28', '2020-06-03 14:39:45');
INSERT INTO `sal_integral` VALUES ('16', '2020', '6', '20', '0.000', 'test2', null, 'SZ', '2020-06-02 14:05:28', '2020-06-03 14:39:45');
INSERT INTO `sal_integral` VALUES ('17', '2020', '6', '20202020', '0.000', 'shelley.sz', null, 'SZ', '2020-06-02 14:05:28', '2020-06-03 14:39:47');
INSERT INTO `sal_integral` VALUES ('18', '2020', '6', '20', '2.000', 'jun.sz', null, 'SZ', '2020-06-02 14:05:28', '2020-06-03 14:39:48');
INSERT INTO `sal_integral` VALUES ('19', '2020', '6', '2002', '0.000', 'ting.sz', null, 'SZ', '2020-06-02 14:05:28', '2020-06-03 14:39:51');
INSERT INTO `sal_integral` VALUES ('20', '2020', '6', '0', '0.000', 'smiling.sz', null, 'SZ', '2020-06-02 14:05:29', '2020-06-03 14:39:52');
INSERT INTO `sal_integral` VALUES ('21', '2020', '6', '2', '0.000', 'daisycheuk.sz', null, 'SZ', '2020-06-02 14:05:29', '2020-06-03 14:39:51');
INSERT INTO `sal_integral` VALUES ('22', '2020', '6', '220', '0.000', 'sandyzeng.sz', null, 'SZ', '2020-06-02 14:05:29', '2020-06-03 14:39:49');
INSERT INTO `sal_integral` VALUES ('23', '2020', '6', '202', '0.000', 'ziqiang.sz', null, 'SZ', '2020-06-02 14:05:29', '2020-06-03 14:39:50');
INSERT INTO `sal_integral` VALUES ('24', '2020', '6', '20', '0.000', 'hr-bj.bj', null, 'BJ', '2020-06-02 14:06:43', '2020-06-03 14:39:51');
INSERT INTO `sal_integral` VALUES ('25', '2020', '6', '202', '0.000', 'wangjing', null, 'BJ', '2020-06-02 14:06:43', '2020-06-03 14:39:50');
INSERT INTO `sal_integral` VALUES ('26', '2020', '6', null, '0.000', 'shjkang.bj', null, 'BJ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('27', '2020', '6', null, '0.000', 'chunhau.bj', null, 'BJ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('28', '2020', '6', null, '0.000', 'Yoyo.bj', null, 'BJ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('29', '2020', '6', null, '0.000', 'yulong.bj', null, 'BJ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('30', '2020', '6', null, '0.000', 'zhuoyang.bj', null, 'BJ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('31', '2020', '6', null, '0.000', 'cdgm', null, 'CD', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('32', '2020', '6', null, '0.000', 'martin', null, 'CD', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('33', '2020', '6', null, '0.000', 'QCCD', null, 'CD', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('34', '2020', '6', null, '0.000', 'JoeY', null, 'CD', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('35', '2020', '6', null, '0.000', 'yanfei.cd', null, 'CD', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('36', '2020', '6', null, '0.000', 'songlin.cd', null, 'CD', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('37', '2020', '6', null, '0.000', 'test1', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('38', '2020', '6', null, '0.000', 'test2', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('39', '2020', '6', null, '0.000', 'shelley.sz', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('40', '2020', '6', null, '0.000', 'jun.sz', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('41', '2020', '6', null, '0.000', 'ting.sz', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('42', '2020', '6', null, '0.000', 'smiling.sz', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('43', '2020', '6', null, '0.000', 'daisycheuk.sz', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('44', '2020', '6', null, '0.000', 'sandyzeng.sz', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
INSERT INTO `sal_integral` VALUES ('45', '2020', '6', null, '0.000', 'ziqiang.sz', null, 'SZ', '2020-06-02 14:06:43', '2020-06-02 14:06:43');
