<?php

class ClueServiceForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $clue_id;
	public $clue_type;
	public $visit_type;
	public $create_staff;
	public $busine_id;
	public $busine_id_text;
	public $visit_obj;
	public $visit_obj_text;
	public $sign_odds;
	public $total_amt;
	public $total_num;
	public $end_flow_id;
	public $service_status;
	public $rpt_amt;
	public $predict_amt;
	public $predict_date;
	public $lbs_main;

	public $clueHeadRow;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
		    'busine_id'=>Yii::t('clue','service obj'),
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,clue_type,create_staff,service_status,lbs_main','safe');
        $list[]=array('clue_id,visit_type,busine_id','required');
        $list[]=array('id','validateID');
        $list[]=array('busine_id','validateBusineID');
		return $list;
	}

    public function validateID($attribute, $param) {
	    $clueHeadModel = new ClueHeadForm("view");
        if($clueHeadModel->retrieveData($this->clue_id)){
            $this->clue_type=$clueHeadModel->clue_type;
            $this->create_staff=CGetName::getEmployeeIDByMy();
            $this->clueHeadRow = $clueHeadModel->getAttributes();
            if(empty($clueHeadModel->service_type)){
                $this->addError($attribute, "请先填写服务类型");
            }
            if(empty($clueHeadModel->cust_class)){
                $this->addError($attribute, "请先填写行业类别");
            }
            if($this->clue_type==1&&empty($clueHeadModel->district)){
                $this->addError($attribute, "请先填写行政区域");
            }
            if($this->clue_type==2){
                if(empty($clueHeadModel->cust_level)){
                    $this->addError($attribute, "请先填写客户分级");
                }
                if(empty($clueHeadModel->cust_type)){
                    $this->addError($attribute, "请先填写客户类型");
                }
                if(empty($clueHeadModel->cust_ka_class)){
                    $this->addError($attribute, "请先填写客户类别");
                }
            }
        }else{
            $this->addError($attribute, "线索不存在，请刷新重试");
        }
    }

    public function validateBusineID($attribute, $param) {
	    if(!empty($this->busine_id)){
	        $idList = array();
	        $nameList = array();
	        $serviceDefList = CGetName::getServiceDefList();
	        foreach ($this->busine_id as $busine_id){
	            if(isset($serviceDefList[$busine_id])){
                    $idList[]=$busine_id;
                    $nameList[]=$serviceDefList[$busine_id];
                }
            }
            if(empty($idList)){
                $this->addError($attribute, "服务项目异常");
            }else{
                $this->busine_id = $idList;
                $this->busine_id_text = implode("、",$nameList);
            }
        }
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
        $index = empty($index)||!is_numeric($index)?0:$index;
		$sql = "select a.* from sal_clue_service a where a.id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->clue_id = $row['clue_id'];
			$this->visit_obj = empty($row['visit_obj'])?array():explode(",",$row['visit_obj']);
			$this->busine_id = empty($row['busine_id'])?array():explode(",",$row['busine_id']);
            $this->busine_id_text = $row['busine_id_text'];
            $this->visit_obj_text = $row['visit_obj_text'];
            $this->visit_type = $row['visit_type'];
            $this->clue_type = $row['clue_type'];
            $this->create_staff = $row['create_staff'];
            $this->sign_odds = $row['sign_odds'];
            $this->total_amt = $row['total_amt'];
            $this->total_num = $row['total_num'];
            $this->end_flow_id = $row['end_flow_id'];
            $this->service_status = $row['service_status'];
            $this->lbs_main = $row['lbs_main'];
            $this->rpt_amt = $row['rpt_amt'];
            $this->predict_amt = $row['predict_amt'];
            $this->predict_date = $row['predict_date'];

            return true;
		}else{
		    return false;
        }
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->save($connection);
			$transaction->commit();
			$this->computeSaveNew();
		}catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function computeSaveNew(){
	    if($this->clueHeadRow["clue_type"]==1){//地推
            $sseModel = new ClueSSEForm('new');
            $storeRow = Yii::app()->db->createCommand()->select("id")->from("sal_clue_store")
                ->where("clue_id=:clue_id",array(":clue_id"=>$this->clue_id))->queryRow();
            if($storeRow){
                $sseModel->clue_service_id=$this->id;
                $sseModel->check=array($storeRow["id"]);
                if($sseModel->validate()){
                    $sseModel->saveData();
                }
            }
        }
    }

	protected function save(&$connection)
	{
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city;
	    switch ($this->getScenario()){
            case "new":
                $connection->createCommand()->insert("sal_clue_service",array(
                    "clue_id"=>$this->clue_id,
                    "clue_type"=>$this->clue_type,
                    "visit_type"=>$this->visit_type,
                    "create_staff"=>$this->create_staff,
                    "busine_id_text"=>$this->busine_id_text,
                    "busine_id"=>is_array($this->busine_id)?implode(",",$this->busine_id):$this->busine_id,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                $connection->createCommand()->insert("sal_clue_history",array(
                    "table_id"=>$this->id,
                    "table_type"=>1,
                    "history_type"=>4,
                    "history_html"=>"<span>增加商机</span><br/><span>服务项目:{$this->busine_id_text}</span>",
                    "lcu"=>$uid,
                ));
                if(empty($this->clueHeadRow["clue_status"])){
                    $connection->createCommand()->update("sal_clue",array(
                        "clue_status"=>2,//商机
                    ),"id=:id",array(":id"=>$this->clue_id));
                }
                break;
            case "edit":
                break;
        }
		return true;
	}

    /**
     * 获取商机数据（用于异步加载）
     */
    public static function getServiceData($clueModel){
        $whereSql="";
        if(ClientHeadList::isReadAll()){
            //全部
        }else{
            $staff_id = CGetName::getEmployeeIDByMy();
            $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIdStr = !empty($groupIdStr)?implode(",",$groupIdStr):$staff_id;
            $whereSql.=" and create_staff in ({$groupIdStr}) ";
        }
        
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_clue_service")
            ->where("clue_id=:clue_id {$whereSql}",array(":clue_id"=>$clueModel->id))
            ->order("id desc")
            ->queryAll();
            
        $services = array();
        if($rows){
            foreach($rows as $row){
                $services[] = array(
                    'id' => $row['id'],
                    'clue_id' => $row['clue_id'],
                    'busine_id_text' => $row['busine_id_text'],
                    'visit_obj_text' => $row['visit_obj_text'],
                    'sign_odds' => $row['sign_odds'],
                    'service_status' => $row['service_status'],
                    'status_text' => CGetName::getServiceStatusStrByKey($row['service_status']),
                    'active' => $clueModel->clue_service_id==$row["id"],
                    'rpt_bool' => in_array($row["service_status"],array(2,4,5)), // 待报价
                    'contract_bool' => in_array($row["service_status"],array(6,8)) // 待合同审批
                );
            }
        }
        
        return array(
            'services' => $services,
            'can_add' => Yii::app()->user->validRWFunction('CM02')||Yii::app()->user->validRWFunction('CM10')
        );
    }

    public static function printClueServiceBox($modelObj,$clueModel){
        $html="";
        $whereSql="";
        if(ClientHeadList::isReadAll()){
            //全部
        }else{
            $staff_id = CGetName::getEmployeeIDByMy();
            $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
            $groupIdStr = implode(",",$groupIdStr);
            $whereSql.=" and create_staff in ({$groupIdStr}) ";
        }
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_clue_service")
            ->where("clue_id=:clue_id {$whereSql}",array(":clue_id"=>$clueModel->id))
            ->order("id desc")
            ->queryAll();
        $triggerBool=false;//是否要收起
        if($rows){
            for ($i=0;$i<count($rows);$i++){
                if($i==3){
                    $html.="<div class=\"col-lg-12 bat_service_div_click text-center\"><div><span>收起</span></div></div>";
                }
                $row=$rows[$i];
                $row["activeBool"] = $clueModel->clue_service_id==$row["id"];
                if($i<3&&$row["activeBool"]){
                    $triggerBool=true;
                }
                $row["box_class"] = $row["activeBool"]?"box-active box-info":"";
                $row["rpt_bool"] = false;//报价按钮
                $row["contract_bool"] = false;//合同按钮
                $row["status_text"] = "";//状态文本
                if(in_array($row["service_status"],array(2,4,5))){//待报价
                    $row["rpt_bool"] = true;
                }elseif (in_array($row["service_status"],array(6,8))){//待合同审批
                    $row["contract_bool"] = true;
                }elseif (!in_array($row["service_status"],array(0,1))){
                    $row["status_text"]=CGetName::getServiceStatusStrByKey($row["service_status"]);
                }
                $html.=$modelObj->renderPartial("//clue/clue_service_box",array("row"=>$row),true);
            }
        }
        if(Yii::app()->user->validRWFunction('CM02')||Yii::app()->user->validRWFunction('CM10')){
            $html.='<div class="mpr-0 col-lg-4 mpr-add">';
            $html.='<div class="box box-clue-service">';
            $html.=TbHtml::tag("div",array(
                'data-load'=>Yii::app()->createUrl('clueService/ajaxShow'),
                'data-submit'=>Yii::app()->createUrl('clueService/ajaxSave'),
                'data-serialize'=>"ClueServiceForm[scenario]=new&ClueServiceForm[clue_id]=".$clueModel->id,
                'data-obj'=>"#clue_service_row",
                'data-fun'=>"clickServiceRow",
                'id'=>"clue-service-add",
                'class'=>'openDialogForm'
            ),false,false);
            //$html.='<div class="box-body" id="clue-service-add">';
            $html.='<a>'.Yii::t('clue',"add client service").'</a>';
            $html.='</div>';
            $html.='</div>';
            $html.='</div>';
        }
        $html.='<script type="text/javascript">';
        $html.="
        $('.bat_service_div_click').click(function(){
            if($(this).hasClass('open')){
                $(this).removeClass('open');
                $(this).find('span').eq(0).text('收起');
                $(this).nextAll('.mpr-0').not('.mpr-add').show();
            }else{
                $(this).addClass('open');
                $(this).find('span').eq(0).text('展开');
                $(this).nextAll('.mpr-0').not('.mpr-add').hide();
            }
		});
		";
        if($triggerBool){
            $html.="$('.bat_service_div_click').trigger('click');";
        }
        $html.='</script>';
        return $html;
    }

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $sql = "select a.id from sal_clue_flow a where a.clue_id=".$index." ";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            $rtn = ($row !== false);
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->getScenario()=='view';
	}
}
