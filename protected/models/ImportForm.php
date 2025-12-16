<?php

class ImportForm extends CFormModel
{
	public $id;
	public $req_dt;
	public $import_type;
	public $city_allow;
	public $fin_dt;
	public $username;
	public $status;
	public $u_status="C";
	public $file_path;
	public $import_name;
	public $import_file;
	public $message;
	public $error_file;

    protected $headList=array();
    protected $bodyList=array();
	protected $successList=array();
    protected $errorList=array();
    protected $keyList=array();
    protected $eveList = array();

	public function attributeLabels()
	{
		return array(
			'import_type'=>Yii::t('import','Import Type'),
			'import_file'=>Yii::t('import','Import File'),
		);
	}

	public function rules()
	{
		return array(
			array('import_type','required'),
			//array('file_content, file_type, mapping, queue_id','safe'),
// Enable direct submit without field mapping
			array('import_file','file','types'=>'xlsx','maxSize'=>1024*1024*5,'allowEmpty'=>false),
		);
	}

    public function getImportTypeList() {
		$rtn = array();
        $rtn["client"] = "导入派单客户";
        $rtn["clientStore"] = "导入派单门店";
        $rtn["cont"] = "导入派单主合约";
        $rtn["vir"] = "导入派单虚拟合约";
		return $rtn;
	}

    public function getClueImportTypeList() {
		$rtn = array();
        if(Yii::app()->user->validFunction('CM01')){
            $rtn["clueBox"] = Yii::t("clue","import clue box");
        }
        if(Yii::app()->user->validFunction('CM02')){
            $rtn["clue"] = Yii::t("clue","import clue head");
        }
        if(Yii::app()->user->validFunction('CM04')){
            $rtn["clueStore"] = Yii::t("clue","import clue store");
        }
		return $rtn;
	}

    protected function getFilePath(){
        $path = Yii::app()->params['docmanPath'];
        $path.="/import_".(Yii::app()->params['envSuffix']==""?"prod":Yii::app()->params['envSuffix']);
        if (!is_dir($path)) {
            mkdir($path);
        }
        $path.="/".$this->import_type;
        if (!is_dir($path)) {
            mkdir($path);
        }
        return $path;
    }
	
	public function setFilePath($file) {
        $file_path = $this->getFilePath();
        $this->import_name = $file->name;
        $ext = pathinfo($file->name,PATHINFO_EXTENSION);
        $file_name = hash_file('md5',$file->tempName);
        $file_path.="/{$file_name}.{$ext}";
        move_uploaded_file($file->tempName,$file_path);
        $this->file_path = $file_path;
    }
	public function saveImport() {
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
            $uid = Yii::app()->user->id;
            $connection->createCommand()->insert("sal_import_queue",array(
                "import_type"=>$this->import_type,
                "city_allow"=>Yii::app()->user->city_allow(),
                "req_dt"=>date("Y-m-d H:i:s"),
                "import_name"=>$this->import_name,
                "import_file"=>$this->file_path,
                "username"=>$uid,
                "status"=>"P",
                "u_status"=>$this->u_status,
                "message"=>"待处理",
            ));
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}
    protected function initForm(){

    }

