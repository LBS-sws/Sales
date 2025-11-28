/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2025-11-28 10:55:27
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for sal_api_curl
-- ----------------------------
DROP TABLE IF EXISTS `sal_api_curl`;
CREATE TABLE `sal_api_curl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `expr_date` date NOT NULL COMMENT '执行时间',
  `status_type` char(255) NOT NULL DEFAULT 'p' COMMENT '状态，p:未发送 C：已完成 E：响应异常',
  `min_url` varchar(255) NOT NULL,
  `info_type` varchar(255) NOT NULL,
  `info_url` varchar(255) DEFAULT NULL COMMENT '接口地址',
  `data_content` longtext NOT NULL COMMENT '发送的curl（json字符串）',
  `out_content` text COMMENT '响应的内容',
  `message` varchar(255) DEFAULT NULL,
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='已发送的CURL';

-- ----------------------------
-- Table structure for sal_clue
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue`;
CREATE TABLE `sal_clue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_code` varchar(20) DEFAULT NULL COMMENT '线索编号',
  `table_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:线索 2：客户',
  `clue_type` int(10) NOT NULL DEFAULT '1' COMMENT '线索类型：1：销售拜访 2：ka项目',
  `clue_status` int(11) NOT NULL DEFAULT '0' COMMENT '线索状态：0：待跟进 1：跟进中 2：商机 3：报价确认 4：合同确认 5：已转化',
  `clue_label` varchar(255) DEFAULT NULL COMMENT '线索标签（未使用）',
  `entry_date` date NOT NULL COMMENT '线索录入时间',
  `cust_name` varchar(255) NOT NULL COMMENT '客户名称',
  `full_name` varchar(255) DEFAULT NULL COMMENT '别名',
  `abbr_code` varchar(6) DEFAULT NULL COMMENT '系统计算后的首字母（线索编号使用）',
  `service_type` varchar(100) DEFAULT NULL COMMENT '服务类型（日报表系统swo_customer_type）',
  `cust_class_group` int(11) DEFAULT NULL COMMENT '行业类别（日报表系统swo_nature）',
  `cust_class` int(11) DEFAULT NULL COMMENT '行业类别（日报表系统swo_nature_type）',
  `cust_ka_class` int(11) DEFAULT NULL COMMENT 'KA客户类别（sal_ka_class）',
  `cust_type` int(10) DEFAULT NULL COMMENT '客户类型（sal_ka_source）',
  `cust_level` int(10) DEFAULT NULL COMMENT '客户分级（sal_ka_level）',
  `group_bool` char(1) NOT NULL DEFAULT 'Y' COMMENT '是否集团客户 Y:是 N:否',
  `cust_vip` varchar(10) DEFAULT 'N' COMMENT '重点客户 Y:重点客户',
  `cust_person` varchar(255) DEFAULT NULL COMMENT '负责人',
  `cust_tel` varchar(255) DEFAULT NULL COMMENT '负责人电话',
  `cust_email` varchar(255) DEFAULT NULL COMMENT '负责人邮箱',
  `cust_person_role` varchar(255) DEFAULT NULL COMMENT '负责人职务',
  `cust_address` text COMMENT '联系人地址',
  `cont_person` varchar(255) DEFAULT NULL COMMENT '合同联系人',
  `cont_tel` varchar(255) DEFAULT NULL COMMENT '合同联系人电话',
  `cont_email` varchar(255) DEFAULT NULL COMMENT '合同联系人邮箱',
  `cont_person_role` varchar(255) DEFAULT NULL COMMENT '合同联系人职务',
  `district` bigint(20) DEFAULT NULL COMMENT '区域id',
  `area` varchar(10) DEFAULT NULL COMMENT '面积',
  `street` varchar(255) DEFAULT NULL COMMENT '街道',
  `address` text COMMENT '详细地址',
  `clue_source` int(11) DEFAULT NULL COMMENT '线索来源',
  `rec_type` int(11) NOT NULL DEFAULT '1' COMMENT '接收类型：1：指定员工 2：地区可见 3：销售自取',
  `rec_employee_id` int(11) DEFAULT NULL COMMENT '跟进员工ID',
  `extra_user` text COMMENT '额外跟进的员工',
  `last_date` date DEFAULT NULL COMMENT '下次跟进时间',
  `end_date` datetime DEFAULT NULL COMMENT '最后跟进时间',
  `end_employee_id` int(11) DEFAULT NULL COMMENT '最后跟进员工',
  `end_flow_id` int(11) DEFAULT NULL COMMENT '最后跟进流程的id',
  `clue_remark` text COMMENT '线索备注',
  `city` varchar(255) DEFAULT NULL COMMENT '城市编号',
  `talk_city_id` varchar(255) DEFAULT NULL COMMENT '洽谈地区',
  `busine_id` varchar(255) DEFAULT NULL COMMENT '业务模式',
  `support_user` int(11) DEFAULT NULL COMMENT '区域支持者',
  `del_num` int(11) DEFAULT '0' COMMENT '是否已删除 0：否 1：是',
  `latitude` varchar(255) DEFAULT NULL COMMENT '经度',
  `longitude` varchar(255) DEFAULT NULL COMMENT '纬度',
  `yewudalei` int(11) DEFAULT NULL COMMENT '业务大类：KA，地推',
  `box_type` int(10) DEFAULT '0' COMMENT '0:自己新增 1：线索分配',
  `ka_id` int(11) DEFAULT NULL COMMENT 'ka项目id',
  `u_id` int(11) DEFAULT NULL COMMENT '派单系统id',
  `u_group_id` int(11) DEFAULT NULL COMMENT '派单系统(联系人分组id)',
  `report_id` int(11) DEFAULT NULL COMMENT '导入时excel的id',
  `file_num` int(10) DEFAULT '0' COMMENT '附件数量',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=893 DEFAULT CHARSET=utf8 COMMENT='客户线索表';

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='报价附件表';

-- ----------------------------
-- Table structure for sal_clue_flow
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_flow`;
CREATE TABLE `sal_clue_flow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL,
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:地推 2:KA',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `visit_date` date NOT NULL COMMENT '跟进时间',
  `create_staff` int(11) NOT NULL COMMENT '创建人',
  `store_num` int(11) NOT NULL DEFAULT '0' COMMENT '门店数量（自动计算)',
  `update_bool` int(11) NOT NULL DEFAULT '1' COMMENT '是否允许修改 1：允许',
  `last_visit_date` date DEFAULT NULL COMMENT '下次跟进时间',
  `predict_date` date DEFAULT NULL COMMENT '预估成交时间',
  `predict_amt` double(11,2) DEFAULT NULL COMMENT '预估成交金额',
  `lbs_main` int(11) DEFAULT NULL COMMENT '主体公司',
  `sign_odds` int(3) DEFAULT NULL COMMENT '签约概率',
  `visit_text` text COMMENT '跟进内容',
  `rpt_bool` int(11) DEFAULT '0' COMMENT '是否报价 0：否 1：是',
  `no_intention_id` int(10) DEFAULT NULL COMMENT '无意向原因id',
  `survey_bool` int(10) DEFAULT NULL COMMENT '0:否 1：是',
  `visit_obj` varchar(255) DEFAULT NULL COMMENT '拜访目的',
  `visit_obj_text` varchar(255) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8 COMMENT='客户线索跟进表';

-- ----------------------------
-- Table structure for sal_clue_history
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_history`;
CREATE TABLE `sal_clue_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:线索记录 2：门店记录 3：报价记录 4：税号记录',
  `table_id` int(11) NOT NULL,
  `history_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:新增 2:修改 3:删除 4：子表修改 5：门户网站记录',
  `history_html` text COMMENT '修改内容',
  `expr_data` varchar(255) DEFAULT NULL COMMENT '额外数据',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1207 DEFAULT CHARSET=utf8 COMMENT='客户线索记录表';

