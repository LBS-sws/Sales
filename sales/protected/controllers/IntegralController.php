<?php

class IntegralController extends Controller
{
	public $function_id='HA06';

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
				'actions'=>array('new','edit','delete','save','downs','downsNew','test'),
				'expression'=>array('IntegralController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view'),
				'expression'=>array('IntegralController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionTest($month=6,$id=0,$year="2021")
    {
        $year = floatval($year);
        $month= floatval($month);
        var_dump("year:{$year}");
        echo "<br/>";
        var_dump("month:{$month}");
        echo "<br/>";
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("a.code,a.name,a.city,c.user_id")
            ->from("hr$suffix.hr_employee a")
            ->leftJoin("hr$suffix.hr_binding c","a.id = c.employee_id")
            ->where("a.id=:id",array(":id"=>$id))->queryRow();
        if($row){
            var_dump("staff:{$row['name']}({$row['code']}) - {$row['city']}");
            echo "<br/>";
            $id = Yii::app()->db->createCommand()->select("id")
                ->from("sal_integral")
                ->where("year=:year and month=:month and username=:username",
                    array(":year"=>$year,":month"=>$month,":username"=>$row["user_id"])
                )->queryScalar();
            if($id){
                var_dump("integral_id:{$id}");
                echo "<br/>";
                $model = new IntegralForm('view');
                $model->retrieveData($id);
                Yii::app()->db->createCommand()->update("sal_integral",array(
                    "point"=>$model['cust_type_name']['point'],
                    "all_sum"=>$model['cust_type_name']['all_sum']
                ),"id='$id'");
                echo "update success";
            }else{
                Yii::app()->db->createCommand()->insert("sal_integral",
                    array(
                        "year"=>$year,
                        "month"=>$month,
                        "username"=>$row["user_id"],
                        "city"=>$row["city"]
                    )
                );
                $id = Yii::app()->db->getLastInsertID();
                var_dump("integral_id:{$id}");
                echo "<br/>";
                echo "add success";
            }
        }else{
            echo "error staff_id";
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new IntegralList;
		if (isset($_POST['IntegralList'])) {
			$model->attributes = $_POST['IntegralList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['criteria_ha06']) && !empty($session['criteria_ha06'])) {
				$criteria = $session['criteria_ha06'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionView($index)
	{
		$model = new IntegralForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

	public function actionEdit($index)
	{
		$model = new IntegralForm('view');
		if (!$model->retrieveDataNew($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form2',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new IntegralForm('new');
		$this->render('form',array('model'=>$model,));
	}

    public function actionDowns($index)
    {
        $model = new IntegralForm('new');
        $model->retrieveData($index);
        $model->downEx($model);
    }

    public function actionDownsNew($index)
    {
        $model = new IntegralForm('new');
        $model->retrieveDataNew($index);
        $model->downExNew($model);
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HA06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HA06');
	}
}
