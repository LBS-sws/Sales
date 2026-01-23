<?php
class IqueueController extends Controller
{
	public $function_id='XF01';

	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
		);
	}

	public function accessRules()
	{
		return array(
			array('allow', 
				'actions'=>array('index','downExcel','checkOrphan','cleanOrphan'),
				'expression'=>array('IqueueController','allowExecute'),
			),
			array('allow',
				'actions'=>array('remove'),
				'expression'=>array('IqueueController','allowRemove'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionRemove($index) {
		try {
			$model = new IqueueList('view');
			$result = $model->remove($index);
			if ($result !== false) {
				echo json_encode(array('status' => 'success', 'message' => $result));
			} else {
				echo json_encode(array('status' => 'error', 'message' => '删除失败：未找到记录或记录已被删除'));
			}
		} catch (Exception $e) {
			echo json_encode(array('status' => 'error', 'message' => '删除失败：' . $e->getMessage()));
		}
        Yii::app()->end();
    }
	
	public function actionCheckOrphan() {
		try {
			$connection = Yii::app()->db;
			
			// 检测孤立门店（门店的客户不存在）
			$orphanStores = $connection->createCommand()
				->select("COUNT(*) as cnt")
				->from("sal_clue_store a")
				->leftJoin("sal_clue b", "a.clue_id = b.id")
				->where("a.clue_id IS NOT NULL AND a.clue_id != 0 AND b.id IS NULL")
				->queryScalar();
			
			// 检测孤立主合同（合同的客户不存在）
			$orphanContracts = $connection->createCommand()
				->select("COUNT(*) as cnt")
				->from("sal_contract a")
				->leftJoin("sal_clue b", "a.clue_id = b.id")
				->where("a.clue_id IS NOT NULL AND a.clue_id != 0 AND b.id IS NULL")
				->queryScalar();
			
			// 检测孤立虚拟合约（虚拟合约的客户不存在）
			$orphanVirtuals = $connection->createCommand()
				->select("COUNT(*) as cnt")
				->from("sal_contract_virtual a")
				->leftJoin("sal_clue b", "a.clue_id = b.id")
				->where("a.clue_id IS NOT NULL AND a.clue_id != 0 AND b.id IS NULL")
				->queryScalar();
			
			// 检测孤立商机（商机的客户不存在）
			$orphanServices = $connection->createCommand()
				->select("COUNT(*) as cnt")
				->from("sal_clue_service a")
				->leftJoin("sal_clue b", "a.clue_id = b.id")
				->where("a.clue_id IS NOT NULL AND a.clue_id != 0 AND b.id IS NULL")
				->queryScalar();
			
			echo json_encode(array(
				'status' => 'success',
				'orphanStores' => intval($orphanStores),
				'orphanContracts' => intval($orphanContracts),
				'orphanVirtuals' => intval($orphanVirtuals),
				'orphanServices' => intval($orphanServices),
			));
		} catch (Exception $e) {
			echo json_encode(array(
				'status' => 'error',
				'message' => $e->getMessage()
			));
		}
		Yii::app()->end();
	}
	
	public function actionCleanOrphan() {
		try {
			$connection = Yii::app()->db;
			$transaction = $connection->beginTransaction();
			
			try {
				$deletedStores = 0;
				$deletedContracts = 0;
				$deletedVirtuals = 0;
				$deletedServices = 0;
				
				// 1. 删除孤立虚拟合约
				$orphanVirtualRows = $connection->createCommand()
					->select("a.id")
					->from("sal_contract_virtual a")
					->leftJoin("sal_clue b", "a.clue_id = b.id")
					->where("a.clue_id IS NOT NULL AND a.clue_id != 0 AND b.id IS NULL")
					->queryAll();
				if (!empty($orphanVirtualRows)) {
					$virIds = array();
					foreach($orphanVirtualRows as $row) {
						$virIds[] = $row['id'];
					}
					$virIdsStr = implode(',', $virIds);
					$connection->createCommand("DELETE FROM sal_contract_vir_info WHERE virtual_id IN ({$virIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contract_vir_staff WHERE vir_id IN ({$virIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contract_vir_week WHERE vir_id IN ({$virIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contpro_virtual WHERE vir_id IN ({$virIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=7 OR table_type=8) AND table_id IN ({$virIdsStr})")->execute();
					// 删除虚拟合约主表
					$connection->createCommand("DELETE FROM sal_contract_virtual WHERE id IN ({$virIdsStr})")->execute();
					$deletedVirtuals = count($orphanVirtualRows);
				}
				
				// 2. 删除孤立主合同
				$orphanContractRows = $connection->createCommand()
					->select("a.id")
					->from("sal_contract a")
					->leftJoin("sal_clue b", "a.clue_id = b.id")
					->where("a.clue_id IS NOT NULL AND a.clue_id != 0 AND b.id IS NULL")
					->queryAll();
				if (!empty($orphanContractRows)) {
					$contIds = array();
					foreach($orphanContractRows as $row) {
						$contIds[] = $row['id'];
					}
					$contIdsStr = implode(',', $contIds);
					$connection->createCommand("DELETE FROM sal_contpro WHERE cont_id IN ({$contIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contpro_virtual WHERE cont_id IN ({$contIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contract_file WHERE cont_id IN ({$contIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contpro_file WHERE cont_id IN ({$contIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contpro_sse WHERE cont_id IN ({$contIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contract_sse WHERE cont_id IN ({$contIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contract_call WHERE cont_id IN ({$contIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_contract_history WHERE (table_type=5 OR table_type=6) AND table_id IN ({$contIdsStr})")->execute();
					// 删除主合同主表
					$connection->createCommand("DELETE FROM sal_contract WHERE id IN ({$contIdsStr})")->execute();
					$deletedContracts = count($orphanContractRows);
				}
				
				// 3. 删除孤立门店
				$orphanStoreRows = $connection->createCommand()
					->select("a.id")
					->from("sal_clue_store a")
					->leftJoin("sal_clue b", "a.clue_id = b.id")
					->where("a.clue_id IS NOT NULL AND a.clue_id != 0 AND b.id IS NULL")
					->queryAll();
				if (!empty($orphanStoreRows)) {
					$storeIds = array();
					foreach($orphanStoreRows as $row) {
						$storeIds[] = $row['id'];
					}
					$storeIdsStr = implode(',', $storeIds);
					$connection->createCommand("DELETE FROM sal_clue_person WHERE clue_store_id IN ({$storeIdsStr}) AND clue_store_id!=0")->execute();
					$connection->createCommand("DELETE FROM sal_clue_sre_soe WHERE clue_store_id IN ({$storeIdsStr})")->execute();
					$connection->createCommand("DELETE FROM sal_clue_history WHERE table_type=2 AND table_id IN ({$storeIdsStr})")->execute();
					// 删除门店主表
					$connection->createCommand("DELETE FROM sal_clue_store WHERE id IN ({$storeIdsStr})")->execute();
					$deletedStores = count($orphanStoreRows);
				}
				
				// 4. 删除孤立商机
				$orphanServiceRows = $connection->createCommand()
					->select("a.id")
					->from("sal_clue_service a")
					->leftJoin("sal_clue b", "a.clue_id = b.id")
					->where("a.clue_id IS NOT NULL AND a.clue_id != 0 AND b.id IS NULL")
					->queryAll();
				if (!empty($orphanServiceRows)) {
					$serviceIds = array();
					foreach($orphanServiceRows as $row) {
						$serviceIds[] = $row['id'];
					}
					$serviceIdsStr = implode(',', $serviceIds);
					// 删除商机主表
					$connection->createCommand("DELETE FROM sal_clue_service WHERE id IN ({$serviceIdsStr})")->execute();
					$deletedServices = count($orphanServiceRows);
				}
				
				$transaction->commit();
				
				echo json_encode(array(
					'status' => 'success',
					'deletedStores' => $deletedStores,
					'deletedContracts' => $deletedContracts,
					'deletedVirtuals' => $deletedVirtuals,
					'deletedServices' => $deletedServices,
				));
			} catch (Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		} catch (Exception $e) {
			echo json_encode(array(
				'status' => 'error',
				'message' => $e->getMessage()
			));
		}
		Yii::app()->end();
	}

	public function actionIndex($pageNum=0) {
		$model = new IqueueList;
		if (isset($_POST['IqueueList'])) {
			$model->attributes = $_POST['IqueueList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_xf01']) && !empty($session['criteria_xf01'])) {
				$criteria = $session['criteria_xf01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionDownExcel($index,$type) {
        $row = Yii::app()->db->createCommand()->select("import_name,error_file,import_file")->from("sal_import_queue")
            ->where("id=:id",array(":id"=>$index))->queryRow();
        if($row){
			if($type!="error"){
                $file = $row["import_file"];
                $filename= iconv('utf-8','gbk//ignore',$row['import_name']);
                header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"); //for pdf or excel file
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                $handle = fopen($file,'r');
                fpassthru($handle);
                fclose($handle);
                die();
			}else{
                $file = $row["error_file"];
                $filename= iconv('utf-8','gbk//ignore',"导入失败");
                $filename.=".xlsx";
                header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"); //for pdf or excel file
                //header('Content-Type:text/plain; charset=ISO-8859-15');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                header('Content-Length: ' . strlen($file));
                echo $file;
                die();
			}
		}else{
            throw new CHttpException(404,'The requested page does not exist.');
		}
	}
	
	public static function allowExecute() {
		return Yii::app()->user->validFunction('XF01');
	}

	public static function allowRemove() {
		return Yii::app()->user->id=="xiangsong";
	}
}
?>
