<?php

/**
 * 数据迁移辅助工具类
 * 提供查询、转换、缓存等辅助功能
 */
class DataMigrationHelper
{
    // 缓存：员工编号 => 员工ID（避免重复查询）
    protected static $employeeCache = array();
    
    // 缓存：业务大类名称 => ID（避免重复查询）
    protected static $yewudaleiCache = array();
    
    // 缓存：城市名称 => 城市代码（避免重复查询）
    protected static $cityCodeCache = array();
    
    // 缓存：主体公司名称 => ID（避免重复查询）
    protected static $lbsMainCache = array();
    
    // 缓存：服务项目名称 => 项目信息（避免重复查询）
    protected static $serviceTypeCache = array();
    
    // 缓存：行业类别名称 => 类别信息（避免重复查询）
    protected static $custClassCache = array();
    
    // 缓存：办事处名称+城市 => 办事处ID（避免重复查询）
    protected static $officeCache = array();
    
    // 缓存：区域名称 => 区域信息（避免重复查询）
    protected static $districtCache = array();
    
    // 缓存：服务类型名称 => 服务类型ID（避免重复查询，用于swo_customer_type）
    protected static $customerTypeCache = array();
    
    // 缓存：枚举类型数据（避免每次调用 CGetName 方法）
    protected static $enumCache = array();
    
    // 缓存：停止原因（按"类型_名称"索引）
    protected static $stopReasonCache = array();
    
    /**
     * 派单系统到CRM系统的服务项目名称映射表
     * 用于兼容两个系统中服务项目名称不一致的情况
     */
    private static $serviceNameMapping = array(
        // 派单系统 => CRM系统
        '租机服务' => '租赁机器',
        '蝇灯服务' => '灭蝇灯服务',
        '鼠臭跟进' => '臭虫跟进',
        '租机服务 (水机)' => '洁净水租机',
        '厨房油烟清洁服务' => '油烟清洗',
        '灭虫（一次性服务）' => '灭虫（一次性）',
        '洁净（一次性服务）' => '洁净（一次性）',
    );
    
    /**
     * 获取当前用户ID（兼容Web和Console环境）
     */
    public static function getCurrentUserId($username = null)
    {
        if (Yii::app() instanceof CWebApplication && !Yii::app()->user->isGuest) {
            return Yii::app()->user->id;
        }
        // Console环境：默认'system'
        return empty($username) ? 'system' : $username;
    }
    
    /**
     * 根据员工编号获取员工ID（带缓存）
     */
    public static function getEmployeeIdByCode($code, $connection)
    {
        if (empty($code)) {
            return null;
        }
        
        // 检查缓存
        if (isset(self::$employeeCache[$code])) {
            return self::$employeeCache[$code];
        }
        
        // 查询数据库，将员工编号转换为员工ID
        // 注意：使用环境后缀区分UAT和生产环境
        $suffix = Yii::app()->params['envSuffix'];
        $empId = $connection->createCommand()
            ->select('id')
            ->from("hr{$suffix}.hr_employee")
            ->where('code=:code', array(':code' => $code))
            ->order('del_num asc, table_type asc, staff_status desc')
            ->queryScalar();
        
        // 缓存结果（包括null值，避免重复查询不存在的员工）
        self::$employeeCache[$code] = $empId;
        
        return $empId;
    }
    
    /**
     * 根据员工编号获取员工完整信息（包括 id, name, code）
     * 用于需要员工姓名的场景（如负责技术员列表）
     * 
     * @param string $code 员工编号
     * @param CDbConnection $connection 数据库连接
     * @return array|null 员工信息数组（包含 id, name, code），不存在返回 null
     */
    public static function getEmployeeByCode($code, $connection)
    {
        if (empty($code)) {
            return null;
        }
        
        // 检查缓存（使用特殊键名以区分 getEmployeeIdByCode 的缓存）
        $cacheKey = 'full_' . $code;
        if (isset(self::$employeeCache[$cacheKey])) {
            return self::$employeeCache[$cacheKey];
        }
        
        // 注意：使用环境后缀区分UAT和生产环境
        $suffix = Yii::app()->params['envSuffix'];
        $empRow = $connection->createCommand()
            ->select('id, name, code')
            ->from("hr{$suffix}.hr_employee")
            ->where('code=:code', array(':code' => $code))
            ->order('del_num asc, table_type asc, staff_status desc')
            ->queryRow();
        
        // 缓存结果
        $result = $empRow ? $empRow : null;
        self::$employeeCache[$cacheKey] = $result;
        
        return $result;
    }
    
    /**
     * 获取或验证城市代码（带缓存）
     * 注意：派单系统导出的应该已经是标准城市代码（如 SZ, BJ 等）
     */
    public static function getCityCodeByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 清理城市名称：去除所有空格和特殊字符
        $originalName = $name;
        $name = trim($name);
        $name = preg_replace('/\s+/', '', $name);  // 去除所有空白字符
        
        if (empty($name)) {
            return null;
        }
        
        // 检查缓存（使用清理后的名称）
        if (isset(self::$cityCodeCache[$name])) {
            return self::$cityCodeCache[$name];
        }
        
