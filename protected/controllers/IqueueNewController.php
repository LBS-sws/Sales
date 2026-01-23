<?php
class IqueueNewController extends Controller
{
	public $function_id='XF01';

	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
		);
	}

	public function accessRules()
	{
		return array(
			array('allow', 
				'actions'=>array('index','downExcel'),
				'expression'=>array('IqueueController','allowExecute'),
			),
			array('allow',
				'actions'=>array('remove'),
				'expression'=>array('IqueueController','allowRemove'),
			),
			array('allow',
				'actions'=>array('superRemove'),
				'expression'=>array('IqueueController','allowSuperRemove'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionRemove($index) {
		$model = new IqueueList('view');
        $result = $model->remove($index);
        if ($result !== false) {
            echo json_encode(array('status' => 'success', 'message' => $result));
        } else {
            echo json_encode(array('status' => 'error', 'message' => '删除失败'));
        }
        Yii::app()->end();
    }
	
	public function actionSuperRemove() {
		// 超级删除：删除所有导入数据（report_id > 0）
		// 1. 权限检查
		if (Yii::app()->user->id != 'xiangsong') {
			echo json_encode(array('status' => 'error', 'message' => '权限不足，仅超级管理员可执行此操作'));
			Yii::app()->end();
		}
		
		// 2. 环境检查：只允许在UAT环境执行
		$envSuffix = Yii::app()->params['envSuffix'];
		if ($envSuffix !== 'uat') {
			echo json_encode(array('status' => 'error', 'message' => '超级删除功能仅限UAT环境使用，当前环境不允许执行此操作'));
			Yii::app()->end();
		}
		
		$model = new IqueueList('view');
		$result = $model->superRemove();
		if ($result !== false) {
			echo json_encode(array('status' => 'success', 'message' => $result));
		} else {
			echo json_encode(array('status' => 'error', 'message' => '超级删除失败'));
		}
		Yii::app()->end();
	}

	public function actionIndex($pageNum=0) {
		$model = new IqueueList;
		if (isset($_POST['IqueueList'])) {
			$model->attributes = $_POST['IqueueList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xf01']) && !empty($session['criteria_xf01'])) {
				$criteria = $session['criteria_xf01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionDownExcel($index,$type) {
        $row = Yii::app()->db->createCommand()->select("import_name,error_file,import_file")->from("sal_import_queue")
            ->where("id=:id",array(":id"=>$index))->queryRow();
        if($row){
			if($type!="error"){
                $file = $row["import_file"];
                $filename= iconv('utf-8','gbk//ignore',$row['import_name']);
                header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"); //for pdf or excel file
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                $handle = fopen($file,'r');
                fpassthru($handle);
                fclose($handle);
                die();
			}else{
                $file = $row["error_file"];
                $filename= iconv('utf-8','gbk//ignore',"导入失败");
                $filename.=".xlsx";
                header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"); //for pdf or excel file
                //header('Content-Type:text/plain; charset=ISO-8859-15');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                header('Content-Length: ' . strlen($file));
                echo $file;
                die();
			}
		}else{
            throw new CHttpException(404,'The requested page does not exist.');
		}
	}
	
	public static function allowExecute() {
		return Yii::app()->user->validFunction('XF01');
	}

	public static function allowRemove() {
		return Yii::app()->user->validFunction('XF01');
	}
	
	public static function allowSuperRemove() {
		return Yii::app()->user->id == 'xiangsong';
	}
}
?>
