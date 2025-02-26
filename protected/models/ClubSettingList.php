<?php

class ClubSettingList extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'explain_text'=>Yii::t('club','explain'),
			'effect_date'=>Yii::t('club','effect date'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select * 
				from sal_club_setting 
				where 1=1 
			";
		$sql2 = "select count(id)
				from sal_club_setting 
				where 1=1 
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'explain_text':
					$clause .= General::getSqlConditionClause('explain_text',$svalue);
					break;
				case 'effect_date':
					$clause .= General::getSqlConditionClause('effect_date',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by id desc ";
        }

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();

		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
			    $this->resetJson($record);
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'explain_text'=>$record['explain_text'],
                    'set_json'=>$record['set_json'],
                    'effect_date'=>General::toMyDate($record['effect_date']),
                );
			}
		}
		$session = Yii::app()->session;
		$session['clubSetting_c01'] = $this->getCriteria();
		return true;
	}

	private function resetJson(&$record){
	    $list = json_decode($record["set_json"],true);
	    $settingList = ClubSettingForm::settingList();
	    foreach ($settingList as $key=>$setting){
	        if(!key_exists($key,$list)){
	            $list[$key]=$setting;
            }
        }
        $record["set_json"] = $list;
    }

    public static function getSalesStrForList($list){
	    $str = $list["number"];
        if($list["type"]==1){//百分比
            $str.="%";
        }else{
            $str.="人";
        }
        return $str;
    }
}