-- ----------------------------
-- Table structure for sal_clue_invoice
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_invoice`;
CREATE TABLE `sal_clue_invoice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL,
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:地推 2:KA',
  `invoice_type` int(2) NOT NULL DEFAULT '1' COMMENT '1:普票 2：专票',
  `invoice_name` varchar(255) NOT NULL COMMENT '税号名称',
  `city` varchar(255) NOT NULL,
  `invoice_header` varchar(255) DEFAULT NULL COMMENT '开票抬头',
  `tax_id` varchar(255) DEFAULT NULL COMMENT '税号',
  `invoice_address` varchar(255) DEFAULT NULL COMMENT '开票地址',
  `invoice_number` varchar(255) DEFAULT NULL COMMENT '开户行',
  `invoice_user` varchar(255) DEFAULT NULL COMMENT '账号',
  `invoice_rmk` text COMMENT '发票备注',
  `invoice_phone` varchar(255) DEFAULT NULL COMMENT '公司电话',
  `show_pay` varchar(255) DEFAULT NULL COMMENT '是否显示收款人',
  `show_cpy` varchar(255) DEFAULT NULL COMMENT '是否显示复核人',
  `show_opy` varchar(255) DEFAULT NULL COMMENT '是否显示开票人',
  `z_display` int(11) DEFAULT '1' COMMENT '0:不显示 1：显示',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='客户线索门店表';

-- ----------------------------
-- Table structure for sal_clue_person
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_person`;
CREATE TABLE `sal_clue_person` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_store_id` int(11) NOT NULL DEFAULT '0' COMMENT '0:集团专属 其它：门店id',
  `person_code` varchar(255) DEFAULT NULL COMMENT '联络人编号',
  `cust_person` varchar(255) DEFAULT NULL COMMENT '联络人',
  `cust_tel` varchar(255) DEFAULT NULL COMMENT '联络人电话',
  `cust_email` varchar(255) DEFAULT NULL COMMENT '联络人电邮',
  `cust_person_role` varchar(255) DEFAULT NULL COMMENT '联络人职务',
  `sex` varchar(255) DEFAULT NULL COMMENT '性别',
  `person_pws` varchar(255) DEFAULT NULL COMMENT 'null:未设置 1：已设置',
  `z_display` int(11) DEFAULT '1' COMMENT '0:不显示 1：显示',
  `status` int(1) DEFAULT '1' COMMENT '联络人状态 1 任职中 2 已离职 3 辞退 4 删除',
  `u_group_id` int(11) DEFAULT NULL,
  `u_id` int(11) DEFAULT NULL COMMENT 'U系统id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=601 DEFAULT CHARSET=utf8 COMMENT='客户联系人表';

-- ----------------------------
-- Table structure for sal_clue_rpt
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_rpt`;
CREATE TABLE `sal_clue_rpt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL,
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:地推 2:KA',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `city` varchar(255) NOT NULL COMMENT '城市',
  `sales_id` int(11) NOT NULL COMMENT '销售id',
  `lbs_main` int(11) NOT NULL COMMENT '主体公司',
  `cust_name` varchar(255) NOT NULL COMMENT '客户名称',
  `cust_level` int(11) DEFAULT NULL,
  `cust_class` int(11) DEFAULT NULL,
  `total_amt` double(11,2) NOT NULL COMMENT '合同金额',
  `rpt_status` int(11) NOT NULL DEFAULT '1' COMMENT '报价状态 1：已发送 2：..... 10：报价通过',
  `file_type` int(11) DEFAULT NULL COMMENT ' 文件类型',
  `is_seal` varchar(1) DEFAULT 'Y' COMMENT '是否用印',
  `seal_type_id` int(11) DEFAULT NULL COMMENT '印章类型',
  `cont_type_id` int(11) DEFAULT NULL COMMENT '合同类型',
  `service_type_id` int(11) DEFAULT NULL COMMENT '服务频次',
  `bill_week` int(11) DEFAULT NULL COMMENT '账期',
  `audit_type` int(11) DEFAULT NULL COMMENT '技术审核',
  `cut_type` int(11) DEFAULT NULL COMMENT '扣款条款',
  `fee_add` int(11) DEFAULT NULL COMMENT '附加费用',
  `remark` text COMMENT '备注',
  `mh_remark` text COMMENT '门户审批意见',
  `mh_id` varchar(100) DEFAULT NULL COMMENT '对应的门户网站id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='商机报价表';

-- ----------------------------
-- Table structure for sal_clue_rpt_file
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_rpt_file`;
CREATE TABLE `sal_clue_rpt_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `rpt_id` int(11) NOT NULL COMMENT '报价id',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '文件分组0:投标文件 1：成本文件',
  `file_name` varchar(255) DEFAULT NULL COMMENT '文件名称（自定义）',
  `phy_file_name` varchar(255) DEFAULT NULL COMMENT '文件名称（系统名）',
  `phy_path_name` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `display_name` varchar(255) DEFAULT NULL COMMENT '文件名（上传名）',
  `file_type` varchar(255) DEFAULT NULL COMMENT '文件后缀',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='报价附件表';

