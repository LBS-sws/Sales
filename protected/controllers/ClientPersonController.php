<?php

class ClientPersonController extends Controller
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
				'actions'=>array('ajaxShow','ajaxSave','ajaxDelete'),
				'expression'=>array('ClientPersonController','allowStoreReadWrite'),
			),
            /*
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ClientFlowController','allowReadOnly'),
			),
            */
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	protected function resertFunCode($type){
        $session = Yii::app()->session;
        $session["menu_code"]="CM10";
        $session["active_func"]="CM10";
        $this->function_id = "CM10";
    }

	public function actionAjaxShow(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            if (isset($_POST['ClientPersonForm'])) {
                $model = new ClientPersonForm($_POST['ClientPersonForm']['scenario']);
                $model->attributes = $_POST['ClientPersonForm'];
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
            if (isset($_POST['ClientPersonForm'])) {
                $model = new ClientPersonForm($_POST['ClientPersonForm']['scenario']);
                $model->attributes = $_POST['ClientPersonForm'];
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

	public function actionAjaxDelete(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            if (isset($_POST['ClientPersonForm'])) {
                $model = new ClientPersonForm($_POST['ClientPersonForm']['scenario']);
                $model->attributes = $_POST['ClientPersonForm'];
                if($model->getScenario()=="delete" && !empty($model->id)){
                    $model->retrieveData($model->id);
                    if($model->id){
                        $html = "确定删除该联系人？";
                        $html .= TbHtml::hiddenField("ClientPersonForm[scenario]", "delete");
                        $html .= TbHtml::hiddenField("ClientPersonForm[id]", $model->id);
                        $html .= TbHtml::hiddenField("ClientPersonForm[clue_id]", $model->clue_id);
                        $html .= TbHtml::hiddenField("ClientPersonForm[clue_store_id]", $model->clue_store_id);
                    }else{
                        $html = "数据异常，请刷新重试";
                    }
                }else{
                    $html = "数据异常，请刷新重试";
                }
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
