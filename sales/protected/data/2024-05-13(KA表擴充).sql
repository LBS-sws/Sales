-- ----------------------------
-- Table structure for sal_ka_bot
-- ----------------------------
ALTER TABLE sal_ka_bot ADD COLUMN shift_bool int(1) NULL DEFAULT 0 COMMENT '是否已转移：0：否 1：是' AFTER city;
ALTER TABLE sal_ra_bot ADD COLUMN shift_bool int(1) NULL DEFAULT 0 COMMENT '是否已转移：0：否 1：是' AFTER city;
ALTER TABLE sal_ca_bot ADD COLUMN shift_bool int(1) NULL DEFAULT 0 COMMENT '是否已转移：0：否 1：是' AFTER city;
