/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-11-06 10:08:01
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_set_menu
-- ----------------------------
DROP TABLE IF EXISTS `sal_set_menu`;
CREATE TABLE `sal_set_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_id` int(11) NOT NULL COMMENT '配置自定义id',
  `set_name` varchar(255) NOT NULL COMMENT '配置名称',
  `set_type` varchar(255) NOT NULL COMMENT '配置类型',
  `u_code` varchar(255) DEFAULT NULL COMMENT '派单系统标识',
  `mh_code` varchar(255) DEFAULT NULL,
  `z_display` int(2) NOT NULL DEFAULT '1' COMMENT '1：显示 0：隐藏',
  `z_index` int(11) DEFAULT '1' COMMENT '层级，数字越高越靠后',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COMMENT='付款类型表';

-- ----------------------------
-- Records of sal_set_menu
-- ----------------------------
INSERT INTO `sal_set_menu` VALUES ('44', '1', '清洁', 'serviceTypeClass', null, '', '1', '100', null, null, '2025-11-05 17:47:19', '2025-11-05 17:49:33');
INSERT INTO `sal_set_menu` VALUES ('45', '2', '租赁机器', 'serviceTypeClass', null, '', '1', '90', null, null, '2025-11-05 17:47:45', '2025-11-05 17:49:35');
INSERT INTO `sal_set_menu` VALUES ('46', '3', '灭虫', 'serviceTypeClass', null, '', '1', '80', null, null, '2025-11-05 17:48:00', '2025-11-05 17:49:39');
INSERT INTO `sal_set_menu` VALUES ('47', '4', '飘盈香', 'serviceTypeClass', null, '', '1', '70', null, null, '2025-11-05 17:48:14', '2025-11-05 17:49:40');
INSERT INTO `sal_set_menu` VALUES ('48', '5', '甲醛', 'serviceTypeClass', null, '', '1', '60', null, null, '2025-11-05 17:48:26', '2025-11-05 17:49:41');
INSERT INTO `sal_set_menu` VALUES ('49', '6', '纸品', 'serviceTypeClass', null, '', '1', '50', null, null, '2025-11-05 17:48:37', '2025-11-05 17:49:41');
INSERT INTO `sal_set_menu` VALUES ('50', '7', '一次性售卖', 'serviceTypeClass', null, '', '1', '40', null, null, '2025-11-05 17:48:49', '2025-11-05 17:49:43');
INSERT INTO `sal_set_menu` VALUES ('53', '1', '统一结算', 'settleTypeClass', '', '', '1', '1', 'shenchao', null, '2025-11-06 09:57:34', '2025-11-06 09:57:34');
INSERT INTO `sal_set_menu` VALUES ('54', '2', '门店结算', 'settleTypeClass', '', '', '1', '1', 'shenchao', null, '2025-11-06 09:57:46', '2025-11-06 09:57:46');
INSERT INTO `sal_set_menu` VALUES ('55', '1', '预付款', 'feeTypeClass', '', '', '1', '1', 'shenchao', null, '2025-11-06 09:58:08', '2025-11-06 09:58:08');
INSERT INTO `sal_set_menu` VALUES ('56', '2', '后付款', 'feeTypeClass', '', '', '1', '1', 'shenchao', null, '2025-11-06 09:58:30', '2025-11-06 09:58:30');
INSERT INTO `sal_set_menu` VALUES ('57', '1', '每月1号', 'billDayClass', null, null, '1', '1', null, null, '2025-11-06 10:00:43', '2025-11-06 10:00:43');
INSERT INTO `sal_set_menu` VALUES ('58', '5', '每月5号', 'billDayClass', null, null, '1', '1', null, null, '2025-11-06 10:00:50', '2025-11-06 10:01:31');
INSERT INTO `sal_set_menu` VALUES ('59', '10', '每月10号', 'billDayClass', null, null, '1', '1', null, null, '2025-11-06 10:00:52', '2025-11-06 10:01:32');
INSERT INTO `sal_set_menu` VALUES ('60', '15', '每月15号', 'billDayClass', null, null, '1', '1', null, null, '2025-11-06 10:00:53', '2025-11-06 10:01:33');
INSERT INTO `sal_set_menu` VALUES ('61', '20', '每月20号', 'billDayClass', null, null, '1', '1', null, null, '2025-11-06 10:01:00', '2025-11-06 10:01:33');
INSERT INTO `sal_set_menu` VALUES ('62', '25', '每月25号', 'billDayClass', null, null, '1', '1', null, null, '2025-11-06 10:01:02', '2025-11-06 10:01:35');
INSERT INTO `sal_set_menu` VALUES ('63', '30', '30天', 'receivableDayClass', null, null, '1', '1', null, null, '2025-11-06 10:01:52', '2025-11-06 10:02:35');
INSERT INTO `sal_set_menu` VALUES ('64', '60', '60天', 'receivableDayClass', null, null, '1', '1', null, null, '2025-11-06 10:01:56', '2025-11-06 10:02:36');
INSERT INTO `sal_set_menu` VALUES ('65', '90', '90天', 'receivableDayClass', null, null, '1', '1', null, null, '2025-11-06 10:01:58', '2025-11-06 10:02:37');
INSERT INTO `sal_set_menu` VALUES ('66', '120', '120天', 'receivableDayClass', null, null, '1', '1', null, null, '2025-11-06 10:02:00', '2025-11-06 10:02:37');
INSERT INTO `sal_set_menu` VALUES ('67', '150', '150天', 'receivableDayClass', null, null, '1', '1', null, null, '2025-11-06 10:02:01', '2025-11-06 10:02:38');
INSERT INTO `sal_set_menu` VALUES ('68', '180', '180天', 'receivableDayClass', null, null, '1', '1', null, null, '2025-11-06 10:02:04', '2025-11-06 10:02:38');
