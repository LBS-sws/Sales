<?php

class MarketCompanyController extends Controller 
{
	public $function_id='MT01';

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
				'actions'=>array('new','edit','delete','save','assign','reject','success','importExcel','downTemp','fileupload','fileremove'),
				'expression'=>array('MarketCompanyController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel','filedownload'),
				'expression'=>array('MarketCompanyController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	//导入Excel
	public function actionImportExcel(){
        $model = new MarketDown();
        $model->attributes = $_POST['MarketDown'];
        $path =Yii::app()->basePath."/commands/template/";
        if ($model->validate()) {
            $img = CUploadedFile::getInstance($model,'file');
            $model->file = $img->getName();
            $url = $path."importMarket.".$img->getExtensionName();
            $img->saveAs($url);
            $loadExcel = new LoadExcel($url);
            $list = $loadExcel->getExcelList();
            $model->insertStaticList();
            $headBool = $model->loadData($list);
            if($headBool){
                if($model->_errorSum>0){
                    $model->downErrorList();
                }else{
                    $dialog = "导入成功。成功数量：".count($model->_successList);
                    Dialog::message(Yii::t('dialog','Information'), $dialog);
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('marketCompany/index'));
        }else{
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            $this->redirect(Yii::app()->createUrl('marketCompany/index'));
        }
    }

	//下载导入模板
	public function actionDownTemp(){
        $model = new MarketDown();
        $model->downTemp();
    }

	//下载Excel(暂时不用)
    public function actionDownExcel()
    {
        $model = new MarketCompanyList();
        if (isset($_POST['MarketCompanyList'])) {
            $model->attributes = $_POST['MarketCompanyList'];
        }
        $year=isset($_POST["year"])?$_POST["year"]:date("Y");
        $model->downExcel($year);
    }

    //分配
    public function actionAssign()
    {
        if (isset($_POST['assign_type'])) {
            $model = new MarketCompanyForm();
            $list = $model->validateAssign();
            if ($list["bool"]) {
                $model->saveAssign($list["data"]);
                Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Assign Done'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            if($_POST['assign_type']!="form"){
                $this->redirect(Yii::app()->createUrl('marketCompany/index'));
            }else{
                $this->redirect(Yii::app()->createUrl('marketCompany/edit',array('index'=>$model->id)));
            }
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new MarketCompanyList;
        if (isset($_POST['MarketCompanyList'])) {
            $model->attributes = $_POST['MarketCompanyList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['marketCompany_c01']) && !empty($session['marketCompany_c01'])) {
                $criteria = $session['marketCompany_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['MarketCompanyForm'])) {
			$model = new MarketCompanyForm($_POST['MarketCompanyForm']['scenario']);
			$model->attributes = $_POST['MarketCompanyForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('marketCompany/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new MarketCompanyForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new MarketCompanyForm('new');
        $this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new MarketCompanyForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new MarketCompanyForm('delete');
		if (isset($_POST['MarketCompanyForm'])) {
			$model->attributes = $_POST['MarketCompanyForm'];
			if ($model->isOccupied()) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('marketCompany/edit',array('index'=>$model->id)));
			} else {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
		        $this->redirect(Yii::app()->createUrl('marketCompany/index'));
			}
		}
	}

    public function actionReject()
    {
        $model = new MarketCompanyForm('reject');
        $list = $model->validateReject();
        if ($list["bool"]) {
            $model->saveRejectAll($list["data"]);
            Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Reject'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Warning'), $message);
        }
        if($list['typeNum']==1){
            $this->redirect(Yii::app()->createUrl('marketCompany/edit',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('marketCompany/index'));
        }
    }

    public function actionSuccess()
    {
        $model = new MarketCompanyForm('success');
        $list = $model->validateSuccess();
        if ($list["bool"]) {
            $model->saveSuccessAll($list["data"]);
            Dialog::message(Yii::t('dialog','Information'), Yii::t('market','Record Success'));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Warning'), $message);
        }
        if($list['typeNum']==1){
            $this->redirect(Yii::app()->createUrl('marketCompany/edit',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('marketCompany/index'));
        }
    }

    public function actionFileupload($doctype) {
        $model = new MarketCompanyForm();
        if (isset($_POST['MarketCompanyForm'])) {
            $model->attributes = $_POST['MarketCompanyForm'];

            $id = ($_POST['MarketCompanyForm']['scenario']=='new') ? 0 : $model->id;
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
        $model = new MarketCompanyForm();
        if (isset($_POST['MarketCompanyForm'])) {
            $model->attributes = $_POST['MarketCompanyForm'];
            $docman = new DocMan($doctype,$model->id,get_class($model));
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            $docman->fileRemove($model->removeFileId[strtolower($doctype)]);
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select id,city from sal_market where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $citylist = Yii::app()->user->city_allow();
            if (strpos($citylist, $row['city']) !== false) {
                $docman = new DocMan($doctype,$docId,'MarketCompanyForm');
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
		return Yii::app()->user->validRWFunction('MT01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('MT01');
	}
}
