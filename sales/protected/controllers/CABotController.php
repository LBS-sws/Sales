<?php

class CABotController extends Controller
{
	public $function_id='CA01';

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
				'expression'=>array('CABotController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel','updateHistory'),
				'expression'=>array('CABotController','allowReadOnly'),
			),
            array('allow',
                'actions'=>array('shift'),
                'expression'=>array('CABotController','allowShift'),
            ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionShift()
    {
        if (isset($_POST['CABotForm'])) {
            $model = new CABotForm($_POST['CABotForm']['scenario']);
            $model->attributes = $_POST['CABotForm'];
            if ($model->validateShift()) {
                $model->shiftData();
//				$model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('ka','Shift Done'));
                $this->redirect(Yii::app()->createUrl('cABot/view',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('cABot/edit',array('index'=>$model->id)));
            }
        }
    }

    public function actionAjaxCustomerName($group='',$id=0)
    {
        $model = new CABotForm();
        echo $model->AjaxCustomerName($group,$id);
    }

    //修改ka項目的操作記錄日期 id：歷史記錄的id
    public function actionUpdateHistory($id,$date){
	    if(!empty($date)){
            $bool = Yii::app()->db->createCommand()->update("sal_ca_bot_history",array(
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
            $list =CABotForm::getSupportUserList($city);
            echo CJSON::encode(array('status'=>1,'list'=>$list));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('cABot/index'));
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new CABotList;
        CABotForm::validateEmployee($model);
        if (isset($_POST['CABotList'])) {
            $model->attributes = $_POST['CABotList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['cABot_c01']) && !empty($session['cABot_c01'])) {
                $criteria = $session['cABot_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['CABotForm'])) {
			$model = new CABotForm($_POST['CABotForm']['scenario']);
			$model->attributes = $_POST['CABotForm'];
            CABotForm::validateEmployee($model);
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('cABot/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new CABotForm('view');
        CABotForm::validateEmployee($model);
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

	public function actionDownExcel()
	{
		$model = new CABotList();
        CABotForm::validateEmployee($model);
        if (isset($_POST['CABotList'])) {
            $model->attributes = $_POST['CABotList'];
        }
		$year=isset($_POST["year"])?$_POST["year"]:date("Y");
		$model->downExcel($year);
	}
	
	public function actionNew()
	{
		$model = new CABotForm('new');
        if(CABotForm::validateEmployee($model)){
            $model->kam_id = $model->employee_name." (".$model->employee_code.")";
            $model->apply_date=date("Y/m/d");
            $this->render('form',array('model'=>$model,));
        }else{
            $message = "该账号没有绑定员工无法新增RA项目";
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            $this->redirect(Yii::app()->createUrl('cABot/index'));
        }
	}
	
	public function actionEdit($index)
	{
		$model = new CABotForm('edit');
        CABotForm::validateEmployee($model);
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
		$model = new CABotForm('delete');
		if (isset($_POST['CABotForm'])) {
			$model->attributes = $_POST['CABotForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('cABot/edit',array('index'=>$model->id)));
			} else {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
		        $this->redirect(Yii::app()->createUrl('cABot/index'));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CA01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CA01');
	}

    public static function allowShift() {//转移
        return Yii::app()->user->validFunction('CN18');
    }
}
