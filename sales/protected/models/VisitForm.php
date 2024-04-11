<?php

class VisitForm extends CFormModel
{
	public $id;
	public $username;
	public $visit_dt;
	public $visit_type;
	public $visit_obj;
	public $cust_type_group;
	public $cust_type;
	public $cust_name;
	public $cust_alt_name;
	public $cust_person;
	public $cust_person_role;
	public $cust_vip = 'N';
	public $cust_tel;
	public $district;
	public $street;
	public $quote;
	public $remarks;
	public $shift;
	public $shift_user;
	public $status;
	public $status_dt;
	public $latitude;
	public $longitude;
	public $deal = 'N';
	public $service_type;
	public $quotation='N';

	public $service = array();
	protected $dynamic_fields = array('latitude', 'longitude', 'deal');
	protected $amount_fields = array('A7','B6','C7','D6','E7','F4','G3','H6');
	
	public $city;
	public $city_name;
	public $staff;
	public $dept_name;
	public $post_name;

	public $files;

	public $docMasterId = array(
							'visit'=>0,
						);
	public $removeFileId = array(
							'visit'=>0,
						);
	public $no_of_attm = array(
							'visit'=>0,
						);
	
	public function serviceDefinition() {
		return array(
			'A'=>array(
					'name'=>Yii::t('sales','清洁'),
					'type'=>'annual',
					'items'=>array(
                                'A10'=>array('name'=>Yii::t('sales','安装费'),'type'=>'amount'),
								'A1'=>array('name'=>Yii::t('sales','马桶/蹲厕'),'type'=>'qty'),
								'A2'=>array('name'=>Yii::t('sales','尿斗'),'type'=>'qty'),
								'A3'=>array('name'=>Yii::t('sales','水盆'),'type'=>'qty','eol'=>true),
								'A4'=>array('name'=>Yii::t('sales','清新机'),'type'=>'qty'),
								'A5'=>array('name'=>Yii::t('sales','皂液机'),'type'=>'qty'),
                                'A9'=>array('name'=>Yii::t('sales','雾化消毒'),'type'=>'qty','eol'=>true),
                                'A11'=>array('name'=>Yii::t('sales','隔油池'),'type'=>'qty'),
                                'A12'=>array('name'=>Yii::t('sales','油烟机清洗'),'type'=>'qty','eol'=>true),
								'A6'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
								'A7'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
								'A8'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),

							),
				),
			'B'=>array(
					'name'=>Yii::t('sales','租赁机器'),
					'type'=>'annual',
					'items'=>array(
								'B1'=>array('name'=>Yii::t('sales','风扇机'),'type'=>'qty'),
								'B2'=>array('name'=>Yii::t('sales','TC豪华'),'type'=>'qty'),
								'B3'=>array('name'=>Yii::t('sales','水性喷机'),'type'=>'qty','eol'=>true),
								'B4'=>array('name'=>Yii::t('sales','压缩香罐'),'type'=>'qty'),
								'B8'=>array('name'=>Yii::t('sales','饮水机租赁'),'type'=>'qty'),
								'B9'=>array('name'=>Yii::t('sales','滤芯'),'type'=>'qty','eol'=>true),
								'B5'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
								'B6'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
								'B7'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
							),
				),
			'C'=>array(
					'name'=>Yii::t('sales','灭虫'),
					'type'=>'annual',
					'items'=>array(
                        'C10'=>array('name'=>Yii::t('sales','安装费'),'type'=>'amount'),
                        'C1'=>array('name'=>Yii::t('sales','服务面积'),'type'=>'qty','eol'=>true),
                        'C2'=>array('name'=>Yii::t('sales','老鼠'),'type'=>'checkbox'),
                        'C3'=>array('name'=>Yii::t('sales','蟑螂'),'type'=>'checkbox'),
                        'C4'=>array('name'=>Yii::t('sales','果蝇'),'type'=>'checkbox'),
                        'C5'=>array('name'=>Yii::t('sales','租灭蝇灯'),'type'=>'checkbox'),
                        'C9'=>array('name'=>Yii::t('sales','焗雾'),'type'=>'checkbox','eol'=>true),
                        'C6'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
                        'C7'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
                        'C8'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
							),
				),
			'D'=>array(
					'name'=>Yii::t('sales','飘盈香'),
					'type'=>'annual',
					'items'=>array(
								'D8'=>array('name'=>Yii::t('sales','装机费'),'type'=>'amount','eol'=>true),
								'D1'=>array('name'=>Yii::t('sales','迷你小机'),'type'=>'qty'),
								'D2'=>array('name'=>Yii::t('sales','小机'),'type'=>'qty'),
								'D3'=>array('name'=>Yii::t('sales','中机'),'type'=>'qty'),
								'D4'=>array('name'=>Yii::t('sales','大机'),'type'=>'qty','eol'=>true),
								'D5'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
								'D6'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
								'D7'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
							),
				),
			'H'=>array(
					'name'=>Yii::t('sales','蔚诺空气业务'),
					'type'=>'annual',
					'items'=>array(
								'H1'=>array('name'=>Yii::t('sales','类别'),'type'=>'select','func'=>'getTypeListForH'),
                                'H4'=>array('name'=>Yii::t('sales','延长维保'),'type'=>'amount','eol'=>true),
								'H2'=>array('name'=>Yii::t('sales','RA488'),'type'=>'qty'),
								'H3'=>array('name'=>Yii::t('sales','RA800'),'type'=>'qty','eol'=>true),
								'H5'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
								'H6'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
								'H7'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
							),
				),
			'E'=>array(
					'name'=>Yii::t('sales','甲醛'),
					'type'=>'annual',
					'items'=>array(
								'E1'=>array('name'=>Yii::t('sales','服务面积'),'type'=>'qty','eol'=>true),
								'E2'=>array('name'=>Yii::t('sales','除甲醛'),'type'=>'qty'),
								'E3'=>array('name'=>Yii::t('sales','AC30'),'type'=>'qty'),
								'E4'=>array('name'=>Yii::t('sales','PR10'),'type'=>'qty'),
								'E5'=>array('name'=>Yii::t('sales','迷你清洁炮'),'type'=>'qty','eol'=>true),
								'E6'=>array('name'=>Yii::t('sales','预估成交率').'(0-100%)','type'=>'pct'),
								'E7'=>array('name'=>Yii::t('sales','合同年金额'),'type'=>'amount','eol'=>true),
								'E8'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
							),
				),
			'F'=>array(
					'name'=>Yii::t('sales','纸品'),
					'type'=>'none',
					'items'=>array(
								'F1'=>array('name'=>Yii::t('sales','擦手纸价'),'type'=>'amount'),
								'F2'=>array('name'=>Yii::t('sales','大卷厕纸价'),'type'=>'amount','eol'=>true),
								'F4'=>array('name'=>Yii::t('sales','合同金额'),'type'=>'amount','eol'=>true),
								'F3'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
							),
				),
			'G'=>array(
					'name'=>Yii::t('sales','一次性售卖'),
					'type'=>'none',
					'items'=>array(
								'G3'=>array('name'=>Yii::t('sales','合同金额'),'type'=>'amount','eol'=>true),
								'G1'=>array('name'=>Yii::t('sales','种类'),'type'=>'text','eol'=>true),
								'G2'=>array('name'=>Yii::t('sales','备注'),'type'=>'rmk'),
							),
				),
		);
	}

	//蔚诺空气业务的类型列表
	public static function getTypeListForH($type=0,$bool=false){
        $arr = array(""=>"");
	    $rows = Yii::app()->db->createCommand()->select("id,name")->from("sal_attr_type")
            ->where("(rpt_type='H' and display_num=1) or id=:id",array(":id"=>$type))
            ->queryAll();
	    if($rows){
            foreach ($rows as $row){
                $arr[$row["id"]] = $row["name"];
            }
        }
        if($bool){
            if (key_exists($type,$arr)){
                return $arr[$type];
            }else{
                return $type;
            }
        }else{
            return $arr;
        }
    }

	//一次性售卖的类型列表
	public static function getTypeListForG($type=0,$bool=false){
        $arr = array(""=>"");
        $rows = Yii::app()->db->createCommand()->select("id,name")->from("sal_attr_type")
            ->where("(rpt_type='G' and display_num=1) or id=:id",array(":id"=>$type))
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                $arr[$row["id"]] = $row["name"];
            }
        }
        if($bool){
            if (key_exists($type,$arr)){
                return $arr[$type];
            }else{
                return $type;
            }
        }else{
            return $arr;
        }
    }

	public function init() {
		$this->city = Yii::app()->user->city();
		$this->username = Yii::app()->user->id;
		$this->visit_dt = date("Y/m/d");
		$this->getStaffInfo();
		$this->status = 'N';

		$services = $this->serviceDefinition();
		foreach ($services as $key=>$value) {
			$fldid = 'svc_'.$key;
			$this->service[$fldid] = '';
			foreach ($value['items'] as $k=>$v) {
				$fldid = 'svc_'.$k;
				$this->service[$fldid] = '';
			}
		}
	}
	
	public function attributeLabels()
	{
		$rtn = array(
			'visit_dt'=>Yii::t('sales','Visit Date'),
			'status_dt'=>Yii::t('sales','Actual Visit Date'),
			'staff'=>Yii::t('sales','Staff'),
			'post_name'=>Yii::t('sales','Position'),
			'dept_name'=>Yii::t('sales','Department'),
			'remarks'=>Yii::t('sales','Remarks'),
			'city'=>Yii::t('sales','City'),
			'visit_type'=>Yii::t('sales','Type'),
			'visit_obj'=>Yii::t('sales','Objective'),
			'cust_type'=>Yii::t('sales','Customer Type'),
			'cust_vip'=>Yii::t('sales','VIP'),
			'cust_name'=>Yii::t('sales','Customer Name'),
			'cust_person'=>Yii::t('sales','Resp. Person'),
			'cust_person_role'=>Yii::t('sales','Role'),
			'cust_tel'=>Yii::t('sales','Phone'),
			'district'=>Yii::t('sales','District'),
			'street'=>Yii::t('sales','Street'),
            'service_type'=>Yii::t('sales','Service Type'),
			'cust_alt_name'=>Yii::t('sales','Branch Name (if any)'),
            'quotation'=>Yii::t('sales','Quotation'),
		);
		
		$services = $this->serviceDefinition();
		foreach($services as $key=>$value) {
			$fldid = 'svc_'.$key;
			$rtn[$fldid] = $value['name'];
			foreach($value['items'] as $k=>$v) {
				$fldid = 'svc_'.$k;
				$rtn[$fldid] = $v['name'];
			}
		}
		
		return $rtn;
	}

	public function rules() {
		return array(
			array('visit_dt, username, district, visit_type, visit_obj,service_type, cust_type, cust_type_group, cust_name','required'),
			array('visit_dt','validateVisitDt'),
			array('service','validateServiceAmount'),
			array('service','validateServices'),
			array('id, city, city_name, remarks, staff, dept_name, post_name, street, cust_person, cust_person_role, cust_vip,quotation,
				cust_tel, cust_alt_name, status, status_dt, latitude, longitude, deal, cust_type_group','safe'),
			array('files, removeFileId, docMasterId, no_of_attm','safe'),
            array ('no_of_attm','validateTaxSlip'),
		);
	}

    public function validateVisitDt($attribute, $params) {
        $this->username = Yii::app()->user->id;
        $visit_dt = date("Y/m/d",strtotime($this->visit_dt));
        $nowDate = date("Y/m/d");
        $minDate = date("Y/m/d",strtotime($nowDate." - 1 day"));
        if($this->getScenario()=="new"){
            if($visit_dt<$minDate){
                $this->addError($attribute, "拜访日期必须大于".$minDate);
            }else{
                $countRow = Yii::app()->db->createCommand()
                    ->select("count(id)")
                    ->from("sal_visit")
                    ->where("username='{$this->username}' and DATE_FORMAT(visit_dt,'%Y/%m/%d')='{$visit_dt}'")->queryScalar();
                if($countRow>=50){//每天录入上线为50条
                    $this->addError($attribute, "每天录入上限为{$countRow}/50条（{$visit_dt}） - {$this->username}");
                }
            }
        }else{
            $visitRow = Yii::app()->db->createCommand()->select("visit_dt")->from("sal_visit")
                ->where("id=:id",array(":id"=>$this->id))->queryRow();
            if($visitRow){
                $old_dt = date("Y/m/d",strtotime($visitRow["visit_dt"]));
                if($old_dt>$visit_dt){
                    $this->visit_dt = $old_dt;
                }
            }
        }
    }

    public function validateTaxSlip($attribute, $params) {
        $count = $this->no_of_attm['visit'];
        if (is_array($this->visit_obj)&&in_array('10',$this->visit_obj)&&(empty($count) || $count==0)) {
            $this->addError($attribute, Yii::t('dialog','No visit Slip'));
        }
    }

    public function inAmtFiles($gid){
        $key = "".$gid;
        if (in_array($key, $this->amount_fields)) {
            return 1;
        }else{
            return 0;
        }
    }
	
	public function validateServiceAmount($attribute, $param) {
		if ($this->isMakingDeal($this->visit_obj)) {
			$total = 0;
			$services = $this->serviceDefinition();
			foreach ($services as $key=>$value) {
				if (in_array($key, $this->amount_fields)) {
					$fldid = 'svc_'.$key;
					if (isset($this->service[$fldid])) {
						if (!empty($this->service[$fldid]) && is_numeric($this->service[$fldid])) $total += $this->service[$fldid];
					}
				}

				foreach ($value['items'] as $k=>$v) {
					if (in_array($k, $this->amount_fields)) {
						$fldid = 'svc_'.$k;
						if (isset($this->service[$fldid])) {
							if (!empty($this->service[$fldid]) && is_numeric($this->service[$fldid])) $total += $this->service[$fldid];
						}
					}
				}
			}
			if ($total == 0) {
				$this->addError($attribute, Yii::t('sales','This record has no contract amount entered'));
			}
		}
	}
	
	public function validateServices($attribute, $params) {
		$services = $this->serviceDefinition();
		foreach ($services as $key=>$value) {
			$fldid = 'svc_'.$key;
			if (isset($this->service[$fldid])) {
				switch ($value['type']) { 
					case 'pct': 
						if (!empty($this->service[$fldid])) {
							if (!is_numeric($this->service[$fldid]) || !is_int($this->service[$fldid]+0) || $this->service[$fldid]+0 > 100 || $this->service[$fldid]+0 < 0)
								$this->addError($attribute, $value['name'].'-'.Yii::t('sales','Percentage').' '.Yii::t('sales','Invalid value'));
						}
						break;
					case 'qty': 
						if (!empty($this->service[$fldid])) {
							if (!is_numeric($this->service[$fldid]) || !is_int($this->service[$fldid]+0))
								$this->addError($attribute, $value['name'].'-'.Yii::t('sales','Qty').' '.Yii::t('sales','Invalid value'));
						}
						break;
					case 'annual':
						if (!empty($this->service[$fldid]) && !is_numeric($this->service[$fldid]))
							$this->addError($attribute, $value['name'].'-'.Yii::t('sales','Annual Amount').' '.Yii::t('sales','Invalid value'));
						break;
					case 'amount':
						if (!empty($this->service[$fldid]) && !is_numeric($this->service[$fldid]))
							$this->addError($attribute, $value['name'].'-'.Yii::t('sales','Amount').' '.Yii::t('sales','Invalid value'));
				}
			}
			
			foreach ($value['items'] as $k=>$v) {
				$fldid = 'svc_'.$k;
				if (isset($this->service[$fldid])) {
					switch ($v['type']) { 
						case 'pct': 
							if (!empty($this->service[$fldid])) {
								if (!is_numeric($this->service[$fldid]) || !is_int($this->service[$fldid]+0) || $this->service[$fldid]+0 > 100 || $this->service[$fldid]+0 < 0)
									$this->addError($attribute, $value['name'].'-'.$v['name'].' '.Yii::t('sales','Invalid value'));
							}
						break;
						case 'qty': 
							if (!empty($this->service[$fldid])) {
								if (!is_numeric($this->service[$fldid]) || !is_int($this->service[$fldid]+0))
									$this->addError($attribute, $value['name'].'-'.$v['name'].' '.Yii::t('sales','Invalid value'));
							}
							break;
						case 'annual':
							if (!empty($this->service[$fldid]) && !is_numeric($this->service[$fldid]))
								$this->addError($attribute, $value['name'].'-'.$v['name'].' '.Yii::t('sales','Invalid value'));
							break;
						case 'amount':
							if (!empty($this->service[$fldid]) && !is_numeric($this->service[$fldid]))
								$this->addError($attribute, $value['name'].'-'.$v['name'].' '.Yii::t('sales','Invalid value'));
					}
				}
			}
		}
	}

	public function retrieveData($index) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$user = Yii::app()->user->id;
		$sql = "select a.*, b.name as city_name, concat(f.code,' - ',f.name) as staff,
					VisitObjDesc(a.visit_obj) as visit_obj_name, d.type_group, 
					docman$suffix.countdoc('visit',a.id) as visitcountdoc, i.cust_vip
				from sal_visit a 
				inner join sal_cust_type d on a.cust_type = d.id
				inner join hr$suffix.hr_binding c on a.username = c.user_id
				inner join hr$suffix.hr_employee f on c.employee_id = f.id
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_custstar i on a.username=i.username and a.cust_name=i.cust_name
				where a.id = $index
			";
		$sql .= ($this->isReadAll()) ? " and a.city in ($citylist)" : " and (a.username='$user' or a.shift_user='$user') ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
//        print_r('<pre/>');
//        print_r($row);
		if ($row!==false) {
			$this->id = $row['id'];
			$this->visit_dt = General::toDate($row['visit_dt']);
			$this->username = $row['username'];
			$this->getStaffInfo();
			$this->shift = $row['shift'];
			$this->shift_user = $row['shift_user'];
			$this->district = $row['district'];
			$this->street = $row['street'];
			$this->city = $row['city'];
			$this->city_name = $row['city_name'];
			$this->cust_name = $row['cust_name'];
			$this->cust_vip = $row['cust_vip'];
			$this->cust_alt_name = $row['cust_alt_name'];
			$this->cust_person = $row['cust_person'];
			$this->cust_person_role = $row['cust_person_role'];
			$this->cust_tel = $row['cust_tel'];
			$this->visit_type = $row['visit_type'];
			$this->visit_obj = json_decode($row['visit_obj']);
			$this->service_type = json_decode($row['service_type']);
			$this->cust_type = $row['cust_type'];
			$this->remarks = $row['remarks'];
			$this->status = $row['status'];
			$this->status_dt = $row['status_dt'];
			$this->cust_type_group = $row['type_group'];
			$this->no_of_attm['visit'] = $row['visitcountdoc'];
            $this->quotation = $row['quotation'];
		}
		
		$sql = "select * from sal_visit_info where visit_id = $index";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();

		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$fldid = $row['field_id'];
				if (isset($this->$fldid)) {
					$this->$fldid = $row['field_value'];
				} elseif (isset($this->service[$row['field_id']])) {
					$this->service[$row['field_id']] = $row['field_value'];
				}
			}
		}

		return true;
	}
	
	protected function getStaffInfo() {
		$suffix = Yii::app()->params['envSuffix'];
		$user = $this->username;
		$sql = "select b.name as staff_name, b.code as staff_code, c.name as dept_name, d.name as post_name
				from hr$suffix.hr_binding a
				inner join hr$suffix.hr_employee b on a.employee_id=b.id
				left outer join hr$suffix.hr_dept c on b.department=c.id 
				left outer join hr$suffix.hr_dept d on b.position=d.id 
				where a.user_id = '$user'
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->staff = $row['staff_code'].' - '.$row['staff_name'];
			$this->dept_name = $row['dept_name'];
			$this->post_name = $row['post_name'];
		}
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$pushMessage = false;
			if ($this->deal!='Y' && $this->isPlaceOrder()) {
				$pushMessage = true;
				$this->deal = 'Y';
			}
			$this->saveVisit($connection);
			$this->saveVisitInfo($connection);
			$this->saveCustCache($connection);
			$this->updateCustVip($connection);
			$this->updateDocman($connection,'VISIT');
			if ($this->scenario=='delete') {
				$this->removeNotification($connection);
			} else {
				if ($pushMessage) $this->addNotification($connection);
			}
			self::resetVisitObjName($this->id,$this->visit_obj);//由于列表查询慢，所以保存特殊字段
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveVisit(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_visit where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_visit(
							username, visit_dt, visit_type, visit_obj, cust_type, cust_name, cust_person_role,service_type,quotation,
							cust_alt_name, cust_person, cust_tel, district, street, remarks, status, status_dt,
							city, luu, lcu
						) values (
							:username, :visit_dt, :visit_type, :visit_obj, :cust_type, :cust_name, :cust_person_role,:service_type,:quotation,
							:cust_alt_name, :cust_person, :cust_tel, :district, :street, :remarks, :status, :status_dt,
							:city, :luu, :lcu
						)";
				break;
			case 'edit':
				$sql = "update sal_visit set 
					username = :username, 
					visit_dt = :visit_dt, 
					visit_type = :visit_type, 
					visit_obj = :visit_obj, 
					service_type=:service_type,
					quotation=:quotation,
					cust_type = :cust_type, 
					cust_name = :cust_name, 
					cust_alt_name = :cust_alt_name, 
					cust_person = :cust_person, 
					cust_person_role = :cust_person_role, 
					cust_tel = :cust_tel, 
					district = :district, 
					street = :street, 
					remarks = :remarks, 
					status = :status,
					status_dt = :status_dt,
					shift_bool = 1,
					luu = :luu
					where id = :id and city=:city";
				break;
		}

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$dt = General::toMyDate($this->visit_dt);

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':username')!==false)
			$command->bindParam(':username',$this->username,PDO::PARAM_STR);
		if (strpos($sql,':visit_dt')!==false)
			$command->bindParam(':visit_dt',$dt,PDO::PARAM_STR);
		if (strpos($sql,':visit_type')!==false)
			$command->bindParam(':visit_type',$this->visit_type,PDO::PARAM_INT);
		if (strpos($sql,':visit_obj')!==false) {
			$values = json_encode($this->visit_obj);
			//print_r($value);exit();
			$command->bindParam(':visit_obj',$values,PDO::PARAM_STR);
		}
        if (strpos($sql,':service_type')!==false) {
            $value = json_encode($this->service_type);
            $command->bindParam(':service_type',$value,PDO::PARAM_STR);
        }
		if (strpos($sql,':cust_type')!==false)
			$command->bindParam(':cust_type',$this->cust_type,PDO::PARAM_INT);
		if (strpos($sql,':cust_name')!==false)
			$command->bindParam(':cust_name',$this->cust_name,PDO::PARAM_STR);
        if (strpos($sql,':quotation')!==false)
            $command->bindParam(':quotation',$this->quotation,PDO::PARAM_STR);
		if (strpos($sql,':cust_alt_name')!==false)
			$command->bindParam(':cust_alt_name',$this->cust_alt_name,PDO::PARAM_STR);
		if (strpos($sql,':cust_person')!==false)
			$command->bindParam(':cust_person',$this->cust_person,PDO::PARAM_STR);
		if (strpos($sql,':cust_person_role')!==false)
			$command->bindParam(':cust_person_role',$this->cust_person_role,PDO::PARAM_STR);
		if (strpos($sql,':cust_tel')!==false)
			$command->bindParam(':cust_tel',$this->cust_tel,PDO::PARAM_STR);
		if (strpos($sql,':district')!==false)
			$command->bindParam(':district',$this->district,PDO::PARAM_INT);
		if (strpos($sql,':street')!==false)
			$command->bindParam(':street',$this->street,PDO::PARAM_STR);
		if (strpos($sql,':remarks')!==false)
			$command->bindParam(':remarks',$this->remarks,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$this->status,PDO::PARAM_STR);
		if (strpos($sql,':status_dt')!==false) {
			$d = empty($this->status_dt) ? null : $this->status_dt;
			$command->bindParam(':status_dt',$d,PDO::PARAM_STR);
		}
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	protected function saveVisitInfo(&$connection) {
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_visit_info where visit_id = :visit_id and field_id = :field_id";
				break;
			case 'new' :
			case 'edit':
				$sql = "insert into sal_visit_info(
						visit_id, field_id, field_value, luu, lcu) values (
						:visit_id, :field_id, :field_value, :luu, :lcu)
						on duplicate key update
						field_value = :field_value, luu  = :luu
						";
		}

		$uid = Yii::app()->user->id;

		foreach ($this->service as $key=>$value) {
			$command=$connection->createCommand($sql);
			if (strpos($sql,':visit_id')!==false)
				$command->bindParam(':visit_id',$this->id,PDO::PARAM_INT);
			if (strpos($sql,':field_id')!==false)
				$command->bindParam(':field_id',$key,PDO::PARAM_STR);
			if (strpos($sql,':field_value')!==false)
				$command->bindParam(':field_value',$value,PDO::PARAM_STR);
			if (strpos($sql,':luu')!==false)
				$command->bindParam(':luu',$uid,PDO::PARAM_STR);
			if (strpos($sql,':lcu')!==false)
				$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
			$command->execute();
		}
		
		foreach ($this->dynamic_fields as $field) {
			if (isset($this->$field)) {
				$command=$connection->createCommand($sql);
				if (strpos($sql,':visit_id')!==false)
					$command->bindParam(':visit_id',$this->id,PDO::PARAM_INT);
				if (strpos($sql,':field_id')!==false)
					$command->bindParam(':field_id',$field,PDO::PARAM_STR);
				if (strpos($sql,':field_value')!==false)
					$command->bindParam(':field_value',$this->$field,PDO::PARAM_STR);
				if (strpos($sql,':luu')!==false)
					$command->bindParam(':luu',$uid,PDO::PARAM_STR);
				if (strpos($sql,':lcu')!==false)
					$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
				$command->execute();
			}
		}
	}

	protected function saveCustCache(&$connection) {
		$sql = '';
		switch ($this->scenario) {
			case 'new' :
			case 'edit':
				$sql = "insert into sal_custcache(
							username, cust_name, cust_alt_name, cust_person, cust_person_role, cust_tel,visit_id,
							district, street, cust_type
						) values (
							:username, :cust_name, :cust_alt_name, :cust_person, :cust_person_role, :cust_tel,:visit_id,
							:district, :street, :cust_type
						)
						on duplicate key update
							cust_alt_name=:cust_alt_name, visit_id=:visit_id, cust_person=:cust_person, cust_person_role=:cust_person_role, 
							cust_tel=:cust_tel, district=:district, street=:street,
							cust_type=:cust_type
						";
		}

		if (!empty($sql)) {
			$command=$connection->createCommand($sql);
            if (strpos($sql,':username')!==false)
                $command->bindParam(':username',$this->username,PDO::PARAM_STR);
            if (strpos($sql,':visit_id')!==false)
                $command->bindParam(':visit_id', $this->id,PDO::PARAM_INT);
			if (strpos($sql,':cust_name')!==false)
				$command->bindParam(':cust_name',$this->cust_name,PDO::PARAM_STR);
			if (strpos($sql,':cust_alt_name')!==false)
				$command->bindParam(':cust_alt_name',$this->cust_alt_name,PDO::PARAM_STR);
			if (strpos($sql,':cust_person')!==false)
				$command->bindParam(':cust_person',$this->cust_person,PDO::PARAM_STR);
			if (strpos($sql,':cust_person_role')!==false)
				$command->bindParam(':cust_person_role',$this->cust_person_role,PDO::PARAM_STR);
			if (strpos($sql,':cust_tel')!==false)
				$command->bindParam(':cust_tel',$this->cust_tel,PDO::PARAM_STR);
			if (strpos($sql,':district')!==false)
				$command->bindParam(':district',$this->district,PDO::PARAM_INT);
			if (strpos($sql,':street')!==false)
				$command->bindParam(':street',$this->street,PDO::PARAM_STR);
			if (strpos($sql,':cust_type')!==false)
				$command->bindParam(':cust_type',$this->cust_type,PDO::PARAM_INT);
			$command->execute();
		}
	}

	protected function addNotification(&$connection) {
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "insert into sal_push_message(
					msg_type, message_en, message_cn, message_tw, status, key_id, lcu, luu
				) values (
					'SALORDER', :message_en, :message_cn, :message_tw, 'P', :key_id, :uid, :uid
				)
			";

