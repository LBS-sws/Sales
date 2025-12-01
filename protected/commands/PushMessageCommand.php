<?php
class PushMessageCommand extends CConsoleCommand {
	protected $webroot;
	protected $uid;
	
	protected $id;
	protected $msg_type;
	protected $msg_en;
	protected $msg_cn;
	protected $msg_tw;
	
	protected $type_def = array(
							'SALORDER'=>'sal_CN04',
						);
	
	public function run($args) {
		$this->webroot = Yii::app()->params['webroot'];
		$sql = "select * from sal_push_message
				where status='P' order by lcd limit 1";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row===false) return;
		
		$this->id = $row['id'];
		$this->msg_type = $row['msg_type'];
		$this->msg_en = $row['message_en'];
		$this->msg_cn = $row['message_cn'];
		$this->msg_tw = $row['message_tw'];
		$ts = $row['lud'];
		$this->uid = $row['lcu'];
		
		if (($this->id!=0) && $this->markStatus($this->id, $ts, 'I')) {
			$ts = $this->getTimeStamp($this->id);
			
			$mesg = "ID:".$this->id." TYPE:".$this->msg_type." MSG:".$this->msg_cn."\n";
			echo $mesg;
	
			$log = $this->pushMessage();
			
			$sts = (strpos($log, 'error')===false) ? 'C' : 'F';
			$this->markStatus($this->id, $ts, $sts);
			$this->saveLog($this->id, $log);
			echo "\t-Done (default)\n";
		}
	}

	protected function pushMessage() {
		$content = array(
			"en" => $this->msg_en,
			"zh-Hans" => $this->msg_cn,
			"zh-Hant" => $this->msg_tw,
			);
		
		$heading = array(
			"en" => "LBS Daily Management".(Yii::app()->params['envSuffix']=='uat' ? ' - UAT' : ''),
			"zh-Hans" => "LBS 日常管理".(Yii::app()->params['envSuffix']=='uat' ? ' - 测试版' : ''),
			"zh-Hant" => "LBS 日常管理".(Yii::app()->params['envSuffix']=='uat' ? ' - 測試版' : ''),
			);
		
		$right = $this->type_def[$this->msg_type];
		
		$fields = array(
//			'app_id' => "3183638f-c26a-409c-a80a-00736ae8a772",
			'app_id' => Yii::app()->params['onesignal'],
			'filters' => array(array("field" => "tag", "key" => $right, "relation" => "exists"),),
			'headings' => $heading,
			'contents' => $content,
			'url'=> 'http://118.89.46.224/sa-prod/index.php',
		);
		
		$fields = json_encode($fields);
    	print("\nJSON sent:\n");
    	print($fields);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
												   'Authorization: Basic '.Yii::app()->params['onesignalKey']));
//												   'Authorization: Basic MzMyYThhODQtZTY3Ni00Mzk4LTllZDMtYmFlZWY5MmU1ZWVm'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
	}
	
	protected function markStatus($id, $ts, $sts) {
		$sql = "update sal_push_message set status=:status where id=:id and lud=:ts";
		$command=Yii::app()->db->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$id,PDO::PARAM_INT);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$sts,PDO::PARAM_STR);
		if (strpos($sql,':ts')!==false)
			$command->bindParam(':ts',$ts,PDO::PARAM_STR);
		$cnt = $command->execute();
		return ($cnt>0);
	}
	
	protected function saveLog($id, $msg) {
		$sql = "update sal_push_message set response=:msg where id=:id";
		$command=Yii::app()->db->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$id,PDO::PARAM_INT);
		if (strpos($sql,':msg')!==false)
			$command->bindParam(':msg',$msg,PDO::PARAM_STR);
		$command->execute();
	}
	
	protected function getTimeStamp($id) {
		$ts = '';
		$sql = "select lud from sal_push_message where id=".$id;
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$ts = $row['lud'];
				break;
			}
		}
		return $ts;
	}
}
?>
