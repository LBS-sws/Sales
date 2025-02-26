<?php

class StopNoneController extends Controller
{
	public $function_id='SC06';
	
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
				'actions'=>array('edit','save','updateAjaxVip'),
				'expression'=>array('StopNoneController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('StopNoneController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new StopNoneList();
        if (isset($_POST['StopNoneList'])) {
            $model->attributes = $_POST['StopNoneList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['stopNone_c01']) && !empty($session['stopNone_c01'])) {
                $criteria = $session['stopNone_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['StopNoneForm'])) {
			$model = new StopNoneForm($_POST['StopNoneForm']['scenario']);
            $model->attributes = $_POST['StopNoneForm'];
            if ($model->validate()) {
                $model->saveData();
                $model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('stopNone/edit',array('index'=>$model->service_id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
		}
	}


	public function actionUpdateAjaxVip()
	{
        $model = new StopNoneList();
        $serviceId = key_exists("service_id",$_POST)?$_POST["service_id"]:0;
        $serviceId = is_numeric($serviceId)?intval($serviceId):0;
        $res = $model->updateVip($serviceId);
        print json_encode($res);
	}

	public function actionView($index)
	{
		$model = new StopNoneForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
	}
	
	public function actionEdit($index)
	{
		$model = new StopNoneForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('SC06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('SC06');
	}
}
