<?php

class RankScoreForm extends CFormModel
{
	public $id;
	public $season;
    public $city;
    public $year;
    public $ranking;
    public $month;
    public $all;
    public $message;


	
	public function attributeLabels()
	{
		return array(
            'season'=>Yii::t('rank','Season'),
            'city'=>Yii::t('sales','City'),
            'year'=>Yii::t('rank','Year'),
            'month'=>Yii::t('rank','Month'),
            'all'=>Yii::t('rank','All'),
		);
	}

	public function rules()
	{
        return array(
            array('','required'),
            array('id,sale_day,','safe'),
        );
	}

    public function init() {
        $this->city ='';
        $this->season="";
//        $this->month=date("m");
//        $this->year=date("Y");
//        $this->staffs_desc = Yii::t('misc','All');
    }


	public function retrieveData($data)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $cities = City::model()->getDescendantList($data['city']);
        if(empty($cities)){
            $cities.="'".$data['city']."'";
        }else{
            $cities.=",'".$data['city']."'";
        }
        if(!empty($data['year'])&&$data['year']>0){
            $this->season='年度总分排行榜';
            $start=$data['year'].'-01-01';
            $end=$data['year'].'-12-31';
            $sql = "select city, username
				from sal_rank  
				where   city in ($cities)  and month<='$end' and month>='$start'   
			  	group by city, username
			";
            $records = Yii::app()->db->createCommand($sql)->queryAll();
            $this->message='-'.$data['year'].'年';
        }elseif(!empty($data['season'])&&$data['season']>0){
            $this->season='赛季总分排行榜';
            $sql = "select city, username
				from sal_rank  
				where  city in ($cities)    and  season='".$data['season']."'
			  	group by city, username   
			";
            $records = Yii::app()->db->createCommand($sql)->queryAll();
            $this->message='- 第'.$this->numToWord($data['season']).'赛季';
        }elseif(!empty($data['month'])&&$data['month']>0){
            $this->season='月度排行榜';
            $sql = "select city, username
				from sal_rank  
				where   city in ($cities)  and MONTH(month)='".$data['month']."'
			  	group by city, username   
			";
            $records = Yii::app()->db->createCommand($sql)->queryAll();
            $this->message='- '.$data['month'].'月';
        }else{
            $this->season='总分排行榜';
            $sql = "select city, username
				from sal_rank  
				where   city in ($cities)
			  	group by city, username   
			";
            $records = Yii::app()->db->createCommand($sql)->queryAll();
        }
       // var_dump($records);die();
        foreach ($records as $record) {
            if (strpos("/'CS'/'H-N'/'HK'/'TC'/'ZS1'/'TP'/'TY'/'KS'/'TN'/'XM'/'ZY'/'MO'/'RN'/'MY'/'WL'/'JMS'/'RW'/","'".$record['city']."'")===false) {
                $temp = array();
                $temp['user'] = $record['username'];
//            $sql = "select name from hr$suffix.hr_employee where id=(SELECT employee_id from hr$suffix.hr_binding WHERE user_id='".$record['username']."')";
//            $row = Yii::app()->db->createCommand($sql)->queryRow();
//            $temp['name']= $row!==false ? $row['name'] : $record['username'];
                $sql = "select a.name as city_name, b.name as region_name 
					from security$suffix.sec_city a
					left outer join security$suffix.sec_city b on a.region=b.code
					where a.code='" . $record['city'] . "'
				";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                $sql_name = "select name
					from hr$suffix.hr_employee a
				left outer join  hr$suffix.hr_binding b on b.employee_id=a.id			  
				where b.user_id='".$record['username']."'
				";
                $name = Yii::app()->db->createCommand($sql_name)->queryRow();
                $temp['name'] = $name['name'];
                $temp['city'] = $row !== false ? $row['city_name'] : $record['city'];



                if(!empty($data['year'])&&$data['year']>0){
                    $this->season='年度总分排行榜';
                    $start=$data['year'].'-01-01';
                    $end=$data['year'].'-12-31';
                    $sql_level="select now_score,all_score from sal_rank  where username='".$record['username']."' and month<='$end' and month>='$start'   order by id desc";
                    $level = Yii::app()->db->createCommand($sql_level)->queryAll();
                    $this->message='-'.$data['year'].'年';
                }elseif(!empty($data['season'])&&$data['season']>0){
                    $this->season='赛季总分排行榜';
                    $sql_level="select now_score,all_score from sal_rank  where username='".$record['username']."' and season='".$data['season']."'   order by id desc";
                    $level = Yii::app()->db->createCommand($sql_level)->queryAll();
                    $this->message='- 第'.$this->numToWord($data['season']).'赛季';
                }elseif(!empty($data['month'])&&$data['month']>0){
                    $this->season='月度排行榜';
                    $sql_level="select now_score,all_score from sal_rank  where username='".$record['username']."' and MONTH(month)='".$data['month']."'   order by id desc";
                    $level = Yii::app()->db->createCommand($sql_level)->queryAll();
                    $this->message='- '.$data['month'].'月';
                }else{
                    $this->season='总分排行榜';
                    $sql_level="select now_score,all_score from sal_rank  where username='".$record['username']."'   order by id desc";
                    $level = Yii::app()->db->createCommand($sql_level)->queryAll();
                }
                $sql = "select * from sal_level where start_fraction <='" . $level[0]['now_score'] . "' and end_fraction >='" . $level[0]['now_score'] . "'";
                $rank_name = Yii::app()->db->createCommand($sql)->queryRow();
                $rank=0;
                foreach ($level as $a){
                    $rank+=$a['all_score'];
                }

                $temp['rank']= $rank;
                $temp['level'] = $rank_name['level'];
                $this->ranking[] = $temp;
            }
        }
       // var_dump($this->ranking);die();
        if ($this->ranking){
            $last_names = array_column($this->ranking,'rank');
            array_multisort($last_names,SORT_DESC,$this->ranking);
        }

