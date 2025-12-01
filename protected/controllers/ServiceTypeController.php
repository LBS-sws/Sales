<?php

class ServiceTypeController extends Controller
{
	public $function_id='HC16';

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
				'actions'=>array('new','edit','delete','save','addOld','sync'),
				'expression'=>array('ServiceTypeController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ServiceTypeController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


    public function actionSync()
    {
        set_time_limit(0);
        echo "start:<br/>";
        $model = new ClueASync();
        $model->syncByVisit();
        $model->syncByKA();
        echo "end:<br/>";
        die();
    }

    public function actionAddOld()
    {
        $model = new ServiceTypeForm('edit');
        $model->addOld();
        die();
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new ServiceTypeList;
		if (isset($_POST['ServiceTypeList'])) {
			$model->attributes = $_POST['ServiceTypeList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['serviceType_c01']) && !empty($session['serviceType_c01'])) {
				$criteria = $session['serviceType_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['ServiceTypeForm'])) {
			$model = new ServiceTypeForm($_POST['ServiceTypeForm']['scenario']);
			$model->attributes = $_POST['ServiceTypeForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('serviceType/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ServiceTypeForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0)
	{
		$model = new ServiceTypeForm('new');
        if(!empty($index)){
            $model->retrieveData($index,true);
            $model->id=null;
            $model->id_char=null;
        }
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new ServiceTypeForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ServiceTypeForm('delete');
		if (isset($_POST['ServiceTypeForm'])) {
			$model->attributes = $_POST['ServiceTypeForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('serviceType/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('serviceType/edit',array('index'=>$model->id)));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HC16');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HC16');
	}
}