-- ----------------------------
-- Table structure for sal_clue_service
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_service`;
CREATE TABLE `sal_clue_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL,
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:销售拜访跟进 2:ka项目',
  `visit_type` int(11) NOT NULL COMMENT '拜访类别',
  `create_staff` int(11) NOT NULL COMMENT '创建人',
  `busine_id` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `busine_id_text` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `visit_obj` varchar(255) DEFAULT NULL COMMENT '拜访目的',
  `visit_obj_text` varchar(255) DEFAULT NULL,
  `sign_odds` int(3) DEFAULT NULL COMMENT '签约概率',
  `lbs_main` int(11) DEFAULT NULL COMMENT '主体公司',
  `predict_date` date DEFAULT NULL COMMENT '预估成交时间',
  `predict_amt` double(11,2) DEFAULT NULL COMMENT '预估成交金额',
  `rpt_amt` double(11,2) DEFAULT NULL COMMENT '报价金额',
  `total_amt` double(11,2) DEFAULT NULL COMMENT '实际成交总金额（系统自动计算）',
  `total_num` int(11) NOT NULL DEFAULT '0' COMMENT '门店总数量（系统自动计算）',
  `visit_num` int(11) NOT NULL DEFAULT '0' COMMENT '跟进次数（系统自动计算）',
  `end_flow_id` int(11) DEFAULT NULL COMMENT '最后跟进id',
  `end_staff_id` int(11) DEFAULT NULL COMMENT '商机最后跟进人',
  `service_status` int(11) NOT NULL DEFAULT '0' COMMENT '0：未跟进 1:跟进中 2：待报价 3：报价中 4：已驳回 5：报价通过 6:待合同审批',
  `ka_ava_id` int(11) DEFAULT NULL,
  `report_id` int(11) DEFAULT NULL COMMENT '导入id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8 COMMENT='线索关联的商机表';

-- ----------------------------
-- Table structure for sal_clue_sre_soe
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_sre_soe`;
CREATE TABLE `sal_clue_sre_soe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `clue_store_id` int(11) NOT NULL COMMENT '门店id',
  `create_staff` int(11) NOT NULL COMMENT '创建人',
  `store_amt` double(11,2) NOT NULL DEFAULT '0.00' COMMENT '门店服务总金额',
  `service_sum` int(11) NOT NULL DEFAULT '0' COMMENT '服务总次数',
  `update_bool` int(11) DEFAULT '1' COMMENT '是否允许修改 1：允许',
  `busine_id` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `busine_id_text` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `remark` text COMMENT '备注',
  `detail_json` text,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8 COMMENT='客户线索商机关联门店表';

-- ----------------------------
-- Table structure for sal_clue_store
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_store`;
CREATE TABLE `sal_clue_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:地推 2:KA',
  `store_code` varchar(255) DEFAULT NULL COMMENT '门店编号',
  `store_name` varchar(255) NOT NULL COMMENT '门店名称',
  `store_full_name` varchar(255) DEFAULT NULL COMMENT '别名',
  `store_status` int(11) NOT NULL DEFAULT '0' COMMENT '0:草稿 1：未服务 2：服务中',
  `cust_class_group` int(11) DEFAULT NULL COMMENT '行业类别（日报表系统swo_nature）',
  `cust_class` varchar(255) DEFAULT NULL COMMENT '行业类别（日报表系统swo_nature_type）',
  `create_staff` int(11) NOT NULL COMMENT '创建人',
  `city` varchar(255) NOT NULL COMMENT '城市编号',
  `office_id` int(11) DEFAULT '0' COMMENT '办事处id 0:本部 ',
  `invoice_id` int(10) DEFAULT NULL COMMENT '税号id（sal_clue_invoice）',
  `area` varchar(255) DEFAULT NULL COMMENT '面积',
  `district` bigint(20) DEFAULT NULL COMMENT '区域id（sal_cust_district）',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `cust_person` varchar(255) DEFAULT NULL COMMENT '联络人',
  `cust_tel` varchar(255) DEFAULT NULL COMMENT '联络人电话',
  `cust_email` varchar(255) DEFAULT NULL COMMENT '联络人电邮',
  `cust_person_role` varchar(255) DEFAULT NULL COMMENT '联络人职务',
  `latitude` varchar(255) DEFAULT NULL COMMENT '经度',
  `longitude` varchar(255) DEFAULT NULL COMMENT '纬度',
  `store_remark` text COMMENT '备注',
  `z_display` int(11) DEFAULT '1' COMMENT '0:不显示 1：显示',
  `u_id` int(11) DEFAULT NULL COMMENT '派单系统id',
  `u_group_id` int(11) DEFAULT NULL,
  `report_id` int(11) DEFAULT NULL COMMENT '导入时excel的id',
  `yewudalei` int(11) DEFAULT NULL COMMENT '业务大类：KA，地推',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8 COMMENT='客户线索门店表';

-- ----------------------------
-- Table structure for sal_clue_u_area
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_u_area`;
CREATE TABLE `sal_clue_u_area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `city_code` varchar(100) NOT NULL COMMENT '城市编号',
  `city_type` int(1) NOT NULL DEFAULT '0' COMMENT '1：本部 0：非本部',
  `u_id` int(11) DEFAULT NULL COMMENT '派单系统id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=932 DEFAULT CHARSET=utf8 COMMENT='项目所属区域';

