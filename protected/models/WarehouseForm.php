<?php
//2024年9月28日09:28:46

class WarehouseForm extends CFormModel
{
	public $id;
	public $goods_code;
	public $name;
	public $unit;
	public $orderClass = "Warehouse";
	public $inventory;
	public $classify_id;
	public $price=0.00;
	public $costing;
	public $decimal_num;
	public $luu;
	public $lcu;
	public $min_num;
	public $matters;
	public $matching;
	public $z_index=1;
    public $display = 1;
    public $jd_classify_no;
    public $jd_classify_name;
    public $old_good_no;

    private $foreach_num = 0;

    public $jd_set = array();
    public static $jd_set_list=array(
        array("field_id"=>"jd_unit_code","field_type"=>"text","field_name"=>"jd unit code"),
        array("field_id"=>"jd_good_id","field_type"=>"text","field_name"=>"jd good id"),
        array("field_id"=>"jd_good_spec","field_type"=>"text","field_name"=>"jd good spec"),
    );

	public function attributeLabels()
	{
		return array(
            'old_good_no'=>Yii::t('procurement','Old Goods Code'),
            'goods_code'=>Yii::t('procurement','Goods Code'),
            'classify_id'=>Yii::t('procurement','Classify'),
            'name'=>Yii::t('procurement','Name'),
            'unit'=>Yii::t('procurement','Unit'),
            'inventory'=>Yii::t('procurement','Inventory'),
            'price'=>Yii::t('procurement','Price（RMB）'),
            'costing'=>Yii::t('procurement','Costing（RMB）'),
            'decimal_num'=>Yii::t('procurement','Decimal'),
            'min_num'=>Yii::t('procurement','min inventory'),
            'matching'=>Yii::t('procurement','matching'),
            'matters'=>Yii::t('procurement','matters'),
            'display'=>Yii::t('procurement','judge for visible'),
            'jd_classify_name'=>Yii::t('procurement','Classify'),
            'jd_classify_no'=>Yii::t('procurement','JD classify no'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('id, goods_code,display, min_num, name, unit, inventory, classify_id, price, costing, decimal_num, lcu, luu, matching, matters,
			jd_set,old_good_no,jd_classify_no,jd_classify_name','safe'),
            array('name','required'),
            //array('jd_classify_no,jd_classify_name','required'),
            array('unit','required'),
            //array('inventory','required'),
            //array('inventory','numerical','allowEmpty'=>false,'integerOnly'=>false),
            //array('min_num','required'),
            //array('min_num','numerical','allowEmpty'=>false,'integerOnly'=>false),
			array('name','validateId'),
			array('name','validateName'),
			array('goods_code','validateCode'),
			//array('price','validatePrice'),
		);
	}
//
	public function validateId($attribute, $params){
	    //Yii::app()->user->validFunction('YN04');
        $city = Yii::app()->user->city();
        $matching = "";
        $matters = "";
        if($this->getScenario() == "edit"){
            $row = Yii::app()->db->createCommand()->select("matching,matters")->from("opr_warehouse")
                ->where('id=:id and (city=:city or local_bool=0)', array(':id'=>$this->id,':city'=>$city))->queryRow();
            if($row){
                if(Yii::app()->user->validFunction('YN04')){
                    $matching = $this->matching;
                    $matters = $this->matters;
                }else{
                    $matching =$row["matching"];
                    $matters =$row["matters"];
                }
            }else{
                $message = "數據異常，請於管理員聯繫";
                $this->addError($attribute,$message);
            }
        }
        $this->matching = $matching;
        $this->matters = $matters;
	}
	public function validateName($attribute, $params){
        $city = Yii::app()->user->city();
        $id = -1;
        if(!empty($this->id)){
            $id = $this->id;
        }
        $rows = Yii::app()->db->createCommand()->select("id")->from("opr_warehouse")
            ->where('name=:name and id!=:id and (city = :city or local_bool=0)', array(':name'=>$this->name,':id'=>$id,':city'=>$city))->queryAll();
        if(count($rows)>0){
            $message = Yii::t('procurement','the name of already exists');
            $this->addError($attribute,$message);
        }
	}
	public function validateCode($attribute, $params){
        $city = Yii::app()->user->city();
        $id = -1;
        if(!empty($this->id)){
            $id = $this->id;
        }
        $rows = Yii::app()->db->createCommand()->select("id")->from("opr_warehouse")
            ->where('goods_code=:goods_code and id!=:id and (city = :city or local_bool=0)', array(':goods_code'=>$this->goods_code,':id'=>$id,':city'=>$city))->queryAll();
        if(count($rows)>0){
            $message = Yii::t('procurement','the Goods Code of already exists');
            $this->addError($attribute,$message);
        }
	}
	public function validatePrice($attribute, $params){
	    if(floatval($this->inventory)<=floatval($this->min_num)){
	        $this->z_index = 2;
        }else{
	        $this->z_index = 1;
        }
	}

    //刪除驗證
    public function deleteValidate(){
        $rs = Yii::app()->db->createCommand()->select()->from("opr_order_goods")->where('goods_id=:goods_id',array(':goods_id'=>$this->id))->queryAll();
        if($rs){
            return false;
        }else{
            return true;
        }
    }

	public function retrieveData($index) {
		$city = Yii::app()->user->city();
		$rows = Yii::app()->db->createCommand()->select("*,costPrice(id,now()) as cost_price")
            ->from("opr_warehouse")->where("id=:id and (city = :city or local_bool=0)",array(":id"=>$index,':city'=>$city))->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
                $searchData=array(
                    "material_number"=>array($row['goods_code']),
                    "org_number"=>CurlForDelivery::getJDCityCodeForCity($city),
                    "warehouse_number"=>CurlForDelivery::getJDStoreListForCity($city),
                );
                $inventoryJD = CurlForDelivery::getWarehouseGoodsStoreForJD(array("data"=>$searchData));
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->unit = $row['unit'];
                $this->classify_id = $row['classify_id'];
                $this->goods_code = $row['goods_code'];
                $this->inventory = key_exists($row['goods_code'],$inventoryJD)?$inventoryJD[$row['goods_code']]["jd_store_sum"]:"0";
                $this->costing = sprintf("%.2f",$row['costing']);
                $this->decimal_num = empty($row['decimal_num'])?"否":$row['decimal_num'];
                $this->price = WarehouseList::getNowWarehousePrice($row["id"]);
                $this->min_num = 0;
                $this->matters = $row['matters'];
                $this->matching = $row['matching'];
                $this->display = $row['display'];
                $this->old_good_no = $row['old_good_no'];
                $this->jd_classify_no = $row['jd_classify_no'];
                $this->jd_classify_name = $row['jd_classify_name'];
                $setRows = Yii::app()->db->createCommand()->select("field_id,field_value")
                    ->from("opr_send_set_jd")->where("table_id=:table_id and set_type='warehouse'",array(":table_id"=>$index))->queryAll();
                $setList = array();
                foreach ($setRows as $setRow){
                    $setList[$setRow["field_id"]] = $setRow["field_value"];
                }
                $this->jd_set=array();
                foreach (self::$jd_set_list as $item){
                    $fieldValue = key_exists($item["field_id"],$setList)?$setList[$item["field_id"]]:null;
                    $this->jd_set[$item["field_id"]] = $fieldValue;
                }
                break;
			}
		}
		return true;
	}
    //获取select控件
    public static function getSelectForDataEx($className,$str,$rows,$htmlArr=array()){
        $selectClass = key_exists("class",$htmlArr)?$htmlArr["class"]:"";
        $readonly = key_exists("readonly",$htmlArr)&&$htmlArr["readonly"]?"readonly":"";
        $html = "<select id='{$className}' name='{$className}' {$readonly} class='form-control {$selectClass}'>";
        $html.="<option value='' data-class=''>".Yii::t("misc","All")."</option>";
        if($rows){
            foreach ($rows as $row){
                $dataOption = "";
                foreach ($row as $option=>$value){
                    if(!in_array($option,array("id","name"))){
                        $dataOption.=" data-{$option}='{$value}'";
                    }
                }
                $select = $row['id']==$str?"selected":"";
                $html.="<option value='{$row['id']}'{$dataOption} {$select}>{$row['name']}</option>";
            }
        }
        $html.="</select>";
        return $html;
    }