    public function importChangeOne() {
        echo "[importChangeOne] 开始执行\n";
        $list = array('code'=>400,'success_num'=>0,'error_num'=>0,'msg'=>"","error_file"=>null);
        $this->status="P";
        
        echo "[importChangeOne] 调用formatData()...\n";
        $this->formatData();
        echo "[importChangeOne] formatData()完成\n";
        
        echo "[importChangeOne] 调用initForm()...\n";
        $this->initForm();
        echo "[importChangeOne] initForm()完成\n";
        
        echo "[importChangeOne] 调用validateHead()...\n";
        $this->validateHead();
        echo "[importChangeOne] validateHead()完成，状态: ".$this->status."\n";
        
        if($this->status=="P"){
            echo "[importChangeOne] 调用saveBodyList()...\n";
            $this->saveBodyList();
            echo "[importChangeOne] saveBodyList()完成\n";
            unset($this->bodyList);
            gc_collect_cycles();
        }else{
            echo "[importChangeOne] 状态不为P，跳过saveBodyList()，错误信息: ".$this->message."\n";
            $list["msg"] = $this->message;
        }
        if(!empty($this->successList)){
            $list["code"]=200;
            $list["success_num"]=count($this->successList);
            $list["msg"].=empty($list["msg"])?"":"<br/>";
            $list["msg"].="成功数量：".$list["success_num"];
        }
        if(!empty($this->errorList)){
            $list["error_num"]=count($this->errorList);
            $list["msg"].=empty($list["msg"])?"":"<br/>";
            $list["msg"].="失败数量：{$list["error_num"]}";
            $excel = new ExcelTool();
            $excel->newFile();
            $excel->setActiveSheet(0);
            $excel->setReportDefaultFormat();
            $excel->writeReportTitle("失败数量：{$list["error_num"]}","请修改下面异常数据后重新导入");
            $row = 5;
            $excel->writeCell(0,$row,"失败原因");
            $excel->setRangeStyle("A{$row}",true,true,"L","T",true,true,"ff0000");
            $excel->setColWidth(0,25);
            $excel->setCellFont(0,$row,array("bold"=>true,"size"=>16));
            foreach ($this->headList as $col=>$item){
                $excel->setColWidth($col+1,20);
                $excel->writeCell($col+1,$row,$item);
                $excel->setCellFont($col+1,$row,array("bold"=>true,"size"=>16));
            }
            $row++;
            foreach ($this->errorList as $errorRow){
                $excel->writeCell(0,$row,$errorRow["message"]);
                $excel->setRangeStyle("A{$row}",true,true,"L","T",true,true,"ff0000");
                foreach ($errorRow["list"] as $col=>$item){
                    $excel->writeCell($col+1,$row,$item);
                }
                $row++;
            }
            $list["error_file"]=$excel->genReport();
            $excel->end();
            // 清理错误列表内存
            unset($this->errorList);
            gc_collect_cycles();
        }
        // 清理其他临时数据
        unset($this->headList);
        unset($this->keyList);
        gc_collect_cycles();
        return $list;
    }

    protected function validateHead(){
        echo "[validateHead] 开始验证excel头\n";
        if(!empty($this->headList)){
            echo "[validateHead] headList不为空，整个列数: ".count($this->headList)."\n";
            foreach ($this->eveList as $row){
                $key = array_search($row["name"],$this->headList);
                if($key!==false){
                    $this->keyList[$row["key"]]=$key;
                    echo "[validateHead] 找到字段 ".$row["name"]." at column ".$key."\n";
                }else{
                    $this->status="E";
                    $this->message="第五行内未找到：".$row["name"];
                    echo "[validateHead] 找不到字段: ".$row["name"]."\n";
                    return false;
                }
            }
        }else{
            $this->status="E";
            $this->message="excel第五行不能为空";
            echo "[validateHead] excel的headList为空\n";
        }
    }

    protected function saveBodyList(){
        if(!empty($this->bodyList)){
            $totalCount = count($this->bodyList);
            echo "开始保存 {$totalCount} 条记录...\n";
            foreach ($this->bodyList as $i=>$row){
                $this->status="P";
                $data=array();
                foreach ($this->eveList as $eveRow){
                    $key = $this->keyList[$eveRow['key']];
                    $data[$eveRow['key']]=$row[$key];
                }
                $this->validateRowData($data);
                if($this->status!="P"){
                    $this->errorList[]=array("list"=>$row,"message"=>$this->message);
                }else{
                    $this->successList[]=$i+5;
                    try {
                        $this->saveOneData($data);
                    } catch (Exception $e) {
                        echo "[错误] 第 ".($i+1)." 行保存失败: ".$e->getMessage()."\n";
                        $this->errorList[]=array("list"=>$row,"message"=>"保存失败: ".$e->getMessage());
                    }
                }
                // 每处理100条记录后清理一次内存和连接缓冲
                if(($i+1)%100==0){
                    echo "[" .date('H:i:s'). "]已处理 " .($i+1). " / " .$totalCount. " 条\n";
                    // 清理数据库连接缓冲区
                    $db = Yii::app()->db;
                    if($db->getActive()){
                        $db->setActive(false);
                        $db->setActive(true);
                    }
                    gc_collect_cycles();
                }
                // 每1条都输出进度
                if($totalCount <= 10 || ($i+1)==1 || ($i+1)==$totalCount){
                    echo "处理 ".($i+1)." 条\n";
                }
            }
            echo "保存 {$totalCount} 条记录完毕\n";
        }
    }

