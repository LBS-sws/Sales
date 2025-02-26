<?php

class HistoryRankController extends Controller
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
				'expression'=>array('HistoryRankController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('HistoryRankController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


    public function actionIndex($pageNum=0)
    {
        $model = new HistoryRankForm;
        $city=$model->city();
        $season=$model->season();
        //     $model->retrieveDatas($model);
//        print_r('<$city>');
      //  print_r($season);
        $this->render('index',array('model'=>$model,'city'=>$city,'season'=>$season,));
    }

	public function actionIndex_s($pageNum=0)
	{
		$model = new HistoryRankList;
		if (isset($_POST['HistoryRankForm'])) {
            $a = $_POST['HistoryRankForm'];
            $model->attributes = $a;
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
				$model->setCriteria($criteria);
			}
		}
        if(!empty($_POST['HistoryRankForm']['city'])){
            $city=$_POST['HistoryRankForm']['city'];
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
		$model = new HistoryRankForm('view');
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
