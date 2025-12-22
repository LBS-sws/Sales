<?php

class ClueInvoiceController extends Controller
{
	public $function_id='CM0X';

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
				'actions'=>array('invoiceList','new','edit','view','save','delete','ajaxShow','ajaxSave'),
				'expression'=>array('ClueInvoiceController','allowStoreReadWrite'),
			),
            /*
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ClueFlowController','allowReadOnly'),
			),
            */
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	protected function resertFunCode($type){
        $session = Yii::app()->session;
        $session["menu_code"]="CM02";
        $session["active_func"]="CM02";
        $this->function_id = "CM02";
    }

    public function actionAjaxShow(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            if (isset($_POST['ClueInvoiceForm'])) {
                $model = new ClueInvoiceForm($_POST['ClueInvoiceForm']['scenario']);
                $model->attributes = $_POST['ClueInvoiceForm'];
                if($model->getScenario()!="new"){
                    $model->retrieveData($model->id);
                }
                $html = $this->renderPartial('//clueInvoice/ajaxForm',array('model'=>$model),true);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>"开票信息表单"));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxSave(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            if (isset($_POST['ClueInvoiceForm'])) {
                $model = new ClueInvoiceForm($_POST['ClueInvoiceForm']['scenario']);
                $model->attributes = $_POST['ClueInvoiceForm'];
                if ($model->validate()) {
                    $model->saveData();
                    echo CJSON::encode(array('status'=>1,'message'=>'保存成功','data'=>array('id'=>$model->id)));
                } else {
                    $message = CHtml::errorSummary($model);
                    echo CJSON::encode(array('status'=>0,'error'=>$message));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

	public function actionInvoiceList($clue_id,$pageNum=0)
	{
	    $this->resertFunCode(1);
        $clueHeadModel = new ClueHeadForm('view');
        if($clueHeadModel->retrieveData($clue_id)){
            $model = new ClueInvoiceList;
            if (isset($_POST['ClueInvoiceList'])) {
                $model->attributes = $_POST['ClueInvoiceList'];
            } else {
                if (isset($session['criteria_ClueInvoice']) && !empty($session['criteria_ClueInvoice'])) {
                    $criteria = $session['criteria_ClueInvoice'];
                    $model->setCriteria($criteria);
                }
            }
            $model->determinePageNum($pageNum);
            $model->retrieveDataByClueAndPage($clueHeadModel->id,$model->pageNum);
            $this->render('index',array('model'=>$model,'clueHeadModel'=>$clueHeadModel));
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
	}

	public function actionNew($clue_id,$type=0)
	{
        $this->resertFunCode($type);
        $clueHeadModel = new ClueHeadForm('view');
        if($clueHeadModel->retrieveData($clue_id)){
            $model = new ClueInvoiceForm('new');
            $model->clue_id = $clueHeadModel->id;
            $model->city = $clueHeadModel->city;
            $model->clueHeadRow = $clueHeadModel->getAttributes();
            $this->render('form',array('model'=>$model,'type'=>$type));
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
	}

    public function actionView($index,$type=0)
    {
        $this->resertFunCode($type);
        $model = new ClueInvoiceForm('view');
        if($model->retrieveData($index)){
            $clueHeadModel = new ClueHeadForm('view');
            if (!$clueHeadModel->retrieveData($model->clue_id)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $model->clueHeadRow = $clueHeadModel->getAttributes();
                $this->render('form',array('model'=>$model,'type'=>$type));
            }
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionEdit($index,$type=0)
    {
        $this->resertFunCode($type);
        $model = new ClueInvoiceForm('edit');
        if($model->retrieveData($index)){
            $clueHeadModel = new ClueHeadForm('view');
            if (!$clueHeadModel->retrieveData($model->clue_id)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $model->clueHeadRow = $clueHeadModel->getAttributes();
                $this->render('form',array('model'=>$model,'type'=>$type));
            }
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

	public function actionSave($type)
	{
		if (isset($_POST['ClueInvoiceForm'])) {
			$model = new ClueInvoiceForm($_POST['ClueInvoiceForm']['scenario']);
            $model->attributes = $_POST['ClueInvoiceForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
            $this->render('form',array('model'=>$model,'type'=>$type));
		}
	}

	public function actionDelete($type)
	{
		if (isset($_POST['ClueInvoiceForm'])) {
			$model = new ClueInvoiceForm('delete');
            $model->attributes = $_POST['ClueInvoiceForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                switch ($type){
                    case 1:
                        $this->redirect(Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id)));
                        break;
                    case 2:
                        $this->redirect(Yii::app()->createUrl('clueInvoice/index'));
                        break;
                    default:
                        $this->redirect(Yii::app()->createUrl('clueInvoice/invoiceList',array('clue_id'=>$model->clue_id)));
                }
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('clueInvoice/edit',array('index'=>$model->id,'type'=>$type)));
			}
		}
	}
	
	public static function allowStoreReadWrite() {
        return Yii::app()->user->validFunction('CM02')||Yii::app()->user->validFunction('CM10');
	}
	
	public static function allowStoreReadOnly() {
		return Yii::app()->user->validFunction('CM02')||Yii::app()->user->validFunction('CM10');
	}
}
