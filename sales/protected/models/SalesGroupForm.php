<?php

class SalesGroupForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $dataJson=array();
	public $dataStr;

    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'dataJson'=>"人员组织架构",
            'dataStr'=>"人员组织架构",
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id','safe'),
			array('dataStr','required'),
			array('dataStr','validateDataStr'),
		);
	}

    public function validateDataStr($attribute, $param) {
        if(!empty($this->dataStr)){
            $dataArr = json_decode($this->dataStr,true);
            if(empty($dataArr)){
                $this->addError($attribute, "请至少配置一个员工");
            }
            $this->dataJson = $dataArr;
        }
    }

	public function retrieveData()
	{
        $this->dataJson = $this->getForeachList(0);
        return true;
	}

	protected function getForeachList($prev_id){
	    $list=array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.*,CONCAT(b.name,'(',b.code,')') as name")
            ->from("sal_group a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("a.prev_id=:prev_id",array(":prev_id"=>$prev_id))->order("id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[]=array(
                    "id"=>$row["id"],
                    "name"=>$row["name"],
                    "staff"=>$row["employee_id"],
                    "type"=>"N",
                    "list"=>$this->getForeachList($row["id"]),
                );
            }
        }
        return $list;
    }

	public static function getAllEmployeeList(){
	    $list=array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("id,CONCAT(name,'(',code,')') as name")
            ->from("hr{$suffix}.hr_employee")
            ->where("staff_status!=-1")->order("table_type asc,id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row["name"];
            }
        }
        return $list;
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
        $city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$this->foreachSave($this->dataJson,0,1);

		return true;
	}

	protected function foreachSave($rows,$add_id=0,$z_index=1){
	    if(!empty($rows)){
	        foreach ($rows as $row){
	            if(empty($row["staff"])||empty($row["name"])){
	                continue;
                }
	            switch ($row["type"]){
                    case "N"://新增及修改
                        if(!empty($row["id"])){//修改
                            Yii::app()->db->createCommand()->update("sal_group",array(
                                "employee_id"=>$row["staff"],
                                "employee_name"=>$row["name"],
                            ),"id=:id",array(":id"=>$row["id"]));
                            $next_id = $row["id"];
                        }else{//新增
                            Yii::app()->db->createCommand()->insert("sal_group",array(
                                "employee_id"=>$row["staff"],
                                "employee_name"=>$row["name"],
                                "prev_id"=>$add_id,
                                "z_index"=>$z_index,
                            ));
                            $next_id = Yii::app()->db->getLastInsertID();
                        }
                        if(isset($row["list"])&&!empty($row["list"])){
                            $z_index++;
                            $this->foreachSave($row["list"],$next_id,$z_index);
                        }
                        break;
                    case "D"://删除
                        if(!empty($row["id"])){
                            $deleteID=CGetName::getGroupNextIDByID($row["id"]);
                            $deleteStr = implode(",",$deleteID);
                            Yii::app()->db->createCommand()->delete("sal_group","id in ({$deleteStr})");
                        }
                        break;
                }
            }
        }
    }

	public function parentDataJsonHtml(){
	    $html="";
	    if(!empty($this->dataJson)){
	        $html.=$this->foreachHtmlByRow($this->dataJson);
        }
	    return $html;
    }

    protected function foreachHtmlByRow($rows){
        $html="";
	    if(!empty($rows)){
	        foreach ($rows as $key=>$row){
	            $html.=$this->getMediaHtml($row);
            }
        }
	    return $html;
    }

    public function getMediaHtml($row=array()){
        $html="";
        if(empty($row)){
            $row=array(
                "id"=>0,
                "name"=>"",
                "staff"=>0,
            );
        }
        if(!isset($row["id"])){
            var_dump($row);die();
        }
        $dataList = array(
            "data-id"=>$row["id"],
            "data-name"=>$row["name"],
            "data-staff"=>$row["staff"],
            "data-type"=>"N",
        );
        $dataStr=TbHtml::renderAttributes($dataList);
        $html.="<div class='media active' {$dataStr}>";
        $html.='<div class="media-left media-left-dashed"><span class="fa fa-caret-right"></span></div>';
        $html.="<div class='media-body'>";
        $html.="<b class='media-heading'>";
        $html.='<span class="click-name">'.$row["name"].'</span>';
        $html.='<span>&nbsp;</span>';
        $html.='<span class="fa fa-plus add_media"></span>';
        $html.='<span class="fa fa-close del_media text-danger"></span>';
        $html.='</b>';
        if(isset($row["list"])&&!empty($row["list"])){
            $html.=$this->foreachHtmlByRow($row["list"]);
        }
        $html.="</div>";
        $html.="</div>";
        return $html;
    }
}