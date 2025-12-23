<?php

class ClueStorePersonController extends Controller
{
	public $function_id='CM10';

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
				'actions'=>array('delete','ajaxShow','ajaxSave','ajaxDelete'),
				'expression'=>array('ClueStorePersonController','allowStoreReadWrite'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionAjaxShow(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            if (isset($_POST['ClueStorePersonForm'])) {
                $model = new ClueStorePersonForm($_POST['ClueStorePersonForm']['scenario']);
                $model->attributes = $_POST['ClueStorePersonForm'];
                if($model->getScenario()!="new"){
                    $model->retrieveData($model->id);
                }
                $html = $this->renderPartial('//clientPerson/ajaxForm',array('model'=>$model),true);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>"联系人表单"));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxSave(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            if (isset($_POST['ClueStorePersonForm'])) {
                $model = new ClueStorePersonForm($_POST['ClueStorePersonForm']['scenario']);
                $model->attributes = $_POST['ClueStorePersonForm'];
                if ($model->validate()) {
                    $model->saveData();
                    $clueStoreModel = new ClueStoreForm("view");
                    $clueStoreModel->id = $model->clue_store_id;
                    $clueStoreModel->clue_id = $model->clue_id;
                    $html = $this->renderPartial('//clueStore/dv_person',array('model'=>$clueStoreModel),true);
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

    public function actionAjaxDelete(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            if (isset($_POST['ClueStorePersonForm'])) {
                $model = new ClueStorePersonForm($_POST['ClueStorePersonForm']['scenario']);
                $model->attributes = $_POST['ClueStorePersonForm'];
                $model->retrieveData($model->id);
                $html = "确定删除该联系人？";
                $html .= TbHtml::hiddenField("ClueStorePersonForm[scenario]", "delete");
                $html .= TbHtml::hiddenField("ClueStorePersonForm[id]", $model->id);
                $html .= TbHtml::hiddenField("ClueStorePersonForm[clue_id]", $model->clue_id);
                $html .= TbHtml::hiddenField("ClueStorePersonForm[clue_store_id]", $model->clue_store_id);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>Yii::t('clue','delete')));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }
	
	public static function allowStoreReadWrite() {
		return Yii::app()->user->validRWFunction('CM10');
	}
	
	public static function allowStoreReadOnly() {
		return Yii::app()->user->validFunction('CM10');
	}
}
