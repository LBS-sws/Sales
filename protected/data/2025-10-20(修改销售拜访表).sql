
-- ----------------------------
-- Table structure for sal_visit
-- ----------------------------
ALTER TABLE sal_visit ADD COLUMN total_amt double(11,2) NOT NULL DEFAULT 0.00 COMMENT '总金额' AFTER district;
ALTER TABLE sal_visit ADD COLUMN busine_id varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '服务项目' AFTER district;
ALTER TABLE sal_visit ADD COLUMN busine_id_text varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '服务项目' AFTER district;

update sal_visit set
busine_id='A,B,C,D,H,E,F,G',
busine_id_text='清洁、租赁机器、灭虫、飘盈香、蔚诺空气业务、甲醛、纸品、一次性售卖'
where busine_id is null;


-- ----------------------------
-- Table structure for sal_cust_type
-- ----------------------------
ALTER TABLE sal_cust_type ADD COLUMN rpt_u int(11) NULL DEFAULT NULL COMMENT 'u系統對應id' AFTER rpt_type;
ALTER TABLE sal_cust_type ADD COLUMN z_index int(11) not NULL DEFAULT 1 COMMENT '层级的数值越高，显示越靠前' AFTER rpt_type;
ALTER TABLE sal_cust_type ADD COLUMN z_display int(1) not NULL DEFAULT 1 COMMENT '1：显示 0：隐藏' AFTER rpt_type;

-- ----------------------------
-- Table structure for sal_cust_district
-- ----------------------------
ALTER TABLE sal_cust_district ADD COLUMN nal_id bigint(20) NULL DEFAULT NULL COMMENT '行政区域對應id' AFTER z_index;
ALTER TABLE sal_cust_district ADD COLUMN nal_tree_names varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地区名称集合' AFTER nal_id;
