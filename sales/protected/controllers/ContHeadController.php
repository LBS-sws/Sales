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
                'actions'=>array('save','edit','new','delete','saveSeal','sendU','sendNewU'),
                'expression'=>array('ContHeadController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','detail','resetContract','sendFile'),
                'expression'=>array('ContHeadController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionResetContract(){
        $suffix = Yii::app()->params['envSuffix'];
        $syncRows = Yii::app()->db->createCommand()->select("*")->from("datasync{$suffix}.sync_mh_api_curl")
            ->where("data_content like '%\"eventType\":\"taskCreate\"%\"contractStatus\":\"1\"%'")->queryAll();
        if($syncRows){
            foreach ($syncRows as $curlRow){
                $data = json_decode($curlRow['data_content'],true);
                $mh_id = key_exists("instId",$data)?$data["instId"]:0;
                switch ($curlRow['info_type']) {
                    case "cont":
                        $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contract")
                            ->where("mh_id=:id and cont_status<30 and cont_status>=10",array(":id"=>$mh_id))->queryRow();
                        if($row){
                            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract",array(
                                "cont_status"=>1,
                            ),"id=".$row["id"]);
                            $model = new ClueContModel();
                            $list = $model->syncChangeOne($data);

                            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contract",array(
                                "cont_status"=>$row["cont_status"],
                            ),"id=".$row["id"]);
                            echo "cont_".$row["id"]."<br/>";
                        }
                        break;
                    case "pro":
                        $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_contpro")
                            ->where("mh_id=:id and pro_status<30 and pro_status>=10",array(":id"=>$mh_id))->queryRow();
                        if($row){
                            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                                "pro_status"=>1,
                            ),"id=".$row["id"]);
                            $model = new ClueProModel();
                            $list = $model->syncChangeOne($data);

                            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_contpro",array(
                                "pro_status"=>$row["pro_status"],
                            ),"id=".$row["id"]);
                            echo "pro_".$row["id"]."<br/>";
                        }
                        break;
                    case "virPro":
                        $row = Yii::app()->db->createCommand()->select("*")->from("sales{$suffix}.sal_virtual_batch")
                            ->where("mh_id=:id and pro_status<30 and pro_status>=10",array(":id"=>$mh_id))->queryRow();
                        if($row){
                            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                                "pro_status"=>1,
                            ),"id=".$row["id"]);
                            $model = new ClueVirProModel();
                            $list = $model->syncChangeOne($data);

                            Yii::app()->db->createCommand()->update("sales{$suffix}.sal_virtual_batch",array(
                                "pro_status"=>$row["pro_status"],
                            ),"id=".$row["id"]);
                            echo "virPro_".$row["id"]."<br/>";
                        }
                        break;
                }
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

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('CT01')||Yii::app()->user->validRWFunction('CM10')||Yii::app()->user->validRWFunction('CM02');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CM02');
    }
}
