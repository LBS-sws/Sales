<?php

class ImportContForm extends ImportVirForm
{
    protected $eveList = array(
        array("name"=>"主合同编号","key"=>"cont_code","fun"=>"valContCode","requite"=>true),
        array("name"=>"客户编号","key"=>"clue_id","fun"=>"valClientCode","requite"=>true),
        array("name"=>"主合同状态","key"=>"cont_status","fun"=>"valVirStatus","requite"=>true),
        array("name"=>"服务项目","key"=>"busine_name","fun"=>"valBusine","requite"=>true),
        array("name"=>"签约时间","key"=>"sign_date","fun"=>"valDate","requite"=>true),
        array("name"=>"合约开始时间","key"=>"cont_start_dt","fun"=>"valDate","requite"=>true),
        array("name"=>"合约结束时间","key"=>"cont_end_dt","fun"=>"valContEndDate","requite"=>true),
        array("name"=>"业务大类","key"=>"yewudalei","fun"=>"valYewudalei","requite"=>true),
        array("name"=>"主体公司","key"=>"lbs_main","fun"=>"valCodeMain","requite"=>true),
        array("name"=>"销售员工编号","key"=>"sales_id","fun"=>"valEmployee","requite"=>true),
        array("name"=>"门店总数量","key"=>"store_sum","fun"=>"valEmptyNumber","requite"=>true),
        array("name"=>"合约总金额","key"=>"total_amt","fun"=>"valEmptyNumber","requite"=>true),
        array("name"=>"服务总次数","key"=>"total_sum","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"结算方式","key"=>"settle_type","fun"=>"valSettleType","requite"=>false),
        array("name"=>"付款方式","key"=>"pay_type","fun"=>"valPayType","requite"=>false),
        array("name"=>"押金备注","key"=>"deposit_rmk","fun"=>"","requite"=>false),
        array("name"=>"已收押金","key"=>"deposit_amt","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"所需押金","key"=>"deposit_need","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"收费方式","key"=>"fee_type","fun"=>"valFeeType","requite"=>false),
        array("name"=>"预付月数","key"=>"pay_month","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"起始月","key"=>"pay_start","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"是否对账","key"=>"bill_bool","fun"=>"valEmptyYes","requite"=>false),
        array("name"=>"账单日","key"=>"bill_day","fun"=>"valBillDay","requite"=>false),
        array("name"=>"付款周期","key"=>"pay_week","fun"=>"valPayWeek","requite"=>false),
        array("name"=>"服务时长(分钟)","key"=>"service_timer","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"是否优先安排服务","key"=>"prioritize_service","fun"=>"valEmptyYes","requite"=>false),
        array("name"=>"应收期限","key"=>"receivable_day","fun"=>"valReceivableDay","requite"=>false),
        array("name"=>"剩余次数","key"=>"surplus_num","fun"=>"valEmptyInt","requite"=>false),
        array("name"=>"剩余金额","key"=>"surplus_amt","fun"=>"valEmptyNumber","requite"=>false),
        array("name"=>"终止或暂停日期","key"=>"stop_date","fun"=>"valDate","requite"=>false),
    );

    protected function valBusine(&$data,$keyStr,$item){
        $busineName = key_exists($keyStr,$data)?$data[$keyStr]:'';
        $busineNameList = empty($busineName)?array():explode(",",$busineName);
        if(!empty($busineNameList)){
            $ids=array();
            $names=array();
            foreach ($busineNameList as $itemName){
                $row = Yii::app()->db->createCommand()->select("id,service_type,id_char")->from("sal_service_type")
                    ->where("name=:name",array(":name"=>$itemName))->queryRow();
                if($row){
                    $ids[]=$row["id_char"];
                    $names[]=$itemName;
                }else{
                    $this->status="E";
                    $this->message=$item['name']."异常，不存在({$itemName})";
                }
            }
            $data["busine_id"]=implode(",",$ids);
            $data["busine_id_text"]=implode("、",$names);
        }else{
            $this->status="E";
            $this->message=$item['name']."不能为空";
        }
    }

    protected function valContCode(&$data,$keyStr,$item){
        $cont_code = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(!empty($cont_code)){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_contract")
                ->where("cont_code=:cont_code",array(":cont_code"=>$cont_code))->queryRow();
            if($row){
                $this->status="E";
                $this->message=$item['name']."已存在({$cont_code})";
            }
        }else{
            $this->status="E";
            $this->message=$item['name']."不能为空";
        }
    }

    protected function valClientCode(&$data,$keyStr,$item){
        $clue_code = key_exists($keyStr,$data)?$data[$keyStr]:'';
        if(!empty($clue_code)){
            $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue")
                ->where("clue_code=:clue_code",array(":clue_code"=>$clue_code))->queryRow();
            if($row){
                if (strpos($this->city_allow,"'{$row['city']}'")!==false){
                    $data[$keyStr]=$row["id"];
                    $data["clue_id"]=$row["id"];
                    $data["clue_type"]=$row["clue_type"];
                    $data["city"]=$row["city"];
                    $data["clueRow"]=$row;
                }else{
                    $this->status="E";
                    $this->message="你没有权限添加城市({$row["city"]})的主合约({$clue_code})";
                }
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$clue_code})";
            }
        }else{
            $this->status="E";
            $this->message=$item['name']."不能为空";
        }
    }

