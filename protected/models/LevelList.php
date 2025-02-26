<?php

class LevelList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'level'=>Yii::t('code','Level'),
			'new_level'=>Yii::t('code','New Level'),
            'start_fraction'=>Yii::t('code','Start Fraction'),
            'end_fraction'=>Yii::t('code','End Fraction'),
            'new_fraction'=>Yii::t('code','New Fraction'),
            'reward'=>Yii::t('code','Reward'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
		$sql1 = "select *
				from sal_level a
				";
		$sql2 = "select count(id)
				from sal_level a
				";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'level':
					$clause .= General::getSqlConditionClause('level',$svalue);
					break;
				case 'new_level':
					$clause .= General::getSqlConditionClause('new_level',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'level':
					$order .= " order by level ";
					break;
				case 'new_level':
					$order .= " order by new_level ";
                    break;
                case 'start_fraction':
                    $order .= " order by start_fraction ";
                    break;
                case 'end_fraction':
                    $order .= " order by end_fraction ";
                    break;
                case 'new_fraction':
                    $order .= " order by new_fraction ";
                    break;
                case 'reward':
                    $order .= " order by reward ";
					break;
			}
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
						'level'=>$record['level'],
						'new_level'=>$record['new_level'],
                        'start_fraction'=>$record['start_fraction'],
                        'end_fraction'=>$record['end_fraction'],
                        'new_fraction'=>$record['new_fraction'],
                        'reward'=>$record['reward'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['criteria_hc05'] = $this->getCriteria();
		return true;
	}

}
