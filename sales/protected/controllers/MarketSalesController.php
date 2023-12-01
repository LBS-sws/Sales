<?php

class MarketSalesController extends Controller 
{
	public $function_id='MT03';

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
				'actions'=>array('edit','back','save','reject','success','fileupload','fileremove'),
				'expression'=>array('MarketSalesController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('MarketSalesController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new MarketSalesList;
        if (isset($_POST['MarketSalesList'])) {
            $model->attributes = $_POST['MarketSalesList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['marketSales_c01']) && !empty($session['marketSales_c01'])) {
                $criteria = $session['marketSales_c01'];
                $model->setCriteria($criteria);
            }
        }
        if(MarketFun::validateEmployee($model)){
            $model->determinePageNum($pageNum);
            $model->retrieveDataByPage($model->pageNum);
            $this->render('index',array('model'=>$model));
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}


	public function actionSave()
	{
		if (isset($_POST['MarketSalesForm'])) {
			$model = new MarketSalesForm($_POST['MarketSalesForm']['scenario']);
			$model->attributes = $_POST['MarketSalesForm'];
            if(MarketFun::validateEmployee($model)){
                if ($model->validate()) {
                    $model->saveData();
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                    $this->redirect(Yii::app()->createUrl('marketSales/edit',array('index'=>$model->id)));
                } else {
                    $message = CHtml::errorSummary($model);
                    Dialog::message(Yii::t('dialog','Validation Message'), $message);
                    $this->render('form',array('model'=>$model,));
                }
            }else{
                throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
            }
		}
	}

	public function actionView($index)
	{
		$model = new MarketSalesForm('view');
        if(MarketFun::validateEmployee($model)){
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionEdit($index)
	{
        $model = new MarketSalesForm('edit');
        if(MarketFun::validateEmployee($model)){
            if (!$model->retrieveData($index)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}
	
	public function actionBack()
	{
		$model = new MarketSalesForm('back');
        if(MarketFun::validateEmployee($model)){
            $list = $model->validateBack();
            if ($list["bool"]) {
                $model->saveBackAll($list["data"]);
                Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Back'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Warning'), $message);
            }
            if($list['typeNum']==1){
                $this->redirect(Yii::app()->createUrl('marketSales/edit',array('index'=>$model->id)));
            }else{
                $this->redirect(Yii::app()->createUrl('marketSales/index'));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
	}

    public function actionReject()
    {
        $model = new MarketSalesForm('reject');
        if(MarketFun::validateEmployee($model)){
            $list = $model->validateReject();
            if ($list["bool"]) {
                $model->saveRejectAll($list["data"]);
                Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Reject'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Warning'), $message);
            }
            if($list['typeNum']==1){
                $this->redirect(Yii::app()->createUrl('marketSales/edit',array('index'=>$model->id)));
            }else{
                $this->redirect(Yii::app()->createUrl('marketSales/index'));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
    }

    public function actionSuccess()
    {
        $model = new MarketSalesForm('success');
        if(MarketFun::validateEmployee($model)){
            $list = $model->validateSuccess();
            if ($list["bool"]) {
                $model->saveSuccessAll($list["data"]);
                Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Success'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Warning'), $message);
            }
            if($list['typeNum']==1){
                $this->redirect(Yii::app()->createUrl('marketSales/edit',array('index'=>$model->id)));
            }else{
                $this->redirect(Yii::app()->createUrl('marketSales/index'));
            }
        }else{
            throw new CHttpException(404,'该账号未绑定员工，请与管理员联系');
        }
    }

    public function actionFileupload($doctype) {
        $model = new MarketSalesForm();
        if (isset($_POST['MarketSalesForm'])) {
            $model->attributes = $_POST['MarketSalesForm'];

            $id = ($_POST['MarketSalesForm']['scenario']=='new') ? 0 : $model->id;
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
        $model = new MarketSalesForm();
        if (isset($_POST['MarketSalesForm'])) {
            $model->attributes = $_POST['MarketSalesForm'];
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
                $docman = new DocMan($doctype,$docId,'MarketSalesForm');
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
		return Yii::app()->user->validRWFunction('MT03');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('MT03');
	}
}
