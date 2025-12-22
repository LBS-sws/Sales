<?php

class ClueStoreController extends Controller
{
	public $function_id='CM04';

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
				'actions'=>array('index','storeList','new','edit','view','detail','save','delete','ajaxShow','ajaxSave','transferStore','SearchTargetCustomer'),
				'expression'=>array('ClueStoreController','allowStoreReadWrite'),
			),
			array('allow',
				'actions'=>array('index','storeList','view','detail','ajaxShow'),
				'expression'=>array('ClueStoreController','allowStoreReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionAjaxShow($fast=0){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $html = "数据异常";
            if (isset($_POST['ClueStoreForm'])) {
                $model = new ClueStoreForm($_POST['ClueStoreForm']['scenario']);
                $model->attributes = $_POST['ClueStoreForm'];
                if($model->getScenario()!="new"){
                    $model->retrieveData($model->id);
                }else{
                    $model->create_staff = CGetName::getEmployeeIDByMy();
                }
                $clueModel = new ClueForm('view');
                if($clueModel->retrieveData($model->clue_id)){
                    $model->clueHeadRow = $clueModel->getAttributes();
                    if($model->getScenario()=="new"){
                        $model->yewudalei = $clueModel->yewudalei;
                    }
                    if($fast==1){
                        $model->fastDataByClueHead();
                    }
                    $html = $this->renderPartial('//clueStore/ajaxForm',array('model'=>$model),true);
                }
            }
            echo CJSON::encode(array('status'=>1,'html'=>$html,'title'=>"门店表单"));
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

    public function actionAjaxSave(){
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            if (isset($_POST['ClueStoreForm'])) {
                $model = new ClueStoreForm($_POST['ClueStoreForm']['scenario']);
                $model->attributes = $_POST['ClueStoreForm'];
                if ($model->validate()) {
                    $model->saveData();
                    echo CJSON::encode(array('status'=>1,'message'=>'保存成功','data'=>array('id'=>$model->id)));
                } else {
                    $message = CHtml::errorSummary($model);
                    echo CJSON::encode(array('status'=>0,'error'=>$message));
                }
            }
        }else{
            $this->redirect(Yii::app()->createUrl('site/index'));
        }
    }

	protected function resertFunCode(){
        $session = Yii::app()->session;
        if(isset($session["clueStoreDetail"])){
            $type = $session["clueStoreDetail"];
        }else{
            $type = 2;
            $session["clueStoreDetail"] = $type;
        }
        switch ($type){
            case 1:
                $funStr = "CM02";
                break;
            case 2:
                $funStr = "CM04";
                break;
            case 4://客户列表
                $funStr = "CM10";
                break;
            case 5://客户详情
                $funStr = "CM10";
                break;
            default:
                $funStr = "CM02";
        }
        $session["menu_code"]=$funStr;
        $session["active_func"]=$funStr;
        $this->function_id = $funStr;
    }

    public function actionIndex($pageNum=0)
    {
        $session = Yii::app()->session;
        $session["clueStoreDetail"]=2;//门店入口改成2
        $this->resertFunCode();
        $model = new ClueTopStoreList();
        if (isset($_POST['ClueTopStoreList'])) {
            $model->attributes = $_POST['ClueTopStoreList'];
        } else {
            if (isset($session['criteria_ClueTopStoreList']) && !empty($session['criteria_ClueTopStoreList'])) {
                $criteria = $session['criteria_ClueTopStoreList'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('top_index',array('model'=>$model));
    }

	public function actionStoreList($clue_id,$pageNum=0)
	{
        $session = Yii::app()->session;
	    $this->resertFunCode();
        $clueHeadModel = new ClueHeadForm('view');
        if($clueHeadModel->retrieveData($clue_id)){
            $model = new ClueStoreList;
            if (isset($_POST['ClueStoreList'])) {
                $model->attributes = $_POST['ClueStoreList'];
            } else {
                if (isset($session['criteria_ClueStore']) && !empty($session['criteria_ClueStore'])) {
                    $criteria = $session['criteria_ClueStore'];
                    $model->setCriteria($criteria);
                }
            }
            $model->determinePageNum($pageNum);
            $model->retrieveDataByClueAndPage($clueHeadModel->id,$model->pageNum);
            $this->render('index',array('model'=>$model,'clueHeadModel'=>$clueHeadModel));
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
	}

	public function actionNew($clue_id=0,$fast=0)
	{
        $this->resertFunCode();
        $clueHeadModel = new ClueHeadForm('view');
        if($clueHeadModel->retrieveData($clue_id)){
            $model = new ClueStoreForm('new');
            $model->clue_id = $clueHeadModel->id;
            $model->city = $clueHeadModel->city;
            $model->clueHeadRow = $clueHeadModel->getAttributes();
            $model->yewudalei = $clueHeadModel->yewudalei;
            if($fast==1){
                $model->fastDataByClueHead();
            }
            $this->render('form',array('model'=>$model));
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'), "请先选择线索或客户");
            $this->redirect(Yii::app()->createUrl('clueStore/index'));
        }
	}

    public function actionView($index)
    {
        $this->resertFunCode();
        $model = new ClueStoreForm('view');
        if($model->retrieveData($index)){
            $clueHeadModel = new ClueHeadForm('view');
            if (!$clueHeadModel->retrieveData($model->clue_id)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $model->clueHeadRow = $clueHeadModel->getAttributes();
                $this->render('form',array('model'=>$model));
            }
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionEdit($index)
    {
        $this->resertFunCode();
        $model = new ClueStoreForm('edit');
        if($model->retrieveData($index)){
            $clueHeadModel = new ClueHeadForm('view');
            if (!$clueHeadModel->retrieveData($model->clue_id)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $model->clueHeadRow = $clueHeadModel->getAttributes();
                $this->render('form',array('model'=>$model));
            }
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

    public function actionDetail($index)
    {
        $this->resertFunCode();
        $model = new ClueStoreForm('view');
        if($model->retrieveData($index)){
            $clueHeadModel = new ClueHeadForm('view');
            if (!$clueHeadModel->retrieveData($model->clue_id)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $model->clueHeadRow = $clueHeadModel->getAttributes();
                $this->render('detail',array('model'=>$model));
            }
        }else{
            throw new CHttpException(404,'The requested page does not exist.');
        }
    }

	public function actionSave()
	{
		if (isset($_POST['ClueStoreForm'])) {
            $this->resertFunCode();
			$model = new ClueStoreForm($_POST['ClueStoreForm']['scenario']);
            $model->attributes = $_POST['ClueStoreForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
            $this->render('form',array('model'=>$model));
		}
	}

	public function actionDelete()
	{
		if (isset($_POST['ClueStoreForm'])) {
		    $type = CGetName::getSessionByStore();
            $this->resertFunCode();
			$model = new ClueStoreForm('delete');
            $model->attributes = $_POST['ClueStoreForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                switch ($type){
                    case 4://客户列表
                    case 1://线索列表
                        $this->redirect(Yii::app()->createUrl('clueStore/storeList',array('clue_id'=>$model->clue_id)));
                        break;
                    case 2://门店列表
                        $this->redirect(Yii::app()->createUrl('clueStore/index'));
                        break;
                    case 5://客户详情
                        $this->redirect(Yii::app()->createUrl('clientHead/view',array('index'=>$model->clue_id)));
                        break;
                    default:
                        $this->redirect(Yii::app()->createUrl('clueHead/view',array('index'=>$model->clue_id)));
                }
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->redirect(Yii::app()->createUrl('clueStore/edit',array('index'=>$model->id)));
			}
		}
	}
	
	/**
	 * AJAX搜索目标客户
	 */
	public function actionSearchTargetCustomer()
	{
		if (!self::allowStoreReadWrite()) {
			echo CJSON::encode(array('status' => 0, 'results' => array()));
			Yii::app()->end();
		}

		if (Yii::app()->request->isAjaxRequest) {
			$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
			$storeId = isset($_POST['store_id']) ? intval($_POST['store_id']) : 0;

			// 获取当前门店的客户ID
			$storeRow = Yii::app()->db->createCommand()
				->select("clue_id")
				->from("sal_clue_store")
				->where("id=:id", array(":id" => $storeId))
				->queryRow();

			if (!$storeRow) {
				echo CJSON::encode(array('status' => 0, 'results' => array()));
				Yii::app()->end();
			}

			$currentClueId = $storeRow['clue_id'];
			$data = array('status' => 0, 'results' => array());

			if (!empty($keyword) && strlen($keyword) >= 1) {
				// 按客户名称或客户编码搜索
				$rows = Yii::app()->db->createCommand()
					->select("id, cust_name, clue_code")
					->from("sal_clue")
					->where("(cust_name LIKE :keyword OR clue_code LIKE :keyword) AND id != :current_id", array(
						":keyword" => "%{$keyword}%",
						":current_id" => $currentClueId
					))
					->order("id desc")
					->limit(10)
					->queryAll();

				if ($rows) {
					$data['status'] = 1;
					foreach ($rows as $row) {
						$data['results'][] = array(
							'id' => $row['id'],
							'name' => $row['cust_name'] . ' (' . $row['clue_code'] . ')'
						);
					}
				}
			}

			echo CJSON::encode($data);
			Yii::app()->end();
		} else {
			throw new CHttpException(400, 'Bad Request');
		}
	}

	/**
	 * 转移门店及其关联合约
	 */
	public function actionTransferStore()
	{
		if (!self::allowStoreTransfer()) {
			echo CJSON::encode(array('status' => false, 'message' => '您没有权限执行此操作'));
			Yii::app()->end();
		}

		if (Yii::app()->request->isPostRequest || Yii::app()->request->isAjaxRequest) {
			$model = new TransferStoreForm();
			$model->store_id = isset($_POST['store_id']) ? intval($_POST['store_id']) : 0;
			$model->target_clue_id = isset($_POST['target_clue_id']) ? intval($_POST['target_clue_id']) : 0;
			$model->transfer_reason = isset($_POST['transfer_reason']) ? trim($_POST['transfer_reason']) : '';

			$result = $model->executeTransfer();

			if (Yii::app()->request->isAjaxRequest) {
				echo CJSON::encode($result);
			} else {
				if ($result['status']) {
					Dialog::message(Yii::t('dialog', 'Information'), $result['message']);
					$this->redirect(Yii::app()->createUrl('clueStore/index'));
				} else {
					Dialog::message(Yii::t('dialog', 'Error'), $result['message']);
					$this->redirect(Yii::app()->createUrl('clueStore/detail', array('index' => $model->store_id)));
				}
			}
			Yii::app()->end();
		} else {
			throw new CHttpException(400, 'Bad Request');
		}
	}
	
	public static function allowStoreTransfer() {
		return Yii::app()->user->validRWFunction('CM11');
	}
	
	public static function allowStoreReadWrite() {
		return Yii::app()->user->validRWFunction('CM02')||Yii::app()->user->validRWFunction('CM10');
	}
	
	public static function allowStoreReadOnly() {
		return Yii::app()->user->validFunction('CM02')||Yii::app()->user->validFunction('CM10');
	}
}
