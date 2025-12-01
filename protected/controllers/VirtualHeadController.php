<?php

class VirtualHeadController extends Controller
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
                'actions'=>array(),
                'expression'=>array('VirtualHeadController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','detail','compare'),
                'expression'=>array('VirtualHeadController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex($pageNum=0)
    {
        $session = Yii::app()->session;
        $session["clueDetail"]="cont";
        $model = new VirtualHeadList;
        if (isset($_POST['VirtualHeadList'])) {
            $model->attributes = $_POST['VirtualHeadList'];
        } else {
            if (isset($session['criteria_VirtualHeadList']) && !empty($session['criteria_VirtualHeadList'])) {
                $criteria = $session['criteria_VirtualHeadList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }

    public function actionNew($clue_service_id)
    {
        $model = new VirtualHeadForm('new');
        $model->clue_service_id=$clue_service_id;
        if ($model->validate()) {
            $model->retrieveDataByNew();
            $this->render('form',array('model'=>$model));
        }else{
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            if($model->clueHeadRow["table_type"]==1){
                $this->redirect(Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id)));
            }else{
                $this->redirect(Yii::app()->createUrl('clientHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id)));
            }
        }
    }

    public function actionEdit($index)
    {
        $model = new VirtualHeadForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateRowByID("id",'');
            if($model->hasErrors()){
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualHead/index'));
            }else{
                $this->render('detail',array('model'=>$model,));
            }
        }
    }

    public function actionDetail($index)
    {
        $model = new VirtualHeadForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateRowByID("id",'');
            if($model->hasErrors()){
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualHead/index'));
            }else{
                $this->render('detail',array('model'=>$model,));
            }
        }
    }

    public function actionCompare($index)
    {
        $model = new VirtualProForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateRowByID("id",'');
            if($model->hasErrors()){
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualHead/index'));
            }else{
                $model->setCompareModelByVirID($model->vir_id);
                $this->render('compare',array('model'=>$model,));
            }
        }
    }

    public function actionView($index)
    {
        $this->layout = 'main_nm';
        $model = new VirtualHeadForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateRowByID("id",'');
            if($model->hasErrors()){
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('virtualHead/index'));
            }else{
                $this->render('detail',array('model'=>$model,));
            }
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('CT02')||Yii::app()->user->validRWFunction('CM10')||Yii::app()->user->validRWFunction('CM02');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CM02');
    }
}
