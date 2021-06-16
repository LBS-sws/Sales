<?php

class TestController extends Controller
{
	public function filters()
	{
		return array(
			'enforceRegisteredStation - error', //apply station checking, except error page
			'enforceSessionExpiration - error,login,logout', 
			'enforceNoConcurrentLogin - error,login,logout',
			'accessControl - error,login,index,home', // perform access control for CRUD operations
		);
	}

	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Declares class-based actions.
	 */

	 public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}


	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
        $this->render('index');
//

    }
    public function actionTest01(){
        $firstDay = date("2021-06-04");
        $arr['start_dt'] = date("Y-m-d", strtotime("$firstDay - 6 day"));
        $arr['end_dt'] = $firstDay;
        $arr['sale'] =["test","test1","testuser"];
        $arr['sort'] = 'singular';
        $arr_email = ReportVisitForm::Summary($arr);
        var_dump($arr_email);
        die();
    }

}