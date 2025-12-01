<?php

class ClueFlowController extends Controller
{
	public $function_id='CM02';

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
				'actions'=>array('ajaxShow','ajaxDelete','ajaxSave'),
				'expression'=>array('ClueFlowController','allowReadWrite'),
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

    public function actionAjaxShow(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            $title = "跟进表单";
            if (isset($_POST['ClueFlowForm'])) {
                $model = new ClueFlowForm($_POST['ClueFlowForm']['scenario']);
                $model->attributes = $_POST['ClueFlowForm'];
                if($model->getScenario()!="new"){
                    $title=Yii::t('clue','update clue flow');
                    $model->retrieveData($model->id);
                }else{
                    $title=Yii::t('clue','add clue flow');
                    $model->retrieveDataByLast();
                    $model->visit_date=date("Y/m/d");
                }
                $model->validateClueServiceID("clue_service_id","");
                $model->validateClueID("clue_id","");
                $html = $this->renderPartial('//clueFlow/ajaxForm',array('model'=>$model),true);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>$title));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxDelete(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            $title = "跟进表单";
            if (isset($_POST['ClueFlowForm'])) {
                $model = new ClueFlowForm($_POST['ClueFlowForm']['scenario']);
                $model->attributes = $_POST['ClueFlowForm'];
                $title=Yii::t('clue','delete');
                $html=Yii::t('clue','delete clue flow body');
                $html.=TbHtml::hiddenField("ClueFlowForm[scenario]",$model->getScenario());
                $html.=TbHtml::hiddenField("ClueFlowForm[id]",$model->id);
                $html.=TbHtml::hiddenField("ClueFlowForm[clue_service_id]",$model->clue_service_id);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>$title));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxSave(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            if (isset($_POST['ClueFlowForm'])) {
                $model = new ClueFlowForm($_POST['ClueFlowForm']['scenario']);
                $model->attributes = $_POST['ClueFlowForm'];
                if ($model->validate()) {
                    $model->saveData();
                    $clientHeadModel = new ClientHeadForm("view");
                    $clientHeadModel->id = $model->clue_id;
                    $clientHeadModel->clue_service_id = $model->clue_service_id;
                    $html = ClueFlowForm::printClueServiceFlowBox($this,$clientHeadModel);
                    $htmlService = ClueServiceForm::printClueServiceBox($this,$clientHeadModel);
                    echo CJSON::encode(array('status'=>1,'html'=>$html,'htmlService'=>$htmlService,'error'=>''));
                } else {
                    $message = CHtml::errorSummary($model);
                    echo CJSON::encode(array('status'=>0,'html'=>'','error'=>$message));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CM02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CM02');
	}
}
