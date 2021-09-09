<?php
class RptVisitList extends CReport {
	protected $readAll = false;
	
	protected function fields() {
		$field1 = array(
			'city_name'=>array('label'=>Yii::t('sales','City'),'width'=>15,'align'=>'L'),
			'staff'=>array('label'=>Yii::t('sales','Staff'),'width'=>20,'align'=>'L'),
			'post_name'=>array('label'=>Yii::t('sales','Position'),'width'=>20,'align'=>'L'),
			'dept_name'=>array('label'=>Yii::t('sales','Department'),'width'=>20,'align'=>'L'),
		);
		
		$field2 = array(
			'visit_type'=>array('label'=>Yii::t('sales','Type'),'width'=>15,'align'=>'L'),
			'visit_obj'=>array('label'=>Yii::t('sales','Objective'),'width'=>25,'align'=>'L'),
			'district'=>array('label'=>Yii::t('sales','District'),'width'=>20,'align'=>'L'),
			'street'=>array('label'=>Yii::t('sales','Street'),'width'=>20,'align'=>'L'),
			'visit_dt'=>array('label'=>Yii::t('sales','Visit Date'),'width'=>15,'align'=>'C'),
			'cust_name'=>array('label'=>Yii::t('sales','Customer Name'),'width'=>25,'align'=>'L'),
			'cust_alt_name'=>array('label'=>Yii::t('sales','Branch Name (if any)'),'width'=>25,'align'=>'L'),
			'cust_type'=>array('label'=>Yii::t('sales','Customer Type'),'width'=>20,'align'=>'L'),
			'cust_vip'=>array('label'=>Yii::t('sales','VIP'),'width'=>10,'align'=>'C'),
			'cust_person'=>array('label'=>Yii::t('sales','Resp. Person'),'width'=>20,'align'=>'L'),
			'cust_person_role'=>array('label'=>Yii::t('sales','Role'),'width'=>20,'align'=>'L'),
			'cust_tel'=>array('label'=>Yii::t('sales','Phone'),'width'=>15,'align'=>'L'),
			'remarks'=>array('label'=>Yii::t('sales','Remarks'),'width'=>30,'align'=>'L'),
			
			'svc_A'=>array('label'=>Yii::t('sales','Monthly Amount'),'width'=>10,'align'=>'C'),
            'svc_A10'=>array('label'=>Yii::t('sales','安装费'),'width'=>10,'align'=>'C'),
			'svc_A1'=>array('label'=>Yii::t('sales','马桶'),'width'=>10,'align'=>'C'),
			'svc_A2'=>array('label'=>Yii::t('sales','尿斗'),'width'=>10,'align'=>'C'),
			'svc_A3'=>array('label'=>Yii::t('sales','水盆'),'width'=>10,'align'=>'C'),
			'svc_A4'=>array('label'=>Yii::t('sales','清新机'),'width'=>10,'align'=>'C'),
			'svc_A5'=>array('label'=>Yii::t('sales','皂液机'),'width'=>10,'align'=>'C'),
            'svc_A9'=>array('label'=>Yii::t('sales','雾化消毒'),'width'=>30,'align'=>'C'),
			'svc_A6'=>array('label'=>Yii::t('sales','预估成交率').'(0-100%)','width'=>10,'align'=>'C'),
			'svc_A7'=>array('label'=>Yii::t('sales','合同年金额'),'width'=>10,'align'=>'C'),
			'svc_A8'=>array('label'=>Yii::t('sales','备注'),'width'=>30,'align'=>'L'),

			
			'svc_B'=>array('label'=>Yii::t('sales','Monthly Amount'),'width'=>10,'align'=>'C'),
			'svc_B1'=>array('label'=>Yii::t('sales','风扇机'),'width'=>10,'align'=>'C'),
			'svc_B2'=>array('label'=>Yii::t('sales','TC豪华'),'width'=>10,'align'=>'C'),
			'svc_B3'=>array('label'=>Yii::t('sales','水性喷机'),'width'=>10,'align'=>'C'),
			'svc_B4'=>array('label'=>Yii::t('sales','压缩香罐'),'width'=>10,'align'=>'C'),
			'svc_B5'=>array('label'=>Yii::t('sales','预估成交率').'(0-100%)','width'=>10,'align'=>'C'),
			'svc_B6'=>array('label'=>Yii::t('sales','合同年金额'),'width'=>10,'align'=>'C'),
			'svc_B7'=>array('label'=>Yii::t('sales','备注'),'width'=>30,'align'=>'L'),
			
			'svc_C'=>array('label'=>Yii::t('sales','Monthly Amount'),'width'=>10,'align'=>'C'),
            'svc_C10'=>array('label'=>Yii::t('sales','安装费'),'width'=>10,'align'=>'C'),
			'svc_C1'=>array('label'=>Yii::t('sales','服务面积'),'width'=>10,'align'=>'C'),
			'svc_C2'=>array('label'=>Yii::t('sales','老鼠'),'width'=>10,'align'=>'C'),
			'svc_C3'=>array('label'=>Yii::t('sales','蟑螂'),'width'=>10,'align'=>'C'),
			'svc_C4'=>array('label'=>Yii::t('sales','果蝇'),'width'=>10,'align'=>'C'),
			'svc_C5'=>array('label'=>Yii::t('sales','租灭蝇灯'),'width'=>10,'align'=>'C'),
            'svc_C9'=>array('label'=>Yii::t('sales','焗雾'),'width'=>10,'align'=>'C'),
			'svc_C6'=>array('label'=>Yii::t('sales','预估成交率').'(0-100%)','width'=>10,'align'=>'C'),
			'svc_C7'=>array('label'=>Yii::t('sales','合同年金额'),'width'=>10,'align'=>'C'),
			'svc_C8'=>array('label'=>Yii::t('sales','备注'),'width'=>30,'align'=>'L'),
			
			'svc_D'=>array('label'=>Yii::t('sales','Monthly Amount'),'width'=>10,'align'=>'C'),
			'svc_D1'=>array('label'=>Yii::t('sales','迷你小机'),'width'=>10,'align'=>'C'),
			'svc_D2'=>array('label'=>Yii::t('sales','小机'),'width'=>10,'align'=>'C'),
			'svc_D3'=>array('label'=>Yii::t('sales','中机'),'width'=>10,'align'=>'C'),
			'svc_D4'=>array('label'=>Yii::t('sales','大机'),'width'=>10,'align'=>'C'),
			'svc_D5'=>array('label'=>Yii::t('sales','预估成交率').'(0-100%)','width'=>10,'align'=>'C'),
			'svc_D6'=>array('label'=>Yii::t('sales','合同年金额'),'width'=>10,'align'=>'C'),
			'svc_D7'=>array('label'=>Yii::t('sales','备注'),'width'=>30,'align'=>'L'),

			'svc_H'=>array('label'=>Yii::t('sales','Monthly Amount'),'width'=>10,'align'=>'C'),
			'svc_H1'=>array('label'=>Yii::t('sales','类别'),'width'=>10,'align'=>'C'),
			'svc_H4'=>array('label'=>Yii::t('sales','延长维保'),'width'=>10,'align'=>'C'),
			'svc_H2'=>array('label'=>Yii::t('sales','RA488'),'width'=>10,'align'=>'C'),
			'svc_H3'=>array('label'=>Yii::t('sales','RA800'),'width'=>10,'align'=>'C'),
			'svc_H5'=>array('label'=>Yii::t('sales','预估成交率').'(0-100%)','width'=>10,'align'=>'C'),
			'svc_H6'=>array('label'=>Yii::t('sales','合同年金额'),'width'=>10,'align'=>'C'),
			'svc_H7'=>array('label'=>Yii::t('sales','备注'),'width'=>30,'align'=>'L'),
			
			'svc_E'=>array('label'=>Yii::t('sales','Monthly Amount'),'width'=>10,'align'=>'C'),
			'svc_E1'=>array('label'=>Yii::t('sales','服务面积'),'width'=>10,'align'=>'C'),
			'svc_E2'=>array('label'=>Yii::t('sales','除甲醛'),'width'=>10,'align'=>'C'),
			'svc_E3'=>array('label'=>Yii::t('sales','AC30'),'width'=>10,'align'=>'C'),
			'svc_E4'=>array('label'=>Yii::t('sales','PR10'),'width'=>10,'align'=>'C'),
			'svc_E5'=>array('label'=>Yii::t('sales','迷你清洁炮'),'width'=>10,'align'=>'C'),
			'svc_E6'=>array('label'=>Yii::t('sales','预估成交率').'(0-100%)','width'=>10,'align'=>'C'),
			'svc_E7'=>array('label'=>Yii::t('sales','合同年金额'),'width'=>10,'align'=>'C'),
			'svc_E8'=>array('label'=>Yii::t('sales','备注'),'width'=>30,'align'=>'L'),
			
			'svc_F1'=>array('label'=>Yii::t('sales','擦手纸价'),'width'=>10,'align'=>'C'),
			'svc_F2'=>array('label'=>Yii::t('sales','大卷厕纸价'),'width'=>10,'align'=>'C'),
			'svc_F4'=>array('label'=>Yii::t('sales','合同金额'),'width'=>10,'align'=>'C'),
			'svc_F3'=>array('label'=>Yii::t('sales','备注'),'width'=>30,'align'=>'L'),
			
			'svc_G3'=>array('label'=>Yii::t('sales','合同金额'),'width'=>10,'align'=>'C'),
			'svc_G1'=>array('label'=>Yii::t('sales','种类'),'width'=>10,'align'=>'C'),
			'svc_G2'=>array('label'=>Yii::t('sales','备注'),'width'=>30,'align'=>'L'),
		);
		return ($this->readAll ? array_merge($field1, $field2) : $field2);
	}
	
