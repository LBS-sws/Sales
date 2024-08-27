<?php

class RABotList extends KABotList {

    public function retrieveDataByPage($pageNum=1)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city_allow = Yii::app()->user->city_allow();
        if(Yii::app()->user->validFunction('CN16')){
            //$whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}' or h.city in ({$city_allow}))";
            $whereSql = "";//2023/06/16 改為可以看的所有記錄
        }elseif(Yii::app()->user->validFunction('CN19')){
            $whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}' or h.city in ({$city_allow}))";
        }else{
            $whereSql = " and (a.kam_id='{$this->employee_id}' or a.support_user='{$this->employee_id}')";
        }
        if($this->sign_odds!==""){
            if(empty($this->orderField)){
                $this->orderField = "expr";
            }
            $this->sign_odds = is_numeric($this->sign_odds)?intval($this->sign_odds):0;
            $whereSql.=" and a.sign_odds={$this->sign_odds} ";
        }
        $sql1 = "select a.id,a.ava_show_date,a.sign_odds,a.follow_date,a.apply_date,a.customer_no,a.customer_name,a.contact_user,a.kam_id,
                b.pro_name as class_name,
                f.pro_name as source_name,
                CONCAT('(',g.rate_num,'%) ',g.pro_name) as link_name, 
                CONCAT(h.name,' (',h.code,')') as kam_name 
				from sal_ra_bot a
				LEFT JOIN sal_ka_class b ON a.class_id=b.id
				LEFT JOIN sal_ka_source f ON a.source_id=f.id
				LEFT JOIN sal_ka_link g ON a.link_id=g.id
				LEFT JOIN hr{$suffix}.hr_employee h ON a.kam_id=h.id
				where a.id>0 {$whereSql}";
        $sql2 = "select count(a.id)
				from sal_ra_bot a
				LEFT JOIN sal_ka_class b ON a.class_id=b.id
				LEFT JOIN sal_ka_source f ON a.source_id=f.id
				LEFT JOIN sal_ka_link g ON a.link_id=g.id
				LEFT JOIN hr{$suffix}.hr_employee h ON a.kam_id=h.id
				where a.id>0 {$whereSql}";
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
                case 'available_date':
                    $clause .= General::getSqlConditionClause('a.ava_show_date',$svalue);
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
                case 'sign_odds':
                    $svalue = is_numeric($svalue)?$svalue:0;
                    $clause .= "and IFNULL(a.sign_odds,0)>={$svalue} ";
                    break;
            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            if($this->orderField==="expr"){ //特殊排序
                $beforeDate = date("Y-m-d",strtotime("-90 days"));
                $order .= " order by if(a.follow_date>='$beforeDate','$beforeDate',a.follow_date) asc,a.follow_date desc ";
            }else{
                $order .= " order by ".$this->orderField." ";
                if ($this->orderType=='D') $order .= "desc ";
            }
        }else{
            $order .= " order by a.follow_date desc ";
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
                $color = $this->getTdColor($record);
                $sql = "select info_date,info_text from sal_ra_bot_info where bot_id=".$record['id']." order by info_date desc";
                $infoRows = Yii::app()->db->createCommand($sql)->queryAll();
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'apply_date'=>General::toDate($record['apply_date']),
                    'available_date'=>General::toDate($record['ava_show_date']),
                    'customer_no'=>$record['customer_no'],
                    'customer_name'=>$record['customer_name'],
                    'contact_user'=>$record['contact_user'],
                    'class_id'=>$record['class_name'],
                    'source_id'=>$record['source_name'],
                    'link_id'=>$record['link_name'],
                    'kam_id'=>$record['kam_name'],
                    'color'=>$color,
                    'sign_odds'=>KABotForm::getSignOddsListForId($record['sign_odds'],true),
                    'follow_date'=>empty($record['follow_date'])?"":General::toDate($record['follow_date']),
                    'detail'=>$infoRows?$infoRows:array(),
                );
            }
        }
        $session = Yii::app()->session;
        $session['rABot_c01'] = $this->getCriteria();
        return true;
    }
}
