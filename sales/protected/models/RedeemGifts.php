<?php

class RedeemGifts extends CListPageModel
{

    public $id = 0;
    public $gift_name;
    public $bonus_point;
    public $inventory;
    public $city;
    public $city_name;
    public $status;
   public $employee_name;
    public $remark;
    public $searchField;
    public $searchValue;
    public $no_of_attm = array(
        'icut'=>0
    );
    public $docType = 'ICUT';
    public $docMasterId = array(
        'icut'=>0
    );
    public $files;
    public $removeFileId = array(
        'icut'=>0
    );
    public function tableName()
    {
        return 'sal_redeem_score';
    }

    public function attributeLabels()
    {
        return array(
//            'id'=>Yii::t('redeem','Gift id'),
            'gift_name'=>Yii::t('redeem','Gift Name'),
            'bonus_point'=>Yii::t('redeem','Bonus Point'),
            'inventory'=>Yii::t('redeem','Inventory'),
            'city'=>Yii::t('redeem','City'),
            'city_name'=>Yii::t('redeem','City Name'),
            'remark'=>Yii::t('redeem','Remark'),
        );
    }

    public function retrieveDataByPage($pageNum=1)
    {
        //$city_allow = Yii::app()->user->city_allow();
        $city_allow = Yii::app()->user->city_allow();
        //$city = Yii::app()->user->city();
       // var_dump($city_allow);die();
        $sql1 = "select *
				from sal_redeem_gifts
				where city IN ($city_allow) 
			";
        $sql2 = "select count(id)
				from sal_redeem_gifts
				where city IN ($city_allow) 
			";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'gift_name':
                    $clause .= General::getSqlConditionClause('gift_name', $svalue);
                    break;
                case 'bonus_point':
                    $clause .= General::getSqlConditionClause('bonus_point', $svalue);
                    break;
                case 'city_name':
                    $clause .= General::getSqlConditionClause('city_name',$svalue);
                    break;
//                   $clause .= ' and city_name in '.CreditRequestList::getCityCodeSqlLikeName($svalue);//CreditRequestList::getCityCodeSqlLikeName($svalue)
            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        } else
            $order = " order by id desc";

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'gift_name'=>$record['gift_name'],
                    'bonus_point'=>$record['bonus_point'],
                    'inventory'=>$record['inventory'],
                    'city_name'=>$record['city_name'],
                    'city'=>Yii::app()->user->city,
                    'remark'=>$record['remark'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['gift_op01'] = $this->getCriteria();
        return true;
    }
    //獲取當前用戶的可用積分
    function getNowIntegral($staffId=0,$lcd=""){
        $user_id = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select * from hr$suffix.hr_binding where user_id ='".$user_id."'";
        $hr_binding=Yii::app()->db->createCommand($sql1)->queryRow();
        $sql2 = 'select * from sal_redeem_score where employee_id ='.$hr_binding['employee_id'];
        $redeem_score = Yii::app()->db->createCommand($sql2)->queryRow();
//        var_dump($redeem_score);die();
        return array(
            "cut"=>$redeem_score['score']?$redeem_score['score']:0,//可用積分
            "sum"=>$redeem_score['score'],//總積分
        );
    }
   function retrieveData($index) {
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("*,docman$suffix.countdoc('ICUT',id) as icutdoc")
            ->from("sal_redeem_gifts")->where("id=:id and city in($city_allow)",array(":id"=>$index))->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $this->id = $row['id'];
                $this->gift_name = $row['gift_name'];
                $this->bonus_point = $row['bonus_point'];
                $this->inventory = $row['inventory'];
                $this->city_name = $row['city_name'];
                $this->city = $row['city'];
                $this->remark = $row['remark'];
                $this->no_of_attm['icut'] = $row['icutdoc'];
                break;

            }
        }
        return true;
    }

}
