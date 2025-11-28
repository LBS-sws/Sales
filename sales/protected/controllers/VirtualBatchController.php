<?php

class VirtualBatchController extends Controller
{
    public $function_id='CT02';

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
                'expression'=>array('VirtualBatchController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','resetFile'),
                'expression'=>array('VirtualBatchController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionResetFile()
    {
        $model = new VirtualBatchForm('view');
        $model->resetFileToQiNiu();
        die();
    }

    public function actionSaveSeal($type='save')
    {
        if (isset($_POST['VirtualBatchForm'])) {
            $model = new VirtualBatchForm('edit');
            $model->attributes = $_POST["VirtualBatchForm"];
            if ($model->retrieveData($model->id)&&$model->validateUploadSeal()) {
                $list=$model->saveSeal($type);
                if($list["bool"]){
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                }else{
                    $message=$list["msg"];
                    Dialog::message(Yii::t('dialog','门户网站异常'), $message);
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
            $this->redirect(Yii::app()->createUrl('virtualBatch/edit',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('virtualBatch/index'));
        }
    }

    public function actionIndex($pageNum=0)
    {
        $session = Yii::app()->session;
        $session["clueDetail"]="virtualBatch";
        $model = new VirtualBatchList;
        if (isset($_POST['VirtualBatchList'])) {
            $model->attributes = $_POST['VirtualBatchList'];
        } else {
            if (isset($session['criteria_VirtualBatchList']) && !empty($session['criteria_VirtualBatchList'])) {
                $criteria = $session['criteria_VirtualBatchList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }

    public function actionNew($check_id,$type='C')
    {
        $model = new VirtualBatchForm('new');
        $model->vir_id_text=$check_id;
        $model->pro_type = $type;
        if ($model->validate()) {
            $model->pro_date=date("Y/m/d");
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
        $model = new VirtualBatchForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateVirIDText("id",'');
            if($model->hasErrors()===false){
                $this->render('form',array('model'=>$model,));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualBatch/index'));
            }
        }
    }

    public function actionDetail($index)
    {
        $model = new VirtualBatchForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateVirIDText("id",'');
            if($model->hasErrors()===false){
                $this->render('detail',array('model'=>$model,));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualBatch/index'));
            }
        }
    }

    public function actionView($index)
    {
        $this->layout = 'mh_main';
        $model = new VirtualBatchForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateVirIDText("id",'');
            if($model->hasErrors()===false){
                $sealBool = $model->validateSeal();
                $this->render('view',array('model'=>$model,'seal'=>$sealBool["status"]==200));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualBatch/index'));
            }
        }
    }

    public function actionSave($type='draft')
    {
        if (isset($_POST['VirtualBatchForm'])) {
            $model = new VirtualBatchForm($type);
            $model->attributes = $_POST["VirtualBatchForm"];
            if ($model->validate()) {
                $model->pro_status = $type=="audit"?1:0;
                $bool = $model->saveData();
                if($bool){
                    if(!empty($model->goMhWebUrl)){
                        $this->redirect($model->goMhWebUrl);
                    }else{
                        Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                        $this->redirect(Yii::app()->createUrl('virtualBatch/edit',array('index'=>$model->id)));
                    }
                }else{
                    $message = CHtml::errorSummary($model);
                    Dialog::message("门户网站异常", $message);
                    if(!empty($model->id)){
                        $this->redirect(Yii::app()->createUrl('virtualBatch/edit',array('index'=>$model->id)));
                    }else{
                        $this->redirect(Yii::app()->createUrl('virtualBatch/new',array('type'=>$model->pro_type,'check_id'=>$model->vir_id_text)));
                    }
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                if(!empty($model->id)){
                    $this->redirect(Yii::app()->createUrl('virtualBatch/edit',array('index'=>$model->id)));
                }else{
                    $this->redirect(Yii::app()->createUrl('virtualBatch/new',array('type'=>$model->pro_type,'check_id'=>$model->vir_id_text)));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('virtualBatch/index'));
        }
    }

    public function actionDelete()
    {
        if (isset($_POST['VirtualBatchForm'])) {
            $model = new VirtualBatchForm('delete');
            $model->attributes = $_POST["VirtualBatchForm"];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('/virtualHead/index'));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualBatch/edit',array('index'=>$model->id)));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('virtualBatch/index'));
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('CM09')||Yii::app()->user->validRWFunction('CM10')||Yii::app()->user->validRWFunction('CM02');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CM02');
    }
}
