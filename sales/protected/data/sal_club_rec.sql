/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2022-01-24 17:37:21
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_club_rec
-- ----------------------------
DROP TABLE IF EXISTS `sal_club_rec`;
CREATE TABLE `sal_club_rec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rec_year` int(4) NOT NULL,
  `month_type` int(2) NOT NULL,
  `employee_id` int(11) NOT NULL COMMENT '員工id',
  `rec_remark` text NOT NULL COMMENT '推薦原因',
  `rec_user` varchar(255) NOT NULL COMMENT '推薦人',
  `rec_name` varchar(255) DEFAULT NULL COMMENT '推薦人昵称',
  `lcu` varchar(100) DEFAULT NULL,
  `luu` varchar(100) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='总监推荐人选（俱樂部專用）';

-- ----------------------------
-- Table structure for sal_club_setting
-- ----------------------------
DROP TABLE IF EXISTS `sal_club_setting`;
CREATE TABLE `sal_club_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `explain_text` varchar(255) DEFAULT NULL COMMENT '解釋說明',
  `effect_date` date NOT NULL COMMENT '生效日期',
  `set_json` text NOT NULL,
  `lcu` varchar(100) DEFAULT NULL,
  `luu` varchar(100) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COMMENT='俱樂部配置表';
