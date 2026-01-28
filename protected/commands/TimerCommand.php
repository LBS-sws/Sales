<?php
class TimerCommand extends CConsoleCommand {

    //每天的23:25点执行
	public function actionIndex() {
	    /*报错代码
		$obj = new FivestepForm();
		$typelist = $obj->getFiveTypeList();
		$steplist = $obj->getStepList();
	    */
        //echo "Timer Start:".date("Y/m/d H:i:s")."\n";
        $typelist = array(
            0=>Yii::t('misc','Insecticidal'),
            1=>Yii::t('misc','Restroom'),
            2=>Yii::t('misc','Third'),
            3=>Yii::t('misc','Air Purifier'),
            4=>Yii::t('misc','Oil separator'),
        );
        $steplist = array('1'=>Yii::t('sales','Step 1'),
            '2'=>Yii::t('sales','Step 2'),
            '3'=>Yii::t('sales','Step 3'),
            '4'=>Yii::t('sales','Step 4'),
            '5'=>Yii::t('sales','Step 5'),
        );

        $suffix = Yii::app()->params['envSuffix'];
        $firstDay = date("Y/m/d");
        $firstDay = date("Y/m/d", strtotime("$firstDay - 30 day"));
        $secondDay = date("Y/m/d", strtotime("$firstDay - 60 day"));
        $sql="select * from hr$suffix.hr_employee WHERE  position in (SELECT id FROM hr$suffix.hr_dept where dept_class='sales') AND staff_status = 0";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                $record['entry_time'] = date_format(date_create($record['entry_time']), "Y/m/d");
                $sql1="select a.*, b.name as city_name, f.name as staff_name, f.code as staff_code, h.name as post_name,
				d.field_value as mgr_score, e.field_value as dir_score, g.field_value as sup_score
				from sales$suffix.sal_fivestep a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sales$suffix.sal_fivestep_info d on a.id=d.five_id and d.field_id='mgr_score'
				left outer join sales$suffix.sal_fivestep_info e on a.id=e.five_id and e.field_id='dir_score'
				left outer join sales$suffix.sal_fivestep_info g on a.id=g.five_id and g.field_id='sup_score'
				left outer join hr$suffix.hr_dept h on f.position=h.id
				where f.name= '".$record['name']."'";
                $arr = Yii::app()->db->createCommand($sql1)->queryAll();
                if($record['entry_time'] == "$firstDay"){
                    $sql = "select approver_type,username from account$suffix.acc_approver where city='".$record['city']."' and approver_type='regionMgr'";
                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $zjl = $rows[0]['username'];
                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$zjl'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();

                    $from_addr = "it@lbsgroup.com.hk";
                    $to_addr = "[\"" .$rs[0]['email']."\"]";
                    $subject = "五部曲提醒-" . $record['name'];
                    $description = "五部曲提醒-" . $record['name'];
                    $message = "姓名：" . $record['name'] . ",入职日期为：" . $record['entry_time'] . ",已经到上传五部曲的时间了<br>";
					$message .= Yii::t('sales','Position')."：".$record['post_name']."<br>";
					$message .= Yii::t('sales','5 Steps')."：".$steplist[$record['step']]."<br>";
					$message .= Yii::t('misc','Five Type')."：".$typelist[$record['five_type']]."<br>";
					$lcu = "admin";
                    $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
                        'request_dt' => date('Y-m-d H:i:s'),
                        'from_addr' => $from_addr,
                        'to_addr' => $to_addr,
                        'subject' => $subject,//郵件主題
                        'description' => $description,//郵件副題
                        'message' => $message,//郵件內容（html）
                        'status' => "P",
                        'lcu' => $lcu,
                        'lcd' => date('Y-m-d H:i:s'),
                    ));
                }elseif (empty($arr)&&$record['entry_time'] == "$secondDay"){
                    $sql = "select approver_type,username from account$suffix.acc_approver where city='".$record['city']."' and approver_type='regionMgr'";
                    $rows = Yii::app()->db->createCommand($sql)->queryAll();
                    $zjl = $rows[0]['username'];
                    $sql1 = "SELECT email FROM security$suffix.sec_user WHERE username='$zjl'";
                    $rs = Yii::app()->db->createCommand($sql1)->queryAll();

                    $from_addr = "it@lbsgroup.com.hk";
                    $to_addr = "[\"" .$rs[0]['email']."\"]";
                    $subject = "五部曲提醒-" . $record['name'];
                    $description = "五部曲提醒-" . $record['name'];
                    $message = "姓名：" . $record['name'] . ",入职日期为：" . $record['entry_time'] . ",賬戶五部曲還是空白请提醒上传";
					$message .= Yii::t('sales','Position')."：".$record['post_name']."<br>";
					$message .= Yii::t('sales','5 Steps')."：".$steplist[$record['step']]."<br>";
					$message .= Yii::t('misc','Five Type')."：".$typelist[$record['five_type']]."<br>";
                    $lcu = "admin";
                    $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
                        'request_dt' => date('Y-m-d H:i:s'),
                        'from_addr' => $from_addr,
                        'to_addr' => $to_addr,
                        'subject' => $subject,//郵件主題
                        'description' => $description,//郵件副題
                        'message' => $message,//郵件內容（html）
                        'status' => "P",
                        'lcu' => $lcu,
                        'lcd' => date('Y-m-d H:i:s'),
                    ));
                }

            }
        }

        //终止客户邮件提醒
        $this->shiftEmailHint();
        //终止客户的再次回访邮件提醒
        $this->shiftAgainEmailHint();
        //销售俱乐部每天刷新一次
        $this->resetClubSales();
        //市场营销的资料超过15天自动退回
        //$this->marketCompanyForBack();
        //自动续约
        $this->actionRenewal();
        //自动终止
        $this->actionStopByS();
        //echo "Timer End:".date("Y/m/d H:i:s")."\n";
	}

    //暂停的CRM合约自动转终止
	public function actionStopByS(){
        $suffix = Yii::app()->params['envSuffix'];
        $setMonth = Yii::app()->db->createCommand()->select("set_name")->from("sales{$suffix}.sal_set_menu")
            ->where("set_type='computeStop'")->order("id desc")->queryRow();
        $pro_remark = "CRM系统自动暂停转终止：".date("Y-m-d H:i:s");
        $stop_set_id=100;//终止原因
        $stopDate = date("Y-m-d");
        if($setMonth){
            $vir_arr=array();//CRM合约id
            $contract_ids=array();//派单系统合约id
            $surplus_json=array();//合约的终止剩余次数及剩余金额
            $setMonth = intval($setMonth["set_name"]);
            $setMonth = is_numeric($setMonth)?$setMonth:0;
            $sDate = date("Y-m-d",strtotime("-{$setMonth} months"));
            $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                ->where("vir_status=40 and DATE_FORMAT(effect_date,'%Y-%m-%d')<='{$sDate}' and u_id is not null")
                ->queryAll();
            if($virRows){
                $totalAmt=0;//终止总金额
                $totalSum=0;//终止总次数
                foreach ($virRows as $key=>$virRow){
                    $boolRow = Yii::app()->db->createCommand()->select("id")->from("sales{$suffix}.sal_contpro_virtual")
                        ->where("vir_id=:id and pro_status>=1 and pro_status<30",array(":id"=>$virRow["id"]))
                        ->queryRow();
                    if($boolRow){//存在变更中的操作
                        unset($virRows[$key]);
                        continue;
                    }
                    $vir_arr[]=$virRow["id"];
                    $contract_ids[$virRow["u_id"]]=$virRow["id"];
                    $surplus_json[$virRow["id"]]=array(
                        "surplus_number"=>empty($virRow["service_sum"])?0:intval($virRow["service_sum"]),
                        "surplus_money"=>empty($virRow["year_amt"])?0:floatval($virRow["year_amt"]),
                    );
                }
                $model = new CurlNotesModel();
                $model->sendSurplusDataSetByUID();
                $model->setMinUrl($model->min_url);
                $uIDs = array_keys($contract_ids);
                $data=array("contract_ids"=>implode(",",$uIDs));
                $list = $model->sendUData($data,"GET",false);
                if($list["status"]){//如果派单那边查出有剩余金额及剩余次数
                    $outData = $list["outData"]["data"];
                    foreach ($outData as $row){
                        if(isset($contract_ids[$row["contract_id"]])){
                            $id = $contract_ids[$row["contract_id"]];
                            $surplus_json[$id]=$row;
                        }
                    }
                }
                if(empty($virRows)){
                    return false;
                }
                foreach ($surplus_json as $item){
                    $totalSum+=$item["surplus_number"];
                    $totalAmt+=$item["surplus_money"];
                }
                $saveArr= array(
                    "pro_type"=>"T",
                    "pro_date"=>$stopDate,
                    "pro_remark"=>$pro_remark,
                    "pro_status"=>30,
                    "city"=>"CN",
                    "vir_id"=>current($vir_arr),
                    "vir_id_text"=>implode(",",$vir_arr),
                    "stop_set_id"=>$stop_set_id,
                    "stop_date"=>$stopDate,
                    "stop_year_amt"=>$totalAmt,
                    "need_back"=>"N",
                    "surplus_num"=>$totalSum,
                    "surplus_amt"=>$totalAmt,
                    "surplus_json"=>json_encode($surplus_json,JSON_UNESCAPED_UNICODE),
                );
                Yii::app()->db->createCommand()->insert("sal_virtual_batch",$saveArr);
                $batch_id = Yii::app()->db->getLastInsertID();
                Yii::app()->db->createCommand()->update("sal_virtual_batch",array(
                    "pro_code"=>"STT".(10000+$batch_id)
                ),"id=:id",array(":id"=>$batch_id));
                foreach ($virRows as $virtualRow){
                    $vir_id = $virtualRow["id"];
                    $virSaveArr = $virtualRow;
                    $virSaveArr["pro_vir_type"]=2;
                    $virSaveArr["vir_batch_id"]=$batch_id;
                    $virSaveArr["vir_id"]=$vir_id;
                    $virSaveArr["pro_type"]="T";
                    $virSaveArr["pro_num"]=CGetName::getProNumByVir($vir_id,$virSaveArr["pro_type"]);
                    $virSaveArr["pro_date"]=$stopDate;
                    $virSaveArr["pro_remark"]=$pro_remark;
                    $virSaveArr["pro_status"]=30;
                    $virSaveArr["pro_change"]=-1*$virtualRow["year_amt"];
                    $dataEx = array(
                        "stop_set_id"=>$stop_set_id,
                        "stop_date"=>$stopDate,
                        "stop_month_amt"=>$virtualRow["month_amt"],
                        "stop_year_amt"=>$virtualRow["year_amt"],
                        "need_back"=>"N",
                        "surplus_num"=>$surplus_json[$vir_id]["surplus_number"],
                        "surplus_amt"=>$surplus_json[$vir_id]["surplus_money"],
                        "vir_status"=>50,
                        "effect_date"=>$stopDate,
                    );
                    foreach ($dataEx as $key=>$item){
                        $virSaveArr[$key]=$item;
                    }
                    unset($virSaveArr["id"]);
                    Yii::app()->db->createCommand()->insert("sal_contpro_virtual",$virSaveArr);
                    $virtualId = Yii::app()->db->getLastInsertID();
                    Yii::app()->db->createCommand()->update("sal_contpro_virtual",array(
                        "pro_code"=>"STT".(10000+$virtualId)
                    ),"id=".$virtualId);
                    Yii::app()->db->createCommand()->update("sal_contract_virtual",$dataEx,"id=".$vir_id);
                }

                //发送续约消息给派单系统
                $uVirModel = new CurlNotesByVirPro();
                $uVirModel->pro_type="T";
                $virIDs = implode(",",$vir_arr);
                $uVirModel->sendAllVirByIDsAndUpdate($virIDs);
            }
        }
    }

    //CRM合约自动续约
	public function actionRenewal(){
        $suffix = Yii::app()->params['envSuffix'];
        $nowTime = date("Y-m-d H:i:s");        // 初始化
        $logFile = dirname(__FILE__).'/../../logs/auto_renewal_'.date("Ym").'.log';
        $csvFile = dirname(__FILE__).'/../../logs/auto_renewal_'.date("YmdHis").'.csv';
        $logDir = dirname($logFile);
        if(!is_dir($logDir)){
            mkdir($logDir, 0777, true);
        }
        // 初始化CSV数据数组
        $csvData = array();
        
        $this->writeRenewalLog($logFile, "\n========== 自动续约开始: {$nowTime} ==========");
        
        $setDay = Yii::app()->db->createCommand()->select("set_name")->from("sales{$suffix}.sal_set_menu")
            ->where("set_type='computeRenewal'")->order("id desc")->queryRow();
        $pro_remark = "CRM系统自动续约";
        if($setDay){
            $setDay = intval($setDay["set_name"]);
            $setDay = is_numeric($setDay)?$setDay:0;
            $rDate = date("Y-m-d",strtotime("+{$setDay} days"));
            $this->writeRenewalLog($logFile, "配置提前天数: {$setDay} 天");
            $this->writeRenewalLog($logFile, "续约截止日期: {$rDate} (合约结束日期 <= 该日期的将被续约)");
            
            // 只续约正常状态(10,30)的合约，排除暂停(40)和终止(50)等其他状态
            // 即使is_renewal='Y'，如果是终止状态也不续约
            $virRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
                ->where("is_renewal='Y' and vir_status in (10,30) and DATE_FORMAT(cont_end_dt,'%Y-%m-%d')<='{$rDate}'")
                ->queryAll();
            
            $this->writeRenewalLog($logFile, "查询到符合条件的虚拟合约数量: ".count($virRows));
            
            if($virRows){
                $virIDs=array();
                $contIDs=array();
                $renewalList=array();
                $renewalCount = 0;
                foreach ($virRows as $key=>$virRow){
                    $boolRow = Yii::app()->db->createCommand()->select("id")->from("sales{$suffix}.sal_contpro_virtual")
                        ->where("vir_id=:id and pro_status>=1 and pro_status<30",array(":id"=>$virRow["id"]))
                        ->queryRow();
                    if($boolRow){//存在变更中的操作
                        $this->writeRenewalLog($logFile, "  跳过虚拟合约ID: {$virRow['id']} (存在进行中的变更操作)");
                        unset($virRows[$key]);
                        continue;
                    }
                    $virIDs[]=$virRow["id"];
                    if(!in_array($virRow["cont_id"],$contIDs)){
                        $contIDs[]=$virRow["cont_id"];
                        $renewalList[$virRow["cont_id"]]=array('maxDate'=>'2020-01-31','minDate'=>'2019-01-01','list'=>array());
                    }
                    $proVirRow=$virRow;
                    unset($proVirRow["id"]);
                    $proVirRow["pro_id"]=0;
                    $proVirRow["vir_id"]=$virRow["id"];
                    $proVirRow["pro_type"]="C";
                    $proVirRow["pro_num"]=CGetName::getProNumByVir($proVirRow["vir_id"],$proVirRow["pro_type"]);
                    $proVirRow["pro_date"]=date("Y-m-d",strtotime($virRow['cont_end_dt']."+1 days"));
                    $proVirRow["pro_remark"]=$pro_remark;
                    $proVirRow["pro_status"]=30;
                    $proVirRow["vir_status"]=30;
                    $proVirRow["pro_change"]=empty($virRow["year_amt"])?0:$virRow["year_amt"];
                    $proVirRow["sign_type"]=2;//续约
                    $proVirRow["effect_date"]=$proVirRow["pro_date"];
                    $proVirRow["is_auto_renewal"]=1;//标识为自动续约
                    // 续约开始日期 = 原合约结束日期 + 1天，续约结束日期 = 续约开始日期 + 1年 - 1天
                    $proVirRow["cont_start_dt"]=$proVirRow["pro_date"];
                    $proVirRow["cont_end_dt"]=date("Y-m-d",strtotime($proVirRow['cont_start_dt']."+1 year -1 days"));
                    
                    $this->writeRenewalLog($logFile, "  虚拟合约ID: {$virRow['id']}, 原结束日期: {$virRow['cont_end_dt']}, 新开始: {$proVirRow['cont_start_dt']}, 新结束: {$proVirRow['cont_end_dt']}, 金额: {$virRow['year_amt']}");
                    $csvData[] = array(
                        'type' => '虚拟合约',
                        'cont_id' => $virRow["cont_id"],
                        'vir_id' => $virRow["id"],
                        'company_name' => isset($virRow["company_name"]) ? $virRow["company_name"] : '',
                        'product_name' => isset($virRow["product_name"]) ? $virRow["product_name"] : '',
                        'old_start_dt' => $virRow["cont_start_dt"],
                        'old_end_dt' => $virRow["cont_end_dt"],
                        'new_start_dt' => $proVirRow["cont_start_dt"],
                        'new_end_dt' => $proVirRow["cont_end_dt"],
                        'year_amt' => $virRow["year_amt"],
                        'month_amt' => isset($virRow["month_amt"]) ? $virRow["month_amt"] : '',
                        'vir_status' => $virRow["vir_status"],
                        'renewal_time' => $nowTime
                    );
                    
                    if($renewalList[$virRow["cont_id"]]['maxDate']<$proVirRow["cont_end_dt"]){
                        $renewalList[$virRow["cont_id"]]['minDate']=$proVirRow["cont_start_dt"];
                        $renewalList[$virRow["cont_id"]]['maxDate']=$proVirRow["cont_end_dt"];
                    }
                    $renewalList[$virRow["cont_id"]]['list'][]=$proVirRow;
                }

                if(empty($contIDs)){
                    return false;
                }
                $contIDs = implode(",",$contIDs);
                $contRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract")
                    ->where("id in ({$contIDs})")
                    ->queryAll();
                if($contRows){
                    foreach ($contRows as $contRow){
                        $cont_id = $contRow["id"];
                        if(isset($renewalList[$cont_id])){
                            $proRow = $contRow;
                            unset($proRow['id']);
                            $proRow["cont_id"]=$contRow["id"];
                            $proRow["pro_type"]="C";
                            $proRow["pro_num"]=CGetName::getProNumByCont($proRow["cont_id"],$proRow["pro_type"]);
                            $proRow["pro_date"]=$contRow["cont_end_dt"];
                            $proRow["pro_remark"]=$pro_remark;
                            $proRow["pro_status"]=30;
                            $proRow["cont_status"]=30;
                            $proRow["pro_change"]=empty($proRow["total_amt"])?0:$proRow["total_amt"];
                            $proRow["sign_type"]=2;//续约
                            $proRow["is_auto_renewal"]=1;//标识为自动续约
                            $proRow["cont_start_dt"]=$renewalList[$cont_id]["minDate"];//
                            $proRow["cont_end_dt"]=$renewalList[$cont_id]["maxDate"];//
                            
                            $this->writeRenewalLog($logFile, "\n主合约ID: {$cont_id}, 客户: {$contRow['cust_name']}, 原结束: {$contRow['cont_end_dt']}, 新开始: {$proRow['cont_start_dt']}, 新结束: {$proRow['cont_end_dt']}");
                            
                            // 收集主合约CSV数据
                            $csvData[] = array(
                                'type' => '主合约',
                                'cont_id' => $cont_id,
                                'vir_id' => '',
                                'company_name' => $contRow["cust_name"],
                                'product_name' => '主合约汇总',
                                'old_start_dt' => $contRow["cont_start_dt"],
                                'old_end_dt' => $contRow["cont_end_dt"],
                                'new_start_dt' => $proRow["cont_start_dt"],
                                'new_end_dt' => $proRow["cont_end_dt"],
                                'year_amt' => isset($contRow["total_amt"]) ? $contRow["total_amt"] : '',
                                'month_amt' => '',
                                'vir_status' => $contRow["cont_status"],
                                'renewal_time' => $nowTime
                            );
                            
                            Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contpro",$proRow);
                            $proRow["id"] = Yii::app()->db->getLastInsertID();
                            $renewalCount++;
                            Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",array(
                                "table_type"=>5,
                                "history_type"=>2,
                                "table_id"=>$contRow["id"],
                                "opr_id"=>$proRow["id"],
                                "history_html"=>"<span>{$pro_remark}</span>",
                            ));//
                            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                                "pro_code"=>"CCR".(10000+$proRow["id"])
                            ),"id=:id",array(":id"=>$proRow["id"]));//
                            if(!empty($renewalList[$cont_id]["list"])){
                                foreach ($renewalList[$cont_id]["list"] as $proVirRow){
                                    $proVirRow["pro_id"]=$proRow["id"];
                                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contpro_virtual",$proVirRow);
                                    $proVirRow["id"] = Yii::app()->db->getLastInsertID();
                                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_history",array(
                                        "table_type"=>7,
                                        "history_type"=>2,
                                        "table_id"=>$proVirRow["vir_id"],
                                        "opr_id"=>$proVirRow["id"],
                                        "history_html"=>"<span>{$pro_remark}</span>",
                                    ));//
                                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro_virtual",array(
                                        "pro_code"=>"CCR".(10000+$proVirRow["id"])
                                    ),"id=:id",array(":id"=>$proVirRow["id"]));//

                                    // 计算 month_amt：从 service_fre_json 中解析 fre_month
                                    $month_amt = null;
                                    if(!empty($proVirRow["service_fre_json"])){
                                        $freJson = json_decode($proVirRow["service_fre_json"], true);
                                        if(isset($freJson["fre_month"]) && !empty($freJson["fre_month"])){
                                            $month_amt = floatval($freJson["fre_month"]);
                                        }
                                    }
                                    // 如果 service_fre_json 中没有 fre_month，且 service_fre_type=1（固定频次）则使用 year_amt 计算
                                    if($month_amt === null && intval($proVirRow["service_fre_type"]) == 1){
                                        // 固定频次：month_amt = year_amt / 12
                                        if(!empty($proVirRow["year_amt"])){
                                            $month_amt = round(floatval($proVirRow["year_amt"]) / 12, 2);
                                        }
                                    }

                                    $updateData = array(
                                        "sign_type"=>$proVirRow["sign_type"],
                                        "cont_start_dt"=>$proVirRow["cont_start_dt"],
                                        "cont_end_dt"=>$proVirRow["cont_end_dt"],
                                    );
                                    // 计算出了 month_amt，则更新
                                    if($month_amt !== null){
                                        $updateData["month_amt"] = $month_amt;
                                    }
                                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_virtual", $updateData, "id=:id", array(":id"=>$proVirRow["vir_id"]));//
                                }
                            }

                            if($renewalList[$cont_id]["maxDate"]>$contRow["cont_end_dt"]){
                                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract",array(
                                    "cont_end_dt"=>$renewalList[$cont_id]["maxDate"],
                                    "sign_type"=>2,
                                ),"id=:id",array(":id"=>$cont_id));//
                            }
                        }
                    }
                }

                //发送续约消息给派单系统
                $this->writeRenewalLog($logFile, "\n开始发送续约消息到派单系统...");
                $uVirModel = new CurlNotesByVirPro();
                $uVirModel->pro_type="C";
                $virIDs = implode(",",$virIDs);
                $uVirModel->sendAllVirByIDsAndUpdate($virIDs);
                $this->writeRenewalLog($logFile, "派单系统消息发送完成");
                
                // 生成CSV文件
                if(!empty($csvData)){
                    $this->generateRenewalCSV($csvFile, $csvData);
                    $this->writeRenewalLog($logFile, "\nCSV报表已生成: ".$csvFile);
                    $this->writeRenewalLog($logFile, "CSV记录数: ".count($csvData)." 条");
                }
                
                $this->writeRenewalLog($logFile, "\n========== 自动续约完成 ==========");
                $this->writeRenewalLog($logFile, "实际续约主合约数量: {$renewalCount}");
                $this->writeRenewalLog($logFile, "涉及虚拟合约ID: ".$virIDs);
                $this->writeRenewalLog($logFile, "结束时间: ".date("Y-m-d H:i:s"));
                $this->writeRenewalLog($logFile, "========================================\n");
            } else {
                $this->writeRenewalLog($logFile, "没有符合条件的合约需要续约");
                $this->writeRenewalLog($logFile, "========================================\n");
            }
        } else {
            $this->writeRenewalLog($logFile, "未找到续约配置(computeRenewal)或配置值为0");
            $this->writeRenewalLog($logFile, "========================================\n");
        }
    }

    //市场营销的资料超过15天自动退回
    private function marketCompanyForBack(){
        $endDate = date("Y-m-d", strtotime(" - 15 day"));
        echo "\n"."marketCompanyForBack start: allot_date < {$endDate}"."\n";
        //分配中的记录如果超过15天，系统自动退回(地区)
        $marketArea = new MarketAreaForm();
        $marketArea->systemBackForLongDate($endDate);
        //分配中的记录如果超过15天，系统自动退回(销售)
        $marketSales = new MarketSalesForm();
        $marketSales->systemBackForLongDate($endDate,3);//销售自动退回
        $marketSales->systemBackForLongDate($endDate,1);//KA销售自动退回

        echo "\n"."marketCompanyForBack End"."\n";
    }

    //销售俱乐部每天刷新一次
	private function resetClubSales(){
	    $year = date("Y");
	    $month = date("n");
        $month_type = $month>6?2:1;
	    $model = new ClubSalesList();
	    $model->clubSalesAllSave($year,$month_type);//刷新本年度
        /* 2023/07/10月修改，不需要刷新上一个年度的数据
        $year = date("Y",strtotime("-6 months"));
        $month = date("n",strtotime("-6 months"));
        $month_type = $month>6?2:1;
        unset($model);
        $model = new ClubSalesList();
	    $model->clubSalesAllSave($year,$month_type);//刷新上一个年度
        */
    }

    //终止客户邮件提醒
	private function shiftEmailHint(){
	    if(date("w")==="5"){ //每週五發郵件提醒
            $shiftList = array();
            $this->setShiftList($shiftList);
            $this->sendForShiftList($shiftList);
        }
    }

    //发送终止客户邮件
    private function sendForShiftList($shiftList){
        $systemId = Yii::app()->params['systemId'];
	    if(!empty($shiftList)){
            $subject = "超6个月终止客户回访提醒";
            $messageEpr="<p>以下客户停单已超6个月，请及时电联或上门回访</p>";
            $messageEpr.="<p>回访内容请在LBS系统-销售系统-终止客户-终止客户回访中完成</p>";
            $messageEpr.="<p>客户服务的基本信息：</p>";
            $tableHead="<table border='1' width='800px'><thead><tr>";
            $tableHead.="<th width='10%'>城市</th>";
            $tableHead.="<th width='30%'>客户名称</th>";
            $tableHead.="<th width='10%'>停单日期</th>";
            $tableHead.="<th width='10%'>客户类别</th>";
            $tableHead.="<th width='10%'>性质</th>";
            $tableHead.="<th width='30%'>业务员</th>";
            $tableHead.="</tr></thead><tbody>";
            $tableEnd="</tbody></table>";
            $email = new Email($subject,"",$subject);
	        foreach ($shiftList as $city=>$staffRows){
	            $cityMessage = "";
	            foreach ($staffRows as $staffId => $rows){
	                $staffMessage="";
	                foreach ($rows as $row){
	                    $message="<tr>";
	                    $message.="<td>{$row['city_name']}</td>";
	                    $message.="<td>{$row['company_name']}</td>";
	                    $message.="<td>{$row['status_dt']}</td>";
	                    $message.="<td>{$row['cust_type']}</td>";
	                    $message.="<td>{$row['nature_name']}</td>";
	                    $message.="<td>{$row['salesman']}</td>";
	                    $message.="</tr>";
	                    $staffMessage.=$message;//添加到员工邮件
                        $cityMessage.=$message;//添加到城市邮件
                    }
                    $email->resetToAddr();
                    if(!empty($staffMessage)){
                        $message = $messageEpr.$tableHead.$staffMessage.$tableEnd;
                        $email->setMessage($message);
                        $email->addEmailToStaffId($staffId);
                        if(!empty($email->getToAddr())){
                            $email->sent("销售系统",$systemId);
                        }
                    }
                }
                $email->resetToAddr();
                if(!empty($cityMessage)){
                    $message = $messageEpr.$tableHead.$cityMessage.$tableEnd;
                    $email->setMessage($message);
                    $email->addEmailToCity($city);
                    if(!empty($email->getToAddr())){
                        $email->sent("销售系统",$systemId);
                    }
                }
            }

            //需要额外发给区域性负责人
            $this->emailEprStopNoneAndCharge($email,$shiftList,$messageEpr,$tableHead,$tableEnd,$systemId);
        }

    }

    //获取终止客户邮件
    private function setShiftList(&$shiftList){
        $suffix = Yii::app()->params['envSuffix'];
        $expr_sql = StopOtherList::getExprSql();
        $rows = Yii::app()->db->createCommand()
            ->select("a.id as service_id,a.city,a.status_dt,a.cust_type,a.nature_type,a.company_name,a.salesman,a.salesman_id,b.id,b.staff_id,city.name as city_name")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back b","a.id=b.service_id ")
            ->leftJoin("security{$suffix}.sec_city city","city.code=a.city ")
            ->where("a.status = 'T' and a.company_id is not NULL and a.salesman_id !=0 and b.back_date is null {$expr_sql}")
            ->order("a.city asc,a.salesman_id asc")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $row["status_dt"]=General::toDate($row["status_dt"]);
                $row["cust_type"]=StopOtherForm::getCustTypeStr($row["cust_type"]);
                $row["nature_name"]=StopOtherForm::getNatureTypeStr($row["nature_type"]);
                $staff_id = empty($row["staff_id"])?$row["salesman_id"]:$row["staff_id"];//判断是否转移数据
                if(!key_exists($row["city"],$shiftList)){
                    $shiftList[$row["city"]] = array();
                }
                if(!key_exists($staff_id,$shiftList[$row["city"]])){
                    $shiftList[$row["city"]][$staff_id] = array();
                }
                $shiftList[$row["city"]][$staff_id][]=$row;
            }
        }
    }

    //终止客户的再次回访邮件提醒
    private function shiftAgainEmailHint(){
        $shiftList = array();
        $this->setShiftAgainList($shiftList);
        $this->sendForShiftAgainList($shiftList);
    }

    //发送终止客户的再次回访邮件
    private function sendForShiftAgainList($shiftList){
        $systemId = Yii::app()->params['systemId'];
	    if(!empty($shiftList)){
            $subject = "终止客户再次回访提醒";
            $messageEpr="<p>再次回访列表还有未完成的记录，请及时回访并登记到销售表系统-终止客户-再次回访处，谢谢</p>";
            $messageEpr.="<p>客户服务的上次回访信息：</p>";
            $tableHead="<table border='1' width='1000px'><thead><tr>";
            $tableHead.="<th width='10%'>城市</th>";
            $tableHead.="<th width='30%'>客户名称</th>";
            $tableHead.="<th width='10%'>停单日期</th>";
            $tableHead.="<th width='10%'>回访日期</th>";
            $tableHead.="<th width='15%'>回访状态</th>";
            $tableHead.="<th width='25%'>业务员</th>";
            $tableHead.="</tr></thead><tbody>";
            $tableEnd="</tbody></table>";
            $email = new Email($subject,"",$subject);
	        foreach ($shiftList as $city=>$staffRows){
	            $cityMessage = "";
	            foreach ($staffRows as $staffId => $rows){
	                $staffMessage="";
	                foreach ($rows as $row){
	                    $message="<tr>";
	                    $message.="<td>{$row['city_name']}</td>";
	                    $message.="<td>{$row['company_name']}</td>";
	                    $message.="<td>{$row['status_dt']}</td>";
	                    $message.="<td>{$row['back_date']}</td>";
	                    $message.="<td>{$row['type_name']}</td>";
	                    $message.="<td>{$row['salesman']}</td>";
	                    $message.="</tr>";
	                    $staffMessage.=$message;//添加到员工邮件
                        $cityMessage.=$message;//添加到城市邮件
                    }
                    $email->resetToAddr();
                    if(!empty($staffMessage)){
                        $message = $messageEpr.$tableHead.$staffMessage.$tableEnd;
                        $email->setMessage($message);
                        $email->addEmailToStaffId($staffId);
                        if(!empty($email->getToAddr())){
                            $email->sent("销售系统",$systemId);
                        }
                    }
                }
                $email->resetToAddr();
                if(!empty($cityMessage)){
                    $message = $messageEpr.$tableHead.$cityMessage.$tableEnd;
                    $email->setMessage($message);
                    $email->addEmailToCity($city);
                    if(!empty($email->getToAddr())){
                        $email->sent("销售系统",$systemId);
                    }
                }
            }

            //需要额外发给区域性负责人
            $this->emailEprStopNoneAndCharge($email,$shiftList,$messageEpr,$tableHead,$tableEnd,$systemId,false);
        }
    }

    //区域性员工需要收到管辖下的未回访邮件
    private function emailEprStopNoneAndCharge($email,$shiftList,$messageEpr,$tableHead,$tableEnd,$systemId,$bool=true){
        $userList = $email->getUserListToPrefixAndReady("SC06");
        if($userList){
            foreach ($userList as $user){
                $userEmailMessage = "";
                //管辖下的所有城市
                $cityList = $email->getAllCityToMaxCity($user["city"]);
                foreach ($cityList as $minCity){
                    if(key_exists($minCity,$shiftList)){
                        //$shiftList[city][staffId][];
                        foreach ($shiftList[$minCity] as $staffRows){
                            foreach ($staffRows as $staffRow){
                                if($bool){//未回访
                                    $userEmailMessage.="<tr>";
                                    $userEmailMessage.="<td>{$staffRow['city_name']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['company_name']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['status_dt']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['cust_type']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['nature_name']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['salesman']}</td>";
                                    $userEmailMessage.="</tr>";
                                }else{//再次回访
                                    $userEmailMessage.="<tr>";
                                    $userEmailMessage.="<td>{$staffRow['city_name']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['company_name']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['status_dt']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['back_date']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['type_name']}</td>";
                                    $userEmailMessage.="<td>{$staffRow['salesman']}</td>";
                                    $userEmailMessage.="</tr>";
                                }
                            }
                        }
                    }
                }

                if(!empty($userEmailMessage)){
                    $email->resetToAddr();
                    $message = $messageEpr.$tableHead.$userEmailMessage.$tableEnd;
                    $email->setMessage($message);
                    $email->addToAddrEmail($user["email"]);
                    $email->addToAddrUser($user["username"]);
                    if(!empty($email->getToAddr())){
                        $email->sent("销售系统",$systemId);
                    }
                }
            }
        }
    }

    //获取终止客户的再次回访邮件
    private function setShiftAgainList(&$shiftList){
        $suffix = Yii::app()->params['envSuffix'];
        $nowDate = date("Y-m-d");
        $rows = Yii::app()->db->createCommand()
            ->select("f.type_name,f.again_day,a.company_name,a.salesman,a.status_dt,info.back_date,a.city,b.staff_id,a.salesman_id,city.name as city_name")
            ->from("sal_stop_back_info info")
            ->leftJoin("sal_stop_type f","f.id=info.back_type")
            ->leftJoin("sal_stop_back b","b.id=info.stop_id")
            ->leftJoin("swoper{$suffix}.swo_service a","a.id=b.service_id")
            ->leftJoin("security{$suffix}.sec_city city","city.code=a.city ")
            ->where("info.end_bool=0 and f.again_type=1 and date_add(info.back_date, interval f.again_day day)='{$nowDate}'")
            ->order("a.city asc,a.salesman_id asc")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $row["status_dt"]=General::toDate($row["status_dt"]);
                $row["back_date"]=General::toDate($row["back_date"]);
                $staff_id = empty($row["staff_id"])?$row["salesman_id"]:$row["staff_id"];//判断是否转移数据
                if(!key_exists($row["city"],$shiftList)){
                    $shiftList[$row["city"]] = array();
                }
                if(!key_exists($staff_id,$shiftList[$row["city"]])){
                    $shiftList[$row["city"]][$staff_id] = array();
                }
                $shiftList[$row["city"]][$staff_id][]=$row;
            }
        }
    }
    
    //写入续约日志到文件
    private function writeRenewalLog($logFile, $message, $echoOutput = true){
        $logMessage = $message."\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        if($echoOutput){
            echo $logMessage;
        }
    }
    
    //生成续约CSV报表
    private function generateRenewalCSV($csvFile, $csvData){
        $fp = fopen($csvFile, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        // CSV表头
        $headers = array(
            '类型',
            '主合约ID',
            '虚拟合约ID',
            '客户名称',
            '产品名称',
            '原开始日期',
            '原结束日期',
            '新开始日期',
            '新结束日期',
            '年度金额',
            '月度金额',
            '状态',
            '续约时间'
        );
        fputcsv($fp, $headers);
        
        // 写入数据行
        foreach($csvData as $row){
            $dataRow = array(
                $row['type'],
                $row['cont_id'],
                $row['vir_id'],
                $row['company_name'],
                $row['product_name'],
                $row['old_start_dt'],
                $row['old_end_dt'],
                $row['new_start_dt'],
                $row['new_end_dt'],
                $row['year_amt'],
                $row['month_amt'],
                $row['vir_status'],
                $row['renewal_time']
            );
            fputcsv($fp, $dataRow);
        }
        
        fclose($fp);
    }
}
?>
