<?php

//  导入依赖的类
Yii::import('application.models.DataMigrationHelper');
Yii::import('application.models.processors.DataMigrationContractProcessor');

/**
 * 虚拟合约迁移数据处理器
 * 负责虚拟合约数据的预处理、插入和更新
 * 这是最复杂的处理器，包含服务频次、服务项目详情等复杂逻辑
 * 
 * @see DataMigrationForm 主控制器
 * @see DataMigrationHelper 辅助工具类
 * @see DataMigrationContractProcessor 合约处理器（用于创建主合约）
 */
class DataMigrationVirtualContractProcessor
{
    /**
     * 验证必需字段（参考 ImportVirForm::eveList 中 requite=>true 的字段）
     * 
     * @param array $data 原始数据
     * @throws Exception 缺少必需字段时抛出异常
     */
    protected static function validateRequiredFields($data)
    {
        $requiredFields = array(
            '虚拟合同编号' => 'vir_code',
            '服务项目' => 'busine_name',
            '门店编号' => 'store_code',
            '虚拟合同状态' => 'vir_status',
            '签约时间' => 'sign_date',
            '合约开始时间' => 'cont_start_dt',
            '合约结束时间' => 'cont_end_dt',
            '业务大类' => 'yewudalei',
            '主体公司' => 'lbs_main',
            '销售员工编号' => 'sales_code',
            '销售关联合约的id' => 'sales_u_id',
            '合约月金额' => 'month_amt',
            '合约年金额' => 'year_amt',
            '服务总次数' => 'service_sum',
            '服务频次类型' => 'service_fre_type',
        );
        
        $missingFields = array();
        foreach ($requiredFields as $chineseName => $fieldKey) {
            if (!isset($data[$chineseName]) || $data[$chineseName] === '') {
                $missingFields[] = $chineseName;
            }
        }
        
        if (!empty($missingFields)) {
            throw new Exception('缺少必需字段：' . implode('、', $missingFields));
        }
    }
    
    /**
     * 虚拟合约数据预处理（中文字段名 → 英文字段名 + 数据转换）
     * 
     * @param array $data 原始数据（中文字段名）
     * @param CDbConnection $connection 数据库连接
     * @param int $reportId 导入任务ID
     * @return array 处理后的数据（英文字段名）
     * @throws Exception 数据验证失败时抛出异常
     */
    public static function preprocess($data, $connection, $reportId = null)
    {
        // 0. 验证必需字段
        self::validateRequiredFields($data);
        
        $processed = array();
        
        // 1. 基本字段映射
        $fieldMap = array(
            '主合同编号' => 'cont_code',
            '虚拟合同编号' => 'vir_code',
            '服务项目' => 'busine_name',
            '门店编号' => 'store_code',
            '虚拟合同状态' => 'vir_status',
            '签约时间' => 'sign_date',
            '合约开始时间' => 'cont_start_dt',
            '合约结束时间' => 'cont_end_dt',
            '业务大类' => 'yewudalei',
            '主体公司' => 'lbs_main',
            '销售员工编号' => 'sales_code',
            '销售关联合约的id' => 'sales_u_id',
            '合约月金额' => 'month_amt',
            '合约年金额' => 'year_amt',
            '服务总次数' => 'service_sum',
            '服务频次类型' => 'service_fre_type',
            '服务频次(文字)' => 'u_service_title',
            '服务频次详情' => 'u_service_info',
            '服务项目详情' => 'serviceTypeInfo',
            '呼叫式单次金额' => 'call_fre_amt',
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
            '服务主体' => 'service_main',
            '首次日期' => 'first_date',
            '常规开始日期' => 'fast_date',
            '是否需安装费' => 'need_install',
            '安装金额' => 'amt_install',
            '被跨区业务员' => 'other_sales_code',
            '被跨区业务员关联合约的id' => 'other_sales_u_id',
            '被跨区业务员业务大类' => 'other_yewudalei',
            '首次技术员' => 'first_tech_code',
            '负责技术员' => 'technician_id_str',
            '外部数据来源' => 'external_source',
            '终止或暂停原因' => 'stop_set_id',
            '终止或暂停日期' => 'stop_date',
            '终止时月金额' => 'stop_month_amt',
            '终止时年金额' => 'stop_year_amt',
            '发票金额' => 'invoice_amount',
            '派单系统id' => 'u_id',
            '是否用印' => 'is_seal',                      // 新增：红框
            '是否客户先用印' => 'is_client_seal',          // 新增：红框
            '服务区域范围' => 'service_area_range',       // 新增：红框
            '非服务区域' => 'non_service_area',           // 新增：红框
            '标靶害虫' => 'target_pest',                  // 新增：红框
            '主体公司办公室代码' => 'lbs_main_office',
            '服务主体办公室代码' => 'service_main_office',
            '服务项目元数据' => 'service_item_metadata',    //  新增
        );
        
        foreach ($fieldMap as $chineseKey => $englishKey) {
            if (isset($data[$chineseKey])) {
                $processed[$englishKey] = $data[$chineseKey];
            }
        }
        
        // 2. 业务大类转换
        if (isset($processed['yewudalei']) && !empty($processed['yewudalei'])) {
            if (!is_numeric($processed['yewudalei'])) {
                $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($processed['yewudalei'], $connection);
                $processed['yewudalei'] = $yewudaleiId ?: null;
            }
        }
        
        // 3. 主体公司转换
        if (isset($processed['lbs_main']) && !empty($processed['lbs_main'])) {
            if (!is_numeric($processed['lbs_main'])) {
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
                    $lbsMainId = DataMigrationHelper::getLbsMainIdByName($processed['lbs_main'], $connection);
                    if (empty($lbsMainId)) {
                        throw new Exception("找不到主体公司: {$processed['lbs_main']}");
                    }
                    $processed['lbs_main'] = $lbsMainId;
                }
            }
        }
        
        // 4. 服务主体转换
        if (isset($processed['service_main']) && !empty($processed['service_main'])) {
            if (!is_numeric($processed['service_main'])) {
                $serviceMainId = null;
                // 优先：通过办公室代码+主体编码匹配
                if (!empty($processed['service_main_office'])) {
                    $paidanServiceEntityInfo = array(
                        'office_code' => $processed['service_main_office'],
                        'entity_code' => $processed['service_main'],
                    );
                    $serviceMainId = DataMigrationHelper::getLbsMainFromPaidanData($paidanServiceEntityInfo, $connection);
                }
                
                // 次之：如果上面没匹配到，直接按名称/编码查找
                if (empty($serviceMainId)) {
                    $serviceMainId = DataMigrationHelper::getLbsMainIdByName($processed['service_main'], $connection);
                }
                
                if (empty($serviceMainId)) {
                    Yii::log("警告：无法识别服务主体 {$processed['service_main']}，保留原值", 'warning', 'DataMigration');
                } else {
                    $processed['service_main'] = $serviceMainId;
                }
            }
        }
        
        // 5. 销售员工编号转ID
        if (isset($processed['sales_code']) && !empty($processed['sales_code'])) {
            $empId = DataMigrationHelper::getEmployeeIdByCode($processed['sales_code'], $connection);
            if ($empId) {
                $processed['sales_id'] = $empId;
            } else {
                throw new Exception('销售员工编号不存在：' . $processed['sales_code']);
            }
            unset($processed['sales_code']);
        }
        
        // 6. 被跨区业务员转换（参考 ImportVirForm::valOtherSalesUID）
        $hasOtherSales = isset($processed['other_sales_code']) && !empty($processed['other_sales_code']);
        $hasOtherSalesUId = isset($processed['other_sales_u_id']) && !empty($processed['other_sales_u_id']);
        
        // 验证：两个字段必须同时有或同时无
        if ($hasOtherSales && !$hasOtherSalesUId) {
            throw new Exception('被跨区业务员填写后，被跨区业务员关联合约的id不能为空');
        }
        if (!$hasOtherSales && $hasOtherSalesUId) {
            throw new Exception('被跨区业务员关联合约的id填写后，被跨区业务员不能为空');
        }
        
