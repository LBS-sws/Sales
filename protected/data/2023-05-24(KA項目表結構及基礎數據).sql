/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2023-05-24 16:42:34
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_ka_area
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_area`;
CREATE TABLE `sal_ka_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_name` varchar(255) NOT NULL,
  `city_code` varchar(10) DEFAULT NULL COMMENT '日報表對應的城市編號',
  `z_index` int(11) NOT NULL DEFAULT '0' COMMENT '層級（數值越高顯示越靠前）',
  `z_display` int(1) NOT NULL DEFAULT '1' COMMENT '是否顯示 1：是 0：否',
  `rpt_type` varchar(10) DEFAULT NULL,
  `city` varchar(10) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='KA客户地区配置';

-- ----------------------------
-- Records of sal_ka_area
-- ----------------------------
INSERT INTO `sal_ka_area` VALUES ('5', '珠海', 'ZH', '0', '1', null, 'ZH', 'shenchao', null, '2023-05-18 16:11:13', '2023-05-18 16:11:13');
INSERT INTO `sal_ka_area` VALUES ('6', '全国', '', '100', '1', null, 'ZH', 'shenchao', 'shenchao', '2023-05-18 16:48:12', '2023-05-22 15:12:32');
INSERT INTO `sal_ka_area` VALUES ('7', '上海', 'SH', '0', '1', null, 'ZH', 'shenchao', null, '2023-05-23 14:38:39', '2023-05-23 14:38:39');

-- ----------------------------
-- Table structure for sal_ka_bot
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_bot`;
CREATE TABLE `sal_ka_bot` (
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
  `source_text` varchar(255) DEFAULT NULL COMMENT '客户来源A（填寫）',
  `source_id` int(10) DEFAULT NULL COMMENT '客戶來源id',
  `area_id` int(10) DEFAULT NULL COMMENT '客户地区',
  `level_id` int(10) DEFAULT NULL COMMENT '級別id',
  `class_id` int(10) DEFAULT NULL COMMENT '類別id',
  `busine_id` int(10) DEFAULT NULL COMMENT '业务模式id',
  `link_id` int(11) DEFAULT NULL COMMENT '當前溝通階段id',
  `reject_id` int(10) DEFAULT NULL COMMENT '拒絕、暫停id',
  `month_amt` decimal(13,2) DEFAULT NULL COMMENT '可签约金额（月度）',
  `quarter_amt` decimal(13,2) DEFAULT NULL COMMENT '可签约金额（季度）',
  `year_amt` decimal(13,2) DEFAULT '0.00' COMMENT '可签约金额（年度）',
  `sign_date` date DEFAULT NULL COMMENT '簽約日期',
  `sign_month` int(3) DEFAULT NULL COMMENT '服務時長（月）',
  `sign_amt` decimal(13,2) DEFAULT NULL COMMENT '簽約金額',
  `sum_amt` decimal(13,2) DEFAULT NULL COMMENT 'ka總金額（系統計算後的總金額）',
  `support_user` int(10) DEFAULT NULL COMMENT '区域支持者',
  `sign_odds` int(10) DEFAULT NULL COMMENT '簽約概率',
  `status_type` int(2) NOT NULL DEFAULT '1' COMMENT '狀態 1：正常 2：暫停 3：終止',
  `remark` text COMMENT '備註信息',
  `city` varchar(10) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='KA客户地区配置';


-- ----------------------------
-- Table structure for sal_ka_bot_history
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_bot_history`;
CREATE TABLE `sal_ka_bot_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `update_type` int(11) NOT NULL DEFAULT '1' COMMENT '修改類型 1：修改',
  `update_html` text NOT NULL COMMENT '修改內容',
  `lcu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='KA客户修改記錄表';


-- ----------------------------
-- Table structure for sal_ka_bot_info
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_bot_info`;
CREATE TABLE `sal_ka_bot_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `info_date` date DEFAULT NULL COMMENT '跟進時間',
  `info_text` text COMMENT '跟進內容',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='KA客户跟進表';


-- ----------------------------
-- Table structure for sal_ka_busine
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_busine`;
CREATE TABLE `sal_ka_busine` (
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='KA业务模式配置';

-- ----------------------------
-- Records of sal_ka_busine
-- ----------------------------
INSERT INTO `sal_ka_busine` VALUES ('5', '虫害防治', '100', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:54:29', '2023-05-18 15:54:29');
INSERT INTO `sal_ka_busine` VALUES ('6', '消毒灭菌', '90', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:54:37', '2023-05-18 15:54:37');
INSERT INTO `sal_ka_busine` VALUES ('7', '空气净化', '70', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:54:48', '2023-05-18 15:54:48');
INSERT INTO `sal_ka_busine` VALUES ('8', '甲醛净化', '60', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:54:56', '2023-05-18 15:54:56');
INSERT INTO `sal_ka_busine` VALUES ('9', '空间香熏', '50', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:55:04', '2023-05-18 15:55:04');
INSERT INTO `sal_ka_busine` VALUES ('10', '卫生间清洁', '40', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:55:13', '2023-05-18 15:55:13');

-- ----------------------------
-- Table structure for sal_ka_class
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_class`;
CREATE TABLE `sal_ka_class` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level_id` int(10) NOT NULL DEFAULT '0' COMMENT '級別id（暫時無用）',
  `pro_name` varchar(255) NOT NULL,
  `z_index` int(11) NOT NULL DEFAULT '0' COMMENT '層級（數值越高顯示越靠前）',
  `z_display` int(1) NOT NULL DEFAULT '1' COMMENT '是否顯示 1：是 0：否',
  `rpt_type` varchar(10) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='KA客户类别配置';

-- ----------------------------
-- Records of sal_ka_class
-- ----------------------------
INSERT INTO `sal_ka_class` VALUES ('5', '5', '食品厂', '100', '1', null, 'shenchao', null, '2023-05-18 15:47:12', '2023-05-18 17:18:01');
INSERT INTO `sal_ka_class` VALUES ('6', '6', '制药厂', '85', '1', null, 'shenchao', 'shenchao', '2023-05-18 15:47:22', '2023-05-18 17:39:32');
INSERT INTO `sal_ka_class` VALUES ('7', '6', '化工厂', '80', '1', null, 'shenchao', 'shenchao', '2023-05-18 15:47:34', '2023-05-18 17:39:32');
INSERT INTO `sal_ka_class` VALUES ('8', '6', '化妆品厂', '70', '1', null, 'shenchao', 'shenchao', '2023-05-18 15:47:44', '2023-05-18 17:39:32');
INSERT INTO `sal_ka_class` VALUES ('9', '7', '玩具厂', '60', '1', null, 'shenchao', null, '2023-05-18 15:47:53', '2023-05-18 17:18:13');
INSERT INTO `sal_ka_class` VALUES ('10', '7', '电子厂', '55', '1', null, 'shenchao', null, '2023-05-18 15:48:02', '2023-05-18 17:18:13');
INSERT INTO `sal_ka_class` VALUES ('11', '7', '塑料厂', '50', '1', null, 'shenchao', null, '2023-05-18 15:48:12', '2023-05-18 17:18:14');
INSERT INTO `sal_ka_class` VALUES ('12', '7', '五金厂', '45', '1', null, 'shenchao', null, '2023-05-18 15:48:20', '2023-05-18 17:18:15');
INSERT INTO `sal_ka_class` VALUES ('13', '7', '制衣厂', '40', '1', null, 'shenchao', null, '2023-05-18 15:48:29', '2023-05-18 17:18:16');
INSERT INTO `sal_ka_class` VALUES ('14', '7', '纸浆厂', '35', '1', null, 'shenchao', null, '2023-05-18 15:48:39', '2023-05-18 17:18:16');
INSERT INTO `sal_ka_class` VALUES ('15', '7', '其他厂', '30', '1', null, 'shenchao', null, '2023-05-18 15:48:47', '2023-05-18 17:18:17');
INSERT INTO `sal_ka_class` VALUES ('16', '8', '连锁餐饮', '25', '1', null, 'shenchao', null, '2023-05-18 15:48:56', '2023-05-18 17:18:18');
INSERT INTO `sal_ka_class` VALUES ('17', '9', '物流', '20', '1', null, 'shenchao', null, '2023-05-18 15:49:07', '2023-05-18 17:18:19');
INSERT INTO `sal_ka_class` VALUES ('18', '10', '酒店', '15', '1', null, 'shenchao', null, '2023-05-18 15:49:17', '2023-05-18 17:18:21');
INSERT INTO `sal_ka_class` VALUES ('19', '11', '商場', '10', '1', null, 'shenchao', null, '2023-05-18 15:49:26', '2023-05-18 17:18:22');
INSERT INTO `sal_ka_class` VALUES ('20', '12', '養老院', '5', '1', null, 'shenchao', null, '2023-05-18 15:49:34', '2023-05-18 17:18:24');

-- ----------------------------
-- Table structure for sal_ka_level
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_level`;
CREATE TABLE `sal_ka_level` (
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='KA客户分级配置';

-- ----------------------------
-- Records of sal_ka_level
-- ----------------------------
INSERT INTO `sal_ka_level` VALUES ('5', 'A', '10', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:43:16', '2023-05-18 15:43:16');
INSERT INTO `sal_ka_level` VALUES ('6', 'B', '9', '1', null, 'ZH', 'shenchao', 'shenchao', '2023-05-18 15:43:22', '2023-05-18 17:39:11');
INSERT INTO `sal_ka_level` VALUES ('7', 'C', '8', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:43:28', '2023-05-18 15:43:28');
INSERT INTO `sal_ka_level` VALUES ('8', 'D', '7', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:43:36', '2023-05-18 15:43:36');
INSERT INTO `sal_ka_level` VALUES ('9', 'E', '5', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:43:41', '2023-05-18 15:43:41');
INSERT INTO `sal_ka_level` VALUES ('10', 'F', '4', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:43:47', '2023-05-18 15:43:47');
INSERT INTO `sal_ka_level` VALUES ('11', 'G', '3', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:43:56', '2023-05-18 15:43:56');
INSERT INTO `sal_ka_level` VALUES ('12', 'H', '2', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:44:01', '2023-05-18 15:44:01');

-- ----------------------------
-- Table structure for sal_ka_link
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_link`;
CREATE TABLE `sal_ka_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rate_num` int(4) NOT NULL DEFAULT '0' COMMENT '溝通階段',
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='KA当前沟通阶段配置';

-- ----------------------------
-- Records of sal_ka_link
-- ----------------------------
INSERT INTO `sal_ka_link` VALUES ('5', '30', '方案报价', '100', '1', null, 'ZH', 'shenchao', 'shenchao', '2023-05-18 16:20:38', '2023-05-22 15:37:21');
INSERT INTO `sal_ka_link` VALUES ('6', '10', '拜访并识别需求', '100', '1', null, 'ZH', 'shenchao', 'shenchao', '2023-05-18 16:20:53', '2023-05-22 15:37:25');
INSERT INTO `sal_ka_link` VALUES ('7', '20', '方案设计沟通', '100', '1', null, 'ZH', 'shenchao', 'shenchao', '2023-05-18 16:21:57', '2023-05-22 15:37:29');
INSERT INTO `sal_ka_link` VALUES ('8', '100', '合同签署', '100', '1', null, 'ZH', 'shenchao', null, '2023-05-18 16:22:11', '2023-05-18 16:22:11');
INSERT INTO `sal_ka_link` VALUES ('9', '0', '已拒绝', '100', '1', null, 'ZH', 'shenchao', null, '2023-05-22 15:38:05', '2023-05-22 15:38:05');

-- ----------------------------
-- Table structure for sal_ka_sales
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_sales`;
CREATE TABLE `sal_ka_sales` (
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='KA销售模式配置';

-- ----------------------------
-- Records of sal_ka_sales
-- ----------------------------
INSERT INTO `sal_ka_sales` VALUES ('5', '合约', '40', '1', null, 'ZH', 'shenchao', null, '2023-05-18 16:00:02', '2023-05-18 16:00:02');
INSERT INTO `sal_ka_sales` VALUES ('6', '单约', '30', '1', null, 'ZH', 'shenchao', null, '2023-05-18 16:00:12', '2023-05-18 16:00:12');
INSERT INTO `sal_ka_sales` VALUES ('7', '产品销售', '20', '1', null, 'ZH', 'shenchao', null, '2023-05-18 16:00:19', '2023-05-18 16:00:19');

-- ----------------------------
-- Table structure for sal_ka_source
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_source`;
CREATE TABLE `sal_ka_source` (
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='KA客户来源配置';

-- ----------------------------
-- Records of sal_ka_source
-- ----------------------------
INSERT INTO `sal_ka_source` VALUES ('5', '新客户', '9', '1', null, 'ZH', 'shenchao', 'shenchao', '2023-05-18 15:30:53', '2023-05-18 15:31:00');
INSERT INTO `sal_ka_source` VALUES ('6', '现有客户', '7', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:31:11', '2023-05-18 15:31:11');
INSERT INTO `sal_ka_source` VALUES ('7', '之前合作过', '5', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:31:23', '2023-05-18 15:31:23');
INSERT INTO `sal_ka_source` VALUES ('8', '之前未成交', '0', '1', null, 'ZH', 'shenchao', null, '2023-05-18 15:31:32', '2023-05-18 15:31:32');

-- ----------------------------
-- Table structure for sal_ka_type
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_type`;
CREATE TABLE `sal_ka_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ka_name` varchar(255) NOT NULL,
  `ka_type` int(2) DEFAULT '1' COMMENT '1:暫停 2：拒絕',
  `z_index` int(10) DEFAULT '0' COMMENT '層級（數值越高顯示越靠前）',
  `z_display` int(2) DEFAULT '1' COMMENT '是否顯示 1：是 0：否',
  `city` varchar(10) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='客戶拒絕、暫停選項';

-- ----------------------------
-- Records of sal_ka_type
-- ----------------------------
INSERT INTO `sal_ka_type` VALUES ('13', '无法联系', '2', '0', '1', null, 'shenchao', 'shenchao', '2023-05-18 18:00:20', '2023-05-18 18:02:28');
INSERT INTO `sal_ka_type` VALUES ('14', '没有需求', '2', '0', '1', null, 'shenchao', null, '2023-05-18 18:02:41', '2023-05-18 18:02:41');
INSERT INTO `sal_ka_type` VALUES ('15', '价格太高', '2', '0', '1', null, 'shenchao', null, '2023-05-18 18:02:49', '2023-05-18 18:02:49');
INSERT INTO `sal_ka_type` VALUES ('16', '价格', '1', '0', '1', null, 'shenchao', null, '2023-05-18 18:02:59', '2023-05-18 18:02:59');
INSERT INTO `sal_ka_type` VALUES ('17', '无需求', '1', '0', '1', null, 'shenchao', null, '2023-05-18 18:03:07', '2023-05-18 18:03:07');
INSERT INTO `sal_ka_type` VALUES ('18', '有指定供货商', '1', '0', '1', null, 'shenchao', null, '2023-05-18 18:03:14', '2023-05-18 18:03:14');
INSERT INTO `sal_ka_type` VALUES ('19', '不考虑更换供货商', '1', '0', '1', null, 'shenchao', null, '2023-05-18 18:03:22', '2023-05-18 18:03:22');
INSERT INTO `sal_ka_type` VALUES ('20', '无法达到客户需求', '1', '0', '1', null, 'shenchao', null, '2023-05-18 18:03:30', '2023-05-18 18:03:30');
INSERT INTO `sal_ka_type` VALUES ('21', '服务地点未能覆盖', '1', '0', '1', null, 'shenchao', null, '2023-05-18 18:03:37', '2023-05-18 18:03:37');
INSERT INTO `sal_ka_type` VALUES ('22', '其他', '1', '0', '1', null, 'shenchao', null, '2023-05-18 18:03:43', '2023-05-18 18:03:43');