    protected function saveOneData($data){
    }

    protected function validateRowData(&$data){
        foreach ($this->eveList as $item){
            $keyStr = $item["key"];
            $requite = key_exists("requite",$item)?$item["requite"]:false;
            $fun = key_exists("fun",$item)?$item["fun"]:"";
            $default = key_exists("default",$item)?$item["default"]:"";
            if($requite&&(!key_exists($keyStr,$data)||$data[$keyStr]===""||$data[$keyStr]===null)){
                $this->status="E";
                $this->message=$item["name"]."不能为空";
            }
            if(!empty($default)&&($data[$keyStr]===""||$data[$keyStr]===null)){
                $data[$keyStr]=$default;
            }
            if(key_exists($keyStr,$data)){//删除UTF8MB4字符串
                $data[$keyStr] = self::removeUtf8mb4($data[$keyStr]);
            }
            if($this->status=="P"&&!empty($fun)){//函数验证及自动完成
                $this->$fun($data,$keyStr,$item);
            }
            if($this->status!="P"){
                return false;
            }
        }
    }

    protected function formatData() {
        echo "[formatData] 开始读取excel文件: ".$this->file_path."\n";
        $excel = new ExcelTool();
        echo "[formatData] 创建 ExcelTool对象\n";
        $excel->start();
        echo "[formatData] 调用 start()\n";
        $excel->readFile($this->file_path);
        echo "[formatData] 读取文件完成\n";
        $this->headList=array();
        $this->bodyList=array();
        /**读取excel文件中的第一个工作表*/
        $excel->setActiveSheet(0);
        $currentSheet = $excel->getActiveSheet();
        echo "[formatData] 获取最高列...\n";
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        $allColumn = $this->getColumnToNum($allColumn);
        echo "[formatData] 最大列号: ".$allColumn."\n";
        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();
        echo "[formatData] 最大行数: ".$allRow."\n";
        
        echo "[formatData] 读取第5行作为列头...\n";
        for($currentColumn= 0;$currentColumn<= $allColumn; $currentColumn++){
            $val = $currentSheet->getCellByColumnAndRow($currentColumn,5)->getValue();
            $val = trim($val);
            array_push($this->headList,$val);
        }
        echo "[formatData] headList数量: ".count($this->headList)."\n";
        
        echo "[formatData] 读取从第6行开始的数据...\n";
        /**从第6行开始输出，因为excel表中第一行为列名*/
        $batchSize = 50;
        $currentBatch = 0;
        $startTime = time();
        for($currentRow = 6;$currentRow <= $allRow;$currentRow++){
            /**从第A列开始输出*/
            $arr = array();
            for($currentColumn= 0;$currentColumn<= $allColumn; $currentColumn++){
                // 改用getValue()替代getCalculatedValue()以提高速度
                $val = $currentSheet->getCellByColumnAndRow($currentColumn,$currentRow)->getValue();
                if(is_null($val)){
                    $val = "";
                }
                $val = trim($val);
                array_push($arr,$val);
            }
            array_push($this->bodyList,$arr);
            
            // 每读取50行输出一次进度
            $currentBatch++;
            if($currentBatch % $batchSize == 0){
                $elapsed = time() - $startTime;
                echo "[formatData] 已读取 ".$currentBatch." 行数据 (耗时 ".$elapsed." 秒)\n";
                // 清理内存
                gc_collect_cycles();
            }
        }
        $elapsed = time() - $startTime;
        echo "[formatData] 数据读取完成，总共 ".count($this->bodyList)." 条体数据 (耗时 ".$elapsed." 秒)\n";
        $excel->end();
        echo "[formatData] 整个文件读取完成\n";
    }

    private function getColumnToNum($str){
        if(strlen($str)==1){
            return ord($str)-65;
        }elseif(strlen($str)==2){
            $num = ord($str)-65;
            $num = 26*($num+1);
            $newStr = $str[1];
            $num += ord($newStr)-65;
            return $num;
        }
        return 60;
    }

