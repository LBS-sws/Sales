<?php

class ClueASync{
	public $search_city=array('SZ');//'CD','SZ',
    public $start_date="2025-10-22";
    public $end_date="2025-11-26";
    public $setList=array();//sql查询后的配置

    public $pinyin;
    public function __construct()
    {
        $phpExcelPath = Yii::getPathOfAlias('ext.pinyin');
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'Autoloader.php');
        $this->pinyin = new Pinyin(); // 默认
        $this->setList["hrEmploy"]=array();//人事系统员工表
        $this->setList["districtList"]=array();//区域查询
        $this->setList["classList"]=array();//客户类型查询
    }


	public function syncByKA(){
        $suffix = Yii::app()->params['envSuffix'];
        $dateSql = " and a.kam_id in (3743,3744,4428,4429,4460,4461,4463,4464,4864,5315,5563,5619,5640,5704,5826,6038,6110,6114,6186,6397)";//只同步林娟
        $cityCodeList=array(
            "3743"=>"HNKA",
            "3744"=>"HDKA",
            "4428"=>"QDMS",
            "4429"=>"XBKA",
            "4460"=>"HDKA",
            "4461"=>"SHKA",
            "4463"=>"XBKA",
            "4464"=>"XBKA",
            "4864"=>"HNKA",
            "5315"=>"HNKA",
            "5563"=>"XBKA",
            "5619"=>"HDKA",
            "5640"=>"HDKA",
            "5704"=>"XBKA",
            "5826"=>"HDKA",
            "6038"=>"XBKA",
            "6110"=>"XBKA",
            "6114"=>"HDKA",
            "6186"=>"HNKA",
            "6397"=>"SHKA",
        );
        $cityCode="HNKA";
        $serviceType=array("char"=>"C","name"=>"虫害防制");
        $busine_id=$serviceType['char'];
        $busine_id_text=$serviceType['name'];
        $kaRows = Yii::app()->db->createCommand()->select("a.*,CONCAT('{$cityCode}') as city_code")->from("sales{$suffix}.sal_ka_bot a")
            ->leftJoin("sales{$suffix}.sal_ka_link b","a.link_id=b.id")
            //->leftJoin("hr{$suffix}.hr_employee f","a.kam_id=f.id")
            ->where("b.rate_num!=100 {$dateSql}")->order("a.id asc")->queryAll();
        if($kaRows){
            foreach($kaRows as $kaRow){
                $kaRow['apply_date'] = date("Y-m-d",strtotime($kaRow['apply_date']));
                $employee_id = "".$kaRow['kam_id'];
                if(isset($cityCodeList[$employee_id])){
                    $kaRow["city_code"]=$cityCodeList[$employee_id];
                }
                //判断是否存在线索
                $clueRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue")
                    ->where("cust_name=:name",array(
                        ':name'=>$kaRow['customer_name']
                    ))->queryRow();
                if(!$clueRow){
                    $insertArr = $this->getInsertClueListByKa($kaRow);
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue",$insertArr);
                    $clue_id = Yii::app()->db->getLastInsertID();
                    Yii::app()->db->createCommand()->insert("sal_clue_history",array(
                        "table_id"=>$clue_id,
                        "table_type"=>1,
                        "history_type"=>1,
                        "history_html"=>"<span>KA项目同步</span>",
                        "lcu"=>$insertArr['lcu'],
                    ));

                    ClueUAreaForm::saveUAreaData($clue_id,$insertArr['city']);
                    ClueUStaffForm::saveUStaffData($clue_id,$insertArr['rec_employee_id']);
                    if(!empty($insertArr['cust_person'])&&!empty($insertArr['cust_tel'])){
                        Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_person",array(
                            "clue_id"=>$clue_id,
                            "clue_store_id"=>0,
                            "cust_person"=>$insertArr['cust_person'],
                            "cust_tel"=>$insertArr['cust_tel'],
                            "lcu"=>$insertArr['lcu'],
                            "lcd"=>$insertArr['lcd'],
                        ));
                        $cust_id = Yii::app()->db->getLastInsertID();
                        Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_person",array(
                            "person_code"=>ClientPersonForm::computeCodeX($clue_id,0,$cust_id),
                        ),"id=:id",array(":id"=>$cust_id));
                    }
                }else{
                    $clue_id = $clueRow['id'];
                }
                //判断是否存在商机
                $clueServiceRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_service")
                    ->where("clue_id=:clue_id and busine_id=:busine_id and create_staff=:create_staff",array(
                        ':create_staff'=>$employee_id,
                        ':clue_id'=>$clue_id,
                        ':busine_id'=>$busine_id
                    ))->queryRow();
                if(!$clueServiceRow){
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_service",array(
                        "clue_id"=>$clue_id,
                        "clue_type"=>2,
                        "visit_type"=>1,
                        "busine_id"=>$busine_id,
                        "busine_id_text"=>$busine_id_text,
                        "visit_obj"=>1,
                        "visit_obj_text"=>"首次",
                        "sign_odds"=>50,
                        "create_staff"=>$employee_id,
                        "service_status"=>1,
                        'lcu'=>$kaRow["lcu"],
                        'lcd'=>$kaRow["lcd"],
                    ));
                    $clue_service_id = Yii::app()->db->getLastInsertID();
                }else{
                    $clue_service_id = $clueServiceRow['id'];
                }
                //判断是否存在跟进
                $infoRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_ka_bot_info")
                    ->where("bot_id=:bot_id and info_date is not null",array(
                        ':bot_id'=>$kaRow['id'],
                    ))->queryAll();
                if($infoRows){
                    foreach($infoRows as $infoRow){
                        $clueFlowRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_flow")
                            ->where("clue_type=2 and table_id=:table_id",array(':table_id'=>$infoRow['id']))->queryRow();
                        if(!$clueFlowRow){
                            Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_flow",array(
                                "clue_id"=>$clue_id,
                                "clue_type"=>2,
                                "clue_service_id"=>$clue_service_id,
                                "visit_date"=>$infoRow['info_date'],
                                "create_staff"=>$employee_id,
                                "sign_odds"=>50,
                                "visit_text"=>$infoRow['info_text'],
                                "visit_obj"=>1,
                                "visit_obj_text"=>"首次",
                                "table_id"=>$infoRow['id'],
                                'lcu'=>$infoRow["lcu"],
                                'lcd'=>$infoRow["lcd"],
                            ));
                        }
                    }
                }
            }
        }
    }

	public function syncByVisit(){
	    return false;
        $suffix = Yii::app()->params['envSuffix'];
		$startDt=$this->start_date;
		$endDt=$this->end_date;
        $whereSql ="visit_dt BETWEEN '{$startDt}' and '{$endDt}' AND visit_obj NOT LIKE '%10%'";
        if(!empty($this->search_city)){
            $whereSql.= " and city in ('".implode("','",$this->search_city)."')";
        }
        $totalNum = Yii::app()->db->createCommand()->select("count(id)")->from("sales{$suffix}.sal_visit")
            ->where($whereSql)->queryScalar();
        echo "total:{$totalNum}\n";
        $this->getVisitRows($whereSql,1,5000,$totalNum);//每次执行5000条数据
	}

	protected function getVisitRows($whereSql,$pageNum,$maxPage,$totalNum){
        $suffix = Yii::app()->params['envSuffix'];
        $startNum=($pageNum-1)*$maxPage;
        if($startNum<$totalNum){
            echo "page:{$pageNum}\n";
            $visitRows = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_visit")
                ->where($whereSql)->order("id asc")->limit($maxPage,$startNum)->queryAll();
            $this->addVisitRows($visitRows);
            $pageNum++;
            $this->getVisitRows($whereSql,$pageNum,$maxPage,$totalNum);
        }
    }

	protected function addVisitRows($visitRows){
        $suffix = Yii::app()->params['envSuffix'];
        $serviceType=array(1=>array("char"=>"A","name"=>"洁净"),2=>array("char"=>"C","name"=>"虫害防制"));
        if($visitRows){
            foreach($visitRows as $visitRow){
                $visitRow['visit_dt'] = date("Y-m-d",strtotime($visitRow['visit_dt']));
                if(!key_exists($visitRow['username'],$this->setList["hrEmploy"])){
                    $this->setList["hrEmploy"][$visitRow['username']]=CGetName::getEmployeeIDByUserName($visitRow['username']);
                }
                $employee_id = $this->setList["hrEmploy"][$visitRow['username']];
                $visitRow["employee_id"]=$employee_id;
                $visitRow['service_type'] = empty($visitRow['service_type'])?'["1"]':$visitRow['service_type'];
                $service_type = json_decode($visitRow['service_type'],true);
                $service_type = is_array($service_type)?current($service_type):"";
                $service_type = is_array($service_type)?current($service_type):"";
                $service_type = "".$service_type;

                $visitRow['visit_obj'] = json_decode($visitRow['visit_obj'],true);
                $visit_obj = is_array($visitRow['visit_obj'])?implode(',',$visitRow['visit_obj']):1;
                $visit_obj = empty($visit_obj)?1:$visit_obj;

                //判断是否存在线索
                $clueRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue")
                    ->where("cust_name=:name",array(
                        ':name'=>$visitRow['cust_name']
                    ))->queryRow();
                if(!$clueRow){
                    $insertArr = $this->getInsertClueListByVisit($visitRow);
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue",$insertArr);
                    $clue_id = Yii::app()->db->getLastInsertID();

                    ClueUAreaForm::saveUAreaData($clue_id,$insertArr['city']);
                    ClueUStaffForm::saveUStaffData($clue_id,$insertArr['rec_employee_id']);
                    if(!empty($insertArr['cust_person'])&&!empty($insertArr['cust_tel'])){
                        Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_person",array(
                            "clue_id"=>$clue_id,
                            "clue_store_id"=>0,
                            "cust_person"=>$insertArr['cust_person'],
                            "cust_tel"=>$insertArr['cust_tel'],
                            "lcu"=>$insertArr['lcu'],
                            "lcd"=>$insertArr['lcd'],
                        ));
                        $cust_id = Yii::app()->db->getLastInsertID();
                        Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_person",array(
                            "person_code"=>ClientPersonForm::computeCodeX($clue_id,0,$cust_id),
                        ),"id=:id",array(":id"=>$cust_id));
                    }
                }else{
                    $clue_id = $clueRow['id'];
                }
                $busine_id=$serviceType[1]["char"];
                $busine_id_text=$serviceType[1]["name"];
                if(isset($serviceType[$service_type])){
                    $busine_id=$serviceType[$service_type]["char"];
                    $busine_id_text=$serviceType[$service_type]["name"];
                }
                //判断是否存在商机
                $clueServiceRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_service")
                    ->where("clue_id=:clue_id and busine_id=:busine_id and create_staff=:create_staff",array(
                        ':create_staff'=>$employee_id,
                        ':clue_id'=>$clue_id,
                        ':busine_id'=>$busine_id
                    ))->queryRow();
                if(!$clueServiceRow){
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_service",array(
                        "clue_id"=>$clue_id,
                        "clue_type"=>1,
                        "visit_type"=>$visitRow['visit_type'],
                        "busine_id"=>$busine_id,
                        "busine_id_text"=>$busine_id_text,
                        "visit_obj"=>$visit_obj,
                        "visit_obj_text"=>$visitRow['visit_obj_name'],
                        "sign_odds"=>$visitRow['sign_odds'],
                        "create_staff"=>$employee_id,
                        "service_status"=>1,
                        'lcu'=>$visitRow["lcu"],
                        'lcd'=>$visitRow["lcd"],
                    ));
                    $clue_service_id = Yii::app()->db->getLastInsertID();
                }else{
                    $clue_service_id = $clueServiceRow['id'];
                }
                //判断是否存在跟进
                $clueFlowRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_clue_flow")
                    ->where("clue_type=1 and table_id=:table_id",array(
                        ':table_id'=>$visitRow['id'],
                    ))->queryRow();
                if(!$clueFlowRow){
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_clue_flow",array(
                        "clue_id"=>$clue_id,
                        "clue_type"=>1,
                        "clue_service_id"=>$clue_service_id,
                        "visit_date"=>$visitRow['visit_dt'],
                        "create_staff"=>$employee_id,
                        "sign_odds"=>$visitRow['sign_odds'],
                        "visit_text"=>$visitRow['remarks'],
                        "visit_obj"=>$visit_obj,
                        "visit_obj_text"=>$visitRow['visit_obj_name'],
                        "table_id"=>$visitRow['id'],
                        'lcu'=>$visitRow["lcu"],
                        'lcd'=>$visitRow["lcd"],
                    ));
                }
            }
        }
    }

    protected function getInsertClueListByVisit($visitRow)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $visitRow['district']="".$visitRow['district'];
        $visitRow['cust_type']="".$visitRow['cust_type'];
        if(!isset($this->setList["districtList"][$visitRow['district']])){
            $districtList = Yii::app()->db->createCommand()->select("nal_id,nal_tree_names")->from("sales{$suffix}.sal_cust_district")
                ->where("id=:id",array(':id'=>$visitRow['district']))->queryRow();
            $districtList = $districtList?$districtList:array("nal_id"=>null,"nal_tree_names"=>null);
            $this->setList["districtList"][$visitRow['district']]=$districtList;
        }
        $districtList = $this->setList["districtList"][$visitRow['district']];
        if(!isset($this->setList["classList"][$visitRow['cust_type']])){
            $class_name = Yii::app()->db->createCommand()->select("name")->from("sales{$suffix}.sal_cust_type")
                ->where("id=:id",array(':id'=>$visitRow['cust_type']))->queryRow();
            $class_name = $class_name?$class_name['name']:'';
            $this->setList["classList"][$visitRow['cust_type']]=$this->getCustClassList($class_name);
        }
        $custClassList = $this->setList["classList"][$visitRow['cust_type']];
        $employee_id = $visitRow['employee_id'];
        $codeList = $this->computeClueCode($visitRow['cust_name']);
        $insertArr=array(
            'abbr_code'=>$codeList['abbr_code'],
            'clue_code'=>$codeList['clue_code'],
            'cust_name'=>$visitRow['cust_name'],
            'service_type'=>$visitRow['service_type'],
            'city'=>$visitRow['city'],
            'table_type'=>1,
            'clue_type'=>1,
            'clue_status'=>1,
            'rec_type'=>1,
            'yewudalei'=>1,
            'group_bool'=>"N",
            'rec_employee_id'=>$employee_id,
            'cust_class'=>$custClassList["id"],
            'cust_class_group'=>$custClassList["group_id"],
            'district'=>empty($districtList["nal_id"])?null:$districtList["nal_id"],
            'address'=>$districtList["nal_tree_names"],
            'street'=>$visitRow["street"],
            'cust_person'=>$visitRow["cust_person"],
            'cust_tel'=>$visitRow["cust_tel"],
            'cust_person_role'=>$visitRow["cust_person_role"],
            'lcu'=>$visitRow["lcu"],
            'lcd'=>$visitRow["lcd"],
            'entry_date'=>$visitRow['visit_dt'],
        );
        return $insertArr;
    }

    protected function getInsertClueListByKa($kaRow)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $employee_id = $kaRow['kam_id'];
        $class_name = Yii::app()->db->createCommand()->select("pro_name")->from("sales{$suffix}.sal_ka_class")
            ->where("id=:id",array(':id'=>$kaRow['class_id']))->queryRow();
        $class_name = $class_name?$class_name['pro_name']:'';
        $custClassList=$this->getCustClassList($class_name);
        $districtList=$this->getDistrictListByCity($kaRow['city_code']);
        $kaRow["source_text"] = is_numeric($kaRow["source_text"])?$kaRow["source_text"]:null;
        $kaRow["support_user"] = is_numeric($kaRow["support_user"])?$kaRow["support_user"]:null;
        //$codeList = $this->computeClueCode($kaRow['customer_name']);
        $insertArr=array(
            //'abbr_code'=>$codeList['abbr_code'],
            'clue_code'=>$kaRow['customer_no'],
            'cust_name'=>$kaRow['customer_name'],
            'service_type'=>'["1"]',
            'city'=>$kaRow['city_code'],
            'table_type'=>1,
            'clue_type'=>2,
            'clue_status'=>1,
            'rec_type'=>1,
            'yewudalei'=>2,
            'group_bool'=>"Y",
            'rec_employee_id'=>$employee_id,
            'cust_class'=>$custClassList["id"],
            'cust_class_group'=>$custClassList["group_id"],
            'district'=>$districtList["id"],
            'address'=>$districtList["address"],
            'cust_person'=>$kaRow["work_user"],
            'cust_tel'=>$kaRow["work_phone"],
            'cust_email'=>$kaRow["work_email"],
            'cust_address'=>$kaRow["contact_adr"],
            'cust_type'=>empty($kaRow["source_id"])?null:$kaRow["source_id"],
            'clue_source'=>empty($kaRow["source_text"])?null:$kaRow["source_text"],
            'cust_ka_class'=>empty($kaRow["class_id"])?null:$kaRow["class_id"],
            'cust_level'=>empty($kaRow["level_id"])?0:$kaRow["level_id"],
            'busine_id'=>$kaRow["busine_id"],
            'support_user'=>empty($kaRow["support_user"])?null:$kaRow["support_user"],
            'talk_city_id'=>empty($kaRow["talk_city_id"])?null:$kaRow["talk_city_id"],
            'lcu'=>$kaRow["lcu"],
            'lcd'=>$kaRow["lcd"],
            'ka_id'=>$kaRow["id"],
            'entry_date'=>$kaRow['apply_date'],
        );
        return $insertArr;
    }

    protected function getDistrictList($district_name,$city='')
    {
        $suffix = Yii::app()->params['envSuffix'];
        $list = array("id"=>"110101000000","address"=>"北京市/北京市/东城区",'searchBool'=>false);
        if(!empty($district_name)){
            $row = Yii::app()->db->createCommand()->select("id,tree_names")->from("sales{$suffix}.sal_national_area")
                ->where("type=3 and tree_names like '%{$district_name}%'")->order("id asc")->queryRow();
            if($row){
                $list["id"]=$row["id"];
                $list["address"]=$row["tree_names"];
                $list["searchBool"]=true;
            }
        }
        if(!$list["searchBool"]&&!empty($city)){
            return $this->getDistrictListByCity($city);
        }
        return $list;

    }

    protected function getDistrictListByCity($city){
        $suffix = Yii::app()->params['envSuffix'];
        $list = array("id"=>"110101000000","address"=>"北京市/北京市/东城区",'searchBool'=>false);
        $city_name = Yii::app()->db->createCommand()->select("name")->from("security{$suffix}.sec_city")
            ->where("code=:code",array(':code'=>$city))->queryRow();
        if($city_name){
            $city_name = $city_name["name"];
            $row = Yii::app()->db->createCommand()->select("id,tree_names")->from("sales{$suffix}.sal_national_area")
                ->where("type=3 and tree_names like '%{$city_name}%'")->order("id asc")->queryRow();
            if($row){
                $list["id"]=$row["id"];
                $list["address"]=$row["tree_names"];
                $list["searchBool"]=true;
            }
        }
        return $list;
    }

    protected function getCustClassList($class_name)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $list = array("id"=>null,"group_id"=>null);
        if(!empty($class_name)){
            $row = Yii::app()->db->createCommand()->select("name,nature_id,id")->from("swoper{$suffix}.swo_nature_type")
                ->where("name like '%{$class_name}%'")->queryRow();
            if($row){
                $list["id"] = $row["id"];
                $list["group_id"] = $row["nature_id"];
            }
        }
        return $list;
    }

    protected function computeClueCode($cust_name){
        $pinyin = $this->pinyin; // 默认
        $full_name = $cust_name;
        $abbr = $pinyin->abbr($full_name);
        $abbr = empty($abbr)?$cust_name:$abbr;
        $abbr = preg_replace("/[^a-zA-Z]/", "", $abbr);
        if(empty($abbr)){
            $abbr = preg_replace("/[^a-zA-Z]/", "", $cust_name);
        }
        if(empty($abbr)){
            $abbr = "NONE";
        }
        $abbr = mb_strlen($abbr)>4?mb_substr($abbr,0,4,'UTF-8'):$abbr;
        $abbr = strtoupper($abbr);
        $row = Yii::app()->db->createCommand()->select("count(*) as sum")
            ->from("sal_clue")->where("abbr_code=:abbr_code",array(":abbr_code"=>$abbr))->queryRow();
        $num = $row?$row["sum"]:0;
        $charNum = floor($num/10)+65;
        $num = floor($num%10);
        $clue_code=$abbr.chr($charNum).$num;
        $abbr_code=$abbr;
        return array("abbr_code"=>$abbr_code,"clue_code"=>$clue_code);
    }
}
