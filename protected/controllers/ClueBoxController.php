<?php

class ClueBoxController extends Controller 
{
	public $function_id='CM01';

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
				'actions'=>array('new','edit','delete','save','batchDelete'),
				'expression'=>array('ClueBoxController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','assign','batchAssign'),
				'expression'=>array('ClueBoxController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionBatchDelete()
    {
        $model = new ClueBoxForm('delete');
        $index = isset($_GET['assign_id'])?$_GET['assign_id']:0;
        if ($model->validateSelect($index)) {
            $model->saveDelete();
            Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
        }
        $this->redirect(Yii::app()->createUrl('clueBox/index'));
    }

    public function actionBatchAssign()
    {
        $model = new ClueBoxForm('assign');
        $index = isset($_GET['assign_id'])?$_GET['assign_id']:0;
        if ($model->assignValidate($index)) {
            $model->saveAssign();
            Dialog::message(Yii::t('dialog','Information'), Yii::t('clue','Assign Done'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
        }
        $this->redirect(Yii::app()->createUrl('clueBox/index'));
    }

    public function actionAssign()
    {
        $model = new ClueBoxForm('assign');
        $index = isset($_GET['assign_id'])?$_GET['assign_id']:0;
        if (!empty($index)) {
            if ($model->assignValidate($index)) {
                $model->saveAssign();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('clue','Assign Done'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('clueBox/index'));
        } else {
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new ClueBoxList;
		if (isset($_POST['ClueBoxList'])) {
			$model->attributes = $_POST['ClueBoxList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_ClueBoxList']) && !empty($session['criteria_ClueBoxList'])) {
				$criteria = $session['criteria_ClueBoxList'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionSave()
	{
		if (isset($_POST['ClueBoxForm'])) {
			$model = new ClueBoxForm($_POST['ClueBoxForm']['scenario']);
            $model->clue_type = isset($_POST['ClueBoxForm']["clue_type"])?$_POST['ClueBoxForm']["clue_type"]:1;
			$model->attributes = $_POST['ClueBoxForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('clueBox/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new ClueBoxForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($city,$clue_type)
	{
		$model = new ClueBoxForm('new');
		$model->entry_date = date("Y/m/d");
		$model->city=$city;
		$model->clue_type=$clue_type;
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new ClueBoxForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ClueBoxForm('delete');
		if (isset($_POST['ClueBoxForm'])) {
		    $model->clue_type = isset($_POST['ClueBoxForm']["clue_type"])?$_POST['ClueBoxForm']["clue_type"]:1;
			$model->attributes = $_POST['ClueBoxForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('clueBox/edit',array('index'=>$model->id)));
			} else {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('clueBox/index'));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CM01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CM01');
	}
}