-- ----------------------------
-- Table structure for sal_clue_u_staff
-- ----------------------------
DROP TABLE IF EXISTS `sal_clue_u_staff`;
CREATE TABLE `sal_clue_u_staff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `employee_id` int(11) NOT NULL COMMENT '城市编号',
  `employee_type` int(1) NOT NULL DEFAULT '0' COMMENT '1：主要负责人 0：其它负责人',
  `u_id` int(11) DEFAULT NULL COMMENT '派单系统id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=863 DEFAULT CHARSET=utf8 COMMENT='项目负责人';

-- ----------------------------
-- Table structure for sal_contpro
-- ----------------------------
DROP TABLE IF EXISTS `sal_contpro`;
CREATE TABLE `sal_contpro` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cont_id` int(11) NOT NULL,
  `clue_id` int(11) NOT NULL,
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:地推 2:KA',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `pro_code` varchar(255) DEFAULT NULL COMMENT '操作',
  `pro_type` char(255) NOT NULL DEFAULT 'N' COMMENT '操作类型 C：续约',
  `pro_num` int(11) NOT NULL DEFAULT '1' COMMENT '同类型操作次数',
  `pro_date` date NOT NULL COMMENT '操作生效时间',
  `pro_remark` text,
  `pro_status` int(11) NOT NULL DEFAULT '0' COMMENT '操作进行中的状态',
  `pro_change` double(11,2) DEFAULT '0.00' COMMENT '变更金额',
  `city` varchar(255) NOT NULL COMMENT '城市',
  `cont_code` varchar(255) DEFAULT NULL COMMENT '合同编号',
  `sales_id` int(11) NOT NULL COMMENT '销售id',
  `other_sales_id` int(10) DEFAULT NULL COMMENT '跨区销售id',
  `lbs_main` int(11) NOT NULL COMMENT '主体公司',
  `predict_amt` double(11,2) NOT NULL COMMENT '预估金额',
  `store_sum` int(11) NOT NULL DEFAULT '0' COMMENT '门店总数量',
  `total_sum` int(11) DEFAULT NULL COMMENT '总次数',
  `total_amt` double(11,2) DEFAULT NULL COMMENT '总金额',
  `cont_status` int(11) NOT NULL DEFAULT '0' COMMENT '合同状态  0：草稿 1：已发送 9:已驳回 10: 合同已生效 20：待印章 30：审批通过',
  `effect_date` date DEFAULT NULL COMMENT '生效日期',
  `stop_date` date DEFAULT NULL COMMENT '终止或暂停日期',
  `surplus_num` int(11) DEFAULT NULL COMMENT '剩余次数',
  `surplus_amt` double(11,2) DEFAULT NULL COMMENT '剩余金额',
  `con_v_type` int(10) DEFAULT NULL COMMENT '合同类型',
  `cont_type` int(4) DEFAULT NULL COMMENT '合约类型 1:普通合约 2：框架合约',
  `cont_start_dt` date DEFAULT NULL COMMENT '合约开始时间',
  `cont_end_dt` date DEFAULT NULL COMMENT '合约结束时间',
  `cont_month_len` int(4) DEFAULT NULL COMMENT '合同月份',
  `sign_type` int(4) DEFAULT '1' COMMENT '签约类型 1：新增 2：续约',
  `sign_date` date DEFAULT NULL COMMENT '签约时间',
  `is_seal` char(1) DEFAULT 'Y' COMMENT '是否印章',
  `seal_type_id` int(11) DEFAULT NULL COMMENT '印章类型id',
  `prioritize_service` char(1) DEFAULT 'Y' COMMENT '是否优先安排服务,Y:是 N：否',
  `prioritize_seal` char(1) DEFAULT 'Y' COMMENT '是否客户先用印,Y:是 N：否',
  `group_bool` char(1) DEFAULT 'Y' COMMENT '是否集团客户 Y:是 N:否',
  `service_timer` int(4) DEFAULT NULL COMMENT '服务时长，分钟',
  `pay_type` int(11) DEFAULT NULL COMMENT '付款方式（sal_pay）',
  `pay_week` int(11) DEFAULT NULL COMMENT '付款周期(日报表系统swo_payweek)',
  `pay_month` int(4) DEFAULT NULL COMMENT '预付月数',
  `pay_start` int(4) DEFAULT NULL COMMENT '起始月',
  `deposit_need` double(11,2) DEFAULT NULL COMMENT '所需押金',
  `deposit_amt` double(11,2) DEFAULT NULL COMMENT '已收押金',
  `deposit_rmk` text COMMENT '押金备注',
  `fee_type` int(4) DEFAULT NULL COMMENT '收费方式',
  `settle_type` int(4) DEFAULT NULL COMMENT '结算方式',
  `bill_day` int(4) DEFAULT NULL COMMENT '账单日',
  `bill_bool` char(1) DEFAULT 'Y' COMMENT '是否对账 Y:是 N:否',
  `receivable_day` int(4) DEFAULT NULL COMMENT '应收期限',
  `is_renewal` char(1) DEFAULT NULL COMMENT '是否自动续约',
  `profit_int` int(10) DEFAULT NULL COMMENT '毛利区间',
  `area_bool` char(1) DEFAULT 'Y' COMMENT '是否面积计算价格 Y:是 N:否',
  `area_json` text,
  `yewudalei` int(11) DEFAULT NULL,
  `other_yewudalei` int(10) DEFAULT NULL COMMENT '跨区业务大类',
  `busine_id` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `busine_id_text` varchar(255) DEFAULT NULL,
  `remark` text COMMENT '备注',
  `mh_remark` text COMMENT '门户审批意见',
  `u_id` int(11) DEFAULT NULL,
  `mh_id` varchar(100) DEFAULT NULL COMMENT '对应的门户网站id',
  `report_id` int(11) DEFAULT NULL COMMENT '导入时excel的id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 COMMENT='合同表';

-- ----------------------------
-- Table structure for sal_contpro_file
-- ----------------------------
DROP TABLE IF EXISTS `sal_contpro_file`;
CREATE TABLE `sal_contpro_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_id` int(11) NOT NULL,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `cont_id` int(11) NOT NULL COMMENT '合同id',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '文件分组0:普通文件 1：印章文件',
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='合约附件表';

