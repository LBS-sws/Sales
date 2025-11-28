<?php
class ImportCommand extends CConsoleCommand {
	protected $webroot;
	protected $city;
	protected $uid;
	protected $id;
	
	public function actionIndex() {
		$sql = "select a.* from sal_import_queue a
				where a.status='P' order by a.req_dt asc limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row){
            $id = $row['id'];
            $this->id=$id;
            Yii::app()->db->createCommand()->update("sal_import_queue",array(
                "status"=>"I"
            ),"id=:id",array(":id"=>$id));
            echo "ID:import_{$id}\n";
            set_error_handler([$this,"myErrorHandler"]);
            switch ($row["import_type"]){
                case "clueBox":
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
                    $model = new ImportClientStoreForm('edit');
                    $model->id = $id;
                    $model->city_allow = $row["city_allow"];
                    $model->req_dt = $row["req_dt"];
                    $model->username = $row["username"];
                    $model->import_type = $row["import_type"];
                    $model->file_path = $row["import_file"];
                    $list = $model->importChangeOne();
                    break;
                case "vir":
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
                echo "\t-Done (default)\n";
            }else{
                Yii::app()->db->createCommand()->update("sal_import_queue",array(
                    "status"=>"E",
                    "error_file"=>$list["error_file"],
                    "fin_dt"=>date("Y-m-d H:i:s"),
                    "message"=>$list["msg"],
                    "success_num"=>$list["success_num"],
                    "error_num"=>$list["error_num"],
                ),"id=".$id);
                echo "\t-FAIL\n";
            }
        }
	}

    public function myErrorHandler($errno, $errstr, $errfile, $errline) {
        $message = "错误 [$errno]: $errstr 在 $errfile 第 $errline 行。";
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
}
?>