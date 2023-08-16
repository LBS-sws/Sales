
-- ----------------------------
-- Table structure for sal_visit
-- ----------------------------
ALTER TABLE sal_visit ADD COLUMN doc_count int(3) NOT NULL DEFAULT 0 COMMENT '附件的總數量' AFTER status_dt;

UPDATE sal_visit SET doc_count = docman.countdoc ('visit', id) WHERE id > 0