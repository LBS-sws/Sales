/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2022-06-09 14:52:49
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_stop_back_info
-- ----------------------------
DROP TABLE IF EXISTS `sal_stop_back_info`;
CREATE TABLE `sal_stop_back_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stop_id` int(10) NOT NULL DEFAULT '0',
  `customer_name` varchar(255) DEFAULT NULL COMMENT '客戶姓名',
  `back_date` date DEFAULT NULL COMMENT '回訪日期',
  `back_type` int(11) DEFAULT NULL COMMENT '客戶狀態',
  `back_remark` text COMMENT '備註',
  `end_bool` int(2) NOT NULL DEFAULT '0' COMMENT '流程結束 1：結束',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='終止客戶的再次跟進表';


-- ----------------------------
-- Table structure for sal_stop_back
-- ----------------------------
ALTER TABLE sal_stop_back ADD COLUMN info_num int(3) NOT NULL DEFAULT 0 COMMENT '跟進的總次數' AFTER back_remark;


-- ----------------------------
-- Table structure for sal_stop_type
-- ----------------------------
ALTER TABLE sal_stop_type ADD COLUMN again_day int(5) NOT NULL DEFAULT 0 COMMENT '繼續跟進的提示日期' AFTER type_name;
ALTER TABLE sal_stop_type ADD COLUMN again_type int(2) NOT NULL DEFAULT 0 COMMENT '是否繼續跟進 0：不跟進 1：跟進' AFTER type_name;
