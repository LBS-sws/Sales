<?php

class StopSearchController extends Controller
{
	public $function_id='SC05';
	
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
				'actions'=>array('edit'),
				'expression'=>array('StopSearchController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('StopSearchController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new StopSearchList();
		$searchBool = Yii::app()->user->validFunction('CN13');
		if(StopBackList::getEmployee($model,$searchBool)){
            if (isset($_POST['StopSearchList'])) {
                $model->attributes = $_POST['StopSearchList'];
            } else {
                $session = Yii::app()->session;
                if (isset($session['stopSearch_c01']) && !empty($session['stopSearch_c01'])) {
                    $criteria = $session['stopSearch_c01'];
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
		$model = new StopSearchForm('view');
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
		$model = new StopSearchForm('view');
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
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('SC05');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('SC05');
	}
}
