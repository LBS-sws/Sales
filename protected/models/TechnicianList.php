<?php
//2024年9月28日09:28:46

class TechnicianList extends CListPageModel
{
    public $searchTimeStart;//開始日期
    public $searchTimeEnd;//結束日期
    public $goods_name;//結束日期
    public $jd_order_type=0;//申请类型
    public function attributeLabels()
    {
        return array(
            'order_code'=>Yii::t('procurement','Order Code'),
            'order_class'=>Yii::t('procurement','Order Class'),
            'activity'=>Yii::t('procurement','Activity Code'),
            'goods_list'=>Yii::t('procurement','Goods List'),
            'order_user'=>Yii::t('procurement','Order User'),
            'technician'=>Yii::t('procurement','Technician'),
            'activity_id'=>Yii::t('procurement','Order of Activity'),
            'status'=>Yii::t('procurement','Order Status'),
            'city'=>Yii::t('procurement','Order For City'),
            'lcd'=>Yii::t('procurement','Apply for time'),
            'jd_order_type'=>Yii::t('procurement','apply type'),
            'goods_name'=>Yii::t('procurement','Goods Name'),
        );
    }

    public function rules(){
        return array(
            array('attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, searchTimeStart, searchTimeEnd','safe',),
        );
    }

    public function retrieveDataByPage($pageNum=1)
    {
        //order_user = '$userName' OR technician = '$userName'
        $userName = Yii::app()->user->name;
        if($this->jd_order_type==1){//销售出库
            $whereSql = " and a.judge_type=1 ";
        }else{
            $whereSql = " and a.judge_type=2 ";
        }
        $sql1 = "select a.*
				from opr_order a
				where a.judge=0 AND a.lcu='$userName' {$whereSql}
			";
        $sql2 = "select count(a.id)
				from opr_order a
				where a.judge=0 AND a.lcu='$userName' {$whereSql}
			";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'lcd':
                    $clause .= General::getSqlConditionClause('a.lcd', $svalue);
                case 'order_code':
                    $clause .= General::getSqlConditionClause('a.order_code', $svalue);
                    break;
                case 'goods_name':
                    $clause .= ' and a.id in '.DeliveryList::getOrderIdSqlLikeGoodsName($svalue);
                    break;
            }
        }
        if (!empty($this->searchTimeStart) && !empty($this->searchTimeStart)) {
            $svalue = str_replace("'","\'",$this->searchTimeStart);
            $clause .= " and a.lcd >='$svalue 00:00:00' ";
        }
        if (!empty($this->searchTimeEnd) && !empty($this->searchTimeEnd)) {
            $svalue = str_replace("'","\'",$this->searchTimeEnd);
            $clause .= " and a.lcd <='$svalue 23:59:59' ";
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        } else
            $order = " order by a.id desc";

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                $record['jd_order_type'] = TechnicianList::getJDOrderTypeForId($record['id'],"jd_order_type");
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'order_code'=>$record['order_code'],
                    'order_class'=>Yii::t("procurement",$record['order_class']),
                    //'activity_id'=>$this->getActivityTitleToId($record['activity_id']),
                    'goods_list'=>WarehouseForm::getGoodsListToId($record['id']),
                    'order_user'=>$record['order_user'],
                    'technician'=>$record['technician'],
                    'status'=>$record['status'],
                    'city'=>$record['city'],
                    'jd_order_type'=>self::getApplyTypeStrForType($record['jd_order_type']),
                    'lcd'=>date("Y-m-d",strtotime($record['lcd'])),
                );
            }
        }
        $session = Yii::app()->session;
        $session['technician_ya0'.$this->jd_order_type] = $this->getCriteria();
        return true;
    }

    public function getCriteria() {
        return array(
            'searchField'=>$this->searchField,
            'searchValue'=>$this->searchValue,
            'orderField'=>$this->orderField,
            'orderType'=>$this->orderType,
            'noOfItem'=>$this->noOfItem,
            'pageNum'=>$this->pageNum,
            'filter'=>$this->filter,
            'searchTimeStart'=>$this->searchTimeStart,
            'searchTimeEnd'=>$this->searchTimeEnd,
        );
    }

    public static function getApplyTypeList(){
        return array(
            0=>Yii::t("procurement","technician apply"),
            1=>Yii::t("procurement","sales apply")
        );
    }

    public static function getApplyTypeStrForType($type){
        $type="".$type;
        $list = self::getApplyTypeList();
        if(key_exists($type,$list)){
            return $list[$type];
        }else{
            return $list[0];
        }
    }

    public static function getJDOrderTypeForId($order_id,$field_id='jd_order_type'){
        $row = Yii::app()->db->createCommand()->select("field_value")
            ->from("opr_send_set_jd")
            ->where("set_type='technician' and table_id=:id and field_id=:field_id",array(
                ":id"=>$order_id,
                ":field_id"=>$field_id
            ))->queryRow();
        if($row){
            return $row["field_value"];
        }else{
            return 0;
        }
    }

    public static function getSalesOutCityAllow($city){
        $arr = array($city);
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("code")
            ->from("security{$suffix}.sec_city_info")
            ->where("field_id='SALES_OUT' and field_value=:city",array(':city'=>$city))
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                if(!in_array($row['code'],$arr)){
                    $arr[]=$row["code"];
                }
            }
        }
        return $arr;
    }

    public static function getCompanyList($city,$code=""){
        $suffix = Yii::app()->params['envSuffix'];
        $cityList = self::getSalesOutCityAllow($city);
        $cityAllow = empty($city)?"0":implode("','",$cityList);
        $rows = Yii::app()->db->createCommand()->select("id,code,name,u_customer_code")
            ->from("swoper{$suffix}.swo_company")
            ->where("(city in ('{$cityAllow}') and status!=2) or u_customer_code=:code",array(':code'=>$code))
            ->queryAll();
        $list = array();
        if($rows){
            foreach ($rows as $row){
                $list[$row["u_customer_code"]] = $row["name"]."({$row["code"]})";
            }
        }
        return $list;
    }

    public static function getCompanyNameForCode($code,$city){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("name,code")
            ->from("swoper{$suffix}.swo_company")
            ->where("code=:code and city=:city",array(':code'=>$code,':city'=>$city))
            ->queryRow();
        if($row){
            return $row["name"]."({$row["code"]})";
        }else{
            return "";
        }
    }

    public static function getCompanyNameForUCode($code,$city=''){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("name,code")
            ->from("swoper{$suffix}.swo_company")
            ->where("u_customer_code=:code",array(':code'=>$code))
            ->queryRow();
        if($row){
            return $row["name"]."({$row["code"]})";
        }else{
            return "";
        }
    }
}
