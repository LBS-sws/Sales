<?php

class RAStatisticController extends Controller
{
	public $function_id='RA02';
	
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
				'actions'=>array('ajaxSave'),
				'expression'=>array('RAStatisticController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel','downYTD','ajaxDetail'),
				'expression'=>array('RAStatisticController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    //详情列表的異步請求
    public function actionAjaxDetail(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $model = new RAStatisticForm();
            $html =$model->ajaxDetailForHtml();
            echo CJSON::encode(array('status'=>1,'html'=>$html));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('RankingMonth/index'));
        }
    }

	public function actionIndex()
	{
		$model = new RAStatisticForm('index');
        $session = Yii::app()->session;
        if (isset($session['rAStatistic_c01']) && !empty($session['rAStatistic_c01'])) {
            $criteria = $session['rAStatistic_c01'];
            $model->setCriteria($criteria);
        }else{
            $model->search_year = date("Y");
            $model->search_month = date("n");
        }
		$this->render('index',array('model'=>$model));
	}

	public function actionView()
	{
        $model = new RAStatisticForm('view');
        KABotForm::validateEmployee($model);
        if (isset($_POST['RAStatisticForm'])) {
            $model->attributes = $_POST['RAStatisticForm'];
            if ($model->validate()) {
                $model->retrieveData();
                $this->render('form',array('model'=>$model));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('index',array('model'=>$model));
            }
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
	}

	public function actionDownExcel()
	{
        $model = new RAStatisticForm('view');
        if (isset($_POST['RAStatisticForm'])) {
            $model->attributes = $_POST['RAStatisticForm'];
            $excelData = key_exists("excel",$_POST)?$_POST["excel"]:array();
            $model->downExcel($excelData);
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
	}

	public function actionDownYTD()
	{
        $model = new RAStatisticForm('view');
        if (isset($_POST['RAStatisticForm'])) {
            $model->attributes = $_POST['RAStatisticForm'];
            $model->downYTD();
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('RA02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('RA02');
	}
}
