<?php

class ManualSyncController extends Controller
{
	public $function_id='ZC03';
	
	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl',
		);
	}

	public function accessRules()
	{
		return array(
			array('allow', 
				'actions'=>array('index','getStoreList','getStorePersonList','getContractList','syncStore','syncStorePerson','syncContract','getUnsyncedClients'),
				'users'=>array('xiangsong'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex()
	{
		$this->render('index');
	}

	/**
	 * 获取门店列表
	 */
	public function actionGetStoreList()
	{
		if(Yii::app()->request->isAjaxRequest){
			$clue_id = isset($_POST['clue_id']) ? intval($_POST['clue_id']) : 0;
			if(empty($clue_id)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择客户'));
				Yii::app()->end();
			}

			$suffix = Yii::app()->params['envSuffix'];
			// 获取需要同步的门店（包括insert和update）
			$storeRows = Yii::app()->db->createCommand()
				->select("a.id,a.store_name,a.store_code,a.address,a.store_status,a.u_id")
				->from("sales{$suffix}.sal_clue_store a")
				->where("a.clue_id=:id",array(":id"=>$clue_id))
				->order("a.id ASC")
				->queryAll();

			$list = array();
			foreach($storeRows as $row){
				$list[] = array(
					'id' => $row['id'],
					'store_name' => $row['store_name'],
					'store_code' => $row['store_code'],
					'address' => $row['address'],
					'status' => empty($row['u_id']) ? '新增' : '更新',
					'u_id' => $row['u_id'],
					'store_status' => $row['store_status'],
				);
			}

			echo CJSON::encode(array(
				'status'=>1,
				'data'=>$list,
				'count'=>count($list)
			));
		}else{
			$this->redirect(Yii::app()->createUrl('manualSync/index'));
		}
	}

	/**
	 * 获取门店联络人列表
	 */
	public function actionGetStorePersonList()
	{
		if(Yii::app()->request->isAjaxRequest){
			$clue_id = isset($_POST['clue_id']) ? intval($_POST['clue_id']) : 0;
			if(empty($clue_id)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择客户'));
				Yii::app()->end();
			}

			$suffix = Yii::app()->params['envSuffix'];
			// 获取该客户下的所有门店
			$storeRows = Yii::app()->db->createCommand()
				->select("id,store_name,store_code,u_id")
				->from("sales{$suffix}.sal_clue_store")
				->where("clue_id=:id",array(":id"=>$clue_id))
				->queryAll();

			$list = array();
			foreach($storeRows as $storeRow){
				// 获取需要同步的门店联络人（手机号不为空）
				$personRows = Yii::app()->db->createCommand()
					->select("a.id,a.cust_person,a.cust_tel,a.cust_email,a.cust_person_role,a.u_id")
					->from("sales{$suffix}.sal_clue_person a")
					->where("a.clue_store_id=:id and a.cust_tel is not null and a.cust_tel != ''",array(":id"=>$storeRow['id']))
					->order("a.id ASC")
					->queryAll();

				foreach($personRows as $personRow){
					$list[] = array(
						'id' => $personRow['id'],
						'store_id' => $storeRow['id'],
						'store_name' => $storeRow['store_name'],
						'store_code' => $storeRow['store_code'],
						'person_name' => $personRow['cust_person'],
						'person_tel' => $personRow['cust_tel'],
						'person_email' => $personRow['cust_email'],
						'person_role' => $personRow['cust_person_role'],
						'status' => empty($personRow['u_id']) ? '新增' : '更新',
						'u_id' => $personRow['u_id'],
						'store_u_id' => $storeRow['u_id'], // 门店是否已同步
					);
				}
			}

			echo CJSON::encode(array(
				'status'=>1,
				'data'=>$list,
				'count'=>count($list)
			));
		}else{
			$this->redirect(Yii::app()->createUrl('manualSync/index'));
		}
	}

	/**
	 * 获取虚拟合约列表
	 */
	public function actionGetContractList()
	{
		if(Yii::app()->request->isAjaxRequest){
			$clue_id = isset($_POST['clue_id']) ? intval($_POST['clue_id']) : 0;
			if(empty($clue_id)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择客户'));
				Yii::app()->end();
			}

			$suffix = Yii::app()->params['envSuffix'];
			// 获取该客户下的所有合同
			$contRows = Yii::app()->db->createCommand()
				->select("a.id,a.cont_code,a.cont_start_dt,a.cont_end_dt")
				->from("sales{$suffix}.sal_contract a")
				->where("a.clue_id=:id",array(":id"=>$clue_id))
				->order("a.id ASC")
				->queryAll();

			$list = array();
			foreach($contRows as $contRow){
				// 获取该合同下的虚拟合约
				$virRows = Yii::app()->db->createCommand()
					->select("a.id,a.vir_code,a.u_id,a.vir_status")
					->from("sales{$suffix}.sal_contract_virtual a")
					->where("a.cont_id=:id",array(":id"=>$contRow['id']))
					->order("a.id ASC")
					->queryAll();

				if(!empty($virRows)){
					foreach($virRows as $virRow){
						$list[] = array(
							'id' => $virRow['id'],
							'cont_id' => $contRow['id'],
							'cont_code' => $contRow['cont_code'],
							'vir_code' => $virRow['vir_code'],
							'cont_start_dt' => $contRow['cont_start_dt'],
							'cont_end_dt' => $contRow['cont_end_dt'],
							'status' => empty($virRow['u_id']) ? '新增' : '更新',
							'u_id' => $virRow['u_id'],
							'vir_status' => $virRow['vir_status'],
						);
					}
				}
			}

			echo CJSON::encode(array(
				'status'=>1,
				'data'=>$list,
				'count'=>count($list)
			));
		}else{
			$this->redirect(Yii::app()->createUrl('manualSync/index'));
		}
	}

	/**
	 * 手动同步门店
	 */
	public function actionSyncStore()
	{
		if(Yii::app()->request->isAjaxRequest){
			$clue_id = isset($_POST['clue_id']) ? intval($_POST['clue_id']) : 0;
			$store_ids = isset($_POST['store_ids']) ? $_POST['store_ids'] : array();
			
			if(empty($clue_id)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择客户'));
				Yii::app()->end();
			}

			if(empty($store_ids) || !is_array($store_ids)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择要同步的门店'));
				Yii::app()->end();
			}

			$suffix = Yii::app()->params['envSuffix'];
			// 检查客户是否存在
			$clientRow = Yii::app()->db->createCommand()
				->select("*")
				->from("sales{$suffix}.sal_clue")
				->where("id=:id",array(":id"=>$clue_id))
				->queryRow();
			
			if(!$clientRow){
				echo CJSON::encode(array('status'=>0,'message'=>'客户不存在'));
				Yii::app()->end();
			}

			// 验证门店ID并获取u_id
			$storeIds = array_map('intval', $store_ids);
			$idSql = implode(',', $storeIds);
			$storeRows = Yii::app()->db->createCommand()
				->select("id,u_id")
				->from("sales{$suffix}.sal_clue_store")
				->where("id in ({$idSql}) and clue_id=:id",array(":id"=>$clue_id))
				->queryAll();

			if(empty($storeRows)){
				echo CJSON::encode(array('status'=>0,'message'=>'所选门店不存在或不属于该客户'));
				Yii::app()->end();
			}

			$insertStoreIds = array();
			$updateStoreIds = array();
			foreach($storeRows as $row){
				if(empty($row['u_id'])){
					$insertStoreIds[] = $row['id'];
				}else{
					$updateStoreIds[] = $row['id'];
				}
			}

			$totalCount = 0;

			// 同步新增门店（insert）
			if(!empty($insertStoreIds)){
				$uStoreModel = new CurlNotesByStore();
				$uStoreModel->operation_type = "insert";
				$uStoreModel->putAllStoreByStoreIDs($insertStoreIds);
				$uStoreModel->setOutContentByData();
				$uStoreModel->sendDataSetByAddStore();
				$uStoreModel->saveCurlToApi();
				$totalCount += count($insertStoreIds);
			}

			// 同步更新门店（update）
			if(!empty($updateStoreIds)){
				$uStoreModel = new CurlNotesByStore();
				$uStoreModel->operation_type = "update";
				$uStoreModel->putAllStoreByStoreIDs($updateStoreIds);
				$uStoreModel->setOutContentByData();
				$uStoreModel->sendDataSetByUpdateStore();
				$uStoreModel->saveCurlToApi();
				$totalCount += count($updateStoreIds);
			}

			$message = '门店同步任务已提交，共'.$totalCount.'个门店';
			if(!empty($insertStoreIds) && !empty($updateStoreIds)){
				$message .= '（新增：'.count($insertStoreIds).'个，更新：'.count($updateStoreIds).'个）';
			}

			echo CJSON::encode(array(
				'status'=>1,
				'message'=>$message,
				'count'=>$totalCount
			));
		}else{
			$this->redirect(Yii::app()->createUrl('manualSync/index'));
		}
	}

	/**
	 * 手动同步门店联络人
	 */
	public function actionSyncStorePerson()
	{
		if(Yii::app()->request->isAjaxRequest){
			$clue_id = isset($_POST['clue_id']) ? intval($_POST['clue_id']) : 0;
			$person_ids = isset($_POST['person_ids']) ? $_POST['person_ids'] : array();
			
			if(empty($clue_id)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择客户'));
				Yii::app()->end();
			}

			if(empty($person_ids) || !is_array($person_ids)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择要同步的门店联络人'));
				Yii::app()->end();
			}

			$suffix = Yii::app()->params['envSuffix'];
			// 检查客户是否存在
			$clientRow = Yii::app()->db->createCommand()
				->select("*")
				->from("sales{$suffix}.sal_clue")
				->where("id=:id",array(":id"=>$clue_id))
				->queryRow();
			
			if(!$clientRow){
				echo CJSON::encode(array('status'=>0,'message'=>'客户不存在'));
				Yii::app()->end();
			}

			// 验证联络人ID并分组
			$personIds = array_map('intval', $person_ids);
			$idSql = implode(',', $personIds);
			$personRows = Yii::app()->db->createCommand()
				->select("a.id,a.clue_store_id,a.u_id,b.u_id as store_u_id")
				->from("sales{$suffix}.sal_clue_person a")
				->leftJoin("sales{$suffix}.sal_clue_store b","a.clue_store_id=b.id")
				->where("a.id in ({$idSql}) and b.clue_id=:id and a.cust_tel is not null and a.cust_tel != ''",array(":id"=>$clue_id))
				->queryAll();

			if(empty($personRows)){
				echo CJSON::encode(array('status'=>0,'message'=>'所选联络人不存在或门店未同步'));
				Yii::app()->end();
			}

			// 按门店分组处理
			$storePersonMap = array();
			foreach($personRows as $personRow){
				$storeId = $personRow['clue_store_id'];
				if(empty($storePersonMap[$storeId])){
					$storePersonMap[$storeId] = array('insert'=>array(),'update'=>array());
				}
				if(empty($personRow['u_id'])){
					$storePersonMap[$storeId]['insert'][] = $personRow['id'];
				}else{
					$storePersonMap[$storeId]['update'][] = $personRow['id'];
				}
			}

			$insertCount = 0;
			$updateCount = 0;

			foreach($storePersonMap as $storeId => $personGroups){
				$nowStoreRow = Yii::app()->db->createCommand()
					->select("*")
					->from("sales{$suffix}.sal_clue_store")
					->where("id=:id",array(":id"=>$storeId))
					->queryRow();

				if(!$nowStoreRow || empty($nowStoreRow['u_id'])){
					continue; // 门店未同步，跳过
				}

				// 处理新增联络人
				if(!empty($personGroups['insert'])){
					$uStoreModel = new CurlNotesByStore();
					$data = array();
					foreach($personGroups['insert'] as $personId){
						$personRow = Yii::app()->db->createCommand()
							->select("*")
							->from("sales{$suffix}.sal_clue_person")
							->where("id=:id",array(":id"=>$personId))
							->queryRow();
						if($personRow){
							$personData = $uStoreModel->getPersonDataByPersonID($personId,$nowStoreRow);
							if(!empty($personData)){
								$data[] = $personData;
							}
						}
					}
					if(!empty($data)){
						$uStoreModel = new CurlNotesByStore();
						$uStoreModel->operation_type = "insert";
						$uStoreModel->data = array(
							"operation_type"=>"insert",
							"data"=>array(),
						);
						$uStoreModel->data["data"] = json_encode($data,JSON_UNESCAPED_UNICODE);
						$uStoreModel->setOutContentByData();
						$uStoreModel->sendDataSetByAddStorePerson();
						$uStoreModel->saveCurlToApi();
						$insertCount += count($data);
					}
				}

				// 处理更新联络人
				if(!empty($personGroups['update'])){
					$uStoreModel = new CurlNotesByStore();
					$data = array();
					foreach($personGroups['update'] as $personId){
						$personRow = Yii::app()->db->createCommand()
							->select("*")
							->from("sales{$suffix}.sal_clue_person")
							->where("id=:id",array(":id"=>$personId))
							->queryRow();
						if($personRow){
							$personData = $uStoreModel->getPersonDataByPersonID($personId,$nowStoreRow);
							if(!empty($personData)){
								$data[] = $personData;
							}
						}
					}
					if(!empty($data)){
						$uStoreModel = new CurlNotesByStore();
						$uStoreModel->operation_type = "update";
						$uStoreModel->data = array(
							"operation_type"=>"update",
							"data"=>array(),
						);
						$uStoreModel->data["data"] = json_encode($data,JSON_UNESCAPED_UNICODE);
						$uStoreModel->setOutContentByData();
						$uStoreModel->sendDataSetByUpdateStorePerson();
						$uStoreModel->saveCurlToApi();
						$updateCount += count($data);
					}
				}
			}

			$totalCount = $insertCount + $updateCount;

			if($totalCount == 0){
				echo CJSON::encode(array('status'=>1,'message'=>'没有需要同步的门店联络人'));
			}else{
				$message = '门店联络人同步任务已提交，共'.$totalCount.'个联络人';
				if($insertCount > 0 && $updateCount > 0){
					$message .= '（新增：'.$insertCount.'个，更新：'.$updateCount.'个）';
				}
				echo CJSON::encode(array(
					'status'=>1,
					'message'=>$message,
					'count'=>$totalCount
				));
			}
		}else{
			$this->redirect(Yii::app()->createUrl('manualSync/index'));
		}
	}

	/**
	 * 手动同步虚拟合约
	 */
	public function actionSyncContract()
	{
		if(Yii::app()->request->isAjaxRequest){
			$clue_id = isset($_POST['clue_id']) ? intval($_POST['clue_id']) : 0;
			$cont_ids = isset($_POST['cont_ids']) ? $_POST['cont_ids'] : array();
			
			if(empty($clue_id)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择客户'));
				Yii::app()->end();
			}

			if(empty($cont_ids) || !is_array($cont_ids)){
				echo CJSON::encode(array('status'=>0,'message'=>'请选择要同步的合同'));
				Yii::app()->end();
			}

			$suffix = Yii::app()->params['envSuffix'];
			// 检查客户是否存在
			$clientRow = Yii::app()->db->createCommand()
				->select("*")
				->from("sales{$suffix}.sal_clue")
				->where("id=:id",array(":id"=>$clue_id))
				->queryRow();
			
			if(!$clientRow){
				echo CJSON::encode(array('status'=>0,'message'=>'客户不存在'));
				Yii::app()->end();
			}

			// 验证合同ID
			$contIds = array_map('intval', $cont_ids);
			$idSql = implode(',', $contIds);
			$contRows = Yii::app()->db->createCommand()
				->select("id")
				->from("sales{$suffix}.sal_contract")
				->where("id in ({$idSql}) and clue_id=:id",array(":id"=>$clue_id))
				->queryAll();

			if(empty($contRows)){
				echo CJSON::encode(array('status'=>0,'message'=>'所选合同不存在或不属于该客户'));
				Yii::app()->end();
			}

			$totalCount = 0;
			foreach($contRows as $contRow){
				// 同步该合同下的虚拟合约
				$uVirModel = new CurlNotesByVir();
				$uVirModel->sendAllVirByContID($contRow['id']);
				$totalCount++;
			}

			if($totalCount == 0){
				echo CJSON::encode(array('status'=>1,'message'=>'没有需要同步的虚拟合约'));
			}else{
				echo CJSON::encode(array(
					'status'=>1,
					'message'=>'虚拟合约同步任务已提交，共'.$totalCount.'个合同',
					'count'=>$totalCount
				));
			}
		}else{
			$this->redirect(Yii::app()->createUrl('manualSync/index'));
		}
	}

	/**
	 * 获取未同步到派单系统的客户列表（u_id为空）
	 */
	public function actionGetUnsyncedClients()
	{
		if(Yii::app()->request->isAjaxRequest){
			$suffix = Yii::app()->params['envSuffix'];
			
			// 查询 u_id 为空的客户（table_type=2 表示客户，table_type=1 表示线索）
			$clients = Yii::app()->db->createCommand()
				->select("id,clue_code,cust_name,city,entry_date,lcd")
				->from("sales{$suffix}.sal_clue")
				->where("(u_id IS NULL OR u_id = '') AND table_type = 2")
				->order("id DESC")
				->limit(100) // 限制返回100条
				->queryAll();

			$list = array();
			foreach($clients as $client){
				$list[] = array(
					'id' => $client['id'],
					'clue_code' => $client['clue_code'],
					'cust_name' => $client['cust_name'],
					'city' => $client['city'],
					'entry_date' => $client['entry_date'],
					'lcd' => $client['lcd'],
				);
			}

			echo CJSON::encode(array(
				'status'=>1,
				'data'=>$list,
				'count'=>count($list)
			));
		}else{
			$this->redirect(Yii::app()->createUrl('manualSync/index'));
		}
	}
}

