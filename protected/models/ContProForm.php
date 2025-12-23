<?php

class ContProForm extends ContHeadForm
{
    public $cont_id;
    public $pro_code;//操作
    public $pro_type;//操作类型 C：续约
    public $pro_num;//同类型操作次数
    public $pro_date;//操作时间
    public $pro_remark;//操作备注
    public $pro_status=0;//操作进行中的状态
    public $pro_change=0;//

    public $contHeadRow;
    public $showStore;

    public $compareModel;
    public $compareArr=array();

    public function rulesEx(){
        $list=array();
        $list[] = array('cont_id,pro_type,pro_date,pro_remark','safe');
        $list[]=array('cont_id','required');
        $list[]=array('cont_id','validateContID');
        $list[]=array('group_bool','computeGroupBool');
        $list[]=array('area_bool','computeAreaBool');
        $list[]=array('id','validateID');
        $list[]=array('areaJson','validateAreaJson');
        $list[]=array('fileJson','validateFileJson');
        $list[]=array('serviceJson','validateServiceJson');
        $list[]=array('cont_status','validateStatus');
        $list[]=array('areaJson','validateAllJson','on'=>array('audit'));
        return $list;
    }

    public function validateAllJson($attribute, $param) {
        if(empty($this->virtual)){
            $this->addError("id", "请至少关联一个门店");
        }
        if($this->pro_type=="C"){
            $this->cont_start_dt = $this->contHeadRow["cont_start_dt"];
            if($this->cont_end_dt<=$this->contHeadRow["cont_end_dt"]){
                $this->addError("id", "合约结束时间必须大于（{$this->contHeadRow["cont_end_dt"]}）");
            }
        }
    }

    public function validateStatus($attribute, $param) {
        $proTypeList = CGetName::getProTypeList();
        if(!key_exists($this->pro_type,$proTypeList)){
            $this->addError("id", "操作类型异常（{$this->pro_type}）");
            return false;
        }
        if(!in_array($this->pro_status,array(0,9))){
            $this->addError($attribute, "该状态无法编辑（".CGetName::getContVirStatusStrByKey($this->pro_status)."）");
        }elseif(!in_array($this->cont_status,array(10,30))){
            $this->addError($attribute, "该合约未完成，无法操作（".CGetName::getContVirStatusStrByKey($this->cont_status)."）");
        }
    }

    public function validateServiceJson($attribute, $param) {
        $serviceJson = json_decode($this->serviceJson,true);
        $this->total_amt=0;
        $this->total_sum=0;
        $this->store_sum=0;
        if(empty($serviceJson)||!is_array($serviceJson)){
            //$this->addError($attribute, "门店及服务项目的数据异常");
        }else{
            $model = new VirtualHeadForm("edit");
            foreach ($serviceJson as $store_id=>$list){//将门店拆分，服务拆分在虚拟合同内
                if(isset($this->clueSSERow[$store_id])){
                    $row = $this->clueSSERow[$store_id];
                }else{
                    continue;
                }
                $virtualTemp = array(
                    "cont_id"=>0,
                    "clue_id"=>$this->clue_id,
                    "clue_service_id"=>$this->clue_service_id,
                    "clue_store_id"=>$store_id,
                    "create_staff"=>$row["sales_id"],
                    "busine_id"=>implode(",",$row["busine_id"]),
                    "busine_id_text"=>implode("、",$row["busine_id_text"]),
                    "detail_json"=>$serviceJson[$store_id]['detail'],
                    "pro_change"=>0,//金额
                    "store_amt"=>0,//门店总金额
                    "service_sum"=>0,//总次数
                    "list"=>array(),//门店内被拆分的服务
                );
                $model->busine_id = $row["busine_id"];
                $model->service = $serviceJson[$store_id]['detail'];
                $storeList=$model->validateServiceAmount("service","");
                $model->validateServices("service","");
                if($model->hasErrors()){
                    $this->addErrors($model->getErrors());
                    return false;
                }
                $this->store_sum++;
                $this->total_amt+=$storeList["total"];
                $this->total_sum+=$model->service_fre_sum;
                $virtualTemp["store_amt"]=$storeList["total"];
                $virtualTemp["service_sum"]=$model->service_fre_sum;
                $virtualTemp["list"]=$storeList["list"];
                $virtualTemp["amt_install"]=$storeList["amt_install"];
                $virtualTemp["remark"]=$storeList["remark"];
                $this->virtual[]=$virtualTemp;//将门店拆分，服务拆分在虚拟合同内
            }
        }
    }

    public function validateID($attribute, $param) {
        $this->login_employee_id=CGetName::getEmployeeIDByMy();
    }

    public function validateContID($attribute, $param) {
        $contHeadModel = new ContHeadForm('view');
        if($contHeadModel->retrieveData($this->cont_id)){
            $this->cont_code = $contHeadModel->cont_code;
            $this->cont_status = $contHeadModel->cont_status;
            $this->clue_service_id = $contHeadModel->clue_service_id;
            $this->contHeadRow = $contHeadModel->getAttributes();
            $clueServiceModel = new ClueServiceForm("view");
            if($clueServiceModel->retrieveData($this->clue_service_id)){
                $this->clue_id = $clueServiceModel->clue_id;
                //$this->sales_id=$clueServiceModel->create_staff;
                $this->busine_id = $clueServiceModel->busine_id;
                $this->busine_id_text = $clueServiceModel->busine_id_text;
                $this->busineList = CGetName::getServiceDefListByIDList($this->busine_id);
                $this->predict_amt = $clueServiceModel->clue_type==2?floatval($clueServiceModel->rpt_amt):floatval($clueServiceModel->predict_amt);
                //$this->total_amt = floatval($clueServiceModel->total_amt);
                $this->clueServiceRow = $clueServiceModel->getAttributes();
                $clueHeadModel = new ClueHeadForm("view");
                if($clueHeadModel->retrieveData($this->clue_id)){
                    $this->clue_type=$clueHeadModel->clue_type;
                    $this->city=$clueHeadModel->city;
                    //$this->yewudalei=$clueHeadModel->yewudalei;
                    $this->clueHeadRow = $clueHeadModel->getAttributes();
                    $this->getUpdateClueServiceRow();
                }else{
                    $this->addError($attribute, "线索不存在，请刷新重试");
                }
            }else{
                $this->addError($attribute, "商机不存在，请刷新重试");
            }
        }else{
            $this->addError($attribute, "合约不存在，请刷新重试");
        }
    }

