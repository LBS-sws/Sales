
-- ----------------------------
-- Table structure for sal_ka_bot_renewal
-- ----------------------------
DROP TABLE IF EXISTS `sal_ka_bot_renewal`;
CREATE TABLE `sal_ka_bot_renewal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `renewal_date` date DEFAULT NULL COMMENT '跟進時間',
  `renewal_num` varchar(10) DEFAULT NULL,
  `renewal_city` varchar(255) DEFAULT NULL,
  `renewal_note` text COMMENT '跟進內容',
  `renewal_amt` decimal(13,2) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='KA客户续约表';

ALTER TABLE sal_ka_bot ADD COLUMN renewal_total_amt decimal(13,2) NULL DEFAULT NULL COMMENT '续约总金额' AFTER follow_date;
ALTER TABLE sal_ka_bot ADD COLUMN renewal_sum int(6) NULL DEFAULT NULL COMMENT '续约门店数量' AFTER follow_date;
ALTER TABLE sal_ka_bot ADD COLUMN contract_type varchar(11) NULL DEFAULT '1' COMMENT '合约类型：1：新增合约 2：续约合约 1,2：新增，续约合约' AFTER follow_date;

-- ----------------------------
-- Table structure for sal_ka_bot_renewal
-- ----------------------------
DROP TABLE IF EXISTS `sal_ca_bot_renewal`;
CREATE TABLE `sal_ca_bot_renewal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `renewal_date` date DEFAULT NULL COMMENT '跟進時間',
  `renewal_num` varchar(10) DEFAULT NULL,
  `renewal_city` varchar(255) DEFAULT NULL,
  `renewal_note` text COMMENT '跟進內容',
  `renewal_amt` decimal(13,2) DEFAULT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='KA客户续约表';

ALTER TABLE sal_ca_bot ADD COLUMN renewal_total_amt decimal(13,2) NULL DEFAULT NULL COMMENT '续约总金额' AFTER follow_date;
ALTER TABLE sal_ca_bot ADD COLUMN renewal_sum int(6) NULL DEFAULT NULL COMMENT '续约门店数量' AFTER follow_date;
ALTER TABLE sal_ca_bot ADD COLUMN contract_type varchar(11) NULL DEFAULT '1' COMMENT '合约类型：1：新增合约 2：续约合约 1,2：新增，续约合约' AFTER follow_date;
