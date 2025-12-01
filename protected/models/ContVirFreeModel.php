<?php
//同步服务频次
class ContVirFreeModel {
	public static $data=array(
		"u_id"=>array("requite"=>true,"function"=>"validateUID"),//
		"first_date"=>array("requite"=>false,"function"=>"validateDate"),//首次日期
		"fast_date"=>array("requite"=>false,"function"=>"validateDate"),//常规开始日期
		"main_code"=>array("requite"=>false,"function"=>"validateLbsMain"),//服务主体
		"update_staff"=>array("requite"=>true,"function"=>"validateLuu"),//修改人
		"update_date"=>array("requite"=>true,"function"=>"validateDate"),//修改时间
        "free_text"=>array("requite"=>false),//服务频次（直接返回服务频次的文字）
		"free_list"=>array("requite"=>false,"function"=>"validateFreeList"),//服务频次
	);
	//{"u_id"=>"129670","first_date"=>"2025-9-28","fast_date"=>"2025-9-28","main_code"=>"LBS-ZH","free_text"=>"1月第一周第4天，2月第三周第4天，2月第四周第6天","free_list"=>''}

    public function validateFreeList(&$data,$keyStr){
        $freeText = key_exists("free_text",$data)?$data["free_text"]:0;
        $freeList = key_exists($keyStr,$data)?$data[$keyStr]:array();
        $u_service_json=array("title"=>$freeText,"list"=>array());
        if(!empty($freeList)){
            foreach ($freeList as $row){
                if(empty($row["month_cycle"])||!is_numeric($row["month_cycle"])){
                    return array("bool"=>false,"error"=>"month_cycle不能为空或只能是数字");
                }
                if(!is_numeric($row["week_cycle"])){
                    return array("bool"=>false,"error"=>"week_cycle只能是数字");
                }
                if(!is_numeric($row["day_cycle"])){
                    return array("bool"=>false,"error"=>"day_cycle只能是数字");
                }
                $temp=array(
                    "vir_id"=>$data["id"],
                    "month_cycle"=>intval($row["month_cycle"]),
                    "week_cycle"=>intval($row["week_cycle"]),
                    "day_cycle"=>intval($row["day_cycle"]),
                );
                if(key_exists("unit_price",$row)){
                    $temp["unit_price"]=empty($row["unit_price"])?0:floatval($row["unit_price"]);
                }
                if(key_exists("contract_date",$row)){
                    $temp["contract_date"]=empty($row["contract_date"])?null:$row["contract_date"];
                }
                if(key_exists("cycle_text",$row)){
                    $temp["cycle_text"]=empty($row["cycle_text"])?null:$row["cycle_text"];
                }
                if(key_exists("is_del",$row)){
                    $temp["is_del"]=empty($row["is_del"])?0:$row["is_del"];
                }
                $u_service_json["list"][]=$temp;
            }
        }
        $data["u_service_json"]=$u_service_json;
        return array("bool"=>true);
    }

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

    public function validateUID(&$data,$keyStr){
        $u_id = key_exists($keyStr,$data)?$data[$keyStr]:0;
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
            ->where("u_id=:u_id",array(":u_id"=>$u_id))->queryRow();
        if($row){
            $data["id"]=$row["id"];
            $data["virRow"]=$row;
            return array("bool"=>true);
        }else{
            return array("bool"=>false,"error"=>"没有找到对应的虚拟合约：{$u_id}");
        }
    }

    public function validateDate(&$data,$keyStr){
        $date = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(empty($date)){
            $data[$keyStr]=null;
        }elseif(strtotime($date)===false){
            return array("bool"=>false,"error"=>"只能是日期格式({$date})");
        }
        return array("bool"=>true);
    }

    public function validateLbsMain(&$data,$keyStr){
        $service_main = key_exists($keyStr,$data)?$data[$keyStr]:0;
        $city = $data["virRow"]["city"];
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_main_lbs")
            ->where("mh_code=:mh_code",
                array(":mh_code"=>$service_main)
            )->queryRow();
        if($row){
            $data["service_main"]=$row["id"];
            return array("bool"=>true);
        }else{
            return array("bool"=>false,"error"=>"城市{$city}没有服务主体编号：{$service_main}");
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
            "first_date","fast_date","service_main","luu"
        );
		foreach ($updateKey as $item){
			if(key_exists($item,$saveData)){
				$data[$item] = $saveData[$item];
			}
		}
        $data["u_service_json"]=json_encode($saveData["u_service_json"],JSON_UNESCAPED_UNICODE);
        $data["lud"]=$saveData["update_date"];
        //保存服务频次
        if(!empty($saveData["u_service_json"]["list"])){
            //清空旧的全部频次记录
            Yii::app()->db->createCommand()->delete("sal_contract_vir_week","vir_id=:vir_id",array(":vir_id"=>$saveData["id"]));
            foreach ($saveData["u_service_json"]["list"] as $temp){
                $temp["lcu"]=$data["luu"];
                $temp["lcd"]=$data["lud"];
                Yii::app()->db->createCommand()->insert("sal_contract_vir_week",$temp);
            }
        }
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
                $connection->createCommand()->update("sal_contract_virtual",$saveTable,"id={$id}");
                Yii::app()->db->createCommand()->insert("sal_contract_history",array(
                    "table_id"=>$id,
                    "table_type"=>7,
                    "history_type"=>2,
                    "history_html"=>"<span>派单系统修改</span>",
                    "lcu"=>$saveData["luu"],
                ));//
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