        //$models = array_slice($models, 0, 20);

        return true;


    }



	public function cityName($city){
        $suffix = Yii::app()->params['envSuffix'];
	    $sql="select name from security$suffix.sec_city  where code='$city'";
	    $name=Yii::app()->db->createCommand($sql)->queryScalar();
	    return $name;
    }



    public function season(){
        $sql = "select season from sal_season group by season";
        $row= Yii::app()->db->createCommand($sql)->queryAll();
        $season[0]='无';
        $i=5;
        foreach ($row as $a){
            $b=$i+1;
            if($i==12){
                $b=1;
            }
            $season[$a['season']]='第'.$this->numToWord($a['season']).'赛季('.$i.'-'.$b.'月)';
            $i=$i+2;
            if($i==13){
                $i=1;
            }
        }
        return $season;
    }

    public function numToWord($num)
    {
        $chiNum = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九');
        $chiUni = array('','十', '百', '千', '万', '亿', '十', '百', '千');
        $chiStr = '';
        $num_str = (string)$num;
        $count = strlen($num_str);
        $last_flag = true; //上一个 是否为0
        $zero_flag = true; //是否第一个
        $temp_num = null; //临时数字
        $chiStr = '';//拼接结果
        if ($count == 2) {//两位数
            $temp_num = $num_str[0];
            $chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];
            $temp_num = $num_str[1];
            $chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num];
        }else if($count > 2){
            $index = 0;
            for ($i=$count-1; $i >= 0 ; $i--) {
                $temp_num = $num_str[$i];
                if ($temp_num == 0) {
                    if (!$zero_flag && !$last_flag ) {
                        $chiStr = $chiNum[$temp_num]. $chiStr;
                        $last_flag = true;
                    }
                }else{
                    $chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;
                    $zero_flag = false;
                    $last_flag = false;
                }
                $index ++;
            }
        }else{
            $chiStr = $chiNum[$num_str[0]];
        }
        return $chiStr;
    }

    public function readExcel(){
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/readexcel.xlsx';
        $objPHPExcel = $objReader->load($path);
//print_r("<pre>");
//        print_r(count($model->record));
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $time=time();
        $str="templates/销售段位得分规则.xlsx";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$str.'"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }


	public function adjustRight() {
		return Yii::app()->user->validFunction('HD03');
	}
	
	public function voidRight() {
		return Yii::app()->user->validFunction('HD03');
	}

	public function isReadOnly() {
//		return ($this->scenario=='view'||$this->status=='V'||$this->posted||!empty($this->req_ref_no)||!empty($this->t3_doc_no));
		return ($this->scenario!='new'||$this->status=='V'||$this->posted||!empty($this->req_ref_no)||!empty($this->t3_doc_no));
	}
}
