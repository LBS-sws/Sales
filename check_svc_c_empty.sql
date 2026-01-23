-- ================================================================
-- 查询 detail_json 中 svc_C 为空的虚拟合约记录
-- 仅查询,不执行更新操作
-- ================================================================

-- 1. 统计总数
SELECT 
    '符合条件的记录总数' AS 说明,
    COUNT(*) AS 数量
FROM `sales`.`sal_contract_virtual`
WHERE `detail_json` LIKE '%{\"svc_C\":\"\",%'
  AND `service_fre_type` = '00000000001'
  AND `invoice_amount` IS NOT NULL;

-- 2. 查看详细数据 (前50条)
SELECT 
    id AS ID,
    vir_code AS 合约编号,
    cont_id AS 合同ID,
    invoice_amount AS 发票金额,
    month_amt AS 月金额,
    year_amt AS 年金额,
    service_sum AS 服务次数,
    JSON_UNQUOTE(JSON_EXTRACT(detail_json, '$.svc_C')) AS 当前svc_C值,
    JSON_UNQUOTE(JSON_EXTRACT(detail_json, '$.svc_C7')) AS svc_C7值,
    CASE 
        WHEN ABS(invoice_amount - year_amt) < 0.01 THEN 'invoice=year'
        WHEN ABS(invoice_amount - month_amt) < 0.01 THEN 'invoice=month ⚠️'
        ELSE 'other ⚠️'
    END AS invoice类型,
    CASE 
        WHEN ABS(year_amt - (month_amt * 12)) < 0.5 THEN '✓'
        ELSE '✗ 异常'
    END AS year金额检查,
    lcu AS 创建人,
    DATE_FORMAT(lcd, '%Y-%m-%d') AS 创建时间,
    DATE_FORMAT(lud, '%Y-%m-%d %H:%i') AS 最后更新
FROM `sales`.`sal_contract_virtual`
WHERE `detail_json` LIKE '%{\"svc_C\":\"\",%'
  AND `service_fre_type` = '00000000001'
  AND `invoice_amount` IS NOT NULL
ORDER BY id DESC
LIMIT 50;

-- 3. 按 invoice 类型统计
SELECT 
    CASE 
        WHEN ABS(invoice_amount - year_amt) < 0.01 THEN 'invoice=year_amt'
        WHEN ABS(invoice_amount - month_amt) < 0.01 THEN 'invoice=month_amt'
        ELSE 'invoice=其他'
    END AS invoice类型,
    COUNT(*) AS 记录数,
    MIN(invoice_amount) AS 最小金额,
    MAX(invoice_amount) AS 最大金额,
    AVG(invoice_amount) AS 平均金额
FROM `sales`.`sal_contract_virtual`
WHERE `detail_json` LIKE '%{\"svc_C\":\"\",%'
  AND `service_fre_type` = '00000000001'
  AND `invoice_amount` IS NOT NULL
GROUP BY CASE 
    WHEN ABS(invoice_amount - year_amt) < 0.01 THEN 'invoice=year_amt'
    WHEN ABS(invoice_amount - month_amt) < 0.01 THEN 'invoice=month_amt'
    ELSE 'invoice=其他'
END;

-- 4. 查看数据异常的记录
SELECT 
    id AS ID,
    vir_code AS 合约编号,
    invoice_amount AS 发票金额,
    month_amt AS 月金额,
    year_amt AS 年金额,
    month_amt * 12 AS 月金额x12,
    year_amt - (month_amt * 12) AS 差异,
    '数据异常' AS 问题
FROM `sales`.`sal_contract_virtual`
WHERE `detail_json` LIKE '%{\"svc_C\":\"\",%'
  AND `service_fre_type` = '00000000001'
  AND `invoice_amount` IS NOT NULL
  AND (
      ABS(invoice_amount - month_amt) < 0.01  -- invoice = month (可能错误)
      OR ABS(year_amt - (month_amt * 12)) > 0.5  -- year ≠ month × 12
  )
ORDER BY id DESC
LIMIT 20;

-- ================================================================
-- 更新 SQL (请在确认数据无误后执行)
-- ================================================================
/*
UPDATE `sales`.`sal_contract_virtual`
SET `detail_json` = JSON_SET(`detail_json`, '$.svc_C', CAST(`invoice_amount` AS CHAR)),
    `lud` = NOW()
WHERE `detail_json` LIKE '%{\"svc_C\":\"\",%'
  AND `service_fre_type` = '00000000001'
  AND `invoice_amount` IS NOT NULL;
*/

-- ================================================================
-- 验证更新结果 (更新后执行)
-- ================================================================
/*
SELECT 
    id,
    vir_code,
    invoice_amount,
    JSON_UNQUOTE(JSON_EXTRACT(detail_json, '$.svc_C')) AS svc_C_value,
    CASE 
        WHEN JSON_UNQUOTE(JSON_EXTRACT(detail_json, '$.svc_C')) = CAST(invoice_amount AS CHAR)
        THEN '✓ 一致'
        ELSE '✗ 不一致'
    END AS 验证结果
FROM `sales`.`sal_contract_virtual`
WHERE `service_fre_type` = '00000000001'
  AND `invoice_amount` IS NOT NULL
LIMIT 20;
*/


