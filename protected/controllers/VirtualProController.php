<?php

class VirtualProController extends Controller
{
    public $function_id='CM07';

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
                'actions'=>array('save','edit','new','delete','saveSeal'),
                'expression'=>array('VirtualProController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view'),
                'expression'=>array('VirtualProController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionSaveSeal($type='save')
    {
        if (isset($_POST['VirtualProForm'])) {
            $model = new VirtualProForm('edit');
            $model->attributes = $_POST["VirtualProForm"];
            if ($model->retrieveData($model->id)&&$model->validateUploadSeal()) {
                $model->saveSeal($type);
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('virtualPro/edit',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('virtualPro/index'));
        }
    }

    public function actionIndex($pageNum=0)
    {
        $session = Yii::app()->session;
        $session["clueDetail"]="virtualPro";
        $model = new VirtualProList;
        if (isset($_POST['VirtualProList'])) {
            $model->attributes = $_POST['VirtualProList'];
        } else {
            if (isset($session['criteria_VirtualProList']) && !empty($session['criteria_VirtualProList'])) {
                $criteria = $session['criteria_VirtualProList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }

    public function actionNew($check_id,$type='C')
    {
        $model = new VirtualProForm('new');
        $model->vir_id_text=$check_id;
        $model->pro_type = $type;
        if ($model->validate()) {
            $this->render('form',array('model'=>$model));
        }else{
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            if (strpos($check_id,',')!==false){
                $this->redirect(Yii::app()->createUrl('virtualHead/index'));
            }else{
                $this->redirect(Yii::app()->createUrl('virtualHead/detail',array('index'=>$model->vir_id)));
            }
        }
    }

    public function actionEdit($index)
    {
        $model = new VirtualProForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateContID("cont_id",'');
            if($model->hasErrors()===false){
                $model->validateID("id",'');
                $this->render('form',array('model'=>$model,));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualPro/index'));
            }
        }
    }

    public function actionDetail($index)
    {
        $model = new VirtualProForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateContID("cont_id",'');
            if($model->hasErrors()===false){
                $model->validateID("id",'');
                $this->render('detail',array('model'=>$model,));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualPro/index'));
            }
        }
    }

    public function actionView($index)
    {
        $this->layout = 'mh_main';
        $model = new VirtualProForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateContID("cont_id",'');
            if($model->hasErrors()===false){
                $model->validateID("id",'');
                $sealBool = $model->validateSeal();
                $this->render('view',array('model'=>$model,'seal'=>$sealBool["status"]==200));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualPro/index'));
            }
        }
    }

    public function actionSave($type='draft')
    {
        if (isset($_POST['VirtualProForm'])) {
            $model = new VirtualProForm($type);
            $model->attributes = $_POST["VirtualProForm"];
            if ($model->validate()) {
                $model->pro_status = $type=="audit"?1:0;
                $bool = $model->saveData();
                if($bool){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                    $this->redirect(Yii::app()->createUrl('virtualPro/edit',array('index'=>$model->id)));
                }else{
                    $message = CHtml::errorSummary($model);
                    Dialog::message("门户网站异常", $message);
                    $this->redirect(Yii::app()->createUrl('virtualPro/new',array('cont_id'=>$model->cont_id)));
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualPro/new',array('cont_id'=>$model->cont_id)));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('virtualPro/index'));
        }
    }

    public function actionDelete()
    {
        if (isset($_POST['VirtualProForm'])) {
            $model = new VirtualProForm('delete');
            $model->attributes = $_POST["VirtualProForm"];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('/contHead/detail',array('index'=>$model->cont_id)));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualPro/edit',array('index'=>$model->id)));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('virtualPro/index'));
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('CM09')||Yii::app()->user->validRWFunction('CM10')||Yii::app()->user->validRWFunction('CM02');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CM02');
    }
}
