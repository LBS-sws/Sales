<?php

class MarketAreaController extends Controller 
{
	public $function_id='MT02';

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
				'actions'=>array('new','edit','back','reject','success','save','assign','fileupload','fileremove'),
				'expression'=>array('MarketAreaController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('MarketAreaController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    //分配
    public function actionAssign()
    {
        if (isset($_POST['assign_type'])) {
            $model = new MarketAreaForm();
            $list = $model->validateAssign();
            if ($list["bool"]) {
                $model->saveAssign($list["data"]);
                Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Assign Done'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            if($_POST['assign_type']!="form"){
                $this->redirect(Yii::app()->createUrl('marketArea/index'));
            }else{
                $this->redirect(Yii::app()->createUrl('marketArea/edit',array('index'=>$model->id)));
            }
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new MarketAreaList;
        if (isset($_POST['MarketAreaList'])) {
            $model->attributes = $_POST['MarketAreaList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['marketArea_c01']) && !empty($session['marketArea_c01'])) {
                $criteria = $session['marketArea_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['MarketAreaForm'])) {
			$model = new MarketAreaForm($_POST['MarketAreaForm']['scenario']);
			$model->attributes = $_POST['MarketAreaForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('marketArea/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new MarketAreaForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new MarketAreaForm('new');
        $this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new MarketAreaForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionBack()
	{
		$model = new MarketAreaForm('back');
        $list = $model->validateBack();
        if ($list["bool"]) {
            $model->saveBackAll($list["data"]);
            Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Back'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Warning'), $message);
        }
        if($list['typeNum']==1){
            $this->redirect(Yii::app()->createUrl('marketArea/edit',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('marketArea/index'));
        }
	}

    public function actionReject()
    {
        $model = new MarketAreaForm('reject');
        $list = $model->validateReject();
        if ($list["bool"]) {
            $model->saveRejectAll($list["data"]);
            Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Reject'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Warning'), $message);
        }
        if($list['typeNum']==1){
            $this->redirect(Yii::app()->createUrl('marketArea/edit',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('marketArea/index'));
        }
    }

    public function actionSuccess()
    {
        $model = new MarketAreaForm('success');
        $list = $model->validateSuccess();
        if ($list["bool"]) {
            $model->saveSuccessAll($list["data"]);
            Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Success'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Warning'), $message);
        }
        if($list['typeNum']==1){
            $this->redirect(Yii::app()->createUrl('marketArea/edit',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('marketArea/index'));
        }
    }

    public function actionFileupload($doctype) {
        $model = new MarketAreaForm();
        if (isset($_POST['MarketAreaForm'])) {
            $model->attributes = $_POST['MarketAreaForm'];

            $id = ($_POST['MarketAreaForm']['scenario']=='new') ? 0 : $model->id;
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
        $model = new MarketAreaForm();
        if (isset($_POST['MarketAreaForm'])) {
            $model->attributes = $_POST['MarketAreaForm'];
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
                $docman = new DocMan($doctype,$docId,'MarketAreaForm');
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
		return Yii::app()->user->validRWFunction('MT02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('MT02');
	}
}