    protected function saveOneData($data){
        Yii::app()->db->createCommand()->insert("sal_clue_service",array(
            'clue_id'=>$data["clue_id"],
            'clue_type'=>$data["clueRow"]["clue_type"],
            'visit_type'=>$this->visit_type,
            'visit_obj'=>$this->visit_obj,
            'visit_obj_text'=>$this->visit_obj_text,
            'create_staff'=>$data["sales_id"],
            'busine_id'=>$data["busine_id"],
            'busine_id_text'=>$data["busine_id_text"],
            'sign_odds'=>100,
            'lbs_main'=>$data["lbs_main"],
            'predict_date'=>$data["sign_date"],
            'predict_amt'=>$data["total_amt"],
            'total_amt'=>$data["total_amt"],
            'total_num'=>1,
            'service_status'=>$data["cont_status"],
            "lcu"=>$this->username,
            'report_id'=>$this->id,
        ));
        $data["clue_service_id"] = Yii::app()->db->getLastInsertID();
        $contArr = array(
            'clue_id'=>$data["clue_id"],
            'clue_type'=>$data["clueRow"]["clue_type"],
            'clue_service_id'=>$data["clue_service_id"],
            'city'=>$data["clueRow"]["city"],
            'cont_code'=>$data["cont_code"],
            'sales_id'=>$data["sales_id"],
            'lbs_main'=>$data["lbs_main"],
            'predict_amt'=>$data["total_amt"],
            'store_sum'=>$data["store_sum"],
            'cont_type'=>1,
            'sign_type'=>1,
            'total_sum'=>$data["total_sum"],
            'total_amt'=>$data["total_amt"],
            'cont_status'=>$data["cont_status"],
            'stop_date'=>$data["stop_date"],
            'surplus_num'=>$data["surplus_num"],
            'surplus_amt'=>$data["surplus_amt"],
            'cont_start_dt'=>$data["cont_start_dt"],
            'cont_end_dt'=>$data["cont_end_dt"],
            'cont_month_len'=>$data["cont_month_len"],
            'sign_date'=>$data["sign_date"],
            'area_bool'=>"N",
            'group_bool'=>"N",
            'prioritize_service'=>$data["prioritize_service"],
            'service_timer'=>$data["service_timer"],
            'pay_type'=>$data["pay_type"],
            'pay_week'=>$data["pay_week"],
            'pay_month'=>$data["pay_month"],
            'pay_start'=>$data["pay_start"],
            'deposit_need'=>$data["deposit_need"],
            'deposit_amt'=>$data["deposit_amt"],
            'deposit_rmk'=>$data["deposit_rmk"],
            'fee_type'=>$data["fee_type"],
            'settle_type'=>$data["settle_type"],
            'bill_day'=>$data["bill_day"],
            'bill_bool'=>$data["bill_bool"],
            'receivable_day'=>$data["receivable_day"],
            'yewudalei'=>$data["yewudalei"],
            'busine_id'=>$data["busine_id"],
            'busine_id_text'=>$data["busine_id_text"],
            'report_id'=>$this->id,
            "lcu"=>$this->username,
        );
        Yii::app()->db->createCommand()->insert("sal_contract",$contArr);
        //sal_contract_sse
        $data["cont_id"] = Yii::app()->db->getLastInsertID();
        $contArr["cont_id"]=$data["cont_id"];
        $contArr["pro_code"]="PDL-".$data["cont_code"];
        $contArr["pro_type"]=$this->proTypeByStatus($data["cont_status"]);
        $contArr["pro_date"]=$data["sign_date"];
        $contArr["pro_remark"]="导入虚拟合约自动生成\n导入id：{$this->id}";
        $contArr["pro_status"]=30;
        $contArr["pro_change"]=$data["cont_status"]==30?$data["total_amt"]:$data["surplus_amt"];
        Yii::app()->db->createCommand()->insert("sal_contpro",$contArr);

        Yii::app()->db->createCommand()->update("sal_clue",array(
            "clue_status"=>ClueVirProModel::getClientStatusByClueID($data["clue_id"]),
        ),"id=:id",array(":id"=>$data["clue_id"]));
    }
}