        // 如果已经是城市代码格式（2-3个大写字母），直接返回并缓存
        if (preg_match('/^[A-Z]{2,3}$/', $name)) {
            self::$cityCodeCache[$name] = $name;
            self::$cityCodeCache[$originalName] = $name;  // 同时缓存原始名称
            return $name;
        }
        
        // 如果是小写，转为大写再检查
        $nameUpper = strtoupper($name);
        if (preg_match('/^[A-Z]{2,3}$/', $nameUpper)) {
            self::$cityCodeCache[$name] = $nameUpper;
            self::$cityCodeCache[$originalName] = $nameUpper;
            return $nameUpper;
        }
        
        // 如果仍然不是代码格式，查询数据库
        $suffix = Yii::app()->params['envSuffix'];
        
        // 优先使用 code 匹配
        $codeResult = $connection->createCommand()
            ->select('code')
            ->from("security{$suffix}.sec_city")
            ->where('code=:code', array(':code' => $nameUpper))
            ->queryScalar();
        if ($codeResult) {
            self::$cityCodeCache[$name] = $codeResult;
            self::$cityCodeCache[$originalName] = $codeResult;
            return $codeResult;
        }
        
        // 尝试按清理后的名字查询
        $result = $connection->createCommand()
            ->select('code')
            ->from("security{$suffix}.sec_city")
            ->where('name=:name', array(':name' => $name))
            ->queryScalar();
        
        // 如果还是找不到，尝试按原始名称查询（处理数据库中也有空格的情况）
        if (!$result && $name !== $originalName) {
            $result = $connection->createCommand()
                ->select('code')
                ->from("security{$suffix}.sec_city")
                ->where('name=:name', array(':name' => $originalName))
                ->queryScalar();
        }
        
        // 如果仍然找不到，尝试使用REPLACE去除数据库中的空格进行模糊匹配
        if (!$result) {
            $result = $connection->createCommand()
                ->select('code')
                ->from("security{$suffix}.sec_city")
                ->where('REPLACE(name, " ", "")=:name', array(':name' => $name))
                ->queryScalar();
            
            if ($result) {
                Yii::log("通过去除空格匹配到城市 - 输入: '{$originalName}', 清理后: '{$name}', 代码: {$result}", 'info', 'DataMigration');
            }
        }
        
        // 缓存结果
        self::$cityCodeCache[$name] = $result ?: null;
        self::$cityCodeCache[$originalName] = $result ?: null;
        