	public function header_structure() {
		$header1 = array(
			'city_name',
			'staff',
			'post_name',
			'dept_name',
		);

		$header2 = array(
			'visit_type',
			'visit_obj',
			'district',
			'street',
			'visit_dt',
			'cust_name',
			'cust_alt_name',
			'cust_type',
			'cust_vip',
			'cust_person',
			'cust_person_role',
			'cust_tel',
			'remarks',
			
			array(
				'label'=>Yii::t('sales','清洁').Yii::t('sales','报价'),
				'child'=>array(
					'svc_A',
                    'svc_A10',
					'svc_A1',
					'svc_A2',
					'svc_A3',
					'svc_A4',
					'svc_A5',
                    'svc_A9',
					'svc_A6',
					'svc_A7',
					'svc_A8',
				),
			),
			
			array(
				'label'=>Yii::t('sales','租赁机器').Yii::t('sales','报价'),
				'child'=>array(
					'svc_B',
					'svc_B1',
					'svc_B2',
					'svc_B3',
					'svc_B4',
					'svc_B5',
					'svc_B6',
					'svc_B7',
				),
			),
			
			array(
				'label'=>Yii::t('sales','灭虫').Yii::t('sales','报价'),
				'child'=>array(
					'svc_C',
                    'svc_C10',
					'svc_C1',
					'svc_C2',
					'svc_C3',
					'svc_C4',
					'svc_C5',
                    'svc_C9',
					'svc_C6',
					'svc_C7',
					'svc_C8',

				),
			),
			
			array(
				'label'=>Yii::t('sales','飘盈香').Yii::t('sales','报价'),
				'child'=>array(
					'svc_D',
					'svc_D1',
					'svc_D2',
					'svc_D3',
					'svc_D4',
					'svc_D5',
					'svc_D6',
					'svc_D7',
				),
			),
			
			array(
				'label'=>Yii::t('sales','蔚诺空气业务').Yii::t('sales','报价'),
				'child'=>array(
					'svc_H',
					'svc_H1',
					'svc_H4',
					'svc_H2',
					'svc_H3',
					'svc_H5',
					'svc_H6',
					'svc_H7',
				),
			),

			array(
				'label'=>Yii::t('sales','甲醛').Yii::t('sales','报价'),
				'child'=>array(
					'svc_E',
					'svc_E1',
					'svc_E2',
					'svc_E3',
					'svc_E4',
					'svc_E5',
					'svc_E6',
					'svc_E7',
					'svc_E8',
				),
			),
			
			array(
				'label'=>Yii::t('sales','纸品').Yii::t('sales','报价'),
				'child'=>array(
					'svc_F1',
					'svc_F2',
					'svc_F4',
					'svc_F3',
				),
			),
			
			array(
				'label'=>Yii::t('sales','一次性售卖').Yii::t('sales','报价'),
				'child'=>array(
					'svc_G3',
					'svc_G1',
					'svc_G2',
				),
			),
		);

		return ($this->readAll ? array_merge($header1, $header2) : $header2);
	}

