<?php

/**
 * 虚拟合约数据检查控制器
 * 用于检查虚拟合约中服务频次金额加总与 month_amt 不一致的数据
 */
class VirtualContractCheckController extends Controller
{
    public $layout = '//layouts/main';

    /**
     * 过滤器配置
     */
    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    /**
     * 访问控制
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('index', 'syncOne', 'syncBatch'),
                'users' => array('@'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }
    
    /**
     * 单个同步：从派单拉取数据并更新到CRM
     */
    public function actionSyncOne()
    {
        $virId = Yii::app()->request->getPost('vir_id');
        
        if (!$virId) {
            echo json_encode(array('success' => false, 'msg' => '缺少参数'));
            Yii::app()->end();
        }
        
        try {
            $db = Yii::app()->db;
            
            // 获取虚拟合约
            $vir = $db->createCommand()
                ->select('id, u_id, vir_code')
                ->from('sal_contract_virtual')
                ->where('id=:id', array(':id' => $virId))
                ->queryRow();
            
            if (!$vir || !$vir['vir_code']) {
                echo json_encode(array('success' => false, 'msg' => '虚拟合约不存在或没有合同编号'));
                Yii::app()->end();
            }
            
            // 从派单获取数据
            $apiUrl = $this->getPaidanApiUrl();
            if (!$apiUrl) {
                echo json_encode(array('success' => false, 'msg' => '请先配置派单API地址'));
                Yii::app()->end();
            }
            
            $url = $apiUrl . '?contract_number=' . urlencode($vir['vir_code']);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            if (!$result) {
                echo json_encode(array('success' => false, 'msg' => '派单API响应解析失败：' . substr($response, 0, 200)));
                Yii::app()->end();
            }
            if ($result['code'] != 200) {
                $errMsg = isset($result['message']) ? $result['message'] : '未知错误';
                echo json_encode(array('success' => false, 'msg' => '派单API错误：' . $errMsg));
                Yii::app()->end();
            }
            if (empty($result['data']['contracts'][0])) {
                echo json_encode(array('success' => false, 'msg' => '派单未返回合约数据'));
                Yii::app()->end();
            }
            
            $paidanData = $result['data']['contracts'][0];
            
            // 调试：记录派单返回的数据
            Yii::log('派单返回数据: month_amt=' . (isset($paidanData['month_amt']) ? $paidanData['month_amt'] : 'null') . 
                     ', year_amt=' . (isset($paidanData['year_amt']) ? $paidanData['year_amt'] : 'null') .
                     ', service_fre_type=' . (isset($paidanData['service_fre_type']) ? $paidanData['service_fre_type'] : 'null'), 
                     'info', 'application');
            
            // 获取现有虚拟合约数据（需要 service_type 和 detail_json）
            $existingVir = $db->createCommand()
                ->select('service_type, detail_json')
                ->from('sal_contract_virtual')
                ->where('id=:id', array(':id' => $virId))
                ->queryRow();
            
            // 根据频次类型决定 month_amt 和 year_amt
            $freType = isset($paidanData['service_fre_type']) ? intval($paidanData['service_fre_type']) : 1;
            $monthAmt = null;
            $yearAmt = null;
            
            if ($freType == 1 || $freType == 3) {
                // 1=固定频次按月, 3=固定每周：有月金额
                $monthAmt = isset($paidanData['month_amt']) ? $paidanData['month_amt'] : 0;
                $yearAmt = isset($paidanData['year_amt']) ? $paidanData['year_amt'] : null;
            } elseif ($freType == 2 || $freType == 4) {
                // 2=非固定, 4=固定非固定金额：只有年金额
                $monthAmt = null;
                $yearAmt = isset($paidanData['year_amt']) ? $paidanData['year_amt'] : 0;
            }
            
            // 生成 detail_json（使用修正后的金额）
            $detailJson = $this->generateDetailJson($existingVir, $paidanData);
            
            // 开启事务
            $transaction = $db->beginTransaction();
            try {
                // 1. 更新主表
                $db->createCommand()->update('sal_contract_virtual', array(
                    'month_amt' => $monthAmt,
                    'year_amt' => $yearAmt,
                    'service_sum' => $paidanData['service_sum'],
                    'service_fre_type' => $paidanData['service_fre_type'],
                    'service_fre_text' => $paidanData['service_fre_text'],
                    'service_fre_json' => $paidanData['service_fre_json'],
                    'u_service_json' => $paidanData['u_service_json'],
                    'detail_json' => $detailJson,
                    'lcu' => Yii::app()->user->name,
                    'lud' => date('Y-m-d H:i:s'),
                ), 'id=:id', array(':id' => $virId));
                
                // 2. 删除旧的频次记录
                $db->createCommand()->delete('sal_contract_vir_week', 'vir_id=:vir_id', array(':vir_id' => $virId));
                
                // 3. 插入新的频次记录
                if (!empty($paidanData['frequency_details']) && is_array($paidanData['frequency_details'])) {
                    foreach ($paidanData['frequency_details'] as $freq) {
                        $db->createCommand()->insert('sal_contract_vir_week', array(
                            'vir_id' => $virId,
                            'month_cycle' => isset($freq['month_cycle']) ? intval($freq['month_cycle']) : 0,
                            'week_cycle' => isset($freq['week_cycle']) ? intval($freq['week_cycle']) : null,
                            'day_cycle' => isset($freq['day_cycle']) ? intval($freq['day_cycle']) : null,
                            'unit_price' => isset($freq['unit_price']) ? floatval($freq['unit_price']) : 0,
                            'cycle_text' => isset($freq['cycle_text']) ? $freq['cycle_text'] : null,
                        ));
                    }
                } else {
                    // 记录日志：没有频次数据
                    Yii::log('同步虚拟合约 ID=' . $virId . ' 时未找到 frequency_details 数据', 'warning', 'application');
                }
                
                $transaction->commit();
                
                // 查询更新后的 detail_json 确认保存成功
                $savedDetailJson = $db->createCommand()
                    ->select('detail_json')
                    ->from('sal_contract_virtual')
                    ->where('id=:id', array(':id' => $virId))
                    ->queryScalar();
                
                echo json_encode(array(
                    'success' => true, 
                    'msg' => '更新成功',
                    'debug_info' => array(
                        'paidan_month_amt' => isset($paidanData['month_amt']) ? $paidanData['month_amt'] : 'null',
                        'paidan_year_amt' => isset($paidanData['year_amt']) ? $paidanData['year_amt'] : 'null',
                        'paidan_service_fre_type' => isset($paidanData['service_fre_type']) ? $paidanData['service_fre_type'] : 'null',
                        'saved_month_amt' => $monthAmt,
                        'saved_year_amt' => $yearAmt,
                        'service_type' => isset($existingVir['service_type']) ? $existingVir['service_type'] : 'null',
                        'saved_detail_json' => substr($savedDetailJson, 0, 800)  // 显示前800字符
                    )
                ));
                
            } catch (Exception $e) {
                $transaction->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'msg' => $e->getMessage()));
        }
        