        if ($hasOtherSales) {
            $empId = DataMigrationHelper::getEmployeeIdByCode($processed['other_sales_code'], $connection);
            if ($empId) {
                $processed['other_sales_id'] = $empId;
            } else {
                throw new Exception('被跨区业务员编号不存在：' . $processed['other_sales_code']);
            }
            unset($processed['other_sales_code']);
        }
        
        // 7. 被跨区业务员业务大类转换
        if (isset($processed['other_yewudalei']) && !empty($processed['other_yewudalei'])) {
            if (!is_numeric($processed['other_yewudalei'])) {
                $otherYewudaleiId = DataMigrationHelper::getYewudaleiIdByName($processed['other_yewudalei'], $connection);
                $processed['other_yewudalei'] = $otherYewudaleiId ?: null;
            }
        }
        
        // 8. 首次技术员转换
        if (isset($processed['first_tech_code']) && !empty($processed['first_tech_code'])) {
            $empId = DataMigrationHelper::getEmployeeIdByCode($processed['first_tech_code'], $connection);
            if ($empId) {
                $processed['first_tech_id'] = $empId;
            } else {
                Yii::log("首次技术员编号不存在: {$processed['first_tech_code']}", 'warning', 'DataMigration');
                $processed['first_tech_id'] = null;
            }
            unset($processed['first_tech_code']);
        }
        
        // 8.5 负责技术员转换（参考 ImportVirForm::valTechnicianList）
        if (isset($processed['technician_id_str']) && !empty($processed['technician_id_str'])) {
            $codeStr = $processed['technician_id_str'];
            $ids = array();
            $names = array();
            // 按逗号分解多个技术员编号
            $codeList = explode(',', $codeStr);
            foreach ($codeList as $code) {
                $code = trim($code);
                if (!empty($code)) {
                    $empRow = DataMigrationHelper::getEmployeeByCode($code, $connection);
                    if ($empRow) {
                        $ids[] = $empRow['id'];
                        $names[] = $empRow['name'] . ' (' . $empRow['code'] . ')';
                    } else {
                        Yii::log("负责技术员编号不存在: {$code}", 'warning', 'DataMigration');
                    }
                }
            }
            if (!empty($ids)) {
                // 将ID和名称以逗号分隔存储
                $processed['technician_id_str'] = implode(',', $ids);
                $processed['technician_id_text'] = implode(',', $names);
            } else {
                $processed['technician_id_str'] = null;
                $processed['technician_id_text'] = null;
            }
        } else {
            $processed['technician_id_str'] = null;
            $processed['technician_id_text'] = null;
        }
        
        // 9. 服务项目转换
        if (isset($processed['busine_name'])) {
            $row = DataMigrationHelper::getServiceTypeByName($processed['busine_name'], $connection);
            if ($row) {
                $processed['busine_id'] = $row['id_char'];
                $processed['busine_id_int'] = $row['id'];
                $processed['busine_id_text'] = $processed['busine_name'];
                $processed['service_type'] = $row['service_type'];
            } else {
                throw new Exception('服务项目不存在：' . $processed['busine_name']);
            }
        }
        
        // 10. 状态转换
        if (isset($processed['vir_status'])) {
            //  完整的虚拟合约状态映射（对应派单系统的6种状态）
            $statusMap = array(
                '生效中' => 30,      // 派单status=1
                '暂停' => 40,        // 派单status=2
                '终止' => 50,        // 派单status=3
            );
            
            if (isset($statusMap[$processed['vir_status']])) {
                $processed['vir_status'] = $statusMap[$processed['vir_status']];
            } elseif (!is_numeric($processed['vir_status'])) {
                //  如果是未知的文字状态，记录警告并设置为默认值（生效中）
                Yii::log("虚拟合约状态值异常: {$processed['vir_status']}，已重置为默认值30(生效中)", 'warning', 'DataMigration');
                $processed['vir_status'] = 30;
            } else {
                //  如果是数字但不在标准范围(10,30,40,50)内，设置为默认值
                $numericStatus = intval($processed['vir_status']);
                if (!in_array($numericStatus, array(10, 30, 40, 50))) {
                    Yii::log("虚拟合约状态值异常: {$processed['vir_status']}，已重置为默认值30(生效中)", 'warning', 'DataMigration');
                    $processed['vir_status'] = 30;
                }
            }
        }
        
        // 11. 服务频次类型转换
        if (isset($processed['service_fre_type'])) {
            $freTypeMap = array(
                '固定' => 1, 
                '固定每周' => 3, 
                '非固定' => 2, 
                '固定非固定金额' => 4,  //  修正：固定非固定金额的类型ID是4
                '呼叫式' => 3,
                '固定（一次性）' => 1  //  一次性服务也是固定频次类型
            );
            if (isset($freTypeMap[$processed['service_fre_type']])) {
                $processed['service_fre_type'] = $freTypeMap[$processed['service_fre_type']];
            }
        }
        
        // 11.1 一次性服务特殊处理：
        // - 固定频次类型（1,3）且服务总次数=1 => 一次性服务，年金额=0
        // - 固定非固定金额（4）不是一次性服务，即使服务总次数=1
        $freType = isset($processed['service_fre_type']) ? intval($processed['service_fre_type']) : 1;
        $serviceSum = isset($processed['service_sum']) ? intval($processed['service_sum']) : 0;
        
        if ($serviceSum == 1 && ($freType == 1 || $freType == 3)) {
            // 一次性服务（固定频次类型，服务总次数=1）：年金额 = 0
            $processed['year_amt'] = 0;
        }
        
        // 11.5 终止或暂停原因转换（使用缓存优化）
        if (isset($processed['stop_set_id']) && !empty($processed['stop_set_id'])) {
            // 只在合约被暂停或终止时处理该字段
            if (in_array($processed['vir_status'], array(40, 50))) {
                if (!is_numeric($processed['stop_set_id'])) {
                    $stopName = $processed['stop_set_id'];
                    // 根据虚拟合约状态确定停止类型: 暂停=1, 终止=2
                    $s_type = $processed['vir_status'] == 40 ? 1 : 2;
                    
                    // 使用缓存查找停止原因
                    $stopReasonId = DataMigrationHelper::getStopReasonId($s_type, $stopName, $connection);
                    $processed['stop_set_id'] = $stopReasonId;
                }
            } else {
                $processed['stop_set_id'] = null;
            }
        } else {
            $processed['stop_set_id'] = null;
        }
        
        // 12. 各种类型字段转换（付款方式、付款周期、收费方式、结算方式、账单日、应收期限）
        self::convertPaymentFields($processed);
        
        // 13. 日期处理
        $dateFields = array('sign_date', 'cont_start_dt', 'cont_end_dt', 'first_date', 'fast_date', 'stop_date');
        foreach ($dateFields as $field) {
            if (isset($processed[$field]) && $processed[$field] !== '') {
                $timestamp = strtotime($processed[$field]);
                $processed[$field] = $timestamp ? date('Y-m-d', $timestamp) : null;
            } else {
                $processed[$field] = null;
            }
        }
        
