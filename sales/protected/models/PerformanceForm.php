<?php

class PerformanceForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $rpt_type;
	public $type_group;
	public $city;
    public $sum;
    public $sums;
    public $year;
    public $month;
    public $spanning;
    public $otherspanning;
    public $business_spanning;
    public $business_otherspanning;
    public $restaurant_spanning;
    public $restaurant_otherspanning;
    public $ltNowDate=false;//小于当前日期：true

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'name'=>Yii::t('code','Description'),
			'rpt_type'=>Yii::t('code','Report Category'),
			'city'=>Yii::t('sales','City'),
			'type_group'=>Yii::t('code','Type'),
            'sum'=>Yii::t('code','Sum'),
            'sums'=>Yii::t('code','Sums'),
            'year'=>Yii::t('code','Year'),
            'month'=>Yii::t('code','Month'),
            'spanning'=>Yii::t('code','Spanning'),
            'otherspanning'=>Yii::t('code','Otherspanning'),
            'business_spanning'=>Yii::t('code','Business_Spanning'),
            'business_otherspanning'=>Yii::t('code','Business_Otherspanning'),
            'restaurant_spanning'=>Yii::t('code','Restaurant_Spanning'),
            'restaurant_otherspanning'=>Yii::t('code','Restaurant_Otherspanning'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('year,month','required'),
			array('id,rpt_type,sum,sums,spanning,otherspanning,business_spanning,business_otherspanning,restaurant_spanning,restaurant_otherspanning','safe'),
            array('id','validateID'),
		);
	}

    public function validateID($attribute, $params) {
        $thisDate = self::isVivienne()?"0000/00/00":date("Y/m/01");
        $status_dt = date("Y/m/d",strtotime($this->year."/".$this->month."/01"));
        $scenario = $this->getScenario();
        if(in_array($scenario,array("new"))){
            $this->ltNowDate = $status_dt<$thisDate;
            //验证新增
            if($status_dt<$thisDate){
                $this->addError($attribute, "无法新增({$status_dt})时间段的数据");
            }
        }else{
            $id= empty($this->id)?0:$this->id;
            $row = Yii::app()->db->createCommand()->select("a.*")->from("sal_performance a")
                ->where("a.id=:id",array(":id"=>$id))->queryRow();
            if($row){
                $row["log_dt"] = date("Y/m/d",strtotime($row["year"]."/".$row["month"]."/01"));
                $this->ltNowDate = $row["log_dt"]<$thisDate;
                if($scenario=="delete"){
                    if($row["log_dt"]<$thisDate){
                        $this->addError($attribute, "无法删除({$row["log_dt"]})时间段的数据");
                    }
                }else{
                    $updateBool = $status_dt<$thisDate;//验证修改后的时间
                    $updateBool = $updateBool||$row["log_dt"]<$thisDate;//验证修改前的时间
                    if($updateBool){
                        $notUpdate=self::getNotUpdateList();
                        foreach ($notUpdate as $item){
                            $this->$item = $row[$item];
                        }
                    }
                }
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public static function getNotUpdateList(){
        return array("year","month","sum","sums","spanning","otherspanning",
            "business_spanning","business_otherspanning","restaurant_spanning","restaurant_otherspanning",
        );
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_performance where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
            $thisDate = self::isVivienne()?"0000/00/00":date("Y/m/01");
			$this->id = $row['id'];
			$this->year = $row['year'];
			$this->month = $row['month'];
            $this->sum = $row['sum'];
            $this->sums = $row['sums'];
            $this->spanning = $row['spanning'];
            $this->otherspanning = $row['otherspanning'];
            $this->business_spanning = $row['business_spanning'];
            $this->business_otherspanning = $row['business_otherspanning'];
            $this->restaurant_spanning = $row['restaurant_spanning'];
            $this->restaurant_otherspanning = $row['restaurant_otherspanning'];
            $this->ltNowDate = date("Y/m/d",strtotime($this->year."/".$this->month."/01"))<$thisDate;
		}
		return true;
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
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_performance where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_performance(
						name, rpt_type, type_group, city, lcu, luu) values (
						:name, :rpt_type, :type_group, :city, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_performance set 
					sum = :sum, 	
					sums = :sums, 	
					spanning = :spanning,
					otherspanning = :otherspanning,	
					business_spanning = :business_spanning,
					business_otherspanning = :business_otherspanning,		
					restaurant_spanning = :restaurant_spanning,
					restaurant_otherspanning = :restaurant_otherspanning,			  
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':sum')!==false)
			$command->bindParam(':sum',$this->sum,PDO::PARAM_STR);
        if (strpos($sql,':sums')!==false)
            $command->bindParam(':sums',$this->sums,PDO::PARAM_STR);
        if (strpos($sql,':spanning')!==false)
            $command->bindParam(':spanning',$this->spanning,PDO::PARAM_STR);
        if (strpos($sql,':otherspanning')!==false)
            $command->bindParam(':otherspanning',$this->otherspanning,PDO::PARAM_STR);
        if (strpos($sql,':business_spanning')!==false)
            $command->bindParam(':business_spanning',$this->business_spanning,PDO::PARAM_STR);
        if (strpos($sql,':business_otherspanning')!==false)
            $command->bindParam(':business_otherspanning',$this->business_otherspanning,PDO::PARAM_STR);
        if (strpos($sql,':restaurant_spanning')!==false)
            $command->bindParam(':restaurant_spanning',$this->restaurant_spanning,PDO::PARAM_STR);
        if (strpos($sql,':restaurant_otherspanning')!==false)
            $command->bindParam(':restaurant_otherspanning',$this->restaurant_otherspanning,PDO::PARAM_STR);
		if (strpos($sql,':type_group')!==false)
			$command->bindParam(':type_group',$this->type_group,PDO::PARAM_INT);
		if (strpos($sql,':rpt_type')!==false)
			$command->bindParam(':rpt_type',$this->rpt_type,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	public function getCityList() {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select code, name from security$suffix.sec_city order by name";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		$rtn = array('99999'=>Yii::t('sales','All'));
		foreach ($rows as $row) {
			$rtn[$row['code']] = $row['name'];
		}
		return $rtn;
	}

	public function isOccupied($index) {
		$rtn = false;
		$sql = "select a.id from sal_visit a where a.cust_type=".$index." limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$rtn = ($row !== false);
		return $rtn;
	}

    public static function isVivienne(){
        $vivienneList = isset(Yii::app()->params['vivienneList'])?Yii::app()->params['vivienneList']:array("VivienneChen88888");
        $uid = Yii::app()->getComponent('user')===null?"admin":Yii::app()->user->id;
        return in_array($uid,$vivienneList);
    }

    public function getReadonly(){
        return $this->scenario=='view'||$this->ltNowDate;
    }
}
