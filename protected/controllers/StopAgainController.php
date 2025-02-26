<?php

class StopAgainController extends Controller
{
	public $function_id='SC07';
	
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
				'expression'=>array('StopAgainController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('StopAgainController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new StopAgainList();
		$searchBool = Yii::app()->user->validFunction('CN13');
		if(StopBackList::getEmployee($model,$searchBool)){
            if (isset($_POST['StopAgainList'])) {
                $model->attributes = $_POST['StopAgainList'];
            } else {
                $session = Yii::app()->session;
                if (isset($session['stopAgain_c01']) && !empty($session['stopAgain_c01'])) {
                    $criteria = $session['stopAgain_c01'];
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

	public function actionView($index)
	{
		$model = new StopAgainForm('view');
        $searchBool = Yii::app()->user->validFunction('CN13');
        if(StopBackList::getEmployee($model,$searchBool)){
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
		$model = new StopAgainForm('edit');
        $searchBool = Yii::app()->user->validFunction('CN13');
        if(StopBackList::getEmployee($model,$searchBool)){
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}

    public function actionSave()
    {
        if (isset($_POST['StopAgainForm'])) {
            $model = new StopAgainForm($_POST['StopAgainForm']['scenario']);
            $model->attributes = $_POST['StopAgainForm'];
            if ($model->validate()) {
                $model->saveData();
                $model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                if($model->getAgainType()===1){
                    $this->redirect(Yii::app()->createUrl('stopAgain/edit',array('index'=>$model->id)));
                }else{
                    $this->redirect(Yii::app()->createUrl('stopAgain/index'));
                }
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }

	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('SC07');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('SC07');
	}
}
