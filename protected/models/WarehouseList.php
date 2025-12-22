<?php
//2024年9月28日09:28:46

class WarehouseList extends CListPageModel
{
    public $inventoryJD=array();

	public function attributeLabels()
	{
		return array(	
			'goods_code'=>Yii::t('procurement','Goods Code'),
			'name'=>Yii::t('procurement','Name'),
			'unit'=>Yii::t('procurement','Unit'),
			'inventory'=>Yii::t('procurement','Inventory'),
			'min_num'=>Yii::t('procurement','min inventory'),
            'jd_classify_name'=>Yii::t('procurement','Classify'),
            'display'=>Yii::t('procurement','judge for visible'),
            'cost_price'=>Yii::t('procurement','price history'),
            'price'=>Yii::t('procurement','Price（RMB）'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$city = Yii::app()->user->city();
		$sql1 = "select a.* 
				from opr_warehouse a
				where (city = '$city' or  local_bool=0)
			";
		$sql2 = "select count(a.id) from opr_warehouse a
				where (city = '$city' or  local_bool=0)
			";
		$clause = "";
		if($this->searchField == 'inventory'){
		    if(empty($this->searchValue)){
                $svalue = 0;
            }else{
                $svalue = str_replace("'","\'",$this->searchValue);
            }
            $clause .= "and inventory = '$svalue' ";
        }elseif (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'goods_code':
					$clause .= General::getSqlConditionClause('a.goods_code', $svalue);
					break;
				case 'name':
					$clause .= General::getSqlConditionClause('a.name', $svalue);
					break;
				case 'display':
                    $svalue = (strpos($svalue,Yii::t("misc","No"))!==false)?0:1;
					$clause .= General::getSqlConditionClause('a.display', $svalue);
					break;
				case 'type':
					$clause .= General::getSqlConditionClause('a.type', $svalue);
					break;
				case 'unit':
					$clause .= General::getSqlConditionClause('a.unit', $svalue);
					break;
				case 'inventory':
					//$clause .= General::getSqlConditionClause('inventory', $svalue);
					$clause .= "and a.inventory = '$svalue' ";
					break;
				case 'jd_classify_name':
                    $clause .= General::getSqlConditionClause('a.jd_classify_name', $svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
		    if("inventory" === $this->orderField){
                $order .= " order by CAST(inventory AS DECIMAL) ";
            }else{
                $order .= " order by a.".$this->orderField." ";
            }
			if ($this->orderType=='D') $order .= "desc ";
		} else
			$order = " order by z_index desc, id desc";

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$this->attr = array();
		if (count($records) > 0) {
		    $searchCode=array();
			foreach ($records as $k=>$record) {
                $searchCode[]=$record['goods_code'];
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'name'=>$record['name'],
                    'unit'=>$record['unit'],
                    'min_num'=>$record['min_num'],
                    'display'=>empty($record['display'])?Yii::t("misc","No"):Yii::t("misc","Yes"),
                    'jd_classify_name'=>$record['jd_classify_name'],
                    //'classify_id'=>ClassifyForm::getClassifyToId($record['classify_id']),
                    'inventory'=>0,
                    'price'=>self::getNowWarehousePrice($record["id"]),
                    'goods_code'=>$record['goods_code'],
                    'color'=>"",
                    //'goodsHistory'=>self::getGoodsHistory($record['id']),
                );
			}
            $searchData=array(
                "material_number"=>$searchCode,
                "org_number"=>CurlForDelivery::getJDCityCodeForCity($city),
                "warehouse_number"=>CurlForDelivery::getJDStoreListForCity($city),
            );
            $this->inventoryJD = CurlForDelivery::getWarehouseGoodsStoreForJD(array("data"=>$searchData));
		}
		$session = Yii::app()->session;
		$session['warehouse_ya01'] = $this->getCriteria();
		return true;
	}

	//獲取物品由訂單扣減的歷史 (最多顯示5條)
	public static function getGoodsHistory($goods_id,$city=""){
        $city = empty($city)?Yii::app()->user->city():$city;
	    $html="";
        $rows = Yii::app()->db->createCommand()->select("a.*")
            ->from("opr_order_goods a")
            ->leftJoin("opr_order b","a.order_id=b.id")
            ->where('b.city=:city and a.goods_id=:goods_id and a.order_status="finished"',array(
                ':city'=>$city,
                ':goods_id'=>$goods_id,
            ))->order('a.lud desc')->limit(5)->queryAll();
	    if($rows){
            foreach ($rows as $historyList){
                $html.= "<tr>";
                $html.= "<td>".$historyList["lud"]."</td>";
                $html.= "<td>".$historyList["lcu"]."</td>";
                $html.= "<td>".$historyList["confirm_num"]."</td>";
                $html.= "</tr>";
            }
        }else{
            $html = "<tr><td colspan='3' class='text-center'>沒有记录</td></tr>";
        }
        return $html;
    }

    //分類的模糊查詢
    private function getClassifyToSql($str){
        $rows = Yii::app()->db->createCommand()->select("id")
            ->from("opr_classify")
            ->where("class_type = 'Warehouse' and name like '%$str%'")->queryAll();
        if($rows){
            $arr = array();
            foreach ($rows as $row){
                array_push($arr,"'".$row["id"]."'");
            }
            $sqlStr = implode(",",$arr);
            return " and classify_id in ($sqlStr)";
        }else{
            return " and classify_id in ('')";
        }
    }

    //获取物料的最新价格
    public static function getNowWarehousePrice($warehouse_id,$city='',$applyDate=''){
        //$city = empty($city)?Yii::app()->user->city():$city;
        $year = date_format(date_create($applyDate),"Y");
        $month = date_format(date_create($applyDate),"n");
        $row = Yii::app()->db->createCommand()->select("price")
            ->from("opr_warehouse_price")
            ->where("warehouse_id=:id and (year<'{$year}' or (year='{$year}' and month<='{$month}'))",
                array(":id"=>$warehouse_id))
            ->order("year desc,month desc")->queryRow();
        return $row?floatval($row["price"]):0;
    }
}
