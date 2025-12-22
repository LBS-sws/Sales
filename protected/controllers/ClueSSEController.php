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
				'actions'=>array('ajaxShow','ajaxDelete','ajaxSave','ajaxAllSave','ajaxBatchUpdate'),
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
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $search = isset($_POST['search']) ? trim($_POST['search']) : '';
            
            if (isset($_POST['ClueSSEForm'])) {
                $model = new ClueSSEForm($_POST['ClueSSEForm']['scenario']);
                $model->attributes = $_POST['ClueSSEForm'];
                $title=Yii::t('clue','clue service store');
                if (empty($model->clue_service_id)) {
                    echo CJSON::encode(array('status'=>0,'error'=>'请先新增商机，再关联门店','title'=>$title));
                    Yii::app()->end();
                }
                $model->validateClueServiceID("clue_service_id","");
                if ($model->hasErrors('clue_service_id')) {
                    $errors = $model->getErrors('clue_service_id');
                    $firstMsg = !empty($errors) ? $errors[0] : '商机不存在，请刷新重试';
                    echo CJSON::encode(array('status'=>0,'error'=>$firstMsg,'title'=>$title));
                    Yii::app()->end();
                }
                $html = $this->renderPartial('//clueSSE/ajaxForm',array(
                    'model'=>$model,
                    'page'=>$page,
                    'search'=>$search
                ),true);
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
    
    // 批量更新设备、洁具、标靶、处理方式
    public function actionAjaxBatchUpdate(){
        if(Yii::app()->request->isAjaxRequest) {
            try {
                $clue_service_id = isset($_POST['clue_service_id']) ? intval($_POST['clue_service_id']) : 0;
                $updateType = isset($_POST['update_type']) ? $_POST['update_type'] : ''; // device/ware/pest/method
                $updateValue = isset($_POST['update_value']) ? $_POST['update_value'] : '';
                $storeIds = isset($_POST['store_ids']) ? $_POST['store_ids'] : array();
                
                if(empty($clue_service_id) || empty($updateType) || empty($storeIds)){
                    echo CJSON::encode(array('status'=>0,'error'=>'参数错误'));
                    Yii::app()->end();
                }
                
                // 验证商机权限
                $model = new ClueSSEForm('edit');
                $model->clue_service_id = $clue_service_id;
                $model->validateClueServiceID("clue_service_id","");
                
                if($model->hasErrors()){
                    echo CJSON::encode(array('status'=>0,'error'=>'商机不存在或无权限'));
                    Yii::app()->end();
                }
                
                $connection = Yii::app()->db;
                $successCount = 0;
                
                foreach($storeIds as $sseId){
                    $sseModel = new ClueSSEForm('edit');
                    if($sseModel->retrieveData($sseId) && $sseModel->clue_service_id == $clue_service_id){
                        // 获取当前服务数据
                        $service = $sseModel->service;
                        
                        // 根据类型更新对应字段
                        foreach($sseModel->serviceDefinition() as $gid=>$items){
                            $fieldKey = '';
                            switch($updateType){
                                case 'device':
                                    $fieldKey = 'svc_'.$gid.'Device';
                                    break;
                                case 'ware':
                                    $fieldKey = 'svc_'.$gid.'Ware';
                                    break;
                                case 'pest':
                                    $fieldKey = 'svc_'.$gid.'Pest';
                                    break;
                                case 'method':
                                    $fieldKey = 'svc_'.$gid.'Method';
                                    break;
                            }
                            
                            if(!empty($fieldKey)){
                                $service[$fieldKey] = $updateValue;
                            }
                        }
                        
                        // 更新数据库
                        $connection->createCommand()->update("sal_clue_sre_soe",array(
                            "detail_json"=>json_encode($service),
                            "luu"=>Yii::app()->user->id,
                        ),"id=:id",array(":id"=>$sseId));
                        
                        $successCount++;
                    }
                }
                
                // 重新加载数据
                $clientHeadModel = new ClientHeadForm("view");
                $clientHeadModel->retrieveData($model->clue_id);
                $clientHeadModel->setClueServiceID($clue_service_id);
                $html = ClueFlowForm::printClueServiceStoreBox($this,$clientHeadModel);
                
                echo CJSON::encode(array(
                    'status'=>1,
                    'html'=>$html,
                    'message'=>"成功更新{$successCount}个门店"
                ));
            } catch (Exception $e) {
                echo CJSON::encode(array('status'=>0,'error'=>'批量更新失败: '.$e->getMessage()));
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
		return Yii::app()->user->validRWFunction('CM02') || Yii::app()->user->validRWFunction('CM10');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CM02') || Yii::app()->user->validFunction('CM10');
	}
}
