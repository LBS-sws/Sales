<?php

class KABotController extends Controller 
{
	public $function_id='KA01';

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
				'actions'=>array('new','edit','delete','save','ajaxSupportUser'),
				'expression'=>array('KABotController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel'),
				'expression'=>array('KABotController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    //区域支持者的異步請求
    public function actionAjaxSupportUser(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $city = key_exists("city",$_POST)?$_POST["city"]:0;
            $list =KABotForm::getSupportUserList($city);
            echo CJSON::encode(array('status'=>1,'list'=>$list));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('kABot/index'));
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new KABotList;
        KABotForm::validateEmployee($model);
        if (isset($_POST['KABotList'])) {
            $model->attributes = $_POST['KABotList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['kABot_c01']) && !empty($session['kABot_c01'])) {
                $criteria = $session['kABot_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['KABotForm'])) {
			$model = new KABotForm($_POST['KABotForm']['scenario']);
			$model->attributes = $_POST['KABotForm'];
            KABotForm::validateEmployee($model);
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('kABot/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new KABotForm('view');
        KABotForm::validateEmployee($model);
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

	public function actionDownExcel()
	{
		$model = new KABotList();
        KABotForm::validateEmployee($model);
		$year=isset($_POST["year"])?$_POST["year"]:date("Y");
		$model->downExcel($year);
	}
	
	public function actionNew()
	{
		$model = new KABotForm('new');
        if(KABotForm::validateEmployee($model)){
            $model->kam_id = $model->employee_name." (".$model->employee_code.")";
            $model->apply_date=date("Y-m-d");
            $this->render('form',array('model'=>$model,));
        }else{
            $message = "该账号没有绑定员工无法新增KA项目";
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            $this->redirect(Yii::app()->createUrl('kABot/index'));
        }
	}
	
	public function actionEdit($index)
	{
		$model = new KABotForm('edit');
        KABotForm::validateEmployee($model);
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new KABotForm('delete');
		if (isset($_POST['KABotForm'])) {
			$model->attributes = $_POST['KABotForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('kABot/edit',array('index'=>$model->id)));
			} else {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
		        $this->redirect(Yii::app()->createUrl('kABot/index'));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('KA01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('KA01');
	}
}
