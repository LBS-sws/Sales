-- ----------------------------
-- Table structure for sal_ka_bot
-- ----------------------------
alter table sal_ka_bot modify column talk_city_id varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '洽谈城市（多个用,分割）';

-- ----------------------------
-- Table structure for sal_ra_bot
-- ----------------------------
alter table sal_ra_bot modify column talk_city_id varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '洽谈城市（多个用,分割）';

-- ----------------------------
-- Table structure for sal_ca_bot
-- ----------------------------
alter table sal_ca_bot modify column talk_city_id varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '洽谈城市（多个用,分割）';
