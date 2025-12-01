<?php

class ClueBoxList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'clue_code'=>Yii::t('clue','clue code'),//线索编号
            'clue_type'=>Yii::t('clue','clue type'),//线索类型
			'cust_name'=>Yii::t('clue','customer name'),//客户名
            'clue_status'=>Yii::t('clue','clue status'),//线索状态
            'cust_class_group'=>Yii::t('clue','trade type'),//行业类别
			'cust_class'=>Yii::t('clue','trade type'),//行业类别
			'cust_person'=>Yii::t('clue','customer person'),//联系人
			'city'=>Yii::t('clue','city manger'),//城市
			'rec_type'=>Yii::t('clue','assign type'),//分配类型
			'district'=>Yii::t('clue','district'),//区域
			'clue_source'=>Yii::t('clue','clue source'),//线索来源
			'last_date'=>Yii::t('clue','last flow date'),//下次跟进时间
			'rec_employee_id'=>Yii::t('clue','rec employee'),//跟进员工
			'end_date'=>Yii::t('clue','end flow date'),//最近跟进时间
			'lcd'=>Yii::t('clue','lcd'),//创建时间
		);
	}

    public function searchColumns() {
        $suffix = Yii::app()->params['envSuffix'];
        $search = array(
            'clue_code'=>"a.clue_code",
            'cust_name'=>"a.cust_name",
            'clue_type'=>"(case a.clue_type when 1 then '地推' else 'KA' end)",
            'cust_class'=>"ifnull(g.name,'')",
            'cust_person'=>'a.cust_person',
            'clue_source'=>'sra.pro_name',
            'last_date'=>"date_format(a.last_date,'%Y/%m/%d')",
            'end_date'=>"date_format(a.end_date,'%Y/%m/%d')",
        );
        if (!Yii::app()->user->isSingleCity()) $search['city'] = 'b.name';
        return $search;
    }
	
	public function retrieveDataByPage($pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $citylist = Yii::app()->user->city_allow();
        //$staff_id = CGetName::getEmployeeIDByMy();
		$sql1 = "select a.*,sra.pro_name as clue_source_name,b.name as city_name,concat(f.name,' (',f.code,')') as employee_name,
                (case a.clue_type when 1 then '地推' else 'KA' end) as clue_type_name,
                g.name as cust_class_name 
				from sal_clue a
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON a.rec_employee_id=f.id
				LEFT JOIN swoper{$suffix}.swo_nature_type g ON a.cust_class=g.id
				LEFT JOIN sal_ka_sra sra ON sra.id=a.clue_source 
				where a.del_num=0 and a.rec_type!=1 and a.city in ({$citylist}) ";
		$sql2 = "select count(a.id)
				from sal_clue a
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				LEFT JOIN hr{$suffix}.hr_employee f ON a.rec_employee_id=f.id
				LEFT JOIN swoper{$suffix}.swo_nature_type g ON a.cust_class=g.id
				LEFT JOIN sal_ka_sra sra ON sra.id=a.clue_source 
				where a.del_num=0 and a.rec_type!=1 and a.city in ({$citylist}) ";
		$clause = "";
        if(!Yii::app()->user->validRWFunction('CM01')){//唯读权限
            $clause.=" and a.rec_type=3 ";
        }
        $static = $this->staticSearchColumns();
        $columns = $this->searchColumns();
        if (!empty($this->searchField) && (!empty($this->searchValue) || in_array($this->searchField, $static) || $this->isAdvancedSearch())) {
            if ($this->isAdvancedSearch()) {
                $clause.= $this->buildSQLCriteria();
            } elseif (in_array($this->searchField, $static)) {
                $clause .= 'and '.$columns[$this->searchField];
            } else {
                $svalue = str_replace("'","\'",$this->searchValue);
                $clause .= General::getSqlConditionClause($columns[$this->searchField],$svalue);
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
                        'clue_code'=>$record['clue_code'],
                        'cust_name'=>$record['cust_name'],
                        'clue_type'=>$record['clue_type_name'],
                        'cust_class'=>$record['cust_class_name'],
                        'cust_person'=>$record['cust_person'],
                        'city'=>$record['city_name'],
                        'rec_type'=>CGetName::getAssignTypeStrByKey($record['rec_type']),
                        'clue_source'=>$record['clue_source_name'],
                        'clue_status'=>CGetName::getClueStatusStrByKey($record['clue_status']),
                        'last_date'=>$record['last_date'],
                        'rec_employee_id'=>$record['employee_name'],
                        'end_date'=>$record['end_date'],
                        'lcd'=>$record['lcd'],
                        'assign_bool'=>true,
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_ClueBoxList'] = $this->getCriteria();
		return true;
	}
}
