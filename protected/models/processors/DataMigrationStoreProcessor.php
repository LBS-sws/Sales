<?php

//  导入依赖的类
Yii::import('application.models.DataMigrationHelper');
Yii::import('application.models.processors.DataMigrationClientProcessor');

/**
 * 门店迁移数据处理器
 * 负责门店数据的预处理、插入和更新
 * 
 * @see DataMigrationForm 主控制器
 * @see DataMigrationHelper 辅助工具类
 * @see DataMigrationClientProcessor 客户处理器（用于自动创建客户）
 */
class DataMigrationStoreProcessor
{
    /**
     * 门店数据预处理（中文字段名 → 英文字段名 + 数据转换）
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
            '客户编号' => 'clue_code',
            '门店编号' => 'store_code',
            '客户名称' => 'store_name',
            '客户简称' => 'store_full_name',
            '门店简称' => 'store_full_name',
            '客户类别' => 'clue_type',
            '门店状态' => 'store_status',
            '跟进销售的员工编号' => 'create_staff',
            '服务类型' => 'service_type',
            '是否集团客户' => 'group_bool',
            '重点客户' => 'cust_vip',
            '客户录入时间' => 'entry_date',
            '城市' => 'city',
            '办事处' => 'office_id',
            '区域' => 'district',
            '详细地址' => 'address',
            '经度' => 'longitude',
            '纬度' => 'latitude',
            '行业类别' => 'cust_class',
            '业务大类' => 'yewudalei',
            '税号' => 'tax_id',
            '开票地址' => 'invoice_address',
            '开票开户行' => 'invoice_number',
            '开票账号' => 'invoice_user',
            '开票备注' => 'invoice_rmk',
            '开票抬头' => 'invoice_header',
            '联系人编号' => 'person_code',
            '联系人名称' => 'cust_person',
            '联系人电话' => 'cust_tel',
            '联系人邮箱' => 'cust_email',
            '联系人职务' => 'cust_person_role',
            '面积' => 'area',
            '门店备注' => 'store_remark',
            '派单系统门店id' => 'u_id',
            '派单系统门店关联联系人id' => 'u_person_id',
            '其它联系人' => 'u_person_list',
        );
        
        foreach ($fieldMap as $chineseKey => $englishKey) {
            if (isset($data[$chineseKey])) {
                $processed[$englishKey] = $data[$chineseKey];
            }
        }
        
        // 2. 自动提取税号
        $processed = self::autoExtractTaxId($processed);
        
        // 3. 处理其它联系人列表
        if (isset($processed['u_person_list']) && !empty($processed['u_person_list'])) {
            $personList = explode(';', $processed['u_person_list']);
            $uPersonData = array();
            if (!empty($personList)) {
                foreach ($personList as $personStr) {
                    $personItem = explode(',', $personStr);
                    if (!empty($personItem[1]) && !empty($personItem[2])) {
                        $temp = array(
                            'person_code' => $personItem[0],
                            'cust_person' => $personItem[1],
                            'cust_tel' => trim($personItem[2]),
                            'cust_email' => empty($personItem[3]) ? null : trim($personItem[3]),
                            'cust_person_role' => empty($personItem[4]) ? null : trim($personItem[4]),
                        );
                        if (!empty($personItem[5]) && is_numeric(trim($personItem[5]))) {
                            $temp['u_id'] = intval(trim($personItem[5]));
                        }
                        if (!empty($personItem[6]) && is_numeric(trim($personItem[6]))) {
                            $temp['u_group_id'] = intval(trim($personItem[6]));
                        }
                        $uPersonData[] = $temp;
                    }
                }
            }
            $processed['uPersonData'] = $uPersonData;
        }
        
        // 4. 门店类别转换
        if (isset($processed['clue_type'])) {
            $clueTypeMap = array(
                '地推' => 1, 
                'KA' => 2,
                '单门店' => 1,  //  新增：单门店也是地推类型
            );
            if (isset($clueTypeMap[$processed['clue_type']])) {
                $processed['clue_type'] = $clueTypeMap[$processed['clue_type']];
            } elseif (!is_numeric($processed['clue_type'])) {
                $processed['clue_type'] = !empty($processed['clue_code']) ? 2 : 1;
            }
        } else {
            $processed['clue_type'] = !empty($processed['clue_code']) ? 2 : 1;
        }
        
        // 5. 门店状态转换
        if (isset($processed['store_status'])) {
            $storeStatusMap = array(
                '服务中' => 2,
                '已停止' => 3,
                '其他' => 4,
                '未服务' => 1,
                '未生效' => 0,
            );
            if (isset($storeStatusMap[$processed['store_status']])) {
                $processed['store_status'] = $storeStatusMap[$processed['store_status']];
            } elseif (!is_numeric($processed['store_status'])) {
                // 未知文本状态，默认为服务中
                $processed['store_status'] = 2;
            } else {
                //  修复：如果是数字但不在标准范围(0-4)内，默认为服务中(2)
                $numericStatus = intval($processed['store_status']);
                if ($numericStatus < 0 || $numericStatus > 4) {
                    Yii::log("门店状态值异常: {$processed['store_status']}，已重置为默认值2(服务中)", 'warning', 'DataMigration');
                    $processed['store_status'] = 2;
                }
                // 否则保持原数字状态（0-4范围内）
            }
        } else {
            $processed['store_status'] = 2;
        }
        
        // 6. 员工编号转ID
        if (isset($processed['create_staff']) && !empty($processed['create_staff'])) {
            $empCode = $processed['create_staff'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['create_staff'] = $empId;
            } else {
                //  员工不存在时跳过，不抛异常
                Yii::log("跟进销售的员工编号不存在：{$empCode}，已跳过此字段", 'warning', 'DataMigration');
                unset($processed['create_staff']);
            }
        }
        
        // 7. 服务类型转换（service_type 是客户服务类型，对应 swo_customer_type 表）
        // 注意：service_type 字段存储的是客户服务类型ID（JSON数组格式），如 ["1","2","3"]
        // 对应 swo_customer_type 表的 ID，例如：1=IA客户, 2=IB客户, 3=IC客户, 4=ID客户
        if (isset($processed['service_type']) && !empty($processed['service_type'])) {
            $serviceName = $processed['service_type'];
            if (!is_numeric($serviceName)) {
                // 如果是服务名称（如 "IA客户;IB客户"），需要转换为 ID
                // 支持分号或逗号分隔
                $serviceList = preg_split('/[;,]/', $serviceName);
                $serviceIds = array();
                foreach ($serviceList as $serviceStr) {
                    $serviceStr = trim($serviceStr);
                    if (!empty($serviceStr)) {
                        // 通过 description 匹配（如 "IA客户", "IB客户"）
                        $serviceId = DataMigrationHelper::getCustomerTypeIdByName($serviceStr, $connection);
                        if ($serviceId) {
                            $serviceIds[] = $serviceId;
                        }
                    }
                }
                if (!empty($serviceIds)) {
                    // 转换为 JSON 数组格式：["1","2"]，确保ID是字符串格式
                    $processed['service_type'] = json_encode(array_map('strval', $serviceIds));
                } else {
                    $processed['service_type'] = '[]';  // 空的 JSON 数组
                }
            }
        } else {
            $processed['service_type'] = '[]';  // 空的 JSON 数组
        }
        
        // 8. 办事处转换
        if (isset($processed['office_id']) && !empty($processed['office_id'])) {
            $officeName = $processed['office_id'];
            if (!is_numeric($officeName)) {
                $city = isset($processed['city']) ? $processed['city'] : '';
                $officeId = DataMigrationHelper::getOfficeIdByNameAndCity($officeName, $city, $connection);
                if ($officeId) {
                    $processed['office_id'] = $officeId;
                } else {
                    //  办事处不存在时置空，不影响导入
                    Yii::log("办事处不存在：{$officeName}（城市：{$city}），已置空", 'warning', 'DataMigration');
                    $processed['office_id'] = 0;
                }
            }
        } else {
            $processed['office_id'] = 0;
        }
        
        // 9. 业务大类转换
        if (isset($processed['yewudalei'])) {
            $yewudalei = $processed['yewudalei'];
            $clueType = isset($processed['clue_type']) ? $processed['clue_type'] : 1;
            
            if ($clueType == 1) {
                $yewudalei = '地推';
            } elseif ($yewudalei == '地推') {
                $yewudalei = 'KA';
            }
            
            if (!is_numeric($yewudalei)) {
                $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($yewudalei, $connection);
                if ($yewudaleiId) {
                    $processed['yewudalei'] = $yewudaleiId;
                } else {
                    $defaultName = ($clueType == 1) ? '地推' : 'KA';
                    $yewudaleiId = DataMigrationHelper::getYewudaleiIdByName($defaultName, $connection);
                    $processed['yewudalei'] = $yewudaleiId ?: null;
                }
            }
        }
        
        // 10. 城市名称转代码
        if (isset($processed['city'])) {
            $originalCity = $processed['city'];
            if ($processed['city'] === '全国') {
                $processed['city'] = '中国';
            }
            if (!preg_match('/^[A-Z]{2,3}$/', $processed['city'])) {
                //  先尝试直接匹配城市
                $cityCode = DataMigrationHelper::getCityCodeByName($processed['city'], $connection);
                if ($cityCode) {
                    $processed['city'] = $cityCode;
                } else {
                    //  如果是"XXX办事处"格式，尝试提取城市名称
                    $cityName = preg_replace('/办事处$/', '', $processed['city']);
                    if ($cityName !== $processed['city']) {
                        $cityCode = DataMigrationHelper::getCityCodeByName($cityName, $connection);
                        if ($cityCode) {
                            Yii::log("城市识别：'{$originalCity}' -> '{$cityName}' ({$cityCode})", 'info', 'DataMigration');
                            $processed['city'] = $cityCode;
                        } else {
                            //  城市不存在时置空，不抛异常
                            Yii::log("城市不存在：{$originalCity}，已置空", 'warning', 'DataMigration');
                            $processed['city'] = null;
                        }
                    } else {
                        //  城市不存在时置空，不抛异常
                        Yii::log("城市不存在：{$originalCity}，已置空", 'warning', 'DataMigration');
                        $processed['city'] = null;
                    }
                }
            }
        }
        
        // 11. 行业类别转换
        if (isset($processed['cust_class']) && !empty($processed['cust_class'])) {
            $custClass = $processed['cust_class'];
            if (!is_numeric($custClass)) {
                $row = DataMigrationHelper::getCustClassByName($custClass, $connection);
                if ($row) {
                    $processed['cust_class'] = $row['id'];
                    $processed['cust_class_group'] = $row['nature_id'];
                } else {
                    $processed['cust_class'] = null;
                }
            }
        } else {
            $processed['cust_class'] = null;
        }
        
        // 12. 区域转换
        if (isset($processed['district']) && !empty($processed['district'])) {
            $districtName = $processed['district'];
            if (!is_numeric($districtName)) {
                $cityName = isset($data['城市']) ? $data['城市'] : '';
                $row = DataMigrationHelper::getDistrictByName($districtName, $cityName, $connection);
                if ($row) {
                    $processed['district'] = $row['id'];
                    if (empty($processed['address'])) {
                        $processed['address'] = $row['tree_names'];
                    }
                } else {
                    $processed['district'] = null;
                }
            }
        } else {
            $processed['district'] = null;
        }
        
        // 13. 是否集团客户转换
        if (isset($processed['group_bool'])) {
            $groupBoolMap = array('是' => 'Y', '否' => 'N', 'Y' => 'Y', 'N' => 'N');
            if (isset($groupBoolMap[$processed['group_bool']])) {
                $processed['group_bool'] = $groupBoolMap[$processed['group_bool']];
            } else {
                $processed['group_bool'] = 'N'; // 默认值
            }
        } else {
            $processed['group_bool'] = 'N'; // 默认值
        }
        
        // 14. 重点客户转换
        if (isset($processed['cust_vip'])) {
            $custVipMap = array('是' => 'Y', '否' => 'N', 'Y' => 'Y', 'N' => 'N');
            if (isset($custVipMap[$processed['cust_vip']])) {
                $processed['cust_vip'] = $custVipMap[$processed['cust_vip']];
            } else {
                $processed['cust_vip'] = 'N'; // 默认值
            }
        } else {
            $processed['cust_vip'] = 'N'; // 默认值
        }
        
        // 15. 客户录入时间处理
        if (isset($processed['entry_date']) && !empty($processed['entry_date'])) {
            $processed['entry_date'] = date('Y-m-d', strtotime($processed['entry_date']));
        }
        
        // 16. 可选整数字段空值处理
        $optionalIntegerFields = array('area', 'u_person_id', 'district', 'cust_class', 'cust_class_group');
        foreach ($optionalIntegerFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }
        
        return $processed;
    }
    
    /**
     * 插入门店数据（参考 ImportClientStoreForm::saveOneData）
     * 
     * @param array $data 预处理后的数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @param int $reportId 导入任务ID
     * @return int 门店ID (clue_store_id)
     * @throws Exception 插入失败时抛出异常
     */
    public static function insert($data, $connection, $username, $reportId)
    {
        $req_dt = date("Y-m-d H:i:s");
        
        // 1. 确保客户存在（根据 project_code 查找）
        $clue_id = null;
        if (!empty($data['clue_code'])) {
            $clueRow = $connection->createCommand()
                ->select('id')
                ->from('sal_clue')
                ->where('clue_code=:code', array(':code' => $data['clue_code']))
                ->queryRow();
            if ($clueRow) {
                $clue_id = $clueRow['id'];
            }
        }
        
        // 如果没有找到客户，自动创建
        if (empty($clue_id)) {
            $clue_id = DataMigrationClientProcessor::autoCreateForStore($data, $connection, $username, $req_dt, $reportId);
        }
        $data['clue_id'] = $clue_id;
        
        // 2. 处理开票信息
        if (!empty($data['clue_id']) && !empty($data['invoice_header'])) {
            $storeName = isset($data['store_name']) && !empty($data['store_name']) ? $data['store_name'] : 'store';
            $invoice_name = $storeName . '_sys_' . time();
            $invoice_type = 2; // 默认专票
            if (empty($data['invoice_address']) || empty($data['tax_id']) || 
                empty($data['invoice_number']) || empty($data['invoice_user'])) {
                $invoice_type = 1; // 普票
            }
            
            $connection->createCommand()->insert("sal_clue_invoice", array(
                "clue_id" => $data['clue_id'],
                "clue_type" => $data['clue_type'],
                "invoice_name" => $invoice_name,
                "city" => $data['city'],
                "invoice_type" => $invoice_type,
                "invoice_header" => $data['invoice_header'],
                "tax_id" => isset($data['tax_id']) ? $data['tax_id'] : null,
                "invoice_address" => isset($data['invoice_address']) ? $data['invoice_address'] : null,
                "invoice_number" => isset($data['invoice_number']) ? $data['invoice_number'] : null,
                "invoice_user" => isset($data['invoice_user']) ? $data['invoice_user'] : null,
                "invoice_rmk" => isset($data['invoice_rmk']) ? $data['invoice_rmk'] : null,
                "lcu" => $username,
                "lcd" => $req_dt,
            ));
            $data['invoice_id'] = $connection->getLastInsertID();
        }
        
        // 3. 生成门店编号（如果没有提供）
        if (empty($data['store_code'])) {
            $row = $connection->createCommand()
                ->select('count(*) as sum')
                ->from('sal_clue_store')
                ->where('clue_id=:clue_id', array(':clue_id' => $data['clue_id']))
                ->queryRow();
            $num = $row && !empty($row['sum']) ? $row['sum'] : 0;
            $charNum = floor($num / 1000) + 65;
            $num = floor($num % 1000);
            $num = '' . (1000 + $num);
            $num = mb_substr($num, 1);
            $store_code = $data['clue_code'] . '-' . chr($charNum) . $num;
            $data['store_code'] = $store_code;
        }
        
        // 4. 插入门店主表（只包含 sal_clue_store 表中真实存在的字段）
        $saveKey = array(
            'clue_id', 'clue_type', 'store_code', 'store_name', 'store_full_name', 'create_staff', 
            'yewudalei', 'cust_class_group', 'cust_class', 'city', 'office_id', 'address', 'district',
            'invoice_id', 'latitude', 'longitude', 'u_id', 'cust_person', 'cust_tel', 'cust_email', 
            'cust_person_role', 'area', 'store_remark', 'store_status'  //  store_status 确实存在于 sal_clue_store 表中
        );
        $saveList = array();
        foreach ($saveKey as $key) {
            if (key_exists($key, $data)) {
                $saveList[$key] = $data[$key];
            }
        }
        if (key_exists("area", $saveList) && empty($saveList["area"])) {
            $saveList["area"] = null;
        }
        $saveList["report_id"] = $reportId;
        $saveList["lcu"] = $username;
        
        $connection->createCommand()->insert("sal_clue_store", $saveList);
        $clue_store_id = $connection->getLastInsertID();
        
        // 5. 插入门店历史记录
        $connection->createCommand()->insert("sal_clue_history", array(
            "table_id" => $clue_store_id,
            "table_type" => 2,
            "history_type" => 1,
            "history_html" => "<span>派单数据导入，导入id：{$reportId}</span>",
            "lcu" => $username,
        ));
        
        // 6. 插入主联系人
        if (!empty($saveList['cust_person']) && !empty($saveList['cust_tel'])) {
            $connection->createCommand()->insert("sal_clue_person", array(
                "clue_id" => $data['clue_id'],
                "clue_store_id" => $clue_store_id,
                "person_code" => isset($data['person_code']) ? $data['person_code'] : null,
                "person_pws" => empty($data['u_id']) ? null : 1,
                "cust_person" => $saveList['cust_person'],
                "cust_tel" => $saveList['cust_tel'],
                "cust_email" => isset($saveList['cust_email']) ? $saveList['cust_email'] : null,
                "cust_person_role" => isset($saveList['cust_person_role']) ? $saveList['cust_person_role'] : null,
                "u_id" => !empty($data['u_person_id']) ? $data['u_person_id'] : null,
                "u_group_id" => !empty($data['u_group_id']) ? $data['u_group_id'] : null,
                "lcu" => $username,
                "lcd" => $req_dt,
            ));
            $cust_id = $connection->getLastInsertID();
            
            // 如果没有联系人编号，自动生成
            if (empty($data['person_code'])) {
                $connection->createCommand()->update("sal_clue_person", array(
                    "person_code" => ClientPersonForm::computeCodeX($data['clue_id'], $clue_store_id, $cust_id),
                ), "id=:id", array(":id" => $cust_id));
            }
        }
        
        // 7. 插入其它联系人
        if (!empty($data['uPersonData'])) {
            foreach ($data['uPersonData'] as $uPerson) {
                $uPerson['clue_id'] = $data['clue_id'];
                $uPerson['clue_store_id'] = $clue_store_id;
                $uPerson['person_pws'] = empty($uPerson['u_id']) ? null : 1;
                $uPerson['lcu'] = $username;
                $uPerson['lcd'] = $req_dt;
                $connection->createCommand()->insert("sal_clue_person", $uPerson);
                $cust_id = $connection->getLastInsertID();
                
                if (empty($uPerson['person_code'])) {
                    $connection->createCommand()->update("sal_clue_person", array(
                        "person_code" => ClientPersonForm::computeCodeX($data['clue_id'], $clue_store_id, $cust_id),
                    ), "id=:id", array(":id" => $cust_id));
                }
            }
        }
        
        Yii::log('门店数据导入成功：store_id=' . $clue_store_id . ', u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null'), 'info', 'DataMigration');
        
