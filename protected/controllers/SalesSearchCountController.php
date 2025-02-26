<?php

class SalesSearchCountController extends Controller
{
	public $function_id='HK07';

	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
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
				'actions'=>array('index','onlySales','allSales','allCity'),
				'expression'=>array('SalesSearchCountController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
       $model = new SalesSearchCountList;
		if (isset($_POST['SalesSearchCountList'])) {
			$model->attributes = $_POST['SalesSearchCountList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionOnlySales()
	{
       $model = new SalesSearchCountList;
		if (isset($_POST['SalesSearchCountList'])) {
			$model->attributes = $_POST['SalesSearchCountList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['onlySales_01']) && !empty($session['onlySales_01'])) {
				$criteria = $session['onlySales_01'];
				$model->setCriteria($criteria);
			}
		}
        $data = $model->onlySales();
		$this->render('onlySales',array('model'=>$model,'chartData'=>$data));
	}

	public function actionAllSales()
	{
       $model = new SalesSearchCountList;
		if (isset($_POST['SalesSearchCountList'])) {
			$model->attributes = $_POST['SalesSearchCountList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['allSales_01']) && !empty($session['allSales_01'])) {
				$criteria = $session['allSales_01'];
				$model->setCriteria($criteria);
			}
		}
        $data = $model->allSales();
		$this->render('allSales',array('model'=>$model,'chartData'=>$data));
	}

	public function actionAllCity()
	{
       $model = new SalesSearchCountList;
		if (isset($_POST['SalesSearchCountList'])) {
			$model->attributes = $_POST['SalesSearchCountList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['allCity_01']) && !empty($session['allCity_01'])) {
				$criteria = $session['allCity_01'];
				$model->setCriteria($criteria);
			}
		}
        $data = $model->allCity();
		$this->render('allCity',array('model'=>$model,'chartData'=>$data));
	}

	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HK07');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HK07');
	}
}
