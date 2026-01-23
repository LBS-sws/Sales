<?php

class AjaxController extends Controller
{
	public $interactive = false;
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl - checksession', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('dummy','remotelogin','remoteloginonlib','notify','notifybadge','getClientInfo'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Lists all models.
	 */
	public function actionDummy()
	{
		Yii::app()->end();
	}

	public function actionRemotelogin() {
		$rtn = '';
		if (!Yii::app()->user->isGuest) {
			$id = Yii::app()->user->staffid(); 
			if (!empty($id)) {
				$suffix = Yii::app()->params['envSuffix'];
				$sql = "select code,code_old from hr$suffix.hr_employee where id=$id";
				$row = Yii::app()->db->createCommand($sql)->queryRow();
				if ($row !== false) {
					$staffcode = $row['code'];
					$staffocode = empty($row['code_old']) ? $row['code'] : $row['code_old'];
					$lang = Yii::app()->language;
					$lang = ($lang=='zh_cn' ? 'zhcn' : ($lang=='zh_tw' ? 'zhtw' : 'en'));
					$sesskey = Yii::app()->user->sessionkey();
					$salt = 'lbscorp168';
					$key = md5($staffcode.$salt.$sesskey.$staffocode);
					$temp = array(
							'id'=>$staffcode.':'.$staffocode,
							'sk'=>$sesskey,
							'ky'=>$key,
							'lang'=>$lang,
						);
					$rtn = json_encode($temp);
				}
			}
		}
		echo $rtn;
		Yii::app()->end();
	}

	public function actionRemoteloginonlib() {
		$rtn = '';
		if (!Yii::app()->user->isGuest) {
			$id = Yii::app()->user->id;
			if (!empty($id)) {
				$suffix = Yii::app()->params['envSuffix'];
				$sql = "select field_value from security$suffix.sec_user_info where username='$id' and 
						field_id='onlibuser'
					";
				$row = Yii::app()->db->createCommand($sql)->queryRow();
				if ($row !== false && !empty($row['field_value'])) {
					$temp = array(
						'id'=>$row['field_value'],
						'pwd'=>$row['field_value'].'$1688',
					);
					$rtn = json_encode($temp);
				}
			}
		}
		echo $rtn;
		Yii::app()->end();
	}
	
	public function actionChecksession() {
		$rtn = true;
		if (!Yii::app()->user->isGuest && Yii::app()->params['sessionIdleTime']!=='') {
			if (isset(Yii::app()->session['session_time'])) {
				$time = Yii::app()->session['session_time'];
				$timelimit = "-".Yii::app()->params['sessionIdleTime'];
				$rtn = (strtotime($timelimit) < strtotime($time));
			} else {
				$rtn = false;
			}
		}
		echo '{"loggedin":'.($rtn?'true':'false').'}';
		Yii::app()->end();
	}

	public function actionNotify() {
		$rtn = array();
		if (!Yii::app()->user->isGuest) {
            $rtn = NoticeForm::getNoticeAjax();
		}
		echo json_encode($rtn);
		Yii::app()->end();
	}
	
	public function actionNotifybadge($param='') {
		$rtn = array();
		$items = empty($param) ? array() : json_decode($param);
		foreach ($items as $item) {
//			if (isset($item->code) && isset($item->function) && isset($this->color)) {
			if (Yii::app()->user->validFunction($item->code)) {
				$result = call_user_func($item->function);
				$rtn[] = array('code'=>$item->code,'count'=>$result,'color'=>$item->color);
			}
//			} 
		}
        //动态审核提示数字
        //$list = Counter::countAuditMutual();
        //$rtn = array_merge($rtn,$list);
		echo json_encode($rtn);
		Yii::app()->end();
	}

	/**
	 * 搜索客户信息
	 */
	public function actionGetClientInfo() {
		$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
		if(empty($keyword)){
			echo CJSON::encode(array('status'=>0,'message'=>'请输入搜索关键词'));
			Yii::app()->end();
		}

		$suffix = Yii::app()->params['envSuffix'];
		
		// 先尝试按ID搜索
		if(is_numeric($keyword)){
			$row = Yii::app()->db->createCommand()
				->select("id,cust_name,clue_code")
				->from("sales{$suffix}.sal_clue")
				->where("id=:id",array(":id"=>intval($keyword)))
				->queryRow();
		}else{
			// 按编号或名称搜索
			$row = Yii::app()->db->createCommand()
				->select("id,cust_name,clue_code")
				->from("sales{$suffix}.sal_clue")
				->where("clue_code=:code OR cust_name LIKE :name",array(
					":code"=>$keyword,
					":name"=>"%{$keyword}%"
				))
				->order("id DESC")
				->limit(1)
				->queryRow();
		}

		if($row){
			echo CJSON::encode(array(
				'status'=>1,
				'clue_id'=>$row['id'],
				'cust_name'=>$row['cust_name'],
				'clue_code'=>$row['clue_code']
			));
		}else{
			echo CJSON::encode(array('status'=>0,'message'=>'未找到客户'));
		}
		Yii::app()->end();
	}
}
