<?php

class VisitController extends Controller 
{
	public $function_id='HK01';

	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
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
				'actions'=>array('new','edit','delete','save','searchcust','readcust','visited',
									'fileupload','fileremove','getcusttypelist','updatevip'
								),
				'expression'=>array('VisitController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload','report','listfile'),
				'expression'=>array('VisitController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
       $model = new VisitList;
		if (isset($_POST['VisitList'])) {
			$model->attributes = $_POST['VisitList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
                if(!empty($_GET['start'])){
                    $arr=$_GET;
                 //   $criteria['filter']='[{"field_id":"visit_dt","operator":">=","srchval":"'.$arr['start'].'"},{"field_id":"visit_dt","operator":"<=","srchval":"'.$arr['end'].'"},{"field_id":"visit_obj","operator":"like","srchval":"签单"},{"field_id":"city_name","operator":"=","srchval":"'.$arr['city'].'"},{"field_id":"staff","operator":"like","srchval":"'.$arr['sales'].'"}]';//这个是直接给session
                    $session['get']=$arr;
                }//根据这个变化
                //$criteria['filter']='[{"field_id":"staff","operator":"like","srchval":"5"}]';
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
        if(!empty($session['get'])){
            $model->retrieveDataByPage_visit($model->pageNum,$session['get']);
        }else{
            $model->retrieveDataByPage($model->pageNum);
        }
       // print_r($session['get']);
		$this->render('index',array('model'=>$model));
	}

	public function actionReport() {
		$model = new VisitList;
		$model->submitReport();
		Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Report submitted. Please go to Report Manager to retrieve the output.'));
		$this->redirect(Yii::app()->createUrl('visit/index'));
	}
	
	public function actionSave() {
		if (isset($_POST['VisitForm'])) {
			$model = new VisitForm($_POST['VisitForm']['scenario']);
			$model->attributes = $_POST['VisitForm'];
			if ($model->validate()) {
                $model->status = 'Y';
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('visit/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionVisited() {
		if (isset($_POST['VisitForm'])) {
			$model = new VisitForm($_POST['VisitForm']['scenario']);
			$model->attributes = $_POST['VisitForm'];
			if ($model->validate()) {
				$model->status = 'Y';
				$model->status_dt = date("Y-m-d H:m:s");
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('visit/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new VisitForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new VisitForm('new');
		$model->visit_dt=date("Y/m/d");
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new VisitForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new VisitForm('delete');
		if (isset($_POST['VisitForm'])) {
			$model->attributes = $_POST['VisitForm'];
			$model->saveData();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
			$this->redirect(Yii::app()->createUrl('visit/index'));
		}
	}
	
	public function actionSearchcust($term,$q,$_type,$page=1) {
		$rtn = '';
		$uid = Yii::app()->user->id;
		$sql = "select cust_name from sal_custcache where username='$uid' and cust_name like '%$term%'";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach($rows as $row) {
				$rtn .= (empty($rtn) ? '' : ',').'{"id":"'.$row['cust_name'].'","text":"'.$row['cust_name'].'"}';
			}
		}
		echo '{"results":['.$rtn.']}';
	}
	
	public function actionReadcust($name) {
		$uid = Yii::app()->user->id;
		$sql = "select f.remarks,f.visit_type,f.quotation,f.visit_obj,f.service_type,
                d.type_group as cust_type_group,
                a.cust_person, a.cust_person_role, a.cust_tel, a.district, a.street, a.cust_type, b.cust_vip  ,a.visit_id
				from sal_custcache a
				LEFT JOIN sal_visit f ON a.visit_id = f.id
				inner join sal_cust_type d on f.cust_type = d.id
				left outer join sal_custstar b on a.username=b.username and a.cust_name=b.cust_name
				where a.username='$uid' and a.cust_name='$name' ORDER BY a.lcd DESC 
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
        if(!empty($row)){
            $row["visit_obj"]=json_decode($row["visit_obj"]);
            $row["service_type"]=json_decode($row["service_type"]);
            $sql1="select field_id,field_value from sal_visit_info where visit_id='".$row['visit_id']."'";
            $arr = Yii::app()->db->createCommand($sql1)->queryALL();
            foreach ($arr as $a){
                $sale[$a['field_id']]=$a['field_value'];
            }
            $rows=array_merge($row,$sale);
        }
        //print_r('<pre/>');
		$rtn = ($row===false) ? '' : json_encode($rows);
		echo $rtn;
	}
	
	public function actionUpdatevip($id, $sts) {
		$uid = Yii::app()->user->id;
		$sql = "select cust_name from sal_visit where id=$id and username='$uid'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$sql = "insert into sal_custstar(username,cust_name,cust_vip) values(:uid, :cust_name, :cust_vip)
						on duplicate key update
						cust_vip = :cust_vip
				";
			$command=Yii::app()->db->createCommand($sql);
			if (strpos($sql,':uid')!==false)
				$command->bindParam(':uid',$uid,PDO::PARAM_STR);
			if (strpos($sql,':cust_name')!==false)
				$command->bindParam(':cust_name',$row['cust_name'],PDO::PARAM_STR);
			if (strpos($sql,':cust_vip')!==false)
				$command->bindParam(':cust_vip',$sts,PDO::PARAM_STR);
			$command->execute();
			echo $row['cust_name'];
		} else {
			echo 'NIL';
		}
	}
	
	public function actionFileupload($doctype) {
		$model = new VisitForm();
		if (isset($_POST['VisitForm'])) {
			$model->attributes = $_POST['VisitForm'];
			
			$id = ($_POST['VisitForm']['scenario']=='new') ? 0 : $model->id;
//			var_dump($_POST['VisitForm']['scenario']);
//			var_dump($id);
//			Yii::app()->end();
			$docman = new DocMan($doctype,$id,get_class($model));
			$docman->masterId = $model->docMasterId[strtolower($doctype)];
			if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
			$docman->fileUpload();
			echo $docman->genTableFileList(false);
		} else {
			echo "NIL";
		}
	}
	
	public function actionFileRemove($doctype) {
		$model = new VisitForm();
		if (isset($_POST['VisitForm'])) {
			$model->attributes = $_POST['VisitForm'];
			$docman = new DocMan($doctype,$model->id,get_class($model));
			$docman->masterId = $model->docMasterId[strtolower($doctype)];
			$docman->fileRemove($model->removeFileId[strtolower($doctype)]);
			echo $docman->genTableFileList(false);
		} else {
			echo "NIL";
		}
	}
	
	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
		$sql = "select city from sal_visit where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan($doctype,$docId,'VisitForm');
				$docman->masterId = $mastId;
				$docman->fileDownload($fileId);
			} else {
				throw new CHttpException(404,'Access right not match.');
			}
		} else {
				throw new CHttpException(404,'Record not found.');
		}
	}

	public function actionGetcusttypelist($group) {
		$rtn = '';
		$rows = VisitForm::getCustTypeList($group);
		foreach ($rows as $key=>$value) {
			$rtn .= "<option value=$key>$value</option>";
		}
		echo $rtn;
	}

    public function actionListfile($docId) {
        $d = new DocMan('VISIT',$docId,'VisitList');
        echo $d->genFileListView();
    }

	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HK01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HK01');
	}
}
