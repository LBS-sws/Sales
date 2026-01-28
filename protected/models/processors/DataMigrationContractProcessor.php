<?php

//  导入依赖的类
Yii::import('application.models.DataMigrationHelper');

/**
 * 合约迁移数据处理器
 * 负责主合约数据的预处理、插入和更新
 * 
 * @see DataMigrationForm 主控制器
 * @see DataMigrationHelper 辅助工具类
 */
class DataMigrationContractProcessor
{
    /**
     * 合约数据预处理（中文字段名 → 英文字段名 + 数据转换）
     * 
     * @param array $data 原始数据（中文字段名）
     * @param CDbConnection $connection 数据库连接
     * @param int $reportId 导入任务ID
     * @return array 处理后的数据（英文字段名）
     * @throws Exception 数据验证失败时抛出异常
     */
    public static function preprocess($data, $connection, $reportId = null)
    {
        $processed = array();
        
        // 1. 基本字段映射
        $fieldMap = array(
            '主合同编号' => 'cont_code',
            '客户编号' => 'clue_code',
            '主合同状态' => 'cont_status',
            '服务项目' => 'busine_name',
            '签约时间' => 'sign_date',
            '合约开始时间' => 'cont_start_dt',
            '合约结束时间' => 'cont_end_dt',
            '业务大类' => 'yewudalei',
            '主体公司' => 'lbs_main',
            '门店总数量' => 'store_sum',
            '合约总金额' => 'total_amt',
            '服务总次数' => 'total_sum',
            '结算方式' => 'settle_type',
            '付款方式' => 'pay_type',
            '押金备注' => 'deposit_rmk',
            '已收押金' => 'deposit_amt',
            '所需押金' => 'deposit_need',
            '收费方式' => 'fee_type',
            '预付月数' => 'pay_month',
            '起始月' => 'pay_start',
            '是否对账' => 'bill_bool',
            '账单日' => 'bill_day',
            '付款周期' => 'pay_week',
            '服务时长(分钟)' => 'service_timer',
            '是否优先安排服务' => 'prioritize_service',
            '应收期限' => 'receivable_day',
            '剩余次数' => 'surplus_num',
            '剩余金额' => 'surplus_amt',
            '终止或暂停日期' => 'stop_date',
            '派单系统合约id' => 'u_id',
            '主体公司办公室代码' => 'lbs_main_office',  //  新增
        );
        
        foreach ($fieldMap as $chineseKey => $englishKey) {
            if (isset($data[$chineseKey])) {
                $processed[$englishKey] = $data[$chineseKey];
            }
        }
        
        // 2. 业务大类转换
        if (isset($processed['yewudalei']) && !empty($processed['yewudalei'])) {
            $yewudalei = $processed['yewudalei'];
            if (!is_numeric($yewudalei)) {
                $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($yewudalei, $connection);
                if ($yewudaleiId) {
                    $processed['yewudalei'] = $yewudaleiId;
                } else {
                    $processed['yewudalei'] = null;
                }
            }
        }
        
        // 3. 主体公司转换（从CRM的sal_main_lbs.show_city字段匹配）
        if (isset($processed['lbs_main']) && !empty($processed['lbs_main'])) {
            $lbsMainValue = $processed['lbs_main'];
            if (!is_numeric($lbsMainValue)) {
                //  优先：使用新字段 lbs_main_office（主体公司办公室代码）
                if (isset($processed['lbs_main_office']) && !empty($processed['lbs_main_office'])) {
                    $paidanEntityInfo = array(
                        'office_code' => $processed['lbs_main_office'],  //  新字段
                        'entity_code' => $processed['lbs_main'],
                    );
                    $lbsMainId = DataMigrationHelper::getLbsMainFromPaidanData($paidanEntityInfo, $connection);
                    
                    if (empty($lbsMainId)) {
                        throw new Exception(
                            "找不到对应的主体公司！办公室代码: {$processed['lbs_main_office']}, 主体编码: {$processed['lbs_main']}。" .
                            "请在 sal_main_lbs 表的 show_city 字段中添加该办公室代码，或在 mh_code 字段中添加主体编码。"
                        );
                    }
                    $processed['lbs_main'] = $lbsMainId;
                } else {
                    // 后备：兼容旧数据，直接按名称/编码查找
                    $lbsMainId = DataMigrationHelper::getLbsMainIdByName($lbsMainValue, $connection);
                    if (empty($lbsMainId)) {
                        throw new Exception(
                            "找不到主体公司: {$lbsMainValue}。" .
                            "请确保在 sal_main_lbs 表中已配置该主体公司。"
                        );
                    }
                    $processed['lbs_main'] = $lbsMainId;
                }
            }
        }
        
        // 4. 员工编号转ID
        if (isset($data['销售员工编号'])) {
            $empCode = $data['销售员工编号'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['sales_id'] = $empId;
            } else {
                //  销售员工不存在时跳过，不抛异常
                Yii::log("销售员工编号不存在：{$empCode}，已跳过此字段", 'warning', 'DataMigration');
                // 注意：不设置 sales_id，后续会使用客户的 rec_employee_id 作为默认值
            }
        }
        
        // 5. 处理服务项目（可能是逗号分隔的多个）
        if (isset($processed['busine_name'])) {
            //  统一替换中文逗号为英文逗号
            $processed['busine_name'] = str_replace('，', ',', $processed['busine_name']);
            
            $busineNames = explode(',', $processed['busine_name']);
            $ids = array();
            $names = array();
            foreach ($busineNames as $name) {
                $name = trim($name);
                if (!empty($name)) {
                    //  统一转换英文括号为中文括号（兼容处理）
                    $name = str_replace('(', '（', $name);
                    $name = str_replace(')', '）', $name);
                    
                    $row = DataMigrationHelper::getServiceTypeByName($name, $connection);
                    if ($row) {
                        $ids[] = $row['id_char'];
                        $names[] = $name;
                    } else {
                        throw new Exception('服务项目不存在：' . $name);
                    }
                }
            }
            $processed['busine_id'] = implode(',', $ids);
            $processed['busine_id_text'] = implode('、', $names);
        }
        
        // 6. 状态转换
        if (isset($processed['cont_status'])) {
            $statusMap = array('生效中' => 30, '暂停' => 40, '终止' => 50);
            if (isset($statusMap[$processed['cont_status']])) {
                $processed['cont_status'] = $statusMap[$processed['cont_status']];
            }
        }
        
        // 7. 日期处理（ 容错处理：日期为空时允许为null）
        $dateFields = array('sign_date', 'cont_start_dt', 'cont_end_dt', 'stop_date');
        foreach ($dateFields as $field) {
            if (isset($processed[$field]) && $processed[$field] !== '') {
                $timestamp = strtotime($processed[$field]);
                if ($timestamp) {
                    $processed[$field] = date('Y-m-d', $timestamp);
                } else {
                    // 日期格式错误，记录警告并置空
                    Yii::log("日期字段 {$field} 格式错误：{$processed[$field]}，已置空", 'warning', 'DataMigration');
                    $processed[$field] = null;
                }
            } else {
                $processed[$field] = null;
            }
        }
        
        // 8. 整数字段空值处理（第一次处理，在类型转换之前）
        $intFields = array('surplus_num', 'total_sum', 'pay_month', 'pay_start', 'service_timer', 'cont_month_len', 'store_sum');
        foreach ($intFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        // 9. 金额字段处理
        $moneyFields = array('total_amt', 'deposit_amt', 'deposit_need', 'surplus_amt');
        foreach ($moneyFields as $field) {
            if (isset($processed[$field])) {
                $processed[$field] = str_replace(',', '', $processed[$field]);
                if ($processed[$field] === '') {
                    $processed[$field] = 0;
                }
            }
        }
        
        // 10. 布尔值处理
        $boolFields = array('bill_bool', 'prioritize_service');
        foreach ($boolFields as $field) {
            if (isset($processed[$field])) {
                if ($processed[$field] === '是' || $processed[$field] === 'Y' || $processed[$field] === '1') {
                    $processed[$field] = 'Y';
                } else {
                    $processed[$field] = 'N';
                }
            }
        }
        
        // 11. 付款方式转换
        if (isset($processed['pay_type']) && !empty($processed['pay_type'])) {
            $payType = $processed['pay_type'];
            if (!is_numeric($payType)) {
                $list = CGetName::getPayTypeLists();
                $key = array_search($payType, $list);
                if ($key !== false) {
                    $processed['pay_type'] = $key;
                } else {
                    Yii::log("付款方式不存在: {$payType}", 'warning', 'DataMigration');
                    $processed['pay_type'] = null;
                }
            }
        }
        
        // 12. 付款周期转换
        if (isset($processed['pay_week']) && !empty($processed['pay_week'])) {
            $payWeek = $processed['pay_week'];
            if (!is_numeric($payWeek)) {
                $list = CGetName::getPayWeekLists();
                $key = array_search($payWeek, $list);
                if ($key !== false) {
                    $processed['pay_week'] = $key;
                } else {
                    Yii::log("付款周期不存在: {$payWeek}", 'warning', 'DataMigration');
                    $processed['pay_week'] = null;
                }
            }
        }
        
        // 13. 收费方式转换
        if (isset($processed['fee_type']) && !empty($processed['fee_type'])) {
            $feeType = $processed['fee_type'];
            if (!is_numeric($feeType)) {
                $list = CGetName::getFeeTypeList();
                $key = array_search($feeType, $list);
                if ($key !== false) {
                    $processed['fee_type'] = $key;
                } else {
                    Yii::log("收费方式不存在: {$feeType}", 'warning', 'DataMigration');
                    $processed['fee_type'] = null;
                }
            }
        }
        
        // 14. 结算方式转换
        if (isset($processed['settle_type']) && !empty($processed['settle_type'])) {
            $settleType = $processed['settle_type'];
            if (!is_numeric($settleType)) {
                $list = CGetName::getSettleTypeList();
                $key = array_search($settleType, $list);
                if ($key !== false) {
                    $processed['settle_type'] = $key;
                } else {
                    Yii::log("结算方式不存在: {$settleType}", 'warning', 'DataMigration');
                    $processed['settle_type'] = null;
                }
            }
        }
        
        // 15. 账单日转换
        if (isset($processed['bill_day']) && !empty($processed['bill_day'])) {
            $billDay = $processed['bill_day'];
            if (!is_numeric($billDay)) {
                $list = CGetName::getBillDayList();
                $key = array_search($billDay, $list);
                if ($key !== false) {
                    $processed['bill_day'] = $key;
                } else {
                    Yii::log("账单日不存在: {$billDay}", 'warning', 'DataMigration');
                    $processed['bill_day'] = null;
                }
            }
        }
        
        // 16. 应收期限转换
        if (isset($processed['receivable_day']) && !empty($processed['receivable_day'])) {
            $receivableDay = $processed['receivable_day'];
            if (!is_numeric($receivableDay)) {
                $list = CGetName::getReceivableDayList();
                $key = array_search($receivableDay, $list);
                if ($key !== false) {
                    $processed['receivable_day'] = $key;
                } else {
                    Yii::log("应收期限不存在: {$receivableDay}", 'warning', 'DataMigration');
                    $processed['receivable_day'] = null;
                }
            }
        }
        
        // 17. 设置默认值
        if (!isset($processed['total_sum'])) $processed['total_sum'] = 0;
        if (!isset($processed['stop_date'])) $processed['stop_date'] = null;
        if (!isset($processed['surplus_num'])) $processed['surplus_num'] = null;
        if (!isset($processed['surplus_amt'])) $processed['surplus_amt'] = null;
        if (!isset($processed['prioritize_service'])) $processed['prioritize_service'] = 'N';
        if (!isset($processed['service_timer'])) $processed['service_timer'] = null;
        if (!isset($processed['pay_type'])) $processed['pay_type'] = null;
        if (!isset($processed['pay_week'])) $processed['pay_week'] = null;
        if (!isset($processed['pay_month'])) $processed['pay_month'] = null;
        if (!isset($processed['pay_start'])) $processed['pay_start'] = null;
        if (!isset($processed['deposit_need'])) $processed['deposit_need'] = null;
        if (!isset($processed['deposit_amt'])) $processed['deposit_amt'] = null;
        if (!isset($processed['deposit_rmk'])) $processed['deposit_rmk'] = null;
        if (!isset($processed['fee_type'])) $processed['fee_type'] = null;
        if (!isset($processed['settle_type'])) $processed['settle_type'] = null;
        if (!isset($processed['bill_day'])) $processed['bill_day'] = null;
        if (!isset($processed['bill_bool'])) $processed['bill_bool'] = 'N';
        if (!isset($processed['receivable_day'])) $processed['receivable_day'] = null;
        
        // 18. 可选整数字段空值处理（确保空字符串转换为 null，避免数据库插入错误）
        $optionalIntegerFields = array(
            'store_sum', 'total_sum', 'surplus_num', 'pay_month', 'pay_start', 
            'bill_day', 'service_timer', 'receivable_day', 'yewudalei', 'lbs_main',
            'pay_type', 'pay_week', 'fee_type', 'settle_type', 'busine_id'
        );
        foreach ($optionalIntegerFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        return $processed;
    }
    
    /**
     * 插入合约数据（参考 ImportContForm::saveOneData）
     * 
     * @param array $data 预处理后的数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @param int $reportId 导入任务ID
     * @return int 合约ID (cont_id)
     * @throws Exception 插入失败时抛出异常
     */
    public static function insert($data, $connection, $username, $reportId)
    {
        // 1. 查找客户（优先使用缓存）
        $clueRow = DataMigrationForm::getCachedClue($data['clue_code'], $connection);
        
        if (!$clueRow) {
            throw new Exception('主合约导入失败：找不到对应的客户（clue_code=' . $data['clue_code'] . '）');
        }
        
        // 2. 计算合约月数
        if (!empty($data['cont_start_dt']) && !empty($data['cont_end_dt'])) {
            $data['cont_month_len'] = DataMigrationHelper::computeMonthLen($data['cont_start_dt'], $data['cont_end_dt']);
        } else {
            $data['cont_month_len'] = 0;
        }
        
        // 3. 先插入 sal_clue_service 表（参考 ImportContForm::saveOneData）
        // 获取拜访类型和对象（使用缓存）
        $visitType = DataMigrationForm::getCachedVisitTypeId();
        $visitObj = DataMigrationForm::getCachedVisitObjId();
        $visitObjText = DataMigrationForm::getCachedVisitObjText();
        
        $clueServiceData = array(
            'clue_id' => $clueRow['id'],
            'clue_type' => $clueRow['clue_type'],
            'visit_type' => $visitType,
            'visit_obj' => $visitObj,
            'visit_obj_text' => $visitObjText,
            'create_staff' => isset($data['sales_id']) ? $data['sales_id'] : $clueRow['rec_employee_id'],
            'busine_id' => isset($data['busine_id']) ? $data['busine_id'] : null,
            'busine_id_text' => isset($data['busine_id_text']) ? $data['busine_id_text'] : null,
            'sign_odds' => 100,
            'lbs_main' => isset($data['lbs_main']) ? $data['lbs_main'] : null,
            'predict_date' => isset($data['sign_date']) ? $data['sign_date'] : null,
            'predict_amt' => isset($data['total_amt']) ? $data['total_amt'] : 0,
            'total_amt' => isset($data['total_amt']) ? $data['total_amt'] : 0,
            'total_num' => 1,
            'service_status' => isset($data['cont_status']) ? $data['cont_status'] : 30,
            'lcu' => $username,
            'report_id' => $reportId,
        );
        
        $connection->createCommand()->insert('sal_clue_service', $clueServiceData);
        $clueServiceId = $connection->getLastInsertID();
        
        // 4. 插入合约主表
        $saveKey = array(
            'clue_type', 'city', 'cont_code', 'cont_status', 'busine_id', 'busine_id_text',
            'sign_date', 'cont_start_dt', 'cont_end_dt', 'cont_month_len', 'yewudalei', 'lbs_main',
            'store_sum', 'total_amt', 'total_sum', 'settle_type', 'pay_type', 'deposit_rmk',
            'deposit_amt', 'deposit_need', 'fee_type', 'pay_month', 'pay_start', 'bill_bool',
            'bill_day', 'pay_week', 'service_timer', 'prioritize_service', 'receivable_day',
            'surplus_num', 'surplus_amt', 'stop_date', 'u_id'
        );
        
        $saveList = array();
        foreach ($saveKey as $key) {
            if (isset($data[$key])) {
                $saveList[$key] = $data[$key];
            }
        }
        
        $saveList['clue_id'] = $clueRow['id'];
        $saveList['clue_type'] = $clueRow['clue_type'];
        $saveList['clue_service_id'] = $clueServiceId;
        $saveList['city'] = $clueRow['city'];
        $saveList['sales_id'] = isset($data['sales_id']) ? $data['sales_id'] : $clueRow['rec_employee_id'];
        $saveList['predict_amt'] = isset($data['total_amt']) ? $data['total_amt'] : 0;
        $saveList['cont_type'] = 1;  // 合约类型：1=主合约
        $saveList['sign_type'] = 1;  // 签约类型
        $saveList['area_bool'] = 'N';
        $saveList['group_bool'] = 'N';
        $saveList['report_id'] = $reportId;
        $saveList['lcu'] = $username;
        
        $connection->createCommand()->insert('sal_contract', $saveList);
        $cont_id = $connection->getLastInsertID();
        
        // 5. 插入合约项目变更记录（sal_contpro）
        $proType = DataMigrationHelper::proTypeByStatus(isset($data['cont_status']) ? $data['cont_status'] : 30);
        $contProData = array_merge($saveList, array(
            'cont_id' => $cont_id,
            'pro_code' => 'PDL-' . $data['cont_code'],
            'pro_type' => $proType,
            'pro_date' => isset($data['sign_date']) ? $data['sign_date'] : null,
            'pro_remark' => "派单数据导入自动生成\n导入id：{$reportId}",
            'pro_status' => 30,
            'pro_change' => (isset($data['cont_status']) && $data['cont_status'] == 30) 
                ? (isset($data['total_amt']) ? $data['total_amt'] : 0) 
                : (isset($data['surplus_amt']) ? $data['surplus_amt'] : 0),
        ));
        $connection->createCommand()->insert('sal_contpro', $contProData);
        
        // 6. 插入合约历史记录
        $connection->createCommand()->insert('sal_contract_history', array(
            'table_id' => $cont_id,
            'table_type' => 5,  // 5=主合约
            'history_type' => 1,
            'history_html' => "<span>派单数据导入，导入id：{$reportId}</span>",
            'lcu' => $username,
        ));
        
        // 7. 更新客户状态
        Yii::import('application.models.ClueVirProModel');
        $newClueStatus = ClueVirProModel::getClientStatusByClueID($clueRow['id']);
        $connection->createCommand()->update('sal_clue', 
            array('clue_status' => $newClueStatus),
            'id=:id',
            array(':id' => $clueRow['id'])
        );
        
        Yii::log('主合约数据导入成功：cont_id=' . $cont_id . ', u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null'), 'info', 'DataMigration');
        
        return $cont_id;
    }
    
    /**
     * 更新合约数据
     * 
     * @param array $data 预处理后的数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @throws Exception 更新失败时抛出异常
     */
    public static function update($data, $connection, $username, $reportId)
    {
        // TODO: 实现合约更新逻辑
        throw new Exception('合约更新功能待实现');
    }
}

