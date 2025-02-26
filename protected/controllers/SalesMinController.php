<?php

class SalesMinController extends Controller
{
	public $function_id='HC11';
	
	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
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
			array('allow', 
				'actions'=>array('edit','save'),
				'expression'=>array('SalesMinController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','edit','test','testU','emailLast'),
				'expression'=>array('SalesMinController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionEmailLast($title="地区每周体检报告",$city=''){
	    $sql = "subject like '%{$title}%'";
	    if(!empty($city)){
	        $cityName = General::getCityName($city);
	        echo $cityName."<br/><br/>";
	        $sql.=" and subject like '%{$cityName}%'";
        }
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("subject,message,lcd")->from("swoper{$suffix}.swo_email_queue")
            ->where($sql)->order("lcd desc")->queryRow();
        var_dump($row);
	    Yii::app()->end();
    }

	public function actionTest($date="",$emailBool=false){
	    $model = new TestForm();
	    $model->run($date,$emailBool);
	    Yii::app()->end();
    }

	public function actionTestU(){
        $year = date("Y");
        $month = date("n");
        echo "Year:{$year}<br/>Month:{$month}<br/><br/>";
        $json = Invoice::getActualAmount($year,$month);
        var_dump($json);
	    Yii::app()->end();
    }


	public function actionSave()
	{
		if (isset($_POST['SalesMinForm'])) {
			$model = new SalesMinForm($_POST['SalesMinForm']['scenario']);
			$model->attributes = $_POST['SalesMinForm'];
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('salesMin/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView()
	{
		$model = new SalesMinForm('view');
		if (!$model->retrieveData()) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit()
	{
		$model = new SalesMinForm('edit');
		if(!self::allowReadWrite()){
		    $model->setScenario("view");
        }
		if (!$model->retrieveData()) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HC11');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HC11');
	}
}
