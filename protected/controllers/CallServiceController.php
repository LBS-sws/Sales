<?php

class CallServiceController extends Controller
{
    public $function_id='CS01';

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
                'actions'=>array('save','edit','new','delete','ajaxStoreShow','ajaxAddStoreShow'),
                'expression'=>array('CallServiceController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view'),
                'expression'=>array('CallServiceController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex($pageNum=0)
    {
        $session = Yii::app()->session;
        $session["clueDetail"]="callService";
        $model = new CallServiceList;
        if (isset($_POST['CallServiceList'])) {
            $model->attributes = $_POST['CallServiceList'];
        } else {
            if (isset($session['criteria_CallServiceList']) && !empty($session['criteria_CallServiceList'])) {
                $criteria = $session['criteria_CallServiceList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }

    public function actionNew($cont_id,$store_ids='',$busine_id='')
    {
        $model = new CallServiceForm('new');
        $model->cont_id=$cont_id;
        if(empty($busine_id)){
            $contModel = new ContForm("view");
            if($contModel->retrieveData($cont_id)){
                $model->busine_id = current($contModel->busine_id);
            }
        }else{
            $model->busine_id = $busine_id;
        }
        $model->store_ids = $store_ids;
        if ($model->validate()) {
            $model->apply_date=date("Y/m/d");
            $model->store_ids = $store_ids;//强制显示
            $this->render('form',array('model'=>$model));
        }else{
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            if (empty($store_ids)){
                $this->redirect(Yii::app()->createUrl('callService/index'));
            }else{
                $this->redirect(Yii::app()->createUrl('contHead/detail',array('index'=>$cont_id)));
            }
        }
    }

    public function actionEdit($index)
    {
        $model = new CallServiceForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->showCallView();
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionView($index)
    {
        $this->layout = 'mh_main';
        $model = new CallServiceForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->showCallView();
            $this->render('view',array('model'=>$model,));
        }
    }

    public function actionSave($type='draft')
    {
        if (isset($_POST['CallServiceForm'])) {
            $model = new CallServiceForm($type);
            $model->attributes = $_POST["CallServiceForm"];
            if ($model->validate()) {
                $model->call_status = $type=="audit"?1:0;
                $bool = $model->saveData();
                if($bool){
                    if(!empty($model->goMhWebUrl)){
                        $this->redirect($model->goMhWebUrl);
                    }else{
                        Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                        $this->redirect(Yii::app()->createUrl('callService/edit',array('index'=>$model->id)));
                    }
                }else{
                    $message = CHtml::errorSummary($model);
                    Dialog::message("门户网站异常", $message);
                    $this->render('form',array('model'=>$model,));
                    /*
                    if(!empty($model->id)){
                        $this->redirect(Yii::app()->createUrl('callService/edit',array('index'=>$model->id)));
                    }else{
                        $this->redirect(Yii::app()->createUrl('callService/new',array('cont_id'=>$model->cont_id,'store_ids'=>$model->store_ids)));
                    }
                    */
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
                    /*
                if(!empty($model->id)){
                    $this->redirect(Yii::app()->createUrl('callService/edit',array('index'=>$model->id)));
                }else{
                    $this->redirect(Yii::app()->createUrl('callService/new',array('type'=>$model->pro_type,'check_id'=>$model->vir_id_text)));
                }
                    */
            }
        }else{
            $this->redirect(Yii::app()->createUrl('callService/index'));
        }
    }

    public function actionDelete()
    {
        if (isset($_POST['CallServiceForm'])) {
            $model = new CallServiceForm('delete');
            $model->attributes = $_POST["CallServiceForm"];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('/callService/index'));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('callService/edit',array('index'=>$model->id)));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('callService/index'));
        }
    }
    public function actionAjaxStoreShow()
    {
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $model = new CallServiceForm('view');
            $title = "关联门店";
            if(isset($_POST["CallServiceForm"])){
                $model->attributes = $_POST["CallServiceForm"];
                $list = $model->getAjaxStoreList();
                $html = $this->renderPartial('//callService/ajaxStoreForm',array('model'=>$model,'list'=>$list),true);
            }else{
                $html = json_encode($_POST,JSON_UNESCAPED_UNICODE);
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>$title));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
    }
    public function actionAjaxAddStoreShow()
    {
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $model = new CallServiceForm('new');
            if(isset($_POST["CallServiceForm"])){
                $model->attributes = $_POST["CallServiceForm"];
                if(isset($_POST["check"])){
                    $ids = implode(",",$_POST["check"]);
                    $model->store_ids.=empty($model->store_ids)?"":",";
                    $model->store_ids.=$ids;
                }
                if($model->validate()){
                    $html = $this->renderPartial('//callService/call_form_store',array('model'=>$model),true);
                    echo CJSON::encode(array('status'=>1,'html'=>$html,'error'=>'','dialog'=>false));
                }else{
                    $message = CHtml::errorSummary($model);
                    echo CJSON::encode(array('status'=>0,'html'=>'','error'=>$message));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('CS01');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CS01');
    }
}