-- ----------------------------
-- Table structure for sal_contpro_sse
-- ----------------------------
DROP TABLE IF EXISTS `sal_contpro_sse`;
CREATE TABLE `sal_contpro_sse` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_id` int(11) NOT NULL,
  `cont_id` int(11) NOT NULL,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `clue_store_id` int(11) NOT NULL COMMENT '门店id',
  `create_staff` int(11) NOT NULL COMMENT '创建人',
  `store_amt` double(11,2) NOT NULL DEFAULT '0.00' COMMENT '门店服务总金额',
  `service_sum` int(11) NOT NULL DEFAULT '0' COMMENT '服务总次数',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '分组id（流程查看使用）',
  `update_bool` int(11) DEFAULT '1' COMMENT '是否允许修改 1：允许',
  `busine_id` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `busine_id_text` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `remark` text COMMENT '备注',
  `detail_json` text,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8 COMMENT='合约关联门店表';

-- ----------------------------
-- Table structure for sal_contpro_virtual
-- ----------------------------
DROP TABLE IF EXISTS `sal_contpro_virtual`;
CREATE TABLE `sal_contpro_virtual` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_vir_type` int(2) NOT NULL DEFAULT '1' COMMENT '1:主合约变更 2：虚拟合约变更 3:呼叫式',
  `pro_id` int(11) DEFAULT NULL COMMENT '主合约的变更id',
  `vir_batch_id` int(11) DEFAULT NULL COMMENT '虚拟合约变更id',
  `call_id` int(11) DEFAULT NULL COMMENT '呼叫式id',
  `vir_id` int(11) NOT NULL,
  `pro_code` varchar(255) DEFAULT NULL,
  `pro_type` char(255) NOT NULL DEFAULT 'N' COMMENT '操作类型 C：续约',
  `pro_num` int(11) NOT NULL DEFAULT '1' COMMENT '同类型操作次数',
  `pro_date` date NOT NULL,
  `pro_remark` text,
  `pro_status` int(11) NOT NULL DEFAULT '0' COMMENT '操作进行中的状态',
  `pro_change` double(11,2) NOT NULL DEFAULT '0.00' COMMENT '变更金额',
  `cont_id` int(11) NOT NULL COMMENT '主合同id',
  `sse_id` int(11) NOT NULL,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:地推 2:KA',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `clue_store_id` int(11) NOT NULL COMMENT '门店id',
  `vir_code` varchar(255) DEFAULT NULL COMMENT '虚拟合约编号',
  `vir_status` int(11) NOT NULL DEFAULT '0' COMMENT '0:草稿',
  `sign_type` int(11) DEFAULT '1' COMMENT '签约类型 1：新增 2：续约',
  `city` varchar(100) NOT NULL COMMENT '城市',
  `office_id` int(11) DEFAULT NULL COMMENT '办事处',
  `busine_id` varchar(255) NOT NULL COMMENT '服务项目（单个）',
  `service_type` int(11) NOT NULL,
  `con_v_type` int(10) DEFAULT NULL COMMENT '合同类型',
  `receivable_day` int(4) DEFAULT NULL COMMENT '应收期限',
  `is_renewal` char(1) DEFAULT NULL COMMENT '是否自动续约',
  `call_fre_amt` double(11,2) DEFAULT NULL COMMENT '呼叫式单次金额',
  `profit_int` int(10) DEFAULT NULL COMMENT '毛利区间',
  `prioritize_seal` char(1) DEFAULT 'Y' COMMENT '是否客户先用印',
  `seal_type_id` int(10) DEFAULT NULL COMMENT '印章id',
  `is_seal` char(1) DEFAULT 'Y' COMMENT '是否印章',
  `bill_bool` char(1) DEFAULT 'Y' COMMENT '是否对账 Y:是 N:否',
  `bill_day` int(4) DEFAULT NULL COMMENT '账单日',
  `settle_type` int(4) DEFAULT NULL COMMENT '结算方式',
  `fee_type` int(4) DEFAULT NULL COMMENT '收费方式',
  `deposit_rmk` text COMMENT '押金备注',
  `deposit_amt` double(11,2) DEFAULT NULL COMMENT '已收押金',
  `deposit_need` double(11,2) DEFAULT NULL COMMENT '所需押金',
  `pay_start` int(4) DEFAULT NULL COMMENT '起始月',
  `pay_month` int(4) DEFAULT NULL COMMENT '预付月数',
  `pay_type` int(11) DEFAULT NULL COMMENT '付款方式（sal_pay）',
  `pay_week` int(11) DEFAULT NULL COMMENT '付款周期(日报表系统swo_payweek)',
  `service_timer` int(4) DEFAULT NULL COMMENT '服务时长，分钟',
  `prioritize_service` char(1) DEFAULT 'Y' COMMENT '是否优先安排服务,Y:是 N：否',
  `sign_date` date DEFAULT NULL COMMENT '签约时间',
  `yewudalei` int(11) DEFAULT NULL,
  `other_yewudalei` int(10) DEFAULT NULL COMMENT '跨区业务大类',
  `lbs_main` int(11) NOT NULL COMMENT '主体公司',
  `service_main` int(10) DEFAULT NULL COMMENT '服务主体',
  `busine_id_text` varchar(255) NOT NULL COMMENT '服务项目（单个）',
  `create_staff` int(11) NOT NULL COMMENT '创建人',
  `sales_id` int(11) NOT NULL COMMENT '业务员id',
  `month_amt` double(11,2) DEFAULT NULL COMMENT '月金额',
  `year_amt` double(11,2) DEFAULT '0.00' COMMENT '年金额',
  `service_sum` int(11) DEFAULT NULL COMMENT '服务总次数',
  `surplus_num` int(11) DEFAULT NULL COMMENT '剩余次数',
  `surplus_amt` double(11,2) DEFAULT NULL COMMENT '剩余金额',
  `service_fre_amt` double(11,2) DEFAULT NULL COMMENT '服务频次总金额',
  `service_fre_sum` int(11) NOT NULL DEFAULT '0' COMMENT '服务频次总次数',
  `service_fre_type` int(11) NOT NULL DEFAULT '0' COMMENT '服务频次类型 1：每月 2：自定义 3：呼叫式',
  `service_fre_json` text COMMENT '服务频次',
  `service_fre_text` text COMMENT '服务频次(文字)',
  `cont_start_dt` date DEFAULT NULL COMMENT '合约开始时间',
  `cont_end_dt` date DEFAULT NULL COMMENT '合约结束时间',
  `cont_month_len` int(11) DEFAULT NULL COMMENT '合同月份',
  `effect_date` date DEFAULT NULL COMMENT '生效日期',
  `fast_date` date DEFAULT NULL COMMENT '常规开始日期',
  `first_date` date DEFAULT NULL COMMENT '首次日期',
  `need_install` char(1) DEFAULT 'N' COMMENT '是否需安装 Y:是 N：否',
  `amt_install` double(11,2) DEFAULT NULL COMMENT '装机金额',
  `other_sales_id` int(11) DEFAULT NULL COMMENT '被跨区业务员',
  `first_tech_id` int(11) DEFAULT NULL COMMENT '首次技术员',
  `technician_id_str` varchar(255) DEFAULT NULL COMMENT '负责技术员',
  `technician_id_text` text,
  `external_source` varchar(255) DEFAULT NULL COMMENT '外部数据来源',
  `stop_set_id` int(11) DEFAULT NULL,
  `stop_date` date DEFAULT NULL COMMENT '终止或暂停日期',
  `stop_month_amt` double(11,2) DEFAULT NULL COMMENT '已使用但数据错误',
  `stop_year_amt` double(11,2) DEFAULT NULL COMMENT '已使用但数据错误',
  `stop_sum_amt` double(11,2) DEFAULT NULL COMMENT '已使用但数据错误',
  `jq_sum` int(11) DEFAULT '0' COMMENT '原机器数量',
  `jq_sum_back` int(11) DEFAULT '0' COMMENT '机器拆回数量',
  `need_back` char(1) DEFAULT NULL COMMENT '是否需要拆机Y:是 N：否',
  `need_back_json` text COMMENT '拆回设备json',
  `month_cycle` varchar(255) DEFAULT NULL,
  `week_cycle` varchar(255) DEFAULT NULL,
  `day_cycle` varchar(255) DEFAULT NULL,
  `invoice_amount` double(11,2) DEFAULT NULL COMMENT '发票金额',
  `remark` text COMMENT '备注',
  `detail_json` text,
  `mh_id` varchar(100) DEFAULT NULL,
  `u_id` int(11) DEFAULT NULL COMMENT '派单系统id',
  `u_service_json` text COMMENT '派单系统服务',
  `report_id` int(11) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=354 DEFAULT CHARSET=utf8 COMMENT='合同的虚拟合同表';

