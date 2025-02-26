<?php

class CAStatisticController extends Controller
{
	public $function_id='CA02';
	
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
				'expression'=>array('CAStatisticController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel','downYTD','ajaxDetail'),
				'expression'=>array('CAStatisticController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    //详情列表的異步請求
    public function actionAjaxDetail(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $model = new CAStatisticForm();
            $html =$model->ajaxDetailForHtml();
            echo CJSON::encode(array('status'=>1,'html'=>$html));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('RankingMonth/index'));
        }
    }

	public function actionIndex()
	{
		$model = new CAStatisticForm('index');
        $session = Yii::app()->session;
        if (isset($session['cAStatistic_c01']) && !empty($session['cAStatistic_c01'])) {
            $criteria = $session['cAStatistic_c01'];
            $model->setCriteria($criteria);
        }else{
            $model->search_year = date("Y");
            $model->search_month = date("n");
        }
		$this->render('index',array('model'=>$model));
	}

	public function actionView()
	{
        $model = new CAStatisticForm('view');
        KABotForm::validateEmployee($model);
        if (isset($_POST['CAStatisticForm'])) {
            $model->attributes = $_POST['CAStatisticForm'];
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
        $model = new CAStatisticForm('view');
        if (isset($_POST['CAStatisticForm'])) {
            $model->attributes = $_POST['CAStatisticForm'];
            $excelData = key_exists("excel",$_POST)?$_POST["excel"]:array();
            $model->downExcel($excelData);
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
	}

	public function actionDownYTD()
	{
        $model = new CAStatisticForm('view');
        if (isset($_POST['CAStatisticForm'])) {
            $model->attributes = $_POST['CAStatisticForm'];
            $model->downYTD();
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CA02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CA02');
	}
}
