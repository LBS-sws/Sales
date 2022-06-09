<?php

class StopNoneForm extends CFormModel
{
	/* User Fields */
    public $id;
    public $service_id;
    public $employee_id;

    public $bold_service=0;
    public $customer_name;
    public $staff_id;
    public $back_date;
    public $back_type;
    public $back_remark;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'bold_service'=>Yii::t('sales','VIP'),
            'customer_name'=>Yii::t('customer','customer name'),
            //'staff_id'=>Yii::t('customer','z_index'),
            'back_date'=>Yii::t('customer','shift date'),
            'back_type'=>Yii::t('customer','shift type'),
            'back_remark'=>Yii::t('customer','shift remark'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,service_id,bold_service,customer_name,staff_id,back_date,back_type,back_remark','safe'),
			array('service_id','required'),
			array('back_date,back_type','required','on'=>array("edit")),
            array('service_id','validateID'),
		);
	}

    public function validateID($attribute, $params) {
        $city=Yii::app()->user->city();
        $citylist = Yii::app()->user->city_allow();
        $expr_sql = StopOtherList::getExprSql();
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.id as service_id,b.id,f.again_type")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back b","a.id=b.service_id ")
            ->leftJoin("sal_stop_type f","f.id=b.back_type ")
            ->where("a.status = 'T' and a.id=:id and a.company_id is not NULL and a.city in ($citylist) {$expr_sql}",array(":id"=>$this->service_id))->queryRow();
        if($row){
            $this->id = $row["id"];
            if(!empty($row["again_type"])){
                $this->addError($attribute, "该回访需要再次回访，无法修改");
            }
        }else{
            $this->addError($attribute, "服务不存在，请刷新重试");
        }
    }

	public function retrieveData($index)
	{
        $city=Yii::app()->user->city();
        $citylist = Yii::app()->user->city_allow();
        $expr_sql = StopOtherList::getExprSql();
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.id as service_id,b.*")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("sal_stop_back b","a.id=b.service_id ")
            ->where("a.status = 'T' and a.id=:id and a.company_id is not NULL and a.city in ($citylist) {$expr_sql}",array(":id"=>$index))->queryRow();
        $this->service_id = $index;
        if($row){
            $this->id = $row['id'];
            $this->back_remark = $row['back_remark'];
            $this->back_type = $row['back_type'];
            $this->back_date = $row['back_date'];
            $this->staff_id = $row['staff_id'];
            $this->customer_name = $row['customer_name'];
            $this->bold_service = empty($row['bold_service'])?false:true;
            return true;
        }else{
            return false;
        }
	}
	
	public function saveData()
	{
	    if($this->getScenario()=='delete'&&!empty($this->id)){
            Yii::app()->db->createCommand()->delete("sal_stop_back","id=".$this->id);
            Yii::app()->db->createCommand()->delete("sal_stop_back_info","stop_id=".$this->id);
        }elseif ($this->getScenario()=='edit'){
            $arr = array();
            $arr['back_remark'] = $this->back_remark;
            $arr['back_type'] = $this->back_type;
            $arr['back_date'] = $this->back_date;
            $arr['customer_name'] = $this->customer_name;
            $arr['bold_service'] = empty($this->bold_service)?0:1;
            if(!empty($this->id)){
                $arr['luu'] = Yii::app()->user->id;
                Yii::app()->db->createCommand()->update("sal_stop_back",$arr,"id=".$this->id);
            }else{
                $arr['lcu'] = Yii::app()->user->id;
                $arr['service_id'] = $this->service_id;
                Yii::app()->db->createCommand()->insert("sal_stop_back",$arr);
                $this->id = Yii::app()->db->getLastInsertID();
            }

            StopNoneForm::stopAgainInfo($this->id,$this->back_type,$arr);
        }
	}

	//终止客户的后续跟进
	public static function stopAgainInfo($stop_id,$back_type,$arr){
        $row = Yii::app()->db->createCommand()
            ->select("again_type,again_day")
            ->from("sal_stop_type")
            ->where("id=:id",array(":id"=>$back_type))->queryRow();
        if($row&&!empty($row["again_type"])){ //需要繼續跟進
            $list=array();
            $list['stop_id'] = $stop_id;
            $list['customer_name'] = $arr['customer_name'];
            $list['back_date'] = $arr['back_date'];
            $list['back_type'] = $arr['back_type'];
            $list['back_remark'] = $arr['back_remark'];
            $list['lcu'] = Yii::app()->user->id;
            Yii::app()->db->createCommand()->insert("sal_stop_back_info",$list);
            $sql = "update sal_stop_back set info_num=info_num+1 where id = {$stop_id}";
            Yii::app()->db->createCommand($sql)->execute();
        }
    }
}