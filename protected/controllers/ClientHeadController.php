<?php

class ClientHeadController extends Controller 
{
	public $function_id='CM10';

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
				'actions'=>array('new','edit','delete','save','backClientBox'),
				'expression'=>array('ClientHeadController','allowReadWrite'),
			),
		array('allow', 
			'actions'=>array('index','view','ajaxLoadService','ajaxLoadFlowAndStore','ajaxLoadReport','ajaxLoadContract','ajaxLoadStore','ajaxLoadPerson','ajaxLoadOperation','ajaxLoadInvoice','ajaxLoadUStaff','ajaxLoadUArea'),
			'expression'=>array('ClientHeadController','allowReadOnly'),
		),
			array('allow',
				'actions'=>array('new'),
				'expression'=>array('ClientHeadController','allowNew'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new ClientHeadList;
        $session = Yii::app()->session;
        $session["clueStoreDetail"]=4;//门店入口改成4
        $session["clueDetail"]="client";//详情的返回
		if (isset($_POST['ClientHeadList'])) {
			$model->attributes = $_POST['ClientHeadList'];
		} else {
			if (isset($session['criteria_ClientHeadList']) && !empty($session['criteria_ClientHeadList'])) {
				$criteria = $session['criteria_ClientHeadList'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionBackClientBox()
	{
		if (isset($_POST['ClientHeadForm'])) {
			$model = new ClientHeadForm('back');
            $model->attributes = $_POST['ClientHeadForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('clue','Back Done'));
				$this->redirect(Yii::app()->createUrl('clientHead/index'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionSave()
	{
		if (isset($_POST['ClientHeadForm'])) {
			$model = new ClientHeadForm($_POST['ClientHeadForm']['scenario']);
            $model->clue_type = isset($_POST['ClientHeadForm']["clue_type"])?$_POST['ClientHeadForm']["clue_type"]:1;
			$model->attributes = $_POST['ClientHeadForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('clientHead/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index,$service_id=0,$addStaff=0)
	{
		$model = new ClientHeadForm('view');
        if($addStaff==1){
            ClueForm::addExtraUserByMy($index);
        }
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
            $session = Yii::app()->session;
            $session["clueTable"]=2;//线索
            $session["clueStoreDetail"]=5;//门店入口改成5
            $clueDetail = isset($session["clueDetail"])?$session["clueDetail"]:"client";
		    $model->setClueServiceID($service_id);
			$this->render('detail',array('model'=>$model,'clueDetail'=>$clueDetail));
		}
	}
	
	public function actionNew($city,$clue_type)
	{
		$model = new ClientHeadForm('new');
        if(empty($city)||empty($clue_type)){
            Dialog::message(Yii::t('dialog','Warning'),"业务管理单元或线索类别不能为空");
            $this->redirect(Yii::app()->createUrl('clientHead/index'));
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
		$model = new ClientHeadForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new ClientHeadForm('delete');
		if (isset($_POST['ClientHeadForm'])) {
		    $model->clue_type = isset($_POST['ClientHeadForm']["clue_type"])?$_POST['ClientHeadForm']["clue_type"]:1;
			$model->attributes = $_POST['ClientHeadForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('clientHead/edit',array('index'=>$model->id)));
			} else {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('clientHead/index'));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('CM10');
	}

	public static function allowNew() {
		return Yii::app()->user->validRWFunction('CM10');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('CM10');
	}

	/**
	 * 异步加载商机tab
	 */
	public function actionAjaxLoadService()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			$model = new ClientHeadForm('view');
			if (!$model->retrieveData($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户不存在'));
				Yii::app()->end();
			}
			
			// 设置当前商机ID（即使没有也会显示默认tab结构）
			$model->setClueServiceID($service_id);
			
			// 使用原来的方法渲染商机卡片HTML，保持原有样式
			$html = ClueServiceForm::printClueServiceBox($this, $model);
			
			echo CJSON::encode(array(
				'status' => 1,
				'html' => $html
			));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载商机跟进记录和关联门店
	 */
	public function actionAjaxLoadFlowAndStore()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			$model = new ClientHeadForm('view');
			if (!$model->retrieveData($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户不存在'));
				Yii::app()->end();
			}
			
			// 设置当前商机ID（即使没有也会显示默认tab结构）
			$model->setClueServiceID($service_id);
			
			// 确保clueServiceRow已初始化
			if (!isset($model->clueServiceRow) || !is_array($model->clueServiceRow)) {
				$model->clueServiceRow = array(
					'id' => 0,
					'service_status' => 0,
					'total_amt' => 0
				);
			}
			
			$html = ClueFlowForm::printClueFlowAndStoreBox($this, $model);
			
			echo CJSON::encode(array(
				'status' => 1,
				'html' => $html ? $html : ''
			));
			Yii::app()->end();
		} catch (Exception $e) {
			Yii::log('actionAjaxLoadFlowAndStore error: '.$e->getMessage(), CLogger::LEVEL_ERROR, 'clientHead.ajax');
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage(),
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载方案报价tab
	 */
	public function actionAjaxLoadReport()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			$pageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			// 使用分页模型查询
			$reportModel = new ClientReportList();
			$reportModel->clue_id = $clue_id;
			$reportModel->searchKeyword = $searchKeyword;
			$reportModel->retrieveDataByPage($pageNum);
			
			$rows = array();
			if ($reportModel->attr) {
				foreach ($reportModel->attr as $row) {
					$rows[] = array(
						'clue_code' => $row['clue_code'],
						'cust_name' => $row['cust_name'],
						'clue_type' => CGetName::getClueTypeStr($row['clue_type']),
						'city' => General::getCityName($row['city']),
						'cust_class' => CGetName::getCustClassStrByKey($row['cust_class']),
						'cust_level' => CGetName::getCustLevelStrByKey($row['cust_level']),
						'clue_service_id' => $row['clue_service_id'],
						'busine_id_text' => CGetName::getBusineStrByText($row['busine_id_text']),
						'rpt_status' => CGetName::getRptStatusStrByKey($row['rpt_status']),
						'lcd' => $row['lcd'],
						'id' => $row['id'],
						'view_url' => Yii::app()->createUrl('clueRpt/edit', array('index' => $row['id'], 'type' => 1))
					);
				}
			}
			
			echo CJSON::encode(array(
				'status' => 1,
				'data' => $rows,
				'pageNum' => $reportModel->pageNum,
				'totalRow' => $reportModel->totalRow,
				'noOfPages' => $reportModel->noOfPages
			));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载合约信息tab
	 */
	public function actionAjaxLoadContract()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			$pageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			// 使用分页模型查询
			$contractModel = new ClientContractList();
			$contractModel->clue_id = $clue_id;
			$contractModel->searchKeyword = $searchKeyword;
			$contractModel->retrieveDataByPage($pageNum);
			
			$rows = array();
			if ($contractModel->attr) {
				foreach ($contractModel->attr as $row) {
					$storeList = CGetName::getClueStoreRowByStoreID($row['clue_store_id']);
					$rows[] = array(
						'vir_code' => $row['vir_code'],
						'store_name' => $storeList['store_name'],
						'city' => General::getCityName($storeList['city']),
						'busine_id_text' => CGetName::getBusineStrByText($row['busine_id_text']),
						'vir_status' => CGetName::getContVirStatusStrByKey($row['vir_status']),
						'sign_type' => CGetName::getSignTypeStrByKey($row['sign_type']),
						'sales_id' => CGetName::getEmployeeNameByKey($row['sales_id']),
						'year_amt' => $row['year_amt'],
						'sign_date' => $row['sign_date'],
						'cont_start_dt' => $row['cont_start_dt'],
						'cont_end_dt' => $row['cont_end_dt'],
						'first_date' => $row['first_date'],
						'id' => $row['id'],
						'detail_url' => Yii::app()->createUrl('virtualHead/detail', array('index' => $row['id']))
					);
				}
			}
			
			echo CJSON::encode(array(
				'status' => 1,
				'data' => $rows,
				'pageNum' => $contractModel->pageNum,
				'totalRow' => $contractModel->totalRow,
				'noOfPages' => $contractModel->noOfPages
			));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载客户门店tab
	 */
	public function actionAjaxLoadStore()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			$pageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			// 使用分页模型查询
			$storeModel = new ClientStoreList();
			$storeModel->clue_id = $clue_id;
			$storeModel->searchKeyword = $searchKeyword;
			$storeModel->retrieveDataByPage($pageNum);
			
			$rows = array();
			$updateBool = Yii::app()->user->validRWFunction('CM10');
			
			if ($storeModel->attr) {
				foreach ($storeModel->attr as $row) {
					$person = $row['cust_person'];
					$person .= !empty($row['cust_person_role']) ? " ({$row['cust_person_role']})" : "";
					$person .= !empty($row['cust_tel']) ? " {$row['cust_tel']}" : "";
					
					$rows[] = array(
						'store_code' => $row['store_code'],
						'store_name' => $row['store_name'],
						'cust_class' => CGetName::getCustClassStrByKey($row['cust_class']),
						'district' => CGetName::getDistrictStrByKey($row['district']),
						'address' => $row['address'],
						'person' => $person,
						'id' => $row['id'],
						'can_edit' => $updateBool
					);
				}
			}
			
			echo CJSON::encode(array(
				'status' => 1,
				'data' => $rows,
				'pageNum' => $storeModel->pageNum,
				'totalRow' => $storeModel->totalRow,
				'noOfPages' => $storeModel->noOfPages
			));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载联系人tab
	 */
	public function actionAjaxLoadPerson()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			$pageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			// 使用分页模型查询
			$personModel = new ClientPersonList();
			$personModel->clue_id = $clue_id;
			$personModel->searchKeyword = $searchKeyword;
			$personModel->retrieveDataByPage($pageNum);
			
			$rows = array();
			$updateBool = Yii::app()->user->validRWFunction('CM10');
			
			if ($personModel->attr) {
				foreach ($personModel->attr as $row) {
					$rows[] = array(
						'person_code' => $row['person_code'],
						'cust_person' => $row['cust_person'],
						'sex' => CGetName::getPersonSexStrByKey($row['sex']),
						'cust_person_role' => $row['cust_person_role'],
						'cust_tel' => $row['cust_tel'],
						'cust_email' => $row['cust_email'],
						'person_pws' => CGetName::getClientPersonPwsStrByKey($row['person_pws']),
						'id' => $row['id'],
						'can_edit' => $updateBool
					);
				}
			}
			
			echo CJSON::encode(array(
				'status' => 1,
				'data' => $rows,
				'pageNum' => $personModel->pageNum,
				'totalRow' => $personModel->totalRow,
				'noOfPages' => $personModel->noOfPages
			));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载操作记录tab
	 */
	public function actionAjaxLoadOperation()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			$pageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			// 使用分页模型查询
			$operationModel = new ClientOperationList();
			$operationModel->clue_id = $clue_id;
			$operationModel->searchKeyword = $searchKeyword;
			$operationModel->retrieveDataByPage($pageNum);
			
			$rows = array();
			if ($operationModel->attr) {
				foreach ($operationModel->attr as $row) {
					$username = empty($row['disp_name']) ? $row['lcu'] : $row['disp_name'];
					$rows[] = array(
						'username' => $username,
						'lcd' => $row['lcd'],
						'history_html' => $row['history_html']
					);
				}
			}
			
			echo CJSON::encode(array(
				'status' => 1,
				'data' => $rows,
				'pageNum' => $operationModel->pageNum,
				'totalRow' => $operationModel->totalRow,
				'noOfPages' => $operationModel->noOfPages
			));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载开票信息tab
	 */
	public function actionAjaxLoadInvoice()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			$list = CGetName::getClueInvoiceRows($clue_id);
			$rows = array();
			$updateBool = Yii::app()->user->validRWFunction('CM10');
			
			if ($list) {
				foreach ($list as $row) {
					$rows[] = array(
						'invoice_name' => $row['invoice_name'],
						'invoice_type' => CGetName::getInvoiceTypeStrByKey($row['invoice_type']),
						'invoice_header' => $row['invoice_header'],
						'tax_id' => $row['tax_id'],
						'invoice_address' => $row['invoice_address'],
						'invoice_number' => $row['invoice_number'],
						'invoice_user' => $row['invoice_user'],
						'z_display' => CGetName::getDisplayStrByKey($row['z_display']),
						'id' => $row['id'],
						'can_edit' => $updateBool
					);
				}
			}
			
			echo CJSON::encode(array('status' => 1, 'data' => $rows));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载项目负责人tab
	 */
	public function actionAjaxLoadUStaff()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			$pageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$searchKeyword = isset($_GET['search']) ? trim($_GET['search']) : '';
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			// 使用分页模型查询
			$staffModel = new ClientUStaffList();
			$staffModel->clue_id = $clue_id;
			$staffModel->searchKeyword = $searchKeyword;
			$staffModel->retrieveDataByPage($pageNum);
			
			$rows = array();
			$updateBool = Yii::app()->user->validRWFunction('CM10');
			
			if ($staffModel->attr) {
				foreach ($staffModel->attr as $row) {
					$employee_type = empty($row['employee_type']) ? Yii::t('clue', 'other u staff') : Yii::t('clue', 'local u staff');
					$rows[] = array(
						'employee_name' => CGetName::getEmployeeNameByKey($row['employee_id']),
						'employee_type' => $employee_type,
						'u_id' => $row['u_id'],
						'lcu' => $row['lcu'],
						'luu' => $row['luu'],
						'lcd' => $row['lcd'],
						'lud' => $row['lud'],
						'id' => $row['id'],
						'can_edit' => $updateBool
					);
				}
			}
			
			echo CJSON::encode(array(
				'status' => 1,
				'data' => $rows,
				'pageNum' => $staffModel->pageNum,
				'totalRow' => $staffModel->totalRow,
				'noOfPages' => $staffModel->noOfPages
			));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}

	/**
	 * 异步加载项目所属区域tab
	 */
	public function actionAjaxLoadUArea()
	{
		try {
			$clue_id = isset($_GET['clue_id']) ? intval($_GET['clue_id']) : 0;
			
			if (empty($clue_id)) {
				echo CJSON::encode(array('status' => 0, 'error' => '客户ID不能为空'));
				Yii::app()->end();
			}
			
			$list = CGetName::getClueUAreaRows($clue_id);
			$rows = array();
			$updateBool = Yii::app()->user->validRWFunction('CM10');
			
			if ($list) {
				foreach ($list as $row) {
					$city_type = empty($row['city_type']) ? Yii::t('clue', 'other u area') : Yii::t('clue', 'local u area');
					$rows[] = array(
						'city_code' => General::getCityName($row['city_code']),
						'city_type' => $city_type,
						'u_id' => $row['u_id'],
						'lcu' => $row['lcu'],
						'luu' => $row['luu'],
						'lcd' => $row['lcd'],
						'lud' => $row['lud'],
						'id' => $row['id'],
						'can_edit' => $updateBool
					);
				}
			}
			
			echo CJSON::encode(array('status' => 1, 'data' => $rows));
			Yii::app()->end();
		} catch (Exception $e) {
			echo CJSON::encode(array(
				'status' => 0,
				'error' => '加载失败: ' . $e->getMessage()
			));
			Yii::app()->end();
		}
	}
}
