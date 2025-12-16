<?php
class ImportCommand extends CConsoleCommand {
	protected $webroot;
	protected $city;
	protected $uid;
	protected $id;
	
	public function actionIndex() {
		$logFile = Yii::getPathOfAlias('application.runtime').'/import_'.date('Y-m-d').'.log';
		$msg = "\n=== 导入命令开始执行 ".date('Y-m-d H:i:s')." ===";
		echo $msg."\n";
		$this->writeLog($logFile, $msg);
		
		$sql = "select a.* from sal_import_queue a where a.status='P' order by a.req_dt asc limit 1";
		
		echo "\n开始检查待导入任务...\n";
		echo "执行SQL: {$sql}\n";
		$this->writeLog($logFile, "执行SQL: {$sql}");
		
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row){
            $id = $row['id'];
            $this->id=$id;
            $msg = "找到导入任务 ID: {$id}, 类型: {$row['import_type']}";
            echo "{$msg}\n";
            $this->writeLog($logFile, $msg);
            
            Yii::app()->db->createCommand()->update("sal_import_queue",array("status"=>"I"),"id=:id",array(":id"=>$id));
            echo "ID:import_{$id}\n";
            $this->writeLog($logFile, "更新状态为处理中(I)");
            
            set_error_handler([$this,"myErrorHandler"]);
            switch ($row["import_type"]){
                case "clueBox":
                    echo "\t-正在导入线索库...\n";
                    $this->writeLog($logFile, "正在导入线索库");
                    $model = new ImportClueBoxForm('edit');
                    $model->id = $id;
                    $model->city_allow = $row["city_allow"];
                    $model->req_dt = $row["req_dt"];
                    $model->username = $row["username"];
                    $model->import_type = $row["import_type"];
                    $model->file_path = $row["import_file"];
                    $list = $model->importChangeOne();
                    break;
                case "clue":
                    echo "\t-正在导入线索...\n";
                    $this->writeLog($logFile, "正在导入线索");
                    $model = new ImportClueForm('edit');
                    $model->id = $id;
                    $model->city_allow = $row["city_allow"];
                    $model->req_dt = $row["req_dt"];
                    $model->username = $row["username"];
                    $model->import_type = $row["import_type"];
                    $model->file_path = $row["import_file"];
                    $list = $model->importChangeOne();
                    break;
                case "clueStore":
                    echo "\t-正在导入线索门店...\n";
                    $this->writeLog($logFile, "正在导入线索门店");
                    $model = new ImportClueStoreForm('edit');
                    $model->id = $id;
                    $model->city_allow = $row["city_allow"];
                    $model->req_dt = $row["req_dt"];
                    $model->username = $row["username"];
                    $model->import_type = $row["import_type"];
                    $model->file_path = $row["import_file"];
                    $list = $model->importChangeOne();
                    break;
                case "client":
                    echo "\t-正在导入派单客户...\n";
                    $this->writeLog($logFile, "正在导入派单客户");
                    $model = new ImportClientForm('edit');
                    $model->id = $id;
                    $model->city_allow = $row["city_allow"];
                    $model->req_dt = $row["req_dt"];
                    $model->username = $row["username"];
                    $model->import_type = $row["import_type"];
                    $model->file_path = $row["import_file"];
                    $list = $model->importChangeOne();
                    break;
                case "clientStore":
                    echo "\t-正在导入派单门店...\n";
                    echo "\t导入文件: ".$row["import_file"]."\n";
                    echo "\t开始调用importChangeOne()...\n";
                    $this->writeLog($logFile, "正在导入派单门店");
                    $this->writeLog($logFile, "导入文件: ".$row["import_file"]);
                    $this->writeLog($logFile, "开始调用importChangeOne()");
                    $model = new ImportClientStoreForm('edit');
                    $model->id = $id;
                    $model->city_allow = $row["city_allow"];
                    $model->req_dt = $row["req_dt"];
                    $model->username = $row["username"];
                    $model->import_type = $row["import_type"];
                    $model->file_path = $row["import_file"];
                    $list = $model->importChangeOne();
                    $code = isset($list["code"])?$list["code"]:"null";
                    $msg = "importChangeOne()执行完毕，返回结果: code=".$code;
                    echo "\t{$msg}\n";
                    $this->writeLog($logFile, $msg);
                    break;
                case "vir":
                    echo "\t-正在导入虚拟员工...\n";
                    $this->writeLog($logFile, "正在导入虚拟员工");
                    $model = new ImportVirForm('edit');
                    $model->id = $id;
                    $model->city_allow = $row["city_allow"];
                    $model->req_dt = $row["req_dt"];
                    $model->username = $row["username"];
                    $model->import_type = $row["import_type"];
                    $model->file_path = $row["import_file"];
                    $list = $model->importChangeOne();
                    break;
                case "cont":
                    echo "\t-正在导入合约...\n";
                    $this->writeLog($logFile, "正在导入合约");
                    $model = new ImportContForm('edit');
                    $model->id = $id;
                    $model->city_allow = $row["city_allow"];
                    $model->req_dt = $row["req_dt"];
                    $model->username = $row["username"];
                    $model->import_type = $row["import_type"];
                    $model->file_path = $row["import_file"];
                    $list = $model->importChangeOne();
                    break;
                default:
                    echo "\t-导入类型错误: {$row['import_type']}\n";
                    $this->writeLog($logFile, "导入类型错误: {$row['import_type']}");
                    $list=array("code"=>400,"success_num"=>0,"error_num"=>0,'msg'=>"import_type error:{$row['import_type']}",'error_file'=>null);
            }
            $message = isset($list["msg"])?$list["msg"]:"成功";
            $list["msg"] = mb_strlen($message)>250?mb_substr($message,0,250,'UTF-8'):$message;
            if(isset($list["code"])&&$list["code"]==200){
                Yii::app()->db->createCommand()->update("sal_import_queue",array(
                    "status"=>"C",
                    "message"=>$list["msg"],
                    "error_file"=>$list["error_file"],
                    "fin_dt"=>date("Y-m-d H:i:s"),
                    "success_num"=>$list["success_num"],
                    "error_num"=>$list["error_num"],
                ),"id=".$id);
                $msg = "导入完成(成功): 成功=".$list["success_num"].", 失败=".$list["error_num"];
                echo "\t{$msg}\n";
                $this->writeLog($logFile, $msg);
            }else{
                Yii::app()->db->createCommand()->update("sal_import_queue",array(
                    "status"=>"E",
                    "error_file"=>$list["error_file"],
                    "fin_dt"=>date("Y-m-d H:i:s"),
                    "message"=>$list["msg"],
                    "success_num"=>$list["success_num"],
                    "error_num"=>$list["error_num"],
                ),"id=".$id);
                $msg = "导入失败: ".$list["msg"];
                echo "\t{$msg}\n";
                $this->writeLog($logFile, $msg);
            }
        }else{
            echo "未找到待处理的导入任务\n";
            $this->writeLog($logFile, "未找到待处理的导入任务");
        }
        $msg = "=== 导入命令执行完毕 ".date('Y-m-d H:i:s')." ===";
        echo "{$msg}\n";
        $this->writeLog($logFile, $msg);
	}

    public function myErrorHandler($errno, $errstr, $errfile, $errline) {
        $message = "错误 [$errno]: $errstr 在 $errfile 第 $errline 行。";
        echo "\t-ERROR: {$message}\n";
        $list=array("code"=>400,"success_num"=>0,"error_num"=>0,'msg'=>'','error_file'=>null);
        $list["msg"] = mb_strlen($message)>250?mb_substr($message,0,250,'UTF-8'):$message;
        Yii::app()->db->createCommand()->update("sal_import_queue",array(
            "status"=>"E",
            "error_file"=>$list["error_file"],
            "fin_dt"=>date("Y-m-d H:i:s"),
            "message"=>$list["msg"],
            "success_num"=>$list["success_num"],
            "error_num"=>$list["error_num"],
        ),"id=".$this->id);
        // 可以选择是否继续执行或终止脚本
        if (error_reporting() !== 0) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        }
    }

    private function writeLog($logFile, $message){
        echo $message."\n";
        $dir = dirname($logFile);
        if(!is_dir($dir)){
            @mkdir($dir, 0777, true);
        }
        @file_put_contents($logFile, "[" .date('H:i:s'). "] " .$message. "\n", FILE_APPEND);
    }
}