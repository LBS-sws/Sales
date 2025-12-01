<?php

class ClueSSEController extends Controller
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
				'actions'=>array('ajaxShow','ajaxDelete','ajaxSave','ajaxAllSave'),
				'expression'=>array('ClueSSEController','allowReadWrite'),
			),
            /*
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ClueSSEController','allowReadOnly'),
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
            if (isset($_POST['ClueSSEForm'])) {
                $model = new ClueSSEForm($_POST['ClueSSEForm']['scenario']);
                $model->attributes = $_POST['ClueSSEForm'];
                $title=Yii::t('clue','clue service store');
                $model->validateClueServiceID("clue_service_id","");
                $html = $this->renderPartial('//clueSSE/ajaxForm',array('model'=>$model),true);
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
            if (isset($_POST['ClueSSEForm'])) {
                $model = new ClueSSEForm($_POST['ClueSSEForm']['scenario']);
                $model->attributes = $_POST['ClueSSEForm'];
                $title=Yii::t('clue','delete');
                $html=Yii::t('clue','delete clue sse body');
                $html.=TbHtml::hiddenField("ClueSSEForm[scenario]",$model->getScenario());
                $html.=TbHtml::hiddenField("ClueSSEForm[id]",$model->id);
                $html.=TbHtml::hiddenField("ClueSSEForm[clue_service_id]",$model->clue_service_id);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>$title));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxSave(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            if (isset($_POST['ClueSSEForm'])) {
                $model = new ClueSSEForm($_POST['ClueSSEForm']['scenario']);
                $model->attributes = $_POST['ClueSSEForm'];
                if ($model->validate()) {
                    $model->saveData();
                    $clientHeadModel = new ClientHeadForm("view");
                    $clientHeadModel->retrieveData($model->clue_id);
                    $clientHeadModel->setClueServiceID($model->clue_service_id);
                    $html = ClueFlowForm::printClueServiceStoreBox($this,$clientHeadModel);
                    echo CJSON::encode(array('status'=>1,'html'=>$html,'error'=>''));
                } else {
                    $message = CHtml::errorSummary($model);
                    echo CJSON::encode(array('status'=>0,'html'=>'','error'=>$message));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxAllSave(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $arrs=$_POST;
            $clue_id=0;
            $clue_service_id=0;
            foreach ($arrs as $queryString){
                $arr=array();
                parse_str($queryString, $arr);
                if(isset($arr['ClueSSEForm'])){
                    $model = new ClueSSEForm($arr['ClueSSEForm']['scenario']);
                    $model->attributes = $arr['ClueSSEForm'];
                    if ($model->validate()) {
                        $model->saveData();
                        $clue_id=$model->clue_id;
                        $clue_service_id=$model->clue_service_id;
                    }
                }
            }
            if(!empty($clue_id)){
                $clientHeadModel = new ClientHeadForm("view");
                $clientHeadModel->retrieveData($clue_id);
                $clientHeadModel->setClueServiceID($clue_service_id);
                $html = ClueFlowForm::printClueServiceStoreBox($this,$clientHeadModel);
                echo CJSON::encode(array('status'=>1,'html'=>$html,'error'=>''));
            }else{
                echo CJSON::encode(array('status'=>0,'html'=>'','error'=>'数据异常'));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

	public function actionUpdate()
	{
		if (isset($_POST['ClueSSEForm'])) {
			$model = new ClueSSEForm('edit');
            $model->attributes = $_POST['ClueSSEForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
            if($model->clueHeadRow["table_type"]==1){
                $this->redirect(Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id)));
            }else{
                $this->redirect(Yii::app()->createUrl('clientHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id)));
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
