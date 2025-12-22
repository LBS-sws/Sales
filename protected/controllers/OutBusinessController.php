<?php

class OutBusinessController extends Controller
{
	public $function_id='G39';
	
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
				'expression'=>array('OutBusinessController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel','ajaxDetail'),
				'expression'=>array('OutBusinessController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    //详情列表的異步請求
    public function actionAjaxDetail(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $model = new OutBusinessTable();
            $html =$model->ajaxDetailForHtml();
            echo CJSON::encode(array('status'=>1,'html'=>$html));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('outBusiness/index'));
        }
    }

    public function actionDownExcel()
    {
        $model = new OutBusinessForm('view');
        if (isset($_POST['OutBusinessForm'])) {
            $model->attributes = $_POST['OutBusinessForm'];
            $excelData = key_exists("excel",$_POST)?$_POST["excel"]:array();
            $model->downExcel($excelData);
        }else{
            $model->setScenario("index");
            $this->render('index',array('model'=>$model));
        }
    }

	public function actionIndex()
	{
		$model = new OutBusinessForm('index');
        $session = Yii::app()->session;
        if (isset($session['outBusiness_c01']) && !empty($session['outBusiness_c01'])) {
            $criteria = $session['outBusiness_c01'];
            $model->setCriteria($criteria);
        }else{
            $model->search_year = date("Y");
            $model->search_month = date("n");
            $model->search_month_end = $model->search_month;
            $model->search_start_date = date("Y/m/01");
            $model->search_end_date = date("Y/m/d");
            $i = ceil($model->search_month/3);//向上取整
            $model->search_quarter = 3*$i-2;
        }
		$this->render('index',array('model'=>$model));
	}

	public function actionView()
	{
	    set_time_limit(0);
        $model = new OutBusinessForm('view');
        if (isset($_POST['OutBusinessForm'])) {
            $model->attributes = $_POST['OutBusinessForm'];
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
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('G39');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('G39');
	}
}
