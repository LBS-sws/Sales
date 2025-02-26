/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-05-05 16:45:20
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_redeem_gift_apply`
-- ----------------------------
DROP TABLE IF EXISTS `sal_redeem_gift_apply`;
CREATE TABLE `sal_redeem_gift_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) DEFAULT NULL COMMENT '员工id',
  `employee_name` varchar(255) DEFAULT NULL COMMENT '员工名字',
  `gift_id` int(11) DEFAULT NULL COMMENT '礼物id',
  `gift_name` varchar(255) DEFAULT NULL COMMENT '礼物名称',
  `bonus_point` int(11) DEFAULT NULL COMMENT '扣除积分',
  `apply_num` int(11) DEFAULT NULL COMMENT '申请数量',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `city_name` varchar(255) DEFAULT NULL COMMENT '城市名称',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态（0-审核中，1-审核通过，2审核驳回）',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `apply_date` date DEFAULT NULL COMMENT '申请时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_redeem_gift_apply
-- ----------------------------
INSERT INTO `sal_redeem_gift_apply` VALUES ('18', '122', '郑燕峰', '1', '测试奖励', '20', '1', 'SH', '上海', '2', 'fty', '2021-04-30');
INSERT INTO `sal_redeem_gift_apply` VALUES ('19', '122', '郑燕峰', '1', '测试奖励', '20', '2', 'SH', '上海', '2', 'ced', '2021-04-30');
INSERT INTO `sal_redeem_gift_apply` VALUES ('20', '122', '郑燕峰', '1', '测试奖励', '20', '1', 'SH', '上海', '1', '', '2021-04-30');
INSERT INTO `sal_redeem_gift_apply` VALUES ('21', '122', '郑燕峰', '1', '测试奖励', '20', '1', 'SH', '上海', '1', '', '2021-04-30');
INSERT INTO `sal_redeem_gift_apply` VALUES ('22', '122', '郑燕峰', '7', '测试02', '50', '1', 'SH', '上海', '1', '测试02兑换', '2021-05-04');
INSERT INTO `sal_redeem_gift_apply` VALUES ('23', '122', '郑燕峰', '6', '测试01', '20', '1', 'SH', '上海', '2', '测试01', '2021-05-04');
