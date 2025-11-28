<?php

class SiteController extends Controller
{
	public function filters()
	{
		return array(
			'enforceRegisteredStation - error', //apply station checking, except error page
			'enforceSessionExpiration - error,login,logout', 
			'enforceNoConcurrentLogin - error,login,logout',
			'accessControl - error,login,mhView,loginOld,index,home,resetloginpassword', // perform access control for CRUD operations
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
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		if (Yii::app()->user->isGuest)
			$this->actionLogin();
		else {
			$uname = Yii::app()->user->name; 
			Yii::app()->session['system'] = Yii::app()->params['systemId'];
			Yii::app()->user->saveUserOption($uname, 'system', Yii::app()->params['systemId']);
            General::includeDrsSysBlock();
			$obj = new SysBlock();
			$blkmsg = $obj->getBlockMessage(Yii::app()->params['systemId']);
			if ($blkmsg!==false) Dialog::message(Yii::t('dialog','Advice'), $blkmsg);
			$this->render('index');
		}
	}

	public function actionHome($url='') {
		if (Yii::app()->user->isGuest)
			$this->actionLogin();
		else {
			$uname = Yii::app()->user->name; 
			Yii::app()->session['system'] = Yii::app()->params['systemId'];
			Yii::app()->user->saveUserOption($uname, 'system', Yii::app()->params['systemId']);
			$this->render('index',array('url'=>$url,));
		}
	}
	
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError() {
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else {
				if ($error['code']==999) {
					$model=new LoginForm;
					Dialog::message('Warning Message', $error['message']);
					$this->layout = "main_nm";
					$this->render('login',array('model'=>$model));
				} else {
					$this->render('error', $error);
				}
			}
		}
	}


    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        //$url = Yii::app()->user->returnUrl;
        $lbsUrl = Yii::app()->getBaseUrl(true);
        if(isset($_GET["user_id"])){
            $lbsUrl.="?user_id=".$_GET["user_id"];//多用户登录
        }
        $muUrl = Yii::app()->params['MHCurlRootURL']."/cas/login?service=".$lbsUrl;
        $this->redirect($muUrl);
    }

	/**
	 * Displays the login page
	 */
	public function actionMhView($index='cont_1',$token=''){
        $this->layout = 'mh_main';
	    $list = explode("_",$index);
	    if(count($list)==2){
            $id = $list[1];
            if(!empty($token)&&Yii::app()->user->isGuest){
                //https://uat-eip.lbsapps.cn/eipapi/openApi/auth/v1/getUserByToken?token=fsdfjlkjfsdajkjkdjkd

                $url = Yii::app()->params['MHCurlFlowURL']."/openApi/auth/v1/getUserByToken?";
                $queryArr = array(
                    "token"=>$token
                );
                $url.= http_build_query($queryArr);
                $result = file_get_contents($url);
                $resultJson = json_decode($result,true);
                //{"state":true,"message":"解析成功","value":{"id":"618984488","account":"400921","fullname":"沈超","userNumber":"400921","email":"shenchao@lbsgroup.com.cn","mobile":"17722039238","address":"珠海市香洲区上冲村中兴街10号","sex":"男","photo":"","status":1,"idCard":"360428199211181659","phone":"","birthday":"1992-11-18","entryDate":"2017-06-01","education":"","updateTime":"2025-09-19 03:45:27","isDelete":"0","version":1,"from":"BEISEN","tenantId":"-1","pOIdEmpAdmin":"622554381"},"code":200}
                if (isset($resultJson["state"])&&$resultJson["state"]){
                    $staffCode = $resultJson["value"]["account"];
                    $suffix = Yii::app()->params['envSuffix'];
                    $staffRow = Yii::app()->db->createCommand()->select("a.user_id")
                        ->from("hr{$suffix}.hr_binding a")
                        ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                        ->where("b.code=:code and b.staff_status!=-1",array(":code"=>$staffCode))->queryRow();
                    if($staffRow){
                        $model=new LoginForm;
                        $model->MHLogin($staffCode,$staffRow["user_id"]);
                    }
                }
            }
            switch ($list[0]){
                case "cont":
                    $model = new ContHeadForm('view');
                    $model->retrieveData($id);
                    $model->validateClueServiceIDByView("clue_service_id",'');
                    $model->validateID("id",'');
                    $sealBool = $model->validateSeal();
                    if($model->hasErrors()){
                        $message = CHtml::errorSummary($model);
                        Dialog::message(Yii::t('dialog','Validation Message'), $message);
                    }
                    $this->render("//contHead/view",array('model'=>$model,'token'=>$token,'seal'=>$sealBool["status"]==200));
                    break;
                case "rpt":
                    $model = new ClueRptForm('view');
                    $model->retrieveData($id);
                    $model->validateClueServiceID("clue_service_id","");
                    $model->getAllFileJson();
                    if($model->hasErrors()){
                        $message = CHtml::errorSummary($model);
                        Dialog::message(Yii::t('dialog','Validation Message'), $message);
                    }
                    $this->render("//clueRpt/view",array('model'=>$model,'token'=>$token));
                    break;
                case "pro":
                    $model = new ContProForm('view');
                    $model->retrieveData($id);
                    $model->validateContIDByView("cont_id",'');
                    $model->validateID("id",'');
                    $sealBool = $model->validateSeal();
                    if($model->hasErrors()){
                        $message = CHtml::errorSummary($model);
                        Dialog::message(Yii::t('dialog','Validation Message'), $message);
                    }
                    $model->setCompareModelByAudit();
                    $this->render("//contPro/view",array('model'=>$model,'token'=>$token,'seal'=>$sealBool["status"]==200));
                    break;
                case "virPro":
                    $model = new VirtualBatchForm('view');
                    $model->retrieveData($id);
                    $model->validateVirIDText("id",'');
                    $sealBool = $model->validateSeal();
                    if($model->hasErrors()){
                        $message = CHtml::errorSummary($model);
                        Dialog::message(Yii::t('dialog','Validation Message'), $message);
                    }
                    $model->setCompareModelByAudit();
                    $this->render('//virtualBatch/view',array('model'=>$model,'token'=>$token,'seal'=>$sealBool["status"]==200));
                    break;
                case "call":
                    $model = new CallServiceForm('view');
                    $model->retrieveData($id);
                    $model->showCallView();
                    $this->render('//callService/view',array('model'=>$model,'token'=>$token));
                    break;
                default:
                    throw new CHttpException(404,'The requested page does not exist.');
            }
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

	/**
	 * Displays the login page
	 */
	public function actionLoginOld()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
			{
				Yii::app()->user->setUrlAfterLogin();
				$this->redirect(Yii::app()->user->returnUrl);
			}
			else
			{
                $errorCode = $model->errorCode;
                $message=CHtml::errorSummary($model);
                $Validation_Message = $errorCode == UserIdentity::ERROR_RESET_PASSWORD ? $_POST['LoginForm']['username'] : 'Validation Message';
                Dialog::message($Validation_Message, $message,$errorCode);
			}
		}
		// display the login form
		$this->layout = "main_nm";
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
        Yii::app()->user->logout();
        $lbsUrl = Yii::app()->getBaseUrl(true)."/site/login";
        $url = Yii::app()->params['MHCurlRootURL']."/cas/logout?service=".$lbsUrl;
        //$result = file_get_contents($url);//单点登出门户网站
        $this->redirect($url);
        //$this->redirect(Yii::app()->homeUrl);
	}

	public function actionPassword()
	{
		$model=new PasswordForm;

		// collect user input data
		if(isset($_POST['PasswordForm']))
		{
			$model->attributes=$_POST['PasswordForm'];
			if($model->validate())
			{
				$model->save();
				Dialog::message('Info', Yii::t('dialog','Password changed'));
				$this->redirect(Yii::app()->baseUrl);
			}
			else
			{
				$message=CHtml::errorSummary($model);
				Dialog::message('Validation Message', $message);
			}
		}
		// display the login form
		$this->render('password',array('model'=>$model));
	}