-- ----------------------------
-- Table structure for sal_contract
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract`;
CREATE TABLE `sal_contract` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:地推 2:KA',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `city` varchar(255) NOT NULL COMMENT '城市',
  `cont_code` varchar(255) DEFAULT NULL COMMENT '合同编号',
  `sales_id` int(11) NOT NULL COMMENT '销售id',
  `other_sales_id` int(10) DEFAULT NULL COMMENT '跨区销售id',
  `lbs_main` int(11) NOT NULL COMMENT '主体公司（sal_main_lbs）',
  `predict_amt` double(11,2) NOT NULL COMMENT '预估金额',
  `store_sum` int(11) NOT NULL DEFAULT '0' COMMENT '门店总数量',
  `total_sum` int(11) DEFAULT NULL COMMENT '总次数',
  `total_amt` double(11,2) DEFAULT NULL COMMENT '总金额',
  `cont_status` int(11) NOT NULL DEFAULT '0' COMMENT '合同状态  0：草稿 1：已发送 9:已驳回 10: 合同已生效 20：待印章 30：审批通过 40：暂停 50：终止',
  `effect_date` date DEFAULT NULL COMMENT '生效日期',
  `stop_date` date DEFAULT NULL COMMENT '终止或暂停日期',
  `surplus_num` int(11) DEFAULT NULL COMMENT '剩余次数',
  `surplus_amt` double(11,2) DEFAULT NULL COMMENT '剩余金额',
  `con_v_type` int(10) DEFAULT NULL COMMENT '合同类型',
  `cont_type` int(4) DEFAULT NULL COMMENT '合约类型 1:普通合约 2：框架合约',
  `cont_start_dt` date DEFAULT NULL COMMENT '合约开始时间',
  `cont_end_dt` date DEFAULT NULL COMMENT '合约结束时间',
  `cont_month_len` int(4) DEFAULT NULL COMMENT '合同月份',
  `sign_type` int(4) DEFAULT '1' COMMENT '签约类型 1：新增 2：续约',
  `sign_date` date DEFAULT NULL COMMENT '签约时间',
  `is_seal` char(1) DEFAULT 'Y' COMMENT '是否印章',
  `seal_type_id` int(11) DEFAULT NULL COMMENT '印章类型id',
  `prioritize_service` char(1) DEFAULT 'Y' COMMENT '是否优先安排服务,Y:是 N：否',
  `prioritize_seal` char(1) DEFAULT 'Y' COMMENT '是否客户先用印,Y:是 N：否',
  `group_bool` char(1) DEFAULT 'Y' COMMENT '是否集团客户 Y:是 N:否',
  `service_timer` int(4) DEFAULT NULL COMMENT '服务时长，分钟',
  `pay_type` int(11) DEFAULT NULL COMMENT '付款方式（sal_pay）',
  `pay_week` int(11) DEFAULT NULL COMMENT '付款周期(日报表系统swo_payweek)',
  `pay_month` int(4) DEFAULT NULL COMMENT '预付月数',
  `pay_start` int(4) DEFAULT NULL COMMENT '起始月',
  `deposit_need` double(11,2) DEFAULT NULL COMMENT '所需押金',
  `deposit_amt` double(11,2) DEFAULT NULL COMMENT '已收押金',
  `deposit_rmk` text COMMENT '押金备注',
  `fee_type` int(4) DEFAULT NULL COMMENT '收费方式',
  `settle_type` int(4) DEFAULT NULL COMMENT '结算方式',
  `bill_day` int(4) DEFAULT NULL COMMENT '账单日',
  `bill_bool` char(1) DEFAULT 'Y' COMMENT '是否对账 Y:是 N:否',
  `receivable_day` int(4) DEFAULT NULL COMMENT '应收期限',
  `is_renewal` char(1) DEFAULT NULL COMMENT '是否自动续约',
  `profit_int` int(10) DEFAULT NULL COMMENT '毛利区间',
  `area_bool` char(1) DEFAULT 'Y' COMMENT '是否面积计算价格 Y:是 N:否',
  `area_json` text,
  `yewudalei` int(11) DEFAULT NULL,
  `other_yewudalei` int(10) DEFAULT NULL COMMENT '跨区业务大类',
  `busine_id` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `busine_id_text` varchar(255) DEFAULT NULL,
  `remark` text COMMENT '备注',
  `mh_remark` text COMMENT '门户审批意见',
  `u_id` int(11) DEFAULT NULL COMMENT '派单系统id',
  `mh_id` varchar(100) DEFAULT NULL COMMENT '对应的门户网站id',
  `report_id` int(11) DEFAULT NULL COMMENT '导入时excel的id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COMMENT='主合同表';

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
  `pro_ids` text COMMENT '记录虚拟合同id(不使用)',
  `call_remark` text,
  `mh_remark` text,
  `city` varchar(255) DEFAULT NULL,
  `mh_id` varchar(255) DEFAULT NULL COMMENT '门户审核id',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='呼叫式服务的呼叫记录';

-- ----------------------------
-- Table structure for sal_contract_file
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract_file`;
CREATE TABLE `sal_contract_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `cont_id` int(11) NOT NULL COMMENT '合同id',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '文件分组0:普通文件 1：印章文件',
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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='合约附件表';

-- ----------------------------
-- Table structure for sal_contract_history
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract_history`;
CREATE TABLE `sal_contract_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_type` int(11) NOT NULL DEFAULT '5' COMMENT '5：主合约记录  6：主合约变更记录 7：虚拟合约记录 8：虚拟合约变更记录 9：呼叫式记录',
  `table_id` int(11) NOT NULL,
  `opr_id` int(11) NOT NULL DEFAULT '0' COMMENT '历史id',
  `history_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:新增 2:修改 3:删除 30:门户流程审批 40：合约变更',
  `history_html` text COMMENT '修改内容',
  `expr_data` varchar(255) DEFAULT NULL COMMENT '额外数据',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=693 DEFAULT CHARSET=utf8 COMMENT='客户线索记录表';

-- ----------------------------
-- Table structure for sal_contract_sse
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract_sse`;
CREATE TABLE `sal_contract_sse` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cont_id` int(11) NOT NULL,
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `clue_store_id` int(11) NOT NULL COMMENT '门店id',
  `create_staff` int(11) NOT NULL COMMENT '创建人',
  `store_amt` double(11,2) NOT NULL DEFAULT '0.00' COMMENT '门店服务总金额',
  `service_sum` int(11) NOT NULL DEFAULT '0' COMMENT '服务总次数',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '分组id（流程查看使用）',
  `update_bool` int(11) DEFAULT '1' COMMENT '是否允许修改 1：允许',
  `busine_id` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `busine_id_text` varchar(255) DEFAULT NULL COMMENT '服务项目',
  `remark` text COMMENT '备注',
  `detail_json` text,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8 COMMENT='合约关联门店表';

-- ----------------------------
-- Table structure for sal_contract_vir_info
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract_vir_info`;
CREATE TABLE `sal_contract_vir_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `virtual_id` int(10) unsigned NOT NULL COMMENT '虚拟合同id',
  `service_type_id` int(11) DEFAULT NULL,
  `field_id` varchar(30) NOT NULL COMMENT '门店包含的字段',
  `field_value` varchar(2000) DEFAULT NULL COMMENT '字段的值',
  `u_id` int(11) DEFAULT NULL,
  `is_del` int(1) NOT NULL DEFAULT '0' COMMENT '0:未删除 1：已删除',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `visitinfo` (`virtual_id`,`field_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5645 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='虚拟合同包含的字段及对应的值';

