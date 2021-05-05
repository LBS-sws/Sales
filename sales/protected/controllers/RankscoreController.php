<?php

class RankscoreController extends Controller
{
	public $function_id='HD03';

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
				'expression'=>array('RankscoreController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('RankscoreController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


    public function actionIndex($pageNum=0)
    {
        $model = new RankScoreForm;
        $session = Yii::app()->session;
        $city=$this->city();

        $season=$model->season();
        $year=$this->year();
        $month=$this->month();
        $all=array(
            0=>'否',
            1=>'是',
        );
//        print_r('<$city>');
      //  print_r($season);
        $this->render('index',array('model'=>$model,'city'=>$city,'season'=>$season,'year'=>$year,'months'=>$month,'all'=>$all,));
    }
    public function month(){
        $sql = "select distinct  MONTH(month) as month from sal_rank order by month asc";
        $row= Yii::app()->db->createCommand($sql)->queryAll();
        $month[]='无';
        foreach ($row as $a){
            $month[$a['month']]=$a['month'].'月';
        }
      return $month;
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
            if (strpos("/'CS'/'H-N'/'HK'/'TC'/'ZS1'/'TP'/'TY'/'KS'/'TN'/'XM'/'ZY'/'MO'/'RN'/'MY'/'WL'/'JMS'/'RW'/","'".$k."'")===false) {
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

    public function year(){
        $start_date = '2021-01-01'; // 自动为00:00:00 时分秒
        $end_date = date("Y-m-d");
        $start_arr = explode("-", $start_date);
        $end_arr = explode("-", $end_date);
        $start_year = intval($start_arr[0]);
        $start_month = intval($start_arr[1]);
        $end_year = intval($end_arr[0]);
        $end_month = intval($end_arr[1]);
        $diff_year = $end_year-$start_year;
        $year_arr[]='无';
        for($year=$end_year;$year>=$start_year;$year--){
            $year_arr[$year] = $year;
        }
         return $year_arr;
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

	public function actionView()
	{
		$model = new RankScoreForm('view');
        if (isset($_POST['RankScoreForm'])) {
            $post = $_POST['RankScoreForm'];
            //判断选项有几个
            $i = 0;
            $j = sizeof( $post );
            foreach ( $post as $value ) {
                if ( $value > 0 ) {
                    $i++; //
                }
            }
            if ( $i != 1 ) {//不是1个的时候
		//if(($post['season']==0&&$post['year']==0&&$post['month']==0&&$post['all']==0)||($post['season']>0&&$post['year']>0&&$post['month']>0&&$post['all']>0)||($post['season']>0&&$post['year']>0&&$post['month']>0&&$post['all']>0)){
            Dialog::message(Yii::t('dialog','RankScoreForm'), Yii::t('dialog','You can only select one of them separately to view season data and annual data'));
            $this->redirect(Yii::app()->createUrl('rankscore/index',array('index'=>$model->id)));
        }else{
            if (!$model->retrieveData($post)) {
                throw new CHttpException(404,'The requested page does not exist.');
            } else {
                $this->render('form',array('model'=>$model,));
            }
        }
        }
	}

//    public function actionExcel()
//    {
//        $model = new RankScoreForm('view');
//        if (!$model->readExcel()) {
//            throw new CHttpException(404,'The requested page does not exist.');
//        } else {
//            $this->render('form',array('model'=>$model,));
//        }
//    }

	
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
		return Yii::app()->user->validRWFunction('HD03');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HD03');
	}
}
