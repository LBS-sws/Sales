/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2024-04-11 15:00:48
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_ca_bot
-- ----------------------------
DROP TABLE IF EXISTS `sal_ca_bot`;
CREATE TABLE `sal_ca_bot` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `apply_date` date NOT NULL COMMENT '錄入日期',
  `customer_no` varchar(200) DEFAULT NULL COMMENT '客戶編號',
  `customer_name` varchar(255) NOT NULL COMMENT '客戶名稱',
  `kam_id` int(11) NOT NULL COMMENT '員工的employee_id（KAM）',
  `head_city_id` int(11) DEFAULT NULL COMMENT '客户总部(城市id)',
  `talk_city_id` int(11) DEFAULT NULL COMMENT '洽谈城市id',
  `contact_user` varchar(255) DEFAULT NULL COMMENT '聯繫人',
  `contact_phone` varchar(255) DEFAULT NULL COMMENT '聯繫人電話',
  `contact_email` varchar(255) DEFAULT NULL COMMENT '聯繫人郵箱',
  `contact_dept` varchar(255) DEFAULT NULL COMMENT '聯繫人職位',
  `contact_adr` varchar(255) DEFAULT NULL COMMENT '联系人地址',
  `work_user` varchar(255) DEFAULT NULL COMMENT '业务联系人',
  `work_phone` varchar(255) DEFAULT NULL COMMENT '业务联系人',
  `work_email` varchar(255) DEFAULT NULL COMMENT '业务联系人',
  `source_text` varchar(255) DEFAULT NULL COMMENT '客户来源A（填寫）',
  `source_id` int(10) DEFAULT NULL COMMENT '客戶來源id',
  `area_id` int(10) DEFAULT NULL COMMENT '客户地区',
  `level_id` int(10) DEFAULT NULL COMMENT '級別id',
  `class_id` int(10) DEFAULT NULL COMMENT '類別id',
  `class_other` varchar(255) DEFAULT NULL COMMENT '当客户类别为其它时',
  `busine_id` varchar(100) DEFAULT NULL COMMENT '业务模式（多个用,分割）',
  `busine_name` varchar(255) DEFAULT NULL COMMENT '业务模式（多个用、分割）翻译',
  `link_id` int(11) DEFAULT NULL COMMENT '當前溝通階段id',
  `reject_id` int(10) DEFAULT NULL COMMENT '拒絕、暫停id',
  `month_amt` decimal(13,2) DEFAULT NULL COMMENT '可签约金额（月度）',
  `quarter_amt` decimal(13,2) DEFAULT NULL COMMENT '可签约金额（季度）',
  `year_amt` decimal(13,2) DEFAULT '0.00' COMMENT '可签约金额（年度）',
  `available_amt` decimal(13,2) DEFAULT NULL COMMENT '可成交金額',
  `available_date` date DEFAULT NULL COMMENT '可成交日期',
  `ava_show_date` date DEFAULT NULL COMMENT '列表需要显示的可成交日期',
  `sign_date` date DEFAULT NULL COMMENT '簽約日期',
  `sign_month` int(3) DEFAULT NULL COMMENT '服務時長（月）',
  `sign_amt` decimal(13,2) DEFAULT NULL COMMENT '簽約金額',
  `sum_amt` decimal(13,2) DEFAULT NULL COMMENT 'ka總金額（系統計算後的總金額）',
  `support_user` int(10) DEFAULT NULL COMMENT '区域支持者',
  `sign_odds` int(10) DEFAULT NULL COMMENT '簽約概率',
  `status_type` int(2) NOT NULL DEFAULT '1' COMMENT '狀態 1：正常 2：暫停 3：終止',
  `remark` text COMMENT '備註信息',
  `city` varchar(10) DEFAULT NULL,
  `follow_date` date DEFAULT NULL COMMENT '跟進日期',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='KA客户地区配置';

-- ----------------------------
-- Table structure for sal_ca_bot_ava
-- ----------------------------
DROP TABLE IF EXISTS `sal_ca_bot_ava`;
CREATE TABLE `sal_ca_bot_ava` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `ava_date` date NOT NULL COMMENT '可成交日期',
  `ava_amt` decimal(13,2) DEFAULT NULL COMMENT '可成交金额',
  `ava_note` text COMMENT '备注',
  `ava_city` varchar(255) DEFAULT NULL COMMENT '城市',
  `ava_num` varchar(255) DEFAULT NULL COMMENT '门店数量',
  `ava_rate` int(4) DEFAULT NULL COMMENT '预估成交%',
  `ava_fact_amt` decimal(13,2) DEFAULT NULL COMMENT '实际成交金额',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='KA客户的可成交日期列表（一对多）';

-- ----------------------------
-- Table structure for sal_ca_bot_history
-- ----------------------------
DROP TABLE IF EXISTS `sal_ca_bot_history`;
CREATE TABLE `sal_ca_bot_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `update_type` int(11) NOT NULL DEFAULT '1' COMMENT '修改類型 1：修改 2:新增',
  `update_html` text NOT NULL COMMENT '修改內容',
  `update_json` text COMMENT '修改後的json數據',
  `espe_type` int(1) DEFAULT NULL COMMENT '1：特別標註的修改',
  `sum_amt` decimal(13,2) DEFAULT NULL COMMENT 'ka總金額',
  `sign_odds` int(10) DEFAULT NULL COMMENT '簽約概率',
  `lcu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='KA客户修改記錄表';

-- ----------------------------
-- Table structure for sal_ca_bot_info
-- ----------------------------
DROP TABLE IF EXISTS `sal_ca_bot_info`;
CREATE TABLE `sal_ca_bot_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `info_date` date DEFAULT NULL COMMENT '跟進時間',
  `info_text` text COMMENT '跟進內容',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='KA客户跟進表';

-- ----------------------------
-- Table structure for sal_ka_dup
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_dup`;
CREATE TABLE `sal_ka_dup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dup_name` varchar(255) NOT NULL COMMENT '需要替换的字符串',
  `dup_value` varchar(255) DEFAULT NULL COMMENT '替换后的字符串',
  `z_index` int(11) NOT NULL DEFAULT '0' COMMENT '層級（數值越高顯示越靠前）',
  `z_display` int(1) NOT NULL DEFAULT '1' COMMENT '是否顯示 1：是 0：否',
  `rpt_type` varchar(10) NOT NULL DEFAULT 'All',
  `city` varchar(10) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='客户名称查重设置';

ALTER TABLE sal_ka_bot ADD COLUMN `search_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '查看客户重复字段' AFTER customer_name;
ALTER TABLE sal_ra_bot ADD COLUMN `search_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '查看客户重复字段' AFTER customer_name;
ALTER TABLE sal_ca_bot ADD COLUMN `search_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '查看客户重复字段' AFTER customer_name;

UPDATE sal_ka_bot SET search_name=customer_name WHERE search_name IS NULL;
UPDATE sal_ra_bot SET search_name=customer_name WHERE search_name IS NULL;
UPDATE sal_ca_bot SET search_name=customer_name WHERE search_name IS NULL;