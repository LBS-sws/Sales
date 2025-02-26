<?php

class ClubSalesController extends Controller
{
	public $function_id='HD04';
	
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
				'actions'=>array('updateDisplay'),
				'expression'=>array('ClubSalesController','allowDisplay'),
			),
			array('allow', 
				'actions'=>array('index'),
				'expression'=>array('ClubSalesController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($year=0,$month_type=1,$reset=false)
	{
		$model = new ClubSalesList();
		$model->clubSalesAll($year,$month_type,$reset);
		$this->render('index',array('model'=>$model));
	}

	public function actionUpdateDisplay()
	{
	    $id = isset($_GET["index"])?$_GET["index"]:0;
	    $key = isset($_GET["key"])?$_GET["key"]:"";
        $model = new ClubSalesList('new');
        if ($model->updateDisplay($id,$key)) {
            Dialog::message(Yii::t('dialog','Information'), Yii::t('club',$key).Yii::t('club','Update Done'));
        } else {
            $message = "数据异常，请刷新重试";
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
        }
        $this->redirect(Yii::app()->createUrl('clubSales/index',array('year'=>$model->year,'month_type'=>$model->month_type)));
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HD04');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HD04');
	}

	public static function allowDisplay() {
		return Yii::app()->user->validFunction('CN14');
	}
}