    protected function valDate(&$data,$keyStr,$item){
        $dateStr = key_exists($keyStr,$data)?$data[$keyStr]:"";
        if(empty($dateStr)){
            $data[$keyStr]=null;
        }else{
            if(strtotime($dateStr)===false){
                $this->status="E";
                $this->message=$item['name']."只能是日期格式({$dateStr})";
            }else{
                $data[$keyStr]=date("Y-m-d",strtotime($dateStr));
            }
        }
    }

    protected function valDateTime(&$data,$keyStr,$item){
        $dateStr = key_exists($keyStr,$data)?$data[$keyStr]:"";
        if(empty($dateStr)){
            $data[$keyStr]=null;
        }else{
            if(strtotime($dateStr)===false){
                $this->status="E";
                $this->message=$item['name']."只能是日期格式({$dateStr})";
            }else{
                $data[$keyStr]=date("Y-m-d H:i:s",strtotime($dateStr));
            }
        }
    }

    protected function getEmployeeListByCode($employeeCode){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("id,code,name")->from("hr{$suffix}.hr_employee")
            ->where("code=:code",array(":code"=>$employeeCode))
            ->order("del_num asc,table_type asc,staff_status desc")->queryRow();
        return $row;
    }

    protected function valEmployee(&$data,$keyStr,$item){
        if(key_exists($keyStr,$data)){
            $employeeCode = $data[$keyStr];
            if(!empty($employeeCode)){
                $row = $this->getEmployeeListByCode($employeeCode);
                if($row){
                    $data[$keyStr]=$row["id"];
                }else{
                    $this->status="E";
                    $this->message=$item['name']."员工编号不存在({$employeeCode})";
                }
            }else{
                $data[$keyStr]=null;
            }
        }
    }

    protected function valServiceType(&$data,$keyStr,$item){
        $suffix = Yii::app()->params['envSuffix'];
        $serviceName = isset($data[$keyStr])?$data[$keyStr]:"";
        if(!empty($serviceName)){
            $serviceList =explode(",",$serviceName);
            $data[$keyStr]=array();
            foreach ($serviceList as $serviceStr){
                $row = Yii::app()->db->createCommand()->select("id")->from("swoper{$suffix}.swo_customer_type")
                    ->where("description=:description",array(":description"=>$serviceStr))->queryRow();
                if($row){
                    $data[$keyStr][]=$row["id"];
                }else{
                    $this->status="E";
                    $this->message=$item['name']."不存在({$serviceStr})";
                }
            }
            $data[$keyStr] = json_encode($data[$keyStr]);
        }else{
            $data[$keyStr]=null;
        }
    }

