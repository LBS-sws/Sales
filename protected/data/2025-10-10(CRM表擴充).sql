
ALTER TABLE sal_contract ADD COLUMN con_v_type int(10) NULL DEFAULT NULL COMMENT '合同类型' AFTER surplus_amt;
ALTER TABLE sal_contpro ADD COLUMN con_v_type int(10) NULL DEFAULT NULL COMMENT '合同类型' AFTER surplus_amt;
ALTER TABLE sal_contract ADD COLUMN is_seal char(1) NULL DEFAULT 'Y' COMMENT '是否印章' AFTER sign_date;
ALTER TABLE sal_contpro ADD COLUMN is_seal char(1) NULL DEFAULT 'Y' COMMENT '是否印章' AFTER sign_date;

ALTER TABLE sal_contract_virtual ADD COLUMN con_v_type int(10) NULL DEFAULT NULL COMMENT '合同类型' AFTER service_type;
ALTER TABLE sal_contpro_virtual ADD COLUMN con_v_type int(10) NULL DEFAULT NULL COMMENT '合同类型' AFTER service_type;
ALTER TABLE sal_contract_virtual ADD COLUMN is_seal char(1) NULL DEFAULT 'Y' COMMENT '是否印章' AFTER receivable_day;
ALTER TABLE sal_contpro_virtual ADD COLUMN is_seal char(1) NULL DEFAULT 'Y' COMMENT '是否印章' AFTER receivable_day;
ALTER TABLE sal_contract_virtual ADD COLUMN seal_type_id int(10) NULL DEFAULT NULL COMMENT '印章id' AFTER receivable_day;
ALTER TABLE sal_contpro_virtual ADD COLUMN seal_type_id int(10) NULL DEFAULT NULL COMMENT '印章id' AFTER receivable_day;
ALTER TABLE sal_contract_virtual ADD COLUMN prioritize_seal char(1) NULL DEFAULT 'Y' COMMENT '是否客户先用印' AFTER receivable_day;
ALTER TABLE sal_contpro_virtual ADD COLUMN prioritize_seal char(1) NULL DEFAULT 'Y' COMMENT '是否客户先用印' AFTER receivable_day;


ALTER TABLE sal_contract ADD COLUMN other_sales_id int(10) NULL DEFAULT NULL COMMENT '跨区销售id' AFTER sales_id;
ALTER TABLE sal_contpro ADD COLUMN other_sales_id int(10) NULL DEFAULT NULL COMMENT '跨区销售id' AFTER sales_id;
ALTER TABLE sal_contract ADD COLUMN other_yewudalei int(10) NULL DEFAULT NULL COMMENT '跨区业务大类' AFTER yewudalei;
ALTER TABLE sal_contpro ADD COLUMN other_yewudalei int(10) NULL DEFAULT NULL COMMENT '跨区业务大类' AFTER yewudalei;
ALTER TABLE sal_contract_virtual ADD COLUMN other_yewudalei int(10) NULL DEFAULT NULL COMMENT '跨区业务大类' AFTER yewudalei;
ALTER TABLE sal_contpro_virtual ADD COLUMN other_yewudalei int(10) NULL DEFAULT NULL COMMENT '跨区业务大类' AFTER yewudalei;

ALTER TABLE sal_contract ADD COLUMN profit_int int(10) NULL DEFAULT NULL COMMENT '毛利区间' AFTER receivable_day;
ALTER TABLE sal_contpro ADD COLUMN profit_int int(10) NULL DEFAULT NULL COMMENT '毛利区间' AFTER receivable_day;
ALTER TABLE sal_contract_virtual ADD COLUMN profit_int int(10) NULL DEFAULT NULL COMMENT '毛利区间' AFTER receivable_day;
ALTER TABLE sal_contpro_virtual ADD COLUMN profit_int int(10) NULL DEFAULT NULL COMMENT '毛利区间' AFTER receivable_day;

ALTER TABLE sal_clue ADD COLUMN box_type int(10) NULL DEFAULT 0 COMMENT '0:自己新增 1：线索池新增' AFTER yewudalei;
ALTER TABLE sal_clue_flow ADD COLUMN survey_bool int(10) NULL DEFAULT NULL COMMENT '0:否 1：是' AFTER rpt_bool;
ALTER TABLE sal_clue_flow ADD COLUMN no_intention_id int(10) NULL DEFAULT NULL COMMENT '无意向原因id' AFTER rpt_bool;


ALTER TABLE sal_contract_vir_staff ADD COLUMN u_yewudalei varchar(10) NULL DEFAULT NULL COMMENT '业务大类派单系统标识' AFTER employee_id;


ALTER TABLE sal_clue ADD COLUMN file_num int(10) NULL DEFAULT 0 COMMENT '附件数量' AFTER report_id;
ALTER TABLE sal_contract ADD COLUMN is_renewal char(1) NULL DEFAULT NULL COMMENT '是否自动续约' AFTER receivable_day;
ALTER TABLE sal_contpro ADD COLUMN is_renewal char(1) NULL DEFAULT NULL COMMENT '是否自动续约' AFTER receivable_day;
ALTER TABLE sal_contract_virtual ADD COLUMN is_renewal char(1) NULL DEFAULT NULL COMMENT '是否自动续约' AFTER receivable_day;
ALTER TABLE sal_contpro_virtual ADD COLUMN is_renewal char(1) NULL DEFAULT NULL COMMENT '是否自动续约' AFTER receivable_day;
ALTER TABLE sal_contract_virtual ADD COLUMN call_fre_amt double(11,2) NULL DEFAULT 0 COMMENT '呼叫式单次金额' AFTER receivable_day;
ALTER TABLE sal_contpro_virtual ADD COLUMN call_fre_amt double(11,2) NULL DEFAULT 0 COMMENT '呼叫式单次金额' AFTER receivable_day;
ALTER TABLE sal_contpro_virtual ADD COLUMN call_id int(11) NULL DEFAULT NULL COMMENT '呼叫式id' AFTER vir_batch_id;
ALTER TABLE sal_contract_file ADD COLUMN sent_bool int(11) NULL DEFAULT 0 COMMENT '0:未发送 1：已发送给派单' AFTER file_type;
ALTER TABLE sal_contpro_file ADD COLUMN sent_bool int(11) NULL DEFAULT 0 COMMENT '0:未发送 1：已发送给派单' AFTER file_type;
ALTER TABLE sal_virtual_batch_file ADD COLUMN sent_bool int(11) NULL DEFAULT 0 COMMENT '0:未发送 1：已发送给派单' AFTER file_type;
ALTER TABLE sal_contract_vir_week ADD COLUMN year_cycle int(4) NULL DEFAULT null COMMENT '年份' AFTER vir_id;
update sal_contract_virtual set call_fre_amt=service_fre_amt,service_fre_sum=0 where service_fre_type=3;
update sal_contpro_virtual set call_fre_amt=service_fre_amt,service_fre_sum=0 where service_fre_type=3;
update sal_contract_virtual set service_sum=service_fre_sum where report_id is null;
update sal_contpro_virtual set service_sum=service_fre_sum where report_id is null;
INSERT INTO sal_cont_str (id,name,str_type,z_display,lcu) VALUES (100, 'CRM自动暂停转终止', 2,0,'shenchao');