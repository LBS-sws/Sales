<?php

class ClueRptController extends Controller
{
	public $function_id='CM05';

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
				'actions'=>array('edit','new','detail','view','save','delete','resetFile'),
				'expression'=>array('ClueRptController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ClueRptController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionNew($clue_service_id)
    {
        $model = new ClueRptForm('new');
        $model->clue_service_id=$clue_service_id;
        if ($model->validate()) {
            $model->lbs_main = $model->clueServiceRow['lbs_main'];
            $model->total_amt = $model->clueServiceRow['predict_amt'];
            if(!empty($model->id)){
                $model->retrieveData($model->id);
                $model->getAllFileJson();
            }
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

    public function actionIndex($pageNum=0)
    {
        $session = Yii::app()->session;
        $session["clueDetail"]="rpt";
        $model = new ClueRptList;
        if (isset($_POST['ClueRptList'])) {
            $model->attributes = $_POST['ClueRptList'];
        } else {
            if (isset($session['criteria_ClueRptList']) && !empty($session['criteria_ClueRptList'])) {
                $criteria = $session['criteria_ClueRptList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }

    public function actionView($index)
    {
        $this->layout = 'mh_main';
        $model = new ClueRptForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            // 门户/审批查看：使用不带团队/城市过滤的校验，避免“线索不存在”
            $model->validateClueServiceIDByView("clue_service_id","");
            $model->getAllFileJson();
            $this->render('view',array('model'=>$model));
        }
    }

    public function actionEdit($index)
    {
        $model = new ClueRptForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateClueServiceID("clue_service_id","");
            $model->getAllFileJson();
            $this->render('form',array('model'=>$model));
        }
    }

	public function actionSave($type)
	{
        if (isset($_POST['ClueRptForm'])) {
            $model = new ClueRptForm($type);
            $model->attributes = $_POST["ClueRptForm"];
            if($type=="audit"){
                $draftModel = new ClueRptForm("draft");
                $draftModel->attributes = $_POST["ClueRptForm"];
                if($draftModel->validate()){
                    $draftModel->rpt_status = 0;
                    $draftModel->saveData();
                    $model->id = $draftModel->id;
                    $_FILES=array();
                    $model->rptFileJson=array();
                    $model->contFileJson=array();
                }
            }
            if ($model->validate()) {
                $model->rpt_status = $type=="audit"?1:0;
                $bool = $model->saveData();
                if($bool){
                    if(!empty($model->goMhWebUrl)){
                        $this->redirect($model->goMhWebUrl);
                    }else{
                        Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                        $this->redirect(Yii::app()->createUrl('clueRpt/edit',array('index'=>$model->id)));
                    }
                }else{
                    $message = CHtml::errorSummary($model);
                    Dialog::message("门户网站异常", $message);
                    if(empty($model->id)){
                        $this->redirect(Yii::app()->createUrl('clueRpt/new',array('clue_service_id'=>$model->clue_service_id)));
                    }else{
                        $this->redirect(Yii::app()->createUrl('clueRpt/edit',array('index'=>$model->id)));
                    }
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('clueRpt/new',array('clue_service_id'=>$model->clue_service_id)));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('clueRpt/index'));
        }
	}

	public function actionDelete()
	{
        if (isset($_POST['ClueRptForm'])) {
            $model = new ClueRptForm('delete');
            $model->attributes = $_POST["ClueRptForm"];
            if ($model->isOccupied($model->id)===false) {
                $model->saveData();
                $this->redirect(Yii::app()->createUrl('clueRpt/index'));
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('clueRpt/edit',array('index'=>$model->id)));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('clueRpt/index'));
        }
	}

    public function actionResetFile()
    {
        $model = new ClueRptForm('view');
        $model->resetFileToQiNiu();
        die();
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CM02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CM02');
	}
}
