<?php

class SalesGroupController extends Controller
{
	public $function_id='HC13';
	
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
				'actions'=>array('edit','save','searchEmployee'),
				'expression'=>array('SalesGroupController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','searchEmployee'),
				'expression'=>array('SalesGroupController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


	public function actionSave()
	{
		if (isset($_POST['SalesGroupForm'])) {
			$model = new SalesGroupForm($_POST['SalesGroupForm']['scenario']);
			$model->attributes = $_POST['SalesGroupForm'];
			if ($model->validate()) {
				$model->saveData();
				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('salesGroup/edit'));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionIndex($index=1)
	{
        echo "index:{$index}<br/><br/><br/>";
        echo "CGetName::getGroupNextIDByID:<br/>";
        $deleteID=CGetName::getGroupNextIDByID($index);
        var_dump($deleteID);
        echo "<br/><br/>CGetName::getGroupStaffIDByStaffID:<br/>";
        $list=CGetName::getGroupStaffIDByStaffID($index);
        var_dump($list);
        Yii::app()->end();
	}

    public function actionSearchEmployee()
    {
        if (!self::allowReadOnly()) {
            echo CJSON::encode(array('results' => array()));
            Yii::app()->end();
        }

        if (!Yii::app()->request->isAjaxRequest) {
            throw new CHttpException(400, 'Bad Request');
        }

        $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
        if ($keyword === '' || mb_strlen($keyword, 'UTF-8') < 1) {
            echo CJSON::encode(array('results' => array()));
            Yii::app()->end();
        }

        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()
            ->select("id,code,name")
            ->from("hr{$suffix}.hr_employee")
            ->where("staff_status!=-1 and (name like :keyword or code like :keyword)", array(
                ":keyword" => "%{$keyword}%",
            ))
            ->order("table_type asc,id asc")
            ->limit(50)
            ->queryAll();

        $results = array();
        if ($rows) {
            foreach ($rows as $row) {
                $results[] = array(
                    "id" => $row["id"],
                    "text" => $row["name"] . " ({$row["code"]})",
                );
            }
        }

        echo CJSON::encode(array('results' => $results));
        Yii::app()->end();
    }

	public function actionView()
	{
		$model = new SalesGroupForm('view');
		if (!$model->retrieveData()) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit()
	{
		$model = new SalesGroupForm('edit');
		if(!self::allowReadWrite()){
		    $model->setScenario("view");
        }
		if (!$model->retrieveData()) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HC13');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HC13');
	}
}
