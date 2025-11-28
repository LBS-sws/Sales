<?php

class ClueUStaffController extends Controller
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
                'actions'=>array('ajaxShow','ajaxSave'),
                'expression'=>array('ClueUStaffController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionAjaxShow(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            if (isset($_POST['ClueUStaffForm'])) {
                $model = new ClueUStaffForm($_POST['ClueUStaffForm']['scenario']);
                $model->attributes = $_POST['ClueUStaffForm'];
                if($model->getScenario()!="new"){
                    $model->retrieveData($model->id);
                }
                $html = $this->renderPartial('//clueUStaff/ajaxForm',array('model'=>$model),true);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>"开票信息表单"));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxSave(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            if (isset($_POST['ClueUStaffForm'])) {
                $model = new ClueUStaffForm($_POST['ClueUStaffForm']['scenario']);
                $model->attributes = $_POST['ClueUStaffForm'];
                if ($model->validate()) {
                    $model->saveData();
                    $clientHeadModel = new ClientHeadForm("view");
                    $clientHeadModel->id = $model->clue_id;
                    $html = $this->renderPartial('//clientHead/dv_u_staff',array('model'=>$clientHeadModel),true);
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

    public static function allowReadWrite() {
        return Yii::app()->user->validFunction('CM02')||Yii::app()->user->validFunction('CM10');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CM02')||Yii::app()->user->validFunction('CM10');
    }
}