        // 14. 整数字段空值处理（确保空字符串转换为 null，避免数据库插入错误）
        $intFields = array(
            'surplus_num', 'service_sum', 'pay_month', 'pay_start', 'service_timer', 'cont_month_len',
            'bill_day', 'receivable_day', 'settle_type', 'fee_type', 'pay_type', 'pay_week',
            'other_sales_id', 'other_yewudalei', 'first_tech_id', 'stop_set_id',
            'service_fre_type', 'service_fre_sum'
        );
        foreach ($intFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        // 15. 金额和数字字段处理
        $numericFields = array(
            'month_amt', 'year_amt', 'deposit_amt', 'deposit_need', 'surplus_amt', 
            'amt_install', 'stop_month_amt', 'stop_year_amt', 'invoice_amount',
            'surplus_num', 'service_sum', 'pay_month', 'pay_start', 'service_timer', 
            'cont_month_len', 'bill_day', 'receivable_day'
        );
        foreach ($numericFields as $field) {
            if (isset($processed[$field])) {
                if ($processed[$field] === '' || $processed[$field] === null) {
                    $processed[$field] = null;
                } else {
                    // 处理带逗号的金额字符串，或直接处理数字
                    $val = str_replace(',', '', (string)$processed[$field]);
                    $processed[$field] = is_numeric($val) ? $val : null;
                }
            }
        }
        
        // 16. 布尔值处理
        $boolFields = array('bill_bool', 'prioritize_service', 'need_install', 'is_seal', 'is_client_seal');
        foreach ($boolFields as $field) {
            if (isset($processed[$field])) {
                $processed[$field] = ($processed[$field] === '是' || $processed[$field] === 'Y' || $processed[$field] === '1') ? 'Y' : 'N';
            }
        }
        
        // 17. 设置服务频次金额和次数
        if (!isset($processed['service_fre_amt'])) {
            //  service_fre_amt 存储的是年金额（除了一次性服务）
            $freType = isset($processed['service_fre_type']) ? intval($processed['service_fre_type']) : 1;
            $serviceSum = isset($processed['service_sum']) ? intval($processed['service_sum']) : 0;
            
            if ($serviceSum == 1 && ($freType == 1 || $freType == 3) && isset($processed['month_amt'])) {
                // 一次性服务（固定频次类型，服务总次数=1）：存储月金额
                $processed['service_fre_amt'] = $processed['month_amt'];
            } else {
                // 其他所有类型：存储年金额
                $processed['service_fre_amt'] = isset($processed['year_amt']) ? $processed['year_amt'] : 0;
            }
        }
        if (!isset($processed['service_fre_sum'])) {
            $processed['service_fre_sum'] = isset($processed['service_sum']) ? $processed['service_sum'] : 0;
        }
        
        // 17.5 严格执行金额映射规则
        //  正确理解：
        // - set_frequency=1,3: invoice_amount = 月金额
        // - set_frequency=2,4: invoice_amount = 年金额
        $freType = isset($processed['service_fre_type']) ? intval($processed['service_fre_type']) : 1;
        $serviceSum = isset($processed['service_sum']) ? intval($processed['service_sum']) : 0;
        
        if ($freType == 2 || $freType == 4) {
            //  非固定(2) / 固定非固定金额(4)：invoice_amount = 年金额
            $processed['invoice_amount'] = !empty($processed['year_amt']) ? $processed['year_amt'] : null;
            // 月金额不显示（设为null或0）
            $processed['month_amt'] = null;
            
        } elseif ($freType == 1 || $freType == 3) { 
            //  固定频次(1) / 固定每周(3)：invoice_amount = 月金额
            $processed['invoice_amount'] = !empty($processed['month_amt']) ? $processed['month_amt'] : null;
            
            // 特殊处理：一次性服务（服务总次数=1）
            if ($serviceSum == 1) {
                // 一次性服务：年金额 = 0
                $processed['year_amt'] = null;
            }
        }
        
        // 18. 根据主合同编号查找主合同
        if (isset($processed['cont_code']) && !empty($processed['cont_code'])) {
            $contRow = $connection->createCommand()
                ->select('*')
                ->from('sal_contract')
                ->where('cont_code=:cont_code', array(':cont_code' => $processed['cont_code']))
                ->queryRow();
            
            if ($contRow) {
                $processed['cont_id'] = $contRow['id'];
                $processed['clue_service_id'] = $contRow['clue_service_id'];
                
                $proRow = $connection->createCommand()
                    ->select('id')
                    ->from('sal_contpro')
                    ->where('cont_id=:cont_id', array(':cont_id' => $contRow['id']))
                    ->order('id ASC')
                    ->queryRow();
                
                if ($proRow) {
                    $processed['pro_id'] = $proRow['id'];
                }
                
                Yii::log("虚拟合约关联到主合同: cont_code={$processed['cont_code']}, cont_id={$contRow['id']}", 'info', 'DataMigration');
            } else {
                Yii::log("主合同编号不存在，将自动创建: cont_code={$processed['cont_code']}", 'warning', 'DataMigration');
            }
        }
        
        // 19. ⚠️ 关键顺序：必须先处理频次，再处理服务项目详情
        // 因为 processServiceInfo 会读取 service_fre_json、service_fre_text 等字段
        self::processServiceFrequency($processed);      // 第一步：生成频次数据
        self::processServiceInfo($processed, $connection);  // 第二步：将频次数据写入 virInfo
        
        return $processed;
    }
    
