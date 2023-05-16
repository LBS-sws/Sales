<?php

class ShiftController extends Controller
{
	public $function_id='HA03';

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
				'actions'=>array('new','edit','zhuan','save','zhuanone','shiftBool'),
				'expression'=>array('ShiftController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ShiftController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
        $model = new ShiftList;
        if (isset($_POST['ShiftList'])) {
            $model->attributes = $_POST['ShiftList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $saleman=$model->saleman();
        $this->render('index',array('model'=>$model,'saleman'=>$saleman));
	}


	public function actionZhuan()
	{
		$model = new ShiftForm('zhuan');
		$arr=$_POST['ShiftList'];
		if (isset($arr)) {
			if (empty($arr['visit_shift'])||empty($arr['id'])){
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','Please check the assigned person and information'));
				$this->redirect(Yii::app()->createUrl('shift/index'));
			} else {
                $saleman=$model->groupShift($arr);
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Data transfer successful'));
				$this->redirect(Yii::app()->createUrl('shift/index'));
			}
		}
	}

	public function actionShiftBool()
	{//取消轉移
		$model = new ShiftForm('zhuan');
		$arr=$_POST['ShiftList'];
		if (isset($arr)) {
			if (empty($arr['id'])){
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','Please check the assigned person and information'));
				$this->redirect(Yii::app()->createUrl('shift/index'));
			} else {
                $model->updateShiftBool($arr);
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Cancel Done'));
				$this->redirect(Yii::app()->createUrl('shift/index'));
			}
		}
	}

    public function actionZhuanOne()
    {
        $model = new ShiftForm('zhuan');
        $arr=$_POST['ShiftForm'];
 //       print_r($arr);
        if (isset($arr)) {
            if (empty($arr['visit_shift'])||empty($arr['id'])){
                Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','Please check the assigned person and information'));
                $this->redirect(Yii::app()->createUrl('shift/index'));
            } else {
                $saleman=$model->groupShift($arr);
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Data transfer successful'));
                $this->redirect(Yii::app()->createUrl('shift/index'));
            }
        }
    }
//	public function actionSave()
//	{
//		if (isset($_POST['VisitobjForm'])) {
//			$model = new VisitobjForm($_POST['VisitobjForm']['scenario']);
//			$model->attributes = $_POST['VisitobjForm'];
//			if ($model->validate()) {
//				$model->saveData();
////				$model->scenario = 'edit';
//				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
//				$this->redirect(Yii::app()->createUrl('visitobj/edit',array('index'=>$model->id)));
//			} else {
//				$message = CHtml::errorSummary($model);
//				Dialog::message(Yii::t('dialog','Validation Message'), $message);
//				$this->render('form',array('model'=>$model,));
//			}
//		}
//	}
//
	public function actionView($index)
	{
		$model = new ShiftForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
//		    print_r('<pre/>');
//            print_r($model);
            $saleman=$model->saleman();
			$this->render('form',array('model'=>$model,'saleman'=>$saleman));
		}
	}
//
//	public function actionNew()
//	{
//		$model = new VisitobjForm('new');
//		$this->render('form',array('model'=>$model,));
//	}
//
//	public function actionEdit($index)
//	{
//		$model = new VisitobjForm('edit');
//		if (!$model->retrieveData($index)) {
//			throw new CHttpException(404,'The requested page does not exist.');
//		} else {
//			$this->render('form',array('model'=>$model,));
//		}
//	}
//
//	public function actionDelete()
//	{
//		$model = new VisitobjForm('delete');
//		if (isset($_POST['VisitobjForm'])) {
//			$model->attributes = $_POST['VisitobjForm'];
//			if ($model->isOccupied($model->id)) {
//				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
//				$this->redirect(Yii::app()->createUrl('visitobj/edit',array('index'=>$model->id)));
//			} else {
//				$model->saveData();
//				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
//		$this->redirect(Yii::app()->createUrl('visitobj/index'));
//			}
//		}
//	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HA03');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HA03');
	}
}
