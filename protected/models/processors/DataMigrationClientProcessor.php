<?php

//  导入依赖的类
Yii::import('application.models.DataMigrationHelper');

/**
 * 客户迁移数据处理器
 * 负责客户数据的预处理、插入和更新
 *
 * @see DataMigrationForm 主控制器
 * @see DataMigrationHelper 辅助工具类
 */
class DataMigrationClientProcessor
{
    /**
     * 清理字符串中的特殊字符和空白
     * @param string $str 要清理的字符串
     * @param bool $removeAllSpaces 是否移除所有空格（默认false，只trim两端空格）
     * @return string 清理后的字符串
     */
    private static function cleanString($str, $removeAllSpaces = false)
    {
        if (empty($str) || !is_string($str)) {
            return $str;
        }
        
        // 去除两端空白
        $str = trim($str);
        
        // 如果需要移除所有空格
        if ($removeAllSpaces) {
            $str = preg_replace('/\s+/', '', $str);
        }
        
        return $str;
    }
    
    /**
     * 确保数组中所有值都是标量（防止"Array to string conversion"错误）
     * @param array &$data 要处理的数据数组（引用传递）
     * @param string $context 上下文名称（用于日志）
     */
    private static function ensureScalarValues(&$data, $context = '')
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // 如果是数组，转换为JSON字符串或用分号连接
                if (empty($value)) {
                    $data[$key] = '';
                } else {
                    // 对于简单数组，用分号连接；对于复杂数组，使用JSON
                    $isSimpleArray = true;
                    foreach ($value as $item) {
                        if (is_array($item) || is_object($item)) {
                            $isSimpleArray = false;
                            break;
                        }
                    }
                    
                    if ($isSimpleArray) {
                        $data[$key] = implode(';', array_filter($value));
                    } else {
                        $data[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }
                }
                
                $contextStr = $context ? " ({$context})" : '';
                Yii::log("字段 {$key}{$contextStr} 是数组类型，已转换为字符串：" . $data[$key], 'warning', 'DataMigration');
            }
        }
    }
    
    /**
     * 客户数据预处理（中文字段名 → 英文字段名 + 数据转换）
     * 参考 ImportClientForm 的 eveList 字段定义
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

        // 1. 基本字段映射（直接对应）
        $fieldMap = array(
            '客户编号' => 'clue_code',
            '客户名称' => 'cust_name',
            '客户状态' => 'clue_status',
            '客户简称' => 'full_name',
            '客户录入时间' => 'entry_date',
            '客户类别' => 'clue_type',
            '服务类型' => 'service_type',
            '业务大类' => 'yewudalei',
            '是否集团客户' => 'group_bool',
            '重点客户' => 'cust_vip',
            '行业类别' => 'cust_class',
            '城市' => 'city',
            '区域' => 'district',
            '街道' => 'street',
            '详细地址' => 'address',
            '经度' => 'longitude',
            '纬度' => 'latitude',
            '联系人编号' => 'person_code',
            '联系人名称' => 'cust_person',
            '联系人电话' => 'cust_tel',
            '联系人邮箱' => 'cust_email',
            '联系人职务' => 'cust_person_role',
            '联系人地址' => 'cust_address',
            '面积' => 'area',
            '客户备注' => 'clue_remark',
            '派单系统客户id' => 'u_id',
            '派单系统客户关联城市id' => 'u_area_id',
            '派单系统客户关联主要负责人id' => 'u_staff_id',
            '派单系统客户关联联系人id' => 'u_person_id',
            '派单系统客户关联联系人分组id' => 'u_group_id',
        );

        foreach ($fieldMap as $chineseKey => $englishKey) {
            if (isset($data[$chineseKey])) {
                $processed[$englishKey] = $data[$chineseKey];
            }
        }

        // 1.5 清理关键字段中的特殊字符和空白
        $fieldsToClean = array('clue_code', 'cust_name', 'full_name', 'city', 'district', 'person_code');
        foreach ($fieldsToClean as $field) {
            if (isset($processed[$field])) {
                // city字段需要移除所有空格，其他字段只trim两端
                $removeAllSpaces = ($field === 'city' || $field === 'clue_code');
                $processed[$field] = self::cleanString($processed[$field], $removeAllSpaces);
            }
        }

        // 2. 客户类别转换（参考 ImportForm::valClueType）
        if (isset($processed['clue_type'])) {
            $clueTypeMap = array('地推' => 1, 'KA' => 2);
            if (isset($clueTypeMap[$processed['clue_type']])) {
                $processed['clue_type'] = $clueTypeMap[$processed['clue_type']];
            } elseif (!is_numeric($processed['clue_type'])) {
                // 有客户编号（project_code）= KA客户，无客户编号 = 地推客户
                $processed['clue_type'] = !empty($processed['clue_code']) ? 2 : 1;
            }
        } else {
            // 如果没有提供clue_type，根据客户编号智能判断
            $processed['clue_type'] = !empty($processed['clue_code']) ? 2 : 1;
        }

        // 2.5 客户状态转换
        // 注意：clue_status 包含线索状态(0-5)和客户状态(10,30,40,50)
        // 线索状态：0=待跟进, 1=跟进中, 2=商机, 3=报价确认, 4=合同确认, 5=已转化
        // 客户状态：10=进行中, 30=进行中, 40=已暂停, 50=已终止
        if (isset($processed['clue_status'])) {
            $clueStatusMap = array(
                '服务中' => 30,      // 派单status=1 → 进行中
                '进行中' => 30,      // 派单status=1 → 进行中
                '已转化' => 5,       // 已转化
                '已终止' => 50,      // 派单status=2 → 已终止
                '已结束' => 50,      // 派单status=2 → 已终止
                '未生效' => 0,       // 待跟进
                '已停止' => 50,      // 已终止
                '已暂停' => 40,      // 已暂停
                '待跟进' => 0,
                '跟进中' => 1,
                '商机' => 2,
                '报价确认' => 3,
                '合同确认' => 4,
                '其他' => 30,        // 默认为进行中
            );
            if (isset($clueStatusMap[$processed['clue_status']])) {
                $processed['clue_status'] = $clueStatusMap[$processed['clue_status']];
            } elseif (!is_numeric($processed['clue_status'])) {
                $processed['clue_status'] = 30;  // 默认为进行中（派单status=1）
            }
        } else {
            $processed['clue_status'] = 30;  // 默认为进行中（派单status=1）
        }

        // 3. 业务大类转换
        if (isset($processed['yewudalei'])) {
            $yewudalei = $processed['yewudalei'];
            $clueType = isset($processed['clue_type']) ? $processed['clue_type'] : 1;

            // 根据客户类别调整业务大类名称
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

        // 4. 员工编号转ID
        if (isset($data['跟进销售的员工编号'])) {
            $empCode = $data['跟进销售的员工编号'];
            $empId = DataMigrationHelper::getEmployeeIdByCode($empCode, $connection);
            if ($empId) {
                $processed['rec_employee_id'] = $empId;
            } else {
                throw new Exception('跟进销售的员工编号不存在：' . $empCode);
            }
        }

        // 5. 城市名称转代码（城市名称已在1.5步骤中清理过）
        if (isset($processed['city']) && !empty($processed['city'])) {
            if ($processed['city'] === '全国') {
                $processed['city'] = '中国';
            }
            // 如果不是城市代码格式（如"SH"、"BJ"），则需要转换
            if (!preg_match('/^[A-Z]{2,3}$/', $processed['city'])) {
                $cityCode = DataMigrationHelper::getCityCodeByName($processed['city'], $connection);
                if ($cityCode) {
                    $processed['city'] = $cityCode;
                } else {
                    throw new Exception('城市不存在：' . $processed['city']);
                }
            }
        }

        // 5.1 服务类型转换（支持逗号或分号分隔的多个服务类型）
        if (isset($processed['service_type']) && !empty($processed['service_type'])) {
            $serviceName = $processed['service_type'];
            Yii::log("[DEBUG] 开始处理服务类型，原始值：{$serviceName}", 'info', 'DataMigration');
            
            if (!is_numeric($serviceName)) {
                // 支持逗号或分号分隔
                $serviceList = preg_split('/[,;]/', $serviceName);
                $serviceIds = array();
                $serviceNames = array(); // 记录未找到ID的服务名称
                
                foreach ($serviceList as $serviceStr) {
                    $serviceStr = trim($serviceStr);
                    if (empty($serviceStr)) {
                        continue;
                    }
                    
                    //  去掉"客户"后缀（如 "IA客户" → "IA"）
                    $serviceStrClean = preg_replace('/客户$/', '', $serviceStr);
                    
                    // 尝试查找服务类型ID（支持按 description 或 code 查找）
                    $serviceId = DataMigrationHelper::getCustomerTypeIdByName($serviceStrClean, $connection);
                    Yii::log("[DEBUG] 查询服务类型 '{$serviceStrClean}' 返回ID：" . ($serviceId ? $serviceId : 'null'), 'info', 'DataMigration');
                    
                    if ($serviceId) {
                        $serviceIds[] = $serviceId;
                    } else {
                        // 如果找不到，记录未匹配的名称
                        $serviceNames[] = $serviceStrClean;
                        Yii::log("未找到服务类型映射 - 代码/名称：{$serviceStrClean}，原始值：{$serviceStr}，请检查 swo_customer_type 表", 'warning', 'DataMigration');
                    }
                }
                
                if (!empty($serviceIds)) {
                    // 转换为 JSON 数组格式：["1","2"]
                    $processed['service_type'] = json_encode(array_map('strval', $serviceIds));
                    Yii::log("[DEBUG] 找到服务类型ID，最终值：{$processed['service_type']}", 'info', 'DataMigration');
                } else {
                    // 如果没有找到任何ID，记录错误并设置为空数组
                    Yii::log("[ERROR] 所有服务类型都未找到ID！原始值：{$serviceName}，清理后：" . implode(',', $serviceNames) . "，将设置为空数组", 'error', 'DataMigration');
                    // 设置为空的 JSON 数组
                    $processed['service_type'] = '[]';
                }
            } elseif (is_numeric($serviceName)) {
                // 单个数字ID，转换为 JSON 数组格式：["1"]
                $processed['service_type'] = json_encode([strval($serviceName)]);
                Yii::log("[DEBUG] 数字ID，最终值：{$processed['service_type']}", 'info', 'DataMigration');
            }
            
            Yii::log("[DEBUG] 服务类型处理完成，最终值：{$processed['service_type']}", 'info', 'DataMigration');
        }

        // 5.2 行业类别转换
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

        // 5.3 是否集团客户转换
        $clueType = isset($processed['clue_type']) ? $processed['clue_type'] : 1;
        if ($clueType == 2) {
            $processed['group_bool'] = 'Y';
        } else {
            if (isset($processed['group_bool'])) {
                if ($processed['group_bool'] === '是' || $processed['group_bool'] === 'Y' || $processed['group_bool'] === '1' || $processed['group_bool'] === 1) {
                    $processed['group_bool'] = 'Y';
                } else {
                    $processed['group_bool'] = 'N';
                }
            } else {
                $processed['group_bool'] = 'N';
            }
        }

        // 5.4 重点客户转换
        if (isset($processed['cust_vip'])) {
            if ($processed['cust_vip'] === '是' || $processed['cust_vip'] === 'Y' || $processed['cust_vip'] === '1' || $processed['cust_vip'] === 1) {
                $processed['cust_vip'] = 'Y';
            } else {
                $processed['cust_vip'] = 'N';
            }
        } else {
            $processed['cust_vip'] = 'N';
        }

        // 5.5 区域转换
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

        // 6. 处理其它销售
        if (isset($data['其它销售'])) {
            $staffCodes = explode(';', $data['其它销售']);
            $staffIds = array();
            foreach ($staffCodes as $code) {
                $code = trim($code);
                if (!empty($code)) {
                    $staffId = DataMigrationHelper::getEmployeeIdByCode($code, $connection);
                    if ($staffId) {
                        $staffIds[] = $staffId;
                    }
                }
            }
            if (!empty($staffIds)) {
                $processed['u_staff_list'] = implode(';', $staffIds);
            }
        }

        // 7. 处理其它城市
        if (isset($data['其它城市'])) {
            $cityNames = explode(';', $data['其它城市']);
            $cityCodes = array();
            foreach ($cityNames as $name) {
                $name = trim($name);
                if (!empty($name)) {
                    if ($name === '全国') {
                        $name = '中国';
                    }
                    $cityCode = DataMigrationHelper::getCityCodeByName($name, $connection);
                    if ($cityCode) {
                        $cityCodes[] = $cityCode;
                    }
                }
            }
            if (!empty($cityCodes)) {
                $processed['u_area_list'] = implode(';', $cityCodes);
            }
        }

        // 8. 日期格式处理
        if (isset($processed['entry_date'])) {
            $timestamp = strtotime($processed['entry_date']);
            if ($timestamp) {
                $processed['entry_date'] = date('Y-m-d', $timestamp);
            }
        }

        // 9. 可选整数字段空值处理
        $optionalIntegerFields = array('area', 'u_group_id', 'u_area_id', 'u_staff_id', 'u_person_id', 'district', 'cust_class', 'cust_class_group');
        foreach ($optionalIntegerFields as $field) {
            if (isset($processed[$field]) && $processed[$field] === '') {
                $processed[$field] = null;
            }
        }

        return $processed;
    }

    /**
     * 插入或更新客户数据（如果客户编号已存在则更新）
     *
     * @param array $data 预处理后的数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @param int $reportId 导入任务ID
     * @return int 客户ID (clue_id)
     * @throws Exception 插入/更新失败时抛出异常
     */
    public static function insert($data, $connection, $username, $reportId)
    {
        $req_dt = date("Y-m-d H:i:s");

        // 检查客户是否已存在（优先按客户编号查找）
        // ⚠️ 重要：KA和地推之间可以有相同的客户名称/编号，必须按业务大类区分
        $clueCode = isset($data['clue_code']) ? $data['clue_code'] : '';
        $custName = isset($data['cust_name']) ? $data['cust_name'] : '';
        $yewudalei = isset($data['yewudalei']) ? $data['yewudalei'] : '';
        
        $existingClueId = null;

        // 1. 优先检查客户编号（如果有客户编号，按编号+业务大类查找）
        if (!empty($clueCode) && !empty($yewudalei)) {
            $existingClue = $connection->createCommand()
                ->select('id, clue_code, cust_name, yewudalei, u_id')
                ->from('sal_clue')
                ->where('clue_code=:clue_code AND yewudalei=:yewudalei', array(
                    ':clue_code' => $clueCode,
                    ':yewudalei' => $yewudalei,
                ))
                ->queryRow();

            if ($existingClue) {
                $existingClueId = $existingClue['id'];
                $yewudaleiName = self::getYewudaleiName($yewudalei, $connection);
                $oldName = $existingClue['cust_name'];
                $newName = isset($data['cust_name']) ? $data['cust_name'] : '未提供';
                
                // 修改逻辑：即使已经有 u_id，也要更新为派单系统的最新值
                if (!empty($existingClue['u_id'])) {
                    Yii::log("客户已存在且已有派单系统ID (u_id={$existingClue['u_id']})，但会执行更新操作，确保数据与派单系统一致 - 客户编号：{$clueCode}，业务大类：{$yewudaleiName}，客户ID：{$existingClueId}", 'info', 'DataMigration');
                }
                
                Yii::log("检测到客户编号在该业务大类下已存在，将执行更新操作 - 客户编号：{$clueCode}，业务大类：{$yewudaleiName}，客户ID：{$existingClueId}，原客户名称：{$oldName}，新客户名称：{$newName}", 'info', 'DataMigration');
                // 调用更新方法
                return self::update($data, $connection, $username, $reportId, $existingClueId);
            }
        }

        // 2. 如果没有客户编号，检查客户名称是否重复（必须在同一业务大类下）
        if (!empty($custName) && !empty($yewudalei) && empty($existingClueId)) {
            $existingClue = $connection->createCommand()
                ->select('id, clue_code, cust_name, yewudalei, u_id')
                ->from('sal_clue')
                ->where('cust_name=:cust_name AND yewudalei=:yewudalei', array(
                    ':cust_name' => $custName,
                    ':yewudalei' => $yewudalei,
                ))
                ->queryRow();

            if ($existingClue) {
                $yewudaleiName = self::getYewudaleiName($yewudalei, $connection);
                
                // 修改逻辑：即使已经有 u_id，也要更新为派单系统的最新值
                if (!empty($existingClue['u_id'])) {
                    Yii::log("客户已存在且已有派单系统ID (u_id={$existingClue['u_id']})，但会执行更新操作，确保数据与派单系统一致 - 客户名称：{$custName}，业务大类：{$yewudaleiName}", 'info', 'DataMigration');
                }
                
                // 客户名称重复处理：
                // 1. 如果是KA客户（clue_type=2），自动在名称后加"(KA)"后缀
                $clueType = isset($data['clue_type']) ? $data['clue_type'] : null;
                if ($clueType == 2) {
                    // 自动添加(KA)后缀
                    $newCustName = $custName . '(KA)';
                    
                    // 检查添加后缀的名称是否也重复
                    $existingClueWithSuffix = $connection->createCommand()
                        ->select('id')
                        ->from('sal_clue')
                        ->where('cust_name=:cust_name AND yewudalei=:yewudalei', array(
                            ':cust_name' => $newCustName,
                            ':yewudalei' => $yewudalei,
                        ))
                        ->queryRow();
                    
                    if (!$existingClueWithSuffix) {
                        // 更新客户名称，添加(KA)后缀
                        $data['cust_name'] = $newCustName;
                        if (isset($data['full_name']) && $data['full_name'] == $custName) {
                            $data['full_name'] = $newCustName;
                        }
                        Yii::log("检测到KA客户名称重复，自动添加(KA)后缀 - 原名称：{$custName}，新名称：{$newCustName}，业务大类：{$yewudaleiName}", 'info', 'DataMigration');
                        // 继续插入流程（使用新名称）
                    } else {
                        // 添加后缀后仍然重复，抛出错误
                        throw new Exception("客户名称在该业务大类下已存在（客户编号：{$existingClue['clue_code']}，客户名称：{$existingClue['cust_name']}，业务大类：{$yewudaleiName}）。即使添加(KA)后缀后仍然重复，请手动处理。");
                    }
                } else {
                    // 非KA客户，直接报错
                    throw new Exception("客户名称在该业务大类下已存在（客户编号：{$existingClue['clue_code']}，客户名称：{$existingClue['cust_name']}，业务大类：{$yewudaleiName}）。请提供客户编号以更新现有客户。");
                }
            }
        }

        // 3. 插入客户主表 sal_clue
        $saveKey = array(
            'clue_type', 'service_type', 'cust_name', 'full_name', 'clue_code', 'abbr_code', 'entry_date',
            'rec_employee_id', 'yewudalei', 'group_bool', 'cust_vip', 'cust_class', 'cust_class_group',
            'city', 'address', 'district', 'street', 'latitude', 'longitude',
            'u_id', 'ka_id', 'u_group_id', 'cust_person', 'cust_tel', 'cust_email',
            'cust_person_role', 'cust_address', 'area', 'clue_remark', 'clue_status',
        );
        $saveList = array();
        foreach ($saveKey as $key) {
            if (isset($data[$key])) {
                $saveList[$key] = $data[$key];
            }
        }
        if (isset($saveList["area"]) && empty($saveList["area"])) {
            $saveList["area"] = null;
        }
        $saveList["report_id"] = $reportId;
        
        //  强制设置为客户（不是线索）
        // table_type: 1=线索, 2=客户
        $saveList["table_type"] = 2;  // 导入的都是正式客户
        
        //  设置客户状态（导入的都是已签约的正式客户）
        if (!isset($saveList["clue_status"])) {
            $saveList["clue_status"] = 30;  // 30=进行中（派单status=1），表示服务中的客户
        }
        
        //  设置接收类型（导入的客户都有明确的负责人）
        // rec_type: 1=指定员工, 2=地区可见, 3=销售自取
        $saveList["rec_type"] = 1;  // 导入的客户都指定了负责员工
        
        $saveList["lcu"] = $username;
        
        //  兼容处理：确保所有字段都是标量值（防止数组转字符串错误）
        self::ensureScalarValues($saveList, 'insert客户主表');

        $connection->createCommand()->insert("sal_clue", $saveList);
        $clue_id = $connection->getLastInsertID();

        // 4. 插入客户历史记录（显示业务大类和行业类别信息）
        $historyInfo = array();
        if (!empty($saveList['yewudalei'])) {
            $yewudaleiName = self::getYewudaleiName($saveList['yewudalei'], $connection);
            $historyInfo[] = "业务大类：{$yewudaleiName}";
        }
        if (!empty($saveList['cust_class'])) {
            $custClassName = self::getCustClassName($saveList['cust_class'], $connection);
            if (!empty($custClassName)) {
                $historyInfo[] = "行业类别：{$custClassName}";
            }
        }
        $historyInfoStr = !empty($historyInfo) ? '<br/>' . implode('、', $historyInfo) : '';
        
        $connection->createCommand()->insert("sal_clue_history", array(
            "table_id" => $clue_id,
            "table_type" => 1,
            "history_type" => 1,
            "history_html" => "<span>派单数据导入，导入任务ID：{$reportId}{$historyInfoStr}</span>",
            "lcu" => $username,
        ));

        // 5. 插入客户城市关联
        $connection->createCommand()->insert("sal_clue_u_area", array(
            "clue_id" => $clue_id,
            "city_code" => $saveList['city'],
            "city_type" => 1,
            "u_id" => !empty($data['u_area_id']) ? $data['u_area_id'] : null,
            "lcu" => $username,
            "lcd" => $req_dt,
        ));

        // 6. 插入客户员工关联
        $connection->createCommand()->insert("sal_clue_u_staff", array(
            "clue_id" => $clue_id,
            "employee_id" => $saveList['rec_employee_id'],
            "employee_type" => 1,
            "u_id" => !empty($data['u_staff_id']) ? $data['u_staff_id'] : null,
            "lcu" => $username,
            "lcd" => $req_dt,
        ));

        // 7. 如果有联系人信息，插入联系人
        if (!empty($saveList['cust_person']) && !empty($saveList['cust_tel'])) {
            $connection->createCommand()->insert("sal_clue_person", array(
                "clue_id" => $clue_id,
                "clue_store_id" => 0,
                "person_code" => isset($data['person_code']) ? $data['person_code'] : null,
                "cust_person" => $saveList['cust_person'],
                "cust_tel" => $saveList['cust_tel'],
                "cust_email" => isset($saveList['cust_email']) ? $saveList['cust_email'] : null,
                "cust_person_role" => isset($saveList['cust_person_role']) ? $saveList['cust_person_role'] : null,
                "u_id" => !empty($data['u_person_id']) ? $data['u_person_id'] : null,
                "lcu" => $username,
                "lcd" => $req_dt,
            ));
        }

        // 8. 处理其它销售
        if (!empty($data['u_staff_list'])) {
            $staffIds = explode(';', $data['u_staff_list']);
            foreach ($staffIds as $staffId) {
                if (!empty($staffId)) {
                    $connection->createCommand()->insert("sal_clue_u_staff", array(
                        "clue_id" => $clue_id,
                        "employee_id" => $staffId,
                        "employee_type" => 2,
                        "u_id" => null,
                        "lcu" => $username,
                        "lcd" => $req_dt,
                    ));
                }
            }
        }

        // 9. 处理其它城市
        if (!empty($data['u_area_list'])) {
            $cityCodes = explode(';', $data['u_area_list']);
            foreach ($cityCodes as $cityCode) {
                if (!empty($cityCode) && $cityCode != $saveList['city']) {
                    $connection->createCommand()->insert("sal_clue_u_area", array(
                        "clue_id" => $clue_id,
                        "city_code" => $cityCode,
                        "city_type" => 2,
                        "u_id" => null,
                        "lcu" => $username,
                        "lcd" => $req_dt,
                    ));
                }
            }
        }

        Yii::log('客户数据导入成功：clue_id=' . $clue_id . ', u_id=' . (isset($data['u_id']) ? $data['u_id'] : 'null'), 'info', 'DataMigration');

        return $clue_id;
    }

    /**
     * 更新客户数据
     *
     * @param array $data 预处理后的数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @param int $reportId 导入任务ID
     * @param int $clueId 客户ID（如果不提供，则根据客户编号查找）
     * @return int 客户ID (clue_id)
     * @throws Exception 更新失败时抛出异常
     */
    public static function update($data, $connection, $username, $reportId, $clueId = null)
    {
        $req_dt = date("Y-m-d H:i:s");
        
        // 如果没有提供客户ID，根据客户编号查找
        if (empty($clueId)) {
            $clueCode = isset($data['clue_code']) ? $data['clue_code'] : '';
            if (empty($clueCode)) {
                throw new Exception('更新客户时必须提供客户编号或客户ID');
            }
            
            $yewudalei = isset($data['yewudalei']) ? $data['yewudalei'] : '';
            $whereStr = 'clue_code=:clue_code';
            $params = array(':clue_code' => $clueCode);
            
            if (!empty($yewudalei)) {
                $whereStr .= ' AND yewudalei=:yewudalei';
                $params[':yewudalei'] = $yewudalei;
            }
            
            $existingClue = $connection->createCommand()
                ->select('id, u_id')
                ->from('sal_clue')
                ->where($whereStr, $params)
                ->queryRow();
            
            if (empty($existingClue)) {
                throw new Exception("找不到客户编号为 {$clueCode} 的客户");
            }
            
            $clueId = $existingClue['id'];
            
            // 修改逻辑：即使已经有 u_id，也要更新为派单系统的最新值
            if (!empty($existingClue['u_id'])) {
                Yii::log("客户已有派单系统ID (u_id={$existingClue['u_id']})，但会执行更新操作，确保数据与派单系统一致 - 客户编号：{$clueCode}，客户ID：{$clueId}", 'info', 'DataMigration');
            }
        } else {
            // 修改逻辑：即使提供了 clueId 且已有 u_id，也要更新为派单系统的最新值
            $existingClue = $connection->createCommand()
                ->select('u_id')
                ->from('sal_clue')
                ->where('id=:id', array(':id' => $clueId))
                ->queryRow();
            
            if ($existingClue && !empty($existingClue['u_id'])) {
                Yii::log("客户已有派单系统ID (u_id={$existingClue['u_id']})，但会执行更新操作，确保数据与派单系统一致 - 客户ID：{$clueId}", 'info', 'DataMigration');
            }
        }
        
        // 1. 更新客户主表 sal_clue
        $updateKey = array(
            'clue_type', 'service_type', 'cust_name', 'full_name', 'clue_code', 'abbr_code', 'entry_date',
            'rec_employee_id', 'yewudalei', 'group_bool', 'cust_vip', 'cust_class', 'cust_class_group',
            'city', 'address', 'district', 'street', 'latitude', 'longitude',
            'u_id', 'ka_id', 'u_group_id', 'cust_person', 'cust_tel', 'cust_email',
            'cust_person_role', 'cust_address', 'area', 'clue_remark', 'clue_status',
        );
        $updateList = array();
        foreach ($updateKey as $key) {
            if (isset($data[$key])) {
                $updateList[$key] = $data[$key];
            }
        }
        
        if (isset($updateList["area"]) && empty($updateList["area"])) {
            $updateList["area"] = null;
        }
        
        //  确保客户状态被更新（如果导入数据中包含状态信息）
        // 如果没有提供状态，则设置为默认值：30=进行中
        if (!isset($updateList["clue_status"]) || $updateList["clue_status"] === '') {
            $updateList["clue_status"] = 30;  // 默认为进行中（派单status=1，服务中）
        }
        
        //  强制更新为客户（不是线索）
        $updateList["rec_type"] = 1;    // 1=指定员工（已分配给具体负责人）
        $updateList["table_type"] = 2;  // 2=客户（不是线索），强制覆盖原值
        
        // 不覆盖 report_id（保留原始导入记录）
        
        $updateList["luu"] = $username;
        $updateList["lud"] = $req_dt;
        
        //  兼容处理：确保所有字段都是标量值（防止数组转字符串错误）
        self::ensureScalarValues($updateList, 'update客户主表');
        
        $connection->createCommand()->update("sal_clue", $updateList, "id=:id", array(':id' => $clueId));
        
        // 2. 插入客户历史记录（显示友好的字段名称）
        $fieldNameMap = array(
            'clue_type' => '客户类别',
            'service_type' => '服务类型',
            'cust_name' => '客户名称',
            'full_name' => '客户简称',
            'clue_code' => '客户编号',
            'abbr_code' => '简拼编号',
            'entry_date' => '录入时间',
            'rec_employee_id' => '跟进销售',
            'yewudalei' => '业务大类',
            'group_bool' => '是否集团',
            'cust_vip' => '重点客户',
            'cust_class' => '行业类别',
            'cust_class_group' => '行业大类',
            'city' => '城市',
            'address' => '详细地址',
            'district' => '区域',
            'street' => '街道',
            'latitude' => '纬度',
            'longitude' => '经度',
            'u_id' => '派单系统客户ID',
            'ka_id' => 'KA客户ID',
            'u_group_id' => '派单系统分组ID',
            'cust_person' => '联系人',
            'cust_tel' => '联系电话',
            'cust_email' => '联系邮箱',
            'cust_person_role' => '联系人职务',
            'cust_address' => '联系地址',
            'area' => '面积',
            'clue_remark' => '客户备注',
            'clue_status' => '客户状态',
        );
        
        $changeFieldsDisplay = array();
        foreach ($updateList as $key => $value) {
            if (!in_array($key, array('luu', 'lud'))) {
                $displayName = isset($fieldNameMap[$key]) ? $fieldNameMap[$key] : $key;
                
                // 特殊处理：显示名称而不是ID
                if ($key == 'yewudalei' && !empty($value)) {
                    $yewudaleiName = self::getYewudaleiName($value, $connection);
                    $changeFieldsDisplay[] = "{$displayName}({$yewudaleiName})";
                } elseif ($key == 'cust_class' && !empty($value)) {
                    $custClassName = self::getCustClassName($value, $connection);
                    if (!empty($custClassName)) {
                        $changeFieldsDisplay[] = "{$displayName}({$custClassName})";
                    } else {
                        $changeFieldsDisplay[] = $displayName;
                    }
                } else {
                    $changeFieldsDisplay[] = $displayName;
                }
            }
        }
        $changeFieldsStr = implode('、', $changeFieldsDisplay);
        
        $connection->createCommand()->insert("sal_clue_history", array(
            "table_id" => $clueId,
            "table_type" => 1,
            "history_type" => 1,
            "history_html" => "<span>派单数据导入更新，导入任务ID：{$reportId}<br/>更新字段：{$changeFieldsStr}</span>",
            "lcu" => $username,
        ));
        
        // 3. 更新主城市关联（如果有城市信息）
        if (isset($updateList['city'])) {
            // 先检查是否已存在主城市记录
            $existingArea = $connection->createCommand()
                ->select('id')
                ->from('sal_clue_u_area')
                ->where('clue_id=:clue_id AND city_type=1', array(':clue_id' => $clueId))
                ->queryScalar();
            
            if ($existingArea) {
                // 更新现有记录
                $connection->createCommand()->update("sal_clue_u_area", array(
                    "city_code" => $updateList['city'],
                    "u_id" => !empty($data['u_area_id']) ? $data['u_area_id'] : null,
                    "luu" => $username,
                    "lud" => $req_dt,
                ), "id=:id", array(':id' => $existingArea));
            } else {
                // 插入新记录
                $connection->createCommand()->insert("sal_clue_u_area", array(
                    "clue_id" => $clueId,
                    "city_code" => $updateList['city'],
                    "city_type" => 1,
                    "u_id" => !empty($data['u_area_id']) ? $data['u_area_id'] : null,
                    "lcu" => $username,
                    "lcd" => $req_dt,
                ));
            }
        }
        
        // 4. 更新主销售关联（如果有员工信息）
        if (isset($updateList['rec_employee_id'])) {
            // 先检查是否已存在主销售记录
            $existingStaff = $connection->createCommand()
                ->select('id')
                ->from('sal_clue_u_staff')
                ->where('clue_id=:clue_id AND employee_type=1', array(':clue_id' => $clueId))
                ->queryScalar();
            
            if ($existingStaff) {
                // 更新现有记录
                $connection->createCommand()->update("sal_clue_u_staff", array(
                    "employee_id" => $updateList['rec_employee_id'],
                    "u_id" => !empty($data['u_staff_id']) ? $data['u_staff_id'] : null,
                    "luu" => $username,
                    "lud" => $req_dt,
                ), "id=:id", array(':id' => $existingStaff));
            } else {
                // 插入新记录
                $connection->createCommand()->insert("sal_clue_u_staff", array(
                    "clue_id" => $clueId,
                    "employee_id" => $updateList['rec_employee_id'],
                    "employee_type" => 1,
                    "u_id" => !empty($data['u_staff_id']) ? $data['u_staff_id'] : null,
                    "lcu" => $username,
                    "lcd" => $req_dt,
                ));
            }
        }
        
        // 5. 更新或插入联系人信息
        if (!empty($updateList['cust_person']) && !empty($updateList['cust_tel'])) {
            // 先检查是否已存在联系人记录（clue_store_id=0 表示客户级联系人）
            $existingPerson = $connection->createCommand()
                ->select('id')
                ->from('sal_clue_person')
                ->where('clue_id=:clue_id AND clue_store_id=0', array(':clue_id' => $clueId))
                ->order('id ASC')
                ->queryScalar();
            
            if ($existingPerson) {
                // 更新现有联系人
                $connection->createCommand()->update("sal_clue_person", array(
                    "cust_person" => $updateList['cust_person'],
                    "cust_tel" => $updateList['cust_tel'],
                    "cust_email" => isset($updateList['cust_email']) ? $updateList['cust_email'] : null,
                    "cust_person_role" => isset($updateList['cust_person_role']) ? $updateList['cust_person_role'] : null,
                    "u_id" => !empty($data['u_person_id']) ? $data['u_person_id'] : null,
                    "luu" => $username,
                    "lud" => $req_dt,
                ), "id=:id", array(':id' => $existingPerson));
            } else {
                // 插入新联系人
                $connection->createCommand()->insert("sal_clue_person", array(
                    "clue_id" => $clueId,
                    "clue_store_id" => 0,
                    "person_code" => isset($data['person_code']) ? $data['person_code'] : null,
                    "cust_person" => $updateList['cust_person'],
                    "cust_tel" => $updateList['cust_tel'],
                    "cust_email" => isset($updateList['cust_email']) ? $updateList['cust_email'] : null,
                    "cust_person_role" => isset($updateList['cust_person_role']) ? $updateList['cust_person_role'] : null,
                    "u_id" => !empty($data['u_person_id']) ? $data['u_person_id'] : null,
                    "lcu" => $username,
                    "lcd" => $req_dt,
                ));
            }
        }
        
        // 6. 处理其它销售（可选，这里不更新，只在插入时处理）
        // 7. 处理其它城市（可选，这里不更新，只在插入时处理）
        
        Yii::log('客户数据更新成功：clue_id=' . $clueId . ', clue_code=' . (isset($data['clue_code']) ? $data['clue_code'] : ''), 'info', 'DataMigration');
        
        return $clueId;
    }

    /**
     * 自动创建客户（用于门店导入时）
     * 参考 ImportClientStoreForm::computeClueID
     *
     * @param array $storeData 门店数据
     * @param CDbConnection $connection 数据库连接
     * @param string $username 操作用户
     * @param string $req_dt 请求时间
     * @param int $reportId 导入任务ID
     * @return int 客户ID (clue_id)
     */
    public static function autoCreateForStore($storeData, $connection, $username, $req_dt, $reportId)
    {
        // 加载拼音扩展
        $phpExcelPath = Yii::getPathOfAlias('ext.pinyin');
        include_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Autoloader.php');
        $pinyin = new Pinyin();

        // 准备客户数据
        $clueData = array(
            'clue_type' => $storeData['clue_type'],
            'service_type' => isset($storeData['service_type']) && !empty($storeData['service_type']) ? $storeData['service_type'] : '[]',
            'cust_name' => $storeData['store_name'],
            'full_name' => !empty($storeData['store_full_name']) ? $storeData['store_full_name'] : $storeData['store_name'],
            'entry_date' => isset($storeData['entry_date']) ? $storeData['entry_date'] : $req_dt,
            'rec_employee_id' => $storeData['create_staff'],
            'yewudalei' => isset($storeData['yewudalei']) ? $storeData['yewudalei'] : null,
            'group_bool' => isset($storeData['group_bool']) && !empty($storeData['group_bool']) ? $storeData['group_bool'] : 'N',
            'cust_vip' => isset($storeData['cust_vip']) && !empty($storeData['cust_vip']) ? $storeData['cust_vip'] : 'N',
            'cust_class' => isset($storeData['cust_class']) ? $storeData['cust_class'] : null,
            'cust_class_group' => isset($storeData['cust_class_group']) ? $storeData['cust_class_group'] : null,
            'city' => $storeData['city'],
            'address' => isset($storeData['address']) ? $storeData['address'] : null,
            'district' => isset($storeData['district']) ? $storeData['district'] : null,
            'latitude' => isset($storeData['latitude']) ? $storeData['latitude'] : null,
            'longitude' => isset($storeData['longitude']) ? $storeData['longitude'] : null,
            'cust_person' => isset($storeData['cust_person']) ? $storeData['cust_person'] : null,
            'cust_tel' => isset($storeData['cust_tel']) ? $storeData['cust_tel'] : null,
            'cust_email' => isset($storeData['cust_email']) ? $storeData['cust_email'] : null,
            'cust_person_role' => isset($storeData['cust_person_role']) ? $storeData['cust_person_role'] : null,
            'cust_address' => isset($storeData['address']) ? $storeData['address'] : null,
            'clue_remark' => isset($storeData['remarks']) ? $storeData['remarks'] : null,
            'rec_type' => 1,  // 1=指定员工（导入的客户都有明确负责人）
            'report_id' => $reportId,
            'table_type' => 2,  // 强制设置为客户（1=线索, 2=客户）
            'lcu' => $username,
            'luu' => $username,
        );

        // 根据门店状态设置客户状态
        // clue_status: 0=待跟进, 30=进行中（服务中）, 40=已暂停, 50=已终止
        // 门店状态: 0=未生效, 1=未服务, 2=服务中, 3=已停止, 4=其他
        $storeStatus = isset($storeData['store_status']) ? $storeData['store_status'] : 2;
        if ($storeStatus == 3) {
            $clueData['clue_status'] = 50;  // 已停止 → 已终止（派单status=2）
        } elseif ($storeStatus == 0) {
            $clueData['clue_status'] = 0;   // 未生效 → 待跟进
        } else {
            $clueData['clue_status'] = 30;  // 服务中/其他 → 进行中（派单status=1）
        }

        // 使用派单提供的客户编号或自动生成
        if (!empty($storeData['clue_code'])) {
            $clueData['clue_code'] = $storeData['clue_code'];
            $full_name = $clueData['full_name'];
            $computeList = CGetName::computeClueCode($pinyin, $full_name);
            $clueData['abbr_code'] = $computeList['abbr_code'];
        } else {
            $full_name = $clueData['full_name'];
            $computeList = CGetName::computeClueCode($pinyin, $full_name);
            $clueData['clue_code'] = $computeList['clue_code'];
            $clueData['abbr_code'] = $computeList['abbr_code'];
        }
        
        //  兼容处理：确保所有字段都是标量值（防止数组转字符串错误）
        self::ensureScalarValues($clueData, 'insert门店关联客户');

        // 插入客户记录
        $connection->createCommand()->insert('sal_clue', $clueData);
        $clue_id = $connection->getLastInsertID();

        // 插入客户历史记录
        $connection->createCommand()->insert('sal_clue_history', array(
            'table_id' => $clue_id,
            'table_type' => 1,
            'history_type' => 1,
            'history_html' => '<span>派单数据导入（门店自动创建客户），导入id：' . $reportId . '</span>',
            'lcu' => $username,
        ));

        // 插入客户城市关联
        $connection->createCommand()->insert('sal_clue_u_area', array(
            'clue_id' => $clue_id,
            'city_code' => $clueData['city'],
            'city_type' => 1,
            'u_id' => null,
            'lcu' => $username,
            'lcd' => $req_dt,
        ));

        // 插入客户销售关联
        $connection->createCommand()->insert('sal_clue_u_staff', array(
            'clue_id' => $clue_id,
            'employee_id' => $clueData['rec_employee_id'],
            'employee_type' => 1,
            'u_id' => null,
            'lcu' => $username,
            'lcd' => $req_dt,
        ));

        // 插入客户联系人
        if (!empty($clueData['cust_person']) && !empty($clueData['cust_tel'])) {
            $connection->createCommand()->insert('sal_clue_person', array(
                'clue_id' => $clue_id,
                'clue_store_id' => 0,
                'cust_person' => $clueData['cust_person'],
                'cust_tel' => $clueData['cust_tel'],
                'cust_email' => isset($clueData['cust_email']) ? $clueData['cust_email'] : null,
                'cust_person_role' => isset($clueData['cust_person_role']) ? $clueData['cust_person_role'] : null,
                'u_id' => null,
                'u_group_id' => null,
                'lcu' => $username,
                'lcd' => $req_dt,
            ));
            $cust_id = $connection->getLastInsertID();
            $connection->createCommand()->update('sal_clue_person', array(
                'person_code' => ClientPersonForm::computeCodeX($clue_id, 0, $cust_id),
            ), 'id=:id', array(':id' => $cust_id));
        }

        Yii::log('自动创建客户成功：clue_id=' . $clue_id . ', clue_code=' . $clueData['clue_code'] . ', cust_name=' . $clueData['cust_name'], 'info', 'DataMigration');

        return $clue_id;
    }
    
    /**
     * 获取业务大类名称（用于日志和错误提示）
     * 
     * @param int $yewudaleiId 业务大类ID
     * @param CDbConnection $connection 数据库连接
     * @return string 业务大类名称
     */
    protected static function getYewudaleiName($yewudaleiId, $connection)
    {
        if (empty($yewudaleiId)) {
            return '未知';
        }
        
        $row = $connection->createCommand()
            ->select('name')
            ->from('sal_yewudalei')
            ->where('id=:id', array(':id' => $yewudaleiId))
            ->queryRow();
        
        return $row ? $row['name'] : "ID:{$yewudaleiId}";
    }
    
    /**
     * 获取行业类别名称（用于日志和错误提示）
     * 
     * @param int $custClassId 行业类别ID
     * @param CDbConnection $connection 数据库连接
     * @return string 行业类别名称
     */
    protected static function getCustClassName($custClassId, $connection)
    {
        if (empty($custClassId)) {
            return '';
        }
        
        $suffix = Yii::app()->params['envSuffix'];
        $row = $connection->createCommand()
            ->select('name')
            ->from("swoper{$suffix}.swo_nature_type")
            ->where('id=:id', array(':id' => $custClassId))
            ->queryRow();
        
        return $row ? $row['name'] : '';
    }
}

