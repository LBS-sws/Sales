-- ----------------------------
-- Table structure for sal_visit
-- ----------------------------
ALTER TABLE sal_visit ADD COLUMN visit_obj_name varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '目的（翻译）' AFTER visit_obj;
ALTER TABLE sal_visit ADD COLUMN visit_info_text varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '拜访价格（翻译）' AFTER doc_count;
ALTER TABLE sal_visit ADD COLUMN sign_odds int(3) not NULL DEFAULT 0 COMMENT '签约概率' AFTER doc_count;
