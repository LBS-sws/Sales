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
				'actions'=>array('new','edit','delete','save','ajaxSupportUser','AjaxCustomerName','fileupload','fileremove'),
				'expression'=>array('RABotController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','downExcel','updateHistory','filedownload'),
				'expression'=>array('RABotController','allowReadOnly'),
			),
            array('allow',
                'actions'=>array('shift'),
                'expression'=>array('RABotController','allowShift'),
            ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionShift()
    {
        if (isset($_POST['RABotForm'])) {
            $model = new RABotForm($_POST['RABotForm']['scenario']);
            $model->attributes = $_POST['RABotForm'];
            if ($model->validateShift()) {
                $model->shiftData();
//				$model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('ka','Shift Done'));
                $this->redirect(Yii::app()->createUrl('rABot/view',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('rABot/edit',array('index'=>$model->id)));
            }
        }
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

    public function actionFileupload($doctype) {
        $model = new RABotForm();
        if (isset($_POST['RABotForm'])) {
            $model->attributes = $_POST['RABotForm'];

            $id = ($_POST['RABotForm']['scenario']=='new') ? 0 : $model->id;
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
        $model = new RABotForm();
        if (isset($_POST['RABotForm'])) {
            $model->attributes = $_POST['RABotForm'];
            $docman = new DocMan($doctype,$model->id,get_class($model));
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            $docman->fileRemove($model->removeFileId[strtolower($doctype)]);
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select id,city from sal_ra_bot where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'RABotForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('RA01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('RA01');
	}

    public static function allowShift() {//转移
        return Yii::app()->user->validFunction('CN18');
    }
}