    //获取库存记录HTML
    public static function getHistoryList($id){
        $statusList = array(//info,danger,success,warning
            1=>array("name"=>Yii::t("procurement","Inventory Update"),'style'=>"danger","id"=>1),
            2=>array("name"=>Yii::t("procurement","Order new"),'style'=>"hidden","id"=>2),
            3=>array("name"=>Yii::t("procurement","Order Update"),'style'=>"hidden","id"=>3),
            4=>array("name"=>Yii::t("procurement","Order Delete"),'style'=>"warning hidden","id"=>4),
            5=>array("name"=>Yii::t("procurement","Import File"),'style'=>"danger","id"=>5),
            6=>array("name"=>Yii::t("app","Warehouse storage Info"),'style'=>"danger","id"=>6),
            7=>array("name"=>Yii::t("procurement","Add For JD"),'style'=>"text-yellow","id"=>7),
            8=>array("name"=>Yii::t("procurement","Update For JD"),'style'=>"text-yellow","id"=>8),
        );
        $html="<div style='margin: 10px 0px;width: 33%'>".self::getSelectForDataEx("changeStatus",1,$statusList)."</div>";
        $html.='<table id="tblFlow" class="table table-bordered table-striped table-hover">';
        $html.="<thead><tr>";
        $html.="<th>".Yii::t("procurement","Operator Time")."</th>";
        $html.="<th>".Yii::t("procurement","Goods Name")."</th>";
        $html.="<th>".Yii::t("procurement","Operator Status")."</th>";
        $html.="<th>".Yii::t("procurement","Operator User")."</th>";
        $html.="<th>".Yii::t("procurement","change num")."</th>";
        $html.="</tr></thead><tbody>";
        $historyList = Yii::app()->db->createCommand()
            ->select("a.apply_date,a.old_sum,a.now_sum,a.apply_name,a.status_type,a.order_code,b.name")
            ->from("opr_warehouse_history a")
            ->leftJoin("opr_warehouse b","a.warehouse_id=b.id")
            ->where("a.warehouse_id=:id",array(":id"=>$id))->order("a.apply_date desc")->queryAll();
        if($historyList){
            foreach ($historyList as $list){
                $status_type=key_exists($list["status_type"],$statusList)?$statusList[$list["status_type"]]["name"]:"";
                $style=key_exists($list["status_type"],$statusList)?$statusList[$list["status_type"]]["style"]:"";
                $changeNum = $list["now_sum"] - $list["old_sum"];
                $html.="<tr class='{$style}' data-type='{$list['status_type']}'>";
                $html.="<td>".$list["apply_date"]."</td>";
                $html.="<td>".$list["name"]."</td>";
                $html.="<td>".$status_type.TbHtml::hiddenField('test',$list["order_code"])."</td>";
                $html.="<td>".$list["apply_name"]."</td>";
                $html.="<td>".$changeNum."</td>";
                $html.="</tr>";

            }
        }
        return $html."</tbody></table>";
    }

