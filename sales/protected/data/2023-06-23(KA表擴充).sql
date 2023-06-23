-- ----------------------------
-- Table structure for sal_ka_bot
-- ----------------------------
ALTER TABLE sal_ka_bot ADD COLUMN follow_date date NULL DEFAULT NULL COMMENT '跟進日期' AFTER city;
-- ----------------------------
-- Table structure for sal_ka_bot_history
-- ----------------------------
ALTER TABLE sal_ka_bot_history ADD COLUMN sign_odds int(10) NULL DEFAULT NULL COMMENT '簽約概率' AFTER update_html;
ALTER TABLE sal_ka_bot_history ADD COLUMN sum_amt decimal(13,2) NULL DEFAULT NULL COMMENT 'ka總金額' AFTER update_html;
ALTER TABLE sal_ka_bot_history ADD COLUMN espe_type int(1) NULL DEFAULT NULL COMMENT '1：特別標註的修改' AFTER update_html;
ALTER TABLE sal_ka_bot_history ADD COLUMN update_json text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '修改後的json數據' AFTER update_html;
