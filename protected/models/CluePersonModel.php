<?php
//设置联系人密码
class CluePersonModel {
	public static $data=array(
        "table_type"=>array("requite"=>true,"function"=>"validateType"),//1:集团，2：客户
		"u_id"=>array("requite"=>true,"function"=>"validateUID"),//
		"person_pws"=>array("requite"=>true,"function"=>"validatePWD"),//1：已设置，0：未设置
        "update_staff"=>array("requite"=>true,"function"=>"validateLuu"),//修改人
        "update_date"=>array("requite"=>true,"function"=>"validateDate"),//修改时间
	);

    public function validateLuu(&$data,$keyStr){
        $suffix = Yii::app()->params['envSuffix'];
        $update_staff = key_exists($keyStr,$data)?$data[$keyStr]:0;
        $row = Yii::app()->db->createCommand()->select("a.user_id")->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->where("b.code=:code",array(":code"=>$update_staff))->queryRow();
        if($row){
            $data["luu"]=$row["user_id"];
        }else{
            $data["luu"]="admin";
        }
        return array("bool"=>true);
    }

    public function validatePWD(&$data,$keyStr){
        $person_pws = key_exists($keyStr,$data)?$data[$keyStr]:1;
        if($person_pws!=1){
            $person_pws=null;
        }
        $data[$keyStr]=$person_pws;
        return array("bool"=>true);
    }

    public function validateType(&$data,$keyStr){
        $table_type = key_exists($keyStr,$data)?$data[$keyStr]:1;
        if($table_type!=1){
            $table_type=2;
        }
        $data[$keyStr]=$table_type;
        return array("bool"=>true);
    }


    public function validateUID(&$data,$keyStr){
        if($data["table_type"]==1){//1:集团
            $storeSQL = " and clue_store_id=0";
        }else{
            $storeSQL = " and clue_store_id!=0";
        }
        $u_id = key_exists($keyStr,$data)?$data[$keyStr]:0;
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_person")
            ->where("u_id=:u_id {$storeSQL}",array(":u_id"=>$u_id))->order("id desc")->queryRow();
        if($row){
            $data["id"]=$row["id"];
            $data["personRow"]=$row;
            return array("bool"=>true);
        }else{
            return array("bool"=>false,"error"=>"没有找到对应的联系人：{$u_id}");
        }
    }

    public function validateDate(&$data,$keyStr){
        $date = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(empty($date)||strtotime($date)===false){
            return array("bool"=>false,"error"=>"只能是日期格式({$date})");
        }else{
            return array("bool"=>true);
        }
    }

    //验证数据
	public function validateRow(&$data){
        if(!is_array($data)){
            return array("bool"=>false,"error"=>"请求内容非法");
        }
		foreach (self::$data as $keyStr=>$item){
			$requite = key_exists("requite",$item)?$item["requite"]:false;
			$maxLen = key_exists("maxLen",$item)?$item["maxLen"]:0;
			$fun = key_exists("function",$item)?$item["function"]:"";
            $keyStr = key_exists("keyStr",$item)?$item["keyStr"]:$keyStr;
            if($requite&&(!key_exists($keyStr,$data)||$data[$keyStr]===""||$data[$keyStr]===null)){
                return array("bool"=>false,"error"=>$keyStr."不能为空");
            }
			if($maxLen>0&&key_exists($keyStr,$data)&&!is_array($data[$keyStr])&&mb_strlen($data[$keyStr],'UTF-8')>$maxLen){
                $data[$keyStr] = mb_substr($data[$keyStr],0,$maxLen,'UTF-8');
                //return array("bool"=>false,"error"=>$keyStr."的长度不能大于{$maxLen}");
			}
			if(!empty($fun)){//函数验证及自动完成
                $result = $this->$fun($data,$keyStr);
                if($result["bool"]===false){ //验证失败不继续验证
					return $result;
                }
			}
		}
		return array("bool"=>true,"saveData"=>$data);
	}

    //保存的数据
    protected function saveTableForSaveData($saveData){
        $data = array();
        $updateKey = array(
            "person_pws","luu"
        );
		foreach ($updateKey as $item){
			if(key_exists($item,$saveData)){
				$data[$item] = $saveData[$item];
			}
		}
        $data["lud"]=$saveData["update_date"];
        return $data;
    }

	public function syncChangeOne($row) {
        $suffix = Yii::app()->params['envSuffix'];
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $result = self::validateRow($row);
            if($result["bool"]){
				$saveData = $result["saveData"];
				$id = $saveData["id"];
                $saveTable = $this->saveTableForSaveData($saveData);
                $connection->createCommand()->update("sal_clue_person",$saveTable,"id={$id}");
                $transaction->commit();
                return array('code'=>200,'msg'=>'修改成功,id:'.$id);
            }else{
                $transaction->commit();//失败也需要结束事务
                return array('code'=>400,'msg'=>$result["error"]);
			}
        }catch(Exception $e) {
            $transaction->rollback();
            return array('code'=>400,'msg'=>$e->getMessage());
        }
    }

}
?>
