<?php
// Common Functions

class CGetName {

    public static function getSessionByStore(){
        $session = Yii::app()->session;
        if(isset($session["clueStoreDetail"])){
            $type = $session["clueStoreDetail"];
        }else{
            $type = 2;
            $session["clueStoreDetail"] = $type;
        }
        return $type;
    }

    public static function getClueStatusList() {
		$list=array(
			0=>"待跟进",
			1=>"跟进中",
			2=>"商机",
			3=>"报价确认",
			4=>"合同确认",
			5=>"已转化",
			10=>"进行中",
			30=>"进行中",
			40=>"已暂停",
			50=>"已终止",
		);
        return $list;
    }

    public static function getClueStatusStrByKey($clue_status) {
		$list = self::getClueStatusList();
		if(isset($list[$clue_status])){
			return $list[$clue_status];
		}else{
			return $clue_status;
		}
    }

    public static function getClientStatusList() {
		$list=array(
			0=>"未生效",
			1=>"服务中",
			2=>"已停止",
			3=>"未知",
            10=>"服务中",
            30=>"服务中",
            40=>"已暂停",
            50=>"已终止",
		);
        return $list;
    }

    public static function getClientStatusStrByKey($clue_status) {
		$list = self::getClientStatusList();
		if(isset($list[$clue_status])){
			return $list[$clue_status];
		}else{
			return $clue_status;
		}
    }

    public static function getClueStoreStatusList() {
		$list=array(
			0=>"未生效",
			1=>"未服务",
			2=>"服务中",
			3=>"已停止",
			4=>"未知",
            10=>"服务中",
            30=>"服务中",
            40=>"已暂停",
            50=>"已终止",
		);
        return $list;
    }

    public static function getClueStoreStatusByKey($store_status) {
		$list = self::getClueStoreStatusList();
		if(isset($list[$store_status])){
			return $list[$store_status];
		}else{
			return $store_status;
		}
    }

    public static function getClientPersonPwsStrByKey($person_pws) {
        if(empty($person_pws)){
            return Yii::t("clue","pws off");//未设置
        }else{
            return Yii::t("clue","pws on");//已设置
        }
    }

    public static function getClientPersonStatusList() {
        return array(
            1=>"任职中",
            2=>"已离职",
            3=>"辞退",
            4=>"删除",
        );
    }

    public static function getClientPersonStatusStrByKey($status) {
        $list = self::getClientPersonStatusList();
        if(isset($list[$status])){
            return $list[$status];
        }else{
            return $status;
        }
    }

    public static function getPersonSexList() {
        return array(
            "man"=>Yii::t("clue","man"),
            "wuman"=>Yii::t("clue","wuman"),
        );
    }

    public static function getPersonSexStrByKey($sex) {
        $list = self::getPersonSexList();
        if(isset($list[$sex])){
            return $list[$sex];
        }else{
            return $sex;
        }
    }

    public static function getServiceStatusStrByKey($clue_status) {
        //0：未跟进 1:跟进中 2：待报价 3：报价中 4：报价已驳回 5：报价通过 6:待合同审批 7:合同审批中 8：合同已驳回 9：合同通过
		$list = array(
            0=>"待跟进",
            1=>"跟进中",
            2=>"待报价",
            3=>"报价中",
            4=>"已驳回",
            5=>"报价通过",
            6=>"待合同审批",
            7=>"合同审批中",
            8=>"合同已驳回",
            10=>"合同已生效",
            19=>"待上传印章",
            20=>"已上传印章",
            30=>"合同通过",
            40=>"已暂停",
            50=>"已终止",
        );
		if(isset($list[$clue_status])){
			return $list[$clue_status];
		}else{
			return $clue_status;
		}
    }

    public static function getRptStatusStrByKey($clue_status) {
        $list = array(
            0=>"草稿",
            1=>"待审批",
            9=>"已驳回",
            10=>"报价通过",
        );
		if(isset($list[$clue_status])){
			return $list[$clue_status];
		}else{
			return $clue_status;
		}
    }

    public static function getContTopStatusStrByKey($clue_status) {
        // 合同状态  0：草稿 1：已发送 9:已驳回 10: 合同已生效 20：待印章 30：审批通过
        $list = array(
            0=>"草稿",
            1=>"待审批",
            9=>"已驳回",
            10=>"合同已生效",
            19=>"待上传盖章合同",
            20=>"已上传合同",
            30=>"生效中",
            40=>"已暂停",
            50=>"已终止",
            60=>"已恢复",
        );
		if(isset($list[$clue_status])){
			return $list[$clue_status];
		}else{
			return $clue_status;
		}
    }

    public static function getContVirStatusStrByKey($clue_status) {
        $list = array(
            0=>"草稿",
            1=>"待审批",
            9=>"已驳回",
            10=>"合同已生效",
            19=>"待上传盖章合同",
            20=>"已上传合同",
            30=>"生效中",
            40=>"已暂停",
            50=>"已终止",
            60=>"已恢复",
        );
		if(isset($list[$clue_status])){
			return $list[$clue_status];
		}else{
			return $clue_status;
		}
    }

