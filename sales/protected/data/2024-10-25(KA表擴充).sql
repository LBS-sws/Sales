
ALTER TABLE sal_ka_bot ADD COLUMN ava_sum int(6) NULL DEFAULT NULL COMMENT '门店总数量' AFTER city;
ALTER TABLE sal_ra_bot ADD COLUMN ava_sum int(6) NULL DEFAULT NULL COMMENT '门店总数量' AFTER city;
ALTER TABLE sal_ca_bot ADD COLUMN ava_sum int(6) NULL DEFAULT NULL COMMENT '门店总数量' AFTER city;

ALTER TABLE sal_ka_bot ADD COLUMN sign_end_date date NULL DEFAULT NULL COMMENT '合同结束日期' AFTER sign_date;
ALTER TABLE sal_ra_bot ADD COLUMN sign_end_date date NULL DEFAULT NULL COMMENT '合同结束日期' AFTER sign_date;
ALTER TABLE sal_ca_bot ADD COLUMN sign_end_date date NULL DEFAULT NULL COMMENT '合同结束日期' AFTER sign_date;

UPDATE sal_ka_bot a SET a.ava_sum = (SELECT SUM(IFNULL(b.ava_num, 0)) FROM sal_ka_bot_ava b WHERE b.bot_id = a.id GROUP BY b.bot_id ) WHERE a.ava_sum IS NULL;
UPDATE sal_ra_bot a SET a.ava_sum = (SELECT SUM(IFNULL(b.ava_num, 0)) FROM sal_ra_bot_ava b WHERE b.bot_id = a.id GROUP BY b.bot_id ) WHERE a.ava_sum IS NULL;
UPDATE sal_ca_bot a SET a.ava_sum = (SELECT SUM(IFNULL(b.ava_num, 0)) FROM sal_ca_bot_ava b WHERE b.bot_id = a.id GROUP BY b.bot_id ) WHERE a.ava_sum IS NULL;

UPDATE sal_ka_bot SET ava_sum=NULL WHERE ava_sum=0;
UPDATE sal_ra_bot SET ava_sum=NULL WHERE ava_sum=0;
UPDATE sal_ca_bot SET ava_sum=NULL WHERE ava_sum=0;

UPDATE sal_ka_bot SET sign_end_date=DATE_ADD(sign_date,INTERVAL sign_month YEAR) WHERE sign_date is NOT NULL AND sign_month is NOT NULL;
UPDATE sal_ra_bot SET sign_end_date=DATE_ADD(sign_date,INTERVAL sign_month YEAR) WHERE sign_date is NOT NULL AND sign_month is NOT NULL;
UPDATE sal_ca_bot SET sign_end_date=DATE_ADD(sign_date,INTERVAL sign_month YEAR) WHERE sign_date is NOT NULL AND sign_month is NOT NULL;