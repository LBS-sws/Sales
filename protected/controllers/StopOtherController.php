<?php

class StopOtherController extends Controller
{
	public $function_id='SC02';
	
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
				'actions'=>array('shiftAll','shiftOne','edit'),
				'expression'=>array('StopOtherController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('StopOtherController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new StopOtherList();
		if (isset($_POST['StopOtherList'])) {
			$model->attributes = $_POST['StopOtherList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['stopOther_c01']) && !empty($session['stopOther_c01'])) {
				$criteria = $session['stopOther_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
        $saleman = StopOtherList::saleman();
		$this->render('index',array('model'=>$model,'saleman'=>$saleman));
	}

	public function actionShiftOne()
	{
		if (isset($_POST['StopOtherForm'])) {
			$model = new StopOtherForm('shift');
			$model->attributes = $_POST['StopOtherForm'];
			$model->shiftId[$model->service_id] = 1;
			if ($model->validate()) {
				$model->shiftAll();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('stopOther/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('stopOther/edit',array("index"=>$model->service_id)));
			}
		}
	}

	public function actionShiftAll()
	{
		if (isset($_POST['StopOtherForm'])) {
			$model = new StopOtherForm('shift');
			$model->attributes = $_POST['StopOtherForm'];
			if ($model->validate()) {
				$model->shiftAll();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
            $this->redirect(Yii::app()->createUrl('stopOther/index'));
		}
	}

	public function actionView($index)
	{
		$model = new StopOtherForm('view');
        $saleman = StopOtherList::saleman();
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,'saleman'=>$saleman,));
		}
	}
	
	public function actionEdit($index)
	{
		$model = new StopOtherForm('edit');
        $saleman = StopOtherList::saleman();
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,'saleman'=>$saleman,));
		}
	}
	
	public function actionDelete()
	{
		$model = new StopOtherForm('delete');
		if (isset($_POST['StopOtherForm'])) {
			$model->attributes = $_POST['StopOtherForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('stopOther/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('SC02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('SC02');
	}
}
