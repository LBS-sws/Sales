
ALTER TABLE sal_ka_bot ADD COLUMN shift_bool int(1) NULL DEFAULT 0 COMMENT '是否已转移：0：否 1：是' AFTER city;
ALTER TABLE sal_ra_bot ADD COLUMN shift_bool int(1) NULL DEFAULT 0 COMMENT '是否已转移：0：否 1：是' AFTER city;
ALTER TABLE sal_ca_bot ADD COLUMN shift_bool int(1) NULL DEFAULT 0 COMMENT '是否已转移：0：否 1：是' AFTER city;


DROP TABLE IF EXISTS `sal_ka_shift`;
CREATE TABLE `sal_ka_shift` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shift_from_tab` varchar(50) NOT NULL COMMENT '转移前数据表格式：_ka_，_ra_，_ca_',
  `shift_from_id` int(10) NOT NULL,
  `shift_from_staff` varchar(255) NOT NULL,
  `shift_to_tab` varchar(50) NOT NULL COMMENT '转移后数据表格式：_ka_，_ra_，_ca_',
  `shift_to_id` int(11) NOT NULL,
  `shift_to_staff` varchar(255) NOT NULL,
  `shift_remark` text COMMENT '转移说明',
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='KA客户转移数据表';

