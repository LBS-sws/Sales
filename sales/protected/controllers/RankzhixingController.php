<?php

class RankzhixingController extends Controller
{
	public $function_id='HD09';

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
				'actions'=>array('newsaiji','newpeople','delete','save','index_s','fileremove','excel'),
				'expression'=>array('RankzhixingController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload'),
				'expression'=>array('RankzhixingController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}


    public function actionIndex($pageNum=0)
    {
        $model = new RankHistoryForm;
        $this->render('index',array('model'=>$model));
    }

	public function actionNewsaiji()
	{
        $suffix = Yii::app()->params['envSuffix'];
        $sql_season="select season from sales$suffix.sal_season order by id desc";
        $season = Yii::app()->db->createCommand($sql_season)->queryScalar();
        if(empty($season)){
            $sql="insert into sales$suffix.sal_season (season) value ('1')                   
             ";
            $command=Yii::app()->db->createCommand($sql)->execute();
        }else{
            $season=$season+1;
            $sql="insert into sales$suffix.sal_season (season) value ('$season')       
             ";
            $command=Yii::app()->db->createCommand($sql)->execute();
        }
	}


	public function actionNewpeople()
	{
        $post=$_POST['RankHistoryForm']['zhi_dt'];
        $date = $post;
        $month = date("Y-m-d", strtotime($date));
        $suffix = Yii::app()->params['envSuffix'];
        $sql_season="select season from sales$suffix.sal_season order by id desc";
        $season_s = Yii::app()->db->createCommand($sql_season)->queryScalar();
        $sql="select a.code
            from security$suffix.sec_city a left outer join security$suffix.sec_city b on a.code=b.region 
            where b.code is null 
            order by a.code";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $city = $row['code'];
                //查找所有参与的销售
                $sql1 = "select a.username			
				from security$suffix.sec_user a  
				left outer join security$suffix.sec_city b on a.city=b.code 			  
				left outer join security$suffix.sec_user_access c on a.username=c.username 	
				left outer join hr$suffix.hr_binding d  on a.username=d.user_id
				left outer join hr$suffix.hr_employee e  on d.employee_id=e.id		
                left outer join hr$suffix.hr_dept f  on e.position=f.id 	
			where a.city='$city'  and c.system_id='sal'  and c.a_read_write like '%HK01%'   and  f.manager_leave=1 and e.staff_status = 0
			";
                $rows = Yii::app()->db->createCommand($sql1)->queryAll();
                foreach ($rows as $records){
                    $star_time=date("Y-m-01", strtotime($date));//当前赛季開始时间
                    $end_time=date("Y-m-31", strtotime($date));//当前赛季結束时间
                    $sql="select username from sal_rank where month>='$star_time' and month<='$end_time'";
                    $row = Yii::app()->db->createCommand($sql)->queryAll();
                    $a=strpos(json_encode($row), json_encode($records));
                    if(empty($a)){
                        //  $city = $records['city'];
                        //判断是否为新入职的，空是新的、、最新赛季分数
                        $span="select a.*,c.rank_day,c.five_rank ,c.mie_rank ,c.three_rank  from sales$suffix.sal_rank a
                                  left outer join sal_rankday c on a.id=c.rank_id
                                  where city='$city' and  username='".$records['username']."' order by id desc";
                        $rankfraction = Yii::app()->db->createCommand($span)->queryRow();
                        if(empty($rankfraction)){
                            //  $rank=$rank+$ruzhi+$five;
                            //第几赛季，具体时间，用户，城市，初始分数或上赛季分数，当前赛季分数
                            $sql = "insert into sales$suffix.sal_rank(season, month, username, city) 
				                  values('$season_s', '$month', '".$records['username']."', '$city')
			                        ";
                            $command=Yii::app()->db->createCommand($sql)->execute();
                        }else{
                            if(empty($rankfraction['now_score'])){
                                $rankfraction['now_score']=0;
                            }
                            $sql="select * from sales$suffix.sal_rank where season='".$rankfraction['season']."' and username='".$records['username']."' and city='$city'";
                            $season = Yii::app()->db->createCommand($sql)->queryAll();
                            if(count($season)==1){
                                $sql2 = "insert into sales$suffix.sal_rank(season, month, username, city,last_score) 
				                  values('$season_s', '$month', '".$records['username']."', '$city','".$rankfraction['now_score']."')
			                        ";
                                $command=Yii::app()->db->createCommand($sql2)->execute();
                            }else{
                                $sql1="select * from sales$suffix.sal_level where start_fraction<='".$rankfraction['now_score']."' and end_fraction>='".$rankfraction['now_score']."'";
                                $record=Yii::app()->db->createCommand($sql1)->queryRow();
                                if(empty($record)){
                                    $record['new_fraction']=0;
                                }elseif($rankfraction['now_score']<9000){
                                    $record['new_fraction']=$rankfraction['now_score'];
                                }
                                $sql2 = "insert into sales$suffix.sal_rank(season, month, username, city,last_score) 
				                  values('$season_s', '$month', '".$records['username']."', '$city','".$record['new_fraction']."')
			                        ";
                                $command=Yii::app()->db->createCommand($sql2)->execute();
                            }
                        }
                        // 入职时间積分
                        $sql_entry_time="select a.* from hr$suffix.hr_employee a 
                                          left outer join hr$suffix.hr_binding b on a.id=b.employee_id                                 
                                          where b.user_id='".$records['username']."'
                                          ";
                        $entry_time = Yii::app()->db->createCommand($sql_entry_time)->queryRow();
                        $time1 = date("Y/m/d", strtotime("$date -1 month"));
                        $time2 = date("Y/m/d", strtotime("$date -3 month"));
                        $rank_id=Yii::app()->db->getLastInsertID();
                        $entry = date("Y/m/d", strtotime($entry_time['entry_time']));
                        if($entry<'2021/03/01'){
                            $sql_rank_day="insert into sal_rankday (employee_id,rank_day,rank_id) value ('".$entry_time['id']."',4,'$rank_id')";
                            $rankday=Yii::app()->db->createCommand($sql_rank_day)->execute();
                            $sql_rank_five="update sal_rankday set five_rank=2 where rank_id='$rank_id'";
                            $rankfive=Yii::app()->db->createCommand($sql_rank_five)->execute();
                            $sql_rank_mie="update sal_rankday set mie_rank=2 where rank_id='$rank_id'";
                            $rankmie=Yii::app()->db->createCommand($sql_rank_mie)->execute();
                            $sql_rank_three="update sal_rankday set three_rank=2 where rank_id='$rank_id'";
                            $rankthree=Yii::app()->db->createCommand($sql_rank_three)->execute();
                        }else{
                            if($time2>=$entry_time['entry_time']&&($rankfraction['rank_day']==0||empty($rankfraction['rank_day']))){
                                $sql_rank_day="insert into sal_rankday (employee_id,rank_day,rank_id) value ('".$entry_time['id']."',3,'$rank_id')";
                                $rankday=Yii::app()->db->createCommand($sql_rank_day)->execute();
                            }elseif($time2<$entry_time['entry_time']&&$entry_time['entry_time']<=$time1&&($rankfraction['rank_day']==0||empty($rankfraction['rank_day']))){
                                $sql_rank_day="insert into sal_rankday (employee_id,rank_day,rank_id) value ('".$entry_time['id']."',1,'$rank_id')";
                                $rankday=Yii::app()->db->createCommand($sql_rank_day)->execute();
                            }elseif($time2>=$entry_time['entry_time']&&($rankfraction['rank_day']==3||$rankfraction['rank_day']==2||$rankfraction['rank_day']==4)){
                                $sql_rank_day="insert into sal_rankday (employee_id,rank_day,rank_id) value ('".$entry_time['id']."',4,'$rank_id')";
                                $rankday=Yii::app()->db->createCommand($sql_rank_day)->execute();
                            }elseif($time2>=$entry_time['entry_time']&&$rankfraction['rank_day']==1){
                                $sql_rank_day="insert into sal_rankday (employee_id,rank_day,rank_id) value ('".$entry_time['id']."',2,'$rank_id')";
                                $rankday=Yii::app()->db->createCommand($sql_rank_day)->execute();
                            }
                            //老员工五部曲分数
                            if($rankfraction['five_rank']==1||$rankfraction['five_rank']==2){
                                $sql_rank_five="update sal_rankday set five_rank=2 where rank_id='$rank_id'";
                                $rankfive=Yii::app()->db->createCommand($sql_rank_five)->execute();
                            }
                            if($rankfraction['mie_rank']==1||$rankfraction['mie_rank']==2){
                                $sql_rank_five="update sal_rankday set mie_rank=2 where rank_id='$rank_id'";
                                $rankfive=Yii::app()->db->createCommand($sql_rank_five)->execute();
                            }
                            if($rankfraction['three_rank']==1||$rankfraction['three_rank']==2){
                                $sql_rank_five="update sal_rankday set three_rank=2 where rank_id='$rank_id'";
                                $rankfive=Yii::app()->db->createCommand($sql_rank_five)->execute();
                            }
                        }
                    }
                }
            }
        }
        print_r(1);
	}

    public function actionSave()
    {
        $post=$_POST['RankHistoryForm']['zhi_dt'];
        $date = $post;
        $suffix = Yii::app()->params['envSuffix'];
        $start = date("Y-m-01", strtotime($date));
        $end = date("Y-m-31",strtotime($date));
        $sql="select a.code
            from security$suffix.sec_city a left outer join security$suffix.sec_city b on a.code=b.region 
            where b.code is null 
            order by a.code";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $city = $row['code'];
                $model = new RankForm('view');
                $sql="select * from sal_rank  where month>='$start' and month<='$end' and city='$city'";
                $row = Yii::app()->db->createCommand($sql)->queryAll();
                foreach ($row as $id){
                    $model->retrieveData($id['id']);
                }
            }
        }
        print_r(2);
    }

    public function actionDelete()
    {
        $sql="delete from sal_rank where id>0 ";
        $command=Yii::app()->db->createCommand($sql)->execute();
        $sql1="delete from sal_rankday where id>0 ";
        $command=Yii::app()->db->createCommand($sql1)->execute();
        $sql2="delete from sal_season where id>0 ";
        $command=Yii::app()->db->createCommand($sql2)->execute();
        print_r(3);
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('HD09');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('HD09');
	}
}
