<?php

class ContHeadController extends Controller
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
                'actions'=>array('save','edit','new','delete','saveSeal','sendU','sendNewU','merge','mergeConfirm','mergeSave'),
                'expression'=>array('ContHeadController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','detail','resetStatus','sendFile','ajaxLoadStores'),
                'expression'=>array('ContHeadController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionResetStatus(){
        $suffix = Yii::app()->params['envSuffix'];
        //刷新所有门店、客户的状态
        $virRows = Yii::app()->db->createCommand()->select("clue_store_id,clue_id")->from("sales{$suffix}.sal_contract_virtual a")
            ->where("vir_status>=10")->group("clue_store_id,clue_id")->queryAll();//
        if($virRows){
            foreach ($virRows as $virRow){
                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue",array(
                    "clue_status"=>ClueVirProModel::getClientStatusByClueID($virRow["clue_id"])
                ),"id=:id",array(":id"=>$virRow["clue_id"]));

                Yii::app()->db->createCommand()->update("sales{$suffix}.sal_clue_store",array(
                    "store_status"=>ClueVirProModel::getStoreStatusByStoreID($virRow["clue_store_id"]),
                ),"id=:id",array(":id"=>$virRow["clue_store_id"]));
            }
        }
    }

    public function actionSendFile()
    {
        echo "start:<br/>";
        $model = new CurlNotesByVirFile();
        $model->sendAllVirFileByOldData();
        echo "end!";
        die();
    }

    public function actionSendU($index,$type="A")
    {
        $suffix = Yii::app()->params['envSuffix'];
        $contProRow = Yii::app()->db->createCommand()->select("id,pro_type,pro_date,effect_date")->from("sales{$suffix}.sal_contpro")
            ->where("cont_id=:id and pro_status>=10",array(":id"=>$index))->order("id desc")->queryRow();
		if ($contProRow){
			$uVirModel = new CurlNotesByVirPro();
			$uVirModel->pro_type=$contProRow["pro_type"];
			$uVirModel->update_effective_date=empty($contProRow["pro_date"])?$contProRow["effect_date"]:$contProRow["pro_date"];
			$uVirModel->sendAllVirByProID($contProRow['id']);
			echo "success";
		}else{
			echo "error";
		}
		Yii::app()->end();
    }

    public function actionSendNewU($index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $contRow = Yii::app()->db->createCommand()->select("id,effect_date")->from("sales{$suffix}.sal_contract")
            ->where("id=:id and cont_status>=10",array(":id"=>$index))->queryRow();
		if ($contRow){
            $uVirModel = new CurlNotesByVir();
            $uVirModel->update_effective_date = $contRow["effect_date"];
            $uVirModel->sendAllVirByContID($contRow["id"]);
			echo "success";
		}else{
			echo "error";
		}
		Yii::app()->end();
    }

    public function actionSaveSeal($type='save')
    {
        if (isset($_POST['ContHeadForm'])) {
            $model = new ContHeadForm('edit');
            $model->attributes = $_POST["ContHeadForm"];
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
            $this->redirect(Yii::app()->createUrl('contHead/detail',array('index'=>$model->id)));
        }else{
            $this->redirect(Yii::app()->createUrl('contHead/index'));
        }
    }

    public function actionIndex($pageNum=0)
    {
        $session = Yii::app()->session;
        $session["clueDetail"]="cont";
        $model = new ContHeadList;
        if (isset($_POST['ContHeadList'])) {
            $model->attributes = $_POST['ContHeadList'];
        } else {
            if (isset($session['criteria_ContHeadList']) && !empty($session['criteria_ContHeadList'])) {
                $criteria = $session['criteria_ContHeadList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }

    public function actionNew($clue_service_id)
    {
        $model = new ContHeadForm('new');
        $model->clue_service_id=$clue_service_id;
        if ($model->validate()&&$model->validateInvoice()) {
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
        $model = new ContHeadForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateClueServiceID("clue_service_id",'');
            if($model->hasErrors()===false){
                $model->validateID("id",'');
                $this->render('form',array('model'=>$model,));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('contHead/index'));
            }
        }
    }

    public function actionDetail($index)
    {
        $model = new ContHeadForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateClueServiceID("clue_service_id",'');
            if($model->hasErrors()===false){
                $model->validateID("id",'');
                $this->render('detail',array('model'=>$model,));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('contHead/index'));
            }
        }
    }

    public function actionView($index)
    {
        $this->layout = 'mh_main';
        $model = new ContHeadForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->validateClueServiceID("clue_service_id",'');
            if($model->hasErrors()===false){
                $model->validateID("id",'');
                $sealBool = $model->validateSeal();
                $this->render('view',array('model'=>$model,'seal'=>$sealBool["status"]==200));
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('contHead/index'));
            }
        }
    }

    public function actionSave($type='draft')
    {
        if (isset($_POST['ContHeadForm'])) {
            $model = new ContHeadForm($type);
            $model->attributes = $_POST["ContHeadForm"];
            if($type=="audit"){
                $draftModel = new ContHeadForm("draft");
                $draftModel->attributes = $_POST["ContHeadForm"];
                if($draftModel->validate()){
                    $draftModel->cont_status = 0;
                    $draftModel->saveData();
                    $_FILES=array();
                    $model->fileJson=array();
                }
            }
            if ($model->validate()) {
                $model->cont_status = $type=="audit"?1:0;
                $bool = $model->saveData();
                if($bool){
                    if(!empty($model->goMhWebUrl)){
                        $this->redirect($model->goMhWebUrl);
                    }else{
                        Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                        $this->redirect(Yii::app()->createUrl('contHead/edit',array('index'=>$model->id)));
                    }
                }else{
                    $message = CHtml::errorSummary($model);
                    Dialog::message("门户网站异常", $message);
                    $this->redirect(Yii::app()->createUrl('contHead/new',array('clue_service_id'=>$model->clue_service_id)));
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                if(empty($model->id)){
                    $this->redirect(Yii::app()->createUrl('contHead/new',array('clue_service_id'=>$model->clue_service_id)));
                }else{
                    $this->redirect(Yii::app()->createUrl('contHead/edit',array('index'=>$model->id)));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('contHead/index'));
        }
    }

    public function actionDelete()
    {
        if (isset($_POST['ContHeadForm'])) {
            $model = new ContHeadForm('delete');
            $model->attributes = $_POST["ContHeadForm"];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                if($model->clueHeadRow["table_type"]==1){
                    $this->redirect(Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id)));
                }else{
                    $this->redirect(Yii::app()->createUrl('clientHead/view',array('index'=>$model->clue_id,'service_id'=>$model->clue_service_id)));
                }
            }else{
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('contHead/edit',array('index'=>$model->id)));
            }
        }else{
            $this->redirect(Yii::app()->createUrl('contHead/index'));
        }
    }

    /**
     * 异步加载合同关联门店列表
     */
    public function actionAjaxLoadStores()
    {
        $cont_id = isset($_GET['cont_id']) ? intval($_GET['cont_id']) : 0;
        $pageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        if (empty($cont_id)) {
            echo json_encode(array('status' => 'error', 'message' => '合同ID不能为空'));
            Yii::app()->end();
        }

        // 使用分页模型查询
        $storeModel = new ContStoreList();
        $storeModel->searchKeyword = $searchKeyword;
        $storeModel->cont_id = $cont_id;
        $storeModel->retrieveDataByPage($pageNum);
        
        $storeList = array();
        
        foreach ($storeModel->attr as $row) {
            $person = $row["cust_person"];
            $person.= !empty($row["cust_person_role"])?" ({$row["cust_person_role"]})":"";
            $person.= !empty($row["cust_tel"])?" {$row["cust_tel"]}":"";
            
            $virLists = CGetName::getContractVirRowsByContIDAndStoreID($row["cont_id"],$row["id"]);
            $callShow = false; // 直接改成和老逻辑一样的变量名和初始值
            $virtualCodes = array();
            
            if($virLists){
                foreach ($virLists as $virList){
                    // 直接判断，符合就设置true，和老逻辑一样
                    if($virList["service_fre_type"]==3&&in_array($virList["vir_status"],array(10,30))){
                        $callShow = true;
                    }
                    $virtualCodes[] = array(
                        'id' => $virList['id'],
                        'code' => $virList['vir_code']
                    );
                }
            }
            
            $storeList[] = array(
                'id' => $row['id'],
                'store_code' => $row['store_code'],
                'store_name' => $row['store_name'],
                'cust_class' => CGetName::getCustClassStrByKey($row['cust_class']),
                'district' => CGetName::getDistrictStrByKey($row['district']),
                'address' => $row['address'],
                'person' => $person,
                'store_status' => CGetName::getClueStoreStatusByKey($row['store_status']),
                'virtual_codes' => $virtualCodes,
                'can_check' => $callShow, // 直接改成和老逻辑一样的判断
                'check_id' => $callShow ? $row['id'] : '' // 如果有符合条件的，check_id就是store_id
            );
        }

        echo json_encode(array(
            'status' => 'success',
            'data' => $storeList,
            'pageNum' => $storeModel->pageNum,
            'totalRow' => $storeModel->totalRow,
            'noOfPages' => $storeModel->noOfPages
        ));
        Yii::app()->end();
    }

    /**
     * 主合同合并-选择页面
     */
    public function actionMerge($clue_id=0)
    {
        $model = new ContMergeForm();
        $model->scenario = 'select';
        $model->clue_id = $clue_id;
        
        if (empty($clue_id)) {
            Dialog::message(Yii::t('dialog','Validation Message'), '客户ID不能为空');
            $this->redirect(Yii::app()->createUrl('contHead/index'));
            return;
        }
        
        // 获取该客户下的所有主合同
        $contractList = $model->getContractListByClueId($clue_id);
        
        if (empty($contractList)) {
            Dialog::message(Yii::t('dialog','Validation Message'), '该客户没有主合同');
            $this->redirect(Yii::app()->createUrl('contHead/index'));
            return;
        }
        
        $this->render('merge',array('model'=>$model, 'contractList'=>$contractList));
    }
    
    /**
     * 主合同合并-确认页面
     */
    public function actionMergeConfirm()
    {
        if (isset($_POST['ContMergeForm'])) {
            $model = new ContMergeForm();
            $model->scenario = 'confirm';
            
            // 处理多选的源合同ID
            if (isset($_POST['ContMergeForm']['source_cont_ids']) && is_array($_POST['ContMergeForm']['source_cont_ids'])) {
                $model->source_cont_ids = $_POST['ContMergeForm']['source_cont_ids'];
                // 取第一个作为主要源合同（用于获取客户ID）
                $model->source_cont_id = $model->source_cont_ids[0];
            }
            $model->clue_id = isset($_POST['ContMergeForm']['clue_id']) ? $_POST['ContMergeForm']['clue_id'] : 0;
            $model->step = 'confirm';
            
            if ($model->validate()) {
                // 汇总所有源主合同的关联数据
                $totalRelatedData = array();
                $allRelatedDetail = array();
                
                foreach ($model->source_cont_ids as $source_id) {
                    $relatedData = $model->getRelatedDataStat($source_id);
                    $relatedDetail = $model->getRelatedDataDetail($source_id);
                    
                    // 累加统计数据
                    foreach ($relatedData as $key => $value) {
                        if (!isset($totalRelatedData[$key])) {
                            $totalRelatedData[$key] = 0;
                        }
                        $totalRelatedData[$key] += $value;
                    }
                    
                    // 合并详细数据
                    foreach ($relatedDetail as $key => $items) {
                        if (!isset($allRelatedDetail[$key])) {
                            $allRelatedDetail[$key] = array();
                        }
                        $allRelatedDetail[$key] = array_merge($allRelatedDetail[$key], $items);
                    }
                }
                
                $model->relatedData = $totalRelatedData;
                
                // 获取该客户下的其他主合同列表（用于选择目标主合同）
                $targetContractList = $model->getContractListByClueId($model->sourceContRow['clue_id']);
                
                // 过滤掉所有源主合同
                $sourceIds = $model->source_cont_ids;
                $targetContractList = array_filter($targetContractList, function($item) use ($sourceIds) {
                    return !in_array($item['id'], $sourceIds);
                });
                
                $this->render('merge_confirm', array(
                    'model' => $model,
                    'relatedDetail' => $allRelatedDetail,
                    'targetContractList' => $targetContractList
                ));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('contHead/merge', array('clue_id'=>$model->clue_id)));
            }
        } else {
            $this->redirect(Yii::app()->createUrl('contHead/index'));
        }
    }
    
    /**
     * 主合同合并-执行合并
     */
    public function actionMergeSave()
    {
        if (isset($_POST['ContMergeForm'])) {
            $model = new ContMergeForm();
            $model->scenario = 'merge';
            
            // 处理多选的源合同ID
            if (isset($_POST['ContMergeForm']['source_cont_ids'])) {
                if (is_string($_POST['ContMergeForm']['source_cont_ids'])) {
                    // 如果是逗号分隔的字符串，转为数组
                    $model->source_cont_ids = explode(',', $_POST['ContMergeForm']['source_cont_ids']);
                } else {
                    $model->source_cont_ids = $_POST['ContMergeForm']['source_cont_ids'];
                }
            }
            $model->target_cont_id = isset($_POST['ContMergeForm']['target_cont_id']) ? $_POST['ContMergeForm']['target_cont_id'] : 0;
            $model->clue_id = isset($_POST['ContMergeForm']['clue_id']) ? $_POST['ContMergeForm']['clue_id'] : 0;
            
            if ($model->validate()) {
                $successCount = 0;
                $failedContracts = array();
                
                // 逐个处理源主合同的合并
                foreach ($model->source_cont_ids as $source_cont_id) {
                    $model->source_cont_id = $source_cont_id;
                    
                    if ($model->mergeSave()) {
                        $successCount++;
                    } else {
                        $failedContracts[] = $source_cont_id;
                    }
                }
                
                if ($successCount > 0) {
                    $message = "成功合并删除 {$successCount} 个主合同";
                    if (!empty($failedContracts)) {
                        $message .= "，失败：" . implode(',', $failedContracts);
                    }
                    Dialog::message(Yii::t('dialog','Information'), $message);
                    $this->redirect(Yii::app()->createUrl('contHead/detail', array('index'=>$model->target_cont_id)));
                } else {
                    $message = CHtml::errorSummary($model);
                    Dialog::message(Yii::t('dialog','Validation Message'), '所有主合同合并失败：'.$message);
                    $this->redirect(Yii::app()->createUrl('contHead/merge', array('clue_id'=>$model->clue_id)));
                }
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('contHead/merge', array('clue_id'=>$model->clue_id)));
            }
        } else {
            $this->redirect(Yii::app()->createUrl('contHead/index'));
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('CT01')||Yii::app()->user->validRWFunction('CM10')||Yii::app()->user->validRWFunction('CM02');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CM02');
    }
}
