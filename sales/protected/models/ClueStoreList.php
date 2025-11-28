<?php

class ClueStoreList extends CListPageModel
{

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'store_code'=>Yii::t('clue','store code'),//门店名称
			'store_name'=>Yii::t('clue','store name'),//门店名称
            'cust_name'=>Yii::t('clue','customer name'),//客户名
			'city'=>Yii::t('clue','city manger'),//城市
            'address'=>Yii::t('clue','address'),//详细地址
            'create_staff'=>Yii::t('clue','sales'),//销售
            'cust_class'=>Yii::t('clue','trade type'),//行业类别
			'cust_person'=>Yii::t('clue','customer person'),//联络人
			'cust_tel'=>Yii::t('clue','person tel'),//联络人电话
			'invoice_header'=>Yii::t('clue','invoice header'),//开票抬头
			'tax_id'=>Yii::t('clue','tax id'),//税号
			'invoice_address'=>Yii::t('clue','invoice address'),//开票地址
            'rec_employee_id'=>Yii::t('clue','rec employee'),//跟进员工
            'yewudalei'=>Yii::t('clue','yewudalei'),//跟进员工
            'store_status'=>Yii::t('clue','status'),//跟进员工
            'u_id'=>Yii::t('clue','u id'),//
		);
	}
	
	public function retrieveDataByClueAndPage($clue_id,$pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select  a.*,b.name as city_name,g.cust_name,g.yewudalei,
                  f.invoice_header,f.tax_id,f.invoice_address,h.name as cust_class_name 
				from sal_clue_store a
				LEFT JOIN sal_clue g on a.clue_id=g.id
				LEFT JOIN swoper{$suffix}.swo_nature_type h ON g.cust_class=h.id
				LEFT JOIN sal_clue_invoice f on a.invoice_id=f.id
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				where g.del_num=0 and g.rec_type=1 and a.clue_id='{$clue_id}'";
		$sql2 = "select count(a.id)
				from sal_clue_store a
				LEFT JOIN sal_clue g on a.clue_id=g.id
				LEFT JOIN swoper{$suffix}.swo_nature_type h ON g.cust_class=h.id
				LEFT JOIN sal_clue_invoice f on a.invoice_id=f.id
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				where g.del_num=0 and g.rec_type=1 and a.clue_id='{$clue_id}'";
		$clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'store_name':
                    $clause .= General::getSqlConditionClause('a.store_name',$svalue);
                    break;
                case 'city':
                    $clause .= General::getSqlConditionClause('b.name',$svalue);
                    break;
                case 'address':
                    $clause .= General::getSqlConditionClause('a.address',$svalue);
                    break;
                case 'cust_person':
                    $clause .= General::getSqlConditionClause('a.cust_person',$svalue);
                    break;
                case 'cust_tel':
                    $clause .= General::getSqlConditionClause('a.cust_tel',$svalue);
                    break;
                case 'invoice_header':
                    $clause .= General::getSqlConditionClause('f.invoice_header',$svalue);
                    break;
                case 'tax_id':
                    $clause .= General::getSqlConditionClause('f.tax_id',$svalue);
                    break;
                case 'invoice_address':
                    $clause .= General::getSqlConditionClause('f.invoice_address',$svalue);
                    break;
                case 'u_id':
                    $clause .= General::getSqlConditionClause('a.u_id',$svalue);
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
						'clue_id'=>$record['clue_id'],
                        'store_name'=>$record['store_name'],
                        'city'=>$record['city_name'],
                        'address'=>$record['address'],
                        'cust_person'=>$record['cust_person'],
                        'cust_tel'=>$record['cust_tel'],
                        'invoice_header'=>$record['invoice_header'],
                        'tax_id'=>$record['tax_id'],
                        'invoice_address'=>$record['invoice_address'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_ClueStore'] = $this->getCriteria();
		return true;
	}
}
