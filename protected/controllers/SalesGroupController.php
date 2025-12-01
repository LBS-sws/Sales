<?php

class SalesGroupController extends Controller
{
	public $function_id='HC13';
	
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
				'expression'=>array('SalesGroupController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index'),
				'expression'=>array('SalesGroupController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	public function actionSave()
	{
		if (isset($_POST['SalesGroupForm'])) {
			$model = new SalesGroupForm($_POST['SalesGroupForm']['scenario']);
			$model->attributes = $_POST['SalesGroupForm'];
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('salesGroup/edit'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionIndex($index=1)
	{
        echo "index:{$index}<br/><br/><br/>";
        echo "CGetName::getGroupNextIDByID:<br/>";
        $deleteID=CGetName::getGroupNextIDByID($index);
        var_dump($deleteID);
        echo "<br/><br/>CGetName::getGroupStaffIDByStaffID:<br/>";
        $list=CGetName::getGroupStaffIDByStaffID($index);
        var_dump($list);
        Yii::app()->end();
	}

	public function actionView()
	{
		$model = new SalesGroupForm('view');
		if (!$model->retrieveData()) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit()
	{
		$model = new SalesGroupForm('edit');
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
		return Yii::app()->user->validRWFunction('HC13');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HC13');
	}
}