    //獲取物品列表
    public function getGoodsList(){
        $city = Yii::app()->user->city();
        $rs = Yii::app()->db->createCommand()->select()->from("opr_warehouse")->where("city=:city",array(":city"=>$city))->queryAll();
        return $rs;
    }

    //根據物品id獲取物品信息
    public static function getGoodsToGoodsId($goods_id){
        $city = Yii::app()->user->city();
        $rows = Yii::app()->db->createCommand()->select("*")
            ->from("opr_warehouse")
            ->where('id = :id',array(':id'=>$goods_id))
            ->queryAll();
        if($rows){
            return $rows[0];
        }else{
            return array();
        }
    }

    //根據物品id獲取金蝶关联数据
    public static function getJDGoodsInfoToGoodsId($goods_id,$field_id="jd_good_id"){
        $row = Yii::app()->db->createCommand()->select("field_value")
            ->from("opr_send_set_jd")
            ->where("set_type='warehouse' and table_id=:table_id and field_id=:field_id",array(
                ':table_id'=>$goods_id,':field_id'=>$field_id
            ))->queryRow();
        if($row){
            return $row["field_value"];
        }else{
            return "";
        }
    }

    //根據訂單id查訂單所有物品
    public static function getGoodsListToId($order_id){
        $rs = Yii::app()->db->createCommand()->select("b.id as warehouse_id,a.lcd,b.matching,b.matters,b.name,b.inventory,b.goods_code,b.jd_classify_no as classify_id,b.unit,a.goods_num,a.confirm_num,a.id,a.goods_id,a.remark,a.note")
            ->from("opr_order_goods a,opr_warehouse b")->where('a.order_id=:order_id and a.goods_id = b.id',array(':order_id'=>$order_id))->queryAll();
        return $rs;
    }

