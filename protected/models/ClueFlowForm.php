<?php

class ClueFlowForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $clue_id;
	public $clue_type;
	public $clue_service_id;
	public $create_staff;
	public $visit_date;
	public $last_visit_date;
	public $sign_odds=0;
	public $visit_text;
	public $rpt_bool=0;
	public $update_bool=1;//允许修改
	public $visit_obj;
	public $city;
	public $visit_obj_text;
	public $predict_date;
	public $predict_amt;
	public $lbs_main;
	public $survey_bool=0;
	public $no_intention_id;
	public $store_num;
	public $table_id;
	public $ltNowDate=false;

	protected $is_qiandan=false;

	public $lcu;
	public $luu;
	public $lcd;
	public $lud;

	public $clueHeadRow;
	public $clueServiceRow;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
            'visit_date'=>Yii::t('clue','flow date'),
		    'rpt_bool'=>Yii::t('clue','rpt bool'),
		    'visit_text'=>Yii::t('clue','flow text'),
		    'visit_obj'=>Yii::t('clue','visit obj'),
		    'sign_odds'=>Yii::t('clue','sign odds'),
		    'clue_service_id'=>Yii::t('clue','clue service'),
		    'predict_date'=>Yii::t('clue','predict date'),
		    'predict_amt'=>Yii::t('clue','predict amt'),
		    'lbs_main'=>Yii::t('clue','lbs main'),
		    'store_num'=>Yii::t('clue','store num'),
		    'survey_bool'=>Yii::t('clue','survey bool'),
		    'no_intention_id'=>Yii::t('clue','no intention'),
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,clue_id,survey_bool,no_intention_id,clue_type,create_staff,last_visit_date,predict_date,predict_amt,lbs_main,store_num,update_bool','safe');
        $list[]=array('clue_service_id','required');
        $list[]=array('visit_obj,sign_odds,visit_date,visit_text,rpt_bool','required','on'=>array("new","edit"));
        $list[]=array('clue_service_id','validateClueServiceID');
        $list[]=array('clue_id','validateClueID');
        $list[]=array('visit_obj','validateVisitObj');
        $list[]=array('id','validateID');
        $list[]=array('sign_odds','validateSignOdds');
        $list[]=array('predict_amt','validateQiandan');
        $list[]=array('survey_bool','validateSurveyBool','on'=>array("new","edit"));
		return $list;
	}

    public function validateQiandan($attribute, $param) {
	    if($this->is_qiandan&&empty($this->predict_amt)){
            $this->addError($attribute, "预估成交金额(年金额)不能为空");
        }
    }
    public function validateSurveyBool($attribute, $param) {
        if($this->clueHeadRow["box_type"]==1){
            if($this->survey_bool===""||$this->survey_bool===null){
                $this->addError($attribute, "请选择是否勘察");
            }
            if(empty($this->sign_odds)){
                if(empty($this->no_intention_id)){
                    $this->addError($attribute, "请选择无意向的原因类型");
                }
            }else{
                $this->no_intention_id=null;
            }
        }else{
            $this->survey_bool=null;
            $this->no_intention_id=null;
        }
    }

    public function validateSignOdds($attribute, $param) {
        if($this->clue_type==2){//KA线索
            $rptRow = Yii::app()->db->createCommand()->select("*")->from("sal_clue_rpt")
                ->where("clue_service_id=:id",array(":id"=>$this->clue_service_id))
                ->order("id desc")->queryRow();
            if($this->getScenario()=="delete"){
                $endFlowRow = Yii::app()->db->createCommand()->select("*")->from("sal_clue_flow")
                    ->where("clue_service_id=:clue_service_id and id!=:id",array(":clue_service_id"=>$this->clue_service_id,":id"=>$this->id))
                    ->order("visit_date desc,id desc")->queryRow();//最后一条跟进信息
                $sign_odds = $endFlowRow?$endFlowRow["sign_odds"]:0;
            }else{
                $sign_odds = $this->sign_odds;
            }
            if($rptRow){
                if(in_array($rptRow["rpt_status"],array(1,10))){//报价中、报价通过
                    if($sign_odds<50){
                        $this->addError($attribute, "该商机已报价，签单概率不能小于50%");
                    }
                }else{
                    if($sign_odds==100){
                        $this->addError($attribute, "该商机正在报价中，签单概率无法填100%");
                    }
                }
            }else{
                if($sign_odds==100){
                    $this->addError($attribute, "该商机还未报价，签单概率无法填100%");
                }
            }
        }
    }

    public function validateID($attribute, $param) {
        $visit_dt = date("Y/m/d",strtotime($this->visit_date));
        $thisDate = VisitForm::isVivienne()?"0000/00/00":date("Y/m/01");
        $scenario = $this->getScenario();
        if($scenario=="new"){
            if($this->clue_type==1){//地推只能录入本月且当天只能50条
                $nowDate = date("Y/m/d");
                $minDate = date("Y/m/d",strtotime($nowDate." - 1 day"));
                $minDate = $minDate<$thisDate?$thisDate:$minDate;
                if($visit_dt<$minDate){
                    $this->addError($attribute, "拜访日期必须大于".$minDate);
                }else{
                    $username = Yii::app()->user->id;
                    $countRow = Yii::app()->db->createCommand()
                        ->select("count(id)")
                        ->from("sal_visit")
                        ->where("username='{$username}' and DATE_FORMAT(visit_dt,'%Y/%m/%d')='{$visit_dt}'")->queryScalar();
                    if($countRow>=50){//每天录入上线为50条
                        $this->addError($attribute, "每天录入上限为{$countRow}/50条（{$visit_dt}） - {$username}");
                    }
                }
            }
        }else{
            $flowRow = Yii::app()->db->createCommand()
                ->select("a.*")
                ->from("sal_clue_flow a")
                ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
            if($flowRow){
                $old_dt = date("Y/m/d",strtotime($flowRow["visit_date"]));
                $this->ltNowDate = $old_dt<$thisDate;
                if($scenario=="delete"){
                    if($this->ltNowDate){
                        $this->addError($attribute, "无法删除({$old_dt})时间段的数据");
                    }else{
                        $this->table_id = $flowRow["table_id"];
                    }
                }else{
                    $updateBool = $visit_dt<$thisDate;//验证修改后的时间
                    $this->ltNowDate = $updateBool||$this->ltNowDate;//验证修改前的时间
                    if($this->ltNowDate){
                        $this->visit_obj = explode(",",$flowRow["visit_obj"]);
                        $notUpdate=self::getNotUpdateList();
                        foreach ($notUpdate as $item){
                            $this->$item = $flowRow[$item];
                        }
                    }
                }
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public static function getNotUpdateList(){
        return array("visit_date","visit_obj_text","sign_odds");
    }

    public function validateClueServiceID($attribute, $param) {
	    $clueServiceModel = new ClueServiceForm("view");
        if($clueServiceModel->retrieveData($this->clue_service_id)){
            $this->clue_id = $clueServiceModel->clue_id;
            $this->clueServiceRow = $clueServiceModel->getAttributes();
        }else{
            $this->addError($attribute, "商机不存在，请刷新重试");
        }
    }

    public function validateClueID($attribute, $param) {
        $clueHeadModel = new ClueHeadForm("view");
        if($clueHeadModel->retrieveData($this->clue_id)){
            $this->city = $clueHeadModel->city;
            $this->clue_type=$clueHeadModel->clue_type;
            $this->create_staff=CGetName::getEmployeeIDByMy();
            $this->clueHeadRow = $clueHeadModel->getAttributes();
            $this->store_num=CGetName::getClueStoreSumByServiceID($this->clue_service_id);
        }else{
            $this->addError($attribute, "线索不存在，请刷新重试");
        }
    }

    public function validateVisitObj($attribute, $param) {
        $this->is_qiandan=false;
	    if(!empty($this->visit_obj)){
	        $idStr = implode("','",$this->visit_obj);
	        $idList = array();
	        $nameList = array();
            $rows = Yii::app()->db->createCommand()->select("name,id")->from("sal_visit_obj")
                ->where("id in ('{$idStr}')")->queryAll();
            if($rows){
                foreach ($rows as $row){
                    $idList[]=$row["id"];
                    $nameList[]=$row["name"];
                    if($row["name"]=="签单"){
                        $this->is_qiandan=true;
                        $this->sign_odds=100;
                    }
                }
            }
            if(empty($idList)){
                $this->addError($attribute, "拜访目的异常");
            }else{
                $this->visit_obj = $idList;
                $this->visit_obj_text = implode("、",$nameList);
            }
        }
    }

	public function retrieveData($index)
	{
		$sql = "select a.* from sal_clue_flow a where a.id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->clue_type = $row['clue_type'];
            $this->clue_service_id = $row['clue_service_id'];
            $this->create_staff = $row['create_staff'];
            $this->visit_date = General::toDate($row['visit_date']);
            $this->last_visit_date = empty($row['last_visit_date'])?"":General::toDate($row['last_visit_date']);
			$this->visit_obj = empty($row['visit_obj'])?array():explode(",",$row['visit_obj']);
            $this->visit_obj_text = $row['visit_obj_text'];
            $this->sign_odds = $row['sign_odds'];
            $this->survey_bool = $row['survey_bool'];
            $this->no_intention_id = $row['no_intention_id'];
            $this->visit_text = $row['visit_text'];
            $this->predict_amt = $row['predict_amt'];
            $this->predict_date = empty($row['predict_date'])?"":General::toDate($row['predict_date']);
            $this->lbs_main = $row['lbs_main'];
            $this->rpt_bool = $row['rpt_bool'];
            $this->table_id = $row['table_id'];
            $this->store_num = $row['store_num'];
            $this->update_bool = $row['update_bool'];
            $this->lcu = $row['lcu'];
            $this->luu = $row['luu'];
            $this->lcd = $row['lcd'];
            $this->lud = $row['lud'];

            return true;
		}else{
		    return false;
        }
	}

	public function retrieveDataByLast()
	{
		$row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_flow")
            ->where("clue_service_id=:clue_service_id",array(":clue_service_id"=>$this->clue_service_id))
            ->order("visit_date desc,id desc")->queryRow();
		if ($row) {
            $this->clue_service_id = $row['clue_service_id'];
            $this->create_staff = $row['create_staff'];
            $this->visit_date = General::toDate($row['visit_date']);
            $this->last_visit_date = empty($row['last_visit_date'])?"":General::toDate($row['last_visit_date']);
			$this->visit_obj = empty($row['visit_obj'])?array():explode(",",$row['visit_obj']);
            $this->visit_obj_text = $row['visit_obj_text'];
            $this->sign_odds = $row['sign_odds'];
            $this->survey_bool = $row['survey_bool'];
            $this->no_intention_id = $row['no_intention_id'];
            $this->predict_amt = $row['predict_amt'];
            $this->predict_date = empty($row['predict_date'])?"":General::toDate($row['predict_date']);
            $this->lbs_main = $row['lbs_main'];
            $this->rpt_bool = $row['rpt_bool'];
            $this->store_num = $row['store_num'];
		}
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->save($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city;
	    switch ($this->getScenario()){
            case "new":
                $connection->createCommand()->insert("sal_clue_flow",array(
                    "clue_id"=>$this->clue_id,
                    "clue_type"=>$this->clue_type,
                    "clue_service_id"=>$this->clue_service_id,
                    "create_staff"=>$this->create_staff,
                    "visit_date"=>$this->visit_date,
                    "last_visit_date"=>empty($this->last_visit_date)?null:$this->last_visit_date,
                    "lbs_main"=>empty($this->lbs_main)?null:$this->lbs_main,
                    "predict_date"=>empty($this->predict_date)?null:$this->predict_date,
                    "predict_amt"=>empty($this->predict_amt)?null:$this->predict_amt,
                    "sign_odds"=>$this->sign_odds,
                    "visit_text"=>$this->visit_text,
                    "rpt_bool"=>empty($this->rpt_bool)?0:$this->rpt_bool,
                    "survey_bool"=>$this->survey_bool===""?null:$this->survey_bool,
                    "no_intention_id"=>empty($this->no_intention_id)?null:$this->no_intention_id,
                    "store_num"=>empty($this->store_num)?0:$this->store_num,
                    "visit_obj_text"=>$this->visit_obj_text,
                    "visit_obj"=>is_array($this->visit_obj)?implode(",",$this->visit_obj):$this->visit_obj,
                    "lcu"=>$uid,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                //刷新商机最新的跟进信息
                $this->resetClueService();
                //刷新线索最新的跟进信息
                $this->resetClueDetail();
                //增加拜访信息(旧数据)
                $this->addVisitORKaDetail();
                break;
            case "edit":
                $connection->createCommand()->update("sal_clue_flow",array(
                    "last_visit_date"=>empty($this->last_visit_date)?null:$this->last_visit_date,
                    "sign_odds"=>$this->sign_odds,
                    "visit_text"=>$this->visit_text,
                    "rpt_bool"=>empty($this->rpt_bool)?0:$this->rpt_bool,
                    "visit_obj_text"=>$this->visit_obj_text,
                    "visit_obj"=>is_array($this->visit_obj)?implode(",",$this->visit_obj):$this->visit_obj,
                    "lbs_main"=>empty($this->lbs_main)?null:$this->lbs_main,
                    "predict_date"=>empty($this->predict_date)?null:$this->predict_date,
                    "predict_amt"=>empty($this->predict_amt)?null:$this->predict_amt,
                    "survey_bool"=>$this->survey_bool===""?null:$this->survey_bool,
                    "no_intention_id"=>empty($this->no_intention_id)?null:$this->no_intention_id,
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                //刷新商机最新的跟进信息
                $this->resetClueService();
                //刷新线索最新的跟进信息
                $this->resetClueDetail();
                break;
            case "delete":
                $connection->createCommand()->delete("sal_clue_flow","id=:id",array(":id"=>$this->id));
                if(!empty($this->table_id)){
                    if($this->clue_type==1){//删除销售拜访
                        $connection->createCommand()->delete("sal_visit","id=:id",array(
                            ":id"=>$this->table_id
                        ));
                    }else{
                        $connection->createCommand()->delete("sal_ka_bot_info","id=:id",array(
                            ":id"=>$this->table_id
                        ));
                    }
                }
                //刷新商机最新的跟进信息
                $this->resetClueService();
                //刷新线索最新的跟进信息
                $this->resetClueDetail();
        }
		return true;
	}

    //刷新商机及线索最新的跟进信息(商机id)
	public function resetClueAllByCSID($clue_service_id){
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_service")
            ->where("id=:id",array(":id"=>$clue_service_id))->queryRow();
	    if($row){
	        $this->clue_id = $row["clue_id"];
	        $this->clue_service_id = $clue_service_id;
            $this->resetClueService();
            $this->resetClueDetail();
        }
    }

    //刷新商机最新的跟进信息
	protected function resetClueService(){
        $visitNum = Yii::app()->db->createCommand()->select("count(*)")->from("sal_clue_flow")
            ->where("clue_service_id=:id",array(":id"=>$this->clue_service_id))->queryScalar();//跟进总次数
        $endFlowRow = Yii::app()->db->createCommand()->select("*")->from("sal_clue_flow")
            ->where("clue_service_id=:id",array(":id"=>$this->clue_service_id))
            ->order("visit_date desc,id desc")->queryRow();//最后一条跟进信息
        if($endFlowRow){
            //0：未跟进 1:跟进中 2：待报价 3：报价中 4：报价已驳回 5：报价通过 6:待合同审批 7:合同审批中 8：合同已驳回 9：合同通过
            $old_status=$this->clueServiceRow["service_status"];
            if($endFlowRow["sign_odds"]>=0&&$endFlowRow["sign_odds"]<100){
                if($this->clue_type==2&&in_array($old_status,array(0,1,4))){//只有KA才有报价审核
                    $service_status = 2;
                }else{
                    $contStatus = $this->clue_type==2?5:1;
                    $service_status = $old_status==6?$contStatus:$old_status;
                }
            }elseif ($endFlowRow["sign_odds"]==100){
                if(in_array($old_status,array(0,1,5,8))){
                    $service_status = 6;
                }else{
                    $service_status = $old_status;
                }
            }else{
                $service_status = 1;
            }
            Yii::app()->db->createCommand()->update("sal_clue_service",array(
                "visit_obj"=>$endFlowRow["visit_obj"],
                "visit_obj_text"=>$endFlowRow["visit_obj_text"],
                "sign_odds"=>$endFlowRow["sign_odds"],
                "end_flow_id"=>$endFlowRow["id"],
                "end_staff_id"=>$endFlowRow["create_staff"],
                "predict_date"=>$endFlowRow["predict_date"],
                "predict_amt"=>$endFlowRow["predict_amt"],
                "lbs_main"=>$endFlowRow["lbs_main"],
                "visit_num"=>$visitNum,
                "service_status"=>$service_status,
            ),"id=:id",array(":id"=>$this->clue_service_id));
        }else{
            Yii::app()->db->createCommand()->update("sal_clue_service",array(
                "visit_obj"=>null,
                "visit_obj_text"=>null,
                "sign_odds"=>null,
                "end_flow_id"=>null,
                "end_staff_id"=>null,
                "visit_num"=>0,
                "service_status"=>0,
            ),"id=:id",array(":id"=>$this->clue_service_id));
        }
    }

    //刷新线索最新的跟进信息
	protected function resetClueDetail(){
        $last_date = Yii::app()->db->createCommand()->select("max(last_visit_date)")->from("sal_clue_flow")
            ->where("clue_id=:id and last_visit_date is not null",array(":id"=>$this->clue_id))
            ->queryScalar();//最后跟进时间
        $endFlowRow = Yii::app()->db->createCommand()->select("*")->from("sal_clue_flow")
            ->where("clue_id=:id",array(":id"=>$this->clue_id))
            ->order("visit_date desc,id desc")->queryRow();//最后一条跟进信息
        if($endFlowRow){
            if($this->clueHeadRow["table_type"]==2){
                $clue_status=0;//未生效 1：服务中
            }else{
                $clue_status=$this->clueHeadRow["clue_status"];
                $clue_status = $clue_status<3?1:$clue_status;//1:跟进中 3：报价确认 4：合同确认 5：已转化
            }
            Yii::app()->db->createCommand()->update("sal_clue",array(
                "end_date"=>$endFlowRow["lud"],
                "end_employee_id"=>$endFlowRow["create_staff"],
                "end_flow_id"=>$endFlowRow["id"],
                "last_date"=>$last_date?$last_date:null,
                "clue_status"=>$clue_status,
            ),"id=:id",array(":id"=>$this->clue_id));
        }else{
            Yii::app()->db->createCommand()->update("sal_clue",array(
                "end_date"=>null,
                "end_employee_id"=>null,
                "end_flow_id"=>null,
                "last_date"=>null,
                "clue_status"=>0,
            ),"id=:id",array(":id"=>$this->clue_id));
        }
    }

    //增加拜访信息(旧数据)
	protected function addVisitORKaDetail(){
        if($this->clue_type==1){//地推
            if(Yii::app()->user->validRWFunction('HK01')){//销售拜访读写权限
                $this->addVisitDetail();
            }
        }else{
            if(Yii::app()->user->validRWFunction('KA01')) {//KA项目读写权限
                $this->addKADetail();
            }
        }
    }

    protected function getKACityID($city_code){
        $areaRow = Yii::app()->db->createCommand()->select("id")->from("sal_ka_area")
            ->where("city_code=:city_code",array(":city_code"=>$city_code))->queryRow();
        if($areaRow){
            return $areaRow["id"];
        }else{
            $areaRow = Yii::app()->db->createCommand()->select("id")->from("sal_ka_area")
                ->where("pro_name='全国'")->queryRow();
            if($areaRow){
                return $areaRow["id"];
            }else{
                return 0;
            }
        }
    }

    protected function getKASignOdds($sign_odds){
        if($sign_odds<50){
            return 40;
        }elseif ($sign_odds==50){
            return 50;
        }elseif ($sign_odds<=80){
            return 60;
        }else{
            return 90;
        }
    }

    protected function getKALinkID($sign_odds){
        $linkRow = Yii::app()->db->createCommand()->select("id")->from("sal_ka_link")
            ->where("rate_num<=:rate_num and rate_num!=100",array(":rate_num"=>$sign_odds))
            ->order("rate_num desc")->queryRow();
        if($linkRow){
            return $linkRow["id"];
        }else{
            $linkRow = Yii::app()->db->createCommand()->select("id")->from("sal_ka_link")
                ->order("rate_num asc")->queryRow();
            if($linkRow){
                return $linkRow["id"];
            }else{
                return 0;
            }
        }
    }

    protected function addKADetail(){
        $uid = Yii::app()->user->id;
        $kaRow = Yii::app()->db->createCommand()->select("id,customer_name")->from("sal_ka_bot")
            ->where("id=:id",array(":id"=>$this->clueHeadRow["ka_id"]))->queryRow();
        if(!$kaRow){
            $kaRow = Yii::app()->db->createCommand()->select("id")->from("sal_ka_bot")
                ->where("customer_name=:customer_name",array(":customer_name"=>$this->clueHeadRow["cust_name"]))->queryRow();
        }
        if(!$kaRow){
            $kaCityID = $this->getKACityID($this->clueHeadRow["city"]);
            Yii::app()->db->createCommand()->insert("sal_ka_bot",array(
                "apply_date"=>$this->clueHeadRow["entry_date"],
                "customer_no"=>$this->clueHeadRow["clue_code"],
                "customer_name"=>$this->clueHeadRow["cust_name"],
                "search_name"=>$this->clueHeadRow["cust_name"],
                "kam_id"=>$this->clueHeadRow["rec_employee_id"],
                "head_city_id"=>$kaCityID,
                "talk_city_id"=>is_array($this->clueHeadRow["talk_city_id"])?json_encode($this->clueHeadRow["talk_city_id"]):$this->clueHeadRow["talk_city_id"],
                "work_user"=>$this->clueHeadRow["cust_person"],
                "work_phone"=>$this->clueHeadRow["cust_tel"],
                "work_email"=>$this->clueHeadRow["cust_email"],
                "contact_user"=>$this->clueHeadRow["cont_person"],
                "contact_phone"=>$this->clueHeadRow["cont_tel"],
                "contact_email"=>$this->clueHeadRow["cont_email"],
                "contact_dept"=>$this->clueHeadRow["cont_person_role"],
                "contact_adr"=>$this->clueHeadRow["cust_address"],
                "source_text"=>$this->clueHeadRow["clue_source"],//客户来源
                "source_id"=>$this->clueHeadRow["cust_type"],//客户类型
                "area_id"=>$kaCityID,//
                "level_id"=>$this->clueHeadRow["cust_level"],
                "class_id"=>$this->clueHeadRow["cust_ka_class"],
                "busine_id"=>is_array($this->clueHeadRow["busine_id"])?json_encode($this->clueHeadRow["busine_id"]):$this->clueHeadRow["busine_id"],
                "support_user"=>$this->clueHeadRow["support_user"],
                "link_id"=>$this->getKALinkID($this->sign_odds),
                //"cust_type"=>$this->clueHeadRow["cust_class"],
                "sign_odds"=>$this->getKASignOdds($this->sign_odds),
                "follow_date"=>$this->visit_date,
                "ava_show_date"=>empty($this->predict_date)?null:$this->predict_date,
                "available_date"=>empty($this->predict_date)?null:$this->predict_date,
                "available_amt"=>empty($this->predict_amt)?null:$this->predict_amt,
                "city"=>$this->clueHeadRow["city"],
                "lcu"=>$uid,
            ));
            $kaId = Yii::app()->db->getLastInsertID();
            Yii::app()->db->createCommand()->update("sal_clue",array(
                "ka_id"=>$kaId,
            ),"id=:id",array(":id"=>$this->clueHeadRow["id"]));
        }else{
            $kaId = $kaRow["id"];
            Yii::app()->db->createCommand()->update("sal_ka_bot",array(
                "ava_show_date"=>empty($this->predict_date)?null:$this->predict_date,
                "follow_date"=>$this->visit_date,
                "customer_name"=>$this->clueHeadRow["cust_name"],
                "search_name"=>$this->clueHeadRow["cust_name"],
                "luu"=>$uid,
            ),"id=:id",array(":id"=>$kaId));
        }
        Yii::app()->db->createCommand()->insert("sal_ka_bot_info",array(
            "bot_id"=>$kaId,
            "info_date"=>$this->visit_date,
            "info_text"=>$this->visit_text,
            "lcu"=>$uid,
        ));
        $botInfoId = Yii::app()->db->getLastInsertID();
        Yii::app()->db->createCommand()->update("sal_clue_flow",array(
            "table_id"=>$botInfoId,
        ),"id=:id",array(":id"=>$this->id));
    }

    public function addVisitByFlowIDs($flow_ids){
        if(is_array($flow_ids)){
            foreach ($flow_ids as $flow_id){
                $model = new ClueFlowForm('view');
                $model->retrieveData($flow_id);
                if($model->clue_type==1&&empty($model->table_id)&&$model->validate()){
                    $model->addVisitDetail($model->lcu);
                }
            }
        }
    }

    protected function addVisitDetail($username=''){
        $uid = empty($username)?Yii::app()->user->id:$username;
        $serviceTypeJson = $this->clueHeadRow["service_type"];
        $visitObjJson = $this->visit_obj;
        $predict_amt = empty($this->predict_amt)?0:floatval($this->predict_amt);
        Yii::app()->db->createCommand()->insert("sal_visit",array(
            "username"=>$uid,
            "visit_dt"=>$this->visit_date,
            "visit_type"=>$this->clueServiceRow["visit_type"],
            "visit_obj"=>json_encode($visitObjJson),//json
            "visit_obj_name"=>str_replace("、",",",$this->visit_obj_text),//
            "quotation"=>$this->rpt_bool==1?"是":"否",
            "service_type"=>json_encode($serviceTypeJson),//json
            "cust_type"=>CGetName::getVisitCustTypeIDByCustClassID($this->clueHeadRow["cust_class"]),
            //"type_group"=>$this->clueHeadRow["cust_type_group"],
            "cust_name"=>$this->clueHeadRow["cust_name"],
            "cust_person"=>$this->clueHeadRow["cust_person"],
            "cust_tel"=>$this->clueHeadRow["cust_tel"],
            "district"=>CGetName::getVisitDistrictIDByNalID($this->clueHeadRow["district"],$this->clueHeadRow["city"]),
            "street"=>$this->clueHeadRow["street"],
            "remarks"=>$this->visit_text,
            "sign_odds"=>empty($this->sign_odds)?0:$this->sign_odds,
            "city"=>$this->clueHeadRow["city"],
            "busine_id"=>is_array($this->clueServiceRow["busine_id"])?implode(",",$this->clueServiceRow["busine_id"]):$this->clueServiceRow["busine_id"],
            "busine_id_text"=>$this->clueServiceRow["busine_id_text"],
            "lcu"=>$uid,
            "status"=>'N',
            "status_dt"=>null,
            "visit_info_text"=>$this->is_qiandan?("{$predict_amt}({$this->clueServiceRow["busine_id_text"]})"):null,
            "total_amt"=>$this->is_qiandan?$this->predict_amt:0,
        ));
        $visitId = Yii::app()->db->getLastInsertID();
        if($this->is_qiandan){
            Yii::app()->db->createCommand()->insert("sal_visit_info",array(
                "visit_id"=>$visitId,
                "field_id"=>"svc_G3",
                "field_value"=>$predict_amt
            ));
            Yii::app()->db->createCommand()->insert("sal_visit_info",array(
                "visit_id"=>$visitId,
                "field_id"=>"svc_G2",
                "field_value"=>"CRM自动生成"
            ));

            $model= new VisitForm('edit');//首页需要提示大神签单
            $model->addNotificationByQian($visitId);
        }
        Yii::app()->db->createCommand()->update("sal_clue_flow",array(
            "table_id"=>$visitId,
        ),"id=:id",array(":id"=>$this->id));
    }

    public static function printClueFlowAndStoreBox($modelObj,$clueModel){
        $html="";
        // 即使商机ID为0，也要渲染完整的tab结构（显示空列表）
        $html.="<div class='box box-info'><div class='box-body'>";
        $tabs=array();
        //商机跟进记录
        $tabs[] = array(
            'label'=>Yii::t("clue","clue service flow"),
            'content'=>self::printClueServiceFlowBox($modelObj,$clueModel),
            'active'=>true,
            "id"=>"clue_service_flow"
        );
        //门店
        $tabs[] = array(
            'label'=>Yii::t("clue","clue service store"),
            'content'=>self::printClueServiceStoreBox($modelObj,$clueModel),
            'active'=>false,
            "id"=>"clue_service_store"
        );
        $html.=TbHtml::tabbableTabs($tabs);
        $html.="</div></div>";
        return $html;
    }

    //商机跟进记录
    public static function printClueServiceFlowBox($modelObj,$clueModel){
        $html="";
        // 即使商机ID为0，也要渲染视图（显示空列表）
        $rows = array();
        if(!empty($clueModel->clue_service_id)){
            $rows = Yii::app()->db->createCommand()->select("*")->from("sal_clue_flow")
                ->where("clue_service_id=:id",array(":id"=>$clueModel->clue_service_id))
                ->order("visit_date desc,id desc")->queryAll();
        }
        $html.=$modelObj->renderPartial("//clue/clue_flow_box",array("rows"=>$rows,"model"=>$clueModel),true);
        return $html;
    }

    //门店
    public static function printClueServiceStoreBox($modelObj,$clueModel){
        $html="";
        // 即使商机ID为0，也要渲染门店区域（显示空列表）
        $updateBool = 1;
        $rows = array();
        if(!empty($clueModel->clue_service_id)){
            // 不分页，获取所有关联门店（page=0表示不分页）
            $rows = CGetName::getClueSSeRowByClueServiceID($clueModel->clue_service_id,$updateBool,0,0);
        }
        $html.=$modelObj->renderPartial("//clue/clue_store_box",array("rows"=>$rows,"model"=>$clueModel),true);
        return $html;
    }

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->getScenario()=='view';
	}
}
