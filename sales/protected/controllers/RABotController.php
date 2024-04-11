<?php

class RABotController extends Controller
{
	public $function_id='RA01';

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
				'actions'=>array('new','edit','delete','save','ajaxSupportUser','AjaxCustomerName'),
				'expression'=>array('RABotController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel','updateHistory'),
				'expression'=>array('RABotController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionAjaxCustomerName($group='',$id=0)
    {
        $model = new RABotForm();
        echo $model->AjaxCustomerName($group,$id);
    }

    //修改ka項目的操作記錄日期 id：歷史記錄的id
    public function actionUpdateHistory($id,$date){
	    if(!empty($date)){
            $bool = Yii::app()->db->createCommand()->update("sal_ra_bot_history",array(
                "lcd"=>$date
            ),"id=:id",array(":id"=>$id));
            echo $bool;
        }else{
	        echo "date error:".$date;
        }
    }

    //区域支持者的異步請求
    public function actionAjaxSupportUser(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $city = key_exists("city",$_POST)?$_POST["city"]:0;
            $list =RABotForm::getSupportUserList($city);
            echo CJSON::encode(array('status'=>1,'list'=>$list));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('rABot/index'));
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new RABotList;
        RABotForm::validateEmployee($model);
        if (isset($_POST['RABotList'])) {
            $model->attributes = $_POST['RABotList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['rABot_c01']) && !empty($session['rABot_c01'])) {
                $criteria = $session['rABot_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['RABotForm'])) {
			$model = new RABotForm($_POST['RABotForm']['scenario']);
			$model->attributes = $_POST['RABotForm'];
            RABotForm::validateEmployee($model);
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('rABot/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new RABotForm('view');
        RABotForm::validateEmployee($model);
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

	public function actionDownExcel()
	{
		$model = new RABotList();
        RABotForm::validateEmployee($model);
        if (isset($_POST['RABotList'])) {
            $model->attributes = $_POST['RABotList'];
        }
		$year=isset($_POST["year"])?$_POST["year"]:date("Y");
		$model->downExcel($year);
	}
	
	public function actionNew()
	{
		$model = new RABotForm('new');
        if(RABotForm::validateEmployee($model)){
            $model->kam_id = $model->employee_name." (".$model->employee_code.")";
            $model->apply_date=date("Y/m/d");
            $this->render('form',array('model'=>$model,));
        }else{
            $message = "该账号没有绑定员工无法新增RA项目";
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            $this->redirect(Yii::app()->createUrl('rABot/index'));
        }
	}
	
	public function actionEdit($index)
	{
		$model = new RABotForm('edit');
        RABotForm::validateEmployee($model);
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			if($model->employee_id!=$model->kam_id){
				$model->scenario="view";
			}
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new RABotForm('delete');
		if (isset($_POST['RABotForm'])) {
			$model->attributes = $_POST['RABotForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('rABot/edit',array('index'=>$model->id)));
			} else {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
		        $this->redirect(Yii::app()->createUrl('rABot/index'));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('RA01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('RA01');
	}
}