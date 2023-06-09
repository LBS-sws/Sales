<?php

class KABotList extends CListPageModel
{
    public $employee_id;
    public $employee_code;
    public $employee_name;
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'apply_date'=>Yii::t('ka','apply date'),
			'customer_no'=>Yii::t('ka','customer no'),
			'customer_name'=>Yii::t('ka','customer name'),
			'contact_user'=>Yii::t('ka','contact user'),
			'source_id'=>Yii::t('ka','source name'),
			'class_id'=>Yii::t('ka','class name'),
			'kam_id'=>Yii::t('ka','KAM'),
			'link_id'=>Yii::t('ka','link name'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
        if(Yii::app()->user->validFunction('CN15')){
            $whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}' or h.city in ({$city_allow}))";
        }else{
            $whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}')";
        }
		$sql1 = "select a.id,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,
                b.pro_name as class_name,
                f.pro_name as source_name,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name, 
                CONCAT(h.name,' (',h.code,')') as kam_name 
				from sal_ka_bot a
				LEFT JOIN sal_ka_class b ON a.class_id=b.id
				LEFT JOIN sal_ka_source f ON a.source_id=f.id
				LEFT JOIN sal_ka_link g ON a.link_id=g.id
				LEFT JOIN hr{$suffix}.hr_employee h ON a.kam_id=h.id
				where a.id>0 {$whereSql}";
		$sql2 = "select count(a.id)
				from sal_ka_bot a
				LEFT JOIN sal_ka_class b ON a.class_id=b.id
				LEFT JOIN sal_ka_source f ON a.source_id=f.id
				LEFT JOIN sal_ka_link g ON a.link_id=g.id
				LEFT JOIN hr{$suffix}.hr_employee h ON a.kam_id=h.id
				where a.id>0 ";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'customer_no':
					$clause .= General::getSqlConditionClause('a.customer_no',$svalue);
					break;
				case 'customer_name':
					$clause .= General::getSqlConditionClause('a.customer_name',$svalue);
					break;
				case 'contact_user':
					$clause .= General::getSqlConditionClause('a.contact_user',$svalue);
					break;
				case 'class_id':
					$clause .= General::getSqlConditionClause('b.pro_name',$svalue);
					break;
				case 'source_id':
					$clause .= General::getSqlConditionClause('f.pro_name',$svalue);
					break;
				case 'link_id':
					$clause .= General::getSqlConditionClause("CONCAT('(',g.rate_num,') ',g.pro_name)",$svalue);
					break;
				case 'kam_id':
					$clause .= General::getSqlConditionClause("CONCAT(h.name,' (',h.code,')')",$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by a.id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
					$this->attr[] = array(
						'id'=>$record['id'],
						'apply_date'=>General::toDate($record['apply_date']),
						'customer_no'=>$record['customer_no'],
						'customer_name'=>$record['customer_name'],
						'contact_user'=>$record['contact_user'],
						'class_id'=>$record['class_name'],
						'source_id'=>$record['source_name'],
						'link_id'=>$record['link_name'],
						'kam_id'=>$record['kam_name'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['kABot_c01'] = $this->getCriteria();
		return true;
	}

	public function downExcel($year){
        $rptModel = new RptKABot();
        $criteria=array(
            "city"=>Yii::app()->user->city(),
            "city_allow"=>Yii::app()->user->city_allow(),
            "year"=>$year,
            "employee_id"=>$this->employee_id,
            "auto_all"=>Yii::app()->user->validFunction('CN15'),
        );
        $param['RPT_NAME'] = "KA Bot";
        $param['CITY'] = $criteria["city"];
        $param['YEAR'] = $criteria["year"];
        $param['CRITERIA'] = json_encode($criteria);
        $rptModel->criteria = $param;
        $rptModel->downExcel("KA项目({$year})");
    }
}
