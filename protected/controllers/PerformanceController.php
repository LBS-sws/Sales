<?php

class PerformanceController extends Controller
{
	public $function_id='HK04';

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
				'actions'=>array('new','edit','delete','save'),
				'expression'=>array('PerformanceController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('PerformanceController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PerformanceList;
		if (isset($_POST['PerformanceList'])) {
			$model->attributes = $_POST['PerformanceList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_hc03']) && !empty($session['criteria_hc03'])) {
				$criteria = $session['criteria_hc03'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['PerformanceForm'])) {
			$model = new PerformanceForm($_POST['PerformanceForm']['scenario']);
			$model->attributes = $_POST['PerformanceForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('performance/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new PerformanceForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new PerformanceForm('new');
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new PerformanceForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new CusttypeForm('delete');
		if (isset($_POST['PerformanceForm'])) {
			$model->attributes = $_POST['PerformanceForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('custtype/edit',array('index'=>$model->id)));
			} else {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
				$this->redirect(Yii::app()->createUrl('custtype/index'));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HK04');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HK04');
	}
}