    public function validateContIDByView($attribute, $param) {
        $contHeadModel = new ContHeadForm('view');
        if($contHeadModel->retrieveData($this->cont_id)){
            $this->cont_code = $contHeadModel->cont_code;
            $this->cont_status = $contHeadModel->cont_status;
            $this->clue_service_id = $contHeadModel->clue_service_id;
            $this->contHeadRow = $contHeadModel->getAttributes();
            $clueServiceModel = new ClueServiceForm("view");
            if($clueServiceModel->retrieveData($this->clue_service_id)){
                $this->clue_id = $clueServiceModel->clue_id;
                //$this->sales_id=$clueServiceModel->create_staff;
                $this->busine_id = $clueServiceModel->busine_id;
                $this->busine_id_text = $clueServiceModel->busine_id_text;
                $this->busineList = CGetName::getServiceDefListByIDList($this->busine_id);
                $this->predict_amt = $clueServiceModel->clue_type==2?floatval($clueServiceModel->rpt_amt):floatval($clueServiceModel->predict_amt);
                //$this->total_amt = floatval($clueServiceModel->total_amt);
                $this->clueServiceRow = $clueServiceModel->getAttributes();
                $clueModel = new ClueForm("view");
                if($clueModel->retrieveData($this->clue_id)){
                    $this->clue_type=$clueModel->clue_type;
                    $this->city=$clueModel->city;
                    //$this->yewudalei=$clueModel->yewudalei;
                    $this->clueHeadRow = $clueModel->getAttributes();
                    $this->getUpdateClueServiceRow();
                }else{
                    $this->addError($attribute, "线索不存在，请刷新重试");
                }
            }else{
                $this->addError($attribute, "商机不存在，请刷新重试");
            }
        }else{
            $this->addError($attribute, "合约不存在，请刷新重试");
        }
    }

    public function retrieveDataByNew($type='C'){
        $row = Yii::app()->db->createCommand()->select("a.id,a.pro_code,a.pro_type,a.pro_status")
            ->from("sal_contpro a")
            ->where("a.cont_id=:id",array(":id"=>$this->cont_id))->order("id desc")->queryRow();
        if($row){
            $this->retrieveData($row["id"]);
            if(in_array($row["pro_status"],array(10,30))){
                $this->id=null;
                $this->mh_id = null;
                $this->serviceJson = null;
                $this->pro_status=0;
                $this->pro_type=$type;
                $this->showStore=array();
                if($type=="C"){
                    $this->sign_type = 2;
                    $this->pro_date=date("Y/m/d",strtotime("{$this->cont_end_dt} + 1 day"));
                }else{
                    $this->pro_date=date("Y/m/d");
                }
            }elseif($row["pro_type"]!=$type){
                $this->addError('id', "该合约已存在操作内容，无法继续操作");
            }
        }
    }

    protected function getGoingVirByContID(){
        $sseRows=array();
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
            ->where("cont_id=:cont_id and vir_status in (10,30)",array(":cont_id"=>$this->cont_id))->queryAll();
        if($rows){
            foreach ($rows as $row){
                //查询是否有进行中的虚拟合同变更
                $proBool = Yii::app()->db->createCommand()->select("id,pro_status")->from("sal_contpro_virtual")
                    ->where("pro_id!=:pro_id and vir_id=:vir_id and pro_status not in (0,10,30)",array(":pro_id"=>$this->id,":vir_id"=>$row["id"]))->queryRow();
                if(!$proBool){
                    $clue_store_id = "".$row["clue_store_id"];
                    if(!key_exists($clue_store_id,$sseRows)){
                        $sseRows[$clue_store_id]=array(
                            "a_id"=>$row["id"],
                            "clue_store_id"=>$row["clue_store_id"],
                            "sales_id"=>$row["sales_id"],
                            "busine_id"=>array(),
                            "busine_id_text"=>array(),
                            "detail_json"=>array(),
                        );
                    }
                    $sseRows[$clue_store_id]["busine_id"][]=$row["busine_id"];
                    $sseRows[$clue_store_id]["busine_id_text"][]=$row["busine_id_text"];
                    $sseRows[$clue_store_id]["detail_json"][$row["busine_id"]]=json_decode($row["detail_json"],true);
                }
            }
        }
        return $sseRows;
    }