	public function genReport() {
		$this->init();
		$this->retrieveData();
		$this->title = $this->getReportName();
		$this->subtitle = '';
		$output = $this->exportExcel();
		return $output;
	}
	
	protected function init() {
		$this->readAll = $this->isReadAll($this->criteria['UID']);
		$this->searchColumns = json_decode($this->criteria['SEARCH_COL']);
		$this->staticSearchColumns = json_decode($this->criteria['STATIC_COL']);
		$criteria = json_decode($this->criteria['CRITERIA']);
		$this->searchField = $criteria->searchField;
		$this->searchValue = $criteria->searchValue;
		$this->filter = json_decode($criteria->filter);
	}
	
	public function retrieveData() {
		$suffix = Yii::app()->params['envSuffix'];
		$uid = $this->criteria['UID'];
		
		$city = $this->criteria['CITY'];
		$citylist = City::model()->getDescendantList($city);
		$citylist .= (empty($citylist) ? '' : ',')."'$city'";

		$sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,  
				d.name as visit_type_name, g.name as cust_type_name,
				h.name as district_name, VisitObjDesc(a.visit_obj) as visit_obj_name, i.cust_vip
				from sal_visit a 
				inner join hr$suffix.hr_binding c on a.username = c.user_id
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				inner join sal_visit_type d on a.visit_type = d.id
				inner join sal_cust_type g on a.cust_type = g.id
				inner join sal_cust_district h on a.district = h.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.city in ($citylist)
			";
		if (!$this->readAll) $sql .= " and a.username='$uid' ";
			
		$clause = "";
		$static = $this->staticSearchColumns;
		$columns = $this->searchColumns;
		if (!empty($this->searchField) && (!empty($this->searchValue) || in_array($this->searchField, $static) || $this->isAdvancedSearch())) {
			if ($this->isAdvancedSearch()) {
				$clause = $this->buildSQLCriteria();
			} elseif (in_array($this->searchField, $static)) {
				$fldid = $this->searchField;
				$clause .= 'and '.$columns->$fldid;
			} else {
				$svalue = str_replace("'","\'",$this->searchValue);
				$fldid = $this->searchField;
				$clause .= General::getSqlConditionClause($columns->$fldid,$svalue);
			}
		}
		$order = $this->readAll	? " order by a.visit_dt desc, f.code" : " order by a.visit_dt desc, b.name, f.code";
		$sql = $sql.$clause.$order;

		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$temp = $this->initTemp();
                if($row['shift']=='Y'){
                    $row['shift']="(旧)";
                }
                if($row['shift']=='Z'){
                    $row['shift']="(转)";
                }
				$temp['id'] = $row['id'];
				$temp['visit_dt'] = General::toDate($row['visit_dt']);
				$temp['username'] = $row['username'];
				$stf = $this->getStaffInfo($row['username']);
				$temp['staff'] = $stf['staff']. $row['shift'];
				$temp['post_name'] = $stf['post_name'];
				$temp['dept_name'] = $stf['dept_name'];
				$temp['district'] = $row['district_name'];
				$temp['street'] = $row['street'];
				$temp['city'] = $row['city'];
				$temp['city_name'] = $row['city_name'];
				$temp['cust_name'] = $row['cust_name'];
				$temp['cust_vip'] = $row['cust_vip'];
				$temp['cust_alt_name'] = $row['cust_alt_name'];
				$temp['cust_person'] = $row['cust_person'];
				$temp['cust_person_role'] = $row['cust_person_role'];
				$temp['cust_tel'] = $row['cust_tel'];
				$temp['visit_type'] = $row['visit_type_name'];
				$temp['visit_obj'] = $row['visit_obj_name'];
				$temp['cust_type'] = $row['cust_type_name'];
				$temp['remarks'] = $row['remarks'];

				$sqld = "select field_id, field_value from sal_visit_info where visit_id=".$row['id'];
				$lines = Yii::app()->db->createCommand($sqld)->queryAll();
				foreach ($lines as $line) {
                    //svc_H1,svc_G1
				    switch ($line['field_id']){
                        case "svc_H1"://蔚诺空气业务 類別
                            $temp[$line['field_id']] = VisitForm::getTypeListForH($line['field_value'],true);
                            break;
                        default:
                            if (strpos('svc_C2,svc_C3,svc_C4,svc_C5,svc_C9,',$line['field_id'].',')===false)
                                $temp[$line['field_id']] = $line['field_value'];
                            else
                                $temp[$line['field_id']] = $line['field_value']=='Y' ? 'Y' : '';
                    }
				}
				
				$this->data[] = $temp;
			}
		}
		
		return true;
	}

	public function getReportName() {
		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
	}
	
	protected function getStaffInfo($user) {
		$rtn = array('staff'=>'', 'dept_name'=>'', 'post_name'=>'');
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select b.name as staff_name, b.code as staff_code, c.name as dept_name, d.name as post_name
				from hr$suffix.hr_binding a
				inner join hr$suffix.hr_employee b on a.employee_id=b.id
				left outer join hr$suffix.hr_dept c on b.department=c.id 
				left outer join hr$suffix.hr_dept d on b.position=d.id 
				where a.user_id = '$user'
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$rtn['staff'] = $row['staff_code'].' - '.$row['staff_name'];
			$rtn['dept_name'] = $row['dept_name'];
			$rtn['post_name'] = $row['post_name'];
		}
		return $rtn;
	}

	protected function initTemp() {
		$rtn = array();
		foreach ($this->fields() as $id=>$item) {
			$rtn[$id] = '';
		}
		return $rtn;
	}
	
	protected function isReadAll($uid) {
		$suffix = Yii::app()->params['envSuffix'];
		$sysid = Yii::app()->params['systemId'];
		$sql = "select a_control from security$suffix.sec_user_access where username='$uid' and system_id='$sysid'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		return ($row!==false && strpos($row['a_control'],'CN03')!==false);
	}
}
?>