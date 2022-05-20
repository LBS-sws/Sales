<?php

class StopSearchForm extends CFormModel
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
    public $lcu;
    public $luu;
    public $lcd;
    public $lud;

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
			array('service_id','required'),
		);
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
            ->select("a.id as service_id,b.*")
            ->from("sal_stop_back b")
            ->leftJoin("swoper{$suffix}.swo_service a","a.id=b.service_id ")
            ->where("a.id=:id and b.back_date is not NULL and a.city in ($citylist) {$employee_sql}",array(":id"=>$index))->queryRow();
        $this->service_id = $index;
        if($row){
            $this->id = $row['id'];
            $this->back_remark = $row['back_remark'];
            $this->back_type = $row['back_type'];
            $this->back_date = $row['back_date'];
            $this->staff_id = $row['staff_id'];
            $this->customer_name = $row['customer_name'];
            $this->lcu = $row['lcu'];
            $this->luu = $row['luu'];
            $this->lcd = $row['lcd'];
            $this->lud = empty($row['luu'])?"":$row['lud'];
            $this->bold_service = empty($row['bold_service'])?false:true;
            return true;
        }else{
            return false;
        }
	}
}