    /**
     * 插入虚拟合约数据（参考 ImportVirForm::saveOneData）
     * 
     * @param array $data 预处理后的数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @param int $reportId 导入任务ID
     * @return int 虚拟合约ID (vir_id)
     * @throws Exception 插入失败时抛出异常
     */
    public static function insert($data, $connection, $username, $reportId)
    {
        // 1. 查找门店（优先使用缓存）
        $storeRow = DataMigrationForm::getCachedStore($data['store_code'], $connection);
        
        if (!$storeRow) {
            throw new Exception('虚拟合约导入失败：找不到对应的门店（store_code=' . $data['store_code'] . '）');
        }
        
        if (empty($storeRow['clue_id'])) {
            throw new Exception('虚拟合约导入失败：门店未关联客户（store_code=' . $data['store_code'] . '）');
        }
        
        // 2. 计算合约月数
        $cont_month_len = DataMigrationHelper::computeMonthLen($data['cont_start_dt'], $data['cont_end_dt']);
        
        // 3. 如果没有主合约，或者主合约缺少必要的关联信息，需要创建
        $cont_id = isset($data['cont_id']) ? $data['cont_id'] : null;
        $clue_service_id = isset($data['clue_service_id']) ? $data['clue_service_id'] : null;
        $pro_id = isset($data['pro_id']) ? $data['pro_id'] : null;
        
        //  修复：检查是否需要创建主合约
        // - 如果 cont_id 为空，需要创建
        // - 如果 cont_id 存在但 clue_service_id 或 pro_id 为空，说明主合同数据不完整，也需要创建
        if (empty($cont_id) || empty($clue_service_id) || empty($pro_id)) {
            if (!empty($cont_id)) {
                // 主合同 ID 存在但关联信息不完整，记录警告并重新创建
                Yii::log("主合同 ID={$cont_id} 存在但缺少 clue_service_id 或 pro_id，将重新创建主合同", 'warning', 'DataMigration');
            }
            
            $result = self::createContractForVirtual($data, $storeRow, $cont_month_len, $connection, $username, $reportId);
            $cont_id = $result['cont_id'];
            $clue_service_id = $result['clue_service_id'];
            $pro_id = $result['pro_id'];
        }
        
        // 4. 生成或更新SSE关联数据（注意：detail_json 需要是数组格式）
        $dataForSSE = $data;
        if (isset($dataForSSE['detail_json']) && is_string($dataForSSE['detail_json'])) {
            $dataForSSE['detail_json'] = json_decode($dataForSSE['detail_json'], true);
        }
        $sse_id = self::computeContSSE($cont_id, $pro_id, $clue_service_id, $storeRow['clue_id'], $storeRow['id'], $dataForSSE, $connection, $username);
        
        // 5. 补充数据
        $data['create_staff'] = $data['sales_id'];
        $data['report_id'] = $reportId;
        
        // 6. 检查虚拟合同编号是否已存在（排除当前记录）
        if (!empty($data['vir_code'])) {
            $whereCondition = 'vir_code=:vir_code';
            $params = array(':vir_code' => $data['vir_code']);
            
            // 如果有 u_id，排除相同 u_id 的记录（允许重复导入覆盖）
            if (!empty($data['u_id'])) {
                $whereCondition .= ' AND (u_id IS NULL OR u_id != :u_id)';
                $params[':u_id'] = $data['u_id'];
            }
            
            $existingVirCodeRow = $connection->createCommand()
                ->select('id, vir_code')
                ->from('sal_contract_virtual')
                ->where($whereCondition, $params)
                ->queryRow();
            
            if ($existingVirCodeRow) {
                throw new Exception('虚拟合同编号已存在：' . $data['vir_code'] . '（vir_id=' . $existingVirCodeRow['id'] . '）');
            }
        }
        
        // 7. 检查是否已存在相同 u_id 的虚拟合约（允许重复导入覆盖）
        if (!empty($data['u_id'])) {
            $existingVirRow = $connection->createCommand()
                ->select('id, vir_code')
                ->from('sal_contract_virtual')
                ->where('u_id=:u_id', array(':u_id' => $data['u_id']))
                ->queryRow();
            
            if ($existingVirRow) {
                $oldVirId = $existingVirRow['id'];
                Yii::log('发现已存在的虚拟合约（vir_id=' . $oldVirId . ', vir_code=' . $existingVirRow['vir_code'] . ', u_id=' . $data['u_id'] . '），将删除旧数据后重新导入', 'info', 'DataMigration');
                
                // 删除旧的虚拟合约相关数据
                $connection->createCommand()->delete('sal_contract_vir_info', 'virtual_id=:virtual_id', array(':virtual_id' => $oldVirId));
                $connection->createCommand()->delete('sal_contract_vir_staff', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
                $connection->createCommand()->delete('sal_contract_vir_week', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
                $connection->createCommand()->delete('sal_contpro_virtual', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
                $connection->createCommand()->delete('sal_contract_virtual', 'id=:id', array(':id' => $oldVirId));
            }
        }
        
        // 7. 插入虚拟合约主表
        $saveKey = array(
            'cont_id', 'sse_id', 'clue_id', 'clue_type', 'clue_service_id', 'clue_store_id', 'vir_code', 'vir_status',
            'city', 'office_id', 'busine_id', 'service_type', 'receivable_day', 'bill_bool', 'bill_day', 'settle_type',
            'fee_type', 'deposit_rmk', 'deposit_amt', 'deposit_need', 'pay_start', 'pay_month', 'pay_type', 'pay_week',
            'service_timer', 'prioritize_service', 'sign_date', 'yewudalei', 'lbs_main', 'service_main', 'busine_id_text',
            'sales_id', 'create_staff', 'month_amt', 'year_amt', 'service_sum', 'surplus_num', 'surplus_amt',
            'call_fre_amt', 'service_fre_amt', 'service_fre_sum', 'service_fre_type', 'service_fre_json', 'service_fre_text',
            'cont_start_dt', 'cont_end_dt', 'cont_month_len', 'fast_date', 'first_date', 'need_install', 'amt_install',
            'other_sales_id', 'other_yewudalei', 'first_tech_id', 'technician_id_str', 'technician_id_text', 'external_source',
            'stop_set_id', 'stop_date', 'stop_month_amt', 'stop_year_amt', 'invoice_amount', 'detail_json', 'u_id', 'u_service_json', 'report_id',
        );
        
        $saveList = array();
        $data['cont_id'] = $cont_id;
        $data['sse_id'] = $sse_id;
        $data['clue_id'] = $storeRow['clue_id'];
        $data['clue_type'] = $storeRow['clue_type'];
        $data['clue_service_id'] = $clue_service_id;
        $data['clue_store_id'] = $storeRow['id'];
        $data['city'] = $storeRow['city'];
        $data['office_id'] = $storeRow['office_id'];
        $data['cont_month_len'] = $cont_month_len;
        
        // 定义整数字段列表（需要将空字符串转换为 null）
        $integerFields = array(
            // 关联ID字段
            'cont_id', 'sse_id', 'clue_id', 'clue_service_id', 'clue_store_id', 'office_id',
            'busine_id', 'yewudalei', 'lbs_main', 'service_main', 'sales_id', 'create_staff',
            // 数值字段
            'receivable_day', 'bill_bool', 'bill_day', 'settle_type', 'fee_type', 'deposit_amt', 
            'deposit_need', 'pay_type', 'pay_week', 'pay_month', 'service_timer', 'prioritize_service',
            'month_amt', 'year_amt', 'service_sum', 'surplus_num', 'surplus_amt', 'call_fre_amt',
            'service_fre_amt', 'service_fre_sum', 'service_fre_type', 'need_install', 'amt_install',
            'other_sales_id', 'other_yewudalei', 'first_tech_id', 'stop_set_id', 'stop_month_amt',
            'stop_year_amt', 'invoice_amount'
        );
        
        foreach ($saveKey as $key) {
            if (key_exists($key, $data)) {
                if (is_array($data[$key])) {
                    $saveList[$key] = json_encode($data[$key], JSON_UNESCAPED_UNICODE);
                } else if (in_array($key, $integerFields) && $data[$key] === '') {
                    // 整数字段如果是空字符串，转换为 null
                    $saveList[$key] = null;
                } else {
                    $saveList[$key] = $data[$key];
                }
            }
        }
        
        $saveList['lcu'] = $username;
        $connection->createCommand()->insert('sal_contract_virtual', $saveList);
        $vir_id = $connection->getLastInsertID();
        
        // 8. 插入虚拟合约进程
        // 重用 $saveList（与 ImportVirForm 保持一致）
        $proVirSave = $saveList;
        $proVirSave['pro_vir_type'] = 1;
        $proVirSave['cont_id'] = $data['cont_id'];
        $proVirSave['pro_id'] = $pro_id;
        $proVirSave['vir_id'] = $vir_id;
        $proVirSave['pro_code'] = 'VDL-' . $data['vir_code'];
        $proVirSave['pro_type'] = DataMigrationHelper::proTypeByStatus($data['vir_status']);
        $proVirSave['pro_date'] = $data['sign_date'];
        $proVirSave['pro_remark'] = "导入虚拟合约\n导入id：{$reportId}";
        $proVirSave['pro_status'] = 30;
        $proVirSave['pro_change'] = $data['vir_status'] == 30 ? $data['year_amt'] : $data['surplus_amt'];
        $proVirSave['pro_change'] = empty($proVirSave['pro_change']) ? 0 : $proVirSave['pro_change'];
        
        $connection->createCommand()->insert('sal_contpro_virtual', $proVirSave);
        
        // 9. 插入虚拟合约详细信息
        if (!empty($data['virInfo'])) {
            foreach ($data['virInfo'] as $virInfo) {
                $virInfo['virtual_id'] = $vir_id;
                $virInfo['lcu'] = $username;
                $connection->createCommand()->insert('sal_contract_vir_info', $virInfo);
            }
        }
        
        // 10. 插入虚拟合约员工关联
        $connection->createCommand()->insert('sal_contract_vir_staff', array(
            'vir_id' => $vir_id,
            'type' => 4,
            'employee_id' => $data['sales_id'],
            'u_yewudalei' => $data['yewudalei'],
            'role' => 1,
            'u_id' => isset($data['sales_u_id']) ? $data['sales_u_id'] : null,
            'lcu' => $username,
        ));
        
        if (!empty($data['other_sales_u_id'])) {
            $connection->createCommand()->insert('sal_contract_vir_staff', array(
                'vir_id' => $vir_id,
                'type' => 5,
                'employee_id' => $data['other_sales_id'],
                'u_yewudalei' => $data['other_yewudalei'],
                'role' => 0,
                'u_id' => $data['other_sales_u_id'],
                'lcu' => $username,
            ));
        }
        
        // 11. 插入虚拟合约周计划
        if (!empty($data['u_service_json']['list'])) {
            foreach ($data['u_service_json']['list'] as $weekList) {
                $weekList['vir_id'] = $vir_id;
                $weekList['lcu'] = $username;
                $connection->createCommand()->insert('sal_contract_vir_week', $weekList);
            }
        }
        
        // 12. 更新客户和门店状态
        $connection->createCommand()->update('sal_clue', array(
            'clue_status' => DataMigrationHelper::getClientStatusByClueID($storeRow['clue_id'], $connection),
        ), 'id=:id', array(':id' => $storeRow['clue_id']));
        
        $connection->createCommand()->update('sal_clue_store', array(
            'store_status' => DataMigrationHelper::getStoreStatusByStoreID($storeRow['id'], $connection),
        ), 'id=:id', array(':id' => $storeRow['id']));
        
        Yii::log('虚拟合约数据导入成功：vir_id=' . $vir_id . ', u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null'), 'info', 'DataMigration');
        
        return $vir_id;
    }
    
    /**
     * 更新虚拟合约数据（删除旧数据后重新插入）
     * 
     * @param array $data 预处理后的数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @param int $reportId 导入任务ID
     * @return int 虚拟合约ID (vir_id)
     * @throws Exception 更新失败时抛出异常
     */
    public static function update($data, $connection, $username, $reportId)
    {
        // 查找现有虚拟合约
        $existingVir = null;
        
        if (!empty($data['u_id'])) {
            // 优先根据 u_id 查找
            $existingVir = $connection->createCommand()
                ->select('id, vir_code, cont_id')
                ->from('sal_contract_virtual')
                ->where('u_id=:u_id', array(':u_id' => $data['u_id']))
                ->queryRow();
        } elseif (!empty($data['vir_code'])) {
            // 其次根据 vir_code 查找
            $existingVir = $connection->createCommand()
                ->select('id, vir_code, cont_id')
                ->from('sal_contract_virtual')
                ->where('vir_code=:vir_code', array(':vir_code' => $data['vir_code']))
                ->queryRow();
        }
        
        if (!$existingVir) {
            throw new Exception('虚拟合约不存在，无法更新（u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null') . ', vir_code=' . (isset($data['vir_code']) ? $data['vir_code'] : 'null') . '）');
        }
        
        $oldVirId = $existingVir['id'];
        Yii::log('更新虚拟合约（vir_id=' . $oldVirId . ', vir_code=' . $existingVir['vir_code'] . '），删除旧数据后重新导入', 'info', 'DataMigration');
        
        // 删除旧的虚拟合约相关数据
        $connection->createCommand()->delete('sal_contract_vir_info', 'virtual_id=:virtual_id', array(':virtual_id' => $oldVirId));
        $connection->createCommand()->delete('sal_contract_vir_staff', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
        $connection->createCommand()->delete('sal_contract_vir_week', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
        $connection->createCommand()->delete('sal_contpro_virtual', 'vir_id=:vir_id', array(':vir_id' => $oldVirId));
        $connection->createCommand()->delete('sal_contract_virtual', 'id=:id', array(':id' => $oldVirId));
        
        // 继承旧的 cont_id（如果新数据没有指定）
        if (empty($data['cont_id']) && !empty($existingVir['cont_id'])) {
            $data['cont_id'] = $existingVir['cont_id'];
        }
        
        // 调用 insert 方法重新插入
        return self::insert($data, $connection, $username, $reportId);
    }
    
    /**
     * 转换付款相关字段（严格匹配）
     */
    protected static function convertPaymentFields(&$processed)
    {
        $fieldNames = array(
            'pay_type' => '付款方式',
            'pay_week' => '付款周期',
            'fee_type' => '收费方式',
            'settle_type' => '结算方式',
            'bill_day' => '账单日',
            'receivable_day' => '应收期限',
        );
        
        foreach ($fieldNames as $field => $chineseName) {
            if (isset($processed[$field]) && !empty($processed[$field]) && !is_numeric($processed[$field])) {
                $originalValue = $processed[$field];
                $id = DataMigrationHelper::getEnumIdByName($field, $originalValue);
                
                if ($id !== null) {
                    $processed[$field] = $id;
                } else {
                    // 严格模式：映射失败直接抛出异常，而不是静默置空
                    $list = DataMigrationHelper::getEnumData($field);
                    $availableOptions = implode('、', $list);
                    throw new Exception(
                        "{$chineseName}的值'{$originalValue}'在系统中不存在！\n" .
                        "可用的选项有：{$availableOptions}\n" .
                        "请修改 Excel 中的数据，或在系统中添加该选项后重新导入。"
                    );
                }
            }
        }
    }
    
    /**
     * 解析服务项目详情字符串
     * 支持两种格式：
     * - 格式1（旧）："蛇;蜈蚣;千足虫"（分号分隔的名称列表）
     * - 格式2（新）："服务区域:一楼;标靶害虫:蛇;灭鼠:Y"（name:value 格式）
     * 
     * @param string $serviceText 服务项目详情字符串
     * @return array 解析后的数组 [['name' => '名称', 'value' => '值'], ...]
     */
    protected static function parseServiceTypeInfo($serviceText)
    {
        if (empty($serviceText)) {
            return array();
        }
        
        $items = explode(';', $serviceText);
        $result = array();
        
        foreach ($items as $item) {
            $item = trim($item);
            if (empty($item)) {
                continue;
            }
            
            // 检查是否包含冒号（新格式）
            if (strpos($item, ':') !== false) {
                // 新格式：name:value
                list($name, $value) = explode(':', $item, 2);
                $result[] = array(
                    'name' => trim($name),
                    'value' => trim($value)
                );
            } else {
                // 旧格式：只有 name，没有 value
                $result[] = array(
                    'name' => $item,
                    'value' => ''
                );
            }
        }
        
        return $result;
    }
    
    /**
     * 处理服务项目详情（参考 ImportVirForm::valServiceInfo）
     */
    protected static function processServiceInfo(&$data, $connection)
    {
        $virDetail = array();
        $virInfo = array();
        
        if (isset($data['busine_id']) && isset($data['month_amt'])) {
            $virInfo[] = array(
                'field_id' => 'svc_' . $data['busine_id'], 
                'field_value' => $data['month_amt']
            );
            $virDetail['svc_' . $data['busine_id']] = $data['month_amt'];
        }
        
        $freeStrList = array(
            'FreType' => 'service_fre_type',
            'FreSum' => 'service_fre_sum',
            'FreAmt' => 'service_fre_amt',
            'FreJson' => 'service_fre_json',
            'FreText' => 'service_fre_text',
        );
        
        foreach ($freeStrList as $keyName => $itemName) {
            if (isset($data[$itemName])) {
                $virInfo[] = array(
                    'field_id' => 'svc_' . $data['busine_id'] . $keyName, 
                    'field_value' => $data[$itemName]
                );
                $virDetail['svc_' . $data['busine_id'] . $keyName] = $data[$itemName];
            }
        }
        
        // 查询年金额字段
        if (isset($data['busine_id_int']) && isset($data['year_amt'])) {
            $yearRow = $connection->createCommand()
                ->select('*')
                ->from('sal_service_type_info')
                ->where("type_id=:id AND input_type='yearAmount'", array(':id' => $data['busine_id_int']))
                ->queryRow();
            
            if ($yearRow) {
                $virInfo[] = array(
                    'service_type_id' => $yearRow['id'],
                    'field_id' => 'svc_' . $yearRow['id_char'],
                    'field_value' => $data['year_amt']
                );
                $virDetail['svc_' . $yearRow['id_char']] = $data['year_amt'];
            }
        }
        
        // 处理服务项目详情（checkbox、device、method 以及 text/textarea）
        if (isset($data['busine_id_int'])) {
            // 1. 解析服务项目详情字符串
            // 支持两种格式：
            // 格式1：分号分隔的名称列表："蛇;蜈蚣;千足虫"（旧格式）
            // 格式2：name:value 格式："服务区域:一楼;标靶害虫:蛇"（新格式）
            if (!empty($data['serviceTypeInfo'])) {
                $parsedItems = self::parseServiceTypeInfo($data['serviceTypeInfo']);
                
                // 查询所有相关的服务类型配置
                $allNames = array();
                foreach ($parsedItems as $item) {
                    $allNames[] = $item['name'];
                }
                
                if (!empty($allNames)) {
                    $escapedNames = array_map(function($name) {
                        return "'" . str_replace("'", "\\'", $name) . "'";
                    }, $allNames);
                    $nameList = implode(',', $escapedNames);
                    
                    $rows = $connection->createCommand()
                        ->select('*')
                        ->from('sal_service_type_info')
                        ->where("type_id=:id AND name IN ({$nameList})", array(':id' => $data['busine_id_int']))
                        ->queryAll();
                    
                    if ($rows) {
                        // 建立 name => row 的映射
                        $nameToRow = array();
                        foreach ($rows as $row) {
                            $nameToRow[$row['name']] = $row;
                        }
                        
                        // 处理每个解析出的项目
                        foreach ($parsedItems as $item) {
                            if (isset($nameToRow[$item['name']])) {
                                $row = $nameToRow[$item['name']];
                                $inputType = $row['input_type'];
                                
                                // 根据输入类型和解析出的值设置字段值
                                if (in_array($inputType, array('checkbox', 'device', 'method')) && empty($item['value'])) {
                                    // 复选框类型且没有值，默认为选中
                                    $fieldValue = 'Y';
                                } else {
                                    // 有明确的值，使用解析出的值
                                    $fieldValue = !empty($item['value']) ? $item['value'] : 'Y';
                                }
                                
                                $virInfo[] = array(
                                    'service_type_id' => $row['id'],
                                    'field_id' => 'svc_' . $row['id_char'],
                                    'field_value' => $fieldValue
                                );
                                $virDetail['svc_' . $row['id_char']] = $fieldValue;
                            }
                        }
                    }
                }
            }

            // 2. 处理文本项（红框：服务区域、标靶害虫等） - 从单独的字段读取
            $textFields = array(
                '服务区域范围' => 'service_area_range',
                '非服务区域' => 'non_service_area',
                '标靶害虫' => 'target_pest'
            );
            
            foreach ($textFields as $chineseName => $englishKey) {
                if (!empty($data[$englishKey])) {
                    // 查找对应名称的配置项
                    $row = $connection->createCommand()
                        ->select('*')
                        ->from('sal_service_type_info')
                        ->where("type_id=:id AND name=:name", array(':id' => $data['busine_id_int'], ':name' => $chineseName))
                        ->queryRow();
                    
                    if ($row) {
                        $virInfo[] = array(
                            'service_type_id' => $row['id'],
                            'field_id' => 'svc_' . $row['id_char'],
                            'field_value' => $data[$englishKey]
                        );
                        $virDetail['svc_' . $row['id_char']] = $data[$englishKey];
                    }
                }
            }
        }
        
        $data['virInfo'] = $virInfo;
        $data['detail_json'] = $virDetail;  // 保持数组格式，不转 JSON 字符串
    }
    
    /**
     * 处理服务频次详情（完全对齐 ImportVirForm::valUServiceJson）
     * 
     * 功能：
     * 1. 提取 sal_contract_vir_week 表需要的 5 个字段
     * 2. 生成 monthPriceList 用于计算 service_fre_json 和 service_fre_text
     * 3. 设置 service_fre_amt、service_fre_sum、call_fre_amt
     */
    protected static function processServiceFrequency(&$data)
    {
        $u_service_title = isset($data['u_service_title']) ? $data['u_service_title'] : '';
        $u_service_json = array('title' => $u_service_title, 'list' => array());
        
        $freeJson = isset($data['u_service_info']) ? $data['u_service_info'] : '';
        $freeJson = empty($freeJson) ? array() : (is_string($freeJson) ? json_decode($freeJson, true) : $freeJson);
        $freeJson = is_array($freeJson) ? $freeJson : array();
        
        $monthPriceList = array();
        $call_fre_amt = 0;
        
        // 遍历服务频次数据，只提取需要的字段（与 ImportVirForm 完全一致）
        foreach ($freeJson as $freeRow) {
            if (isset($freeRow['month_cycle']) && is_numeric($freeRow['month_cycle']) 
                && isset($freeRow['unit_price']) && is_numeric($freeRow['unit_price'])) {
                
                $freeRow['month_cycle'] = intval($freeRow['month_cycle']);
                $freeRow['unit_price'] = floatval($freeRow['unit_price']);
                $call_fre_amt = $freeRow['unit_price'];
                
                // 计算月份周期（用于生成 service_fre_json）
                $monthNum = self::calcPeriodByMonth($freeRow['month_cycle']);
                $monthKey = $monthNum . '_' . $freeRow['unit_price'];
                if (!isset($monthPriceList[$monthKey])) {
                    $monthPriceList[$monthKey] = array(
                        'month' => $monthNum,
                        'price' => $freeRow['unit_price'],
                        'num' => 0
                    );
                }
                $monthPriceList[$monthKey]['num']++;
                
                // 只保留 sal_contract_vir_week 表需要的字段
                $temp = array(
                    'month_cycle' => $freeRow['month_cycle'],
                    'week_cycle' => isset($freeRow['week_cycle']) ? intval($freeRow['week_cycle']) : null,
                    'day_cycle' => isset($freeRow['day_cycle']) ? intval($freeRow['day_cycle']) : null,
                    'unit_price' => $freeRow['unit_price'],
                    'cycle_text' => isset($freeRow['cycle_text']) ? $freeRow['cycle_text'] : null,
                );
                $u_service_json['list'][] = $temp;
            }
        }
        
        // 处理 monthPriceList，生成 service_fre_json 和 service_fre_text
        if (!empty($monthPriceList)) {
            $monthPriceList = self::computeFreeByU($monthPriceList);
            $data['service_fre_text'] = '';
            
            // 修正：fre_type 应该与主表的 service_fre_type 保持同步
            // 这样 CRM 前端 JS 才能根据类型正确渲染“固定频次”页签下的内容
            $currentFreType = isset($data['service_fre_type']) ? $data['service_fre_type'] : 2;

            $data['service_fre_json'] = array(
                'fre_amt' => 0,      // 频次总年金额
                'fre_month' => 0,    // 频次总月金额
                'fre_sum' => 0,      // 频次总次数
                'fre_type' => $currentFreType, // 同步频次类型
                'fre_list' => array(), // 频次详情
            );
            
            // 如果有多种价格组合，标记为非固定频次
            if (count($monthPriceList) >= 2) {
                $data['service_fre_type'] = 2;
                $data['service_fre_json']['fre_type'] = 2;
            }
            
            // 修复：针对用户反馈保持频次描述简洁，并在文字末尾增加尾差说明的逻辑
            $total_fre_amt = 0; // 累计总金额（年）
            $total_fre_sum = 0; // 累计总次数（年）
            foreach ($monthPriceList as $monthPriceRow) {
                $targetMonthly = !empty($data['month_amt']) ? $data['month_amt'] : 0;
                $times = $monthPriceRow['fre_num'];
                $monthsInGroup = count($monthPriceRow['month']);

                if ($times > 0 && $targetMonthly > 0) {
                    // 1. 直接计算单次单价，四舍五入保留2位小数
                    $realUnitPrice = round($targetMonthly / $times, 2);
                    
                    // 2. 计算并记录尾差说明
                    $displayTotal = round($realUnitPrice * $times, 2);
                    $diff = round($targetMonthly - $displayTotal, 2);
                    $diffText = ($diff != 0) ? "(含尾差{$diff}元)" : "";

                    $monthPriceRow['fre_amt'] = $realUnitPrice;
                    $data['service_fre_json']['fre_list'][] = $monthPriceRow;
                    $data['service_fre_text'] .= implode('、', $monthPriceRow['month']) . "月,每月服务{$times}次,每次金额{$realUnitPrice}{$diffText};";
                } else {
                    // 兜底逻辑：如果没有月金额，保持原样
                    $data['service_fre_json']['fre_list'][] = $monthPriceRow;
                    $data['service_fre_text'] .= implode('、', $monthPriceRow['month']) . "月,每月服务{$times}次,每次金额{$monthPriceRow['fre_amt']};";
                }
                
                // 累计年总次数和总金额
                $total_fre_sum += ($times * $monthsInGroup);
                $total_fre_amt += ($targetMonthly * $monthsInGroup);
            }
            
            $data['service_fre_json']['fre_sum'] = $total_fre_sum;
            $data['service_fre_json']['fre_amt'] = $total_fre_amt;

            // 在 CRM 中，fre_month 存储的是“权重平均单次单价”
            $data['service_fre_json']['fre_month'] = $total_fre_sum > 0 
                ? round($total_fre_amt / $total_fre_sum, 2) 
                : 0;
            
            $data['service_fre_json'] = json_encode($data['service_fre_json'], JSON_UNESCAPED_UNICODE);
        }        
        $data['u_service_json'] = $u_service_json;
        
        // 设置服务频次金额和次数
        // 修正：恢复为年金额，保持与 ImportVirForm 逻辑一致
        $data['service_fre_amt'] = isset($data['year_amt']) ? $data['year_amt'] : 0;
        $data['service_fre_sum'] = isset($data['service_sum']) ? $data['service_sum'] : 0;
        $data['call_fre_amt'] = (isset($data['service_fre_type']) && $data['service_fre_type'] == 3) ? $call_fre_amt : 0;
    }
    
    /**
     * 计算月周期对应的月份编号（参考 ImportVirForm::calcPeriodByMonth）
     * 
     * @param int $cycle 周期值（2的幂次方）
     * @param int $max 最大月份数（默认12个月）
     * @return int 月份编号（1-12）
     */
    protected static function calcPeriodByMonth($cycle, $max = 12)
    {
        for ($i = 1; $i <= $max; $i++) {
            if (pow(2, $i - 1) == $cycle) {
                return $i;
            }
        }
        return 1;
    }
    
    /**
     * 合并同价格同次数的月份（参考 ImportVirForm::computeFreeByU）
     * 
     * @param array $monthPriceList 月份价格列表
     * @return array 合并后的频次列表
     */
    protected static function computeFreeByU($monthPriceList)
    {
        $list = array();
        foreach ($monthPriceList as $monthPriceRow) {
            $keyStr = $monthPriceRow['price'] . '_' . $monthPriceRow['num'];
            if (!isset($list[$keyStr])) {
                $list[$keyStr] = array(
                    'month' => array(),              // 频次包含月份
                    'fre_num' => $monthPriceRow['num'],  // 每月次数
                    'type_sum' => 3,                 // 次数类型：3=每月
                    'fre_amt' => $monthPriceRow['price'], // 每次金额
                    'type_amt' => 1,                 // 金额类型：1
                );
            }
            $list[$keyStr]['month'][] = $monthPriceRow['month'];
        }
        return $list;
    }
    
    /**
     * 为虚拟合约创建主合约（参考 ImportVirForm::computeContID）
     */
    protected static function createContractForVirtual($data, $storeRow, $cont_month_len, $connection, $username, $reportId)
    {
        // 1. 首先创建销售回访记录（商机）
        $connection->createCommand()->insert('sal_clue_service', array(
            'clue_id' => $storeRow['clue_id'],
            'clue_type' => $storeRow['clue_type'],
            'visit_type' => self::getDefaultVisitType($connection),
            'visit_obj' => self::getDefaultVisitObj($connection),
            'visit_obj_text' => self::getDefaultVisitObjText($connection),
            'create_staff' => $data['sales_id'],
            'busine_id' => $data['busine_id'],
            'busine_id_text' => $data['busine_id_text'],
            'sign_odds' => 100,
            'lbs_main' => $data['lbs_main'],
            'predict_date' => $data['sign_date'],
            'predict_amt' => isset($data['year_amt']) ? $data['year_amt'] : 0,
            'total_amt' => isset($data['year_amt']) ? $data['year_amt'] : 0,
            'total_num' => 1,
            'service_status' => $data['vir_status'],
            'lcu' => $username,
            'report_id' => $reportId,
        ));
        $clue_service_id = $connection->getLastInsertID();
        
        // 2. 创建主合同
        $contArr = array(
            'clue_id' => $storeRow['clue_id'],
            'clue_type' => $storeRow['clue_type'],
            'clue_service_id' => $clue_service_id,
            'city' => $storeRow['city'],
            'cont_code' => 'DL-' . $data['vir_code'],
            'sales_id' => $data['sales_id'],
            'lbs_main' => $data['lbs_main'],
            'predict_amt' => isset($data['year_amt']) ? $data['year_amt'] : 0,
            'store_sum' => 1,
            'cont_type' => 1,
            'sign_type' => 1,
            'total_sum' => isset($data['service_sum']) ? $data['service_sum'] : 0,
            'total_amt' => isset($data['year_amt']) ? $data['year_amt'] : 0,
            'cont_status' => $data['vir_status'],
            'stop_date' => isset($data['stop_date']) ? $data['stop_date'] : null,
            'surplus_num' => isset($data['surplus_num']) ? $data['surplus_num'] : null,
            'surplus_amt' => isset($data['surplus_amt']) ? $data['surplus_amt'] : null,
            'cont_start_dt' => $data['cont_start_dt'],
            'cont_end_dt' => $data['cont_end_dt'],
            'cont_month_len' => $cont_month_len,
            'sign_date' => $data['sign_date'],
            //  派单系统没有的字段不赋值，保持 null（不强制赋值）
            'area_bool' => isset($data['area_bool']) && $data['area_bool'] !== '' ? $data['area_bool'] : null,
            'group_bool' => isset($data['group_bool']) && $data['group_bool'] !== '' ? $data['group_bool'] : null,
            'prioritize_service' => !empty($data['prioritize_service']) ? $data['prioritize_service'] : null,
            'service_timer' => !empty($data['service_timer']) ? $data['service_timer'] : null,
            'pay_type' => !empty($data['pay_type']) ? $data['pay_type'] : null,
            'pay_week' => !empty($data['pay_week']) ? $data['pay_week'] : null,
            'pay_month' => !empty($data['pay_month']) ? $data['pay_month'] : null,
            'pay_start' => isset($data['pay_start']) && $data['pay_start'] !== '' ? $data['pay_start'] : null,
            'deposit_need' => isset($data['deposit_need']) && $data['deposit_need'] !== '' ? $data['deposit_need'] : null,
            'deposit_amt' => isset($data['deposit_amt']) && $data['deposit_amt'] !== '' ? $data['deposit_amt'] : null,
            'deposit_rmk' => isset($data['deposit_rmk']) && $data['deposit_rmk'] !== '' ? $data['deposit_rmk'] : null,
            'fee_type' => !empty($data['fee_type']) ? $data['fee_type'] : null,
            'settle_type' => !empty($data['settle_type']) ? $data['settle_type'] : null,
            'bill_day' => !empty($data['bill_day']) ? $data['bill_day'] : null,
            'bill_bool' => isset($data['bill_bool']) && $data['bill_bool'] !== '' ? $data['bill_bool'] : null,
            'receivable_day' => !empty($data['receivable_day']) ? $data['receivable_day'] : null,
            'yewudalei' => $data['yewudalei'],
            'other_sales_id' => !empty($data['other_sales_id']) ? $data['other_sales_id'] : null,
            'other_yewudalei' => !empty($data['other_yewudalei']) ? $data['other_yewudalei'] : null,
            'busine_id' => $data['busine_id'],
            'busine_id_text' => $data['busine_id_text'],
            'report_id' => $reportId,
            'lcu' => $username,
        );
        $connection->createCommand()->insert('sal_contract', $contArr);
        $cont_id = $connection->getLastInsertID();
        
        // 3. 创建主合同变更记录（初始执行状态）
        $contArr['cont_id'] = $cont_id;
        $contArr['pro_code'] = 'PDL-' . $data['vir_code'];
        $contArr['pro_type'] = DataMigrationHelper::proTypeByStatus($data['vir_status']);
        $contArr['pro_date'] = $data['sign_date'];
        $contArr['pro_remark'] = "导入虚拟合约自动生成\n导入id：{$reportId}";
        $contArr['pro_status'] = 30;
        $contArr['pro_change'] = $data['vir_status'] == 30 ? $data['year_amt'] : $data['surplus_amt'];
        $contArr['pro_change'] = empty($contArr['pro_change']) ? 0 : $contArr['pro_change'];
        $connection->createCommand()->insert('sal_contpro', $contArr);
        $pro_id = $connection->getLastInsertID();
        
        return array(
            'cont_id' => $cont_id,
            'clue_service_id' => $clue_service_id,
            'pro_id' => $pro_id,
        );
    }
    
    /**
     * 获取默认拜访类型
     */
    protected static function getDefaultVisitType($connection)
    {
        static $visitType = null;
        if ($visitType === null) {
            $typeRow = $connection->createCommand()
                ->select('id')
                ->from('sal_visit_type')
                ->order('id ASC')
                ->queryRow();
            $visitType = $typeRow ? $typeRow['id'] : 1;
        }
        return $visitType;
    }
    
    /**
     * 获取默认拜访对象
     */
    protected static function getDefaultVisitObj($connection)
    {
        static $visitObj = null;
        if ($visitObj === null) {
            $objRow = $connection->createCommand()
                ->select('id')
                ->from('sal_visit_obj')
                ->where("rpt_type='DEAL'")
                ->queryRow();
            $visitObj = $objRow ? $objRow['id'] : 1;
        }
        return $visitObj;
    }
    
    /**
     * 获取默认拜访对象文本
     */
    protected static function getDefaultVisitObjText($connection)
    {
        static $visitObjText = null;
        if ($visitObjText === null) {
            $objRow = $connection->createCommand()
                ->select('name')
                ->from('sal_visit_obj')
                ->where("rpt_type='DEAL'")
                ->queryRow();
            $visitObjText = $objRow ? $objRow['name'] : '签单';
        }
        return $visitObjText;
    }
    
    /**
     * 计算合约SSE关联数据（参考 ImportVirForm::computeContSSEID）
     */
    protected static function computeContSSE($cont_id, $pro_id, $clue_service_id, $clue_id, $clue_store_id, $data, $connection, $username)
    {
        // 准备SSE数据（detail_json 应该是数组格式）
        $detailJson = isset($data['detail_json']) ? $data['detail_json'] : array();
        if (is_string($detailJson)) {
            $detailJson = json_decode($detailJson, true);
            if (!is_array($detailJson)) {
                $detailJson = array();
            }
        }
        
        $sseArr = array(
            'clue_id' => $clue_id,
            'clue_service_id' => $clue_service_id,
            'clue_store_id' => $clue_store_id,
            'create_staff' => $data['sales_id'],
            'store_amt' => isset($data['year_amt']) ? $data['year_amt'] : 0,
            'service_sum' => isset($data['service_sum']) ? $data['service_sum'] : 0,
            'update_bool' => 3,
            'busine_id' => $data['busine_id'],
            'busine_id_text' => $data['busine_id_text'],
            'detail_json' => $detailJson,
            'lcu' => $username,
        );
        
        // 1. 处理 sal_clue_sre_soe（线索-商机-门店关联）
        $clueSSE = $connection->createCommand()
            ->select('*')
            ->from('sal_clue_sre_soe')
            ->where('clue_service_id=:clue_service_id AND clue_store_id=:clue_store_id', array(
                ':clue_service_id' => $clue_service_id,
                ':clue_store_id' => $clue_store_id,
            ))
            ->queryRow();
        
        if ($clueSSE) {
            $thisArr = self::mergeSSERow($sseArr, $clueSSE);
            $connection->createCommand()->update('sal_clue_sre_soe', $thisArr, 'id=' . $clueSSE['id']);
        } else {
            // 插入时需要将 detail_json 转换为字符串
            $insertArr = $sseArr;
            if (is_array($insertArr['detail_json'])) {
                $insertArr['detail_json'] = json_encode($insertArr['detail_json'], JSON_UNESCAPED_UNICODE);
            }
            $connection->createCommand()->insert('sal_clue_sre_soe', $insertArr);
        }
        
        // 2. 处理 sal_contract_sse（合同-门店关联）
        $contSSE = $connection->createCommand()
            ->select('*')
            ->from('sal_contract_sse')
            ->where('cont_id=:cont_id AND clue_store_id=:clue_store_id', array(
                ':cont_id' => $cont_id,
                ':clue_store_id' => $clue_store_id,
            ))
            ->queryRow();
        
        if ($contSSE) {
            $thisArr = self::mergeSSERow($sseArr, $contSSE);
            $connection->createCommand()->update('sal_contract_sse', $thisArr, 'id=' . $contSSE['id']);
            $sse_id = $contSSE['id'];
        } else {
            // 插入时需要将 detail_json 转换为字符串
            $insertArr = $sseArr;
            $insertArr['cont_id'] = $cont_id;
            if (is_array($insertArr['detail_json'])) {
                $insertArr['detail_json'] = json_encode($insertArr['detail_json'], JSON_UNESCAPED_UNICODE);
            }
            $connection->createCommand()->insert('sal_contract_sse', $insertArr);
            $sse_id = $connection->getLastInsertID();
        }
        
        // 3. 处理 sal_contpro_sse（合同进程-门店关联）
        $contProSSE = $connection->createCommand()
            ->select('*')
            ->from('sal_contpro_sse')
            ->where('pro_id=:pro_id AND clue_store_id=:clue_store_id', array(
                ':pro_id' => $pro_id,
                ':clue_store_id' => $clue_store_id,
            ))
            ->queryRow();
        
        if ($contProSSE) {
            $thisArr = self::mergeSSERow($sseArr, $contProSSE);
            $connection->createCommand()->update('sal_contpro_sse', $thisArr, 'id=' . $contProSSE['id']);
        } else {
            // 插入时需要将 detail_json 转换为字符串，并添加 cont_id 和 pro_id
            $insertArr = $sseArr;
            $insertArr['cont_id'] = $cont_id;  //  添加 cont_id
            $insertArr['pro_id'] = $pro_id;
            if (is_array($insertArr['detail_json'])) {
                $insertArr['detail_json'] = json_encode($insertArr['detail_json'], JSON_UNESCAPED_UNICODE);
            }
            $connection->createCommand()->insert('sal_contpro_sse', $insertArr);
        }
        
        return $sse_id;
    }
    
    /**
     * 合并SSE数据行（参考 ImportVirForm::mergeSSERow）
     */
    protected static function mergeSSERow($sseArr, $existingRow)
    {
        // 确保 detail_json 是数组格式
        if (empty($existingRow['detail_json'])) {
            $existingRow['detail_json'] = array();
        } elseif (is_string($existingRow['detail_json'])) {
            $existingRow['detail_json'] = json_decode($existingRow['detail_json'], true);
            if (!is_array($existingRow['detail_json'])) {
                $existingRow['detail_json'] = array();
            }
        }
        
        if (empty($sseArr['detail_json'])) {
            $sseArr['detail_json'] = array();
        } elseif (is_string($sseArr['detail_json'])) {
            $sseArr['detail_json'] = json_decode($sseArr['detail_json'], true);
            if (!is_array($sseArr['detail_json'])) {
                $sseArr['detail_json'] = array();
            }
        }
        
        // 合并数据
        $sseArr['busine_id'] = $existingRow['busine_id'] . ',' . $sseArr['busine_id'];
        $sseArr['busine_id_text'] = $existingRow['busine_id_text'] . '、' . $sseArr['busine_id_text'];
        $sseArr['detail_json'] = array_merge($existingRow['detail_json'], $sseArr['detail_json']);
        $sseArr['detail_json'] = json_encode($sseArr['detail_json'], JSON_UNESCAPED_UNICODE);
        $sseArr['store_amt'] += $existingRow['store_amt'];
        $sseArr['service_sum'] += $existingRow['service_sum'];
        
        return $sseArr;
    }
}

