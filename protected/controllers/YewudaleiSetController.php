<?php

class YewudaleiSetController extends Controller
{
	public $function_id='HC19';

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
				'expression'=>array('YewudaleiSetController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('YewudaleiSetController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new YewudaleiSetList;
		if (isset($_POST['YewudaleiSetList'])) {
			$model->attributes = $_POST['YewudaleiSetList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['yewudaleiSet_c01']) && !empty($session['yewudaleiSet_c01'])) {
				$criteria = $session['yewudaleiSet_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['YewudaleiSetForm'])) {
			$model = new YewudaleiSetForm($_POST['YewudaleiSetForm']['scenario']);
			$model->attributes = $_POST['YewudaleiSetForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('yewudaleiSet/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new YewudaleiSetForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new YewudaleiSetForm('new');
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new YewudaleiSetForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new YewudaleiSetForm('delete');
		if (isset($_POST['YewudaleiSetForm'])) {
			$model->attributes = $_POST['YewudaleiSetForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('yewudaleiSet/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('yewudaleiSet/edit',array('index'=>$model->id)));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HC19');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HC19');
	}
}