//		$svctype = "";
//		$amount = "";
		$amt = 0;
		$svcmsg_cn = "";
		$svcmsg_tw = "";
		$svcmsg_en = "";
		$services = $this->serviceDefinition();
		foreach($services as $key=>$value) {
			if (strpos("ABCDEFGH", $key)!==false) {
				foreach($value['items'] as $k=>$v) {
					if ($v['type']=='amount') {
						$fldid = 'svc_'.$k;
						if (isset($this->service[$fldid]) && !empty($this->service[$fldid])) {
							$svctype = $value['name'];
							$amount = $this->service[$fldid];
							
							if ($key == 'F' || $key == 'G') {
								if ($k == 'F4' || $key == 'G') {
									$svcmsg_cn .= (($svcmsg_cn=="") ? "" : "，")."服务类型：$svctype ，合同金额：$amount";
									$svcmsg_tw .= (($svcmsg_tw=="") ? "" : "，")."服務類型：$svctype ，合同金額：$amount";
									$svcmsg_en .= (($svcmsg_en=="") ? "" : ",")."Service Type: $svctype , Amount: $amount";
								}
							} elseif ($k == 'A10' || $k == 'C10') {
								$svcmsg_cn .= (($svcmsg_cn=="") ? "" : "，")."服务类型：$svctype ，安装费：$amount";
								$svcmsg_tw .= (($svcmsg_tw=="") ? "" : "，")."服務類型：$svctype ，安装费：$amount";
								$svcmsg_en .= (($svcmsg_en=="") ? "" : ",")."Service Type: $svctype , Installed Amount: $amount";
							} elseif ($k == 'H4') {
								$svcmsg_cn .= (($svcmsg_cn=="") ? "" : "，")."服务类型：$svctype ，延长维保：$amount";
								$svcmsg_tw .= (($svcmsg_tw=="") ? "" : "，")."服務類型：$svctype ，延长维保：$amount";
								$svcmsg_en .= (($svcmsg_en=="") ? "" : ",")."Service Type: $svctype , Extended maintenance: $amount";
							} else {
								$svcmsg_cn .= (($svcmsg_cn=="") ? "" : "，")."服务类型：$svctype ，合同年金额：$amount";
								$svcmsg_tw .= (($svcmsg_tw=="") ? "" : "，")."服務類型：$svctype ，合同年金額：$amount";
								$svcmsg_en .= (($svcmsg_en=="") ? "" : ",")."Service Type: $svctype , Annual Amount: $amount";
							}
							
//							$amt += $this->service[$fldid];
//							$svctype .= (($svctype=="") ? "" : ",").$value['name'];
						}
					}
				}
			}
		}
		
		if (!empty($svcmsg_cn) && !empty($svcmsg_tw) && !empty($svcmsg_en)) {
			$uid = Yii::app()->user->id;
			$staff = $this->staff;
			$cityname = Yii::app()->user->city_name();
			$amount = number_format($amt, 2, '.','');
			
//			$message_cn = "恭喜《 $cityname 》$staff 大神签单啦，服务类型：$svctype ，合同年金额：$amount";
//			$message_tw = "恭喜《 $cityname 》$staff 大神簽單啦，服務類型：$svctype ，合同年金額：$amount";
//			$message_en = "Congratulation!《 $cityname 》$staff signs a contract, Service Type: $svctype, Annual Amount: $amount";
			
			$message_cn = "恭喜《 $cityname 》$staff 大神签单啦，$svcmsg_cn";
			$message_tw = "恭喜《 $cityname 》$staff 大神簽單啦，$svcmsg_tw";
			$message_en = "Congratulation!《 $cityname 》$staff signs a contract, $svcmsg_en";

			$command=$connection->createCommand($sql);
			if (strpos($sql,':message_en')!==false)
				$command->bindParam(':message_en',$message_en,PDO::PARAM_STR);
			if (strpos($sql,':message_cn')!==false)
				$command->bindParam(':message_cn',$message_cn,PDO::PARAM_STR);
			if (strpos($sql,':message_tw')!==false)
				$command->bindParam(':message_tw',$message_tw,PDO::PARAM_STR);
			if (strpos($sql,':key_id')!==false)
				$command->bindParam(':key_id',$this->id,PDO::PARAM_INT);
			if (strpos($sql,':uid')!==false)
				$command->bindParam(':uid',$uid,PDO::PARAM_STR);
			$command->execute();
		}
	}

	public function removeNotification(&$connection) {
		if ($this->id != 0) {
			$sql = "delete from sal_push_message where key_id = :key_id";
			$command=$connection->createCommand($sql);
			if (strpos($sql,':key_id')!==false)
				$command->bindParam(':key_id',$this->id,PDO::PARAM_INT);
			$command->execute();
		}
	}

	public function updateCustVip(&$connection) {
		$sql = "insert into sal_custstar(username,cust_name,cust_vip) values(:username, :cust_name, :cust_vip)
					on duplicate key update
					cust_vip = :cust_vip
			";
		$command=$connection->createCommand($sql);
		if (strpos($sql,':username')!==false)
			$command->bindParam(':username',$this->username,PDO::PARAM_STR);
		if (strpos($sql,':cust_name')!==false)
			$command->bindParam(':cust_name',$this->cust_name,PDO::PARAM_STR);
		if (strpos($sql,':cust_vip')!==false)
			$command->bindParam(':cust_vip',$this->cust_vip,PDO::PARAM_STR);
		$command->execute();
	}
	
	protected function updateDocman(&$connection, $doctype) {
		if ($this->scenario=='new') {
			$docidx = strtolower($doctype);
			if ($this->docMasterId[$docidx] > 0) {
				$docman = new DocMan($doctype,$this->id,get_class($this));
				$docman->masterId = $this->docMasterId[$docidx];
				$docman->updateDocId($connection, $this->docMasterId[$docidx]);
			}
		}
	}

	protected function isPlaceOrder() {
		if (!empty($this->visit_obj)) {
			$sql = "select id from sal_visit_obj where rpt_type='DEAL'";
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				foreach($rows as $row) {
					if (is_array($this->visit_obj)) {
						foreach($this->visit_obj as $value) {
							if ($value==$row['id']) return true;
						}
					} else {
						if ($this->visit_obj==$row['id']) return true;
					}
				}
			}
		}
		return false;
	}
	
	public function isReadOnly() {
		return (
					$this->scenario=='view' || $this->status=='D'
				);
	}
	
	public function getVisitTypeList() {
		$rtn = array(''=>Yii::t('misc','-- None --'));
		$sql = "select id, name from sal_visit_type";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach($rows as $row) {
				$rtn[$row['id']] = $row['name'];
			}
		}
		return $rtn;
	}

    public static function getServiceTypeList()
    {
        $suffix = Yii::app()->params['envSuffix'];
        $list = array();
        $sql = "select id, description from swoper$suffix.swo_customer_type order by description";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $list[$row['id']] = $row['description'];
            }
        }
        return $list;
    }

	public static function getVisitObjList() {
//		$rtn = array(''=>Yii::t('misc','-- None --'));
		$rtn = array();
		$sql = "select id, name from sal_visit_obj";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach($rows as $row) {
				$rtn[$row['id']] = $row['name'];
			}
		}
		return $rtn;
	}

	public function getCustTypeList($type_group=1) {
		$city = Yii::app()->user->city();
		$rtn = array(''=>Yii::t('misc','-- None --'));
		$sql = "select id, name from sal_cust_type where (city='99999' or city='$city') and type_group=$type_group order by name";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach($rows as $row) {
				$rtn[$row['id']] = $row['name'];
			}
		}
		return $rtn;
	}
	
	public function getDistrictList() {
		$citylist = Yii::app()->user->city_allow();

		$rtn = array(''=>Yii::t('misc','-- None --'));
		$sql = "select id, name from sal_cust_district where city in ($citylist) and display=1 order by z_index desc,name";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach($rows as $row) {
				$rtn[$row['id']] = $row['name'];
			}
		}
		return $rtn;
	}

	protected function isMakingDeal($obj) {
		if (empty($obj)) {
			return false;
		} else {
			$objlist = is_array($obj) ? implode(',',$obj) : $obj;
			$sql = "select count(id) from sal_visit_obj where id in ($objlist) and rpt_type='DEAL'";
			$cnt = Yii::app()->db->createCommand($sql)->queryScalar();
			return ($cnt > 0);
		}
	}
	
	public static function isReadAll() {
		return Yii::app()->user->validFunction('CN03');
	}

	//由於列表需要顯示目的及报价，導致列表打開太慢，所以保存目的及报价
	public static function resetVisitObjName($visit_id=0,$visit_obj='',$objList=array()){
	    if(empty($objList)){
            $objList = self::getVisitObjList();
        }
        $visit_id = is_numeric($visit_id)?intval($visit_id):0;
        $updateArr = array();
        $list=array();
        $sql = "select field_id, field_value from sal_visit_info where field_id in ('svc_A','svc_B','svc_C','svc_D','svc_E','svc_F4','svc_G3','svc_H') and visit_id = '{$visit_id}'";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($rows as $a) {
            for ($i = 0; $i < count($rows); $i++) {
                $list[$a['field_id']]=$a['field_value'];
            }
        }
        $quote =array();
        if(isset($list['svc_A'])&&!empty($list['svc_A'])){
            $quote[]=$list['svc_A']."(清洁)";
        }
        if(isset($list['svc_B'])&&!empty($list['svc_B'])){
            $quote[]=$list['svc_B']."(机器)";
        }
        if(isset($list['svc_C'])&&!empty($list['svc_C'])){
            $quote[]=$list['svc_C']."(灭虫)";
        }
        if(isset($list['svc_D'])&&!empty($list['svc_D'])){
            $quote[]=$list['svc_D']."(飘盈香)";
        }
        if(isset($list['svc_H'])&&!empty($list['svc_H'])){
            $quote[]=$list['svc_H']."(蔚诺空气业务)";
        }
        if(isset($list['svc_E'])&&!empty($list['svc_E'])){
            $quote[]=$list['svc_E']."(甲醛)";
        }
        if(isset($list['svc_F4'])&&!empty($list['svc_F4'])){
            $quote[]=$list['svc_F4']."(纸品)";
        }
        if(isset($list['svc_G3'])&&!empty($list['svc_G3'])){
            $quote[]=$list['svc_G3']."(一次性售卖)";
        }
        $search = array("[", "]", '"');
        $replace = array("","","");

        $visit_obj_name="";
        if(!is_array($visit_obj)){
            $visit_obj = str_replace($search, $replace, $visit_obj);
            $visit_obj = explode(",",$visit_obj);
        }
        if(!empty($visit_obj)){
            foreach ($visit_obj as $item){
                $visit_obj_name.=empty($visit_obj_name)?"":",";
                $visit_obj_name.=key_exists($item,$objList)?$objList[$item]:$item;
            }
        }
        $updateArr["visit_obj_name"]=$visit_obj_name;
        if(!empty($quote)){
            if(count($quote)>3){
                foreach ($quote as $key=>$item){
                    if(!empty($key)&&$key%3==0){
                        $quote[$key]="<br/>".$quote[$key];
                    }
                }
            }
            //$sqls="update sal_visit set quotation='是' where id='".$visit_id."'";
            $updateArr["quotation"]="是";//不知道什么作用，以前就有(参考上面被注释的sql)
            $updateArr["visit_info_text"]=implode("/",$quote);
        }
        Yii::app()->db->createCommand()->update("sal_visit",$updateArr,"id='{$visit_id}'");
	}

	//由於列表需要顯示附件數量，導致列表打開太慢，所以保存附件數量
	public function resetFileSum($id=0){
        $id = empty($id)||!is_numeric($id)?0:$id;
        if(!empty($id)){
            $suffix = Yii::app()->params['envSuffix'];
            $sql = "update sal_visit set
              doc_count=docman{$suffix}.countdoc('visit',{$id})
              WHERE id={$id}
            ";
            Yii::app()->db->createCommand($sql)->execute();
        }
    }
}
