<?php

class ClueInvoiceList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'invoice_name'=>Yii::t('clue','invoice name'),//门店名称
			'city'=>Yii::t('clue','city'),//开票抬头
			'invoice_header'=>Yii::t('clue','invoice header'),//开票抬头
			'tax_id'=>Yii::t('clue','tax id'),//税号
			'invoice_address'=>Yii::t('clue','invoice address'),//开票地址
            'invoice_type'=>Yii::t('clue','invoice type'),//
		);
	}
	
	public function retrieveDataByClueAndPage($clue_id,$pageNum=1)
	{
        $suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select a.*,b.name as city_name
				from sal_clue_invoice a
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				where a.clue_id='{$clue_id}' ";
		$sql2 = "select count(a.id)
				from sal_clue_invoice a
				LEFT JOIN security{$suffix}.sec_city b ON a.city=b.code
				where a.clue_id='{$clue_id}' ";
		$clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'invoice_name':
                    $clause .= General::getSqlConditionClause('a.invoice_name',$svalue);
                    break;
                case 'city':
                    $clause .= General::getSqlConditionClause('b.name',$svalue);
                    break;
                case 'invoice_header':
                    $clause .= General::getSqlConditionClause('a.invoice_header',$svalue);
                    break;
                case 'tax_id':
                    $clause .= General::getSqlConditionClause('a.tax_id',$svalue);
                    break;
                case 'invoice_address':
                    $clause .= General::getSqlConditionClause('a.invoice_address',$svalue);
                    break;
            }
        }
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
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
                        'invoice_name'=>$record['invoice_name'],
                        'invoice_type'=>CGetName::getInvoiceTypeStrByKey($record['invoice_type']),
                        'city'=>$record['city_name'],
                        'invoice_header'=>$record['invoice_header'],
                        'tax_id'=>$record['tax_id'],
                        'invoice_address'=>$record['invoice_address'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_ClueInvoice'] = $this->getCriteria();
		return true;
	}
}
