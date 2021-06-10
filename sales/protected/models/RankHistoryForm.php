<?php

class RankHistoryForm extends CFormModel
{
	public $id;
	public $season;
	public $city;
	public $date;
    public $rank=array();
    public $lic;
    public $zhi_dt='2021-01-01';


	
	public function attributeLabels()
	{
		return array(
            'season'=>Yii::t('sales','Season'),
            'city'=>Yii::t('sales','City'),
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
        $this->city =Yii::app()->user->city();
        $this->season="";
//        $this->month=date("m");
//        $this->year=date("Y");
//        $this->staffs_desc = Yii::t('misc','All');
    }


	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql="select a.*,b.employee_name from sal_rank a
              left outer join hr$suffix.hr_binding b on b.user_id=a.username
              where b.id='$index' and a.city=b.city 
              ";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();

        if(!$rows){
            return true;
        }
        foreach ($rows as $v) {
            $month=date('m', strtotime( $v['month']));
            if (strpos("01.03.05.07.09.12", $month) === false) {
                $this->rank[] = $v;
            }
            if(end($rows)==$v&&count($rows)%2==1){
                $this->rank[] = $v;
            }
        }
        $i=1;
        if(!$this->rank){
            return true;
        }
        foreach ($this->rank as &$value){
            $a=$value['month'];
            if($i==1){
                $a=$value['month'];
                if (date('m', strtotime("$a "))%2==1){
                    $start=date('Y-m', strtotime("$a "));
                    $a = date('Y-m-01', strtotime("$a -1 month"));
                }else{
                    $start=date('Y-m', strtotime("$a -1 month"));
                }

                $stat_s=$this->numToWord($value['season']);
            }
            $end=date('Y-m', strtotime("$a +1 month"));
            $end_s=$this->numToWord($value['season']);
            $value['season']=$this->numToWord($value['season']);
            $sql_rank_name="select level from sal_level where start_fraction <='".$value['now_score']."' and end_fraction >='".$value['now_score']."'";
            $level= Yii::app()->db->createCommand($sql_rank_name)->queryScalar();

            $value['rank']=$level;
            $i=$i+1;
        }
        $this->lic=$start.'至'.$end.'（第'.$stat_s.'赛季）-（第'.$end_s.'赛季）';
        $this->rank=array_reverse($this->rank);

//        print_r('<pre>');
      //  print_r($this->rank);


		return true;
	}




	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveTrans($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	
	protected function saveTrans(&$connection) {
		$sql = '';
		switch ($this->scenario) {
//			case 'delete':
//				$sql = "update sal_integral set
//						sal_day = :sal_day,
//						luu = :luu
//						where id = :id and city = :city
//					";
//				break;
//			case 'new':
//				$sql = "insert into acc_trans(
//						trans_dt, trans_type_code, acct_id,	trans_desc, amount,	status, city, luu, lcu) values (
//						:trans_dt, :trans_type_code, :acct_id, :trans_desc, :amount, 'A', :city, :luu, :lcu)";
//				break;
			case 'edit':
				$sql = "update sal_integral set 
						sale_day = :sale_day	  				  
						where id = :id 
					";
				break;
		}

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':sale_day')!==false)
			$command->bindParam(':sale_day',$this->sale_day,PDO::PARAM_INT);
		$command->execute();
		return true;
	}

	public function cityName($city){
        $suffix = Yii::app()->params['envSuffix'];
	    $sql="select name from security$suffix.sec_city  where code='$city'";
	    $name=Yii::app()->db->createCommand($sql)->queryScalar();
	    return $name;
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

    public function season(){
        $sql = "select season from sal_season group by season";
        $row= Yii::app()->db->createCommand($sql)->queryAll();
        $season=array();
        foreach ($row as $a){
            $season[$a['season']]='第'.$this->numToWord($a['season']).'赛季';
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
		return Yii::app()->user->validFunction('HD01');
	}
	
	public function voidRight() {
		return Yii::app()->user->validFunction('HD01');
	}

	public function isReadOnly() {
//		return ($this->scenario=='view'||$this->status=='V'||$this->posted||!empty($this->req_ref_no)||!empty($this->t3_doc_no));
		return ($this->scenario!='new'||$this->status=='V'||$this->posted||!empty($this->req_ref_no)||!empty($this->t3_doc_no));
	}
}