    //
    public function getPriceHistory($id){
        $city = Yii::app()->user->city();
        $html = '';
        $rs = Yii::app()->db->createCommand()->select("a.year,a.month,a.price,b.name,b.goods_code")
            ->from("opr_warehouse_price a")
            ->leftJoin('opr_warehouse b',"a.warehouse_id=b.id")
            ->where('b.id =:id and a.city=:city',array(':id'=>$id,':city'=>$city))->order("a.year desc,a.month desc")->queryAll();
        if($rs){
            foreach ($rs as $row){
                $html.="<tr>";
                $html.="<td>".$row["goods_code"]."</td>";
                $html.="<td>".$row["name"]."</td>";
                $html.="<td>".$row["year"]."/".$row["month"]."</td>";
                $html.="<td>".$row["price"]."</td>";
                $html.="</tr>";
            }
        }
        if(empty($html)){
            $html.="<span>该物品没有单价历史</span>";
        }
        return array('status'=>1,'html'=>$html);
    }

    //
    public static function getPriceToIdAndDate($id,$date=''){
        if(empty($date)){
            $date = date("Y-m");
        }
        $year = intval(date("Y",strtotime($date)));
        $month = intval(date("m",strtotime($date)));
        $rs = Yii::app()->db->createCommand()->select("price")->from("opr_warehouse_price")
            ->where("warehouse_id =:id and (year < $year or (year = $year and month<=$month))",array(':id'=>$id))->order("year desc,month desc")->queryRow();
        if($rs){
            return $rs["price"];
        }
        return 0;
    }

	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
		    //$oldGoodList = $this->getGoodsToGoodsId($this->id);
			$this->saveHistory($connection);
			$this->saveGoods($connection);
            //保存金蝶要求的字段
			$this->saveJDSetInfo($connection);
			$transaction->commit();

