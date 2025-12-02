<?php

class RankController extends Controller
{
	public $function_id='HD01';

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
				'actions'=>array('new','edit','delete','save','index_s','fileremove','excel'),
				'expression'=>array('RankController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('RankController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


    public function actionIndex($pageNum=0)
    {
        $model = new RankForm;
        $city=$this->city();
        $season=$model->season();
        $this->render('index',array('model'=>$model,'city'=>$city,'season'=>$season,));
    }

    public function city(){
        $suffix = Yii::app()->params['envSuffix'];
        $model = new City();
        $id=Yii::app()->user->id;
        $sql="select city from security$suffix.sec_user where username='$id'";
        $city = Yii::app()->db->createCommand($sql)->queryScalar();
        $records=$model->getDescendant($city);
        array_unshift($records,$city);
        $cityname=array();
        foreach ($records as $v=>&$k) {
            if (strpos("/'CS'/'H-N'/'HK'/'TC'/'ZS1'/'TP'/'TY'/'KS'/'TN'/'XM'/'ZY'/'MO'/'RN'/'MY'/'WL'/'HN2'/'JMS'/'RW'/'HN1'/'HXHB'/'HD'/'HN'/'HD1'/'CN'/'HX'/'HB'/","'".$k."'")===false) {
                $sql = "select name from security$suffix.sec_city where code='" . $k . "'";
                $name = Yii::app()->db->createCommand($sql)->queryAll();
                $cityname[] = $name[0]['name'];
            }else{
                unset($records[$v]);
            }
        }
        $city=array_combine($records,$cityname);
        return $city;
    }

	public function actionIndex_s($pageNum=0)
	{
		$model = new RankList;
		if (isset($_POST['RankForm'])) {
            $a = $_POST['RankForm'];
            $model->attributes = $a;
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
				$model->setCriteria($criteria);
			}
		}
        if(!empty($_POST['RankForm']['city'])){
            $city=$_POST['RankForm']['city'];
            $season=$_POST['RankForm']['season'];
        }
        $session = Yii::app()->session;
        if(!empty($city)){
            $session['city']= $city;
            $session['season']= $season;

        }
        if(empty($city)){
            $a['city']=$session['city'];
            $a['season']=$session['season'];
        }
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum,$a);
		$this->render('index_s',array('model'=>$model,'a'=>$a));
	}


//	public function actionSave()
//	{
//		if (isset($_POST['RankForm'])) {
//			$model = new RankForm($_POST['RankForm']['scenario']);
//			$model->attributes = $_POST['RankForm'];
//			if ($model->validate()) {
//				$model->saveData();
//				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
//				$this->redirect(Yii::app()->createUrl('target/edit',array('index'=>$model->id)));
//			} else {
//				$message = CHtml::errorSummary($model);
//				Dialog::message(Yii::t('dialog','Validation Message'), $message);
//				$this->render('form',array('model'=>$model,));
//			}
//		}
//	}

	public function actionView($index)
	{
		$model = new RankForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

    public function actionExcel()
    {
        $model = new RankForm('view');
        if (!$model->readExcel()) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

	
//	public function actionEdit($index)
//	{
//		$model = new RankForm('edit');
//		if (!$model->retrieveData($index)) {
//			throw new CHttpException(404,'The requested page does not exist.');
//		} else {
//			$this->render('form',array('model'=>$model,));
//		}
//	}
	

	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HD01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HD01');
	}
}