    protected function getGoingVirByClueID(){
        $sseRows=array();
        $rows = Yii::app()->db->createCommand()
            ->select("s.id,s.create_staff")
            ->from("sal_clue_store s")
            ->leftJoin("sal_contract_virtual v","v.cont_id=:cont_id and v.clue_store_id=s.id",array(":cont_id"=>$this->cont_id))
            ->where("s.clue_id=:clue_id and v.id is null",array(":clue_id"=>$this->clue_id))
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $clue_store_id = "".$row["id"];
                if(!key_exists($clue_store_id,$sseRows)){
                    $sseRows[$clue_store_id]=array(
                        "a_id"=>$row["id"],
                        "clue_store_id"=>$row["id"],
                        "sales_id"=>$row["create_staff"],
                        "busine_id"=>$this->busine_id,
                        "busine_id_text"=>explode("、",$this->busine_id_text),
                        "detail_json"=>array(),
                    );
                }
            }
        }
        return $sseRows;
    }

    public function getUpdateClueServiceRow() {
        $this->showStore=array();
        $this->clueSSERow=array();
        if(in_array($this->pro_status,array(0,9))){//允许修改
            if($this->pro_type=="NA"){//增加门店
                $this->clueSSERow = $this->getGoingVirByClueID();
            }else{
                $this->clueSSERow = $this->getGoingVirByContID();
            }
        }
        $this->getUpdateClueServiceRowByEdit();
    }

    protected function getUpdateClueServiceRowByEdit(){
        $rows = Yii::app()->db->createCommand()->select("id,clue_store_id,busine_id,sales_id,busine_id_text,detail_json")->from("sal_contpro_virtual")
            ->where("pro_id=:id",array(":id"=>$this->id))->order("id desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $this->showStore[]=$row["clue_store_id"];
                if(!isset($this->clueSSERow[$row["clue_store_id"]])){
                    $this->clueSSERow[$row["clue_store_id"]]=array(
                        "a_id"=>$row["id"],
                        "clue_store_id"=>$row["clue_store_id"],
                        "sales_id"=>$row["sales_id"],
                        "busine_id"=>array(),
                        "busine_id_text"=>array(),
                        "detail_json"=>array(),
                    );
                }
                if(!in_array($row["busine_id"],$this->clueSSERow[$row["clue_store_id"]]["busine_id"])){
                    $this->clueSSERow[$row["clue_store_id"]]["busine_id"][]=$row["busine_id"];
                    $this->clueSSERow[$row["clue_store_id"]]["busine_id_text"][]=$row["busine_id_text"];
                }
                $this->clueSSERow[$row["clue_store_id"]]["detail_json"][$row["busine_id"]]=json_decode($row["detail_json"],true);
            }
        }
    }

    public function getModelIDByFileID($fileID){
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_contpro_file")
            ->where("id=:id",array(":id"=>$fileID))->queryRow();//
        if($row){
            $this->id=$row["cont_id"];
            $this->lookFileRow = $row;
        }else{
            $this->id=0;
        }
    }

    public function getFileJson(){
        $this->fileJson=array();
        $id = empty($this->id)?0:$this->id;
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contpro_file")
            ->where("cont_id=:id and pro_id<".$id,array(":id"=>$this->cont_id))->order("id asc")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $this->fileJson[]=array(
                    "id"=>$row["id"],
                    "contID"=>$this->id,
                    "readyOnly"=>true,
                    "fileID"=>$row["phy_file_name"],
                    "fileVal"=>"",
                    "fileName"=>$row["file_name"],
                    "groupID"=>$row["group_id"],
                    "tableName"=>"pro",
                    "uflag"=>"N",
                );
            }
        }
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contpro_file")
            ->where("pro_id=:id",array(":id"=>$this->id))->order("id asc")->queryAll();//
        if($rows){
            foreach ($rows as $row){
                $this->fileJson[]=array(
                    "id"=>$row["id"],
                    "contID"=>$this->id,
                    //"fileID"=>$row["phy_path_name"]."/".$row["phy_file_name"],
                    "fileID"=>$row["phy_file_name"],
                    "fileVal"=>"",
                    "fileName"=>$row["file_name"],
                    "groupID"=>$row["group_id"],
                    "tableName"=>"pro",
                    "uflag"=>"N",
                );
            }
        }
    }

    public function setCompareModelByContID($index){
        $this->compareModel = new ContForm();
        $this->compareModel->retrieveData($index);
        $this->computeCompareArr();
    }

    public function setCompareModelByAudit(){
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_contpro")
            ->where("id<:id and cont_id=:cont_id",array(":id"=>$this->id,":cont_id"=>$this->cont_id))
            ->order("id desc")->queryRow();
        $this->compareModel = new ContProForm();
        if($row){
            $this->compareModel->retrieveData($row["id"]);
        }
        $this->computeCompareArr();
    }

    protected function computeCompareArr(){
        $updateList = $this->historyUpdateList();
        foreach ($updateList as $item){
            if($this->$item!=$this->compareModel->$item){
                $this->compareArr[]=array(
                    "key"=>$item,
                    "name"=>$this->getAttributeLabel($item),
                    "oldText"=>$this->getNameForValue($item,$this->$item,$this),
                    "newText"=>$this->getNameForValue($item,$this->compareModel->$item,$this->compareModel),
                );
            }
        }
    }

    public function printCompareHtmlByAudit(){
        $html="";
        $html.= "<div class='table-responsive'><table class='table table-hover table-striped table-bordered table-condensed'>";
        $html.= "<thead><tr><th>被修改字段名称</th><th>修改前信息</th><th>修改后信息</th></tr></thead><tbody>";
        foreach ($this->compareArr as $compareItem){
            $html.= "<tr>";
            $html.= "<th>".$compareItem["name"]."</th>";
            $html.= "<td>".$compareItem["newText"]."</td>";
            $html.= "<td>".$compareItem["oldText"]."</td>";
            $html.= "</tr>";
        }
        $html.= "</tbody></table></div>";
        return $html;
    }

    public function printCompareHtml(){
        $html="";
        $html.= "<div class='compare-bottom-div visible-lg'><table class='table table-hover table-bordered table-condensed'>";
        $html.= "<thead><tr class='danger'><th>被修改字段名称</th><th>历史信息</th><th>最新信息</th></tr></thead><tbody>";
        foreach ($this->compareArr as $compareItem){
            $html.= "<tr class='warning'>";
            $html.= "<th>".$compareItem["name"]."</th>";
            $html.= "<th>".$compareItem["oldText"]."</th>";
            $html.= "<th>".$compareItem["newText"]."</th>";
            $html.= "</tr>";
        }
        $html.= "</tbody></table></div>";
        return $html;
    }

    public function retrieveData($index)
    {
        $index = empty($index)||!is_numeric($index)?0:$index;
        $sql = "select a.* from sal_contpro a where a.id=".$index." ".$this->retrieveSqlEx();
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $this->id = $row['id'];
            $this->cont_id = $row['cont_id'];
            $this->pro_type = $row['pro_type'];
            $this->pro_code = $row['pro_code'];
            $this->pro_num = $row['pro_num'];
            $this->pro_date = empty($row['pro_date'])?"":General::toDate($row['pro_date']);
            $this->pro_remark = $row['pro_remark'];
            $this->pro_status = $row['pro_status'];

            $this->clue_id = $row['clue_id'];
            $this->clue_type = $row['clue_type'];
            $this->clue_service_id = $row['clue_service_id'];
            $this->city = $row['city'];
            $this->cont_code = $row['cont_code'];
            $this->sales_id = $row['sales_id'];
            $this->other_sales_id = $row['other_sales_id'];
            $this->other_yewudalei = $row['other_yewudalei'];
            $this->lbs_main = $row['lbs_main'];
            $this->store_sum = $row['store_sum'];
            $this->predict_amt = $row['predict_amt'];
            $this->total_sum = $row['total_sum'];
            $this->total_amt = $row['total_amt'];
            $this->surplus_num = $row['surplus_num'];
            $this->surplus_amt = $row['surplus_amt'];
            $this->cont_status = $row['cont_status'];
            $this->con_v_type = $row['con_v_type'];
            $this->cont_type = $row['cont_type'];
            $this->cont_start_dt = empty($row['cont_start_dt'])?"":General::toDate($row['cont_start_dt']);
            $this->cont_end_dt = empty($row['cont_end_dt'])?"":General::toDate($row['cont_end_dt']);
            $this->sign_date = empty($row['sign_date'])?"":General::toDate($row['sign_date']);
            $this->effect_date = empty($row['effect_date'])?"":General::toDate($row['effect_date']);
            $this->stop_date = empty($row['stop_date'])?"":General::toDate($row['stop_date']);
            $this->cont_month_len = $row['cont_month_len'];
            $this->sign_type = $row['sign_type'];
            $this->is_seal = $row['is_seal'];
            $this->is_renewal = $row['is_renewal'];
            $this->seal_type_id = $row['seal_type_id'];
            $this->prioritize_service = $row['prioritize_service'];
            $this->prioritize_seal = $row['prioritize_seal'];
            $this->group_bool = $row['group_bool'];
            $this->service_timer = $row['service_timer'];
            $this->pay_week = $row['pay_week'];
            $this->pay_type = $row['pay_type'];
            $this->pay_month = $row['pay_month'];
            $this->pay_start = $row['pay_start'];
            $this->deposit_need = $row['deposit_need'];
            $this->deposit_amt = $row['deposit_amt'];
            $this->deposit_rmk = $row['deposit_rmk'];
            $this->fee_type = $row['fee_type'];
            $this->profit_int = $row['profit_int'];
            $this->settle_type = $row['settle_type'];
            $this->bill_day = $row['bill_day'];
            $this->bill_bool = $row['bill_bool'];
            $this->receivable_day = $row['receivable_day'];
            $this->area_bool = $row['area_bool'];
            $this->areaJson = empty($row['area_json'])?array():json_decode($row['area_json'],true);
            $this->remark = $row['remark'];
            $this->mh_remark = $row['mh_remark'];
            $this->yewudalei = $row['yewudalei'];
            $this->mh_id = $row['mh_id'];
            $this->busine_id = empty($row['busine_id'])?array():explode(",",$row['busine_id']);
            $this->busine_id_text = $row['busine_id_text'];

            $this->lcu = $row['lcu'];
            $this->luu = $row['luu'];
            $this->lcd = $row['lcd'];
            $this->lud = $row['lud'];

            return true;
        }else{
            return false;
        }
    }

    //哪些字段修改后需要记录
    protected static function historyUpdateList(){
        //,'total_amt'
        $list = array('lbs_main','yewudalei','sales_id','other_sales_id','other_yewudalei','predict_amt','store_sum','total_sum','total_amt','con_v_type','cont_type','cont_start_dt','cont_end_dt',
            'sign_type','sign_date','is_seal','is_renewal','seal_type_id','prioritize_service','prioritize_seal','group_bool',
            'service_timer','pay_week','pay_month','pay_start','deposit_need','deposit_amt','fee_type',
            'settle_type','bill_day','bill_bool','profit_int','receivable_day','area_bool');
        return $list;
    }

    //保存历史记录
    protected function historySave(&$connection){
        $uid = Yii::app()->user->id;
        $list=array("table_type"=>6,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>2,"history_html"=>array());
        switch ($this->getScenario()){
            case "new":
                $model = new ContHeadForm();
                $model->retrieveData($this->cont_id);
                $keyArr = self::historyUpdateList();
                foreach ($keyArr as $key){
                    if($this->whenEqual($key,$model,$this)){
                        $list["history_html"][]="<span>".$this->getAttributeLabel($key)."：".self::getNameForValue($key,$model->$key,$model)." 修改为 ".self::getNameForValue($key,$this->$key,$this)."</span>";
                    }
                }
                if(!empty($list["history_html"])){
                    $list["table_id"]=0;
                    $list["history_html"] = implode("<br/>",$list["history_html"]);
                    $connection->createCommand()->insert("sal_contract_history", $list);
                }
                break;
            case "edit":
                $model = new ContProForm();
                $model->retrieveData($this->id);
                $keyArr = self::historyUpdateList();
                foreach ($keyArr as $key){
                    if($this->whenEqual($key,$model,$this)){
                        $list["history_html"][]="<span>".$this->getAttributeLabel($key)."：".self::getNameForValue($key,$model->$key,$model)." 修改为 ".self::getNameForValue($key,$this->$key,$this)."</span>";
                    }
                }
                if(!empty($list["history_html"])){
                    $list["history_html"] = implode("<br/>",$list["history_html"]);
                    $connection->createCommand()->insert("sal_contract_history", $list);
                }
                break;
        }
    }

    protected function save(&$connection)
    {
        $uid = Yii::app()->user->id;
        $this->cont_month_len = CGetName::computeMothLenBySE($this->cont_start_dt,$this->cont_end_dt);
        //contract_code
        $saveArr = $this->getMySaveArr();
        $saveExpr= array(
            "pro_type"=>$this->pro_type,
            "pro_num"=>CGetName::getProNumByCont($this->cont_id,$this->pro_type),
            "pro_date"=>empty($this->pro_date)?null:General::toDate($this->pro_date),
            "pro_remark"=>$this->pro_remark,
            "pro_status"=>$this->pro_status,
        );
        $saveArr = array_merge($saveArr,$saveExpr);
        //pro_change
        switch ($this->getScenario()){
            case "new":
                $saveArr["cont_id"] = $this->cont_id;
                $saveArr["cont_code"] = $this->cont_code;
                $saveArr["lcu"]=$uid;
                $saveArr["mh_id"]=null;
                $saveArr["lcd"]=date("Y-m-d H:i:s");
                $connection->createCommand()->insert("sal_contpro",$saveArr);
                $this->id = Yii::app()->db->getLastInsertID();
                Yii::app()->db->createCommand()->update("sal_contpro",array(
                    "pro_code"=>"CPR".(10000+$this->id)
                ),"id=".$this->id);
                $connection->createCommand()->update("sal_contract_history",array(
                    "table_id"=>$this->id,
                ),"table_id=0 and table_type=6 and lcu=:lcu",array(":lcu"=>$uid));
                $connection->createCommand()->insert("sal_contract_history",array(
                    "table_id"=>$this->id,
                    "table_type"=>6,
                    "history_type"=>1,
                    "history_html"=>"<span>新增".CGetName::getProTypeStrByKey($this->pro_type)."</span>",
                    "lcu"=>$uid,
                ));
                break;
            case "edit":
                $saveArr["luu"]=$uid;
                $connection->createCommand()->update("sal_contpro",$saveArr,"id=:id",array(":id"=>$this->id));
                break;
            case "delete":
                $connection->createCommand()->delete("sal_contpro","id=:id",array(":id"=>$this->id));
                $connection->createCommand()->delete("sal_contpro_sse","pro_id=:pro_id",array(":pro_id"=>$this->id));
                $connection->createCommand()->delete("sal_contpro_virtual","pro_id=:pro_id",array(":pro_id"=>$this->id));
                $connection->createCommand()->delete("sal_contract_history","table_type=6 and table_id=:table_id",array(":table_id"=>$this->id));
        }
        $this->addContractSSE();//增加合约关联的门店
        $this->saveFile();//保存附件
        //$this->resetClueService();//
        return true;
    }

    protected function getFilePath(){
        $path="CRM/pro_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
        $path.="/".$this->id;
        return $path;
    }

    //保存附件
    protected function saveFile($group_id=0){
        $qiNiuFile = new QiNiuFile();
        $qiNiuFile->start();
        $path = $this->getFilePath();
        $uid = Yii::app()->user->id;
        switch ($this->getScenario()){
            case "new":
            case "edit":
                if(!empty($this->fileJson)){
                    foreach ($this->fileJson as $row){
                        if(!isset($row["uflag"])){
                            continue;
                        }
                        $saveList = array(
                            "clue_id"=>$this->clue_id,
                            "clue_service_id"=>$this->clue_service_id,
                            "pro_id"=>$this->id,
                            "cont_id"=>$this->cont_id,
                            "file_name"=>$row["fileName"],
                        );
                        if(isset($row["file"])){
                            $file_name = hash_file('md5',$row["file"]["fileTmpName"]);
                            $file_name = $file_name.".".$row["file"]["fileExt"];
                            $saveList["phy_file_name"] = $file_name;//文件名称（系统名）
                            $saveList["phy_path_name"] = $path;//文件地址
                            $saveList["display_name"] = $row["file"]["fileName"];//文件名（上传名）
                            $saveList["file_type"] = $row["file"]["fileType"];
                            $saveList["group_id"] = $group_id;
                            $qiNiuFile->uploadFile($path."/".$file_name,$row["file"]["fileTmpName"]);
                            //move_uploaded_file($row["file"]["fileTmpName"],$path."/".$file_name);
                        }
                        switch ($row["uflag"]){
                            case "Y"://修改，新增
                                if(empty($row["id"])){
                                    $saveList["lcu"]=$uid;
                                    Yii::app()->db->createCommand()->insert("sal_contpro_file",$saveList);
                                }else{
                                    $saveList["luu"]=$uid;
                                    Yii::app()->db->createCommand()->update("sal_contpro_file",$saveList,"id=:id and pro_id=:pro_id",array(":id"=>$row["id"],":pro_id"=>$this->id));
                                }
                                break;
                            case "D"://删除
                                Yii::app()->db->createCommand()->delete("sal_contpro_file","id=:id and pro_id=:pro_id",array(":id"=>$row["id"],":pro_id"=>$this->id));
                                break;
                        }
                    }
                }
                break;

            case "delete"://pro_id
                Yii::app()->db->createCommand()->delete("sal_contpro_file","pro_id=:pro_id",array(":pro_id"=>$this->id));
                /*$dirPath = Yii::app()->params['docmanPath']."/../upload/".Yii::app()->params['systemId'];
                $dirPath.="/pro_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
                $dirPath.="/".$this->id;
                $this->deleteDir($dirPath);
                */
                break;
        }
        $qiNiuFile->end();
    }

    //增加合约关联的门店
    protected function addContractSSE(){
        $uid = Yii::app()->user->id;
        $this->pro_change=0;
        switch ($this->getScenario()){
            case "new":
            case "edit":
                foreach ($this->virtual as $row){
                    $sseRow = Yii::app()->db->createCommand()->select("id")->from("sal_contpro_sse")
                        ->where("pro_id=:pro_id and clue_store_id=:clue_store_id",array(
                            ":pro_id"=>$this->id,
                            ":clue_store_id"=>$row["clue_store_id"],
                        ))->queryRow();
                    if(!$sseRow){//新增关联门店
                        Yii::app()->db->createCommand()->insert("sal_contpro_sse",array(
                            "pro_id"=>$this->id,
                            "cont_id"=>$this->cont_id,
                            "clue_id"=>$this->clue_id,
                            "clue_service_id"=>$this->clue_service_id,
                            "clue_store_id"=>$row["clue_store_id"],
                            "create_staff"=>$row["create_staff"],
                            "busine_id"=>$row["busine_id"],
                            "busine_id_text"=>$row["busine_id_text"],
                            "store_amt"=>$row["store_amt"],
                            "detail_json"=>is_array($row["detail_json"])?json_encode($row["detail_json"],JSON_UNESCAPED_UNICODE):$row["detail_json"],
                            "service_sum"=>$row["service_sum"],//服务总次数
                            "lcu"=>$uid,
                        ));
                        $sseId = Yii::app()->db->getLastInsertID();
                    }else{
                        $sseId = $sseRow["id"];
                        Yii::app()->db->createCommand()->update("sal_contpro_sse",array(
                            "create_staff"=>$row["create_staff"],
                            "busine_id"=>$row["busine_id"],
                            "busine_id_text"=>$row["busine_id_text"],
                            "store_amt"=>$row["store_amt"],
                            "detail_json"=>is_array($row["detail_json"])?json_encode($row["detail_json"],JSON_UNESCAPED_UNICODE):$row["detail_json"],
                            "service_sum"=>$row["service_sum"],//服务总次数
                            "luu"=>$uid,
                        ),"id=".$sseId);
                    }
                    $this->inSSEList[]=$sseId;
                    $this->addVirtual($row,$sseId);
                }
                break;
            default:
                return false;
        }
        $inSSEList = implode(",",$this->inSSEList);
        Yii::app()->db->createCommand()->delete("sal_contpro_sse","pro_id=:pro_id and id not in ({$inSSEList})",array(
            ":pro_id"=>$this->id,
        ));//删除多余的关联门店
        $inVirList = implode(",",$this->inVirList);
        Yii::app()->db->createCommand()->delete("sal_contpro_virtual","pro_id=:pro_id and id not in ({$inVirList})",array(
            ":pro_id"=>$this->id
        ));//删除多余的虚拟合约
        Yii::app()->db->createCommand()->update("sal_contpro",array("pro_change"=>$this->pro_change),"id=".$this->id);
    }

    protected function getSaveVirExprData($row,$virtualRow,$busine_id,$newData){
        $virRow = Yii::app()->db->createCommand()->select("*")->from("sal_contract_virtual")
            ->where("cont_id=:cont_id and clue_store_id=:clue_store_id and busine_id=:busine_id",array(
                ":cont_id"=>$this->cont_id,
                ":clue_store_id"=>$row["clue_store_id"],
                ":busine_id"=>$busine_id,
            ))->queryRow();
        if($virRow){
            $vir_id =$virRow["id"];
            $pro_change = CGetName::computeProChangeAmt($this->pro_type,$virRow["year_amt"],$virtualRow["year_amt"]);
            $this->pro_change +=$pro_change;
            $data = $virRow;
            unset($data["id"]);
            unset($data["lcu"]);
            unset($data["lcd"]);
            unset($data["luu"]);
            unset($data["lud"]);
            $dataEx = array(
                "pro_id"=>$this->id,
                "vir_id"=>$vir_id,
                "pro_type"=>$this->pro_type,
                "pro_num"=>CGetName::getProNumByVir($vir_id,$this->pro_type),
                "pro_date"=>$this->pro_date,
                "pro_remark"=>$this->pro_remark,
                "pro_status"=>$this->pro_status,
                "pro_change"=>$pro_change,
            );
            $dataEx = array_merge($dataEx,$newData);
            if($this->pro_type=="C"){
                $dataEx["cont_start_dt"]=$this->pro_date;
            }
            foreach ($dataEx as $key=>$item){
                $data[$key]=$item;
            }
            return $data;
        }else{
            $pro_change = CGetName::computeProChangeAmt($this->pro_type,$virtualRow["year_amt"],$virtualRow["year_amt"]);
            $this->pro_change +=$pro_change;
            $dataEx = array(
                "pro_id"=>$this->id,
                "vir_id"=>0,
                "pro_type"=>$this->pro_type,
                "pro_num"=>1,
                "pro_date"=>$this->pro_date,
                "pro_remark"=>$this->pro_remark,
                "pro_status"=>$this->pro_status,
                "pro_change"=>$pro_change,
            );
            $virCode = $this->computeVirCode($this->cont_id);
            $storeRow = Yii::app()->db->createCommand()->select("*")->from("sal_clue_store")
                ->where("id=:id",array(":id"=>$row["clue_store_id"]))->queryRow();
            $service_type = Yii::app()->db->createCommand()->select("service_type")->from("sal_service_type")
                ->where("id_char=:id_char",array(":id_char"=>$busine_id))->queryRow();
            $virSaveExpr=array(
                "cont_id"=>$this->cont_id,
                "busine_id"=>$busine_id,
                "clue_id"=>$this->clue_id,
                "clue_type"=>$this->clue_type,
                "clue_service_id"=>$this->clue_service_id,
                "clue_store_id"=>$row["clue_store_id"],
                "city"=>$storeRow["city"],
                "office_id"=>$storeRow["office_id"],
                "service_type"=>$service_type?$service_type["service_type"]:0,
                "busine_id_text"=>$virtualRow["name"],
                "vir_code"=>$virCode,
            );
            $dataEx = array_merge($dataEx,$newData,$virSaveExpr);
            return $dataEx;
        }
    }

    protected function addVirtual($row,$sseId){
        $uid = Yii::app()->user->id;
        if(!empty($row["list"])){//
            foreach ($row["list"] as $busine_id=>$virtualRow){
                $updateRow = Yii::app()->db->createCommand()->select("id")->from("sal_contpro_virtual")
                    ->where("pro_id=:pro_id and clue_store_id=:clue_store_id and busine_id=:busine_id",array(
                        ":pro_id"=>$this->id,
                        ":clue_store_id"=>$row["clue_store_id"],
                        ":busine_id"=>$busine_id,
                    ))->queryRow();
                $virSaveArr = $this->getSaveVirData($row,$virtualRow);
                $virSaveExprArr = $this->getSaveVirExprData($row,$virtualRow,$busine_id,$virSaveArr);
                $virSaveArr = $virSaveExprArr;
                $virSaveArr["effect_date"] = $this->pro_date;
                if($updateRow){
                    $virtualId = $updateRow["id"];
                    $virSaveArr["luu"]=$uid;
                    unset($virSaveArr["sse_id"]);
                    Yii::app()->db->createCommand()->update("sal_contpro_virtual",$virSaveArr,"id=".$virtualId);
                }else{
                    $virSaveExpr=array(
                        "cont_id"=>$this->cont_id,
                        "sse_id"=>$sseId,
                        "busine_id"=>$busine_id,
                        "clue_id"=>$this->clue_id,
                        "clue_type"=>$this->clue_type,
                        "clue_service_id"=>$this->clue_service_id,
                        "clue_store_id"=>$row["clue_store_id"],
                        "busine_id_text"=>$virtualRow["name"],
                        "lcu"=>$uid,
                    );
                    $virSaveArr = array_merge($virSaveArr,$virSaveExpr);
                    $virSaveArr["lcd"]=date("Y-m-d H:i:s");
                    Yii::app()->db->createCommand()->insert("sal_contpro_virtual",$virSaveArr);
                    $virtualId = Yii::app()->db->getLastInsertID();
                    Yii::app()->db->createCommand()->update("sal_contpro_virtual",array(
                        "pro_code"=>"VPR".(10000+$virtualId)
                    ),"id=".$virtualId);
                }
                $this->inVirList[]=$virtualId;
                //$this->addVirtualInfo($virtualRow["items"],$virtualId);
            }
        }
    }

    //保存印章
    public function saveSeal($type='save'){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
        $this->saveFile(100);
        if($type!="save"){
            Yii::app()->db->createCommand()->update("sal_contpro",array(
                "pro_status"=>20,
                'luu'=>$uid
            ),"id=:id",array(":id"=>$this->id));
            Yii::app()->db->createCommand()->update("sal_contpro_file",array(
                "group_id"=>1,
                'luu'=>$uid
            ),"pro_id=:id and group_id=100",array(":id"=>$this->id));//保存的印章文件转生效中
            Yii::app()->db->createCommand()->insert("sal_contract_history",array(
                "table_id"=>$this->id,
                "table_type"=>6,
                "history_type"=>2,
                "history_html"=>"<span>已上传印章</span>",
                "lcu"=>$uid,
            ));
            //印章文件发送给派单系统
            $curlNotesByVirFile = new CurlNotesByVirFile();
            $curlNotesByVirFile->sendAllVirByProID($this->id);

            $noticeModel = new CNoticeFlowModel();
            $taskId = $this->getMHTaskID();
            $url="/openApi/runtime/task/v1/complete";
            $data = array(
                "account"=>CGetName::getEmployeeCodeByMy(),
                "actionName"=>"agree",
                "instId"=>$this->mh_id,
                "opinion"=>"CRM系统已上传印章",
                "taskId"=>$taskId,
                //"formKey"=>"LBSxshtsp",
            );
            $outData = $noticeModel->sendMHPostByUrlAndData($url,$data);
            if(!$outData["status"]){//系统自动同意
                $list["bool"] = false;
                $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
            }
        }
        return $list;
    }

    //发送消息至门户网站
    protected function sendDataToMH(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        if($this->pro_status==1){//发送
            if(!empty($this->mh_id)){
                return $this->sendDataToMHByUpdate();
            }else{
                return $this->sendDataToMHByNew();
            }
        }
        return $list;
    }

    protected function sendDataToMHByUpdate(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
        $noticeModel = new CNoticeFlowModel();
        $url = "/openApi/runtime/instance/v1/setVariables?instId=".$this->mh_id;
        $data = $this->getMHData();
        $outData = $noticeModel->sendMHPostByUrlAndData($url,$data);
        if(!$outData["status"]){//发送修改数据
            $list["bool"] = false;
            $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
        }else{
            $taskId = $this->getMHTaskID();
            $url = CGetName::getMHUrlPrx()."/matter/approvalForm?type=request&taskId={$taskId}&instId={$this->mh_id}&isGetApprovalBtn=true";
            $this->goMhWebUrl =CGetName::getMHWebUrlByUrl($url);
            $historyList=array("table_type"=>6,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>30,"history_html"=>"<span>重新发起</span>");
            Yii::app()->db->createCommand()->insert("sal_contract_history",$historyList);
        }
        return $list;
    }

    protected function getMHData(){
        $lbsCityCode = CGetName::getLbsCityCodeByProID($this->id);
        $list = array(
            "lbsMain"=>CGetName::getLbsMainStrByKeyAndStr($this->lbs_main,'mh_code'),//主体公司编码
            "lbsMainCityCode"=>$this->city,//主城市编码
            "lbsCityCode"=>$lbsCityCode,//门店城市编码
            "lbsBizCatCode"=>CGetName::getYewudaleiStrByKey($this->yewudalei,'mh_code'),//业务大类编码
            "saleId"=>CGetName::getEmployeeStrByKey('bs_staff_id',$this->sales_id),//销售人员北森id
            "contractType"=>CGetName::getContTypeStrByKey($this->con_v_type,'mh_code'),//合同类型
            "isSeal"=>$this->is_seal,//是否用印
            "isPrepayment"=>$this->fee_type==1?"Y":"N",//是否预付款(Y:预付款)
            "sealCode"=>$this->is_seal=="Y"?CGetName::getSealCodeStrByKeyAndStr($this->seal_type_id,'mh_code'):"",//印章编码
            "customerName"=>$this->clueHeadRow["cust_name"],
            //"contractChangeAmt"=>floatval($this->pro_change),
            //"totalAmt"=>floatval($this->total_amt),
            //"signType"=>CGetName::getMHSignTypeBySignType($this->sign_type),
            //"isPriorityArranged"=>$this->prioritize_service,
        );
        if($this->pro_type=="A"){//内容调整
            /*
             * 变更类型：changeType
             * 1、服务内容变更  2、主体公司变更
             * 3、暂停  4、恢复  5、终止
             * svcChange、entityChange、suspend、resume、terminate
             */
            if($this->contHeadRow["lbs_main"]!=$this->lbs_main){
                $list["changeType"]="entityChange";
            }else{
                $list["changeType"]="svcChange";
            }
            $list["contractChangeAmt"]=floatval($this->pro_change);
            $list["contractNowAmt"]=floatval($this->total_amt);
            $list["contractOldAmt"]=$list["contractNowAmt"]-$list["contractChangeAmt"];
        }else{
            $list["totalAmt"]=floatval($this->total_amt);
            $list["signType"]=$this->pro_type=="NA"?"addStore":CGetName::getMHSignTypeBySignType($this->sign_type);
            $list["isPriorityArranged"]=$this->prioritize_service;
        }
        return $list;
    }

    protected function sendDataToMHByNew(){
        $list = array("bool"=>true,"msg"=>"");//true:成功
        $uid = Yii::app()->user->id;
        if($this->pro_status==1){//发送
            $noticeModel = new CNoticeFlowModel();
            $dataEx = array(
                "vars"=>$this->getMHData()
            );
            $businesskey="pro_".$this->id;
            /*
            *  投标报价： "flowKey": "LBStbbxsp"
            *  合同审批： "flowKey": "LBSxshtsp"
            *  合同变更审批："flowKey": "LBShtbgsp"
            */
            $mhKey = $this->pro_type=="A"?"LBShtbgsp":"LBSxshtsp";
            $outData = $noticeModel->sendMHAuditByDataEx($businesskey,$mhKey,$dataEx);
            if(!$outData["status"]){
                $list["bool"] = false;
                $list["msg"]=isset($outData["outData"]["message"])?$outData["outData"]["message"]:$outData["message"];
            }else{
                $instId = isset($outData["outData"]["instId"])?$outData["outData"]["instId"]:null;
                $this->mh_id = $instId;
                Yii::app()->db->createCommand()->update("sal_contpro",array(
                    "mh_id"=>$instId,
                ),"id=:id",array(":id"=>$this->id));
                $historyList=array("table_type"=>6,"table_id"=>$this->id,"lcu"=>$uid,"history_type"=>30,"history_html"=>"<span>发起审批</span>");
                $historyList["expr_data"]=$instId;
                Yii::app()->db->createCommand()->insert("sal_contract_history",$historyList);
                $historyList=array("table_type"=>5,"table_id"=>$this->cont_id,"lcu"=>$uid,"history_type"=>30,"history_html"=>"");
                $historyList["history_html"]="<span>合同".CGetName::getProTypeStrByKey($this->pro_type)."</span>";
                $historyList["expr_data"]=$instId;
                Yii::app()->db->createCommand()->insert("sal_contract_history",$historyList);
                $taskID = $this->getMHTaskID();
                $url = CGetName::getMHUrlPrx()."/matter/approvalForm?type=request&taskId={$taskID}&instId={$this->mh_id}&isGetApprovalBtn=true";
                $this->goMhWebUrl =CGetName::getMHWebUrlByUrl($url);
            }
        }
        return $list;
    }

	public function isOccupied($index) {
		$rtn = true;//默认不允许删除
		if($this->retrieveData($index)){
            $rtn = false;//允许删除
        }
		return $rtn;
	}

	public function isReadonly() {
		return $this->getScenario()=='view'||!in_array($this->pro_status,array(0,9));
	}

    public function resetFileToQiNiu(){
        //将旧文件全部发送到七牛空间
        $pathOld=Yii::app()->params['docmanPath'];
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_contpro_file")
            ->where("phy_path_name like '{$pathOld}%'")->order("id asc")->queryAll();//
        echo "start:".count($rows)."<br/>";
        if($rows){
            $qiNiuFile = new QiNiuFile();
            $qiNiuFile->start();
            foreach ($rows as $row){
                $filePath = $row["phy_path_name"]."/".$row["phy_file_name"];
                if (file_exists($filePath)) {
                    $row["phy_path_name"] = str_replace($pathOld,"CRM",$row["phy_path_name"]);
                    $fileBody = file_get_contents($filePath);
                    $key = $row["phy_path_name"]."/".$row["phy_file_name"];
                    $bool = $qiNiuFile->uploadFileBody($key,$fileBody);
                    if($bool){
                        Yii::app()->db->createCommand()->update("sal_contpro_file",array(
                            "phy_path_name"=>$row["phy_path_name"]
                        ),"id=".$row["id"]);
                    }
                }else{
                    var_dump($filePath);
                    echo "<br/>";
                }
            }
            $qiNiuFile->end();
        }
        echo "end!";
    }
}
