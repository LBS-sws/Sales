<?php

class ContProController extends Controller
{
    public $function_id='CT01';

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
                'expression'=>array('ContProController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','compare','resetFile'),
                'expression'=>array('ContProController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionResetFile()
    {
        $model = new ContProForm('view');
        $model->resetFileToQiNiu();
        die();
    }

    public function actionSaveSeal($type='save')
    {
        if (isset($_POST['ContProForm'])) {
            $model = new ContProForm('edit');
            $model->attributes = $_POST["ContProForm"];
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
            $this->redirect(Yii::app()->createUrl('contPro/edit',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('contPro/index'));
        }
    }

    public function actionIndex($pageNum=0)
    {
        $session = Yii::app()->session;
        $session["clueDetail"]="contPro";
        $model = new ContProList;
        if (isset($_POST['ContProList'])) {
            $model->attributes = $_POST['ContProList'];
        } else {
            if (isset($session['criteria_ContProList']) && !empty($session['criteria_ContProList'])) {
                $criteria = $session['criteria_ContProList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }

    public function actionNew($cont_id,$type='C',$store_id='')
    {
        $model = new ContProForm('new');
        $model->cont_id=$cont_id;
        $model->retrieveDataByNew($type);
        if (!$model->hasErrors()&&$model->validate()) {
            if(!empty($store_id)&&isset($model->clueSSERow[$store_id])){
                $model->showStore = empty($model->showStore)?array():$model->showStore;
                $model->showStore[]=$store_id;
            }
            $this->render('form',array('model'=>$model));
        }else{
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            $this->redirect(Yii::app()->createUrl('contHead/detail',array('index'=>$model->cont_id)));
        }
    }

    public function actionEdit($index)
    {
        $model = new ContProForm('edit');
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
                $this->redirect(Yii::app()->createUrl('contPro/index'));
            }
        }
    }

    public function actionCompare($index)
    {
        $model = new ContProForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateContID("cont_id",'');
            if($model->hasErrors()===false){
                $model->validateID("id",'');
                $model->setCompareModelByContID($model->cont_id);
                $this->render('compare',array('model'=>$model,));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('contPro/index'));
            }
        }
    }

    public function actionDetail($index)
    {
        $model = new ContProForm('view');
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
                $this->redirect(Yii::app()->createUrl('contPro/index'));
            }
        }
    }

    public function actionView($index)
    {
        $this->layout = 'mh_main';
        $model = new ContProForm('view');
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
                $this->redirect(Yii::app()->createUrl('contPro/index'));
            }
        }
    }

    public function actionSave($type='draft')
    {
        if (isset($_POST['ContProForm'])) {
            $model = new ContProForm($type);
            $model->attributes = $_POST["ContProForm"];
            if ($model->validate()) {
                $model->pro_status = $type=="audit"?1:0;
                $bool = $model->saveData();
                if($bool){
                    if(!empty($model->goMhWebUrl)){
                        $this->redirect($model->goMhWebUrl);
                    }else{
                        Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                        $this->redirect(Yii::app()->createUrl('contPro/edit',array('index'=>$model->id)));
                    }
                }else{
                    $message = CHtml::errorSummary($model);
                    Dialog::message("门户网站异常", $message);
                    if (empty($model->id)){
                        $this->redirect(Yii::app()->createUrl('contPro/new',array('cont_id'=>$model->cont_id,'type'=>$model->pro_type)));
                    }else{
                        $this->redirect(Yii::app()->createUrl('contPro/edit',array('index'=>$model->id)));
                    }
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                if (empty($model->id)){
                    $this->redirect(Yii::app()->createUrl('contPro/new',array('cont_id'=>$model->cont_id,'type'=>$model->pro_type)));
                }else{
                    $this->redirect(Yii::app()->createUrl('contPro/edit',array('index'=>$model->id)));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('contPro/index'));
        }
    }

    public function actionDelete()
    {
        if (isset($_POST['ContProForm'])) {
            $model = new ContProForm('delete');
            $model->attributes = $_POST["ContProForm"];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('/contHead/detail',array('index'=>$model->cont_id)));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('contPro/edit',array('index'=>$model->id)));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('contPro/index'));
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('CM09')||Yii::app()->user->validRWFunction('CM10')||Yii::app()->user->validRWFunction('CM02');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CM02');
    }
}
