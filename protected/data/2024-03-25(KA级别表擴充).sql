ALTER TABLE sal_ka_level ADD COLUMN `ka_type`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NKA' COMMENT 'ka类别：NKA，RKA' AFTER pro_name;