-- ----------------------------
-- Table structure for sal_contract_vir_staff
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract_vir_staff`;
CREATE TABLE `sal_contract_vir_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vir_id` int(10) unsigned NOT NULL COMMENT '虚拟合同id',
  `type` int(1) NOT NULL COMMENT '1：工单 2：跟进单 4：主要销售 5跨区销售',
  `employee_id` int(10) NOT NULL COMMENT '员工id',
  `u_yewudalei` varchar(10) DEFAULT NULL COMMENT '业务大类派单系统标识',
  `role` varchar(10) DEFAULT NULL COMMENT '1:主要人员 0：协作人员',
  `u_id` int(11) DEFAULT NULL,
  `is_del` int(1) NOT NULL DEFAULT '0' COMMENT '0:未删除 1：已删除',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `visitinfo` (`vir_id`,`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4198 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='合约和员工关联数据(仅提供给派单系统)';

-- ----------------------------
-- Table structure for sal_contract_vir_week
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract_vir_week`;
CREATE TABLE `sal_contract_vir_week` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vir_id` int(10) unsigned NOT NULL COMMENT '虚拟合同id',
  `year_cycle` int(4) DEFAULT NULL COMMENT '年份',
  `month_cycle` int(10) DEFAULT NULL COMMENT '月份-1的2次幂',
  `week_cycle` int(10) DEFAULT NULL COMMENT '周数-1的2次幂',
  `day_cycle` int(10) DEFAULT NULL COMMENT '天数的2次幂',
  `unit_price` decimal(11,2) DEFAULT NULL,
  `contract_date` date DEFAULT NULL,
  `cycle_text` varchar(255) DEFAULT NULL COMMENT '第n周周m',
  `u_id` int(11) DEFAULT NULL,
  `is_del` int(1) NOT NULL DEFAULT '0' COMMENT '0:未删除 1：已删除',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7489 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='合约和频次关联数据(仅提供给派单系统)';

-- ----------------------------
-- Table structure for sal_contract_virtual
-- ----------------------------
DROP TABLE IF EXISTS `sal_contract_virtual`;
CREATE TABLE `sal_contract_virtual` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cont_id` int(11) NOT NULL COMMENT '主合同id',
  `sse_id` int(11) NOT NULL COMMENT '主合同关联虚拟合同（该id或许不存在）',
  `clue_id` int(11) NOT NULL COMMENT '线索id',
  `clue_type` int(11) NOT NULL DEFAULT '1' COMMENT '1:地推 2:KA',
  `clue_service_id` int(11) NOT NULL COMMENT '商机id',
  `clue_store_id` int(11) NOT NULL COMMENT '门店id',
  `vir_code` varchar(255) DEFAULT NULL COMMENT '虚拟合约编号',
  `vir_status` int(11) NOT NULL DEFAULT '0' COMMENT '0：草稿 1：已发送 9:已驳回 10: 合同已生效 20：待印章 30：审批通过 40：暂停 50：终止',
  `sign_type` int(11) DEFAULT '1' COMMENT '签约类型 1：新增 2：续约',
  `city` varchar(100) NOT NULL COMMENT '城市',
  `office_id` int(11) DEFAULT NULL COMMENT '办事处',
  `busine_id` varchar(255) NOT NULL COMMENT '服务项目（单个）',
  `service_type` varchar(255) NOT NULL,
  `con_v_type` int(10) DEFAULT NULL COMMENT '合同类型',
  `receivable_day` int(4) DEFAULT NULL COMMENT '应收期限',
  `is_renewal` char(1) DEFAULT NULL COMMENT '是否自动续约',
  `call_fre_amt` double(11,2) DEFAULT NULL COMMENT '呼叫式单次金额',
  `profit_int` int(10) DEFAULT NULL COMMENT '毛利区间',
  `prioritize_seal` char(1) DEFAULT 'Y' COMMENT '是否客户先用印',
  `seal_type_id` int(10) DEFAULT NULL COMMENT '印章id',
  `is_seal` char(1) DEFAULT 'Y' COMMENT '是否印章',
  `bill_bool` char(1) DEFAULT 'Y' COMMENT '是否对账 Y:是 N:否',
  `bill_day` int(4) DEFAULT NULL COMMENT '账单日',
  `settle_type` int(4) DEFAULT NULL COMMENT '结算方式',
  `fee_type` int(4) DEFAULT NULL COMMENT '收费方式',
  `deposit_rmk` text COMMENT '押金备注',
  `deposit_amt` double(11,2) DEFAULT NULL COMMENT '已收押金',
  `deposit_need` double(11,2) DEFAULT NULL COMMENT '所需押金',
  `pay_start` int(4) DEFAULT NULL COMMENT '起始月',
  `pay_month` int(4) DEFAULT NULL COMMENT '预付月数',
  `pay_type` int(11) DEFAULT NULL COMMENT '付款方式（sal_pay）',
  `pay_week` int(11) DEFAULT NULL COMMENT '付款周期(日报表系统swo_payweek)',
  `service_timer` int(4) DEFAULT NULL COMMENT '服务时长，分钟',
  `prioritize_service` char(1) DEFAULT 'Y' COMMENT '是否优先安排服务,Y:是 N：否',
  `sign_date` date DEFAULT NULL COMMENT '签约时间',
  `yewudalei` int(11) DEFAULT NULL,
  `other_yewudalei` int(10) DEFAULT NULL COMMENT '跨区业务大类',
  `lbs_main` int(11) NOT NULL COMMENT '主体公司（sal_main_lbs）',
  `service_main` int(10) DEFAULT NULL COMMENT '服务主体',
  `busine_id_text` varchar(255) NOT NULL COMMENT '服务项目（单个）',
  `create_staff` int(11) NOT NULL COMMENT '创建人',
  `sales_id` int(11) NOT NULL COMMENT '业务员id',
  `month_amt` double(11,2) DEFAULT NULL COMMENT '月金额',
  `year_amt` double(11,2) DEFAULT '0.00' COMMENT '年金额',
  `service_sum` int(11) DEFAULT NULL COMMENT '服务总次数',
  `surplus_num` int(11) DEFAULT NULL COMMENT '剩余次数',
  `surplus_amt` double(11,2) DEFAULT NULL COMMENT '剩余金额',
  `service_fre_amt` double(11,2) DEFAULT NULL COMMENT '服务频次总金额',
  `service_fre_sum` int(11) NOT NULL DEFAULT '0' COMMENT '服务频次总次数',
  `service_fre_type` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '服务频次类型 1：每月 2：自定义 3：呼叫式',
  `service_fre_json` text COMMENT '服务频次',
  `service_fre_text` text COMMENT '服务频次(文字)',
  `cont_start_dt` date DEFAULT NULL COMMENT '合约开始时间',
  `cont_end_dt` date DEFAULT NULL COMMENT '合约结束时间',
  `cont_month_len` int(11) DEFAULT NULL COMMENT '合同月份',
  `effect_date` date DEFAULT NULL COMMENT '生效日期',
  `fast_date` date DEFAULT NULL COMMENT '常规开始日期',
  `first_date` date DEFAULT NULL COMMENT '首次日期',
  `need_install` char(1) DEFAULT 'N' COMMENT '是否需安装 Y:是 N：否',
  `amt_install` double(11,2) DEFAULT NULL COMMENT '装机金额',
  `other_sales_id` int(11) DEFAULT NULL COMMENT '被跨区业务员',
  `first_tech_id` int(11) DEFAULT NULL COMMENT '首次技术员',
  `technician_id_str` varchar(255) DEFAULT NULL COMMENT '负责技术员',
  `technician_id_text` text,
  `external_source` varchar(255) DEFAULT NULL COMMENT '外部数据来源',
  `stop_set_id` int(11) DEFAULT NULL COMMENT '终止原因id（sal_cont_str）',
  `stop_date` date DEFAULT NULL COMMENT '终止或暂停日期',
  `stop_month_amt` double(11,2) DEFAULT NULL COMMENT '涉及终止月金额',
  `stop_year_amt` double(11,2) DEFAULT NULL COMMENT '涉及终止年金额',
  `stop_sum_amt` double(11,2) DEFAULT NULL COMMENT '涉及终止总金额',
  `jq_sum` int(11) DEFAULT '0' COMMENT '原机器数量',
  `jq_sum_back` varchar(255) DEFAULT NULL COMMENT '机器拆回数量',
  `need_back` char(1) DEFAULT NULL COMMENT '是否需要拆机Y:是 N：否',
  `need_back_json` text COMMENT '拆回设备json',
  `month_cycle` varchar(255) DEFAULT NULL,
  `week_cycle` varchar(255) DEFAULT NULL,
  `day_cycle` varchar(255) DEFAULT NULL,
  `invoice_amount` double(11,2) DEFAULT NULL COMMENT '发票金额 固定频次传月金额 非固定传年金额',
  `remark` text COMMENT '备注',
  `detail_json` text COMMENT '设备等信息',
  `mh_id` varchar(100) DEFAULT NULL,
  `u_id` int(11) DEFAULT NULL COMMENT '派单系统id',
  `u_service_json` text COMMENT '派单系统服务',
  `report_id` int(11) DEFAULT NULL COMMENT '导入时excel的id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=199 DEFAULT CHARSET=utf8 COMMENT='合同的虚拟合同表';

-- ----------------------------
-- Table structure for sal_import_queue
-- ----------------------------
DROP TABLE IF EXISTS `sal_import_queue`;
CREATE TABLE `sal_import_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `import_type` varchar(100) NOT NULL COMMENT '导入类型',
  `city_allow` varchar(5000) DEFAULT NULL COMMENT '导入账号的城市权限',
  `req_dt` datetime DEFAULT NULL COMMENT '导入时间',
  `fin_dt` datetime DEFAULT NULL COMMENT '完成时间',
  `username` varchar(30) NOT NULL COMMENT '导入账号',
  `status` char(1) NOT NULL COMMENT '导入状态，P待处理 E：失败 C：成功',
  `u_status` char(1) NOT NULL DEFAULT 'P' COMMENT '同步派单状态，P待处理 E：失败 C：已发送',
  `success_num` int(10) DEFAULT NULL COMMENT '成功数量',
  `error_num` int(10) DEFAULT NULL COMMENT '失败数量',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `import_name` varchar(255) DEFAULT NULL COMMENT '文件名',
  `import_file` varchar(255) DEFAULT NULL COMMENT '导入文件地址',
  `message` text COMMENT '说明',
  `error_file` longblob COMMENT '报错后文件地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sal_virtual_batch
