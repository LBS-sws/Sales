<?php

class ClubSettingForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $explain_text;
	public $effect_date;
	public $set_json=array();

    public static $noCity=array('CS','H-N','HK','TC','ZS1','TP','TY','KS','TN','XM','ZY','MO','RN','MY','WL','JMS','RW');
    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'explain_text'=>Yii::t('club','explain'),
            'effect_date'=>Yii::t('club','effect date'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,explain_text,effect_date,set_json','safe'),
			array('effect_date,set_json','required'),
            array('set_json','validateJson'),
		);
	}

    public function validateJson($attribute, $params) {
	    $list = ClubSettingForm::settingList();
	    foreach ($list as $key => $row){
	        unset($list[$key]["fun"]);
            if(!key_exists($key,$this->set_json)){
                $this->addError($attribute, Yii::t("club",$row["name"])."不存在，请刷新重试");
                return false;
            }
            if(!isset($this->set_json[$key]["number"])||empty($this->set_json[$key]["number"])){
                $this->addError($attribute, Yii::t("club",$row["name"])." 不可为空白");
            }
            $list[$key]["number"] = $this->set_json[$key]["number"];
            $list[$key]["type"] = $this->set_json[$key]["type"];
        }
        $this->set_json = $list;
    }

    public static function typeList(){
	    return array(
	        1=>Yii::t("club","ratio"),//百分比人數
	        2=>Yii::t("club","fixed"),//固定人數
        );
    }

    public static function settingList(){
	    $list = array(
            //销售精英
	        "sales_elite"=>array("name"=>"sales_elite","fun"=>"validateTrue","type"=>1,"number"=>10),
            //最佳进步表现人员
            "sales_forward"=>array("name"=>"sales_forward","fun"=>"validateTrue","type"=>1,"number"=>4),
            //新业务杰出表现人员
            "sales_out"=>array("name"=>"sales_out","fun"=>"validateOut","type"=>1,"number"=>4),
            //陌生拜访记录最多销售
            "sales_visit"=>array("name"=>"sales_visit","fun"=>"validateVisit","type"=>2,"number"=>1),
            //总监推荐人选
            "sales_rec"=>array("name"=>"sales_rec","fun"=>"validateTrue","type"=>2,"number"=>1),
        );
	    return $list;
    }

    //获取销售总人数
    public static function getSalesCount($date=""){
        $date = empty($date)?date("Y-m-d"):General::toMyDate($date);
        $noCity = self::$noCity;
        $noCitySql = implode("','",$noCity);
        $suffix = Yii::app()->params['envSuffix'];
        $scalar = Yii::app()->db->createCommand()->select("count(a.id)")->from("hr{$suffix}.hr_employee a")
            ->leftJoin("hr{$suffix}.hr_dept b","a.position=b.id")
            ->leftJoin("hr{$suffix}.hr_binding f","a.id=f.employee_id")
            ->where("f.user_id is not null and replace(a.entry_time,'/', '-')<='{$date}' and b.dept_class='Sales' and a.city not in ('{$noCitySql}') and b.manager_leave=1 and a.staff_status!=-1")->queryScalar();
        return $scalar;
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from sal_club_setting where id='".$index."'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->explain_text = $row['explain_text'];
			$this->effect_date = General::toDate($row['effect_date']);
			$this->set_json = json_decode($row['set_json'],true);
            return true;
		}else{
		    return false;
        }
	}


    //获取俱乐部配置
    public static function getClubSettingForDate($date,$salesCount=""){
        $date = General::toMyDate($date);
        $row = Yii::app()->db->createCommand()->select("set_json")->from("sal_club_setting")
            ->where("effect_date<=:date",array(":date"=>$date))->order("effect_date desc")->queryRow();
        $row = $row?json_decode($row["set_json"],true):array();
        $list = ClubSettingForm::settingList();
        if($salesCount===""||!is_numeric($salesCount)){
            $salesCount = self::getSalesCount($date);
        }
        foreach ($list as $key => $setting){
            if(key_exists($key,$row)){
                $list[$key]["number"] = $row[$key]["number"];
                $list[$key]["type"] = $row[$key]["type"];
                $list[$key]["userList"] = array();
                if($row[$key]["type"]==1){ //百分比
                    $people = $salesCount*$row[$key]["number"]*0.01;
                    $people = round($people);
                    $people = empty($people)?1:$people;//不足一人按照一人计算
                    $list[$key]["people"] = $people;
                }else{
                    $list[$key]["people"] = $row[$key]["number"];
                }
            }
        }
        return $list;
    }

    public function getSettingHtml(){
        $lists = ClubSettingForm::settingList();
        $className = get_class($this);
        $html="";
        foreach ($lists as $key=>$list){
            $setting = key_exists($key,$this->set_json)?$this->set_json[$key]:$list;
            $number = key_exists("number",$setting)?$setting["number"]:"";
            $type = key_exists("type",$setting)?$setting["type"]:1;
            $str = $type==1?"%":"人";
            $html.= '<div class="form-group ratioPeople">';
            $html.= Tbhtml::label(Yii::t("club",$list["name"]),'',array('class'=>"col-lg-2 control-label",'required'=>true));
            $html.= '<div class="col-lg-4">';
            $html.= '<div class="input-group">';
            $html.= '<div class="input-group-btn">';
            $html.=TbHtml::dropDownList("{$className}[set_json][{$key}][type]",$type,ClubSettingForm::typeList(),array('readonly'=>($this->scenario=='view'),'class'=>'changeSelect'));
            $html.= '</div>';
            $html.= TbHtml::numberField("{$className}[set_json][{$key}][number]",$number,array('min'=>0,'readonly'=>($this->scenario=='view'),'class'=>'forNumber','append'=>$str));
            $html.= '</div>';
            $html.= '</div>';
            $html.= '<div class="col-lg-4">';
            $html.= '<p class="form-control-static salesNum"></p>';
            $html.= '</div>';
            $html.= '</div>';
        }
        return $html;
    }
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDataForSql($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_club_setting where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_club_setting(
						explain_text, effect_date, set_json, lcu, lcd) values (
						:explain_text, :effect_date, :set_json, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update sal_club_setting set 
					explain_text = :explain_text, 
					effect_date = :effect_date,
					set_json = :set_json,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':explain_text')!==false)
			$command->bindParam(':explain_text',$this->explain_text,PDO::PARAM_INT);
		if (strpos($sql,':effect_date')!==false)
			$command->bindParam(':effect_date',$this->effect_date,PDO::PARAM_INT);
		if (strpos($sql,':set_json')!==false){
            $set_json = json_encode($this->set_json);
            $command->bindParam(':set_json',$set_json,PDO::PARAM_STR);
        }

		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcd')!==false){
            $date = date("Y-m-d H:i:s");
            $command->bindParam(':lcd',$date,PDO::PARAM_STR);
        }
		$command->execute();

        if ($this->scenario=='new')
            $this->id = Yii::app()->db->getLastInsertID();

		return true;
	}
}