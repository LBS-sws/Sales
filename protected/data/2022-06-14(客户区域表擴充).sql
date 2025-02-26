
-- ----------------------------
-- Table structure for sal_cust_district
-- ----------------------------
ALTER TABLE sal_cust_district ADD COLUMN display int(1) NOT NULL DEFAULT 1 COMMENT '0：不顯示  1：顯示' AFTER city;
ALTER TABLE sal_cust_district ADD COLUMN z_index int(11) NOT NULL DEFAULT 0 AFTER city;