    protected function valClueType(&$data,$keyStr,$item){
        $list = array("地推"=>1,"KA"=>2);
        if(key_exists($keyStr,$data)){
            $clueType = $data[$keyStr];
            if(key_exists($clueType,$list)){
                $data[$keyStr]=$list[$clueType];
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$clueType})";
            }
        }else{
            $data[$keyStr]=1;
        }
    }

    protected function valClueName(&$data,$keyStr,$item){
        if(key_exists($keyStr,$data)){
            $clueName = $data[$keyStr];
            $row = Yii::app()->db->createCommand()->select("clue_code")->from("sal_clue")
                ->where("cust_name=:cust_name",array(
                    ":cust_name"=>$clueName,
                ))->queryRow();
            if($row){
                $this->status="E";
                $this->message=$item['name']."已存在({$row['clue_code']})";
            }else{
                $kaRow = Yii::app()->db->createCommand()->select("id")->from("sal_ka_bot")
                    ->where("customer_name=:customer_name",array(
                        ":customer_name"=>$clueName,
                    ))->queryRow();
                if($kaRow){
                    $data["ka_id"]=$kaRow["id"];
                }
            }
        }
    }

    protected function valStoreName(&$data,$keyStr,$item){
        if(key_exists($keyStr,$data)){
            $storeName = $data[$keyStr];
            $clue_id = isset($data["clue_id"])?$data["clue_id"]:"";
            $row = Yii::app()->db->createCommand()->select("store_code")->from("sal_clue_store")
                ->where("store_name=:store_name",array(
                    ":store_name"=>$storeName
                ))->queryRow();
            if($row){
                $this->status="E";
                $this->message=$item['name']."已存在({$row['store_code']})";
            }elseif (empty($clue_id)){
                $item["name"]="客户名称";
                $this->valClueName($data,$keyStr,$item);
            }
        }
    }

    protected function getCityByName($cityName){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("code")->from("security{$suffix}.sec_city")
            ->where("name=:name",array(":name"=>$cityName))->queryRow();
        return $row;
    }

    protected function valCity(&$data,$keyStr,$item){
        if(key_exists($keyStr,$data)){
            $cityName = $data[$keyStr];
            $row = $this->getCityByName($cityName);
            if($row){
                if (strpos($this->city_allow,"'{$row["code"]}'")!==false){
                    $data[$keyStr]=$row["code"];
                    $data["city_name"]=$cityName;
                }else{
                    $this->status="E";
                    $this->message="你没有城市({$cityName})的权限";
                }
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$cityName})";
            }
        }
    }

    protected function valDistrict(&$data,$keyStr,$item){
        $districtName =key_exists($keyStr,$data)?$data[$keyStr]:"";
        if(!empty($districtName)){
            $city_name = $data["city_name"];
            $districtName = str_replace("'","\'",$districtName);
            $row = Yii::app()->db->createCommand()->select("id,tree_names,
                    (case 
                        WHEN area_name='{$districtName}' THEN 10
                        ELSE 0
                    end) as order_one,
                    (case 
                        WHEN tree_names LIKE '%{$city_name}%' and area_name LIKE '%{$districtName}%' THEN 9
                        WHEN tree_names LIKE '%{$city_name}%' THEN 8
                        ELSE 0
                    end) as order_num")
                ->from("sal_national_area")
                ->where("type=3 and tree_names like '%{$districtName}%'")->order("order_one desc,order_num desc")->queryRow();
            if($row){
                $data[$keyStr]=$row["id"];
                if(empty($data["address"])){
                    $data["address"]=$row["tree_names"];
                }
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$districtName})";
            }
        }else{
            $data[$keyStr]=null;
        }
    }

    protected function valCustClass(&$data,$keyStr,$item){
        $custClass = key_exists($keyStr,$data)?$data[$keyStr]:"";
        if(!empty($custClass)){
            $suffix = Yii::app()->params['envSuffix'];
            $row = Yii::app()->db->createCommand()->select("a.id,a.nature_id")
                ->from("swoper{$suffix}.swo_nature_type a")
                ->where("a.name=:name",array(":name"=>$custClass))->order("z_display desc,id desc")->queryRow();
            if($row){
                $data[$keyStr]=$row["id"];
                $data["cust_class_group"]=$row["nature_id"];
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$custClass})";
            }
        }else{
            $data[$keyStr]=null;
        }
    }

    protected function valOffice(&$data,$keyStr,$item){
        $officeName = key_exists($keyStr,$data)?$data[$keyStr]:"";
        if(!empty($officeName)){
            $city = $data["city"];
            $city_name = $data["city_name"];
            $suffix = Yii::app()->params['envSuffix'];
            $row = Yii::app()->db->createCommand()->select("id")->from("hr{$suffix}.hr_office")
                ->where("name=:name and city=:city",array(":name"=>$officeName,":city"=>$city))->queryRow();
            if($row){
                $data[$keyStr]=$row["id"];
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$city_name}：{$officeName})";
            }
        }else{
            $data[$keyStr]=0;
        }
    }

    protected function valInvoice(&$data,$keyStr,$item){
        $clue_id = key_exists("clue_id",$data)?$data["clue_id"]:"";
        $invoice_header = key_exists("invoice_header",$data)?$data["invoice_header"]:"";
        $invoice_address = key_exists("invoice_address",$data)?$data["invoice_address"]:"";
        $tax_id = key_exists("tax_id",$data)?$data["tax_id"]:"";
        $invoice_number = key_exists("invoice_number",$data)?$data["invoice_number"]:"";
        $invoice_user = key_exists("invoice_user",$data)?$data["invoice_user"]:"";
        $invoice_type=2;
        if(empty($invoice_address)||empty($tax_id)||empty($invoice_number)||empty($invoice_user)){
            $invoice_type=1;
        }
        $data["invoice_type"]=$invoice_type;
        if(!empty($clue_id)&&!empty($invoice_header)){
            if($invoice_type==1){//普票
                $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_invoice")
                    ->where("clue_id=:clue_id and invoice_type=:invoice_type and invoice_header=:invoice_header",array(
                        ":clue_id"=>$clue_id,
                        ":invoice_type"=>$invoice_type,
                        ":invoice_header"=>$invoice_header
                    ))->queryRow();
            }else{
                $row = Yii::app()->db->createCommand()->select("*")->from("sal_clue_invoice")
                    ->where("clue_id=:clue_id and tax_id=:tax_id and invoice_number=:invoice_number and invoice_user=:invoice_user and invoice_type=:invoice_type and invoice_header=:invoice_header",array(
                        ":clue_id"=>$clue_id,
                        ":invoice_type"=>$invoice_type,
                        ":tax_id"=>$tax_id,
                        ":invoice_number"=>$invoice_number,
                        ":invoice_user"=>$invoice_user,
                        ":invoice_header"=>$invoice_header
                    ))->queryRow();
            }
            if($row){
                $data["invoice_id"]=$row["id"];
            }
        }
    }

    protected function valYewudalei(&$data,$keyStr,$item){
        $yewudalei = isset($data[$keyStr])?$data[$keyStr]:"-1";
        $clueType = isset($data["clue_type"])?$data["clue_type"]:1;
        $yewudalei = $clueType==1?"地推":($yewudalei=="地推"?"KA":$yewudalei);
        $row = Yii::app()->db->createCommand()->select("id")->from("sal_yewudalei")
            ->where("name=:name",array(":name"=>$yewudalei))->queryRow();
        if($row){
            $data[$keyStr]=$row["id"];
        }else{
            $yewudalei = $clueType==1?"地推":"KA";
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_yewudalei")
                ->where("name=:name",array(":name"=>$yewudalei))->queryRow();
            $data[$keyStr]=$row["id"];
        }
    }

    protected function valOtherYewudalei(&$data,$keyStr,$item){
        $yewudalei = isset($data[$keyStr])?$data[$keyStr]:"";
        $other_sales_id = isset($data["other_sales_id"])?$data["other_sales_id"]:"";
        if(!empty($other_sales_id)){
            $row = Yii::app()->db->createCommand()->select("id")->from("sal_yewudalei")
                ->where("name=:name",array(":name"=>$yewudalei))->queryRow();
            if($row){
                $data[$keyStr]=$row["id"];
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$yewudalei})";
            }
        }else{
            $data[$keyStr]=null;
        }
    }

    protected function valGroupBool(&$data,$keyStr,$item){
        $clueType = isset($data["clue_type"])?$data["clue_type"]:1;
        if($clueType==2){
            $data[$keyStr]="Y";
        }else{
            $groupName = key_exists($keyStr,$data)?$data[$keyStr]:"否";
            if($groupName=="是"){
                $data[$keyStr]="Y";
            }else{
                $data[$keyStr]="N";
            }
        }
    }

    protected function valVip(&$data,$keyStr,$item){
        $vipName = key_exists($keyStr,$data)?$data[$keyStr]:"否";
        if($vipName=="是"){
            $data[$keyStr]="Y";
        }else{
            $data[$keyStr]="N";
        }
    }

    protected function valEmptyYes(&$data,$keyStr,$item){
        $yesName = key_exists($keyStr,$data)?$data[$keyStr]:"";
        if(empty($yesName)){
            $data[$keyStr]=null;
        }else{
            if($yesName=="是"){
                $data[$keyStr]="Y";
            }else{
                $data[$keyStr]="N";
            }
        }
    }

    protected function valNumber(&$data,$keyStr,$item){
        if(key_exists($keyStr,$data)&&!empty($data[$keyStr])){
            $number = $data[$keyStr];
            if(!is_numeric($number)){
                $this->status="E";
                $this->message=$item['name']."只能是数字({$number})";
            }
        }
    }

    protected function valUStaff(&$data,$keyStr,$item){
        if(key_exists($keyStr,$data)&&!empty($data[$keyStr])){
            $staffList = explode(";",$data[$keyStr]);
            $uStaffData=array();
            if(!empty($staffList)){
                foreach ($staffList as $staffStr){
                    $staffItem = explode(",",$staffStr);
                    if(isset($staffItem[0])){
                        $staffItem[0]=trim($staffItem[0]);
                        $row = $this->getEmployeeListByCode($staffItem[0]);
                        if($row){
                            $temp=array(
                                "employee_id"=>$row["id"]
                            );
                            if(!empty($staffItem[1])){
                                $staffItem[1]=trim($staffItem[1]);
                                if(!empty($staffItem[1])&&is_numeric($staffItem[1])){
                                    if(intval($staffItem[1])!=floatval($staffItem[1])){
                                        $this->status="E";
                                        $this->message=$item['name']."_派单系统id异常({$staffItem[1]})";
                                        return false;
                                    }elseif(intval($staffItem[1])>987654321){
                                        $this->status="E";
                                        $this->message=$item['name']."不能大于987654321({$staffItem[1]})";
                                        return false;
                                    }else{
                                        $temp["u_id"]=intval($staffItem[1]);
                                    }
                                }
                            }
                            $uStaffData[]=$temp;
                        }
                    }
                }
            }
            $data["uStaffData"]=$uStaffData;
        }
    }

    protected function valUPerson(&$data,$keyStr,$item){
        if(key_exists($keyStr,$data)&&!empty($data[$keyStr])){
            $personList = explode(";",$data[$keyStr]);
            $uPersonData=array();
            if(!empty($personList)){
                foreach ($personList as $personStr){
                    $personItem = explode(",",$personStr);
                    if(!empty($personItem[1])&&!empty($personItem[2])){
                        $temp=array(
                            "person_code"=>$personItem[0],
                            "cust_person"=>$personItem[1],
                            "cust_tel"=>trim($personItem[2]),
                            "cust_email"=>empty($personItem[3])?null:trim($personItem[3]),
                            "cust_person_role"=>empty($personItem[4])?null:trim($personItem[4]),
                        );
                        if(!empty($personItem[5])){
                            if(empty($temp["person_code"])){
                                $this->status="E";
                                $this->message="填写派单id({$personItem[5]})后，联系人编号不能为空";
                                return false;
                            }
                            $personItem[5]=trim($personItem[5]);
                            if(!empty($personItem[5])&&is_numeric($personItem[5])){
                                if(intval($personItem[5])!=floatval($personItem[5])){
                                    $this->status="E";
                                    $this->message=$item['name']."_派单系统id异常({$personItem[5]})";
                                    return false;
                                }elseif(intval($personItem[5])>987654321){
                                    $this->status="E";
                                    $this->message=$item['name']."_派单系统id不能大于987654321({$personItem[5]})";
                                    return false;
                                }else{
                                    $temp["u_id"]=intval($personItem[5]);
                                }
                            }
                        }
                        if(!empty($personItem[6])){
                            $personItem[6]=trim($personItem[6]);
                            if(!empty($personItem[6])&&is_numeric($personItem[6])){
                                if(intval($personItem[6])!=floatval($personItem[6])){
                                    $this->status="E";
                                    $this->message=$item['name']."_分组id异常({$personItem[6]})";
                                    return false;
                                }elseif (intval($personItem[6])>987654321){
                                    $this->status="E";
                                    $this->message=$item['name']."_分组id不能大于987654321({$personItem[6]})";
                                    return false;
                                }else{
                                    $temp["u_group_id"]=intval($personItem[6]);
                                }
                            }
                        }
                        $uPersonData[]=$temp;
                    }
                }
            }
            $data["uPersonData"]=$uPersonData;
        }
    }

    protected function valUArea(&$data,$keyStr,$item){
        if(key_exists($keyStr,$data)&&!empty($data[$keyStr])){
            $areaList = explode(";",$data[$keyStr]);
            $uAreaData=array();
            if(!empty($areaList)){
                foreach ($areaList as $areaStr){
                    $areaItem = explode(",",$areaStr);
                    if(!empty($areaItem[0])){
                        $areaItem[0]=trim($areaItem[0]);
                        $row = $this->getCityByName($areaItem[0]);
                        if($row){
                            $temp=array(
                                "city_code"=>$row["code"],
                            );
                            if(!empty($areaItem[1])){
                                $areaItem[1]=trim($areaItem[1]);
                                if(!empty($areaItem[1])&&is_numeric($areaItem[1])){
                                    if(intval($areaItem[1])!=floatval($areaItem[1])){
                                        $this->status="E";
                                        $this->message=$item['name']."_派单系统id异常({$areaItem[1]})";
                                    }elseif(intval($areaItem[1])>987654321){
                                        $this->status="E";
                                        $this->message=$item['name']."_派单系统id不能大于987654321({$areaItem[1]})";
                                        return false;
                                    }else{
                                        $temp["u_id"]=intval($areaItem[1]);
                                    }
                                }
                            }
                            $uAreaData[]=$temp;
                        }
                    }
                }
            }
            $data["uAreaData"]=$uAreaData;
        }
    }

    protected function valEmpty(&$data,$keyStr,$item){
        if($data[$keyStr]===""||$data[$keyStr]===null){
            $data[$keyStr]=null;
        }
    }

    protected function valEmptyNumber(&$data,$keyStr,$item){
        if($data[$keyStr]===""||$data[$keyStr]===null){
            $data[$keyStr]=null;
        }elseif (!is_numeric($data[$keyStr])){
            $this->status="E";
            $this->message=$item['name']."只能是数字({$data[$keyStr]})";
        }
    }

    protected function valEmptyInt(&$data,$keyStr,$item){
        if($data[$keyStr]===""||$data[$keyStr]===null){
            $data[$keyStr]=null;
        }elseif (!is_numeric($data[$keyStr])||floatval($data[$keyStr])!=intval($data[$keyStr])){
            $this->status="E";
            $this->message=$item['name']."只能是数字({$data[$keyStr]})".floatval($data[$keyStr])."_".intval($data[$keyStr]);
        }elseif(intval($data[$keyStr])>987654321){
            $this->status="E";
            $this->message=$item['name']."不能大于987654321({$data[$keyStr]})";
        }
    }

    protected function valUGroupID(&$data,$keyStr,$item){
        $u_person_id = key_exists("u_person_id",$data)?$data["u_person_id"]:"";
        if($data[$keyStr]===""||$data[$keyStr]===null){
            if(!empty($u_person_id)){
                $this->status="E";
                $this->message="填写联系人关联id后，联系人关联分组id不能为空";
            }else{
                $data[$keyStr]=null;
            }
        }elseif (!is_numeric($data[$keyStr])||floatval($data[$keyStr])!=intval($data[$keyStr])){
            $this->status="E";
            $this->message=$item['name']."只能是数字({$data[$keyStr]})".floatval($data[$keyStr])."_".intval($data[$keyStr]);
        }elseif(intval($data[$keyStr])>987654321){
            $this->status="E";
            $this->message=$item['name']."不能大于987654321({$data[$keyStr]})";
        }
    }

    protected function valSource(&$data,$keyStr,$item){
        $sourceType = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        if(empty($sourceType)){
            $data[$keyStr]=null;
        }else{
            $list=CGetName::getClueSourceList();
            $key = array_search($sourceType, $list);
            if($key!==false){
                $data[$keyStr]=$key;
            }else{
                $this->status="E";
                $this->message=$item['name']."不存在({$sourceType})";
            }
        }
    }

    protected function valPersonCode(&$data,$keyStr,$item){
        $personCode = key_exists($keyStr, $data) ? $data[$keyStr] : '';
        $u_person_id = key_exists("u_person_id", $data) ? $data["u_person_id"] : '';
        if(empty($personCode)){
            if(!empty($u_person_id)){
                $this->status="E";
                $this->message="填写联系人关联id后，".$item['name']."不能为空";
            }else{
                $data[$keyStr]=null;
            }
        }else{
            if(empty($u_person_id)){
                $this->status="E";
                $this->message="填写联系人编号后，联系人关联id不能为空";
            }else{
                $data[$keyStr]=$personCode;
            }
        }
    }

    //在PHP中删除UTF-8字符串中的
    public static function removeUtf8mb4($text) {
        if(is_array($text)){
            return $text;
        }
        // 正则表达式匹配所有四字节UTF-8字符
        $regex = '/[\x{10000}-\x{10FFFF}]/u';
        // 使用正则表达式进行替换，替换为空字符串
        return preg_replace($regex, '', $text);
    }
}