			//$this->sendCurlJD($oldGoodList);//发送数据到金蝶
			$this->setScenario("edit");
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update. ('.$e->getMessage().')');
		}
	}

	public static function insertWarehouseHistory($id,$inventory,$status_type=1,$bool=false,$order_code=''){
        $oldRow = self::getGoodsToGoodsId($id);
        if($bool){
            $inventory=floatval($oldRow["inventory"])-$inventory;
        }
        if($oldRow["inventory"]!=$inventory){
            Yii::app()->db->createCommand()->insert('opr_warehouse_history',array(
                'apply_date'=>date("Y-m-d H:i:s"),
                'warehouse_id'=>$id,
                'old_sum'=>$oldRow["inventory"],
                'now_sum'=>$inventory,
                'apply_name'=>Yii::app()->user->user_display_name(),
                'status_type'=>$status_type,
                'order_code'=>$order_code,
                'lcu'=>Yii::app()->user->id,
            ));
        }
    }

    //保存金蝶要求的字段
    protected function saveJDSetInfo(&$connection) {
	    if($this->getScenario()!="delete"){
            foreach (self::$jd_set_list as $list){
                $field_value = key_exists($list["field_id"],$this->jd_set)?$this->jd_set[$list["field_id"]]:null;
                $rs = Yii::app()->db->createCommand()->select("id,field_id")->from("opr_send_set_jd")
                    ->where("set_type ='warehouse' and table_id=:table_id and field_id=:field_id",array(
                        ':field_id'=>$list["field_id"],':table_id'=>$this->id,
                    ))->queryRow();
                if($rs){
                    $connection->createCommand()->update('opr_send_set_jd',array(
                        "field_value"=>$field_value,
                    ),"id=:id",array(':id'=>$rs["id"]));
                }else{
                    $connection->createCommand()->insert('opr_send_set_jd',array(
                        "table_id"=>$this->id,
                        "set_type"=>'warehouse',
                        "field_id"=>$list["field_id"],
                        "field_value"=>$field_value,
                    ));
                }
            }
        }else{
            $connection->createCommand()->delete('opr_send_set_jd',"table_id={$this->id} and set_type='warehouse'");
        }
    }
    
    protected function saveHistory(&$connection) {
        switch ($this->scenario) {
            case 'delete':
                $connection->createCommand()->delete('opr_warehouse_history',"warehouse_id={$this->id}");
                break;
            case 'new':
                break;
            case 'edit':
                //self::insertWarehouseHistory($this->id,$this->inventory);
                break;
        }
    }

	protected function saveGoods(&$connection) {
		$sql = '';
        switch ($this->scenario) {
            case 'delete':
                $sql = "delete from opr_warehouse where id = :id";
                break;
            case 'new':
                $sql = "insert into opr_warehouse(
							name, unit, display, inventory, classify_id, lcu, goods_code,city,costing,decimal_num,min_num,matching,matters,old_good_no,jd_classify_no,jd_classify_name
						) values (
							:name, :unit, :display, '0', :classify_id, :lcu, :goods_code,:city,:costing,:decimal_num,'0',:matching,:matters,:old_good_no,:jd_classify_no,:jd_classify_name
						)";
                break;
            case 'edit':
                $sql = "update opr_warehouse set
							name = :name, 
							goods_code = :goods_code, 
							display = :display, 
							classify_id = :classify_id, 
							unit = :unit,
							costing = :costing,
							decimal_num = :decimal_num,
							matching = :matching,
							matters = :matters,
							luu = :luu,
							old_good_no = :old_good_no,
							jd_classify_no = :jd_classify_no,
							jd_classify_name = :jd_classify_name
						where id = :id
						";
                break;
        }
		if (empty($sql)) return false;

        //$city = Yii::app()->user->city();
        $city = Yii::app()->user->city();
        $uid = Yii::app()->user->id;

        $command=$connection->createCommand($sql);
        if (strpos($sql,':id')!==false)
            $command->bindParam(':id',$this->id,PDO::PARAM_INT);
        if (strpos($sql,':display')!==false)
            $command->bindParam(':display',$this->display,PDO::PARAM_INT);
        if (strpos($sql,':name')!==false)
            $command->bindParam(':name',$this->name,PDO::PARAM_STR);
        if (strpos($sql,':goods_code')!==false){
            $goodsCode = empty($this->goods_code)?1:$this->goods_code;
            $command->bindParam(':goods_code',$goodsCode,PDO::PARAM_STR);
        }
        if (strpos($sql,':unit')!==false)
            $command->bindParam(':unit',$this->unit,PDO::PARAM_STR);
        if (strpos($sql,':price')!==false)
            $command->bindParam(':price',$this->price,PDO::PARAM_STR);
        if (strpos($sql,':costing')!==false)
            $command->bindParam(':costing',$this->costing,PDO::PARAM_STR);
        if (strpos($sql,':decimal_num')!==false)
            $command->bindParam(':decimal_num',$this->decimal_num,PDO::PARAM_STR);
        if (strpos($sql,':min_num')!==false){
			$this->min_num = empty($this->min_num)?0:$this->min_num;
            $command->bindParam(':min_num',$this->min_num,PDO::PARAM_STR);
		}
        if (strpos($sql,':inventory')!==false){
			$this->inventory = empty($this->inventory)?0:$this->inventory;
            $command->bindParam(':inventory',$this->inventory,PDO::PARAM_STR);
		}
        if (strpos($sql,':z_index')!==false)
            $command->bindParam(':z_index',$this->z_index,PDO::PARAM_INT);
        if (strpos($sql,':matching')!==false)
            $command->bindParam(':matching',$this->matching,PDO::PARAM_STR);
        if (strpos($sql,':matters')!==false)
            $command->bindParam(':matters',$this->matters,PDO::PARAM_STR);
        if (strpos($sql,':classify_id')!==false)
            $command->bindParam(':classify_id',$this->classify_id,PDO::PARAM_INT);
        if (strpos($sql,':old_good_no')!==false){
			$this->old_good_no = empty($this->old_good_no)?$this->goods_code:$this->old_good_no;
            $command->bindParam(':old_good_no',$this->old_good_no,PDO::PARAM_INT);
		}
        if (strpos($sql,':jd_classify_no')!==false)
            $command->bindParam(':jd_classify_no',$this->jd_classify_no,PDO::PARAM_INT);
        if (strpos($sql,':jd_classify_name')!==false)
            $command->bindParam(':jd_classify_name',$this->jd_classify_name,PDO::PARAM_INT);

        if (strpos($sql,':city')!==false)
            $command->bindParam(':city',$city,PDO::PARAM_STR);
        if (strpos($sql,':luu')!==false)
            $command->bindParam(':luu',$uid,PDO::PARAM_STR);
        if (strpos($sql,':lcu')!==false)
            $command->bindParam(':lcu',$uid,PDO::PARAM_STR);

        $command->execute();

        if ($this->scenario=='new'){
            $this->id = Yii::app()->db->getLastInsertID();
        }
        $this->setGoodsCode($this->id);
		return true;
	}

	//生成不重复的物品编号
    private function setGoodsCode($id){
        if($this->getScenario()=="new"){
            $this->foreach_num++;
            $city = Yii::app()->user->city();
            $code = strval($id);
            $goodsCode = "W";
            for($i = 0;$i < 5-strlen($code);$i++){
                $goodsCode.="0";
            }
            $goodsCode .= $code;
            $row = Yii::app()->db->createCommand()->select("id")->from("opr_warehouse")
                ->where('goods_code=:goods_code and id!=:id and city = :city',
                    array(':goods_code'=>$goodsCode,':id'=>$this->id,':city'=>$city))->queryRow();
            if($row&&$this->foreach_num<10){
                $this->setGoodsCode($row['id']);
            }else{
                Yii::app()->db->createCommand()->update('opr_warehouse', array(
                    'goods_code'=>$goodsCode,
                    'old_good_no'=>$goodsCode,
                ), 'id=:id', array(':id'=>$this->id));
            }
        }
    }

    public static function downExcel(){
        $city = Yii::app()->user->city();
        $list["head"] = array("存货编码","存货名称","主计量单位","所属分类码","参考售价(成本)","是否允许小数","现有库存","产品配比","使用注意事项");
        if(Yii::app()->user->validFunction('YN02')){
            $list["head"][]="单价年月";
            $list["head"][]="单价";
        }
        $list["head"][]="是否显示";
        $rs = Yii::app()->db->createCommand()->select("a.*")->from("opr_warehouse a")
            //->leftJoin("opr_classify b","a.classify_id=b.id")
            ->where('a.city=:city or  a.local_bool=0',array(':city'=>$city))->queryAll();
        $list["body"] = array();
        $searchData=array(
            "org_number"=>CurlForDelivery::getJDCityCodeForCity($city),
            "warehouse_number"=>CurlForDelivery::getJDStoreListForCity($city),
        );
        $JDList = CurlForDelivery::getWarehouseGoodsStoreForJD(array("data"=>$searchData));
        if($rs){
            foreach ($rs as $row){
				$good_no = $row["goods_code"];
                $arr = array(
                    "goods_code"=>$row["goods_code"],
                    "name"=>$row["name"],
                    "unit"=>$row["unit"],
                    "classify_name"=>$row["jd_classify_name"],
                    "costing"=>$row["costing"],
                    "decimal_num"=>$row["decimal_num"],
                    "inventory"=>key_exists($good_no,$JDList)?$JDList[$good_no]["jd_store_sum"]:"",
                    "matching"=>$row["matching"],
                    "matters"=>$row["matters"]
                );
                if(Yii::app()->user->validFunction('YN02')){
                    $priceList = Yii::app()->db->createCommand()->select("price as cost_price,year,month")->from("opr_warehouse_price")
                        ->where("(year<date_format(:date_time,'%Y') or (year=date_format(:date_time,'%Y') and month<=date_format(:date_time,'%m'))) AND warehouse_id = :id",
                            array(':id'=>$row['id'],':date_time'=>date("Y-m-d")))->order("year DESC,month DESC")->queryRow();
                    if(!$priceList){
                        $priceList=array('cost_price'=>'无','year'=>'无','month'=>'无');
                    }
                    $arr["cost_year_month"] = $priceList["cost_price"]==="无"?"无":$priceList["year"]."/".$priceList["month"];
                    $arr["cost_price"] = $priceList["cost_price"];
                }
                $arr["display"] = empty($row["display"])?"不显示":"显示";
                $list["body"][]=$arr;
            }
        }
        return $list;
    }

    public static function downPriceExcel(){
        $list["head"] = array("物品编号","物品名称","年份","月份","单价");
        $rs = Yii::app()->db->createCommand()->select("a.*")->from("opr_warehouse a")
            ->where('a.local_bool=0')->queryAll();
        $list["body"] = array();
        if($rs){
            $year = date("Y");
            $month = date("n");
            foreach ($rs as $row){
                $priceList = Yii::app()->db->createCommand()->select("price as cost_price,year,month")->from("opr_warehouse_price")
                    ->where("(year<date_format(:date_time,'%Y') or (year=date_format(:date_time,'%Y') and month<=date_format(:date_time,'%m'))) AND warehouse_id = :id",
                        array(':id'=>$row['id'],':date_time'=>date("Y-m-d")))->order("year DESC,month DESC")->queryRow();
                if(!$priceList){
                    $priceList=array('cost_price'=>'0','year'=>'','month'=>'');
                }
                $arr = array(
                    "goods_code"=>$row["goods_code"],
                    "name"=>$row["name"],
                    "price_year"=>$year,
                    "price_month"=>$month,
                    "price"=>$priceList["cost_price"],
                );
                $list["body"][]=$arr;
            }
        }
        return $list;
    }

    protected function sendCurlJD($oldGoodList){
        if($this->getScenario()=="new"){
            CurlForWareHouse::addGood($this->id);
        }else{
            CurlForWareHouse::editGood($this->id,$oldGoodList);
        }
    }
}
