-- ----------------------------
-- Table structure for sal_ka_bot
-- ----------------------------
ALTER TABLE sal_ka_bot ADD COLUMN `work_email`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '业务联系人邮箱' AFTER contact_dept;
ALTER TABLE sal_ka_bot ADD COLUMN `work_phone`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '业务联系人电话' AFTER contact_dept;
ALTER TABLE sal_ka_bot ADD COLUMN `work_user`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '业务联系人' AFTER contact_dept;
ALTER TABLE sal_ka_bot ADD COLUMN `con_email`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '合同联系人邮箱' AFTER contact_dept;
ALTER TABLE sal_ka_bot ADD COLUMN `con_phone`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '合同联系人电话' AFTER contact_dept;
ALTER TABLE sal_ka_bot ADD COLUMN `con_user`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '合同联系人' AFTER contact_dept;
ALTER TABLE sal_ka_bot ADD COLUMN `contact_adr` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联系人地址' AFTER contact_dept;

ALTER TABLE sal_ka_bot ADD COLUMN `ava_show_date`  date NULL DEFAULT NULL COMMENT '列表需要显示的可成交日期' AFTER available_date;
ALTER TABLE sal_ka_bot ADD COLUMN `busine_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '业务模式（多个用、分割）翻译' AFTER busine_id;

alter table sal_ka_bot modify column busine_id varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '业务模式（多个用,分割）';

UPDATE sal_ka_bot SET ava_show_date=available_date WHERE ava_show_date IS NULL;

-- ----------------------------
-- Table structure for sal_ka_bot_ava
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_bot_ava`;
CREATE TABLE `sal_ka_bot_ava` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `ava_date` date NOT NULL COMMENT '可成交日期',
  `ava_amt` decimal(13,2) DEFAULT NULL COMMENT '可成交金额',
  `ava_rate` int(4) DEFAULT NULL COMMENT '预估成交%',
  `ava_fact_amt` decimal(13,2) DEFAULT NULL COMMENT '实际成交金额',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='KA客户的可成交日期列表（一对多）';