        return self::$cityCodeCache[$name];
    }
    
    /**
     * 根据业务大类名称获取ID（带缓存）
     */
    public static function getYewudaleiIdByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 检查缓存
        if (isset(self::$yewudaleiCache[$name])) {
            return self::$yewudaleiCache[$name];
        }
        
        // 从 sal_yewudalei 表查询
        $id = $connection->createCommand()
            ->select('id')
            ->from('sal_yewudalei')
            ->where('name=:name', array(':name' => $name))
            ->queryScalar();
        
        // 缓存结果
        self::$yewudaleiCache[$name] = $id ?: null;
        
        return self::$yewudaleiCache[$name];
    }
    
    /**
     * 根据办公室代码（office_code）从 show_city 字段查找主体公司ID（带缓存）
     * 
     * @param string $officeCode 派单系统的办公室代码（如 GZ, SH, GZMJ）
     * @param CDbConnection $connection 数据库连接
     * @return int|null 主体公司ID
     */
    public static function getLbsMainIdByOfficeCode($officeCode, $connection)
    {
        if (empty($officeCode)) {
            return null;
        }
        
        // 缓存key使用 office_code
        $cacheKey = 'office_' . $officeCode;
        
        // 检查缓存
        if (isset(self::$lbsMainCache[$cacheKey])) {
            return self::$lbsMainCache[$cacheKey];
        }
        
        // 从 sal_main_lbs 表查询，匹配 show_city 字段中包含该办公室代码的记录
        // show_city 格式如：'SHKA,SHMS,DGKA,ZSKA,FSKA,...'
        $id = $connection->createCommand()
            ->select('id')
            ->from('sal_main_lbs')
            ->where('FIND_IN_SET(:office_code, show_city) > 0', array(':office_code' => $officeCode))
            ->order('z_display DESC, id DESC')  // 优先显示启用的，ID大的（最新的）
            ->limit(1)
            ->queryScalar();
        
        // 缓存结果
        self::$lbsMainCache[$cacheKey] = $id ?: null;
        
        return self::$lbsMainCache[$cacheKey];
    }
    
    /**
     * 根据主体编码（entity_code）获取主体公司ID（带缓存）
     * ⚠️ 注意：此方法仅用于精确匹配 mh_code，建议优先使用 getLbsMainIdByOfficeCode
     * 
     * @param string $entityCode 派单系统的主体编码（如 IC-GZ, IC-SH）
     * @param CDbConnection $connection 数据库连接
     * @return int|null 主体公司ID
     */
    public static function getLbsMainIdByEntityCode($entityCode, $connection)
    {
        if (empty($entityCode)) {
            return null;
        }
        
        // 检查缓存
        if (isset(self::$lbsMainCache[$entityCode])) {
            return self::$lbsMainCache[$entityCode];
        }
        
        // 从 sal_main_lbs 表查询，使用 mh_code 字段匹配派单的 entity_code
        $id = $connection->createCommand()
            ->select('id')
            ->from('sal_main_lbs')
            ->where('mh_code=:mh_code', array(':mh_code' => $entityCode))
            ->queryScalar();
        
        // 缓存结果
        self::$lbsMainCache[$entityCode] = $id ?: null;
        
        return self::$lbsMainCache[$entityCode];
    }
    
    /**
     * 兼容旧方法：根据名称查找
     * @deprecated 请使用 getLbsMainIdByOfficeCode 或 getLbsMainIdByEntityCode
     */
    public static function getLbsMainIdByName($name, $connection)
    {
        // 先尝试作为 office_code 查找（推荐）
        $id = self::getLbsMainIdByOfficeCode($name, $connection);
        
        // 如果找不到，再尝试作为 entity_code 查找
        if (empty($id)) {
            $id = self::getLbsMainIdByEntityCode($name, $connection);
        }
        
        return $id;
    }
    
    /**
     * 从派单系统的主体信息中查找主体公司（找不到时自动添加到show_city）
     * 
     * @param array $paidanEntityInfo 派单系统的主体信息数组（包含 office_code, entity_code）
     * @param CDbConnection $connection 数据库连接
     * @return int|null 主体公司ID，找不到返回 null
     */
    public static function getLbsMainFromPaidanData($paidanEntityInfo, $connection)
    {
        if (empty($paidanEntityInfo)) {
            return null;
        }
        
        $officeCode = isset($paidanEntityInfo['office_code']) ? $paidanEntityInfo['office_code'] : null;
        $entityCode = isset($paidanEntityInfo['entity_code']) ? $paidanEntityInfo['entity_code'] : null;
        
        // 优先：根据办公室代码在 show_city 中查找
        if (!empty($officeCode)) {
            $id = self::getLbsMainIdByOfficeCode($officeCode, $connection);
            if (!empty($id)) {
                return $id;
            }
        }
        
        // 如果在 show_city 中找不到，尝试根据 entity_code 查找主体公司
        if (!empty($entityCode)) {
            $id = self::getLbsMainIdByEntityCode($entityCode, $connection);
            if (!empty($id)) {
                // 找到了主体公司，但 show_city 中没有该办公室代码
                // 自动将办公室代码添加到 show_city 字段中
                if (!empty($officeCode)) {
                    self::addOfficeCodeToShowCity($id, $officeCode, $connection);
                }
                return $id;
            }
        }
        
        // 都找不到时记录日志
        Yii::log(
            "找不到对应的主体公司 - office_code: {$officeCode}, entity_code: {$entityCode}。" .
            "请在 sal_main_lbs 表中先配置该主体公司（mh_code字段）。",
            'warning',
            'DataMigration'
        );
        
        return null;
    }
    
    /**
     * 将办公室代码添加到主体公司的 show_city 字段中
     * 
     * @param int $lbsMainId 主体公司ID
     * @param string $officeCode 办公室代码
     * @param CDbConnection $connection 数据库连接
     */
    protected static function addOfficeCodeToShowCity($lbsMainId, $officeCode, $connection)
    {
        if (empty($lbsMainId) || empty($officeCode)) {
            return;
        }
        
        // 查询当前的 show_city 值
        $currentShowCity = $connection->createCommand()
            ->select('show_city, name')
            ->from('sal_main_lbs')
            ->where('id=:id', array(':id' => $lbsMainId))
            ->queryRow();
        
        if (!$currentShowCity) {
            return;
        }
        
        $showCityValue = isset($currentShowCity['show_city']) ? $currentShowCity['show_city'] : '';
        $companyName = isset($currentShowCity['name']) ? $currentShowCity['name'] : '';
        
        // ✅ 解析现有城市列表（过滤空值）
        $existingCities = !empty($showCityValue) ? explode(',', $showCityValue) : array();
        $existingCities = array_filter($existingCities, function($city) {
            return !empty(trim($city)); // 过滤空字符串
        });
        
        // 检查是否已存在（避免重复添加）
        if (in_array($officeCode, $existingCities)) {
            return; // 已存在，无需添加
        }
        
        // ✅ 追加新代码（在原有基础上添加，不覆盖）
        $existingCities[] = $officeCode;
        $newShowCity = implode(',', $existingCities);
        
        // 更新数据库
        $connection->createCommand()->update(
            'sal_main_lbs',
            array(
                'show_city' => $newShowCity,
                'luu' => self::getCurrentUserId(),
            ),
            'id=:id',
            array(':id' => $lbsMainId)
        );
        
        Yii::log(
            "自动添加办公室代码到主体公司 - ID: {$lbsMainId}, 公司: {$companyName}, " .
            "添加代码: {$officeCode}, 更新后的show_city: {$newShowCity}",
            'info',
            'DataMigration'
        );
    }
    
    /**
     * ⚠️ 已废弃：不再自动创建主体公司
     * @deprecated 主体公司必须预先在 CRM 系统中配置，不支持自动创建
     */
    public static function getOrCreateLbsMainFromPaidanData($paidanEntityInfo, $connection)
    {
        // 只查找，不创建
        return self::getLbsMainFromPaidanData($paidanEntityInfo, $connection);
    }
    
    /**
     * ⚠️ 已废弃：不再自动创建主体公司
     * @deprecated 主体公司必须预先在 CRM 系统中配置
     */
    public static function createLbsMain($entityCode, $connection, $cityCode = null, $companyName = null)
    {
        Yii::log(
            "createLbsMain() 方法已废弃。主体公司必须预先在 sal_main_lbs 表中配置，" .
            "请联系管理员添加：entity_code={$entityCode}, office_code={$cityCode}",
            'error',
            'DataMigration'
        );
        return null;
    }
    
    /**
     * 根据服务项目名称获取信息（带缓存和映射）
     */
    public static function getServiceTypeByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 检查缓存
        if (isset(self::$serviceTypeCache[$name])) {
            return self::$serviceTypeCache[$name];
        }
        
        // 从 sal_service_type 表查询信息
        $row = $connection->createCommand()
            ->select('id, id_char, service_type, name')
            ->from('sal_service_type')
            ->where('name=:name', array(':name' => $name))
            ->queryRow();
        
        // ✅ 如果直接查询失败，尝试使用映射表转换后再查询
        if (!$row && isset(self::$serviceNameMapping[$name])) {
            $mappedName = self::$serviceNameMapping[$name];
            Yii::log("服务项目名称映射：'{$name}' => '{$mappedName}'", 'info', 'DataMigration');
            
            $row = $connection->createCommand()
                ->select('id, id_char, service_type, name')
                ->from('sal_service_type')
                ->where('name=:name', array(':name' => $mappedName))
                ->queryRow();
        }
        
        // 缓存结果（使用原始名称作为key）
        self::$serviceTypeCache[$name] = $row;
        
        return $row;
    }
    
    /**
     * 根据派单系统的服务项目信息查找CRM的服务项目信息（sal_service_type_info）
     * 
     * CRM服务项目类型（input_type）包括：
     * - method: 服务方法（如：安装、清洁、维保、滞留喷洒）
     * - device: 设备（如：皂液机、厕纸机、灭蝇灯）
     * - pest: 害虫（如：鼠、蟑螂、蚊）
     * - ware: 设施（如：坐厕、洗手盆、尿缸）
     * - text: 文本输入（如：服务面积）
     * - yearAmount: 年金额
     * - remark: 备注
     * 
     * @param array $paidanServiceItem 派单系统服务项目 ['u_code' => xxx, 'u_type' => xxx, 'name' => xxx]
     * @param CDbConnection $connection 数据库连接
     * @return array|null CRM服务项目信息，包含 input_type 等字段
     */
    public static function getServiceTypeInfoFromPaidan($paidanServiceItem, $connection)
    {
        if (empty($paidanServiceItem)) {
            return null;
        }
        
        // 支持新的字段名（service_item_code, service_item_class）和旧的字段名（u_code, u_type）
        $uCode = null;
        $uType = null;
        $itemName = isset($paidanServiceItem['name']) ? $paidanServiceItem['name'] : 
                   (isset($paidanServiceItem['service_item_name']) ? $paidanServiceItem['service_item_name'] : null);
        
        // 优先使用新字段名
        if (isset($paidanServiceItem['service_item_code'])) {
            $uCode = (string)$paidanServiceItem['service_item_code']; // 转为字符串
        } elseif (isset($paidanServiceItem['u_code'])) {
            $uCode = $paidanServiceItem['u_code'];
        }
        
        if (isset($paidanServiceItem['service_item_class'])) {
            $uType = intval($paidanServiceItem['service_item_class']);
        } elseif (isset($paidanServiceItem['u_type'])) {
            $uType = $paidanServiceItem['u_type'];
        }
        
        // 优先：根据 u_code 和 u_type 精确匹配
        if (!empty($uCode) && !empty($uType)) {
            $cacheKey = "service_{$uCode}_{$uType}";
            
            // 检查缓存
            if (isset(self::$serviceTypeCache[$cacheKey])) {
                return self::$serviceTypeCache[$cacheKey];
            }
            
            // 从 sal_service_type_info 表查询
            $row = $connection->createCommand()
                ->select('id, type_id, id_char, name, u_code, u_type, input_type, is_device, z_display')
                ->from('sal_service_type_info')
                ->where('u_code=:u_code AND u_type=:u_type AND z_display=1', array(
                    ':u_code' => $uCode,
                    ':u_type' => $uType
                ))
                ->queryRow();
            
            if ($row) {
                // 缓存结果
                self::$serviceTypeCache[$cacheKey] = $row;
                
                // 记录服务项目类型信息
                Yii::log(
                    "匹配服务项目 - u_code: {$uCode}, 名称: {$row['name']}, " .
                    "类型: {$row['input_type']} (method/device/pest/ware等)",
                    'info',
                    'DataMigration'
                );
                
                return $row;
            }
        }
        
        // 后备1：如果提供了class，尝试通过input_type + 名称匹配
        $itemClass = isset($paidanServiceItem['service_item_class']) ? $paidanServiceItem['service_item_class'] : 
                    (isset($paidanServiceItem['class']) ? $paidanServiceItem['class'] : null);
        
        if (!empty($itemName) && $itemClass !== null) {
            $inputType = self::getInputTypeByPaidanClass($itemClass, $itemName);
            
            $row = $connection->createCommand()
                ->select('id, type_id, id_char, name, u_code, u_type, input_type, is_device')
                ->from('sal_service_type_info')
                ->where('name=:name AND input_type=:input_type AND z_display=1', array(
                    ':name' => $itemName,
                    ':input_type' => $inputType
                ))
                ->queryRow();
            
            if ($row) {
                Yii::log(
                    "通过名称和input_type匹配服务项目 - 名称: {$itemName}, input_type: {$inputType}, " .
                    "class: {$itemClass}。建议在 sal_service_type_info 中配置 u_code={$uCode} 和 u_type={$itemClass}。",
                    'warning',
                    'DataMigration'
                );
                return $row;
            }
        }
        
        // 后备2：仅根据名称模糊匹配（最不推荐）
        if (!empty($itemName)) {
            $row = $connection->createCommand()
                ->select('id, type_id, id_char, name, u_code, u_type, input_type, is_device')
                ->from('sal_service_type_info')
                ->where('name=:name AND z_display=1', array(':name' => $itemName))
                ->queryRow();
            
            if ($row) {
                Yii::log(
                    "仅通过名称匹配服务项目 - 名称: {$itemName}, 类型: {$row['input_type']}。" .
                    "强烈建议在 sal_service_type_info 中配置 u_code 和 u_type 进行精确匹配。",
                    'warning',
                    'DataMigration'
                );
                return $row;
            }
        }
        
        // 找不到时记录日志
        $classInfo = $itemClass !== null ? ", class: {$itemClass}" : "";
        Yii::log(
            "找不到对应的服务项目 - u_code: {$uCode}, u_type: {$uType}{$classInfo}, 名称: {$itemName}。" .
            "请在 sal_service_type_info 表中配置该服务项目的 u_code 和 u_type。",
            'warning',
            'DataMigration'
        );
        
        return null;
    }
    
    /**
     * 根据派单服务项目信息查找CRM服务项目ID
     * 
     * @param string $uCode 派单系统服务项目编码
     * @param int $uType 派单系统服务项目类型
     * @param CDbConnection $connection 数据库连接
     * @return int|null CRM服务项目ID
     */
    public static function getServiceTypeInfoIdByPaidan($uCode, $uType, $connection)
    {
        $info = self::getServiceTypeInfoFromPaidan(
            array('u_code' => $uCode, 'u_type' => $uType),
            $connection
        );
        
        return $info ? intval($info['id']) : null;
    }
    
    /**
     * 根据派单服务项目的class值推断CRM的input_type
     * 
     * @param int $class 派单系统的分类 (0=项目, 1=处理方式, 2=设备)
     * @param string $itemName 服务项目名称（当class=0时需要）
     * @return string CRM的input_type (method/device/pest/ware/text)
     */
    public static function getInputTypeByPaidanClass($class, $itemName = '')
    {
        // class=1: 处理方式 → method
        if ($class == 1) {
            return 'method';
        }
        
        // class=2: 设备 → device
        if ($class == 2) {
            return 'device';
        }
        
        // class=0: 项目，需要根据名称进一步判断
        if ($class == 0) {
            // 害虫关键词（class=0，type=3的项目）
            $pestKeywords = array(
                '老鼠', '鼠', '蚁', '蚂蚁', '红火蚁', '蟑螂', '蜚蠊',
                '蚊子', '蚊', '苍蝇', '果蝇', '蝇',
                '跳蚤', '蛀虫', '白蚁', '螨虫', '臭虫', '虫',
                '千足虫', '蜈蚣', '衣蛾', '虱子', '蠓',
                '仓储飞蛾', '仓储甲虫', '蛇', '鸟', '园林绿化害虫',
                '滞留', '焗雾', '勾枪', '空间消毒'
            );
            
            // 设施关键词（class=0，type=1或部分设施相关的项目）
            $wareKeywords = array(
                '坐厕', '尿缸', '洗手盆', '蹲厕', 
                '隔油箱', '隔油池', '净水机', 
                '洗手间', '小便池', '二次供水箱'
            );
            
            // 设备/机器关键词（class=0，type=4的设备类项目）
            $deviceKeywords = array(
                '洗地机', '租赁', '风扇机', '喷机', '香罐', 
                '饮水机', '滤芯', '香薰机', '芳香机'
            );
            
            // 判断是否是设备（优先级最高，避免"喷机"被误判为害虫）
            foreach ($deviceKeywords as $keyword) {
                if (strpos($itemName, $keyword) !== false) {
                    return 'device';
                }
            }
            
            // 判断是否是害虫
            foreach ($pestKeywords as $keyword) {
                if (strpos($itemName, $keyword) !== false) {
                    return 'pest';
                }
            }
            
            // 判断是否是设施
            foreach ($wareKeywords as $keyword) {
                if (strpos($itemName, $keyword) !== false) {
                    return 'ware';
                }
            }
            
            // 默认返回text
            return 'text';
        }
        
        // 其他情况默认返回text
        return 'text';
    }
    
    /**
     * 根据 input_type 过滤服务项目
     * 用于区分不同类型的服务项目：method（方法）、device（设备）、pest（害虫）等
     * 
     * @param string $inputType 服务项目类型：method, device, pest, ware 等
     * @param int $typeId 服务大类ID（可选）
     * @param CDbConnection $connection 数据库连接
     * @return array 服务项目列表
     */
    public static function getServiceTypeInfoByInputType($inputType, $typeId = null, $connection)
    {
        $where = 'input_type=:input_type AND z_display=1';
        $params = array(':input_type' => $inputType);
        
        if (!empty($typeId)) {
            $where .= ' AND type_id=:type_id';
            $params[':type_id'] = $typeId;
        }
        
        $rows = $connection->createCommand()
            ->select('id, type_id, id_char, name, u_code, u_type, input_type, is_device')
            ->from('sal_service_type_info')
            ->where($where, $params)
            ->order('type_id ASC, z_index ASC')
            ->queryAll();
        
        return $rows;
    }
    
    /**
     * 根据行业类别名称获取信息（带缓存）
     */
    public static function getCustClassByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 如果已经是数字ID，直接返回
        if (is_numeric($name)) {
            return array('id' => intval($name), 'nature_id' => null);
        }
        
        // 检查缓存
        if (isset(self::$custClassCache[$name])) {
            return self::$custClassCache[$name];
        }
        
        // 从 swo_nature_type 表查询信息
        $suffix = Yii::app()->params['envSuffix'];
        $row = $connection->createCommand()
            ->select('a.id, a.nature_id')
            ->from("swoper{$suffix}.swo_nature_type a")
            ->where('a.name=:name', array(':name' => $name))
            ->order('z_display desc, id desc')
            ->queryRow();
        
        // 缓存结果
        self::$custClassCache[$name] = $row;
        
        return $row;
    }
    
    /**
     * 计算合约月数
     */
    public static function computeMonthLen($startDate, $endDate)
    {
        if (empty($startDate) || empty($endDate)) {
            return 0;
        }
        
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        
        if ($start === false || $end === false || $start > $end) {
            return 0;
        }
        
        $diff = $end - $start;
        return ceil($diff / (30 * 24 * 3600)); // 按30天一个月计算
    }
    
    /**
     * 根据合约状态获取pro_type
     */
    public static function proTypeByStatus($status)
    {
        $statusMap = array(
            30 => 'BIZ',  // 生效中
            40 => 'SUSP', // 暂停
            50 => 'TERM', // 终止
        );
        return isset($statusMap[$status]) ? $statusMap[$status] : 'BIZ';
    }
    
    /**
     * 获取客户状态
     */
    public static function getClientStatusByClueID($clue_id, $connection = null)
    {
        if (empty($clue_id)) {
            return 1; // 默认状态
        }
        
        if ($connection === null) {
            $connection = Yii::app()->db;
        }
        
        // 查询客户是否有合约
        $hasContract = $connection->createCommand()
            ->select('count(*)')
            ->from('sal_contract')
            ->where('clue_id=:clue_id', array(':clue_id' => $clue_id))
            ->queryScalar();
        
        return $hasContract > 0 ? 7 : 1; // 7=已签约, 1=线索
    }
    
    /**
     * 获取门店状态
     */
    public static function getStoreStatusByStoreID($store_id, $connection = null)
    {
        if (empty($store_id)) {
            return 1; // 默认状态：未服务
        }
        
        if ($connection === null) {
            $connection = Yii::app()->db;
        }
        
        // ✅ 修复：根据虚拟合约状态计算门店状态（参考 ClueVirProModel::getStoreStatusByStoreID）
        // 查询该门店的所有虚拟合约状态（只查询有效状态：10=待生效, 30=生效中, 40=已暂停, 50=已终止）
        $statusRow = $connection->createCommand()
            ->select('MIN(vir_status) as min_status')
            ->from('sal_contract_virtual')
            ->where('clue_store_id=:store_id AND vir_status IN (10,30,40,50)', array(':store_id' => $store_id))
            ->queryRow();
        
        $status = 1; // 默认：未服务
        
        if ($statusRow && !empty($statusRow['min_status'])) {
            // 将虚拟合约状态映射为门店状态
            // 虚拟合约状态：10=待生效, 30=生效中, 40=已暂停, 50=已终止
            // 门店状态：0=未生效, 1=未服务, 2=服务中, 3=已停止, 4=其他, 10=服务中, 30=服务中, 40=已暂停, 50=已终止
            $virStatus = intval($statusRow['min_status']);
            switch($virStatus) {
                case 10:  // 待生效
                    $status = 1;  // 未服务
                    break;
                case 30:  // 生效中
                    $status = 2;  // 服务中
                    break;
                case 40:  // 已暂停
                    $status = 3;  // 已停止（暂停也算停止）
                    break;
                case 50:  // 已终止
                    $status = 3;  // 已停止
                    break;
                default:
                    Yii::log("门店{$store_id}的虚拟合约状态异常: {$virStatus}，已设置为默认值1(未服务)", 'warning', 'DataMigrationHelper');
                    $status = 1;  // 默认未服务
            }
        }
        
        return $status;
    }
    
    /**
     * 根据服务类型名称获取ID（swo_customer_type表，带缓存）
     */
    public static function getCustomerTypeIdByName($name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 检查缓存
        if (isset(self::$customerTypeCache[$name])) {
            return self::$customerTypeCache[$name];
        }
        
        // 查询数据库 - 优先使用 rpt_cat 字段（服务类型代码，如 IA、IB）
        $suffix = Yii::app()->params['envSuffix'];
        
        // 方案1：使用 rpt_cat 字段查询（推荐，用于 IA、IB 等代码）
        $row = $connection->createCommand()
            ->select('id')
            ->from("swoper{$suffix}.swo_customer_type")
            ->where('rpt_cat=:name', array(':name' => $name))
            ->queryRow();
        
        // 方案2：如果没找到，尝试用 description 查询（用于完整名称，如 "IA客户"）
        if (!$row) {
            $row = $connection->createCommand()
                ->select('id')
                ->from("swoper{$suffix}.swo_customer_type")
                ->where('description=:name', array(':name' => $name))
                ->queryRow();
        }
        
        // 缓存结果
        $id = $row ? intval($row['id']) : null;
        self::$customerTypeCache[$name] = $id;
        
        return $id;
    }
    
    
    /**
     * 根据办事处名称和城市获取ID（带缓存）
     */
    public static function getOfficeIdByNameAndCity($name, $city, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        // 缓存key包含办事处名称和城市
        $cacheKey = $name . '|' . $city;
        
        // 检查缓存
        if (isset(self::$officeCache[$cacheKey])) {
            return self::$officeCache[$cacheKey];
        }
        
        // 查询数据库
        $suffix = Yii::app()->params['envSuffix'];
        $row = $connection->createCommand()
            ->select('id')
            ->from("hr{$suffix}.hr_office")
            ->where('name=:name AND city=:city', array(':name' => $name, ':city' => $city))
            ->queryRow();
        
        // 缓存结果（0表示不存在）
        $id = $row ? intval($row['id']) : 0;
        self::$officeCache[$cacheKey] = $id;
        
        return $id;
    }
    
    /**
     * 根据区域名称和城市获取区域信息（带缓存）
     */
    public static function getDistrictByName($districtName, $cityName, $connection)
    {
        if (empty($districtName)) {
            return null;
        }
        
        // 缓存key包含区域名称和城市
        $cacheKey = $districtName . '|' . $cityName;
        
        // 检查缓存
        if (isset(self::$districtCache[$cacheKey])) {
            return self::$districtCache[$cacheKey];
        }
        
        // 查询数据库（完全参考原有逻辑）
        $districtName = str_replace("'", "\\'", $districtName);
        $row = $connection->createCommand()
            ->select("id, tree_names,
                (CASE 
                    WHEN area_name='{$districtName}' THEN 10
                    ELSE 0
                END) as order_one,
                (CASE 
                    WHEN tree_names LIKE '%{$cityName}%' AND area_name LIKE '%{$districtName}%' THEN 9
                    WHEN tree_names LIKE '%{$cityName}%' THEN 8
                    ELSE 0
                END) as order_num")
            ->from('sal_national_area')
            ->where("area_name LIKE '%{$districtName}%'")
            ->order('order_one desc, order_num desc, id desc') 
            ->queryRow();
        
        // 缓存结果
        self::$districtCache[$cacheKey] = $row;
        
        return $row;
    }
    
    /**
     * 清空所有缓存
     */
    public static function clearAllCaches()
    {
        self::$employeeCache = array();
        self::$yewudaleiCache = array();
        self::$cityCodeCache = array();
        self::$lbsMainCache = array();
        self::$serviceTypeCache = array();
        self::$custClassCache = array();
        self::$officeCache = array();
        self::$districtCache = array();
        self::$customerTypeCache = array();
        self::$enumCache = array();
        self::$stopReasonCache = array();
        
        Yii::log('已清空所有数据迁移缓存', 'info', 'DataMigration');
    }
    
    /**
     * 获取枚举类型数据（带缓存）
     * 用于获取各种下拉列表数据（费用类型、账单日、结算方式等）
     * 
     * @param string $type 枚举类型名称（如 'fee_type', 'bill_day' 等）
     * @return array 枚举数据数组（ID => 名称）
     */
    public static function getEnumData($type)
    {
        // 检查缓存
        if (isset(self::$enumCache[$type])) {
            return self::$enumCache[$type];
        }
        
        // 根据类型调用对应的 CGetName 方法
        $data = array();
        switch ($type) {
            case 'fee_type':
                $data = CGetName::getFeeTypeList();
                break;
            case 'bill_day':
                $data = CGetName::getBillDayList();
                break;
            case 'settle_type':
                $data = CGetName::getSettleTypeList();
                break;
            case 'pay_week':
                $data = CGetName::getPayWeekList();
                break;
            case 'receivable_day':
                $data = CGetName::getReceivableDayList();
                break;
            case 'pay_type':
                $data = CGetName::getPayTypeList();
                break;
            default:
                Yii::log("未知的枚举类型: {$type}", 'warning', 'DataMigration');
                return array();
        }
        
        // 缓存结果
        self::$enumCache[$type] = $data;
        
        return $data;
    }
    
    /**
     * 根据名称获取枚举值ID（严格匹配，带缓存）
     * 
     * @param string $type 枚举类型名称
     * @param string $name 枚举值名称
     * @return int|null 枚举值ID，找不到返回 null
     */
    public static function getEnumIdByName($type, $name)
    {
        if (empty($name)) {
            return null;
        }
        
        $list = self::getEnumData($type);
        $name = trim($name);
        $key = array_search($name, $list);
        
        return $key !== false ? $key : null;
    }
    
    /**
     * 获取停止原因数据（带缓存）
     * 
     * @param CDbConnection $connection 数据库连接
     * @return array 停止原因数据数组（"类型_名称" => ID）
     */
    public static function getStopReasonData($connection)
    {
        // 检查缓存
        if (!empty(self::$stopReasonCache)) {
            return self::$stopReasonCache;
        }
        
        // 查询所有停止原因
        $rows = $connection->createCommand()
            ->select('id, name, str_type')
            ->from('sal_cont_str')
            ->queryAll();
        
        $data = array();
        foreach ($rows as $row) {
            $key = $row['str_type'] . '_' . $row['name'];
            $data[$key] = $row['id'];
        }
        
        // 缓存结果
        self::$stopReasonCache = $data;
        
        return $data;
    }
    
    /**
     * 根据停止类型和名称获取停止原因ID（带缓存）
     * 
     * @param int $strType 停止类型（1=暂停, 2=终止）
     * @param string $name 停止原因名称
     * @param CDbConnection $connection 数据库连接
     * @return int|null 停止原因ID，找不到返回 null
     */
    public static function getStopReasonId($strType, $name, $connection)
    {
        if (empty($name)) {
            return null;
        }
        
        $data = self::getStopReasonData($connection);
        $key = $strType . '_' . $name;
        
        if (isset($data[$key])) {
            return $data[$key];
        }
        
        // 如果找不到，返回同类型的第一个默认原因
        foreach ($data as $cacheKey => $id) {
            if (strpos($cacheKey, $strType . '_') === 0) {
                return $id;
            }
        }
        
        return null;
    }
    
    /**
     * 预加载常用数据缓存（类似 ImportVirForm::initCacheData）
     * 建议在批量导入开始时调用，以减少后续的数据库查询次数
     * 
     * @param CDbConnection $connection 数据库连接
     */
    public static function preloadCache($connection)
    {
        $startTime = microtime(true);
        
        // 1. 预加载枚举类型数据
        $enumTypes = array('fee_type', 'bill_day', 'settle_type', 'pay_week', 'receivable_day', 'pay_type');
        foreach ($enumTypes as $type) {
            self::getEnumData($type);
        }
        
        // 2. 预加载停止原因数据
        self::getStopReasonData($connection);
        
        // 3. 预加载业务大类数据
        $yewuRows = $connection->createCommand()
            ->select('id, name')
            ->from('sal_yewudalei')
            ->queryAll();
        foreach ($yewuRows as $row) {
            self::$yewudaleiCache[$row['name']] = $row['id'];
        }
        
        // 4. 预加载服务项目数据
        $serviceRows = $connection->createCommand()
            ->select('id, id_char, service_type, name')
            ->from('sal_service_type')
            ->queryAll();
        foreach ($serviceRows as $row) {
            self::$serviceTypeCache[$row['name']] = $row;
        }
        
        // 5. 预加载行业类别数据
        $custClassRows = $connection->createCommand()
            ->select('id, class_name, nature_id')
            ->from('sal_cust_class')
            ->queryAll();
        foreach ($custClassRows as $row) {
            self::$custClassCache[$row['class_name']] = $row;
        }
        
        // 6. 预加载客户类型数据（swo_customer_type）
        $suffix = Yii::app()->params['envSuffix'];
        $customerTypeRows = $connection->createCommand()
            ->select('id, description as name')
            ->from("swoper{$suffix}.swo_customer_type")
            ->queryAll();
        foreach ($customerTypeRows as $row) {
            self::$customerTypeCache[$row['name']] = $row['id'];
        }
        
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);
        
        Yii::log("数据迁移缓存预加载完成，耗时 {$duration}ms", 'info', 'DataMigration');
        Yii::log("缓存统计 - 业务大类: " . count(self::$yewudaleiCache) . 
                 ", 服务项目: " . count(self::$serviceTypeCache) . 
                 ", 行业类别: " . count(self::$custClassCache) . 
                 ", 客户类型: " . count(self::$customerTypeCache) . 
                 ", 停止原因: " . count(self::$stopReasonCache), 'info', 'DataMigration');
    }
    
    /**
     * 清空所有缓存（用于测试或重置）
     */
    public static function clearCache()
    {
        self::$employeeCache = array();
        self::$yewudaleiCache = array();
        self::$cityCodeCache = array();
        self::$lbsMainCache = array();
        self::$serviceTypeCache = array();
        self::$custClassCache = array();
        self::$officeCache = array();
        self::$districtCache = array();
        self::$customerTypeCache = array();
        self::$enumCache = array();
        self::$stopReasonCache = array();
        
        Yii::log('已清空所有数据迁移缓存', 'info', 'DataMigration');
    }
}

