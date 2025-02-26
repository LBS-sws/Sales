-- ----------------------------
-- Table structure for sal_visit
-- ----------------------------
ALTER TABLE sal_visit ADD COLUMN shift_user varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '轉移後的賬號' AFTER shift;
ALTER TABLE sal_visit ADD COLUMN shift_bool int(1) NOT NULL DEFAULT 1 COMMENT '員工離職後是否在轉移列表顯示 1:是 0：否' AFTER shift;
