/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-11-25 16:44:49
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_clue_file
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_file`;
CREATE TABLE `sal_clue_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `file_name` varchar(255) DEFAULT NULL COMMENT '文件名称（自定义）',
  `phy_file_name` varchar(255) DEFAULT NULL COMMENT '文件名称（系统名）',
  `phy_path_name` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `display_name` varchar(255) DEFAULT NULL COMMENT '文件名（上传名）',
  `file_type` varchar(255) DEFAULT NULL COMMENT '文件后缀',
  `sent_bool` int(11) DEFAULT '0' COMMENT '0:未发送 1：已发送给派单',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='报价附件表';

-- ----------------------------
-- Table structure for sal_contract_call
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract_call`;
CREATE TABLE `sal_contract_call` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL,
  `clue_type` int(11) NOT NULL,
  `cont_id` int(11) NOT NULL COMMENT '主合约id',
  `call_code` varchar(255) DEFAULT NULL,
  `busine_id` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `busine_id_text` varchar(255) DEFAULT NULL COMMENT '服务项目（文字）',
  `apply_date` date NOT NULL COMMENT '申请日期（为了显示频次选项）',
  `call_status` int(11) NOT NULL DEFAULT '0' COMMENT '0:草稿',
  `call_json` text COMMENT '呼叫式详情(客户选择)',
  `call_month_json` text COMMENT '呼叫式选择的月份数据',
  `call_text` text COMMENT '呼叫式文本显示',
  `call_sum` int(11) DEFAULT '0' COMMENT '呼叫式总次数',
  `call_amt` double(11,2) DEFAULT '0.00' COMMENT '呼叫式总金额',
  `store_num` int(11) NOT NULL DEFAULT '0' COMMENT '呼叫门店数量',
  `store_ids` text COMMENT '呼叫式关联的门店',
  `vir_ids` text COMMENT '呼叫式关联的合约',
  `pro_ids` text COMMENT '审核成功后生成的记录虚拟合同id',
  `call_remark` text,
  `mh_remark` text,
  `city` varchar(255) DEFAULT NULL,
  `mh_id` varchar(255) DEFAULT NULL COMMENT '门户审核id',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='呼叫式服务的呼叫记录';