        return $clue_store_id;
    }
    
    /**
     * 更新门店数据
     * 
     * @param array $data 预处理后的数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @throws Exception 更新失败时抛出异常
     */
    public static function update($data, $connection, $username, $reportId)
    {
        // TODO: 实现门店更新逻辑
        throw new Exception('门店更新功能待实现');
    }
    
    /**
     * 自动提取税号（从开票备注等字段中智能识别）
     * 
     * @param array $data 数据
     * @return array 处理后的数据
     */
    protected static function autoExtractTaxId($data)
    {
        // 如果已经有税号，直接返回
        if (!empty($data['tax_id'])) {
            return $data;
        }
        
        // 从开票备注中提取税号（18位统一社会信用代码或15位税号）
        if (!empty($data['invoice_rmk'])) {
            // 匹配18位统一社会信用代码
            if (preg_match('/([0-9A-Z]{18})/', $data['invoice_rmk'], $matches)) {
                $data['tax_id'] = $matches[1];
                return $data;
            }
            // 匹配15位税号
            if (preg_match('/([0-9]{15})/', $data['invoice_rmk'], $matches)) {
                $data['tax_id'] = $matches[1];
                return $data;
            }
        }
        
        // 从开票抬头中提取（通常税号会跟在抬头后面）
        if (!empty($data['invoice_header'])) {
            if (preg_match('/([0-9A-Z]{18})/', $data['invoice_header'], $matches)) {
                $data['tax_id'] = $matches[1];
                return $data;
            }
            if (preg_match('/([0-9]{15})/', $data['invoice_header'], $matches)) {
                $data['tax_id'] = $matches[1];
                return $data;
            }
        }
        
        return $data;
    }
}