        Yii::app()->end();
    }
    
    /**
     * 批量同步
     */
    public function actionSyncBatch()
    {
        $virIds = Yii::app()->request->getPost('vir_ids');
        
        if (!$virIds || !is_array($virIds)) {
            echo json_encode(array('success' => false, 'msg' => '请选择要同步的数据'));
            Yii::app()->end();
        }
        
        $apiUrl = $this->getPaidanApiUrl();
        if (!$apiUrl) {
            echo json_encode(array('success' => false, 'msg' => '请先配置派单API地址'));
            Yii::app()->end();
        }
        
        $db = Yii::app()->db;
        $success = 0;
        $failed = 0;
        
        foreach ($virIds as $virId) {
            try {
                $vir = $db->createCommand()
                    ->select('id, u_id, vir_code')
                    ->from('sal_contract_virtual')
                    ->where('id=:id', array(':id' => $virId))
                    ->queryRow();
                
                if (!$vir || !$vir['vir_code']) {
                    $failed++;
                    continue;
                }
                
                $url = $apiUrl . '?contract_number=' . urlencode($vir['vir_code']);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                curl_close($ch);
                
                $result = json_decode($response, true);
                if (!$result || $result['code'] != 200 || empty($result['data']['contracts'][0])) {
                    $failed++;
                    continue;
                }
                
                $paidanData = $result['data']['contracts'][0];
                
                // 获取现有虚拟合约数据（需要 service_type 和 detail_json）
                $existingVir = $db->createCommand()
                    ->select('service_type, detail_json')
                    ->from('sal_contract_virtual')
                    ->where('id=:id', array(':id' => $virId))
                    ->queryRow();
                
                // 生成 detail_json
                $detailJson = $this->generateDetailJson($existingVir, $paidanData);
                
                // 根据频次类型决定 month_amt 和 year_amt
                $freType = isset($paidanData['service_fre_type']) ? intval($paidanData['service_fre_type']) : 1;
                $monthAmt = null;
                $yearAmt = null;
                
                if ($freType == 1 || $freType == 3) {
                    // 1=固定频次按月, 3=固定每周：有月金额
                    $monthAmt = isset($paidanData['month_amt']) ? $paidanData['month_amt'] : 0;
                    $yearAmt = isset($paidanData['year_amt']) ? $paidanData['year_amt'] : null;
                } elseif ($freType == 2 || $freType == 4) {
                    // 2=非固定, 4=固定非固定金额：只有年金额
                    $monthAmt = null;
                    $yearAmt = isset($paidanData['year_amt']) ? $paidanData['year_amt'] : 0;
                }
                
                // 开启事务
                $transaction = $db->beginTransaction();
                try {
                    // 1. 更新主表
                    $db->createCommand()->update('sal_contract_virtual', array(
                        'month_amt' => $monthAmt,
                        'year_amt' => $yearAmt,
                        'service_sum' => $paidanData['service_sum'],
                        'service_fre_type' => $paidanData['service_fre_type'],
                        'service_fre_text' => $paidanData['service_fre_text'],
                        'service_fre_json' => $paidanData['service_fre_json'],
                        'u_service_json' => $paidanData['u_service_json'],
                        'detail_json' => $detailJson,
                        'lcu' => Yii::app()->user->name,
                        'lud' => date('Y-m-d H:i:s'),
                    ), 'id=:id', array(':id' => $virId));
                    
                    // 2. 删除旧的频次记录
                    $db->createCommand()->delete('sal_contract_vir_week', 'vir_id=:vir_id', array(':vir_id' => $virId));
                    
                    // 3. 插入新的频次记录
                    if (!empty($paidanData['frequency_details']) && is_array($paidanData['frequency_details'])) {
                        foreach ($paidanData['frequency_details'] as $freq) {
                            $db->createCommand()->insert('sal_contract_vir_week', array(
                                'vir_id' => $virId,
                                'month_cycle' => isset($freq['month_cycle']) ? intval($freq['month_cycle']) : 0,
                                'week_cycle' => isset($freq['week_cycle']) ? intval($freq['week_cycle']) : null,
                                'day_cycle' => isset($freq['day_cycle']) ? intval($freq['day_cycle']) : null,
                                'unit_price' => isset($freq['unit_price']) ? floatval($freq['unit_price']) : 0,
                                'cycle_text' => isset($freq['cycle_text']) ? $freq['cycle_text'] : null,
                            ));
                        }
                    } else {
                        // 记录日志：没有频次数据
                        Yii::log('批量同步虚拟合约 ID=' . $virId . ' 时未找到 frequency_details 数据', 'warning', 'application');
                    }
                    
                    $transaction->commit();
                    $success++;
                    
                } catch (Exception $e) {
                    $transaction->rollback();
                    throw $e;
                }
                
            } catch (Exception $e) {
                $failed++;
            }
        }
        
        echo json_encode(array(
            'success' => true,
            'msg' => "成功: {$success} 条, 失败: {$failed} 条"
        ));
        
        Yii::app()->end();
    }
    
    /**
     * 获取派单API地址
     */
    protected function getPaidanApiUrl()
    {
        // 从配置文件读取
        if (isset(Yii::app()->params['paidanSyncApiUrl'])) {
            return Yii::app()->params['paidanSyncApiUrl'];
        }
        return null;
    }

    /**
     * 生成 detail_json（根据派单数据更新服务项目详情）
     */
    protected function generateDetailJson($existingVir, $paidanData)
    {
        // 获取服务类型代码（PHP 5.x 兼容）
        $serviceType = isset($existingVir['service_type']) ? $existingVir['service_type'] : '';
        if (empty($serviceType)) {
            return null;
        }
        
        // 解析现有的 detail_json
        $detailJson = array();
        if (!empty($existingVir['detail_json'])) {
            $detailJson = json_decode($existingVir['detail_json'], true);
            if (!is_array($detailJson)) {
                $detailJson = array();
            }
        }
        
        // 根据频次类型决定使用年金额还是月金额
        // 1=固定频次按月, 3=固定每周 使用月金额
        // 2=非固定, 4=固定非固定金额 使用年金额
        $freType = isset($paidanData['service_fre_type']) ? intval($paidanData['service_fre_type']) : 1;
        $mainAmount = 0;  // 默认为0，不是空字符串
        
        $monthAmt = isset($paidanData['month_amt']) ? floatval($paidanData['month_amt']) : 0;
        $yearAmt = isset($paidanData['year_amt']) ? floatval($paidanData['year_amt']) : 0;
        
        if ($freType == 1 || $freType == 3) {
            // 固定频次按月 或 固定每周：使用月金额
            $mainAmount = $monthAmt;
        } elseif ($freType == 2 || $freType == 4) {
            // 非固定 或 固定非固定金额：使用年金额
            $mainAmount = $yearAmt;
        }
        
        // 调试日志
        Yii::log("detail_json 生成 - serviceType: {$serviceType}, freType: {$freType}, monthAmt: {$monthAmt}, yearAmt: {$yearAmt}, mainAmount: {$mainAmount}", 'info', 'application');
        
        // 更新 detail_json 中的字段（遍历所有可能的字段前缀）
        // 找出所有 svc_ 开头的 FreText 字段并更新
        $newFreText = isset($paidanData['service_fre_text']) ? $paidanData['service_fre_text'] : '';
        
        foreach ($detailJson as $key => $value) {
            // 更新所有服务类型的 FreText
            if (preg_match('/^svc_(.+)FreText$/', $key)) {
                $detailJson[$key] = $newFreText;
            }
        }
        
        // 更新主服务类型的字段
        $detailJson['svc_' . $serviceType] = $mainAmount;
        $detailJson['svc_' . $serviceType . 'FreType'] = $freType;
        $detailJson['svc_' . $serviceType . 'FreSum'] = isset($paidanData['service_sum']) ? intval($paidanData['service_sum']) : 0;
        $detailJson['svc_' . $serviceType . 'FreAmt'] = isset($paidanData['year_amt']) ? floatval($paidanData['year_amt']) : 0;
        $detailJson['svc_' . $serviceType . 'FreJson'] = isset($paidanData['service_fre_json']) ? $paidanData['service_fre_json'] : '';
        $detailJson['svc_' . $serviceType . 'FreText'] = $newFreText;
        $detailJson['svc_' . $serviceType . '7'] = isset($paidanData['year_amt']) ? floatval($paidanData['year_amt']) : 0;
        
        // PHP 5.3 兼容：不使用 JSON_UNESCAPED_UNICODE
        if (defined('JSON_UNESCAPED_UNICODE')) {
            return json_encode($detailJson, JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode($detailJson);
        }
    }

    /**
     * 主页面
     */
    public function actionIndex()
    {
        try {
            // 获取分页参数
            $page = max(1, intval(Yii::app()->request->getParam('page', 1)));
            $pageSize = min(200, max(20, intval(Yii::app()->request->getParam('pageSize', 100))));
            
            // 获取检查结果
            $result = $this->getCheckResults($page, $pageSize);
            
            // 渲染视图
            $this->render('index', array(
                'data' => $result['data'],
                'currentPage' => $page,
                'pageSize' => $pageSize,
                'totalRecords' => $result['total'],
                'totalPages' => ceil($result['total'] / $pageSize),
            ));
            
        } catch (Exception $e) {
            echo '<h3>错误：' . CHtml::encode($e->getMessage()) . '</h3>';
            echo '<pre>';
            echo '文件：' . $e->getFile() . "\n";
            echo '行号：' . $e->getLine() . "\n";
            echo '堆栈：' . "\n" . $e->getTraceAsString();
            echo '</pre>';
        }
    }

    /**
     * 获取检查结果
     * 
     * @param int $page 页码
     * @param int $pageSize 每页记录数
     * @return array
     */
    protected function getCheckResults($page = 1, $pageSize = 100)
    {
        $connection = Yii::app()->db;
        $offset = ($page - 1) * $pageSize;
        
        // 使用派生表计算 calculated_month_amt
        $sql = "
            SELECT 
                v.*,
                cs.store_code,
                cs.store_name,
                c.clue_code,
                c.cust_name AS clue_name,
                calc.calculated_month_amt,
                ABS(v.month_amt - calc.calculated_month_amt) AS diff
            FROM sal_contract_virtual v
            LEFT JOIN sal_clue_store cs ON cs.id = v.clue_store_id
            LEFT JOIN sal_clue c ON c.id = cs.clue_id
            -- 派生表：计算每个虚拟合约的平均月金额
            LEFT JOIN (
                SELECT 
                    vw.vir_id,
                    ROUND(AVG(month_total), 2) AS calculated_month_amt
                FROM (
                    SELECT 
                        vir_id,
                        month_cycle,
                        SUM(unit_price) AS month_total
                    FROM sal_contract_vir_week
                    WHERE month_cycle > 0 AND unit_price > 0
                    GROUP BY vir_id, month_cycle
                ) AS vw
                GROUP BY vw.vir_id
            ) AS calc ON calc.vir_id = v.id
            WHERE v.vir_status IN (30, 40, 50)
              AND v.month_amt IS NOT NULL
              AND v.month_amt > 0
              AND NOT (v.service_sum = 1 AND v.service_fre_type IN (1, 3))
              AND EXISTS (
                  SELECT 1 FROM sal_contract_vir_week WHERE vir_id = v.id LIMIT 1
              )
              AND calc.calculated_month_amt IS NOT NULL
              AND ABS(v.month_amt - calc.calculated_month_amt) > 0.01
            ORDER BY diff DESC
            LIMIT {$pageSize} OFFSET {$offset}
        ";
        
        $records = $connection->createCommand($sql)->queryAll();
        
        // 统计总数 - 使用相同的派生表逻辑
        $countSql = "
            SELECT COUNT(*) AS total
            FROM sal_contract_virtual v
            LEFT JOIN (
                SELECT 
                    vw.vir_id,
                    ROUND(AVG(month_total), 2) AS calculated_month_amt
                FROM (
                    SELECT 
                        vir_id,
                        month_cycle,
                        SUM(unit_price) AS month_total
                    FROM sal_contract_vir_week
                    WHERE month_cycle > 0 AND unit_price > 0
                    GROUP BY vir_id, month_cycle
                ) AS vw
                GROUP BY vw.vir_id
            ) AS calc ON calc.vir_id = v.id
            WHERE v.vir_status IN (30, 40, 50)
              AND v.month_amt IS NOT NULL
              AND v.month_amt > 0
              AND NOT (v.service_sum = 1 AND v.service_fre_type IN (1, 3))
              AND EXISTS (
                  SELECT 1 FROM sal_contract_vir_week WHERE vir_id = v.id LIMIT 1
              )
              AND calc.calculated_month_amt IS NOT NULL
              AND ABS(v.month_amt - calc.calculated_month_amt) > 0.01
        ";
        
        $totalResult = $connection->createCommand($countSql)->queryRow();
        $total = $totalResult ? intval($totalResult['total']) : 0;
        
        // 处理每条记录
        foreach ($records as &$row) {
            // 获取周表数据
            $weekData = $connection->createCommand()
                ->select('*')
                ->from('sal_contract_vir_week')
                ->where('vir_id=:vir_id', array(':vir_id' => $row['id']))
                ->queryAll();
            
            // 格式化周表数据用于显示
            $weekText = array();
            $monthGroups = array();
            
            foreach ($weekData as $week) {
                $monthCycle = intval($week['month_cycle']);
                if (!isset($monthGroups[$monthCycle])) {
                    $monthGroups[$monthCycle] = array();
                }
                $monthGroups[$monthCycle][] = $week;
            }
            
            // 按month_cycle降序排序
            krsort($monthGroups);
            
            foreach ($monthGroups as $monthCycle => $weeks) {
                // 按unit_price降序排序
                usort($weeks, function($a, $b) {
                    $diffPrice = floatval($b['unit_price']) - floatval($a['unit_price']);
                    if ($diffPrice == 0) {
                        return 0;
                    }
                    return ($diffPrice > 0) ? 1 : -1;
                });
                
                foreach ($weeks as $week) {
                    $parts = array();
                    $parts[] = "月周期={$week['month_cycle']}";
                    if (!empty($week['week_cycle'])) {
                        $parts[] = "周={$week['week_cycle']}";
                    }
                    if (!empty($week['day_cycle'])) {
                        $parts[] = "日={$week['day_cycle']}";
                    }
                    $parts[] = "单价={$week['unit_price']}";
                    if (!empty($week['cycle_text'])) {
                        $parts[] = "说明={$week['cycle_text']}";
                    }
                    
                    $weekText[] = implode(', ', $parts);
                }
            }
            
            $row['week_detail'] = implode("\n", $weekText);
            
            // 格式化service_fre_json用于显示
            if (!empty($row['service_fre_json'])) {
                $json = json_decode($row['service_fre_json'], true);
                if ($json) {
                    $row['service_fre_json_formatted'] = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                } else {
                    $row['service_fre_json_formatted'] = $row['service_fre_json'];
                }
            } else {
                $row['service_fre_json_formatted'] = '';
            }
        }
        
        return array(
            'data' => $records,
            'total' => $total
        );
    }
}
