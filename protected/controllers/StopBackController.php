<?php

class StopBackController extends Controller
{
	public $function_id='SC01';
	
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
				'actions'=>array('edit','delete','save','updateAjaxVip'),
				'expression'=>array('StopBackController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('StopBackController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new StopBackList();
		if(StopBackList::getEmployee($model)){
            if (isset($_POST['StopBackList'])) {
                $model->attributes = $_POST['StopBackList'];
            } else {
                $session = Yii::app()->session;
                if (isset($session['stopBack_c01']) && !empty($session['stopBack_c01'])) {
                    $criteria = $session['stopBack_c01'];
                    $model->setCriteria($criteria);
                }
            }
            $model->determinePageNum($pageNum);
            $model->retrieveDataByPage($model->pageNum);
            $this->render('index',array('model'=>$model));
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}


	public function actionSave()
	{
		if (isset($_POST['StopBackForm'])) {
			$model = new StopBackForm($_POST['StopBackForm']['scenario']);
            $model->attributes = $_POST['StopBackForm'];
            if(StopBackList::getEmployee($model)){
                if ($model->validate()) {
                    $model->saveData();
                    $model->scenario = 'edit';
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                    $this->redirect(Yii::app()->createUrl('stopBack/edit',array('index'=>$model->service_id)));
                } else {
                    $message = CHtml::errorSummary($model);
                    Dialog::message(Yii::t('dialog','Validation Message'), $message);
                    $this->render('form',array('model'=>$model,));
                }
            }else{
                Dialog::message(Yii::t('dialog','Validation Message'), "该账号未绑定员工，请与管理员联系");
                $this->render('form',array('model'=>$model));
            }
		}
	}


	public function actionUpdateAjaxVip()
	{
        $model = new StopBackList();
        if(StopBackList::getEmployee($model)){
            $serviceId = key_exists("service_id",$_POST)?$_POST["service_id"]:0;
            $serviceId = is_numeric($serviceId)?intval($serviceId):0;
            $res = $model->updateVip($serviceId);
            print json_encode($res);
        }else{
            print json_encode(array("status"=>0,"message"=>"该账号未绑定员工，请与管理员联系"));
        }
	}

	public function actionView($index)
	{
		$model = new StopBackForm('view');
        if(StopBackList::getEmployee($model)){
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionEdit($index)
	{
		$model = new StopBackForm('edit');
        if(StopBackList::getEmployee($model)){
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionDelete()
	{
		$model = new StopBackForm('delete');
        if (isset($_POST['StopBackForm'])) {
            $model->attributes = $_POST['StopBackForm'];
            if(StopBackList::getEmployee($model)){
                if ($model->validate()) {
                    $model->saveData();
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                    $this->redirect(Yii::app()->createUrl('stopBack/index'));
                } else {
                    $message = CHtml::errorSummary($model);
                    Dialog::message(Yii::t('dialog','Validation Message'), $message);
                    $this->render('form',array('model'=>$model));
                }
            }else{
                Dialog::message(Yii::t('dialog','Validation Message'), "该账号未绑定员工，请与管理员联系");
                $this->render('form',array('model'=>$model));
            }
        }
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('SC01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('SC01');
	}
}
