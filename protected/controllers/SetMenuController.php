<?php

class SetMenuController extends Controller
{
	public $function_id='HC21';

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
				'actions'=>array('new','edit','delete','save','resetPerson','sendPerson'),
				'expression'=>array('SetMenuController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('SetMenuController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionResetPerson(){
        $rows = Yii::app()->db->createCommand()->select("*")->from("sal_clue_person")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $personCode = ClueStorePersonForm::computeCodeX($row["clue_id"],$row["clue_store_id"],$row["id"]);
                Yii::app()->db->createCommand()->update("sal_clue_person",array(
                    "person_code"=>$personCode,
                ),"id=:id",array(":id"=>$row["id"]));
            }
        }
    }

	public function actionSendPerson(){
        $clientPerRows = Yii::app()->db->createCommand()->select("a.id as s_id,b.*")->from("sal_clue_person a")
            ->leftJoin("sal_clue b","a.clue_id=b.id")
            ->where("b.report_id is null and a.clue_store_id=0 and a.u_id is not null and b.u_id is not null")->queryAll();
        if($clientPerRows){
            $data = array();
            $uClientModel = new CurlNotesByClient();
            foreach ($clientPerRows as $clientPerRow){
                $data[]=$uClientModel->getPersonDataByPersonID($clientPerRow['s_id'],$clientPerRow);
            }
            $uClientModel->data=array(
                "operation_type"=>$uClientModel->operation_type,
                "data"=>array(),
            );
            $uClientModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uClientModel->setOutContentByData();
            $uClientModel->saveCurlToApi();
        }
        $storePerRows=Yii::app()->db->createCommand()->select("a.id as s_id,b.*")->from("sal_clue_person a")
            ->leftJoin("sal_clue_store b","a.clue_store_id=b.id")
            ->where("b.report_id is null and a.clue_store_id!=0 and a.u_id is not null and b.u_id is not null")->queryAll();
        if($storePerRows){
            $data=array();
            $uStoreModel = new CurlNotesByStore();
            foreach ($storePerRows as $storePerRow){
                $data[]=$uStoreModel->getPersonDataByPersonID($storePerRow['s_id'],$storePerRow);
            }
            $uStoreModel->data=array(
                "operation_type"=>$uStoreModel->operation_type,
                "data"=>array(),
            );
            $uStoreModel->data["data"]=json_encode($data,JSON_UNESCAPED_UNICODE);
            $uStoreModel->setOutContentByData();
            $uStoreModel->saveCurlToApi();
        }
        echo "count client:".count($clientPerRows).";count store".count($storePerRows);
        die();
    }

	public function actionIndex($pageNum=0)
	{
		$model = new SetMenuList;
		if (isset($_POST['SetMenuList'])) {
			$model->attributes = $_POST['SetMenuList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['setMenu_c01']) && !empty($session['setMenu_c01'])) {
				$criteria = $session['setMenu_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['SetMenuForm'])) {
			$model = new SetMenuForm($_POST['SetMenuForm']['scenario']);
			$model->attributes = $_POST['SetMenuForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('setMenu/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new SetMenuForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new SetMenuForm('new');
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new SetMenuForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new SetMenuForm('delete');
		if (isset($_POST['SetMenuForm'])) {
			$model->attributes = $_POST['SetMenuForm'];
			if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('setMenu/index'));
			} else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('setMenu/edit',array('index'=>$model->id)));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HC21');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HC21');
	}
}
