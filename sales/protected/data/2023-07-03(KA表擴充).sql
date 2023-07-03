-- ----------------------------
-- Table structure for sal_ka_bot
-- ----------------------------
ALTER TABLE sal_ka_bot ADD COLUMN available_date date NULL DEFAULT NULL COMMENT '可成交日期' AFTER year_amt;
ALTER TABLE sal_ka_bot ADD COLUMN available_amt decimal(13,2) NULL DEFAULT NULL COMMENT '可成交金額' AFTER year_amt;