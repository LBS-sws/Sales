<?php

class SalesSearchCustomerList extends CListPageModel
{
	public $company_name;
	private $countBool=false;
	private $employee_id;
	private $employee_code;
	private $employee_name;
	private $search_list=false;

	
	public function attributeLabels()
	{
		return array(	
			'company_code'=>Yii::t('customer','Customer Code'),
			'company_name'=>Yii::t('customer','Customer Name'),
			'company_type_list'=>Yii::t('customer','Customer Type'),
			'company_status'=>Yii::t('customer','Status'),
			'full_name'=>Yii::t('customer','Full Name'),
			'cont_name'=>Yii::t('customer','Contact Name'),
			'cont_phone'=>Yii::t('customer','Contact Phone'),
			'city_name'=>Yii::t('misc','City'),
			'city_list'=>Yii::t('misc','City'),
			'status_dt'=>Yii::t('customer','Date'),
			'status'=>Yii::t('customer','Status'),
			'cust_type_desc'=>Yii::t('customer','Type'),
			'product_desc'=>Yii::t('customer','Product'),
			'first_dt'=>Yii::t('customer','First Date'),
			'amt_paid'=>Yii::t('customer','Amount'),
		);
	}
	
	public function rules()
	{	$rtn1 = parent::rules();
		$rtn2 =  array(
			array('company_name','safe',),
		);
		return array_merge($rtn1, $rtn2);
	}

	private function validateName(){
	    if(!empty($this->company_name)){
            $date = date("Y-m-d");
            $row =Yii::app()->db->createCommand()->select("id,search_num,search_json,search_str")
                ->from("sal_search")
                ->where('employee_id=:employee_id and date_format(search_date,"%Y-%m-%d")=:search_date',
                    array(':employee_id'=>$this->employee_id,':search_date'=>$date)
                )->queryRow();
            $this->search_list=$row;
            if($row&&$row["search_num"]>=20){//2023/06/20  改为20次
                //查询次数不允许超过10次
                $this->addError("company_name", "查询次数不允许超过20次");
                return false;
            }
        }
        return true;
    }

	public function retrieveDataByPage($pageNum=1)
	{
	    if(!$this->validateName()){
	        return true;
        }
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();
		$sql1 = "select a.*, c.name as city_name, b.status, b.type_list  
				from swoper$suffix.swo_company a
				inner join security$suffix.sec_city c on a.city=c.code
				left outer join swoper$suffix.swo_company_status b on a.id=b.id
				where a.city in ($city) 
			";
		$sql2 = "select count(a.id)
				from swoper$suffix.swo_company a
				inner join security$suffix.sec_city c on a.city=c.code
				left outer join swoper$suffix.swo_company_status b on a.id=b.id
				where a.city in ($city) 
			";
		$clause = "";
		if (empty($this->company_name)) {
            $clause .= (empty($clause) ? '' : ' and ')."1!=1 ";
		}else{
            $svalue = str_replace("'","\'",$this->company_name);
            $clause .= (empty($clause) ? '' : ' and ')."a.name like '%$svalue%'";
        }
		if ($clause!='') $clause = ' and ('.$clause.')'; 
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		}

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$record = Yii::app()->db->createCommand($sql)->queryRow();
		
