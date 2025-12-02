<?php

class ClueHeadController extends Controller 
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
				'actions'=>array('new','edit','delete','save','backClueBox'),
				'expression'=>array('ClueHeadController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('ClueHeadController','allowReadOnly'),
			),
			array('allow',
				'actions'=>array('ajaxCustName','ajaxChangeCustName','ajaxArea','ajaxNational','ajaxNationalSearch',
                    'getcusttypelist','ajaxYewudalei','ajaxAddDate'),
				'expression'=>array('ClueHeadController','allowAll'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ClueHeadList;
        $session = Yii::app()->session;
        $session["clueStoreDetail"]=1;//门店入口改成1
        $session["clueDetail"]="clue";//详情的返回为线索列表
		if (isset($_POST['ClueHeadList'])) {
			$model->attributes = $_POST['ClueHeadList'];
		} else {
			if (isset($session['criteria_ClueHeadList']) && !empty($session['criteria_ClueHeadList'])) {
				$criteria = $session['criteria_ClueHeadList'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionBackClueBox()
	{
		if (isset($_POST['ClueHeadForm'])) {
			$model = new ClueHeadForm('back');
            $model->clue_type = isset($_POST['ClueHeadForm']["clue_type"])?$_POST['ClueHeadForm']["clue_type"]:1;
            $model->attributes = $_POST['ClueHeadForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('clue','Back Done'));
				$this->redirect(Yii::app()->createUrl('clueHead/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionSave()
	{
		if (isset($_POST['ClueHeadForm'])) {
			$model = new ClueHeadForm($_POST['ClueHeadForm']['scenario']);
            $model->clue_type = isset($_POST['ClueHeadForm']["clue_type"])?$_POST['ClueHeadForm']["clue_type"]:1;
			$model->attributes = $_POST['ClueHeadForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('clueHead/view',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index,$service_id=0,$addStaff=0)
	{
		$model = new ClueHeadForm('view');
		if($addStaff==1){
		    ClueForm::addExtraUserByMy($index);
        }
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
            $session = Yii::app()->session;
            $session["clueStoreDetail"]=3;//门店入口改成3
            $session["clueTable"]=1;//线索
            $clueDetail = isset($session["clueDetail"])?$session["clueDetail"]:"head";
		    $model->setClueServiceID($service_id);
			$this->render('detail',array('model'=>$model,'clueDetail'=>$clueDetail));
		}
	}
	
	public function actionNew($city,$clue_type)
	{
		$model = new ClueHeadForm('new');
		if(empty($city)||empty($clue_type)){
            Dialog::message(Yii::t('dialog','Warning'),"业务管理单元或线索类别不能为空");
            $this->redirect(Yii::app()->createUrl('clueHead/index'));
        }else{
            $model->entry_date = date("Y/m/d");
            $model->city=$city;
            $model->clue_type=$clue_type;
            $model->rec_type=1;
            $model->rec_employee_id=CGetName::getEmployeeIDByMy();
            $model->login_employee_id=$model->rec_employee_id;
            $model->yewudalei=$clue_type==1?1:2;
            $this->render('form',array('model'=>$model,));
        }
	}
	
	public function actionEdit($index)
	{
		$model = new ClueHeadForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ClueHeadForm('delete');
		if (isset($_POST['ClueHeadForm'])) {
		    $model->clue_type = isset($_POST['ClueHeadForm']["clue_type"])?$_POST['ClueHeadForm']["clue_type"]:1;
			$model->attributes = $_POST['ClueHeadForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('clueHead/edit',array('index'=>$model->id)));
			} else {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('clueHead/index'));
			}
		}
	}

	public function actionAjaxCustName($city,$cust_name)
	{
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $clueHeadModel = new ClueHeadForm('view');
            $data = $clueHeadModel->ajaxBlurCustName($city,$cust_name);
            echo CJSON::encode($data);//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
	}

	public function actionAjaxChangeCustName($cust_name)
	{
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $clueHeadModel = new ClueHeadForm('view');
            $data = $clueHeadModel->ajaxChangeCustName($cust_name);
            echo CJSON::encode($data);//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
	}

	public function actionAjaxArea($city)
	{
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $htmlObj=array();
            $htmlObj["officeObj"]= TbHtml::dropDownList("bbb","",CGetName::getOfficeList($city));
            echo CJSON::encode($htmlObj);//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
	}

	public function actionAjaxNational()
	{
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $id=isset($_POST["id"])?$_POST["id"]:0;
            $type=isset($_POST["type"])?$_POST["type"]:1;
            $data = CGetName::getNationalListByType($type,$id);
            echo CJSON::encode($data);//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
	}

	public function actionAjaxNationalSearch()
	{
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $search=isset($_POST["search"])?$_POST["search"]:'';
            $city=isset($_POST["city"])?$_POST["city"]:'';
            $items = array();
            if(empty($search)){
                $items = CGetName::getNationalSearchItemByCity($city);
            }
            $clue_type=isset($_POST["clue_type"])?$_POST["clue_type"]:'';
            $data = CGetName::getNationalListBySearch($search,$clue_type,$items);
            echo CJSON::encode($data);//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
	}

	public function actionAjaxAddDate()
	{
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $date=isset($_POST["date"])?$_POST["date"]:'';
            $endDate = empty($date)?"":date("Y/m/d",strtotime("{$date} + 1 year - 1 day"));
            echo CJSON::encode(array("state"=>1,"endDate"=>$endDate));//Yii 的方法将数组处理成json数据
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
	}

	public function actionAjaxYewudalei($employee_id='')
	{
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $rtn = '';
            $rows = CGetName::getYewudaleiListByEmployee($employee_id);
            foreach ($rows as $key=>$value) {
                $rtn .= "<option value='{$key}'>{$value}</option>";
            }
            echo $rtn;
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
        Yii::app()->end();
	}

    public function actionGetcusttypelist($group) {
        $rtn = '';
        $rows = CGetName::getCustClassList($group);
        foreach ($rows as $key=>$value) {
            $rtn .= "<option value='{$key}'>{$value}</option>";
        }
        echo $rtn;
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CM02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CM02');
	}

	public static function allowAll() {
		return true;
	}
}