-- ----------------------------
DROP TABLE IF EXISTS `sal_virtual_batch`;
CREATE TABLE `sal_virtual_batch` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pro_code` varchar(255) DEFAULT NULL COMMENT '操作',
  `pro_type` char(255) NOT NULL DEFAULT 'N' COMMENT '操作类型 C：续约',
  `pro_date` date NOT NULL COMMENT '操作生效时间',
  `pro_remark` text,
  `pro_status` int(11) NOT NULL DEFAULT '0' COMMENT '操作进行中的状态',
  `pro_change` double(11,2) NOT NULL DEFAULT '0.00',
  `city` varchar(255) NOT NULL COMMENT '城市',
  `create_staff` int(11) DEFAULT NULL COMMENT '创建员工',
  `vir_id` int(11) NOT NULL,
  `vir_id_text` text,
  `vir_code_text` text,
  `busine_id` varchar(255) DEFAULT NULL,
  `busine_id_text` varchar(255) DEFAULT NULL,
  `stop_set_id` int(11) DEFAULT NULL COMMENT '终止、暂停原因id',
  `stop_date` date DEFAULT NULL COMMENT '终止或暂停日期',
  `stop_month_amt` double(11,2) DEFAULT NULL COMMENT '涉及月金额',
  `stop_year_amt` double(11,2) DEFAULT NULL COMMENT '涉及年金额',
  `stop_sum_amt` double(11,2) DEFAULT NULL COMMENT '涉及总金额（未使用）',
  `need_back` char(1) DEFAULT NULL COMMENT '是否需要拆机Y:是 N：否',
  `need_back_json` text COMMENT '拆回设备json',
  `surplus_num` int(11) DEFAULT NULL COMMENT '剩余次数',
  `surplus_amt` double(11,2) DEFAULT NULL COMMENT '剩余金额',
  `surplus_json` text COMMENT '剩余金额的json',
  `mh_remark` text COMMENT '门户审批意见',
  `mh_id` varchar(100) DEFAULT NULL COMMENT '对应的门户网站id',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='虚拟变更表批量修改、暂停、终止、恢复';

-- ----------------------------
-- Table structure for sal_virtual_batch_file
-- ----------------------------
DROP TABLE IF EXISTS `sal_virtual_batch_file`;
CREATE TABLE `sal_virtual_batch_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vir_batch_id` int(11) NOT NULL,
  `vir_id_text` text NOT NULL,
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '文件分组0:普通文件 1：印章文件',
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='虚拟合约变更附件表';
