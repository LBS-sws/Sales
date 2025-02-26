<?php

class RankhistoryController extends Controller
{
	public $function_id='HD02';

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
				'actions'=>array('new','edit','delete','save','index_s','fileremove','excel'),
				'expression'=>array('RankHistoryController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('RankHistoryController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


    public function actionIndex($pageNum=0)
    {
        $model = new RankHistoryForm;
        $city=$model->city();
        $season=$model->season();
        $this->render('index',array('model'=>$model,'city'=>$city,'season'=>$season,));
    }

	public function actionIndex_s($pageNum=0)
	{
		$model = new RankHistoryList;
		if (isset($_POST['RankHistoryForm'])) {
            $a = $_POST['RankHistoryForm'];
            $model->attributes = $a;
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
				$model->setCriteria($criteria);
			}
		}
        if(!empty($_POST['RankHistoryForm']['city'])){
            $city=$_POST['RankHistoryForm']['city'];
        }
        $session = Yii::app()->session;
        if(!empty($city)){
            $session['city']= $city;
        }
        if(empty($city)){
            $a['city']=$session['city'];
        }
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum,$a);
		$this->render('index_s',array('model'=>$model,'a'=>$a));
	}


//	public function actionSave()
//	{
//		if (isset($_POST['RankForm'])) {
//			$model = new RankForm($_POST['RankForm']['scenario']);
//			$model->attributes = $_POST['RankForm'];
//			if ($model->validate()) {
//				$model->saveData();
//				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
//				$this->redirect(Yii::app()->createUrl('target/edit',array('index'=>$model->id)));
//			} else {
//				$message = CHtml::errorSummary($model);
//				Dialog::message(Yii::t('dialog','Validation Message'), $message);
//				$this->render('form',array('model'=>$model,));
//			}
//		}
//	}

	public function actionView($index)
	{
		$model = new RankHistoryForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}


//	public function actionEdit($index)
//	{
//		$model = new RankForm('edit');
//		if (!$model->retrieveData($index)) {
//			throw new CHttpException(404,'The requested page does not exist.');
//		} else {
//			$this->render('form',array('model'=>$model,));
//		}
//	}
	

	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HD02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HD02');
	}
}
