<?php

class SalesSearchCustomerController extends Controller
{
	public $function_id='HK06';

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
/*		
			array('allow', 
				'actions'=>array('new','edit','delete','save'),
				'expression'=>array('CustomerController','allowReadWrite'),
			),
*/
			array('allow', 
				'actions'=>array('index','ajaxCompanyName'),
				'expression'=>array('SalesSearchCustomerController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionAjaxCompanyName($group='')
	{
        $model = new SalesSearchCustomerList;
        echo $model->AjaxCompanyName($group);
    }

	public function actionIndex($pageNum=1)
	{
		$model = new SalesSearchCustomerList;
		if (isset($_POST['SalesSearchCustomerList'])) {
			$model->attributes = $_POST['SalesSearchCustomerList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
				$model->setCriteria($criteria);
			}
		}
		if($model->getEmployee()){
            $model->determinePageNum($pageNum);
            $model->retrieveDataByPage($model->pageNum);
            $message = CHtml::errorSummary($model);
            if(!empty($message)){
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->render('index',array('model'=>$model));
        }else{
            throw new CHttpException(404,'您的账号未绑定员工，请与管理员联系');
        }
	}

	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HK06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HK06');
	}
}