    public static function getVisitObjList() {
//		$rtn = array(''=>Yii::t('misc','-- None --'));
        $rtn = array();
        $sql = "select id, name from sal_visit_obj";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach($rows as $row) {
                $rtn[$row['id']] = $row['name'];
            }
        }
        return $rtn;
    }

    public static function getVisitObjListNotDEAL() {
//		$rtn = array(''=>Yii::t('misc','-- None --'));
        $rtn = array();
        $sql = "select id, name from sal_visit_obj WHERE rpt_type!='DEAL'";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach($rows as $row) {
                $rtn[$row['id']] = $row['name'];
            }
        }
        return $rtn;
    }

    public static function getVisitTypeStrByKey($key) {
        $row = Yii::app()->db->createCommand()->select("name")->from("sal_visit_type")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if ($row) {
            return $row["name"];
        }
        return $key;
    }

    public static function getBusineStrByText($text) {
        if(empty($text)){
            return "";
        }else{
            $colorList = array("bg-maroon","bg-yellow","bg-green");
            $html="<ul class='list-inline rendered' style='margin: 0px;'>";
            $list = explode("、",$text);
            if(!empty($list)){
                foreach ($list as $key=>$item){
                    $colorNum = $key%3;
                    $colorStr = isset($colorList[$colorNum])?$colorList[$colorNum]:"";
                    $html.="<li class='choice'><span class='badge {$colorStr}'>{$item}</span></li>";
                }
            }
            $html.="</ul>";
            return $html;
        }
    }

    public static function getShowTypeList() {
        return array(
            1=>Yii::t("clue","show only"),
            2=>Yii::t("clue","show all"),
            3=>Yii::t("clue","show customize"),
        );
    }

    public static function getShowTypeByKey($key) {
        $list = self::getShowTypeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getFileTypeList() {
        return array(
            1=>'投标/报价',
            2=>'其他',
        );
    }

    public static function getFileTypeStrByKey($key) {
        $list = self::getFileTypeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getServiceFreeList() {
        return array(
            1=>'每月',
            2=>'自定义',
            3=>'呼叫式',
        );
    }

    public static function getServiceFreeStrByKey($key) {
        $key = empty($key)?$key:intval($key);
        $list = self::getServiceFreeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getBillWeekList() {
        return array(
            1=>'≤60天',
            2=>'＞60天',
        );
    }

    public static function getBillWeekStrByKey($key) {
        $list = self::getBillWeekList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getAuditTypeList() {
        return array(
            1=>'标准虫害',
            2=>'其他害虫',
        );
    }

    public static function getAuditTypeStrByKey($key) {
        $list = self::getAuditTypeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getHasAndNotList() {
        return array(
            1=>'有',
            2=>'无',
        );
    }

    public static function getHasAndNotStrByKey($key) {
        $list = self::getHasAndNotList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getSignOddsList() {
        return array(
            0=>"0%（无意向）",
            20=>"20%",
            50=>"50%",
            80=>"80%",
            100=>"100%",
        );
    }

    public static function getSignOddsStrByKey($key) {
        if($key===""||$key===null){
            return "";
        }else{
            return "{$key}%";
        }
    }

    public static function getLbsMainList($city='') {
        $list=array();
        $city = empty($city)?Yii::app()->user->city():$city;
        $rows = Yii::app()->db->createCommand()->select("id,name")
            ->from("sal_main_lbs")
            ->where("show_type=2 or (show_type=1 AND city='{$city}') or (show_type=3 AND FIND_IN_SET('{$city}',show_city))")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row["name"];
            }
        }
        return $list;
    }

    public static function getLbsMainNameByKey($key) {
        $row = Yii::app()->db->createCommand()->select("name")
            ->from("sal_main_lbs")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        return $row?$row["name"]:$key;
    }

    public static function getLbsCityCodeByClueService($clue_service_id) {
        $cityArr = array();
        $rows = Yii::app()->db->createCommand()->select("b.city")
            ->from("sal_clue_sre_soe a")
            ->leftJoin("sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.clue_service_id=:id",array(":id"=>$clue_service_id))
            ->group("b.city")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $cityArr[]=$row["city"];
            }
        }
        return empty($cityArr)?"":implode(",",$cityArr);
    }

    public static function getLbsCityCodeByContID($cont_id) {
        $cityArr = array();
        $rows = Yii::app()->db->createCommand()->select("b.city")
            ->from("sal_contract_sse a")
            ->leftJoin("sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.cont_id=:id",array(":id"=>$cont_id))
            ->group("b.city")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $cityArr[]=$row["city"];
            }
        }
        return empty($cityArr)?"":implode(",",$cityArr);
    }

    public static function getLbsCityCodeByProID($pro_id) {
        $cityArr = array();
        $rows = Yii::app()->db->createCommand()->select("b.city")
            ->from("sal_contpro_sse a")
            ->leftJoin("sal_clue_store b","a.clue_store_id=b.id")
            ->where("a.pro_id=:id",array(":id"=>$pro_id))
            ->group("b.city")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $cityArr[]=$row["city"];
            }
        }
        return empty($cityArr)?"":implode(",",$cityArr);
    }

    public static function getLbsMainStrByKeyAndStr($key,$str) {
        $row = Yii::app()->db->createCommand()->select($str)
            ->from("sal_main_lbs")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        return $row?$row[$str]:$key;
    }

    public static function getSealCodeStrByKeyAndStr($key,$str) {
        $row = Yii::app()->db->createCommand()->select($str)
            ->from("sal_seal")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        return $row?$row[$str]:$key;
    }

    public static function getRptBoolList() {
        return array(
            0=>Yii::t("clue","no"),
            1=>Yii::t("clue","yes"),
        );
    }

    public static function getRptBoolStrByKey($key) {
        $list = self::getRptBoolList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getDisplayList() {
        return array(
            "1"=>Yii::t("clue","yes"),
            "0"=>Yii::t("clue","no"),
        );
    }

    public static function getDisplayStrByKey($key) {
        $list = self::getDisplayList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getSetMenuList() {
        return array(
            "serviceTypeClass"=>"服务项目分类汇总分类",
            "settleTypeClass"=>"结算方式分类",
            "billDayClass"=>"账单日分类",
            "receivableDayClass"=>"应收期限分类",
            "feeTypeClass"=>"收费方式",
            "profitClass"=>"毛利区间",
            "computeRenewal"=>"生效中的合约自动续约",
            "computeStop"=>"暂停合约自动转终止",
        );
    }

    public static function getSetMenuHintList() {
        return array(
            "serviceTypeClass"=>array("data-hint"=>""),
            "settleTypeClass"=>array("data-hint"=>""),
            "billDayClass"=>array("data-hint"=>""),
            "receivableDayClass"=>array("data-hint"=>""),
            "feeTypeClass"=>array("data-hint"=>""),
            "profitClass"=>array("data-hint"=>""),
            "computeRenewal"=>array("data-hint"=>"项目名称填写天数，例如：填写30,那么合同结束日期 - 当前日期的天数小于或等于30(天)，则系统自动续约"),
            "computeStop"=>array("data-hint"=>"项目名称填写月数，例如：填写3,那么当前日期 - 合同暂停日期的月数大于或等于3(月)，则系统自动终止"),
        );
    }

    public static function getSetMenuStrByKey($key) {
        $list = self::getSetMenuList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getInvoiceTypeList() {
        return array(
            1=>"普票",
            2=>"专票",
            3=>"个人",
        );
    }

    public static function getInvoiceTypeStrByKey($key) {
        $list = self::getInvoiceTypeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getYewudaleiKAList() {
        $list=array();
        $rows = Yii::app()->db->createCommand()->select("id,name")->from("sal_yewudalei")
            ->where("id!=1 and status=1")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row["name"];
            }
        }
        return $list;
    }

    public static function getYewudaleiListByEmployee($employee_id) {
        $text = self::getEmployeeStrByKey("yewudalei_text",$employee_id);
        $list=array();
        if(!empty($text)){
            $svalue = str_replace(",","','",$text);
            $rows = Yii::app()->db->createCommand()->select("id,name")->from("sal_yewudalei")
                ->where("name in ('{$svalue}') and status=1")->queryAll();
            if($rows){
                foreach ($rows as $row){
                    $list[$row["id"]]=$row["name"];
                }
            }
        }
        return $list;
    }

    public static function getOneYewudaleiByEmployee($employee_id) {
        $text = self::getEmployeeStrByKey("yewudalei_text",$employee_id);
        $key="";
        if(!empty($text)){
            $svalue = str_replace(",","','",$text);
            $row = Yii::app()->db->createCommand()->select("id,name")->from("sal_yewudalei")
                ->where("name in ('{$svalue}') and status=1")->queryRow();
            if($row){
                $key = $row["id"];
            }
        }
        return $key;
    }

    public static function getCustVipList() {
        return array(
            "Y"=>Yii::t("clue","yes"),
            "N"=>Yii::t("clue","no"),
        );
    }

    public static function getCustVipStrByKey($key) {
        $list = self::getCustVipList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getServiceTypeList()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $list = array();
        $sql = "select id, description from swoper$suffix.swo_customer_type order by description";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['id']] = $row['description'];
            }
        }
        return $list;
    }

    public static function getServiceTypeStrByKey($key){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("description")->from("swoper$suffix.swo_customer_type")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row["description"];
        }else{
            return $key;
        }
    }

    public static function getServiceTypeStrByList($list){
        if(empty($list)){
            return "";
        }
        $quotedIds = array();
        foreach ($list as $id) {
            $quotedIds[] = "'" . str_replace("'", "\\'", $id) . "'";
        }
        $ids = implode(",", $quotedIds);
        
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("GROUP_CONCAT(description) as name")->from("swoper$suffix.swo_customer_type")
            ->where("id in ({$ids})")->queryRow();
        if($row){
            return $row["name"];
        }else{
            return "";
        }
    }

    public static function getYewudaleiStrByKey($key,$str="name"){
        $row = Yii::app()->db->createCommand()->select($str)->from("sal_yewudalei")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    public static function getEmployeeStrByKey($str,$key){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("hr$suffix.hr_employee")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $str=="code"?"403527":$key;
        }
    }

    public static function getEmployeeStrByUsername($str,$username){
        $employee_id = self::getEmployeeIDByUserName($username);
        return self::getEmployeeStrByKey($str,$employee_id);
    }

    public static function getEmployeeNameByKey($key){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("id,name,code")->from("hr$suffix.hr_employee")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row["name"]." ({$row['code']})";
        }else{
            return $key;
        }
    }

    public static function getEmployeeRowByKey($key){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("*")->from("hr$suffix.hr_employee")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row;
        }else{
            return array();
        }
    }

    public static function getEmployeeCodeByMy(){
        $username = Yii::app()->user->id;
        $employeeID = self::getEmployeeIDByUserName($username);
        return self::getEmployeeStrByKey("code",$employeeID);
    }

    public static function getEmployeeIDByMy(){
        $username = Yii::app()->user->id;
        return self::getEmployeeIDByUserName($username);
    }

    public static function getEmployeeIDByUserName($username){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select employee_id from hr$suffix.hr_binding WHERE user_id='{$username}'";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            return $row["employee_id"];
        }else{
            return 0;
        }
    }

    public static function getUserNameByEmployeeID($employee_id){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select user_id from hr$suffix.hr_binding WHERE employee_id='{$employee_id}'";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            return $row["user_id"];
        }else{
            return 0;
        }
    }

    public static function getUserNameHasAccess($username,$access,$system_id=''){
        $suffix = Yii::app()->params['envSuffix'];
        $system_id = empty($system_id)?Yii::app()->params['systemId']:$system_id;
        $row = Yii::app()->db->createCommand()->select("username")->from("security$suffix.sec_user_access")
            ->where("system_id='{$system_id}' and username='{$username}' and a_read_write like '%{$access}%'")->queryRow();
        if($row){
            return true;
        }else{
            return false;
        }
    }

    public static function getCustClassList($cust_class_group=0){
        $suffix = Yii::app()->params['envSuffix'];
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("id,name")
            ->from("swoper{$suffix}.swo_nature_type")
            ->where("nature_id=:nature_id and z_display=1",array(":nature_id"=>$cust_class_group))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row["name"];
            }
        }
        return $list;
    }

    public static function getCustTypeGroupList(){
        $suffix = Yii::app()->params['envSuffix'];
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("b.id,b.description")
            ->from("swoper{$suffix}.swo_nature_type a")
            ->leftJoin("swoper{$suffix}.swo_nature b","a.nature_id=b.id")
            ->where("a.z_display=1")
            ->group("b.id,b.description")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row["description"];
            }
        }
        return $list;
    }

    public static function getCustClassGroupStrByKey($key){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("id,description")
            ->from("swoper{$suffix}.swo_nature")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row["description"];
        }else{
            return $key;
        }
    }

    public static function getCustClassStrByKey($key,$str="name"){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("swoper{$suffix}.swo_nature_type")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    public static function getVisitDistrictIDByNalID($nal_id,$city) {
        $row = Yii::app()->db->createCommand()->select("id,IF(city='{$city}',1,0) as city_order")->from("sal_cust_district")
            ->where("nal_id=:nal_id",array(":nal_id"=>$nal_id))->order("city_order desc")->queryRow();
        if($row){
            return $row["id"];
        }else{
            $row = Yii::app()->db->createCommand()->select("id,IF(name='其他',1,0) as name_order")->from("sal_cust_district")
                ->where("city=:city",array(":city"=>$city))->order("nal_id asc,name_order desc")->queryRow();
            if($row){
                return $row["id"];
            }else{
                return 0;
            }
        }
    }

    public static function getVisitCustTypeIDByCustClassID($cust_class) {
        $name = CGetName::getCustClassStrByKey($cust_class,'name');
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_cust_type")
            ->where("name=:name",array(":name"=>$name))->queryRow();
        if($row){
            return $row["id"];
        }else{
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_cust_type")
                ->where("name like '%其它%'")->queryRow();
            if($row){
                return $row["id"];
            }else{
                $row = Yii::app()->db->createCommand()->select("id")->from("sal_cust_type")->queryRow();
                if($row){
                    return $row["id"];
                }else{
                    return 0;
                }
            }
        }
    }

    public static function getNationalAreaRowByCityName($cityName) {
        $result = preg_replace("/[a-zA-Z]/", "", $cityName);
        $areaRow = Yii::app()->db->createCommand()->select("id,parent_ids,tree_names,area_name")->from("sal_national_area")
            ->where("type=3 and status=1 and tree_names like '%{$result}%'")
            ->order("listsort asc,id asc")->queryRow();
        return $areaRow;
    }

    public static function getNationalListByType($type,$parent_id=-1) {
        $list = array();
        $whereSql = $parent_id==-1?"":" and parent_id={$parent_id}";
        $rows = Yii::app()->db->createCommand()->select("id,parent_ids,tree_names,area_name")->from("sal_national_area")
            ->where("type=:type and status=1 {$whereSql}",array(":type"=>$type))
            ->order("listsort asc,id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row;
            }
        }
        return $list;
    }

    public static function getNationalListBySearch($search,$clue_type,$items=array()) {
        $whereSql="";
        if(!empty($search)){
            $svalue = str_replace("'","\'",$search);
            $whereSql.="tree_names like '%{$svalue}%' and ";
        }
        if(is_array($items)&&!empty($items)){
            $sqlItems=array();
            foreach ($items as $item){
                if(!empty($item)){
                    $item = str_replace("'","\'",$item);
                    $sqlItems[]="tree_names like '%{$item}%'";
                }
            }
            if(!empty($sqlItems)){
                $whereSql.="(".implode(" or ",$sqlItems).") and ";
            }
        }
        $list = array();
        $limit = 20;
        $rows = Yii::app()->db->createCommand()->select("id,parent_ids,tree_names,area_name")->from("sal_national_area")
            ->where("{$whereSql} status=1 and type=3")
            ->order("parent_ids asc,listsort asc,id asc")->limit($limit)->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row;
            }
        }
        return $list;
    }

    public static function getNationalSearchItemByCity($city) {
        $items=array();
        $suffix = Yii::app()->params['envSuffix'];
        if(!empty($city)){
            $cityRow = Yii::app()->db->createCommand()->select("name")->from("security$suffix.sec_city")
                ->where("code=:code",array(":code"=>$city))->queryRow();
            if($cityRow){
                $items[]=preg_replace("/[a-zA-Z]/", "", $cityRow["name"]);
                $rows = Yii::app()->db->createCommand()->select("name")->from("hr{$suffix}.hr_office")
                    ->where("city=:city",array(":city"=>$city))->queryAll();
                if($rows){
                    foreach ($rows as $row){
                        $row["name"]=preg_replace("/[a-zA-Z]/", "", $row["name"]);
                        $items[]=str_replace("办事处","",$row["name"]);
                    }
                }
            }
        }
        return $items;
    }

    public static function getDistrictList($city) {
        $city = empty($city)?Yii::app()->user->city:$city;
        $rtn = array();
        $sql = "select id, name from sal_cust_district where city='{$city}' and display=1 order by z_index desc,name";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach($rows as $row) {
                $rtn[$row['id']] = $row['name'];
            }
        }
        return $rtn;
    }

    public static function getDistrictStrByKey($key,$str="area_name"){
        if(empty($key)){
            return "";
        }
        $row = Yii::app()->db->createCommand()->select($str)->from("sal_national_area")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $str=="*"?$row:$row[$str];
        }else{
            return "";
        }
    }

    public static function getClueSourceList(){
        $rtn = array();
        $rows = Yii::app()->db->createCommand()->select("id,pro_name")->from("sal_ka_sra")
            ->where("z_display=1")->queryAll();
        if (count($rows) > 0) {
            foreach($rows as $row) {
                $rtn[$row['id']] = $row['pro_name'];
            }
        }
        return $rtn;
    }

    public static function getClueSourceStrByKey($key){
        $row = Yii::app()->db->createCommand()->select("id,pro_name")->from("sal_ka_sra")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row["pro_name"];
        }else{
            return $key;
        }
    }

    public static function getClueUAreaRows($clue_id){
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_clue_u_area")
            ->where("clue_id=:id",array(":id"=>$clue_id))->queryAll();
        return $rows;
    }

    public static function getClueUStaffRows($clue_id){
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_clue_u_staff")
            ->where("clue_id=:id",array(":id"=>$clue_id))->queryAll();
        return $rows;
    }

    public static function getCustKAClassStrByKey($key){
        $row = Yii::app()->db->createCommand()->select("id,pro_name")->from("sal_ka_class")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row["pro_name"];
        }else{
            return $key;
        }
    }

    public static function getCustTypeKAStrByKey($key){
        $row = Yii::app()->db->createCommand()->select("id,pro_name")->from("sal_ka_source")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row["pro_name"];
        }else{
            return $key;
        }
    }

    public static function getCustLevelStrByKey($key){
        $row = Yii::app()->db->createCommand()->select("id,pro_name")->from("sal_ka_level")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row["pro_name"];
        }else{
            return $key;
        }
    }

    public static function getContTypeList(){
        $list =array();
        $rows = Yii::app()->db->createCommand()->select("id,name")->from("sal_cont_type")
            ->where("z_display=1")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] =$row["name"];
            }
        }
        return $list;
    }

    public static function getContTypeStrByKey($key,$str="name"){
        $row = Yii::app()->db->createCommand()->select($str)->from("sal_cont_type")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return $key;
        }
    }

    public static function getAssignTypeList() {
        if(Yii::app()->user->validRWFunction('CM01')){
            $list = self::getAssignTypeAllList();
        }else{
            $list = array(
                1=>Yii::t("clue","flow by staff"),
            );
        }
        return $list;
    }

    public static function getAssignTypeAllList() {
        $list = array(
            1=>Yii::t("clue","flow by staff"),
            2=>Yii::t("clue","flow by city"),
            //3=>Yii::t("clue","flow by try"),
        );
        return $list;
    }

    public static function getAssignTypeStrByKey($key) {
        $list = self::getAssignTypeAllList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getVisitTypeList() {
        $rtn = array();
        $sql = "select id, name from sal_visit_type";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach($rows as $row) {
                $rtn[$row['id']] = $row['name'];
            }
        }
        return $rtn;
    }

    public static function getBusineList(){
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("pro_name,id")->from("sal_ka_busine")
            ->where("z_display=1")
            ->order("z_index desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["pro_name"];
            }
        }
        return $list;
    }

    public static function getFunClueTypeList() {
        $list = array();
        if(Yii::app()->user->validFunction('HK01')){
            $list[1]="地推";
        }
        if(Yii::app()->user->validFunction('KA01')){
            $list[2]="KA";
        }
        return $list;
    }

    public static function getAllClueTypeList() {
        $list = array();
        $list[1]="地推";
        $list[2]="KA";
        return $list;
    }

    public static function getAreaStrByArea($area) {
        if($area===""||$area===null){
            return "";
        }else{
            return "{$area}"."平方米";
        }
    }

    public static function getFlowOddsList() {
        $list = array();
        $list[1]="我负责的";
        $list[2]="下属负责的";
        $list[3]="今日待跟进";
        $list[4]="今日已跟进";
        $list[5]="从未跟进的";
        return $list;
    }

    public static function getClueTypeStr($clue_type) {
        $list = self::getAllClueTypeList();
        if(isset($list[$clue_type])){
            return $list[$clue_type];
        }else{
            return $clue_type;
        }
    }

    public static function getCityListWithCityAllow($city_allow='') {
        $list = array();
        $suffix = Yii::app()->params['envSuffix'];
        $clause = !empty($city_allow) ? "code in ($city_allow)" : "1>1";
        $sql = "select code, name from security$suffix.sec_city WHERE {$clause} order by name";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['code']] = $row['name'];
            }
        }
        return $list;
    }

    public static function getAssignCityList() {
        $list = array();
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select code, name from security$suffix.sec_city WHERE 1=1 order by name";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['code']] = $row['name'];
            }
        }
        return $list;
    }

    public static function getStoreCityList() {
        $list = array();
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select code, name from security$suffix.sec_city WHERE ka_bool!=2 order by name";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['code']] = $row['name'];
            }
        }
        return $list;
    }

    public static function getOfficeList($city) {
        $list = array(
            "0"=>"本部",
        );
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("id,name")->from("hr{$suffix}.hr_office")
            ->where("city=:city",array(":city"=>$city))->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['id']] = $row['name'];
            }
        }
        return $list;
    }

    public static function getOfficeStrByKey($key,$str="name") {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("hr{$suffix}.hr_office")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if ($key) {
            return $row[$str];
        }
        return $str=="name"?"本部":"";
    }

    public static function getAssignEmployeeList($employee_id=0) {
        $employee_id=empty($employee_id)||!is_numeric($employee_id)?0:intval($employee_id);
        $list = array();
        if(Yii::app()->user->validRWFunction('CM01')){
            $list = self::getAssignEmployeeAllList($employee_id);
        }else{//唯读权限
            $suffix = Yii::app()->params['envSuffix'];
            $username = Yii::app()->user->id;
            $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
                ->from("hr{$suffix}.hr_binding a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                ->where("a.user_id=:user_id or a.employee_id={$employee_id}",array(":user_id"=>$username))
                ->order("b.city,b.entry_time,b.id")
                ->queryAll();
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $list[$row['id']] = $row['name']." ({$row['code']})";
                }
            }
        }
        return $list;
    }

    public static function getAssignEmployeeAllList($employee_id=0) {
        $employee_id=empty($employee_id)||!is_numeric($employee_id)?0:intval($employee_id);
        $list = array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->leftJoin("security{$suffix}.sec_user_access f","a.user_id=f.username and f.system_id='sal'")
            ->where("b.id={$employee_id} or (b.staff_status!=-1 and f.a_read_write like '%CM02%')")
            ->order("b.city,b.entry_time,b.id")
            ->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['id']] = $row['name']." ({$row['code']})";
            }
        }
        return $list;
    }

    public static function getAssignEmployeeCityList($city,$employee_id=0) {
        $employee_id=empty($employee_id)||!is_numeric($employee_id)?0:intval($employee_id);
        $list = array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->leftJoin("security{$suffix}.sec_user_access f","a.user_id=f.username and f.system_id='sal'")
            ->where("b.id={$employee_id} or (b.city=:city and b.staff_status!=-1 and f.a_read_write like '%CM02%')",array(
                ":city"=>$city
            ))->order("b.city,b.entry_time,b.id")
            ->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['id']] = $row['name']." ({$row['code']})";
            }
        }
        return $list;
    }

    public static function getVEmployeeListByCity($city,$sales_id=0) {
        $sales_id = empty($sales_id)||!is_numeric($sales_id)?0:intval($sales_id);
        $list = array();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr{$suffix}.hr_binding a")
            ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
            ->leftJoin("security{$suffix}.sec_user_access f","a.user_id=f.username and f.system_id='sal'")
            ->where("b.id={$sales_id} or (b.city=:city and b.staff_status!=-1 and f.a_read_write like '%CM02%')",array(":city"=>$city))
            ->order("b.entry_time,b.id")
            ->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['id']] = $row['name']." ({$row['code']})";
            }
        }
        return $list;
    }

    //获取线索管理操作记录
    public static function getClueHistoryRows($table_id,$table_type=1){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.history_html,IFNULL(b.disp_name,a.lcu) as lcu,a.lcd")
            ->from("sal_clue_history a")
            ->leftJoin("security{$suffix}.sec_user b","a.lcu=b.username")
            ->where("a.table_id=:table_id and a.table_type=:table_type",array(
                ":table_id"=>$table_id,
                ":table_type"=>$table_type,
            ))->order("a.lcd desc")->queryAll();
        return $rows;
    }

    //获取主合约追踪
    public static function getClueNameByID($clue_id){
        $row = Yii::app()->db->createCommand()->select("cust_name")->from("sal_clue")
            ->where("id=:id",array(":id"=>$clue_id))->queryRow();
        return $row?$row["cust_name"]:"";
    }

    //获取主合约追踪
    public static function getContProRows($cont_id){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.*,IFNULL(b.disp_name,a.lcu) as display_name")
            ->from("sal_contpro a")
            ->leftJoin("security{$suffix}.sec_user b","a.lcu=b.username")
            ->where("a.cont_id=:cont_id",array(
                ":cont_id"=>$cont_id,
            ))->order("a.lcd asc")->queryAll();
        return $rows;
    }

    //获取虚拟合约追踪
    public static function getContVirProRows($vir_id){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.*,IFNULL(b.disp_name,a.lcu) as display_name")
            ->from("sal_contpro_virtual a")
            ->leftJoin("security{$suffix}.sec_user b","a.lcu=b.username")
            ->where("a.vir_id=:vir_id",array(
                ":vir_id"=>$vir_id,
            ))->order("a.lcd asc")->queryAll();
        return $rows;
    }

    //获取线索管理操作记录
    public static function getContractHistoryRows($table_id,$table_type=5){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.history_html,IFNULL(b.disp_name,a.lcu) as lcu,a.lcd,a.opr_id")
            ->from("sal_contract_history a")
            ->leftJoin("security{$suffix}.sec_user b","a.lcu=b.username")
            ->where("a.table_id=:table_id and a.table_type=:table_type",array(
                ":table_id"=>$table_id,
                ":table_type"=>$table_type,
            ))->order("a.lcd desc,a.id desc")->queryAll();
        return $rows;
    }

    //获取客户管理操作记录
    public static function getClientHistoryRows($table_id,$table_type=1){
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("a.history_html,a.lcu,a.lcd,b.disp_name")
            ->from("sal_client_history a")
            ->leftJoin("security{$suffix}.sec_user b","a.lcu = b.username")
            ->where("a.table_id=:table_id and a.table_type=:table_type",array(
                ":table_id"=>$table_id,
                ":table_type"=>$table_type,
            ))->order("lcd desc")->queryAll();
        return $rows;
    }

    //获取线索内的门店总数
    public static function getClueStoreSumByServiceID($clue_service_id){
        $rows = Yii::app()->db->createCommand()->select("id")
            ->from("sal_clue_sre_soe")
            ->where("clue_service_id=:clue_service_id",array(":clue_service_id"=>$clue_service_id))->queryAll();
        return $rows?count($rows):0;
    }

    //获取客户内的门店总数
    public static function getClientStoreSumByServiceID($client_service_id){
        $rows = Yii::app()->db->createCommand()->select("id")
            ->from("sal_client_sre_soe")
            ->where("client_service_id=:client_service_id",array(":client_service_id"=>$client_service_id))->queryAll();
        return $rows?count($rows):0;
    }

    //获取线索内的门店
    public static function getClueStoreRows($clue_id){
        $rows = Yii::app()->db->createCommand()->select("a.*,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where("a.clue_id=:clue_id and a.z_display=1",array(":clue_id"=>$clue_id))
            ->order("a.lcd asc")->queryAll();
        return $rows;
    }

    //获取线索内的门店（支持搜索和分页）
    public static function getClueStoreRowsWithPagination($clue_id, $search = '', $pageNum = 1, $pageSize = 20){
        $params = array(":clue_id" => $clue_id);
        $whereSql = "a.clue_id=:clue_id and a.z_display=1";

        if(!empty($search)){
            $whereSql .= " and (a.store_code like :search or a.store_name like :search or a.address like :search"
                ." or a.cust_person like :search or a.cust_tel like :search"
                ." or b.invoice_header like :search or b.tax_id like :search or b.invoice_address like :search"
                ." or CAST(a.id AS CHAR) like :search)";
            $params[":search"] = "%{$search}%";
        }

        $totalCount = Yii::app()->db->createCommand()
            ->select("count(*)")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where($whereSql, $params)
            ->queryScalar();
        $totalCount = intval($totalCount);

        if($pageSize <= 0) $pageSize = 20;
        $totalPages = $totalCount > 0 ? intval(ceil($totalCount / $pageSize)) : 1;
        if($totalPages < 1) $totalPages = 1;
        if($pageNum < 1) $pageNum = 1;
        if($pageNum > $totalPages) $pageNum = $totalPages;

        $offset = ($pageNum - 1) * $pageSize;

        $rows = Yii::app()->db->createCommand()
            ->select("a.*,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where($whereSql, $params)
            ->order("a.lcd asc")
            ->limit($pageSize, $offset)
            ->queryAll();

        return array(
            'rows' => $rows ? $rows : array(),
            'totalCount' => $totalCount,
            'pageNum' => intval($pageNum),
            'pageSize' => intval($pageSize),
            'totalPages' => intval($totalPages),
        );
    }

    //获取线索内的门店
    public static function getClueStoreRowsByContID($cont_id){
        $rows = Yii::app()->db->createCommand()
            ->select("a.*,sse.cont_id,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_contract_sse sse")
            ->leftJoin("sal_clue_store a","sse.clue_store_id=a.id")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where("sse.cont_id=:cont_id",array(":cont_id"=>$cont_id))
            ->order("a.lcd asc")->queryAll();
        return $rows;
    }

    //获取线索内的门店
    public static function getContractVirRowsByContIDAndStoreID($cont_id,$store_id){
        $rows = Yii::app()->db->createCommand()->select("id,vir_code,service_fre_type,vir_status")->from("sal_contract_virtual")
            ->where("cont_id=:cont_id and clue_store_id=:store_id",array(":cont_id"=>$cont_id,":store_id"=>$store_id))
            ->order("lcd asc")->queryAll();
        return $rows;
    }

    //获取门店相关的虚拟合约
    public static function getContractVirRowsByStoreID($store_id){
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
            ->where("clue_store_id=:store_id",array(":store_id"=>$store_id))
            ->order("lcd asc")->queryAll();
        return $rows;
    }
    //获取客户相关的虚拟合约
    public static function getContractVirRowsByClueID($clue_id){
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
            ->where("clue_id=:clue_id",array(":clue_id"=>$clue_id))
            ->order("lcd asc")->queryAll();
        return $rows;
    }

    //获取与门店无关的虚拟合约
    public static function getContractSelectByNoStore($clue_id,$store_id){
        $rows = Yii::app()->db->createCommand()->select("cont_id")->from("sal_contract_virtual")
            ->where("clue_id=:clue_id and clue_store_id=:store_id",array(":clue_id"=>$clue_id,":store_id"=>$store_id))
            ->group("cont_id")->queryAll();
        $inContList = array();
        $list = array();
        if($rows){
            foreach ($rows as $row){
                $inContList[]=$row["cont_id"];
            }
        }
        $ids=empty($inContList)?0:implode(",",$inContList);
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contract")
            ->where("clue_id=:clue_id and cont_status>=30 and id not in ({$ids})",array(":clue_id"=>$clue_id))->queryAll();
        if($rows){
            foreach ($rows as $row){//busine_id_text
                $list[$row["id"]]=$row["cont_code"]."({$row["busine_id_text"]})";
            }
        }
        return $list;
    }

    //获取线索内的门店
    public static function getClueStoreRowByStoreID($store_id){
        $row = Yii::app()->db->createCommand()
            ->select("a.*,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where("a.id=:id",array(":id"=>$store_id))
            ->queryRow();
        return $row;
    }

    //批量获取线索内的门店（过滤掉已有报价的门店）
    public static function getClueStoreRowsByStoreIDs($store_ids){
        if (empty($store_ids)) {
            return array();
        }
        $rows = Yii::app()->db->createCommand()
            ->select("a.*,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where(array('in', 'a.id', $store_ids))
            ->andWhere("(a.report_id IS NULL OR a.report_id = '')")
            ->queryAll();
        return $rows;
    }

    //获取商机内关联的所有门店
    public static function getClueSSeRowByClueServiceID($clue_service_id,$updateBool=1,$page=0,$pageSize=10){
        $query = Yii::app()->db->createCommand()
            ->select("a.id as a_id,a.update_bool,a.clue_store_id,a.busine_id,a.busine_id_text,b.*,f.invoice_header,f.tax_id,f.invoice_address,CONCAT('{$updateBool}') as rec_bool")
            ->from("sal_clue_sre_soe a")
            ->leftJoin("sal_clue_store b","a.clue_store_id=b.id")
            ->leftJoin("sal_clue_invoice f","b.invoice_id=f.id")
            ->where("a.clue_service_id=:id",array(":id"=>$clue_service_id))
            ->order("a.id asc");
        
        // 如果page大于0，说明需要分页
        if($page > 0 && $pageSize > 0){
            $offset = ($page - 1) * $pageSize;
            $query->limit($pageSize, $offset);
        }
        
        $rows = $query->queryAll();
        return $rows;
    }
    
    // 获取关联门店总数
    public static function getClueSSeCountByClueServiceID($clue_service_id){
        $count = Yii::app()->db->createCommand()
            ->select("COUNT(a.id) as total")
            ->from("sal_clue_sre_soe a")
            ->where("a.clue_service_id=:id",array(":id"=>$clue_service_id))
            ->queryScalar();
        return $count ? $count : 0;
    }

    //获取合约内关联的所有门店
    public static function getContSSeRowByClueServiceID($clue_service_id,$group_id=0){
        $whereSql = "";
        if(!empty($group_id)){
            $whereSql=" and a.group='{$group_id}'";
        }
        $rows = Yii::app()->db->createCommand()
            ->select("a.id as a_id,a.update_bool,a.clue_store_id,a.busine_id,a.busine_id_text,a.create_staff,b.*,f.invoice_header,f.tax_id,f.invoice_address")
            ->from("sal_contract_sse a")
            ->leftJoin("sal_clue_store b","a.clue_store_id=b.id")
            ->leftJoin("sal_clue_invoice f","b.invoice_id=f.id")
            ->where("a.clue_service_id=:id".$whereSql,array(":id"=>$clue_service_id))
            ->order("a.id asc")->queryAll();//
        return $rows;
    }

    //获取合约内关联的所有门店
    public static function getContSSeRowByContID($cont_id,$group_id=0){
        $whereSql = "";
        if(!empty($group_id)){
            $whereSql=" and a.group='{$group_id}'";
        }
        $rows = Yii::app()->db->createCommand()
            ->select("a.id as a_id,a.update_bool,a.clue_store_id,a.busine_id,a.busine_id_text,a.create_staff,b.*,f.invoice_header,f.tax_id,f.invoice_address")
            ->from("sal_contract_sse a")
            ->leftJoin("sal_clue_store b","a.clue_store_id=b.id")
            ->leftJoin("sal_clue_invoice f","b.invoice_id=f.id")
            ->where("a.cont_id=:id".$whereSql,array(":id"=>$cont_id))
            ->order("a.id asc")->queryAll();//
        return $rows;
    }

    //获取合约内关联的所有门店(历史）
    public static function getContSSeRowByProID($pro_id){
        $whereSql = "";
        $rows = Yii::app()->db->createCommand()
            ->select("a.id as a_id,a.update_bool,a.clue_store_id,a.busine_id,a.busine_id_text,a.create_staff,b.*,f.invoice_header,f.tax_id,f.invoice_address")
            ->from("sal_contpro_sse a")
            ->leftJoin("sal_clue_store b","a.clue_store_id=b.id")
            ->leftJoin("sal_clue_invoice f","b.invoice_id=f.id")
            ->where("a.pro_id=:id".$whereSql,array(":id"=>$pro_id))
            ->order("a.id asc")->queryAll();//
        return $rows;
    }

    //获取客户内的门店
    public static function getClientStoreRows($client_id){
        $rows = Yii::app()->db->createCommand()->select("a.*,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_client_store a")
            ->leftJoin("sal_client_invoice b","a.invoice_id=b.id")
            ->where("a.client_id=:client_id and a.z_display=1",array(":client_id"=>$client_id))
            ->order("a.lcd asc")->queryAll();
        return $rows;
    }

    //获取客户内的联系人
    public static function getClientPersonRows($clue_id,$clue_store_id=0){
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_clue_person")
            ->where("clue_id=:clue_id and clue_store_id=:clue_store_id",array(
                ":clue_id"=>$clue_id,
                ":clue_store_id"=>$clue_store_id
            ))->order("lcd desc")->queryAll();
        return $rows;
    }

    //获取线索内的税号
    public static function getInvoiceList($clue_id,$invoice_id=0){
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_clue_invoice")
            ->where("id=:id or (clue_id=:clue_id and z_display=1)",array(":id"=>$invoice_id,":clue_id"=>$clue_id))->order("lcd asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["invoice_name"];
            }
        }
        return $list;
    }

    //获取客户内的税号
    public static function getClientInvoiceList($client_id,$invoice_id=0){
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_client_invoice")
            ->where("id=:id or (client_id=:client_id and z_display=1)",array(":id"=>$invoice_id,":client_id"=>$client_id))->order("lcd asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["invoice_name"];
            }
        }
        return $list;
    }

    //获取线索内的税号
    public static function getInvoiceOptionsList($clue_id,$invoice_id=0){
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_clue_invoice")
            ->where("id=:id or (clue_id=:clue_id and z_display=1)",array(":id"=>$invoice_id,":clue_id"=>$clue_id))->order("lcd asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = array(
                    "data-invoice_header"=>$row["invoice_header"],
                    "data-tax_id"=>$row["tax_id"],
                    "data-invoice_address"=>$row["invoice_address"],
                    "data-invoice_type"=>$row["invoice_type"],
                    "data-invoice_number"=>$row["invoice_number"],
                    "data-invoice_user"=>$row["invoice_user"],
                );
            }
        }
        return $list;
    }

    //获取线索内的税号
    public static function getClientInvoiceOptionsList($client_id,$invoice_id=0){
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_client_invoice")
            ->where("id=:id or (client_id=:client_id and z_display=1)",array(":id"=>$invoice_id,":client_id"=>$client_id))->order("lcd asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = array(
                    "data-invoice_header"=>$row["invoice_header"],
                    "data-tax_id"=>$row["tax_id"],
                    "data-invoice_address"=>$row["invoice_address"],
                );
            }
        }
        return $list;
    }

    //获取线索内的税号
    public static function getClueInvoiceRows($clue_id){
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_clue_invoice")
            ->where("clue_id=:clue_id",array(":clue_id"=>$clue_id))->order("lcd asc")->queryAll();
        return $rows;
    }

    //获取客户内的税号
    public static function getClientInvoiceRows($client_id){
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_client_invoice")
            ->where("client_id=:client_id",array(":client_id"=>$client_id))->order("lcd asc")->queryAll();
        return $rows;
    }

    //获取客户内的报价历史
    public static function getClientReportHistoryRows($clue_id){
        $rows = Yii::app()->db->createCommand()
            ->select("b.*,a.clue_code,a.cust_name,a.clue_type,a.city,a.cust_class,a.cust_level,c.busine_id_text")
            ->from("sal_clue_rpt b")
            ->leftJoin("sal_clue_service c","b.clue_service_id=c.id")
            ->leftJoin("sal_clue a","b.clue_id=a.id")
            ->where("b.clue_id=:clue_id",array(":clue_id"=>$clue_id))
            ->order("b.lcd desc")->queryAll();
        return $rows;
    }

    //获取线索内的门店(未绑定的门店)
    public static function getClueStoreNotSSERows($clue_id,$clue_service_id,$search='',$page=0,$pageSize=10){
        $idStr="0";
        $sseRows = Yii::app()->db->createCommand()->select("a.clue_store_id")
            ->from("sal_clue_sre_soe a")
            ->where("clue_service_id=:id",array(":id"=>$clue_service_id))->queryAll();
        if($sseRows){
            foreach ($sseRows as $sseRow){
                $idStr.=",".$sseRow["clue_store_id"];
            }
        }
        
        $whereSql = "a.clue_id=:clue_id and a.id not in ($idStr) and a.z_display=1";
        $params = array(":clue_id"=>$clue_id);
        
        // 添加搜索条件（门店名称/编号/地址/联系人/电话/开票信息）
        if(!empty($search)){
            $whereSql .= " and (a.store_code like :search or a.store_name like :search or a.address like :search or a.cust_person like :search or a.cust_tel like :search"
                ." or b.invoice_header like :search or b.tax_id like :search or b.invoice_address like :search"
                ." or CAST(a.id AS CHAR) like :search)";
            $params[":search"] = "%".$search."%";
        }
        
        $query = Yii::app()->db->createCommand()->select("a.*,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where($whereSql, $params)
            ->order("a.lcd desc");
        
        // 如果page大于0，说明需要分页
        if($page > 0 && $pageSize > 0){
            $offset = ($page - 1) * $pageSize;
            $query->limit($pageSize, $offset);
        }
        
        $rows = $query->queryAll();
        return $rows;
    }
    
    // 获取未绑定门店总数
    public static function getClueStoreNotSSECount($clue_id,$clue_service_id,$search=''){
        $idStr="0";
        $sseRows = Yii::app()->db->createCommand()->select("a.clue_store_id")
            ->from("sal_clue_sre_soe a")
            ->where("clue_service_id=:id",array(":id"=>$clue_service_id))->queryAll();
        if($sseRows){
            foreach ($sseRows as $sseRow){
                $idStr.=",".$sseRow["clue_store_id"];
            }
        }
        
        $whereSql = "a.clue_id=:clue_id and a.id not in ($idStr) and a.z_display=1";
        $params = array(":clue_id"=>$clue_id);
        
        // 添加搜索条件（门店名称/编号/地址/联系人/电话/开票信息）
        if(!empty($search)){
            $whereSql .= " and (a.store_code like :search or a.store_name like :search or a.address like :search or a.cust_person like :search or a.cust_tel like :search"
                ." or b.invoice_header like :search or b.tax_id like :search or b.invoice_address like :search"
                ." or CAST(a.id AS CHAR) like :search)";
            $params[":search"] = "%".$search."%";
        }
        
        $count = Yii::app()->db->createCommand()->select("COUNT(a.id) as total")
            ->from("sal_clue_store a")
            ->leftJoin("sal_clue_invoice b","a.invoice_id=b.id")
            ->where($whereSql, $params)
            ->queryScalar();
        return $count ? $count : 0;
    }

    //获取客户内的门店(未绑定的门店)
    public static function getClientStoreNotSSERows($client_id,$client_service_id){
        $idStr="0";
        $sseRows = Yii::app()->db->createCommand()->select("a.client_store_id")
            ->from("sal_client_sre_soe a")
            ->where("client_service_id=:id",array(":id"=>$client_service_id))->queryAll();
        if($sseRows){
            foreach ($sseRows as $sseRow){
                $idStr.=",".$sseRow["client_store_id"];
            }
        }
        $rows = Yii::app()->db->createCommand()->select("a.*,b.invoice_header,b.tax_id,b.invoice_address")
            ->from("sal_client_store a")
            ->leftJoin("sal_client_invoice b","a.invoice_id=b.id")
            ->where("a.client_id=:client_id and a.id not in ($idStr) and a.z_display=1",array(":client_id"=>$client_id))->order("lcd asc")->queryAll();
        return $rows;
    }

    public static function getSupportUserList($city='',$id=0){
        $suffix = Yii::app()->params['envSuffix'];
        $list=array(""=>"");
        if(!empty($city)){
            $incharge=0;
            $inRow = Yii::app()->db->createCommand()->select("code,incharge")
                ->from("security{$suffix}.sec_city")
                ->where("code=:code",array(":code"=>$city))
                ->queryRow();//查询城市的负责人
            if($inRow){
                $incharge = $inRow["incharge"];
            }
            $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
                ->from("hr{$suffix}.hr_binding a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                ->leftJoin("hr{$suffix}.hr_dept f","b.position=f.id")
                ->where("(b.city in ('{$city}') and f.dept_class='Sales') or a.user_id in ('{$incharge}') or b.id=:id",
                    array(":id"=>$id)
                )->queryAll();//查询城市下的销售人员
            if($rows){
                foreach ($rows as $row){
                    $list[$row["id"]] = $row["name"]." ({$row["code"]})";
                }
            }
        }
        return $list;
    }

    public static function getNumberNull($number){
        return $number===""||$number===null?null:floatval($number);
    }

    public static function computeProChangeAmt($pro_type,$old_amt,$now_amt){
        $old_amt = empty($old_amt)?0:floatval($old_amt);
        $now_amt = empty($now_amt)?0:floatval($now_amt);
        switch ($pro_type){
            case "N":
            case "NA":
            case "C":
                return $now_amt;
            case "A":
                return $now_amt-$old_amt;
            case "S":
            case "T":
                return -1*$old_amt;
            case "R":
                return $old_amt;
        }
    }

    public static function computeMothLenBySE($start_dt,$end_dt){
        if(empty($start_dt)||empty($end_dt)){
            return null;
        }
        $startYear = date_format(date_create($start_dt),"Y");
        $startMonth = date_format(date_create($start_dt),"n");
        $endYear = date_format(date_create($end_dt),"Y");
        $endMonth = date_format(date_create($end_dt),"n");
        if(date_format(date_create($end_dt),"t")==date_format(date_create($end_dt),"d")){
            $endMonth++;
        }
        $length= ($endYear-$startYear)*12;
        $length = $length+($endMonth-$startMonth);
        return $length<0?0:$length;
    }

    public static function getProNumByCont($cont_id,$pro_type){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("count(id) as num")->from("sales{$suffix}.sal_contpro")
            ->where("cont_id=:id and pro_type=:pro_type and pro_status=30",array(":id"=>$cont_id,":pro_type"=>$pro_type))->queryRow();
        $num = $row?$row["num"]:0;
        return $num+1;
    }

    public static function getProNumByVir($vir_id,$pro_type){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("count(id) as num")->from("sales{$suffix}.sal_contpro_virtual")
            ->where("vir_id=:id and pro_type=:pro_type and pro_status=30",array(":id"=>$vir_id,":pro_type"=>$pro_type))->queryRow();
        $num = $row?$row["num"]:0;
        return $num+1;
    }

    public static function getProNumByCall($vir_id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("call_id")->from("sales{$suffix}.sal_contpro_virtual")
            ->where("vir_id=:id and pro_vir_type=3 and pro_type='CALL' and pro_status=30",array(":id"=>$vir_id))
            ->group("call_id")->queryAll();
        $num = $row?count($row):0;
        return $num+1;
    }

    public static function getContSTSetTypeList(){
        return array(1=>"暂停",2=>"终止",3=>"无意向原因");
    }

    public static function getContSTSetStrByKey($key){
        $list = self::getContSTSetTypeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getStopSetIDListByType($pro_type,$stop_set_id=0){
        $suffix = Yii::app()->params['envSuffix'];
        switch ($pro_type){
            case "S":
                $str_type=1;
                break;
            case "T":
                $str_type=2;
                break;
            default:
                $str_type=$pro_type;
        }
        $list =array();
        $rows = Yii::app()->db->createCommand()->select("id,name")->from("sales{$suffix}.sal_cont_str")
            ->where("id =:id or (str_type=:str_type and z_display=1)",array(":str_type"=>$str_type,":id"=>$stop_set_id))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["name"];
            }
        }
        return $list;
    }

    public static function getProTypeList() {
        return array(
            "N"=>Yii::t("clue","New"),
            "A"=>Yii::t("clue","Amend"),
            "C"=>Yii::t("clue","Renew"),
            "S"=>Yii::t("clue","Suspend"),
            "T"=>Yii::t("clue","Terminate"),
            "R"=>Yii::t("clue","Resume"),
            "NA"=>Yii::t("clue","New Store"),
            "CAll"=>Yii::t("clue","call service"),
        );
    }

    public static function getProTypeStrByKey($key) {
        $list = self::getProTypeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getPayWeekList() {
        $suffix = Yii::app()->params['envSuffix'];
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("id,description")
            ->from("swoper{$suffix}.swo_payweek")
            ->where("z_display=1")->order("id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row["description"];
            }
        }
        return $list;
    }

    public static function getPayWeekStrByKey($key,$str="description") {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($str)->from("swoper{$suffix}.swo_payweek")
            ->where("id=:id",array(":id"=>$key))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return "";
        }
    }

    public static function getServiceDefList() {
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_service_type")->where("z_display=1")
            ->order("z_index asc,id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id_char"]]=$row["name"];
            }
        }
        return $list;
    }

    public static function getServiceDefNameByID($id,$str="name") {
        $row = Yii::app()->db->createCommand()->select($str)
            ->from("sal_service_type")->where("id=:id",array(":id"=>$id))
            ->queryRow();
        if($str=="*"){
            return $row?$row:array();
        }else{
            return $row?$row["name"]:"";
        }
    }

    public static function getServiceDefListByIDList($idList) {
        $arr = self::getServiceDefList();
        $list = array();
        foreach ($arr as $key=>$item){
            if(in_array($key,$idList)){
                $list[$key] = $item;
            }
        }
        return $list;
    }

    public static function getServiceDefStrByKey($key) {
        $list = self::getServiceDefList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getContactTypeList() {
        return array(
            1=>Yii::t('clue','normal contract'),//普通合约
            2=>Yii::t('clue','box contract')//框架合约
        );
    }

    public static function getContactTypeStrByKey($key) {
        $list = self::getContactTypeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getSignTypeList() {
        return array(
            1=>Yii::t('clue','sign new'),//新签
            2=>Yii::t('clue','sign renewal'),//续约
            /*
            3=>Yii::t('clue','sign suspend'),//暫停
            4=>Yii::t('clue','sign terminate'),//终止
            5=>Yii::t('clue','sign resume'),//恢复
            */
        );
    }

    public static function getSignTypeSql($sign_type="cont.sign_type") {
        $list = self::getSignTypeList();
        $sql = "(case {$sign_type}";
        foreach ($list as $key=>$item){
            $sql.=" when {$key} then '{$item}'";
        }
        $sql.=" else '' end)";
        return $sql;
    }

    public static function getSignTypeStrByKey($key) {
        $list = self::getSignTypeList();
        if(isset($list[$key])){
            return $list[$key];
        }else{
            return $key;
        }
    }

    public static function getFeeTypeList() {
        return self::getSetMenuTypeList("feeTypeClass");
    }

    public static function getFeeTypeStrByKey($key,$str="set_name") {
        return self::getSetMenuTypeStrByKey($key,"feeTypeClass",$str);
    }

    public static function getSetMenuTypeList($set_type) {
        $list=array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_set_menu")
            ->where("set_type=:set_type",array(":set_type"=>$set_type))->order("z_index asc,id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["set_id"]]=$row["set_name"];
            }
        }
        return $list;
    }

    public static function getSetMenuTypeStrByKey($set_id,$set_type,$str="set_name") {
        $row = Yii::app()->db->createCommand()->select($str)->from("sal_set_menu")
            ->where("set_id=:set_id and set_type=:set_type",array(":set_id"=>$set_id,":set_type"=>$set_type))->queryRow();
        if($row){
            return $row[$str];
        }else{
            return "";
        }
    }

    public static function getSettleTypeList() {
        return self::getSetMenuTypeList("settleTypeClass");
    }

    public static function getSettleTypeStrByKey($key,$str="set_name") {
        return self::getSetMenuTypeStrByKey($key,"settleTypeClass",$str);
    }

    public static function getBillDayList() {
        return self::getSetMenuTypeList("billDayClass");
    }

    public static function getBillDayStrByKey($key,$str="set_name") {
        return self::getSetMenuTypeStrByKey($key,"billDayClass",$str);
    }

    public static function getReceivableDayList() {
        return self::getSetMenuTypeList("receivableDayClass");
    }

    public static function getReceivableDayStrByKey($key,$str="set_name") {
        return self::getSetMenuTypeStrByKey($key,"receivableDayClass",$str);
    }

    public static function getProfitList() {
        return self::getSetMenuTypeList("profitClass");
    }

    public static function getProfitStrByKey($key,$str="set_name") {
        return self::getSetMenuTypeStrByKey($key,"profitClass",$str);
    }

    public static function getInputTypeList() {
        return array(
            'qty'=>'数量',
            'amount'=>'金额',
            'install_amt'=>'安装费',
            'yearAmount'=>'年金额',
            'pct'=>'百分比',
            'checkbox'=>'复选框',
            'select'=>'选择框',
            'text'=>'文本框',
            'rmk'=>'富文本框',
            'remark'=>'备注',
            'device'=>'设备',
            'ware'=>'洁具',
            'pest'=>'标靶虫害',
            'method'=>'处理方式',
        );
    }

    public static function getSelectList() {
        return array("VisitForm::getTypeListForH"=>"VisitForm::getTypeListForH");
    }

    public static function getANTypeList() {
        return array(
            'annual'=>'包含月金额',
            'none'=>'不包含月金额',
        );
    }

    public static function getInfoTotalKeyList(){
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_service_type_info")->where("total_bool=1")
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[]=$row["id_char"];
            }
        }
        return $list;
    }

    public static function serviceDefinition($busine_id='') {
        $list=array();
        $whereSql = "z_display=1";
        if(!empty($busine_id)){
            $whereSql = "FIND_IN_SET(id_char,'{$busine_id}')";
        }
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("sal_service_type")->where($whereSql)
            ->order("z_index asc,id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id_char"]]=array(
                    "name"=>$row["name"],
                    "type"=>$row["type_str"],
                    "items"=>array(),
                );
                $infoRows = Yii::app()->db->createCommand()->select("*")
                    ->from("sal_service_type_info")
                    ->where("z_display=1 and type_id=".$row["id"])
                    ->order("z_index asc,id asc")->queryAll();
                if($infoRows){
                    foreach ($infoRows as $infoRow){
                        $list[$row["id_char"]]["items"][$infoRow["id_char"]]=array(
                            "name"=>$infoRow["name"],
                            "type"=>$infoRow["input_type"],
                            "func"=>empty($infoRow["func"])?null:$infoRow["func"],
                            "eol"=>$infoRow["eol_bool"]==1?true:false,
                        );
                    }
                }
            }
        }
        return $list;
    }

    public static function serviceDefinitionEx() {
        return array(
            'A'=>array(
                'name'=>Yii::t('sales','清洁'),
                'type'=>'annual',
                'items'=>array(
                    'A10'=>array('name'=>Yii::t('sales','安装费'),'type'=>'amount'),
                    'A1'=>array('name'=>Yii::t('sales','马桶/蹲厕'),'type'=>'qty'),
                    'A2'=>array('name'=>Yii::t('sales','尿斗'),'type'=>'qty'),
                    'A3'=>array('name'=>Yii::t('sales','水盆'),'type'=>'qty','eol'=>true),
                    'A4'=>array('name'=>Yii::t('sales','清新机'),'type'=>'qty'),
                    'A5'=>array('name'=>Yii::t('sales','皂液机'),'type'=>'qty'),
                    'A9'=>array('name'=>Yii::t('sales','雾化消毒'),'type'=>'qty','eol'=>true),
                    'A11'=>array('name'=>Yii::t('sales','隔油池'),'type'=>'qty'),
                    'A12'=>array('name'=>Yii::t('sales','油烟机清洗'),'type'=>'qty','eol'=>true),
                    'A6'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
                    'A7'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
                    'A8'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),

                ),
            ),
            'B'=>array(
                'name'=>Yii::t('sales','租赁机器'),
                'type'=>'annual',
                'items'=>array(
                    'B1'=>array('name'=>Yii::t('sales','风扇机'),'type'=>'qty'),
                    'B2'=>array('name'=>Yii::t('sales','TC豪华'),'type'=>'qty'),
                    'B3'=>array('name'=>Yii::t('sales','水性喷机'),'type'=>'qty','eol'=>true),
                    'B4'=>array('name'=>Yii::t('sales','压缩香罐'),'type'=>'qty'),
                    'B8'=>array('name'=>Yii::t('sales','饮水机租赁'),'type'=>'qty'),
                    'B9'=>array('name'=>Yii::t('sales','滤芯'),'type'=>'qty','eol'=>true),
                    'B5'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
                    'B6'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
                    'B7'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
                ),
            ),
            'C'=>array(
                'name'=>Yii::t('sales','灭虫'),
                'type'=>'annual',
                'items'=>array(
                    'C10'=>array('name'=>Yii::t('sales','安装费'),'type'=>'amount'),
                    'C1'=>array('name'=>Yii::t('sales','服务面积'),'type'=>'qty','eol'=>true),
                    'C2'=>array('name'=>Yii::t('sales','老鼠'),'type'=>'checkbox'),
                    'C3'=>array('name'=>Yii::t('sales','蟑螂'),'type'=>'checkbox'),
                    'C4'=>array('name'=>Yii::t('sales','果蝇'),'type'=>'checkbox'),
                    'C5'=>array('name'=>Yii::t('sales','租灭蝇灯'),'type'=>'checkbox'),
                    'C9'=>array('name'=>Yii::t('sales','焗雾'),'type'=>'checkbox'),
                    'C11'=>array('name'=>Yii::t('sales','白蚁'),'type'=>'checkbox','eol'=>true),
                    'C6'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
                    'C7'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
                    'C8'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
                ),
            ),
            'D'=>array(
                'name'=>Yii::t('sales','飘盈香'),
                'type'=>'annual',
                'items'=>array(
                    'D8'=>array('name'=>Yii::t('sales','装机费'),'type'=>'amount','eol'=>true),
                    'D1'=>array('name'=>Yii::t('sales','迷你小机'),'type'=>'qty'),
                    'D2'=>array('name'=>Yii::t('sales','小机'),'type'=>'qty'),
                    'D3'=>array('name'=>Yii::t('sales','中机'),'type'=>'qty'),
                    'D4'=>array('name'=>Yii::t('sales','大机'),'type'=>'qty','eol'=>true),
                    'D5'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
                    'D6'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
                    'D7'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
                ),
            ),
            'H'=>array(
                'name'=>Yii::t('sales','蔚诺空气业务'),
                'type'=>'annual',
                'items'=>array(
                    'H1'=>array('name'=>Yii::t('sales','类别'),'type'=>'select','func'=>'VisitForm::getTypeListForH'),
                    'H4'=>array('name'=>Yii::t('sales','延长维保'),'type'=>'amount','eol'=>true),
                    'H2'=>array('name'=>Yii::t('sales','RA488'),'type'=>'qty'),
                    'H3'=>array('name'=>Yii::t('sales','RA800'),'type'=>'qty','eol'=>true),
                    'H5'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
                    'H6'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
                    'H7'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
                ),
            ),
            'E'=>array(
                'name'=>Yii::t('sales','甲醛'),
                'type'=>'annual',
                'items'=>array(
                    'E1'=>array('name'=>Yii::t('sales','服务面积'),'type'=>'qty','eol'=>true),
                    'E2'=>array('name'=>Yii::t('sales','除甲醛'),'type'=>'qty'),
                    'E3'=>array('name'=>Yii::t('sales','AC30'),'type'=>'qty'),
                    'E4'=>array('name'=>Yii::t('sales','PR10'),'type'=>'qty'),
                    'E5'=>array('name'=>Yii::t('sales','迷你清洁炮'),'type'=>'qty','eol'=>true),
                    'E6'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
                    'E7'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
                    'E8'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
                ),
            ),
            'F'=>array(
                'name'=>Yii::t('sales','纸品'),
                'type'=>'none',
                'items'=>array(
                    'F1'=>array('name'=>Yii::t('sales','擦手纸价'),'type'=>'amount'),
                    'F2'=>array('name'=>Yii::t('sales','大卷厕纸价'),'type'=>'amount','eol'=>true),
                    'F4'=>array('name'=>Yii::t('sales','合同金额'),'type'=>'amount','eol'=>true),
                    'F3'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
                ),
            ),
            'G'=>array(
                'name'=>Yii::t('sales','一次性售卖'),
                'type'=>'none',
                'items'=>array(
                    'G3'=>array('name'=>Yii::t('sales','合同金额'),'type'=>'amount','eol'=>true),
                    'G1'=>array('name'=>Yii::t('sales','种类'),'type'=>'text','eol'=>true),
                    'G2'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
                ),
            ),
        );
    }

    public static function getSealTypeList($seal_id=0) {
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_seal")
            ->where("z_display=1 or id=:id",array(":id"=>$seal_id))->order("id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row["name"];
            }
        }
        return $list;
    }

    public static function getSealTypeStrByID($seal_id) {
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_seal")
            ->where("id=:id",array(":id"=>$seal_id))->queryRow();
        return $row?$row["name"]:$seal_id;
    }

    public static function getPayTypeList($pay_id=0) {
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_pay")
            ->where("z_display=1 or id=:id",array(":id"=>$pay_id))->order("id asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]]=$row["name"];
            }
        }
        return $list;
    }

    public static function getPayTypeStrByID($pay_id) {
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_pay")
            ->where("id=:id",array(":id"=>$pay_id))->queryRow();
        return $row?$row["name"]:$pay_id;
    }

    public static function getGroupNextIDByID($id){
        $id = $id===''||$id===null||!is_numeric($id)?-1:$id;
        $list = array($id);
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_group")
            ->where("prev_id=:prev_id",array(":prev_id"=>$id))->queryAll();
        if($rows){
            foreach ($rows as $row){
                if(!in_array($row["id"],$list)){
                    $temp=self::getGroupNextIDByID($row["id"]);
                    $list=array_merge($list,$temp);
                }
            }
        }
        return $list;
    }

    //根据人员组织架构获取管辖下的所有员工
    public static function getGroupStaffIDByStaffID($staff_id){
        $staff_id = $staff_id===''||$staff_id===null||!is_numeric($staff_id)?-1:$staff_id;
        $list = array();
        $rows = Yii::app()->db->createCommand()->select("id")->from("sal_group")
            ->where("employee_id=:employee_id",array(":employee_id"=>$staff_id))->queryAll();
        if($rows){
            $groupIDList =array();
            foreach ($rows as $row){
                $groupTemp=self::getGroupNextIDByID($row["id"]);
                $groupIDList = array_merge($groupIDList,$groupTemp);
            }
            $groupIDStr = implode(",",$groupIDList);
            $empRows = Yii::app()->db->createCommand()->select("employee_id")->from("sal_group")
                ->where("id in ({$groupIDStr})")->group("employee_id")->queryAll();
            if($empRows){
                foreach ($empRows as $empRow){
                    $list[]=$empRow["employee_id"];
                }
            }
        }else{
            $list[]=$staff_id;
        }
        return $list;
    }

    public static function getMHUrlPrx(){
        $userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:"";
        $mobileAgents = array("Mobile","webOS","iPod","Android","iPhone","iPad","Windows Phone","MicroMessenger");
        foreach ($mobileAgents as $agent) {
            if (strpos($userAgent, $agent) !== false) {
                return "mobile";
            }
        }
        return "front";
    }

    public static function getMHUrlByClueRptMHID($mh_id){
        $url = Yii::app()->params['MHCurlFlowURL'];
        $url = str_replace("eipapi",self::getMHUrlPrx()."/matter/approvalForm",$url);
        return $url."?type=request&instId=".$mh_id;
    }

    public static function getMHWebUrlByUrl($end_url){
        $url = Yii::app()->params['MHCurlFlowURL'];
        $url = str_replace("eipapi",$end_url,$url);
        return $url;
    }

    public static function getServiceTypeRptStrByID($id){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("rpt_cat")->from("swoper$suffix.swo_customer_type")
            ->where("id=:id",array(":id"=>$id))->queryRow();
        if($row&&!empty($row["rpt_cat"])){
            return $row["rpt_cat"];
        }else{
            return "NONE";
        }
    }

    public static function getServiceTypeRptStrByList($ids){
        if(empty($ids)){
            return "NONE";
        }
        $suffix = Yii::app()->params['envSuffix'];
        $idList = array();
        foreach($ids as $id){
            $idList[] = intval($id);
        }
        $idStr = implode(",", $idList);
        $rows = Yii::app()->db->createCommand()->select("rpt_cat")->from("swoper$suffix.swo_customer_type")
            ->where("id in ({$idStr}) and rpt_cat!='' and rpt_cat is not null")->group("rpt_cat")->queryAll();
        if($rows){
            $rpt_cat="";
            foreach ($rows as $row){
                $rpt_cat.=$row["rpt_cat"];
            }
            return $rpt_cat;
        }else{
            return "NONE";
        }
    }

    public static function getServiceInfoListByChar($rows){
        $filedList = array_keys($rows);
        $inSql = implode("','",$filedList);
        $inSql = str_replace("svc_",'',$inSql);
        $infoRows = Yii::app()->db->createCommand()->select("id,id_char")->from("sal_service_type_info")
            ->where("id_char in ('{$inSql}')")->queryAll();
        $list=array();
        if($infoRows){
            foreach ($infoRows as $infoRow){
                $list["svc_{$infoRow['id_char']}"]=$infoRow["id"];
            }
        }
        return $list;
    }

    public static function getVirSqlIDByContID($cont_id){
        $rows = Yii::app()->db->createCommand()->select("id")->from("sal_contract_virtual")
            ->where("cont_id=:cont_id",array(":cont_id"=>$cont_id))->queryAll();
        $list=array(0);
        if($rows){
            foreach ($rows as $row){
                $list[]=$row["id"];
            }
        }
        return implode(",",$list);
    }

    public static function getOldCallTextByVirID($vir_id,$call_id=0){
        $whereSql="";
        if(!empty($call_id)&&is_numeric($call_id)){
            $whereSql.=" and id<{$call_id}";
        }
        $rows = Yii::app()->db->createCommand()->select("call_text,call_month_json")->from("sal_contract_call")
            ->where("FIND_IN_SET({$vir_id},vir_ids) and call_status>0 {$whereSql}")->queryAll();
        $callText=array();
        $monthText=array();
        if($rows){
            foreach ($rows as $row){
                $call_month_json = empty($row["call_month_json"])?array():json_decode($row["call_month_json"],true);
                $callText[]=$row["call_text"];
                $call_month_json = array_keys($call_month_json);
                $monthText=array_merge($monthText,$call_month_json);
            }
        }
        $callText = empty($callText)?"":("已呼叫服务月份：<br/>".implode("<br/>",$callText));
        $monthText = empty($monthText)?"":implode(",",$monthText);
        return array("callText"=>$callText,"monthText"=>$monthText);
    }

    public static function resetVirWeek($virRow){
        $vir_id = $virRow["id"];
        $suffix = Yii::app()->params['envSuffix'];
        Yii::app()->db->createCommand()->delete("sales{$suffix}.sal_contract_vir_week","vir_id={$vir_id}");//全部清空
        if(!empty($virRow['service_fre_json'])){
            $freJson = json_decode($virRow['service_fre_json'],true);
            $monthCycle = 0;
            if(isset($freJson["fre_list"])){
                foreach ($freJson["fre_list"] as $row){
                    $monthList = isset($row['month'])?$row['month']:array();
                    if(empty($monthList)){
                        continue;
                    }
                    $fre_num = isset($row['fre_num'])?intval($row['fre_num']):1;
                    $type_sum = isset($row['type_sum'])?intval($row['type_sum']):3;//次数类型：1每次，2：总共，3：每月，4：每周
                    $fre_amt = isset($row['fre_amt'])?floatval($row['fre_amt']):1;
                    $type_amt = isset($row['type_amt'])?intval($row['type_amt']):3;//金额类型：1每次，2：总共，3：每月，4：每周
                    $totalSum=0;//总次数
                    $totalAmt=0;//总金额
                    switch ($type_sum){//次数类型
                        case 1://每次
                            $totalSum=count($monthList)*$fre_num;
                            break;
                        case 2://总共
                            $totalSum=$fre_num;
                            break;
                        case 3://每月
                            $totalSum=count($monthList)*$fre_num;
                            break;
                        case 4://每周
                            $totalSum=52*$fre_num;
                            break;
                    }
                    switch ($type_amt){//金额类型
                        case 1://每次
                            $totalAmt=$totalSum*$fre_amt;
                            break;
                        case 2://总共
                            $totalAmt=$fre_amt;
                            break;
                        case 3://每月
                            $totalAmt=count($monthList)*$fre_amt;
                            break;
                        case 4://每周
                            $totalAmt=52*$fre_amt;
                            break;
                    }
                    $oneMonthNum = $type_sum==4?$fre_num:$totalSum/count($monthList);//每月循环多少次(每周的次数，派单那边自己计算)
                    $is_del = 0;//是否删除 0：否； 1：是
                    if($oneMonthNum<0){
                        $is_del = 1;
                        $oneMonthNum*=-1;
                    }
                    $oneNumberAmt = empty($totalSum)?0:round($totalAmt/$totalSum,2);//每次多少金额
                    //由于总金额分配到每次时有余数，所以需要两个金额
                    $firstAmt=$oneNumberAmt;//第一次的金额
                    $allAmt=$oneNumberAmt;//平均到其它频次的金额
                    if($type_sum==3&&$type_amt==3){//金额和次数的类型都是每月
                        $amtList = self::freeUnitPrice($fre_amt,$fre_num);
                        $firstAmt=$amtList[0];
                        $allAmt=$amtList[1];
                    }
                    foreach ($monthList as $month){
                        $year_cycle =null;
                        if (strpos($month,'年')!==false){
                            $yearMonthList = explode("年",$month);
                            $year_cycle=$yearMonthList[0];
                            $month=$yearMonthList[1];
                        }
                        $oneMonth=pow(2,$month-1);
                        $monthCycle+=$oneMonth;
                        for ($i=0;$i<$oneMonthNum;$i++){
                            Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_vir_week",array(
                                "vir_id"=>$vir_id,
                                "unit_price"=>$i==0?$firstAmt:$allAmt,
                                "month_cycle"=>$oneMonth,
                                "year_cycle"=>$year_cycle,
                                "is_del"=>$is_del,
                                "lcu"=>$virRow["lcu"],
                                "luu"=>$virRow["lcu"],
                            ));
                        }
                    }
                }
            }
            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_virtual",array(
                "month_cycle"=>$monthCycle
            ),"id=".$vir_id);
        }
    }

    //设置虚拟合约的同步信息
    public static function resetVirStaffAndWeek($vir_id){
        $suffix = Yii::app()->params['envSuffix'];
        $virRow = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract_virtual")
            ->where("id=:id",array(":id"=>$vir_id))->queryRow();
        if($virRow){
            self::resetVirWeek($virRow);
            $sales_id = $virRow["sales_id"];
            $u_yewudalei = self::getYewudaleiStrByKey($virRow["yewudalei"],"u_id");
            $other_sales_id = $virRow["other_sales_id"];
            $virStaff = Yii::app()->db->createCommand()->select("id,employee_id,u_yewudalei")->from("sales{$suffix}.sal_contract_vir_staff")
                ->where("vir_id=:id and type=4",array(":id"=>$vir_id))->queryRow();
            if($virStaff){
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_vir_staff",array(
                    "employee_id"=>$sales_id,
                    "u_yewudalei"=>$u_yewudalei,
                    "is_del"=>0,
                    "luu"=>$virRow["luu"],
                ),"id=".$virStaff["id"]);
            }else{
                Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_vir_staff",array(
                    "vir_id"=>$vir_id,
                    "type"=>4,
                    "employee_id"=>$sales_id,
                    "u_yewudalei"=>$u_yewudalei,
                    "role"=>1,
                    "lcu"=>$virRow["lcu"],
                ));
            }
            $otherStaff = Yii::app()->db->createCommand()->select("id,employee_id,u_yewudalei")->from("sales{$suffix}.sal_contract_vir_staff")
                ->where("vir_id=:id and type=5",array(":id"=>$vir_id))->queryRow();
            if(!empty($other_sales_id)){
                $u_other_yewudalei = self::getYewudaleiStrByKey($virRow["other_yewudalei"],"u_id");
                if($otherStaff){
                    Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_vir_staff",array(
                        "employee_id"=>$other_sales_id,
                        "u_yewudalei"=>$u_other_yewudalei,
                        "is_del"=>0,
                        "luu"=>$virRow["luu"],
                    ),"id=".$otherStaff["id"]);
                }else{
                    Yii::app()->db->createCommand()->insert("sales{$suffix}.sal_contract_vir_staff",array(
                        "vir_id"=>$vir_id,
                        "type"=>5,
                        "employee_id"=>$other_sales_id,
                        "u_yewudalei"=>$u_other_yewudalei,
                        "role"=>0,
                        "lcu"=>$virRow["lcu"],
                    ));
                }
            }elseif ($otherStaff){
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract_vir_staff",array(
                    "is_del"=>1,
                    "luu"=>$virRow["luu"],
                ),"id=".$otherStaff["id"]);
            }
        }
    }

    public static function computeClueCode($pinyin,$full_name){
        $list=array("clue_code"=>"","abbr_code"=>"");
        $abbr = $pinyin->abbr($full_name);
        $abbr = empty($abbr)?$full_name:$abbr;
        $abbr = preg_replace("/[^a-zA-Z]/", "", $abbr);
        if(empty($abbr)){
            $abbr = preg_replace("/[^a-zA-Z]/", "", $full_name);
        }
        if(empty($abbr)){
            $abbr = "NONE";
        }
        $abbr = mb_strlen($abbr)>4?mb_substr($abbr,0,4,'UTF-8'):$abbr;
        $abbr = strtoupper($abbr);
        $row = Yii::app()->db->createCommand()->select("count(*) as sum")
            ->from("sal_clue")->where("abbr_code=:abbr_code",array(":abbr_code"=>$abbr))->queryRow();
        $num = $row?$row["sum"]:0;
        $charNum = floor($num/10)+65;
        $num = floor($num%10);
        $clue_code=$abbr.chr($charNum).$num;
        $abbr_code=$abbr;
        $list["clue_code"]=$clue_code;
        $list["abbr_code"]=$abbr_code;
        return $list;
    }

    public static function getMHSignTypeBySignType($sign_type){
        switch ($sign_type){
            case 1://新签
                return "add";
            default://续约
                return "renew";
        }
    }

    public static function getFilePath($index,$tableName){
        switch ($tableName){
            case "cont":
                $model = new ContHeadForm('edit');
                $model->getModelIDByFileID($index);
                return $model->lookFileRow;
            case "rpt":
                $model = new ClueRptForm('edit');
                $model->getModelIDByFileID($index);
                return $model->lookFileRow;
            case "pro":
                $model = new ContProForm('edit');
                $model->getModelIDByFileID($index);
                return $model->lookFileRow;
            case "clue":
                $model = new ClueForm('edit');
                $model->getModelIDByFileID($index);
                return $model->lookFileRow;
            default:
                return array();
        }
    }
    public static function freeUnitPrice($total, $parts){
        // 检查分配的部分是否为零，避免除以零的错误
        if ($parts == 0) {
            return [0, 0];
        }
        $ava_amt = round($total/$parts,2);
        $ava_sum_amt = $ava_amt*$parts;
        $mul_amt = $ava_sum_amt-$total;
        return [$ava_amt-$mul_amt, $ava_amt];
    }

    //派单系统复制过来的方法
    public static function evenlyDistribute($total, $parts){
        // 检查分配的部分是否为零，避免除以零的错误
        if ($parts == 0) {
            return [0, 0];
        }
        // 保留2位小数
        $base = self::round_bcdiv($total, $parts, 2);
        // 计算第一个值：基础值加上所有余数
        // 使用bcmul计算基础值乘以部分数，然后用bcsub从总数中减去这个乘积，得到余数
        $remainder = self::round_bcsub($total, self::round_bcmul($base, $parts, 2), 2);
        $firstValue = self::round_bcadd($base, $remainder, 2);
        // 第二个值就是基础值
        $secondValue = $base;
        // 返回两个值：第一个值包含所有余数，第二个值是常规的分配值
        return [$firstValue, $secondValue];
    }

    /**
     * 修改bcadd方法，避免直接截取小数位不四舍五入的问题
     * @param Float $left_value 加号左边数
     * @param Float $right_value 加号右边数
     * @param Int $decimal_places 保留小数位，默认0
     * @return Float 返回结果
     */
    public static function round_bcadd($left_value,$right_value,$decimal_places=0){
        return round(bcadd($left_value,$right_value,bcadd($decimal_places,2)),$decimal_places);
    }

    /**
     * 修改bcsub方法，避免直接截取小数位不四舍五入的问题
     * @param Float $left_value 减号左边数
     * @param Float $right_value 减号右边数
     * @param Int $decimal_places 保留小数位，默认0
     * @return Float 返回结果
     */
    public static function round_bcsub($left_value,$right_value,$decimal_places=0){
        return round(bcsub($left_value,$right_value,bcadd($decimal_places,2)),$decimal_places);
    }

    /**
     * 修改bcmul方法，避免直接截取小数位不四舍五入的问题
     * @param Float $left_value 乘号左边数
     * @param Float $right_value 乘号右边数
     * @param Int $decimal_places 保留小数位，默认0
     * @return Float 返回结果
     */
    public static function round_bcmul($left_value,$right_value,$decimal_places=0){
        return round(bcmul($left_value,$right_value,bcadd($decimal_places,2)),$decimal_places);
    }

    /**
     * 修改bcdiv方法，避免直接截取小数位不四舍五入的问题
     * @param Float $left_value 分子
     * @param Float $right_value 分母
     * @param Int $decimal_places 保留小数位，默认0
     * @return Float 返回结果
     */
    public static function round_bcdiv($left_value,$right_value,$decimal_places=0){
        return round(bcdiv($left_value,$right_value,bcadd($decimal_places,2)),$decimal_places);
    }
}

?>