		$list = array();
		$this->attr = array();
		if ($record) {
            $this->countBool = true;
            $detail = $this->getServiceList($record['id'], $record['code'], $record['name'], $record['city']);
            $kaDetail = $this->getServiceKAList($record['id'], $record['code'], $record['name'], $record['city']);
            $idDetail = $this->getServiceIDList($record['id'], $record['code'], $record['name'], $record['city']);
            $this->attr[] = array(
                'company_id'=>$record['id'],
                'company_code'=>$record['code'],
                'company_name'=>$record['name'],
                'full_name'=>$record['full_name'],
                'cont_name'=>$record['cont_name'],
                'cont_phone'=>$record['cont_phone'],
                'city_name'=>$record['city_name'],
                'company_status'=>$this->statusDesc($record['status']),
                'detail'=>array_merge($detail,$kaDetail,$idDetail),
            );
		}
/*		$session = Yii::app()->session;
		$session[$this->criteraName()] = $this->getCriteria();*/
		$this->insertSearchHistory();
		return true;
	}

	private function criteraName(){
        return 'criteria_'.get_class($this);
    }

    //记录搜索的历史
    private function insertSearchHistory(){
        if($this->countBool){
            $uid = Yii::app()->user->id;
            $dateTime = date("Y-m-d H:i:s");
            $arr = array('data'=>$dateTime,'name'=>$this->company_name);
            $row =$this->search_list;
            if($row){
                $json = json_decode($row["search_json"],true);
                $json[]=$arr;
                Yii::app()->db->createCommand()->update("sal_search",array(
                    'search_num'=>($row["search_num"]+1),
                    'search_json'=>json_encode($json),
                    'search_str'=>($row["search_str"].",".$this->company_name),
                ),"id=".$row["id"]);
            }else{
                Yii::app()->db->createCommand()->insert("sal_search",array(
                    'employee_id'=>$this->employee_id,
                    'employee_code'=>$this->employee_code,
                    'employee_name'=>$this->employee_name,
                    'city'=>Yii::app()->user->city(),
                    'search_date'=>$dateTime,
                    'search_num'=>1,
                    'search_json'=>json_encode(array($arr)),
                    'search_str'=>$this->company_name,
                    'lcu'=>$uid,
                ));
            }
        }
    }

    public function getEmployee(){
        $suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
        $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee b","a.employee_id = b.id")
            ->where('user_id=:user_id',array(':user_id'=>$uid))->queryRow();
        if ($rows){
            $this->employee_id = $rows["id"];
            $this->employee_code = $rows["code"];
            $this->employee_name = $rows["name"];
            return true;
        }
        return false;
    }

	protected function getServiceList($id, $code, $name, $city) {
        $suffix = Yii::app()->params['envSuffix'];
		$rtn = array();
		$name = str_replace("'","\'",$name);
		$sql = "select a.*, c.description as cust_type_desc, d.description as product_desc   
				from swoper$suffix.swo_service a
				left outer join swoper$suffix.swo_service b on a.company_name=b.company_name 
					and a.status_dt < b.status_dt and a.cust_type=b.cust_type
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				left outer join swoper$suffix.swo_product d on a.product_id=d.id 
				where b.id is null and a.city='$city'
				and (a.company_id=$id or a.company_name like concat('$code',' %') 
				or a.company_name like concat('%','$name'));
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$rtn[] = array(
							'status_dt'=>General::toDate($row['status_dt']),
							'status'=>($row['status']=='T' ? $this->statusDesc('T') : $this->statusDesc('A')),
							'service'=>$row['service'],
							'first_dt'=>General::toDate($row['first_dt']),
							'amt_paid'=>$row['amt_paid'],
							'cust_type_desc'=>$row['cust_type_desc'],
							'product_desc'=>$row['product_desc'],
							'paid_type'=>($row['paid_type']=='M' ? Yii::t('service','Monthly')
											: ($row['paid_type']=='Y' ? Yii::t('service','Yearly')
												: ($row['paid_type']=='1' ? Yii::t('service','One time') : ''))
									),
						);
			}
		} 
		return $rtn;
	}

	protected function getServiceKAList($id, $code, $name, $city) {
        $suffix = Yii::app()->params['envSuffix'];
		$rtn = array();
		$name = str_replace("'","\'",$name);
		$sql = "select a.*, c.description as cust_type_desc, d.description as product_desc   
				from swoper$suffix.swo_service_ka a
				left outer join swoper$suffix.swo_service_ka b on a.company_name=b.company_name 
					and a.status_dt < b.status_dt and a.cust_type=b.cust_type
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				left outer join swoper$suffix.swo_product d on a.product_id=d.id 
				where b.id is null and a.city='$city'
				and (a.company_id=$id or a.company_name like concat('$code',' %') 
				or a.company_name like concat('%','$name'));
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$rtn[] = array(
							'status_dt'=>General::toDate($row['status_dt']),
							'status'=>($row['status']=='T' ? $this->statusDesc('T') : $this->statusDesc('A')),
							'service'=>$row['service'],
							'first_dt'=>General::toDate($row['first_dt']),
							'amt_paid'=>$row['amt_paid'],
							'cust_type_desc'=>$row['cust_type_desc'],
							'product_desc'=>$row['product_desc'],
							'paid_type'=>($row['paid_type']=='M' ? Yii::t('service','Monthly')
											: ($row['paid_type']=='Y' ? Yii::t('service','Yearly')
												: ($row['paid_type']=='1' ? Yii::t('service','One time') : ''))
									),
						);
			}
		}
		return $rtn;
	}

	protected function getServiceIDList($id, $code, $name, $city) {
        $suffix = Yii::app()->params['envSuffix'];
		$rtn = array();
		$name = str_replace("'","\'",$name);
		$sql = "select a.*, c.description as cust_type_desc, d.description as product_desc   
				from swoper$suffix.swo_serviceid a
				left outer join swoper$suffix.swo_serviceid b on a.company_name=b.company_name 
					and a.status_dt < b.status_dt and a.cust_type=b.cust_type
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				left outer join swoper$suffix.swo_product d on a.product_id=d.id 
				where b.id is null and a.city='$city'
				and (a.company_id=$id or a.company_name like concat('$code',' %') 
				or a.company_name like concat('%','$name'));
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$rtn[] = array(
							'status_dt'=>General::toDate($row['status_dt']),
							'status'=>($row['status']=='T' ? $this->statusDesc('T') : $this->statusDesc('A')),
							'service'=>$row['service'],
							'first_dt'=>General::toDate($row['first_dt']),
							'amt_paid'=>$row['amt_paid'],
							'cust_type_desc'=>$row['cust_type_desc'],
							'product_desc'=>$row['product_desc'],
							'paid_type'=>Yii::t('service','Monthly'),
						);
			}
		}
		return $rtn;
	}
	
	public function getCriteria() {
		$rtn1 = parent::getCriteria();
		$rtn2 = array(
					'company_name'=>$this->company_name
				);
		return array_merge($rtn1, $rtn2);
	}
	
	public function statusDesc($invalue) {
		switch($invalue) {
			case 'A': return Yii::t('customer','Active'); break;
			case 'T': return Yii::t('customer','Terminated'); break;
			default: return Yii::t('customer','Unknown');
		};
	}

	public function AjaxCompanyName($group){
        $suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city_allow();//swoper$suffix.swo_service
        $html = "";
        if($group!==""){
            $group = str_replace("'","\'",$group);
            $records = Yii::app()->db->createCommand()->select('a.id,a.code,a.name')
                ->from("swoper$suffix.swo_company a")
                ->where("(a.name like '%$group%' or a.code like '%$group%') and a.city in ($city) and a.status!=2")
                ->queryAll();
            if($records){
                foreach ($records as $row){
                    $html.="<li><a class='clickThis'>".$row['name']."</a>";
                }
            }else{
                $html = "<li><a>没有结果</a></li>";
            }
        }else{
            $html = "<li><a>请输入客户名称</a></li>";
        }
        return $html;
    }
}
