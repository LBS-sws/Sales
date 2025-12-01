<?php

class ServiceTypeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $id_char;
	public $type_str;
	public $class_id;
	public $service_type;
	public $name;
	public $u_code;
	public $z_index=1;
	public $z_display=1;

    public $infoJson=array();
    public $saveInfo=array();

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
        return array(
            'id_char'=>Yii::t('clue','id char'),
            'name'=>Yii::t('clue','Project Name'),
            'z_index'=>"层级",
            'service_type'=>"服务类型",
            'type_str'=>"金额类型",
            'class_id'=>"分类汇总",
            'u_code'=>Yii::t('clue','u main code'),
            'z_display'=>Yii::t('clue','z display'),
        );
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,id_char,type_str,u_code,infoJson,class_id','safe'),
			array('name,service_type,z_index,z_display','required'),
            array('id','validateID'),
            array('infoJson','validateInfoJson','on'=>array("new",'edit')),
		);
	}

    public function validateInfoJson($attribute, $param) {
	    $this->saveInfo=array();
	    if(!empty($this->infoJson)){
	        $selectList = CGetName::getSelectList();
            foreach ($this->infoJson as $row){
                if($row["uflag"]=="D"){
                    $infoRow = Yii::app()->db->createCommand()->select("*")
                        ->from("sal_service_type_info")
                        ->where("id=:id",array(":id"=>$row["id"]))->queryRow();
                    if($infoRow){
                        $clueFlowRow = Yii::app()->db->createCommand()->select("*")
                            ->from("sal_visit_info")
                            ->where("field_id='svc_{$infoRow["id_char"]}'")->queryRow();
                        if($clueFlowRow){
                            $this->addError($attribute, "该子项目（{$infoRow["name"]}）已关联销售拜访，无法删除");
                            return false;
                        }
                        $this->saveInfo[]=$row;
                    }
                }elseif($row["inputType"]=="select"){
                    if(empty($row["func"])){
                        $this->addError($attribute, "该子项目（{$row["name"]}）的选择框函数不能为空");
                        return false;
                    }
                    if(!key_exists($row["func"],$selectList)){
                        $this->addError($attribute, "该子项目（{$row["name"]}）的选择框函数异常");
                        return false;
                    }
                    $this->saveInfo[]=$row;
                }else{
                    if(!empty($row["name"])){
                        $this->saveInfo[]=$row;
                    }
                }
            }
        }
    }
    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $row = Yii::app()->db->createCommand()->select("id")
                ->from("sal_service_type")
                ->where("name=:name",array(
                    ":name"=>$this->name
                ))->queryRow();
            if($row){
                $this->addError($attribute, "名称已存在，无法重复添加");
            }else{
                $endRow = Yii::app()->db->createCommand()->select("id_char")
                    ->from("sal_service_type")
                    ->order("id desc")->queryRow();
                $id_char = $endRow?$endRow["id_char"]:"H";
                $id_char = is_numeric($id_char)?intval($id_char):100;
                $id_char++;
                $this->id_char = $id_char;
            }
        }else{
            if($scenario=="delete"){
                $clueFlowRow = Yii::app()->db->createCommand()->select("*")
                    ->from("sal_visit_info")
                    ->where("field_id like 'svc_{$this->id_char}%'")->queryRow();
                if($clueFlowRow){
                    $this->addError($attribute, "该项目已关联销售拜访，无法删除");
                }
            }else{
                $row = Yii::app()->db->createCommand()->select("*")
                    ->from("sal_service_type")
                    ->where("id=:id",array(
                        ":id"=>$this->id
                    ))->queryRow();
                if($row){
                    $this->id_char=$row["id_char"];
                }else{
                    $this->addError($attribute, "该项目异常，请刷新重试");
                }
            }
        }
    }

	public function retrieveData($index,$bool=false)
	{
		$sql = "select * from sal_service_type where id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->id_char = $row['id_char'];
			$this->service_type = $row['service_type'];
			$this->type_str = $row['type_str'];
			$this->name = $row['name'];
			$this->u_code = $row['u_code'];
			$this->class_id = $row['class_id'];
			$this->z_index = $row['z_index'];
			$this->z_display = $row['z_display'];
            $this->infoJson=array();
            $infoRows = Yii::app()->db->createCommand()->select("*")
                ->from("sal_service_type_info")
                ->where("type_id=".$index)
                ->order("z_index asc,id asc")->queryAll();
            if($infoRows){
                foreach ($infoRows as $infoRow){
                    $this->infoJson[]=array(
                        "id"=>$bool?null:$infoRow["id"],
                        "name"=>$infoRow["name"],
                        "uCode"=>$infoRow["u_code"],
                        "uType"=>$infoRow["u_type"],
                        "inputType"=>$infoRow["input_type"],
                        "defaultValue"=>$infoRow["default_value"],
                        "func"=>$infoRow["func"],
                        "eolBool"=>$infoRow["eol_bool"],
                        "zIndex"=>$infoRow["z_index"],
                        "zDisplay"=>$infoRow["z_display"],
                        "totalBool"=>$infoRow["total_bool"],
                        "uflag"=>$bool?"Y":"N",
                    );
                }
            }
		}
		return true;
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->save($connection);
			$this->saveTypeInfo();
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveTypeInfo()
	{
        $uid = Yii::app()->user->id;
        if(!empty($this->saveInfo)){
            foreach ($this->saveInfo as $row){
                switch ($row["uflag"]){
                    case "Y"://
                        $row["totalBool"] = $row["inputType"]=='yearAmount'?1:$row["totalBool"];
                        if(!empty($row["id"])){
                            Yii::app()->db->createCommand()->update("sal_service_type_info",array(
                                "name"=>$row["name"],
                                "u_code"=>$row["uCode"],
                                "u_type"=>empty($row["uType"])||!is_numeric($row["uType"])?null:intval($row["uType"]),
                                "input_type"=>$row["inputType"],
                                "func"=>$row["func"],
                                "eol_bool"=>$row["eolBool"],
                                "default_value"=>$row["defaultValue"]===""?null:$row["defaultValue"],
                                "z_index"=>$row["zIndex"],
                                "z_display"=>$row["zDisplay"],
                                "total_bool"=>in_array($row["inputType"],array('amount','yearAmount'))?$row["totalBool"]:0,
                                "luu"=>$uid,
                            ),"id=:id",array("id"=>$row["id"]));
                        }else{
                            Yii::app()->db->createCommand()->insert("sal_service_type_info",array(
                                "name"=>$row["name"],
                                "u_code"=>$row["uCode"],
                                "u_type"=>empty($row["uType"])||!is_numeric($row["uType"])?null:intval($row["uType"]),
                                "input_type"=>$row["inputType"],
                                "func"=>$row["func"],
                                "eol_bool"=>$row["eolBool"],
                                "default_value"=>$row["defaultValue"]===""?null:$row["defaultValue"],
                                "z_index"=>$row["zIndex"],
                                "z_display"=>$row["zDisplay"],
                                "type_id"=>$this->id,
                                "total_bool"=>in_array($row["inputType"],array('amount','yearAmount'))?$row["totalBool"]:0,
                                "lcu"=>$uid,
                            ));
                            $id = Yii::app()->db->getLastInsertID();
                            Yii::app()->db->createCommand()->update("sal_service_type_info",array(
                                "id_char"=>$this->id_char."_".$id,
                            ),"id=:id",array("id"=>$id));
                        }
                        break;
                    case "D"://
                        Yii::app()->db->createCommand()->delete("sal_service_type_info","id=".$row["id"]);
                        break;
                }
            }
        }
    }

	protected function save(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_service_type where id = :id";
                Yii::app()->db->createCommand()->delete("sal_service_type_info","type_id=".$this->id);
				break;
			case 'new':
				$sql = "insert into sal_service_type(
						id_char,name,service_type,class_id,type_str,u_code, z_index, z_display, lcu, luu) values (
						:char_id,:name,:service_type,:class_id,:type_str,:u_code, :z_index, :z_display, :lcu, :luu)";
				break;
			case 'edit':
				$sql = "update sal_service_type set 
					name = :name, 
					service_type = :service_type, 
					class_id = :class_id, 
					type_str = :type_str, 
					u_code = :u_code,
					z_index = :z_index,
					z_display = :z_display,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':service_type')!==false)
			$command->bindParam(':service_type',$this->service_type,PDO::PARAM_INT);
		if (strpos($sql,':class_id')!==false){
            $this->class_id = empty($this->class_id)?null:intval($this->class_id);
            $command->bindParam(':class_id',$this->class_id,PDO::PARAM_INT);
        }
		if (strpos($sql,':type_str')!==false)
			$command->bindParam(':type_str',$this->type_str,PDO::PARAM_STR);
		if (strpos($sql,':char_id')!==false)
			$command->bindParam(':char_id',$this->id_char,PDO::PARAM_STR);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':u_code')!==false)
			$command->bindParam(':u_code',$this->u_code,PDO::PARAM_STR);
		if (strpos($sql,':z_index')!==false)
			$command->bindParam(':z_index',$this->z_index,PDO::PARAM_STR);
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

	public function addOld(){
	    $rows = CGetName::serviceDefinitionEx();
	    $i=0;
	    foreach ($rows as $id_char => $row){
            $typeRow = Yii::app()->db->createCommand()->select("*")
                ->from("sal_service_type")
                ->where("id_char=:id_char",array(":id_char"=>$id_char))->queryRow();
            if($typeRow){
                continue;
            }
            $i++;
            Yii::app()->db->createCommand()->insert("sal_service_type",array(
                "id_char"=>$id_char,
                "service_type"=>0,
                "type_str"=>$row["type"],
                "name"=>$row["name"],
                "z_index"=>$i*10,
                "z_display"=>1,
                "lcu"=>"admin",
            ));
            $type_id = Yii::app()->db->getLastInsertID();
            $j=0;
            foreach ($row["items"] as $j_char=>$item){
                $j++;
                Yii::app()->db->createCommand()->insert("sal_service_type_info",array(
                    "type_id"=>$type_id,
                    "id_char"=>$j_char,
                    "name"=>$item["name"],
                    "input_type"=>$item["type"],
                    "total_bool"=>$item["type"]=='yearAmount'||in_array($j_char, VisitForm::$amount_fields)?1:0,
                    "func"=>isset($item["func"])?$item["func"]:null,
                    "eol_bool"=>isset($item["eol"])&&$item["eol"]?1:0,
                    "z_index"=>$j*10,
                    "z_display"=>1,
                    "lcu"=>"admin",
                ));
            }
        }
    }

    public function isReadonly(){
	    return $this->getScenario()=='view';
    }
}
