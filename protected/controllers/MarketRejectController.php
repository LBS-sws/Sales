<?php

class MarketRejectController extends Controller 
{
	public $function_id='MT04';

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
				'actions'=>array('edit','back','readyAll','fileupload','fileremove'),
				'expression'=>array('MarketRejectController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('MarketRejectController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new MarketRejectList;
        if (isset($_POST['MarketRejectList'])) {
            $model->attributes = $_POST['MarketRejectList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['marketReject_c01']) && !empty($session['marketReject_c01'])) {
                $criteria = $session['marketReject_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
	}

	public function actionView($index)
	{
		$model = new MarketRejectForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
	}
	
	public function actionEdit($index)
	{
        $model = new MarketRejectForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
	}
	
	public function actionBack()
	{
		$model = new MarketRejectForm('back');
        if (isset($_POST['MarketRejectForm'])) {
            $model->attributes = $_POST['MarketRejectForm'];
            if ($model->isOccupied()) {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Warning'), $message);
                $this->redirect(Yii::app()->createUrl('marketReject/edit',array('index'=>$model->id)));
            } else {
                $model->saveBack();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Back'));
                $this->redirect(Yii::app()->createUrl('marketReject/index'));
            }
        }
	}

	public function actionReadyAll()
	{
		$model = new MarketRejectForm('ready');
        $list = $model->validateReadyAll();
        if ($list["bool"]) {
            $model->saveReadyAll($list["data"]);
            Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Ready Done'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
        }
        $this->redirect(Yii::app()->createUrl('marketReject/index'));
	}

    public function actionFileupload($doctype) {
        $model = new MarketRejectForm();
        if (isset($_POST['MarketRejectForm'])) {
            $model->attributes = $_POST['MarketRejectForm'];

            $id = ($_POST['MarketRejectForm']['scenario']=='new') ? 0 : $model->id;
            $docman = new DocMan($doctype,$id,get_class($model));
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
            $docman->fileUpload();
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileRemove($doctype) {
        $model = new MarketRejectForm();
        if (isset($_POST['MarketRejectForm'])) {
            $model->attributes = $_POST['MarketRejectForm'];
            $docman = new DocMan($doctype,$model->id,get_class($model));
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            $docman->fileRemove($model->removeFileId[strtolower($doctype)]);
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select id from sal_market where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $citylist = Yii::app()->user->city_allow();
            if (strpos($citylist, $row['city']) !== false) {
                $docman = new DocMan($doctype,$docId,'MarketRejectForm');
                $docman->masterId = $mastId;
                $docman->fileDownload($fileId);
            } else {
                throw new CHttpException(404,'Access right not match.');
            }
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }


    public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('MT04');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('MT04');
	}
}