/*	
	public function actionLanguage($locale)
	{
		$session = Yii::app()->session;
		$session['lang'] = $locale;
		Yii::app()->language = $locale;
		$uname = Yii::app()->user->name; 
		Yii::app()->user->saveUserOption($uname, 'lang', $locale);
		$this->actionHome();
	}
*/
	public function actionLanguage() {
		$model=new LanguageForm;

		// collect user input data
		if(isset($_POST['LanguageForm'])) {
			$model->attributes=$_POST['LanguageForm'];
			
			$session = Yii::app()->session;
			$session['lang'] = $model->language;
			Yii::app()->language = $model->language;
			$uname = Yii::app()->user->name; 
			Yii::app()->user->saveUserOption($uname, 'lang', $model->language);
			
			$this->redirect(Yii::app()->baseUrl);
		}
		// display the login form
		$model->language = Yii::app()->language;
		$this->render('language',array('model'=>$model));
	}
	
	public function actionNotifyopt() {
		$model=new NotifyoptForm;

		if(isset($_POST['NotifyoptForm'])) {
			$model->attributes=$_POST['NotifyoptForm'];
			$model->save();
			Dialog::message('Info', Yii::t('dialog','Option changed'));
		}
		$model->retrieveData();
		$this->render('notifyopt',array('model'=>$model));
	}

    public function actionResetLoginPassword()
    {
        $model=new ResetPasswordForm;
        if(isset($_POST['ajax']) && $_POST['ajax']==='reset-login-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        // collect user input data
        if(isset($_POST['ResetPasswordForm']))
        {
            $model->attributes=$_POST['ResetPasswordForm'];
            if($model->validate())
            {
                $result = (new User())->resetPassword($_POST['ResetPasswordForm']['username'],$_POST['ResetPasswordForm']['new_password']);
                if($result['status']) $this->redirect(Yii::app()->user->loginUrl);
                Dialog::message('Validation Message', $result['msg']);
            }
            else
            {
                $message=CHtml::errorSummary($model);
                Dialog::message('Validation Message', $message);
            }
        }
//        print_r($_POST);exit;
        $this->layout = "main_reset_login";
        $this->render('resetloginpassword',array('model'=>$model));
    }
}