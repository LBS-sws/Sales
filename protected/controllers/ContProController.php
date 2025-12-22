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
                'actions'=>array('save','edit','new','delete','saveSeal','ajaxLoadSseStores'),
                'expression'=>array('ContProController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','compare','resetFile','ajaxLoadSseStores'),
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

    /**
     * 异步加载关联门店列表
     */
    public function actionAjaxLoadSseStores()
    {
        $pro_id = isset($_GET['pro_id']) ? intval($_GET['pro_id']) : 0;
        $cont_id = isset($_GET['cont_id']) ? intval($_GET['cont_id']) : 0;
        $pro_type = isset($_GET['pro_type']) ? trim($_GET['pro_type']) : '';
        $type = isset($_GET['type']) ? trim($_GET['type']) : '';

        if (empty($cont_id)) {
            header('Content-Type: application/json; charset=UTF-8');
            echo CJSON::encode(array('status' => 'error', 'message' => '合同ID不能为空'));
            Yii::app()->end();
        }

        header('Content-Type: application/json; charset=UTF-8');

        // 如果有pro_id，加载变更记录；否则根据cont_id新建
        if (!empty($pro_id)) {
            $model = new ContProForm('view');
            if (!$model->retrieveData($pro_id)) {
                echo CJSON::encode(array('status' => 'error', 'message' => '变更记录不存在'));
                Yii::app()->end();
            }
            $model->getUpdateClueServiceRow();
        } else {
            $model = new ContProForm('new');
            $model->cont_id = $cont_id;
            $model->pro_status = 0;
            $model->validateContID('cont_id', '');
            if ($model->hasErrors()) {
                $errors = $model->getErrors();
                $firstMsg = '加载失败';
                foreach ($errors as $fieldErrors) {
                    if (is_array($fieldErrors) && !empty($fieldErrors[0])) {
                        $firstMsg = $fieldErrors[0];
                        break;
                    }
                }
                echo CJSON::encode(array('status' => 'error', 'message' => $firstMsg));
                Yii::app()->end();
            }

            if (empty($pro_type) && !empty($type)) {
                $pro_type = $type;
            }

            if (!empty($pro_type)) {
                $model->pro_type = $pro_type;
                $model->getUpdateClueServiceRow();
            }
        }

        // 提取所有门店ID
        $storeIds = array();
        if (empty($pro_id) && empty($pro_type)) {
            $model->pro_type = 'C';
            $model->getUpdateClueServiceRow();
            $storeIdsCont = array();
            if (!empty($model->clueSSERow)) {
                foreach ($model->clueSSERow as $row) {
                    $storeIdsCont[] = $row['clue_store_id'];
                }
            }

            $model->pro_type = 'NA';
            $model->getUpdateClueServiceRow();
            $storeIdsNa = array();
            if (!empty($model->clueSSERow)) {
                foreach ($model->clueSSERow as $row) {
                    $storeIdsNa[] = $row['clue_store_id'];
                }
            }

            $storeIds = !empty($storeIdsCont) ? $storeIdsCont : $storeIdsNa;
        } else {
            if (!empty($model->clueSSERow)) {
                foreach ($model->clueSSERow as $row) {
                    $storeIds[] = $row['clue_store_id'];
                }
            }
        }

        // 使用分页模型查询
        $storeModel = new ContProStoreList();
        $storeModel->cont_id = $cont_id;
        $storeModel->pro_id = $pro_id;

        // 如果有门店ID限制，添加到查询条件
        $storeModel->storeIds = $storeIds;

        $storeModel->noOfItem = 999999;
        $storeModel->retrieveDataByPage(1);

        $storeList = array();
        foreach ($storeModel->attr as $storeInfo) {
            $storeList[] = array(
                'id' => $storeInfo['id'],
                'store_name' => $storeInfo['store_name'],
                'address' => $storeInfo['address'],
                'cust_person' => $storeInfo['cust_person'],
                'cust_tel' => $storeInfo['cust_tel'],
                'invoice_header' => $storeInfo['invoice_header'],
                'tax_id' => $storeInfo['tax_id'],
                'invoice_address' => $storeInfo['invoice_address'],
                'checked' => in_array($storeInfo['id'], $model->showStore)
            );
        }

        echo CJSON::encode(array(
            'status' => 'success',
            'data' => $storeList,
            'pageNum' => $storeModel->pageNum,
            'totalRow' => $storeModel->totalRow,
            'noOfPages' => $storeModel->noOfPages
        ));
        Yii::app()->end();
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
