<?php

class MarketFun{

    //获取跟进类型
    public static function getStateTypeList(){
        return array(
            1=>Yii::t("market","state go"),
            2=>Yii::t("market","none"),
        );
    }

    //获取跟进类型名称
    public static function getStateNameToType($type){
        $list = self::getStateTypeList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    //获取分配类型
    public static function getAllowTypeList(){
        return array(
            1=>Yii::t("market","KA sales"),
            2=>Yii::t("market","city"),
        );
    }

    //获取分配类型名称
    public static function getAllowNameToType($type,$die=false){
        $list = array(
            1=>Yii::t("market","KA sales"),
            2=>Yii::t("market","city"),
            3=>Yii::t("market","employee"),
        );
        $type = "".$type;
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    //获取区域列表
    public static function getAreaList(){
        return array(
            ""=>"",//
            "HN"=>Yii::t("market","South China"),//华南
            "HD"=>Yii::t("market","East China"),//华东
            "HX"=>Yii::t("market","West China"),//华西
            "HB"=>Yii::t("market","North China"),//华北
        );
    }

    //获取区域名称
    public static function getAreaNameToType($type){
        $list = self::getAreaList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    //获取企业状态列表
    public static function getCompanyStateList(){
        return array(
            ""=>"",//
            "in"=>Yii::t("market","in operation"),//续存
            "close"=>Yii::t("market","close down"),//倒闭
        );
    }

    //获取企业状态名称
    public static function getCompanyStateNameToType($type){
        $list = self::getCompanyStateList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    //获取企业规模列表
    public static function getCompanySizeList(){
        return array(
            ""=>"",//
            "big"=>Yii::t("market","big"),//大
            "middle"=>Yii::t("market","middle"),//中
            "small"=>Yii::t("market","small"),//小
        );
    }

    //获取企业规模名称
    public static function getCompanySizeNameToType($type){
        $list = self::getCompanySizeList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    //获取企业分类列表
    public static function getCompanyTypeList(){
        return array(
            ""=>"",//
            "plant"=>Yii::t("market","food plant"),//食品加工厂
            "hotel"=>Yii::t("market","hotel chain"),//酒店连锁
            "food"=>Yii::t("market","food chain"),//饮食连锁
            "school"=>Yii::t("market","school"),//学校
            "government"=>Yii::t("market","government"),//政府
            "logistics"=>Yii::t("market","logistics chain"),//物流冷链
            "family"=>Yii::t("market","individual family"),//个人家庭
            "other"=>Yii::t("market","other"),//其它
        );
    }

    //获取企业分类名称
    public static function getCompanyTypeNameToType($type){
        $list = self::getCompanyTypeList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $type;
        }
    }

    //根据类型获取颜色
    public static function getTrColor($row){
        $status_type = $row["status_type"];
        switch ($status_type){
            case 0://未分配
                return"";
            case 1://系统退回
            case 2://手动退回
                return "text-maroon";
            case 5://已分配
                return "text-primary";
            case 6://跟进中
                return "text-teal";
            case 8://已拒绝
                return "text-gray";
            case 10://已完成
                return "text-success";
        }
        return"";
    }

    //根据类型获取颜色(地区)
    public static function getTrColorForArea($row){
        $status_type = $row["status_type"];
        switch ($status_type){
            case 1://系统退回
            case 2://员工退回
                return "text-maroon";
            case 5://已分配
                if($row["allot_type"]==2){ //地区
                    return "text-yellow";
                }else{ //员工
                    return "text-primary";
                }
            case 6://跟进中
                return "text-teal";
            case 8://已拒绝
                return "text-gray";
            case 10://已完成
                return "text-success";
        }
        return"";
    }

    //根据类型获取颜色(员工)
    public static function getTrColorForSales($row){
        $status_type = $row["status_type"];
        switch ($status_type){
            case 5://待跟进
                return "text-yellow";
            case 6://跟进中
                return "text-teal";
            case 8://已拒绝
                return "text-gray";
            case 10://已完成
                return "text-success";
        }
        return"";
    }

    //获取层级对应的字符串(小写)
    public static function getZIndexStr($z_index){
        $z_index = empty($z_index)?0:$z_index;
        $list = array(
            0=>"",
            1=>"ka",
            2=>"area",
            3=>"staff",
        );
        if(isset($list[$z_index])){
            return $list[$z_index];
        }else{
            return $z_index;
        }
    }

    //判断地区菜单是否能分配
    public static function isAssign($row){
        //未分配
        $bool = empty($row["status_type"]);
        //人工退回
        $bool = $bool||($row["status_type"]==2&&in_array($row["z_index"],array(1,2)));
        //系统退回
        $bool = $bool||($row["status_type"]==1&&in_array($row["z_index"],array(1,2)));
        return $bool;
    }
    //判断资料菜单是否能分配
    public static function isAssignForArea($row){
        //未分配给员工
        $bool = $row["status_type"]==5&&$row["allot_type"]==2;
        //人工退回
        $bool = $bool||($row["status_type"]==2&&$row["z_index"]==3);
        //系统退回
        $bool = $bool||($row["status_type"]==1&&$row["z_index"]==3);
        return $bool;
    }

    //根据类型转中文
    public static function getStatusStr($row){
        $status_type = $row["status_type"];
        switch ($status_type){
            case 0://未分配
                return Yii::t("market","state none");
            case 1://系统退回
                if($row["z_index"]==3){
                    return Yii::t("market","state staff back");
                }else{
                    return Yii::t("market","state system back");
                }
            case 2://手动退回
                $str = self::getZIndexStr($row["z_index"]);
                return Yii::t("market","state {$str} back");
            case 3://地区退回
                return Yii::t("market","state area back");
            case 4://员工退回
                return Yii::t("market","state staff back");
            case 5://已分配
                return Yii::t("market","state assigned");
            case 6://跟进中
                return Yii::t("market","state go");
            case 8://已拒绝
                return Yii::t("market","state reject");
            case 10://已完成
                return Yii::t("market","state success");
        }
        return"";
    }

    //根据类型转中文
    public static function getStatusStrForArea($row){
        $status_type = $row["status_type"];
        switch ($status_type){
            case 1://系统退回
                return Yii::t("market","state system back");
            case 2://员工退回
                return Yii::t("market","state staff back");
            case 5://已分配
                if($row["allot_type"]==2){ //地区
                    return Yii::t("market","state none");
                }else{ //员工
                    return Yii::t("market","state assigned");
                }
            case 6://跟进中
                return Yii::t("market","state go");
            case 8://已拒绝
                return Yii::t("market","state reject");
            case 10://已完成
                return Yii::t("market","state success");
        }
        return"";
    }

    //根据类型转中文
    public static function getStatusStrForSales($row){
        $status_type = $row["status_type"];
        switch ($status_type){
            case 5://已分配
                return Yii::t("market","state follow");
            case 6://跟进中
                return Yii::t("market","state go");
            case 8://已拒绝
                return Yii::t("market","state reject");
            case 10://已完成
                return Yii::t("market","state success");
        }
        return"";
    }

    //获取客户资料查询类型
    public static function getSearchStatusList(){
        //0:未分配 1：退回 5：已分配 6：跟进中
        $list = array(
            ""=>Yii::t("market","all"),
            0=>Yii::t("market","state none"),
            1=>Yii::t("market","state back"),
            5=>Yii::t("market","state assigned"),
            6=>Yii::t("market","state go"),
        );
        return $list;
    }

    //获取客户资料查询类型(地区查询)
    public static function getSearchAreaStatusList(){
        //0:未分配 1：退回 5：已分配 6：跟进中
        $list = array(
            ""=>Yii::t("market","all"),
            0=>Yii::t("market","state none"),
            1=>Yii::t("market","state back"),
            5=>Yii::t("market","state assigned"),
            6=>Yii::t("market","state go"),
            8=>Yii::t("market","state reject"),
            10=>Yii::t("market","state success"),
        );
        return $list;
    }

    //获取客户资料查询类型(员工查询)
    public static function getSearchSalesStatusList(){
        //5：未跟进 6：跟进中
        $list = array(
            ""=>Yii::t("market","all"),
            5=>Yii::t("market","state follow"),
            6=>Yii::t("market","state go"),
            8=>Yii::t("market","state reject"),
            10=>Yii::t("market","state success"),
        );
        return $list;
    }

    public static function validateEmployee($model){
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id = b.id")
            ->where("a.user_id=:user_id",array(":user_id"=>$uid))
            ->queryRow();
        if($row){
            $model->employee_id = $row["id"];
            $model->employee_code = $row["code"];
            $model->employee_name = $row["name"];
            return true;
        }else{
            return false;
        }
    }

    public static function getEmployeeNameForId($kam_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_employee b")
            ->where("b.id=:id",array(":id"=>$kam_id))
            ->queryRow();
        if($row){
            return $row["name"]." ({$row["code"]})";
        }else{
            return "";
        }
    }

    public static function getMarketCityList($name=""){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.code,b.name,a.region_code")
            ->from("swoper{$suffix}.swo_city_set a")
            ->leftJoin("security{$suffix}.sec_city b","a.code=b.code")
            ->where("a.show_type=1")->order("a.z_index desc")
            ->queryAll();//
        $arr=array("list"=>array(),"option"=>array());
        if($rows){
            foreach ($rows as $row){
                $arr["list"][$row["code"]] = $row["name"];
                $arr["option"][$row["code"]] = array("data-area"=>$row["region_code"]);
            }
        }
        if(!empty($name)){
            $arr["list"][$name]=$name;
        }
        return $arr;
    }

    public static function getAllCityList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.code,a.name")
            ->from("security{$suffix}.sec_city a")
            ->where("a.code not in (select region from security{$suffix}.sec_city WHERE region is not null and region!='' group by region)")
            ->queryAll();//
        $arr=array();
        if($rows){
            foreach ($rows as $row){
                $arr[$row["code"]] = $row["name"];
            }
        }
        return $arr;
    }

    public static function getKASalesList(){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.code,a.name")
            ->from("hr$suffix.hr_employee a")
            ->leftJoin("hr$suffix.hr_dept b","a.position = b.id")
            ->where("b.name like '%KA%'")
            ->queryAll();//
        $arr=array();
        if($rows){
            foreach ($rows as $row){
                $arr[$row["id"]] = $row["name"]." ({$row['code']})";
            }
        }
        return $arr;
    }

    public static function getKASalesListForCity($city){
        $suffix = Yii::app()->params['envSuffix'];
        $system_id = Yii::app()->params['systemId'];
        $rows = Yii::app()->db->createCommand()
            ->select("a.id,a.code,a.name")
            ->from("hr$suffix.hr_employee a")
            ->leftJoin("hr$suffix.hr_binding b","b.employee_id=a.id")
            ->leftJoin("security$suffix.sec_user_access f","f.username=b.user_id and system_id='{$system_id}'")
            ->where("a.city=:city and f.a_read_write like '%MT03%'",array(":city"=>$city))
            ->queryAll();//
        $arr=array();
        if($rows){
            foreach ($rows as $row){
                $arr[$row["id"]] = $row["name"]." ({$row['code']})";
            }
        }
        return $arr;
    }

    public static function getStatusTypeForStateId($state_id,$status_type){
        $row = Yii::app()->db->createCommand()->select("state_type,state_day")
            ->from("sal_market_state")->where("id=:id",array(":id"=>$state_id))->queryRow();
        if($row){
            switch ($row["state_type"]){
                case 1://跟进中
                    $status_type = in_array($status_type,array(1,2))?$status_type:6;
                    break;
            }
        }
        return $status_type;
    }
}
