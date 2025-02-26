/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2024-01-30 10:12:18
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_ka_sra
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_sra`;
CREATE TABLE `sal_ka_sra` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_name` varchar(255) NOT NULL,
  `z_index` int(11) NOT NULL DEFAULT '0' COMMENT '層級（數值越高顯示越靠前）',
  `z_display` int(1) NOT NULL DEFAULT '1' COMMENT '是否顯示 1：是 0：否',
  `rpt_type` varchar(10) DEFAULT NULL,
  `city` varchar(10) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='KA客户来源配置(原来的客户来源修改为客户类型)';
