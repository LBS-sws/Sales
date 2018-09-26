<?php
class RptCutList extends CReport {
	protected function fields() {
		return array(
			'employee_name'=>array('label'=>Yii::t('integral','Employee Name'),'width'=>22,'align'=>'L'),
            's_city'=>array('label'=>Yii::t('integral','City'),'width'=>20,'align'=>'L'),
			'gift_type'=>array('label'=>Yii::t('integral','Cut Name'),'width'=>30,'align'=>'L'),
			'bonus_point'=>array('label'=>Yii::t('integral','Cut Integral'),'width'=>25,'align'=>'C'),
            'apply_num'=>array('label'=>Yii::t('integral','Number of applications'),'width'=>20,'align'=>'L'),
            'integral_sum'=>array('label'=>Yii::t('integral','Cut Integral Sum'),'width'=>20,'align'=>'L'),
			'apply_date'=>array('label'=>Yii::t('integral','apply for time'),'width'=>15,'align'=>'L'),
		);
	}
	
	public function genReport() {
		$this->retrieveData();
		$this->title = $this->getReportName();
		$this->subtitle = Yii::t('integral','Cut activities Name').':'.$this->criteria['START_DT'].' - '.$this->criteria['END_DT'].' / '
			.Yii::t('report','Staffs').':'.$this->criteria['STAFFSDESC']
			;
		return $this->exportExcel();
	}

	public function retrieveData() {
        $start_dt = $this->criteria['START_DT'];
        $end_dt = $this->criteria['END_DT'];
		$city = $this->criteria['CITY'];
		$staff_id = $this->criteria['STAFFS'];
		
		$citymodel = new City();
		$citylist = $citymodel->getDescendantList($city);
		$citylist = empty($citylist) ? "'$city'" : "$citylist,'$city'";
		
		$suffix = Yii::app()->params['envSuffix'];

        $cond_time = "";
        if(!empty($start_dt)){
            $start_dt = date("Y-m-d",strtotime($start_dt));
            $cond_time.=" and a.apply_date>='$start_dt' ";
        }
        if(!empty($end_dt)){
            $end_dt = date("Y-m-d",strtotime($end_dt));
            $cond_time.=" and a.apply_date<='$end_dt' ";
        }

		$cond_staff = '';
		if (!empty($staff_id)) {
			$ids = explode('~',$staff_id);
			if(count($ids)>1){
                $cond_staff = implode(",",$ids);
            }else{
                $cond_staff = $staff_id;
            }
			if ($cond_staff!=''){
                $cond_staff = " and a.employee_id in ($cond_staff) ";
            } 
		}

        $sql = "select a.*,b.gift_name,d.name AS employee_name,d.city AS s_city from gr_gift_request a
                LEFT JOIN gr_gift_type b ON a.gift_type = b.id
                LEFT JOIN hr$suffix.hr_employee d ON a.employee_id = d.id
                where d.city in($citylist) 
                $cond_staff $cond_time
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$temp = array();
				$temp['employee_name'] = $row['employee_name'];
                $temp['s_city'] = CGeneral::getCityName($row['s_city']);
				$temp['gift_type'] = $row['gift_type'];
				$temp['bonus_point'] = $row['bonus_point'];
                $temp['apply_num'] = $row['apply_num'];
                $temp['integral_sum'] = intval($row['apply_num'])*intval($row['bonus_point']);
                $temp['apply_date'] = CGeneral::toDate($row['apply_date']);
				$this->data[] = $temp;
			}
		}
		return true;
	}
	
	public function getReportName() {
		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
	}
}
?>