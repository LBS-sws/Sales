<?php

class StopOtherForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $service_id;

	public $bold_service;
	public $customer_name;
	public $staff_id;
	public $back_date;
	public $back_type;
	public $back_remark;

	public $shiftId;
	public $shiftStaff;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'shiftId'=>Yii::t('customer','Service'),
            'shiftStaff'=>Yii::t('report','Please select the assigned person'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,service_id,shiftId,shiftStaff','safe'),
			array('shiftStaff,shiftId','required'),
            array('shiftStaff','validateStaff'),
            array('shiftId','validateID'),
		);
	}

    public function validateStaff($attribute, $params) {
	    $staffList = StopOtherList::saleman();
	    if(!key_exists($this->shiftStaff,$staffList)){
	        $this->shiftId=array();
        }
    }

    public function validateID($attribute, $params) {
        $city=Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
	    $list = array();
	    if(!empty($this->shiftId)){
	        foreach ($this->shiftId as $serviceId=>$value){
                if($value==1){
                    $row = Yii::app()->db->createCommand()->select("id")->from("swoper{$suffix}.swo_service")
                        ->where("id=:id and company_id is not NULL and city='{$city}'",array(":id"=>$serviceId))->queryRow();
                    if($row){
                        $list[$serviceId] = $value;
                    }
                }
            }
        }
        $this->shiftId = $list;
    }

	public function retrieveData($index)
	{
        $city=Yii::app()->user->city();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from sal_stop_back where service_id='".$index."' and company_id is not NULL and city='{$city}'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$this->service_id = $index;
		if ($row!==false) {
			$this->id = $row['id'];
			$this->back_remark = $row['back_remark'];
			$this->back_type = $row['back_type'];
			$this->back_date = $row['back_date'];
			$this->staff_id = $row['staff_id'];
			$this->customer_name = $row['customer_name'];
            $this->bold_service = $row['bold_service'];
		}
        return true;
	}
	
	public function shiftAll(){
        if(!empty($this->shiftId)){
            foreach ($this->shiftId as $serviceId=>$value){
                $row = Yii::app()->db->createCommand()->select("id")->from("sal_stop_back")
                    ->where("service_id=:id",array(":id"=>$serviceId))->queryRow();
                if($row){
                    Yii::app()->db->createCommand()->update("sal_stop_back",array(
                        "staff_id"=>$this->shiftStaff
                    ),"id=".$row["id"]);
                }else{
                    Yii::app()->db->createCommand()->insert("sal_stop_back",array(
                        "service_id"=>$serviceId,
                        "bold_service"=>0,
                        "staff_id"=>$this->shiftStaff
                    ));
                }
            }
        }
	}

	public static function getServiceList($serviceId){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("a.*,b.contract_no")
            ->from("swoper{$suffix}.swo_service a")
            ->leftJoin("swoper{$suffix}.swo_service_contract_no b","a.id=b.service_id")
            ->leftJoin("swoper{$suffix}.swo_company d","a.company_id=d.id")
            ->where("a.id=:id",array(":id"=>$serviceId))->queryRow();
        if($row){
            $row["status_dt"]=General::toDate($row["status_dt"]);
            $row["sign_dt"]=General::toDate($row["sign_dt"]);
            $row["ctrt_end_dt"]=General::toDate($row["ctrt_end_dt"]);
            $row["status_desc"]=Yii::t("service","Terminate");
            $row["cust_type"]=self::getCustTypeStr($row["cust_type"]);
            $row["cust_type_name"]=self::getCustTypeNameStr($row["cust_type_name"]);
            $row["nature_type"]=self::getNatureTypeStr($row["nature_type"]);
            $row["paid_type"]=self::getPaidTypeStr($row["paid_type"]);
            $row["need_install"]=$row["need_install"]=="Y"?Yii::t("misc","Yes"):Yii::t("misc","No");
            $row["company_name"]=self::getCompanyStr($row["company_id"]);
            $row["salesman_name"]=self::getEmployeeStr($row["salesman_id"]);
            $row["othersalesman_name"]=self::getEmployeeStr($row["othersalesman_id"]);
            $row["technician_name"]=self::getEmployeeStr($row["technician_id"]);
        }else{
            $row = array();
        }
        return $row;
    }

    public static function getCompanyStr($company_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("code,name")->from("swoper{$suffix}.swo_company")
            ->where("id=:id",array(":id"=>$company_id))->queryRow();
        if($row){
            return $row["code"].$row["name"];
        }else{
            return empty($company_id)?"":$company_id;
        }
    }

    public static function getEmployeeStr($employee_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.code,a.name")
            ->from("hr{$suffix}.hr_employee a")
            ->where("a.id=:id",array(":id"=>$employee_id))->queryRow();
        if($row){
            return $row["name"]." ({$row['code']})";
        }else{
            return empty($employee_id)?"":$employee_id;
        }
    }

    public static function getCustTypeStr($cust_type){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("description")
            ->from("swoper{$suffix}.swo_customer_type")
            ->where("id=:id",array(":id"=>$cust_type))->queryRow();
        if($row){
            return $row["description"];
        }else{
            return Yii::t("misc","-- None --");
        }
    }

    public static function getCustTypeNameStr($cust_type_name){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("cust_type_name")
            ->from("swoper{$suffix}.swo_customer_type_twoname")
            ->where("id=:id",array(":id"=>$cust_type_name))->queryRow();
        if($row){
            return $row["cust_type_name"];
        }else{
            return Yii::t("misc","-- None --");
        }
    }

    public static function getNatureTypeStr($nature_type){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("description")
            ->from("swoper{$suffix}.swo_nature")
            ->where("id=:id",array(":id"=>$nature_type))->queryRow();
        if($row){
            return $row["description"];
        }else{
            return Yii::t("misc","-- None --");
        }
    }

    public static function getPaidTypeStr($paid_type){
        switch ($paid_type){
            case "M":
                return Yii::t("service","Monthly");
            case "Y":
                return Yii::t("service","Yearly");
            default:
                return Yii::t("service","One time");
        }
    }
}