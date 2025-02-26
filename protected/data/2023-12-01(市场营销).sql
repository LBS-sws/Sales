/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2023-12-01 17:35:11
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_market
-- ----------------------------
DROP TABLE IF EXISTS `sal_market`;
CREATE TABLE `sal_market` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `number_no` varchar(20) DEFAULT NULL COMMENT '编号',
  `company_name` varchar(255) NOT NULL COMMENT '企业名称',
  `company_web` varchar(255) DEFAULT NULL COMMENT '企业网站',
  `city` varchar(10) DEFAULT NULL COMMENT '所属城市',
  `city_name` varchar(255) DEFAULT NULL,
  `area` varchar(10) DEFAULT NULL COMMENT '区域',
  `legal_user` varchar(100) DEFAULT NULL COMMENT '法定代表人',
  `person_phone` text COMMENT '联系人 /电话',
  `company_size` varchar(10) DEFAULT NULL COMMENT '公司规模',
  `company_state` varchar(10) DEFAULT NULL COMMENT '企业状态',
  `sign_address` varchar(255) DEFAULT NULL COMMENT '注册地址',
  `company_date` date DEFAULT NULL COMMENT '企业成立日期',
  `run_address` varchar(255) DEFAULT NULL COMMENT '经营地址',
  `company_type` varchar(255) DEFAULT NULL COMMENT '企业分类',
  `company_note` text COMMENT '企业介绍',
  `allot_date` date DEFAULT NULL COMMENT '分配时间',
  `allot_type` int(1) NOT NULL DEFAULT '0' COMMENT '分配类型 0：未分配 1：KA  2：地区 3：地区员工',
  `allot_city` varchar(10) DEFAULT NULL COMMENT '分配城市',
  `allot_ka` int(10) DEFAULT NULL COMMENT '员工id（暂时不使用）',
  `allot_employee` int(10) DEFAULT NULL COMMENT '员工id',
  `start_date` datetime DEFAULT NULL COMMENT '建档日期',
  `end_date` datetime DEFAULT NULL COMMENT '最后跟进时间',
  `status_type` int(2) NOT NULL DEFAULT '0' COMMENT '状态 0：未分配 1：系统退回 2：手动退回 5:已分配 6:跟进中 8：已拒绝 10：已完成',
  `z_index` int(11) NOT NULL DEFAULT '0' COMMENT '0:资料层  1：ka层  2：地区 3：员工',
  `back_note` text COMMENT '退回原因',
  `reject_note` text COMMENT '无意向原因',
  `ready_bool` int(1) NOT NULL DEFAULT '1' COMMENT '完成或拒绝后是否已读 1：已读 0：未读',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='客户资料库';

-- ----------------------------
-- Table structure for sal_market_history
-- ----------------------------
DROP TABLE IF EXISTS `sal_market_history`;
CREATE TABLE `sal_market_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` int(11) NOT NULL,
  `update_type` int(11) NOT NULL DEFAULT '1' COMMENT '修改類型 1：修改 2:新增',
  `allot_type` int(11) NOT NULL DEFAULT '0' COMMENT '分配记录 0：无效 1：KA  2：地区 3：地区员工',
  `update_html` text NOT NULL COMMENT '修改內容',
  `update_json` text COMMENT '修改後的json數據',
  `lcu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8 COMMENT='客户资料库修改記錄表';

-- ----------------------------
-- Table structure for sal_market_info
-- ----------------------------
DROP TABLE IF EXISTS `sal_market_info`;
CREATE TABLE `sal_market_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` int(11) NOT NULL,
  `state_id` int(11) DEFAULT NULL COMMENT '跟进id',
  `info_date` date DEFAULT NULL COMMENT '跟進時間',
  `info_text` text COMMENT '跟進內容',
  `del_bool` int(1) NOT NULL DEFAULT '0' COMMENT '是否已删除 1：已删除',
  `email_bool` int(11) NOT NULL DEFAULT '0' COMMENT '是否已邮件提醒 0：无 1：已提醒',
  `email_id` int(11) NOT NULL DEFAULT '0' COMMENT '邮件提醒id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='客户资料库跟進表';

-- ----------------------------
-- Table structure for sal_market_state
-- ----------------------------
DROP TABLE IF EXISTS `sal_market_state`;
CREATE TABLE `sal_market_state` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state_name` varchar(255) NOT NULL,
  `state_type` int(2) NOT NULL DEFAULT '1' COMMENT '1:跟进中 2：拒絕 3：已接受',
  `state_day` int(11) NOT NULL DEFAULT '0' COMMENT '跟进中时，多少天后邮件提醒',
  `z_index` int(10) DEFAULT '0' COMMENT '層級（數值越高顯示越靠前）',
  `z_display` int(2) DEFAULT '1' COMMENT '是否顯示 1：是 0：否',
  `city` varchar(10) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='客戶跟进状态';

-- ----------------------------
-- Table structure for sal_market_user
-- ----------------------------
DROP TABLE IF EXISTS `sal_market_user`;
CREATE TABLE `sal_market_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `market_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL COMMENT '跟进id',
  `user_dept` varchar(100) DEFAULT NULL COMMENT '跟進時間',
  `user_phone` varchar(100) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_wechat` varchar(100) DEFAULT NULL,
  `user_text` text COMMENT '联系人备注',
  `del_bool` int(1) NOT NULL DEFAULT '0' COMMENT '是否已删除 1：已删除',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COMMENT='客户资料联系人表';
