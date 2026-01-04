<?php

class ClientPersonForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $clue_id;
	public $num_code=0;
	public $clue_store_id=0;
	public $person_code;
	public $cust_person;
	public $cust_tel;
	public $cust_email;
	public $cust_person_role;
	public $sex;
	public $person_pws;
	public $z_display=1;
	public $u_group_id;
	public $u_id;

    public $clientHeadRow;
    public $clientStoreRow;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		$list = array(
            'clue_id'=>Yii::t('clue','client id'),//
            'clue_store_id'=>Yii::t('clue','client store'),//
            'person_code'=>Yii::t('clue','person code'),//
            'cust_person'=>Yii::t('clue','person name'),//
            'cust_tel'=>Yii::t('clue','person tel'),//
            'cust_email'=>Yii::t('clue','person email'),//
            'cust_person_role'=>Yii::t('clue','person role'),//
            'sex'=>Yii::t('clue','person sex'),//
            'person_pws'=>Yii::t('clue','person pws'),//
            'z_display'=>Yii::t('clue','z display'),//开票地址
		);
		return $list;
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
	    $list = array();
        $list[]=array('id,person_code,person_pws,z_display,sex,cust_email','safe');
        $list[]=array('clue_id','required');
        $list[]=array('clue_store_id,cust_person,cust_tel,cust_person_role','required','on'=>array("new","edit"));
        $list[]=array('clue_id','validateClueID');
        $list[]=array('id','validateID');
		return $list;
	}

    public function validateID($attribute, $param) {
        $scenario = $this->getScenario();
        if($scenario=="new"){
            $storeRow = Yii::app()->db->createCommand()->select("id")
                ->from("sal_clue_person")
                ->where("cust_person=:cust_person and clue_id=:clue_id and clue_store_id=0",array(
                    ":cust_person"=>$this->cust_person,
                    ":clue_id"=>$this->clue_id
                ))->queryRow();
            if($storeRow){
                $this->addError($attribute, "联系人名称已存在，无法重复添加");
            }
        }else{
            $storeRow = Yii::app()->db->createCommand()->select("a.*")
                ->from("sal_clue_person a")
                ->where("a.id=:id",array(":id"=>$this->id))->queryRow();
            if($storeRow){
            }else{
                $this->addError($attribute, "数据异常，请刷新重试");
            }
        }
    }

    public function validateClueID($attribute, $param) {
        $clientHeadModel = new ClientHeadForm("view");
        if($clientHeadModel->retrieveData($this->clue_id)){
            $this->clientHeadRow = $clientHeadModel->getAttributes();
        }else{
            $this->addError($attribute, "客户不存在，请刷新重试");
        }
    }

	public function retrieveData($index)
	{
        $index = empty($index)||!is_numeric($index)?0:intval($index);
		$sql = "select a.* from sal_clue_person a where a.id=".$index." ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
            $this->clue_id = $row['clue_id'];
            $this->clue_store_id = $row['clue_store_id'];
            $this->person_code = $row['person_code'];
            $this->cust_person = $row['cust_person'];
            $this->cust_tel = $row['cust_tel'];
            $this->cust_email = $row['cust_email'];
            $this->cust_person_role = $row['cust_person_role'];
            $this->sex = $row['sex'];
            $this->person_pws = $row['person_pws'];
            $this->z_display = $row['z_display'];
            $this->u_id = $row['u_id'];
            $this->u_group_id = $row['u_group_id'];

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
                //如果手机号码为空，则不创建联络人
                if(empty($this->cust_tel)){
                    return true;
                }
                $connection->createCommand()->insert("sal_clue_person",array(
                    "clue_id"=>$this->clue_id,
                    "clue_store_id"=>$this->clue_store_id,
                    "cust_person"=>$this->cust_person,
                    "cust_tel"=>$this->cust_tel,
                    "cust_email"=>$this->cust_email,
                    "cust_person_role"=>$this->cust_person_role,
                    "sex"=>$this->sex,
                    "lcu"=>$uid,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
                $this->person_code = self::computeCodeX($this->clue_id,$this->clue_store_id,$this->id);
                $connection->createCommand()->update("sal_clue_person",array(
                    "person_code"=>$this->person_code,
                ),"id=:id",array(":id"=>$this->id));
                $this->setScenario("edit");
                $this->sendDataByU();
                break;
            case "edit":
                //如果手机号码为空，则不更新联络人
                if(empty($this->cust_tel)){
                    return true;
                }
                $connection->createCommand()->update("sal_clue_person",array(
                    "cust_person"=>$this->cust_person,
                    "cust_tel"=>$this->cust_tel,
                    "cust_email"=>$this->cust_email,
                    "cust_person_role"=>$this->cust_person_role,
                    "sex"=>$this->sex,
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                $this->sendDataByU();
                break;
            case "delete":
                $connection->createCommand()->update("sal_clue_person",array(
                    "z_display"=>0,
                    "status"=>4,
                    "luu"=>$uid,
                ),"id=:id",array(":id"=>$this->id));
                break;
        }
		return true;
	}

    public static function saveUPersonDataByClueModel($model,$uid=''){
	    //如果手机号码为空，则不创建联络人
        if(empty($model->cust_tel)){
            return false;
        }
        $uid = empty($uid)?Yii::app()->user->id:$uid;
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_clue_person")
            ->where("cust_person=:person and clue_id=:id and clue_store_id=0",array(":id"=>$model->id,":person"=>$model->cust_person))->queryRow();
        if($row){
            Yii::app()->db->createCommand()->update("sal_clue_person",array(
                "cust_person"=>$model->cust_person,
                "cust_tel"=>$model->cust_tel,
                "cust_email"=>$model->cust_email,
                "cust_person_role"=>$model->cust_person_role,
                "luu"=>$uid,
            ),"id=:id",array(":id"=>$row["id"]));
        }else{
            Yii::app()->db->createCommand()->insert("sal_clue_person",array(
                "clue_id"=>$model->id,
                "clue_store_id"=>0,
                "cust_person"=>$model->cust_person,
                "cust_tel"=>$model->cust_tel,
                "cust_email"=>$model->cust_email,
                "cust_person_role"=>$model->cust_person_role,
                "lcu"=>$uid,
            ));
            $id = Yii::app()->db->getLastInsertID();
            $person_code=ClientPersonForm::computeCodeX($model->id,0,$id);
            Yii::app()->db->createCommand()->update("sal_clue_person",array(
                "person_code"=>$person_code,
            ),"id=:id",array(":id"=>$id));
        }
    }

    public static function saveUPersonDataByStoreModel($model){
        //如果手机号码为空，则不创建联络人
        if(empty($model->cust_tel)){
            return false;
        }
        $uid = Yii::app()->user->id;
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_clue_person")
            ->where("cust_person=:person and clue_store_id=:id",array(":id"=>$model->id,":person"=>$model->cust_person))->queryRow();
        if($row){
            Yii::app()->db->createCommand()->update("sal_clue_person",array(
                "cust_person"=>$model->cust_person,
                "cust_tel"=>$model->cust_tel,
                "cust_email"=>$model->cust_email,
                "cust_person_role"=>$model->cust_person_role,
                "luu"=>$uid,
            ),"id=:id",array(":id"=>$row["id"]));
        }else{
            Yii::app()->db->createCommand()->insert("sal_clue_person",array(
                "clue_id"=>$model->clue_id,
                "clue_store_id"=>$model->id,
                "cust_person"=>$model->cust_person,
                "cust_tel"=>$model->cust_tel,
                "cust_email"=>$model->cust_email,
                "cust_person_role"=>$model->cust_person_role,
                "lcu"=>$uid,
            ));
            $id = Yii::app()->db->getLastInsertID();
            $person_code=ClientPersonForm::computeCodeX($model->clue_id,$model->id,$id);
            Yii::app()->db->createCommand()->update("sal_clue_person",array(
                "person_code"=>$person_code,
            ),"id=:id",array(":id"=>$id));
        }
    }

    //发送数据至派单系统
    public function sendDataByU(){
        if(in_array($this->getScenario(),array("new","edit"))){
            $uClientModel = new CurlNotesByClient();
            if(empty($this->clientHeadRow["u_id"])){//客户未同步，则同步客户信息
                $uClientModel->putDataByClientID($this->clue_id);
            }else{
                $uClientModel->putPersonDataByPersonID($this->id,$this->clientHeadRow);
            }
            $uClientModel->setOutContentByData();
            $uClientModel->saveCurlToApi();
        }
    }

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
		return $rtn;
	}

    public static function computeCodeX($clue_id,$clue_store_id,$person_id){
        $preCode="";
        if(empty($clue_store_id)){
            $clue_store_id=0;
            $row = Yii::app()->db->createCommand()->select("clue_code")->from("sal_clue")
                ->where("id=:id",array(":id"=>$clue_id))->queryRow();
            if($row){
                $preCode=$row["clue_code"];
            }
        }else{
            $row = Yii::app()->db->createCommand()->select("store_code")->from("sal_clue_store")
                ->where("id=:id",array(":id"=>$clue_store_id))->queryRow();
            if($row){
                $preCode=$row["store_code"];
            }
        }
        $preCode.="-";
        $row = Yii::app()->db->createCommand()->select("max(person_code) as max_code")->from("sal_clue_person")
            ->where("clue_id=:clue_id and clue_store_id=:clue_store_id and person_code like'{$preCode}%' and id<$person_id",array(
                ":clue_id"=>$clue_id,":clue_store_id"=>$clue_store_id
            ))->queryRow();
        if($row){
            $num = str_replace($preCode,"",$row["max_code"]);
            $num = is_numeric($num)?intval($num):-1;
            $num++;
            if($num<999){
                $num+= 1000;
                $num = "".$num;
                $num = mb_substr($num,1);
            }
            $preCode.=$num;
        }else{
            $preCode.="000";
        }
        return $preCode;
    }

	public function isReadonly() {
		return $this->getScenario()=='view';
	}
}
