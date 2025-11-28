<?php

class ClientHeadController extends Controller 
{
	public $function_id='CM10';

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
				'actions'=>array('new','edit','delete','save','backClientBox'),
				'expression'=>array('ClientHeadController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ClientHeadController','allowReadOnly'),
			),
			array('allow',
				'actions'=>array('new'),
				'expression'=>array('ClientHeadController','allowNew'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ClientHeadList;
        $session = Yii::app()->session;
        $session["clueStoreDetail"]=4;//门店入口改成4
        $session["clueDetail"]="client";//详情的返回
		if (isset($_POST['ClientHeadList'])) {
			$model->attributes = $_POST['ClientHeadList'];
		} else {
			if (isset($session['criteria_ClientHeadList']) && !empty($session['criteria_ClientHeadList'])) {
				$criteria = $session['criteria_ClientHeadList'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionBackClientBox()
	{
		if (isset($_POST['ClientHeadForm'])) {
			$model = new ClientHeadForm('back');
            $model->attributes = $_POST['ClientHeadForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('clue','Back Done'));
				$this->redirect(Yii::app()->createUrl('clientHead/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionSave()
	{
		if (isset($_POST['ClientHeadForm'])) {
			$model = new ClientHeadForm($_POST['ClientHeadForm']['scenario']);
            $model->clue_type = isset($_POST['ClientHeadForm']["clue_type"])?$_POST['ClientHeadForm']["clue_type"]:1;
			$model->attributes = $_POST['ClientHeadForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('clientHead/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index,$service_id=0,$addStaff=0)
	{
		$model = new ClientHeadForm('view');
        if($addStaff==1){
            ClueForm::addExtraUserByMy($index);
        }
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
            $session = Yii::app()->session;
            $session["clueTable"]=2;//线索
            $session["clueStoreDetail"]=5;//门店入口改成5
            $clueDetail = isset($session["clueDetail"])?$session["clueDetail"]:"client";
		    $model->setClueServiceID($service_id);
			$this->render('detail',array('model'=>$model,'clueDetail'=>$clueDetail));
		}
	}
	
	public function actionNew($city,$clue_type)
	{
		$model = new ClientHeadForm('new');
        if(empty($city)||empty($clue_type)){
            Dialog::message(Yii::t('dialog','Warning'),"业务管理单元或线索类别不能为空");
            $this->redirect(Yii::app()->createUrl('clientHead/index'));
        }else{
            $model->entry_date = date("Y/m/d");
            $model->city=$city;
            $model->clue_type=$clue_type;
            $model->rec_type=1;
            $model->rec_employee_id=CGetName::getEmployeeIDByMy();
            $model->login_employee_id=$model->rec_employee_id;
            $model->yewudalei=$clue_type==1?1:2;
            $this->render('form',array('model'=>$model,));
        }
	}
	
	public function actionEdit($index)
	{
		$model = new ClientHeadForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ClientHeadForm('delete');
		if (isset($_POST['ClientHeadForm'])) {
		    $model->clue_type = isset($_POST['ClientHeadForm']["clue_type"])?$_POST['ClientHeadForm']["clue_type"]:1;
			$model->attributes = $_POST['ClientHeadForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('clientHead/edit',array('index'=>$model->id)));
			} else {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('clientHead/index'));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CM10');
	}

	public static function allowNew() {
		return Yii::app()->user->validRWFunction('CM10');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CM10');
	}
}
