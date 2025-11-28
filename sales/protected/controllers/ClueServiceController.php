<?php

class ClueServiceController extends Controller
{
	public $function_id='CM03';

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
				'actions'=>array('addClueService','ajaxShow','ajaxSave'),
				'expression'=>array('ClueServiceController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index'),
				'expression'=>array('ClueServiceController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionAjaxShow(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            $title = "跟进表单";
            if (isset($_POST['ClueServiceForm'])) {
                $model = new ClueServiceForm($_POST['ClueServiceForm']['scenario']);
                $model->attributes = $_POST['ClueServiceForm'];
                if($model->getScenario()!="new"){
                    $title=Yii::t('clue','update clue service');
                    $model->retrieveData($model->id);
                }else{
                    $title=Yii::t('clue','add clue service');
                }
                $model->validateID("id","");
                $html = $this->renderPartial('//clueService/ajaxForm',array('model'=>$model),true);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>$title));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxSave(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            if (isset($_POST['ClueServiceForm'])) {
                $scenario = $_POST['ClueServiceForm']['scenario'];
                $model = new ClueServiceForm($scenario);
                $model->attributes = $_POST['ClueServiceForm'];
                $model->validateID("id","");
                $flowModel = new ClueFlowForm("new");
                if(isset($model->clueHeadRow["clue_type"])&&$model->clueHeadRow["clue_type"]==1){
                    $flowModel->attributes = isset($_POST['ClueFlowForm'])?$_POST['ClueFlowForm']:array();
                    $flowModel->clue_id=$model->clue_id;
                    $flowModel->validate();
                    $flowModel->clearErrors("clue_service_id");
                }
                if ($model->validate()&&!$flowModel->hasErrors()) {
                    $model->saveData();
                    if($flowModel->clue_type==1){
                        $flowModel->clue_service_id=$model->id;
                        $flowModel->validateClueServiceID("clue_service_id","");
                        $flowModel->saveData();
                    }
                    $clientHeadModel = new ClientHeadForm("view");
                    $clientHeadModel->id = $model->clue_id;
                    $clientHeadModel->clue_service_id = $model->id;
                    $html = ClueServiceForm::printClueServiceBox($this,$clientHeadModel);
                    $htmlFlow="";
                    if($scenario!="edit"){
                        $clientHeadModel->rec_employee_id = $model->clueHeadRow["rec_employee_id"];
                        $clientHeadModel->setClueServiceID($model->id);
                        $htmlFlow = ClueFlowForm::printClueFlowAndStoreBox($this,$clientHeadModel);
                    }
                    echo CJSON::encode(array('status'=>1,'html'=>$html,'htmlFlow'=>$htmlFlow,'error'=>''));
                } else {
                    $message = CHtml::errorSummary($model);
                    $message.= CHtml::errorSummary($flowModel);
                    echo CJSON::encode(array('status'=>0,'html'=>'','error'=>$message));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionIndex($pageNum=0)
    {
        $model = new ClueServiceList;
        $session = Yii::app()->session;
        $session["clueDetail"]="service";//详情的返回为商机列表
        if (isset($_POST['ClueServiceList'])) {
            $model->attributes = $_POST['ClueServiceList'];
        } else {
            if (isset($session['criteria_ClueServiceList']) && !empty($session['criteria_ClueServiceList'])) {
                $criteria = $session['criteria_ClueServiceList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }

	public function actionAddClueService()
	{
		if (isset($_POST['ClueServiceForm'])) {
			$model = new ClueServiceForm('new');
            $model->attributes = $_POST['ClueServiceForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
			if($model->clueHeadRow["table_type"]==1){
                $this->redirect(Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id,'service_id'=>$model->id)));
            }else{
                $this->redirect(Yii::app()->createUrl('clientHead/view',array('index'=>$model->clue_id,'service_id'=>$model->id)));
            }
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CM02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CM02');
	}
}
