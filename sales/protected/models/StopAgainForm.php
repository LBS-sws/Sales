<?php

class StopAgainForm extends CFormModel
{
	/* User Fields */
    public $id;
    public $stop_id;
    public $service_id;
    public $employee_id;

    public $bold_service=0;
    public $customer_name;
    public $staff_id;
    public $back_date;
    public $back_type;
    public $back_remark;
    public $lcu;
    public $luu;
    public $lcd;
    public $lud;

    private $info_num=0;
    private $again_type=0;
    private $again_day=0;

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
            'lcu'=>Yii::t('customer','lcu'),
            'luu'=>Yii::t('customer','luu'),
            'lcd'=>Yii::t('customer','lcd'),
            'lud'=>Yii::t('customer','lud'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,service_id,bold_service,customer_name,staff_id,back_date,back_type,back_remark','safe'),
			array('id,back_date,back_type','required'),
            array('id','validateID'),
            array('back_type','validateType'),
		);
	}

    public function validateType($attribute, $params) {
	    if(empty($this->back_type)){
	        return;
        }
        $row = Yii::app()->db->createCommand()
            ->select("again_type,again_day")
            ->from("sal_stop_type")
            ->where("id=:id",array(":id"=>$this->back_type))->queryRow();
        if($row){
            $this->again_type = $row["again_type"];
            $this->again_day = $row["again_day"];
        }else{
            $this->addError($attribute, "回访客户状态不存在，请刷新重试");
        }
    }

    public function validateID($attribute, $params) {
        $employee_sql ="";
        if(!empty($this->employee_id)){
            $employee_sql =" and (a.salesman_id={$this->employee_id} or b.staff_id={$this->employee_id})";
        }
        $citylist = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("b.service_id,b.info_num,info.stop_id")
            ->from("sal_stop_back_info info")
            ->leftJoin("sal_stop_back b","b.id=info.stop_id ")
            ->leftJoin("swoper{$suffix}.swo_service a","a.id=b.service_id ")
            ->where("info.id=:id and info.end_bool=0 and a.city in ($citylist) {$employee_sql}",array(":id"=>$this->id))
            ->queryRow();
        if($row){
            $this->info_num = intval($row['info_num']);
            $this->stop_id = $row['stop_id'];
            $this->service_id = $row['service_id'];
        }else{
            $this->addError($attribute, "服务不存在，请刷新重试");
        }
    }

    public function getAgainType(){
	    return intval($this->again_type);
    }

	public function retrieveData($index)
	{
        $employee_sql ="";
        if(!empty($this->employee_id)){
            $employee_sql =" and (a.salesman_id={$this->employee_id} or b.staff_id={$this->employee_id})";
        }
        $citylist = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("b.service_id,info.*,b.bold_service")
            ->from("sal_stop_back_info info")
            ->leftJoin("sal_stop_back b","b.id=info.stop_id ")
            ->leftJoin("swoper{$suffix}.swo_service a","a.id=b.service_id ")
            ->where("info.id=:id and info.end_bool=0 and a.city in ($citylist) {$employee_sql}",array(":id"=>$index))->queryRow();
        if($row){
            $this->id = $index;
            $this->stop_id = $row['stop_id'];
            $this->service_id = $row['service_id'];
            $this->bold_service = empty($row['bold_service'])?false:true;
            return true;
        }else{
            return false;
        }
	}

	public static function getAgainList($stop_id){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.*,b.disp_name,f.type_name,date_add(a.back_date, interval f.again_day day) as again_end_date")
            ->from("sal_stop_back_info a")
            ->leftJoin("sal_stop_type f","a.back_type=f.id")
            ->leftJoin("security{$suffix}.sec_user b","a.lcu=b.username")
            ->where("a.stop_id=:id",array(":id"=>$stop_id))->order("a.back_date desc,a.lcd desc")->queryAll();
        return $rows?$rows:array();
    }

    public function saveData()
    {
        if ($this->getScenario()=='edit'){
            //结束以前的所有回访
            Yii::app()->db->createCommand()->update("sal_stop_back_info",array("end_bool"=>1),"stop_id=".$this->stop_id);
            //需要修改的数据
            $updateArr = array();
            $updateArr['info_num'] = $this->info_num+1;
            $updateArr['bold_service'] = empty($this->bold_service)?0:1;
            //新增继续回访的数据
            $arr = array();
            $arr['back_remark'] = $this->back_remark;
            $arr['back_type'] = $this->back_type;
            $arr['back_date'] = $this->back_date;
            $arr['customer_name'] = $this->customer_name;
            $arr['lcu'] = Yii::app()->user->id;
            $arr['stop_id'] = $this->stop_id;
            if(empty($this->again_type)){ //不需要再次回访
                $arr['end_bool'] = 1;
                $updateArr['back_remark'] = $this->back_remark;
                $updateArr['back_type'] = $this->back_type;
                $updateArr['back_date'] = $this->back_date;
                $updateArr['customer_name'] = $this->customer_name;
                $updateArr['luu'] = Yii::app()->user->id;
            }
            Yii::app()->db->createCommand()->insert("sal_stop_back_info",$arr);
            Yii::app()->db->createCommand()->update("sal_stop_back",$updateArr,"id=".$this->stop_id);
        }
    }
}