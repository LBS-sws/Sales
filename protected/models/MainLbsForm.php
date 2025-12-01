<?php

class MainLbsForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $city;
	public $sh_code;
	public $mh_code;
	public $show_type=1;
	public $show_city=array();
	public $z_display=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
        return array(
            'name'=>Yii::t('clue','lbs main'),
            'city'=>Yii::t('clue','city manger'),
            'sh_code'=>Yii::t('clue','sh code'),
            'mh_code'=>Yii::t('clue','mh main code'),
            'show_type'=>Yii::t('clue','show type'),
            'show_city'=>Yii::t('clue','show city'),
            'z_display'=>Yii::t('clue','z display'),
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,city,sh_code,show_city','safe'),
			array('name,mh_code,show_type,z_display','required'),
            array('show_type','validateShowType'),
            array('id','validateID'),
		);
	}

    public function validateShowType($attribute, $param) {
        if($this->show_type==3){//部分城市可见
            if(empty($this->show_city)){
                $this->addError($attribute, "可见城市不能为空");
            }
        }else{
            $this->show_city=array();
        }
    }
    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $row = Yii::app()->db->createCommand()->select("id")
                ->from("sal_main_lbs")
                ->where("name=:name",array(
                    ":name"=>$this->name
                ))->queryRow();
            if($row){
                $this->addError($attribute, "主体公司已存在，无法重复添加");
            }
        }else{
            $row = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_main_lbs a")
                ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
            if($row){
                if($scenario=="delete"){
                    $clueFlowRow = Yii::app()->db->createCommand()->select("*")
                        ->from("sal_clue_flow")
                        ->where("lbs_main=:id",array(":id"=>$this->id))->queryRow();
                    if($clueFlowRow){
                        $this->addError($attribute, "该主体公司已关联线索跟进，无法删除");
                    }
                }
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

	public function retrieveData($index)
	{
		$city = Yii::app()->user->city();
		$sql = "select * from sal_main_lbs where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
            $this->city = $row['city'];
			$this->sh_code = $row['sh_code'];
			$this->mh_code = $row['mh_code'];
			$this->show_type = $row['show_type'];
			$this->show_city = empty($row['show_city'])?array():explode(",",$row['show_city']);
			$this->z_display = $row['z_display'];
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
				$sql = "delete from sal_main_lbs where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_main_lbs(
						name, sh_code,mh_code, city, show_type, show_city, z_display, lcu, luu) values (
						:name, :sh_code,:mh_code, :city, :show_type, :show_city, :z_display, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_main_lbs set 
					name = :name, 
					sh_code = :sh_code,
					mh_code = :mh_code,
					city = :city,
					show_type = :show_type,
					show_city = :show_city,
					z_display = :z_display,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':sh_code')!==false)
			$command->bindParam(':sh_code',$this->sh_code,PDO::PARAM_STR);
		if (strpos($sql,':mh_code')!==false)
			$command->bindParam(':mh_code',$this->mh_code,PDO::PARAM_STR);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':show_type')!==false)
			$command->bindParam(':show_type',$this->show_type,PDO::PARAM_STR);
		if (strpos($sql,':show_city')!==false){
            $show_city = empty($this->show_city)||!is_array($this->show_city)?null:implode(",",$this->show_city);
            $command->bindParam(':show_city',$show_city,PDO::PARAM_STR);
        }
		if (strpos($sql,':z_display')!==false)
			$command->bindParam(':z_display',$this->z_display,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}
}
