<?php
//2024年9月28日09:28:46

class DeliveryForm extends CFormModel
{
	public $id;
	public $order_user;
	//public $technician;
    public $status;
    public $remark;
    public $city;
	public $luu;
	public $lcu;
	public $lcd;
	public $statusList;
	public $order_code;
	public $total_price;
	public $goods_list;
	public $ject_remark;

	//單個物品退回專用
	public $confirm_num;
	public $num;
	public $black_id;
	public $black_store_id;//明细id（包含仓库）
	public $store_id;
	public $store_num;
	public $goods_id;

	//批量處理的訂單
    public $orderList;
    public $checkBoxDown;


    public $jd_set = array(
        "jd_order_type"=>0,
        "jd_company_code"=>''
    );
    public static $jd_set_list=array(
        array("field_id"=>"jd_order_type","field_type"=>"list","field_name"=>"jd order type","display"=>"none"),
        array("field_id"=>"jd_company_code","field_type"=>"list","field_name"=>"jd company code","display"=>"none"),
        //array("field_id"=>"jd_order_code","field_type"=>"text","field_name"=>"jd order code","display"=>"none"),
    );

    public function attributeLabels()
	{
		return array(
            'black_id'=>Yii::t('procurement','Goods Name'),
            'num'=>Yii::t('procurement','Black Number'),
            'order_code'=>Yii::t('procurement','Order Code'),
            'goods_list'=>Yii::t('procurement','Goods List'),
            'order_user'=>Yii::t('procurement','Order User'),
            //'technician'=>Yii::t('procurement','Technician'),
            'status'=>Yii::t('procurement','Order Status'),
            'remark'=>Yii::t('procurement','Remark'),
            'lcu'=>Yii::t('procurement','Apply for user'),
            'lcd'=>Yii::t('procurement','Apply for time'),
            'ject_remark'=>Yii::t('procurement','reject remark'),
            'total_price'=>"订单总价",
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('id,jd_set,lcd,num,black_id, total_price, order_code, order_user, order_class, activity_id, technician, status, remark, ject_remark, luu, lcu, lud, lcd, checkBoxDown','safe'),
            array('goods_list','required','on'=>array('audit','edit','reject')),
            array('id','validateID'),
            array('goods_list','validateGoods','on'=>array('audit','edit')),
            array('id','validatePriceOverTime','on'=>array('audit','edit')),
            array('ject_remark','required','on'=>'reject'),
            array('black_id,store_id','required','on'=>'black'),
            array('num','required','on'=>'black'),
            array('num','validateNum','on'=>'black'),
            //array('order_num','numerical','allowEmpty'=>true,'integerOnly'=>true),
            //array('order_num','in','range'=>range(0,600)),
		);
	}

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $city_allow = Yii::app()->user->city_allow();
        $row = Yii::app()->db->createCommand()->select("city,lcu,lcd,order_code")->from("opr_order")
            ->where("id=:id AND judge=0 AND city in ($city_allow)",array(":id"=>$id))->queryRow();
        if($row){
            $this->city = $row["city"];
            $this->lcu = $row["lcu"];
            $this->lcd = $row["lcd"];
            $this->order_code = $row["order_code"];
            $storeCount = Yii::app()->db->createCommand()->select("count(id)")->from("opr_store")
                ->where("city=:city and z_display=1 and store_type=1",array(":city"=>$row["city"]))->queryScalar();
            if(empty($storeCount)){
                $this->addError($attribute, "该城市没有默认仓库:".$row["city"]);
                return false;
            }
        }else{
            $this->addError($attribute, "订单不存在，请刷新重试");
            return false;
        }
    }

	public function validatePriceOverTime($attribute='', $params='')
    {
        $city_allow = Yii::app()->user->city_allow();
        $rows = Yii::app()->db->createCommand()->select("id")
            ->from("opr_order")->where("id=:id AND status in ('sent','read') AND judge=0 AND city in ($city_allow)",array(":id"=>$this->id))->queryRow();
        if(!$rows){
            $message = "订单不存在，请刷新重试";
            $this->addError($attribute,$message);
        }

        // Percy: 解決台灣不執行此檢查的問題
		if ((isset(Yii::app()->params['checkPriceOverTime']) && Yii::app()->params['checkPriceOverTime']==false)) {
			return true;
		}
        if(date("Y-m-d")<="2020-03-20"){ //一月份以前不需要驗證
            return true;
        }
        return true;//不需要价格验证
        $city = $this->city;
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        $month = $day>20?$month-1:$month-2;
        if($month <= 0){
            $month = 12+$month;
            $year--;
        }
        $bool = Yii::app()->db->createCommand()->select("a.id")->from("opr_warehouse_price a")
            ->where('a.city = :city and a.year=:year and a.month=:month', array(':city' =>$city,':year' =>$year,':month' =>$month))->queryRow();
        if ($bool) {
            return true;
        }else{
            if(!empty($attribute)){
                $message = $year."年".$month."月价格未导入，请联系财务同事";
                $this->addError($attribute,$message);
            }
            return false;
        }
    }

	public function validateNum($attribute, $params){
        if($this->num!==""){
            $idList = Yii::app()->db->createCommand()->select("*")->from("opr_order_goods")->where('id = :id',array(':id'=>$this->black_id))->queryRow();
            if($idList){
                $this->confirm_num = $idList["confirm_num"];
                $this->id = $idList["order_id"];
                $this->goods_id = $idList["goods_id"];
                $storeOrder = Yii::app()->db->createCommand()->select("id,store_num")->from("opr_order_goods_store")
                    ->where('order_goods_id=:id and store_id=:store_id',array(':id'=>$this->black_id,':store_id'=>$this->store_id))->queryRow();
                if($storeOrder){//由于老版没有仓库，所以额外查询
                    $this->black_store_id = $storeOrder["id"];
                    $idList["confirm_num"] = $storeOrder["store_num"];
                }
                if(is_numeric($this->num)){
                    if(floatval($idList["confirm_num"])<floatval($this->num)){
                        $message = "退回數量不能大於實際數量";
                        $this->addError($attribute,$message);
                    }elseif (floatval($idList["confirm_num"])>0&&floatval($this->num)<0){
                        $message = "退回數量不能小于0";
                        $this->addError($attribute,$message);
                    }
                }else{
                    $message = "退回數量只能為數字";
                    $this->addError($attribute,$message);
                }
                $this->store_num = $idList["confirm_num"];
            }else{
                $message = "訂單內的物品不存在";
                $this->addError($attribute,$message);
            }
        }
    }

	//驗證訂單內的物品
    public function validateGoods($attribute, $params){
        $city_allow = Yii::app()->user->city_allow();
        $orderRow = Yii::app()->db->createCommand()->select("*")
            ->from("opr_order")->where("id=:id AND judge=0 AND city in ($city_allow)",array(":id"=>$this->id))->queryRow();
	    if(!$orderRow){
            $message = "订单异常，请刷新重试";
            $this->addError($attribute,$message);
            return false;
        }
        $goods_list = $this->goods_list;
        if(count($this->goods_list)<1){
            $message = Yii::t('procurement','Fill in at least one goods');
            $this->addError($attribute,$message);
            return false;
        }
        $storeList = StoreForm::getStoreRowForCity($this->city);

        $searchData=array(
            "org_number"=>CurlForDelivery::getJDCityCodeForCity($this->city),
            "warehouse_number"=>CurlForDelivery::getJDStoreListForCity($this->city),
        );
        $jd_goods_list = CurlForDelivery::getWarehouseGoodsStoreForJD(array("data"=>$searchData));
        if(empty($jd_goods_list)){
            $message = "金蝶物料为空，请与管理员联系。({$this->city})";
            $this->addError($attribute,$message);
            return false;
        }
        foreach ($goods_list as $key =>$goods){
            $list = WarehouseForm::getGoodsToGoodsId($goods["goods_id"]);
            if(empty($goods["store_list"]["store_id"])){
                $this->addError($attribute,"仓库不存在，请刷新重试");
                return false;
            }else{
                $goods["confirm_num"]=0;
                foreach ($goods["store_list"]["store_id"] as $storeKey=>$storeID){
                    $storeID="".$storeID;
                    if(!key_exists($storeID,$storeList)){
                        $this->addError($attribute,"仓库不存在，请刷新重试");
                        return false;
                    }
                    $jd_goods_code = $list["goods_code"];
                    $jd_store_code = $storeList[$storeID]["jd_store_no"];
                    if(key_exists($jd_goods_code,$jd_goods_list)){
                        if(key_exists("{$jd_store_code}",$jd_goods_list[$jd_goods_code]["jd_warehouse_list"])){
                            if($jd_goods_list[$jd_goods_code]["jd_warehouse_list"][$jd_store_code]["qty"]<$goods["store_list"]["store_num"][$storeKey]){
                                $message = $list["name"]."：金蝶系统库存不足(".$jd_goods_list[$jd_goods_code]["jd_warehouse_list"][$jd_store_code]["qty"].")";
                                $this->addError($attribute,$message);
                                return false;
                            }
                        }else{
                            $message = $list["name"]."：金蝶仓库没有找到该物品(".$jd_store_code.")";
                            $this->addError($attribute,$message);
                            return false;
                        }
                    }else{//2024年10月9日11:23:38，金蝶系统说不需要该验证
                        //$message = $list["name"]."：金蝶系统没有找到该物品(".$jd_goods_code.")";
                        //$this->addError($attribute,$message);
                        //return false;
                    }
                    $goods["confirm_num"]+=$goods["store_list"]["store_num"][$storeKey];
                }
            }
            $this->goods_list[$key]["confirm_num"]=$goods["confirm_num"];
            if(empty($goods["goods_id"]) && empty($goods["confirm_num"])){
                unset($this->goods_list[$key]);
                continue;
            }else if ($goods["confirm_num"] === ""){
                $message = Yii::t('procurement','Actual Number cannot be empty');
                $this->addError($attribute,$message);
                return false;
            }else if(!is_numeric($goods["confirm_num"])){
                $message = Yii::t('procurement','Actual Number can only be numbered');
                $this->addError($attribute,$message);
                return false;
            }else if ($goods["confirm_num"] != 0){
                if (empty($list)){
                    $message = Yii::t('procurement','Not Font Goods').$goods["goods_id"]."a";
                    $this->addError($attribute,$message);
                    return false;
                }

                //物品价格过高，限制审核
                $price = WarehouseList::getNowWarehousePrice($goods["goods_id"],$orderRow['city'],$orderRow['lcd']);
                $price*=floatval($goods["confirm_num"]);
                if($price>999999){
                    $message = $list["name"]."价格过高，无法保存。计算后价格:{$price}";
                    $this->addError($attribute,$message);
                    return false;
                }
            }

        }
        if(count($this->goods_list)<1){
            $message = Yii::t('procurement','Fill in at least one goods');
            $this->addError($attribute,$message);
        }
    }

	public function retrieveData($index) {
		$city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
		$rows = Yii::app()->db->createCommand()->select("*")
            ->from("opr_order")->where("id=:id AND judge=0 AND city in ($city_allow)",array(":id"=>$index))->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
                $this->id = $row['id'];
                $this->order_code = $row['order_code'];
                $this->goods_list = WarehouseForm::getGoodsListToId($row['id']);
                $this->order_user = $row['order_user'];
                //$this->technician = $row['technician'];
                $this->status = $row['status'];
                $this->city = $row['city'];
                $this->total_price = floatval($row['total_price']);
                $this->remark = $row['remark'];
                $this->lcu = OrderGoods::getNameToUsername($row['lcu']);
                $this->ject_remark = $row['ject_remark'];
                $this->lcd = date("Y-m-d",strtotime($row['lcd']));
                $this->statusList = OrderForm::getStatusListToId($row['id']);

                $setRows = Yii::app()->db->createCommand()->select("field_id,field_value")
                    ->from("opr_send_set_jd")->where("table_id=:table_id and set_type='technician'",array(":table_id"=>$index))->queryAll();
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
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
            $oldOrderStatus = Yii::app()->db->createCommand()->select("*")->from("opr_order")
                ->where("id=:id",array(":id"=>$this->id))->queryRow();
			$this->saveGoods($connection);
            $curlData = $this->saveGoodsInfo($connection,$oldOrderStatus);
            $bool=true;
            if(!empty($curlData["goods_item"])){
                $jdCurlModel = new CurlForDelivery();
                $jdCurl = $jdCurlModel->sendJDCurlForOne($curlData);
                if($jdCurl["code"]!=200){//金蝶提示失败
                    $bool = false;
                    $transaction->rollback();
                    $jdCurlModel->saveTableForArr();
                    Dialog::message("金蝶系统异常", $jdCurl["message"]);
                }else{
                    $transaction->commit();
                    $jdCurlModel->saveTableForArr();
                    $this->resetZIndex();
                    //發送流程
                    $this->sendFlow($this->getScenario());
                    //發送郵件
                    //OrderGoods::sendEmailTwo(array($oldOrderStatus),$this->status,$this->order_code);
                }
            }else{
                $transaction->commit();
                //發送流程
                $this->sendFlow($this->getScenario());
            }
            return $bool;
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update. ('.$e->getMessage().')');
		}
	}

    //發送流程
    protected function sendFlow($scenario,$blackMessage=''){
        if($this->jd_set["jd_order_type"]==1){
            $titleStr = "销售出库";
            $hrefName = "salesOut";
        }else{
            $titleStr = "外勤领料";
            $hrefName = "technician";
        }
        $menuCode = "YD02";
        $flowModel = new CNoticeFlowModel($menuCode,$this->id);
        $flowModel->setOwerNumForUsername($this->lcu);
        $html = "<p>下单城市：".CGeneral::getCityName($this->city)."</p>";
        $html .= "<p>下单用户：".OrderGoods::getNameToUsername($this->lcu)."</p>";
        $html .= "<p>申请时间：".$this->lcd."</p>";
        $html .= "<p>订单编号：".$this->order_code."</p>";
        //$flowModel->setDescription($description);
        switch ($scenario){
            case "audit"://审核
                $html .= "<p>审核时间：".date('Y-m-d H:i:s')."</p>";
                $flowModel->setMessage($html);
                $flowModel->setMB_PC_Url("{$hrefName}/edit",array("index"=>$this->id));
                $subject="{$titleStr}已发货，等待收货（订单编号：".$this->order_code."）";
                $flowModel->setSubject($subject);
                $flowModel->addEmailToLcu($this->lcu);
                $flowModel->saveFlowAll("",$menuCode);
                break;
            case "black"://退回单个物料
                $html .= "<p>退回物料：".$blackMessage."</p>";
                $html .= "<p>退回时间：".date('Y-m-d H:i:s')."</p>";
                $flowModel->setMessage($html);
                $flowModel->setMB_PC_Url("{$hrefName}/edit",array("index"=>$this->id));
                $subject="单个物料被退回，请查看详情（订单编号：".$this->order_code."）";
                $flowModel->setSubject($subject);
                $flowModel->sendFinishFlow($menuCode);
                $flowModel->addEmailToLcu($this->lcu);
                $flowModel->note_type=2;
                $flowModel->saveNoticeAll("",$menuCode);
                break;
            case "backward"://退回
                $html .= "<p>退回时间：".date('Y-m-d H:i:s')."</p>";
                $flowModel->setMessage($html);
                $flowModel->setMB_PC_Url("{$hrefName}/edit",array("index"=>$this->id));
                $subject="{$titleStr}已退回，等待重新发货（订单编号：".$this->order_code."）";
                $flowModel->setSubject($subject);
                $flowModel->sendFinishFlow($menuCode);
                $flowModel->addEmailToLcu($this->lcu);
                $flowModel->note_type=2;
                $flowModel->saveNoticeAll("",$menuCode);
                $flowModel->setMB_PC_Url("delivery/edit",array("index"=>$this->id));
                $flowModel->resetToAddr();
                $flowModel->note_type=1;
                $flowModel->addEmailToPrefixAndCity("YD02",$this->city);
                $flowModel->saveFlowAll("",$menuCode);
                break;
            case "reject"://拒绝
                $html.="<p>拒绝原因：{$this->ject_remark}</p>";
                $flowModel->setMessage($html);
                $flowModel->setMB_PC_Url("{$hrefName}/edit",array("index"=>$this->id));
                $subject="{$titleStr}已拒绝，请查看详情（订单编号：".$this->order_code."）";
                $flowModel->setSubject($subject);
                $flowModel->addEmailToLcu($this->lcu);
                $flowModel->sendRefuseFlow($menuCode);
                $flowModel->note_type=2;
                $flowModel->saveNoticeAll("",$menuCode);
                break;
        }
    }

    protected function saveGoodsInfo(&$connection,$oldOrderStatus){
        $time = date_format(date_create(),"Y-m-d H:i:s");
        $curlData = self::getCurlDateForOrder($oldOrderStatus,$time);
        $uid = Yii::app()->user->id;
        $connection->createCommand()->insert('opr_order_status', array(
            'order_id'=>$this->id,
            'status'=>$this->status,
            'r_remark'=>$this->remark,
            'lcu'=>Yii::app()->user->user_display_name(),
            'time'=>$time,
        ));

        $totalPrice = 0;
        //物品的添加、修改
        foreach ($this->goods_list as $goods){
            if(!empty($goods["id"])){
                $price = WarehouseList::getNowWarehousePrice($goods['goods_id'],$oldOrderStatus['city'],$oldOrderStatus['lcd']);
                $totalPrice+=$goods["confirm_num"]*$price;
                //修改
                $connection->createCommand()->update('opr_order_goods', array(
                    'confirm_num'=>$goods["confirm_num"],
                    'total_price'=>$goods["confirm_num"]*$price,
                    'remark'=>$goods["remark"],
                    'luu'=>$uid,
                    'lud'=>$time,
                ), 'id=:id', array(':id'=>$goods["id"]));
                //选择发货仓库(开始)
                $this->saveOrderGoodStore($connection,$goods,$uid,$time);
                //选择发货仓库(结束)
                if ($this->scenario == "audit"){
                    $warehouseRow = $connection->createCommand()->select("*")->from("opr_warehouse")
                        ->where("id =:id",array(":id"=>$goods["goods_id"]))->queryRow();
                    $storeList =$connection->createCommand()->select("a.id,a.store_num,b.jd_store_no")->from("opr_order_goods_store a")
                        ->leftJoin("opr_store b","a.store_id=b.id")
                        ->where("order_goods_id =:order_goods_id",array(":order_goods_id"=>$goods["id"]))->queryAll();
                    if($storeList){
                        $warehouseRow["store_list"]=$storeList;
                    }
                    $warehouseRow["note"] = $goods["note"];
                    $warehouseRow["remark"] = $goods["remark"];
                    $tempArr=self::getCurlDateForWarehouse($warehouseRow,$goods["confirm_num"]);
                    $curlData["goods_item"] = array_merge($curlData["goods_item"],$tempArr);
                    //记录库存数量
                    $connection->createCommand()->insert('opr_warehouse_history',array(
                        'apply_date'=>$time,
                        'warehouse_id'=>$goods["goods_id"],
                        'old_sum'=>0,
                        'now_sum'=>0-$goods["confirm_num"],
                        'apply_name'=>Yii::app()->user->user_display_name(),
                        'status_type'=>2,
                        'order_code'=>$oldOrderStatus["order_code"],
                        'lcu'=>$uid,
                    ));
                    /*由于金蝶要求LBS系统不需要储存库存，所以无法记录库存的变更
                    //減少庫存 inventory
                    $connection->createCommand()->update('opr_warehouse',array(
                        'inventory'=>$warehouseRow["inventory"]-$goods["confirm_num"]
                    ),"id=:id",array(":id"=>$goods["goods_id"]));
                    */
                }
            }
        }

        //修改訂單總價
        $connection->createCommand()->update('opr_order', array(
            'total_price'=>$totalPrice,
        ), 'id=:id', array(':id'=>$this->id));

        return $curlData;
    }

    protected function saveOrderGoodStore(&$connection,$goods,$uid,$time){
        $storeRows = $connection->createCommand()->select("*")->from("opr_order_goods_store")
            ->where("order_goods_id=".$goods["id"])->queryAll();
        $storeIDList=array();
        if($storeRows){
            foreach ($storeRows as $storeRow){
                $storeIDList[$storeRow["id"]] = $storeRow["id"];
            }
        }
        foreach ($goods["store_list"]["store_id"] as $storeKey=>$storeID){
            $orderGoodStoreId = $goods["store_list"]["id"][$storeKey];
            if(!empty($orderGoodStoreId)){//修改发货仓库
                if(isset($storeIDList[$orderGoodStoreId])){
                    unset($storeIDList[$orderGoodStoreId]);
                    $connection->createCommand()->update('opr_order_goods_store', array(
                        'store_id'=>$goods["store_list"]["store_id"][$storeKey],
                        'store_num'=>$goods["store_list"]["store_num"][$storeKey],
                        'luu'=>$uid,
                        'lud'=>$time,
                    ), 'id=:id', array(':id'=>$orderGoodStoreId));
                }
            }else{//新增发货仓库
                $connection->createCommand()->insert('opr_order_goods_store', array(
                    'order_goods_id'=>$goods["id"],
                    'store_id'=>$goods["store_list"]["store_id"][$storeKey],
                    'store_num'=>$goods["store_list"]["store_num"][$storeKey],
                    'lcu'=>$uid,
                    'lcd'=>$time,
                ));
            }
        }
        if(!empty($storeIDList)){
            foreach ($storeIDList as $id){
                $connection->createCommand()->delete('opr_order_goods_store',"id=".$id);
            }
        }
    }

	protected function saveGoods(&$connection) {
		$sql = '';
        switch ($this->scenario) {
            case 'edit':
                $sql = "update opr_order set
							remark = :remark,
							luu = :luu,
							status = :status
						where id = :id AND judge=0
						";
                break;
            case 'audit':
                $sql = "update opr_order set
							remark = :remark,
							ject_remark = '',
							luu = :luu,
							audit_time = :audit_time,
							status = :status
						where id = :id AND judge=0
						";
                break;
            case 'reject':
                $sql = "update opr_order set
							ject_remark = :ject_remark,
							luu = :luu,
							status = :status
						where id = :id AND judge=0
						";
                $this->goods_list = array();
                $this->remark = $this->ject_remark;
                break;
        }
		if (empty($sql)) return false;

        $city = Yii::app()->user->city();
        $uid = Yii::app()->user->id;
        $order_username = Yii::app()->user->name;
        $command=$connection->createCommand($sql);
        if (strpos($sql,':id')!==false)
            $command->bindParam(':id',$this->id,PDO::PARAM_INT);
        if (strpos($sql,':status')!==false){
            if ($this->scenario == "audit"){
                $this->status = "approve";
            }elseif ($this->scenario == "edit"){
                $this->status = "read";
            }elseif ($this->scenario == "reject"){
                $this->status = "reject";
            }
            $command->bindParam(':status',$this->status,PDO::PARAM_STR);
        }

        if (strpos($sql,':remark')!==false)
            $command->bindParam(':remark',$this->remark,PDO::PARAM_STR);
        if (strpos($sql,':ject_remark')!==false)
            $command->bindParam(':ject_remark',$this->ject_remark,PDO::PARAM_STR);
        if (strpos($sql,':audit_time')!==false)
            $command->bindParam(':audit_time',date('Y-m-d H:i:s'),PDO::PARAM_STR);
        if (strpos($sql,':lud')!==false)
            $command->bindParam(':lud',date('Y-m-d H:i:s'),PDO::PARAM_STR);
        if (strpos($sql,':luu')!==false)
            $command->bindParam(':luu',$uid,PDO::PARAM_STR);
        $command->execute();
		return true;
	}

    //減少庫存
	public function reduceInventory($goodsId,$num){
        if(empty($goodsId)||!is_numeric($goodsId) || floor($goodsId)!=$goodsId){
            return false;
        }
        if(empty($num)||!is_numeric($num)){
            return false;
        }
        Yii::app()->db->createCommand("update opr_warehouse set inventory=inventory-$num where id=$goodsId")->execute();
    }

    //退回
    function backward(){
        $row = Yii::app()->db->createCommand()->select("*")->from("opr_order")->where('status = "approve" and id = :id',array(':id'=>$this->id))->queryRow();
        if($row){
            $this->city = $row["city"];
            $this->lcu = $row["lcu"];
            $this->lcd = $row["lcd"];
            $this->order_code = $row["order_code"];
            $uid = Yii::app()->user->id;
            $time = date_format(date_create(),"Y-m-d H:i:s");
            $curlData = DeliveryForm::getCurlDateForOrder($row,$time);
            $storeDefaultList = StoreForm::getStoreDefaultForCity($row["city"]);
            if($storeDefaultList===false){
                return false;
            }
            $this->status = "sent";
            $connection = Yii::app()->db;
            $transaction=$connection->beginTransaction();
            try {
                //修改訂單狀態
                $connection->createCommand()->update('opr_order', array(
                    'status'=>$this->status,
                    'remark'=>$this->remark,
                    'luu'=>$uid,
                    'lud'=>$time,
                ), 'id=:id', array(':id'=>$this->id));
                //補回庫存
                $goods_list = Yii::app()->db->createCommand()->select("id,goods_id,confirm_num,note,remark")->from("opr_order_goods")
                    ->where('order_id = :order_id',array(':order_id'=>$this->id))->queryAll();
                if($goods_list){
                    foreach ($goods_list as $goods){
                        $num = $goods["confirm_num"];
                        $goodsId = $goods["goods_id"];
                        $warehouseRow = $connection->createCommand()->select("*")->from("opr_warehouse")
                            ->where("id =:id",array(":id"=>$goodsId))->queryRow();
                        $storeList =$connection->createCommand()->select("a.id,a.store_num as back_num,a.store_num,b.jd_store_no")->from("opr_order_goods_store a")
                            ->leftJoin("opr_store b","a.store_id=b.id")
                            ->where("order_goods_id =:order_goods_id",array(":order_goods_id"=>$goods["id"]))->queryAll();
                        if($storeList){
                            $warehouseRow["store_list"]=$storeList;
                        }else{
                            $warehouseRow["store_list"]=array();
                            $warehouseRow["store_list"][]=array("id"=>0,"back_num"=>$num,"store_num"=>$num,"jd_store_no"=>$storeDefaultList["jd_store_no"]);
                        }
                        $warehouseRow["note"] = $goods["note"];
                        $warehouseRow["remark"] = $goods["remark"];
                        $tempArr=self::getCurlDateForWarehouse($warehouseRow,$goods["confirm_num"]);
                        $curlData["goods_item"] = array_merge($curlData["goods_item"],$tempArr);
                        //记录库存
                        $connection->createCommand()->insert('opr_warehouse_history',array(
                            'apply_date'=>$time,
                            'warehouse_id'=>$goodsId,
                            'old_sum'=>0,
                            'now_sum'=>0+$num,
                            'apply_name'=>Yii::app()->user->user_display_name(),
                            'status_type'=>4,
                            'order_code'=>$row["order_code"],
                            'lcu'=>$uid,
                        ));
                        /*由于金蝶要求LBS系统不需要储存库存，所以无法记录库存的变更
                        //补回庫存
                        $connection->createCommand()->update('opr_warehouse',array(
                            'inventory'=>$warehouseRow["inventory"]+$num
                        ),"id=:id",array(":id"=>$goodsId));
                        */
                    }
                }

                //添加狀態記錄
                $connection->createCommand()->insert('opr_order_status', array(
                    'order_id'=>$this->id,
                    'status'=>"backward",
                    'r_remark'=>$this->remark,
                    'lcu'=>Yii::app()->user->user_display_name(),
                    'time'=>$time,
                ));

                $jdCurlModel = new CurlForDelivery();
                $jdCurl = $jdCurlModel->backJDCurlForOrder($curlData);
                if($jdCurl["code"]!=200){//金蝶提示失败
                    $transaction->rollback();
                    $jdCurlModel->saveTableForArr();
                    Dialog::message("金蝶系统异常", $jdCurl["message"]);
                }else{
                    $transaction->commit();
                    $jdCurlModel->saveTableForArr();
                    $this->resetZIndex();

                    $this->sendFlow("backward");//发送流程
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('procurement','Backward Done'));
                }
                return true;
            }catch(Exception $e) {
                $transaction->rollback();
                throw new CHttpException(404,'Cannot update. ('.$e->getMessage().')');
            }
        }
        return false;
    }


    public function getTableHeard(){
        $arr = array("物品編號","物品名称","物品單位","要求備註","總部備註","要求數量","實際數量");
        return $arr;
    }
    //整理出下載的物品列表
    public function resetGoodsList(){
        $goodsList = $this->goods_list;
        $arr = array();
        foreach ($goodsList as $goods){
            array_push($arr,array($goods["goods_code"],$goods["name"],$goods["unit"],$goods["note"],$goods["remark"],$goods["goods_num"],$goods["confirm_num"]));
        }
        return $arr;
    }
    //
    private function resetZIndex(){
        //由于金蝶要求LBS系统不需要储存库存，所以无法记录库存的变更
        //Yii::app()->db->createCommand("update opr_warehouse set z_index=1 where (inventory+0)>(min_num+0)")->execute();
        //Yii::app()->db->createCommand("update opr_warehouse set z_index=2 where (inventory+0)<=(min_num+0)")->execute();
    }

    //退回單個物品
    public function blackGoods($str=""){
        $bool = true;
        $num = $this->num;
        $goodsId = $this->goods_id;
        $blackId = $this->black_id;
        $blackStoreId = empty($this->black_store_id)?$this->black_id:$this->black_store_id;
        $jd_store_no = StoreForm::getStoreListForStoreID($this->store_id);
        $time = date_format(date_create(),"Y-m-d H:i:s");
        $uid = Yii::app()->user->id;
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $order = $connection->createCommand()->select("*")->from("opr_order")
                ->where("id =:id",array(":id"=>$this->id))->queryRow();
            $warehouseRow = $connection->createCommand()->select("*")->from("opr_warehouse")
                ->where("id =:id",array(":id"=>$goodsId))->queryRow();
            $warehouseRow["store_list"]=array();
            $warehouseRow["store_list"][]=array("id"=>$blackStoreId,"back_num"=>$num,"store_num"=>$this->store_num,"jd_store_no"=>$jd_store_no);
            $curlData=self::getCurlDateForOrder($order,$time);

            $warehouseRow["note"] = "";
            $warehouseRow["remark"] = "";
            $tempArr=self::getCurlDateForWarehouse($warehouseRow,$this->confirm_num,array("back_num"=>$num));
            $curlData["goods_item"] = array_merge($curlData["goods_item"],$tempArr);
            //记录库存
            $connection->createCommand()->insert('opr_warehouse_history',array(
                'apply_date'=>$time,
                'warehouse_id'=>$goodsId,
                'old_sum'=>0,
                'now_sum'=>0+$num,
                'apply_name'=>Yii::app()->user->user_display_name(),
                'status_type'=>3,
                'order_code'=>$order["order_code"],
                'lcu'=>$uid,
            ));
            /*由于金蝶要求LBS系统不需要储存库存，所以无法记录库存的变更
            //补回庫存
            $connection->createCommand()->update('opr_warehouse',array(
                'inventory'=>$warehouseRow["inventory"]+$num
            ),"id=:id",array(":id"=>$goodsId));
            */
            //修改发货数量
            $priceNum = $this->confirm_num-$num;
            $price = WarehouseList::getNowWarehousePrice($goodsId,$order["city"],$order["lcd"]);
            $connection->createCommand()->update('opr_order_goods',array(
                'confirm_num'=>$priceNum,
                'total_price'=>$priceNum*$price,
            ),"id=:id",array(":id"=>$blackId));
            $connection->createCommand()->update('opr_order_goods_store',array(
                'store_num'=>$this->store_num-$num
            ),"order_goods_id=:id and store_id=:store_id",array(":id"=>$blackId,":store_id"=>$this->store_id));

            $blackMessage = $str."退回數量:$num";
            //記錄
            $connection->createCommand()->insert('opr_order_status', array(
                'order_id'=>$this->id,
                'status'=>"backward",
                'r_remark'=>$blackMessage,
                'lcu'=>Yii::app()->user->user_display_name(),
                'time'=>$time,
            ));
            //記錄
            $connection->createCommand()->insert('opr_warehouse_back', array(
                'order_id'=>$this->id,
                'warehouse_id'=>$goodsId,
                'store_id'=>$this->store_id,
                'back_num'=>$num,
                'old_num'=>$this->confirm_num,
                'lcu'=>Yii::app()->user->user_display_name(),
            ));

            $jdCurlModel = new CurlForDelivery();
            $jdCurl = $jdCurlModel->backJDCurlForGoods($curlData);
            if($jdCurl["code"]!=200){//金蝶提示失败
                $bool=false;
                $transaction->rollback();
                $jdCurlModel->saveTableForArr();
                Dialog::message("金蝶系统异常", $jdCurl["message"]);
            }else{
                $transaction->commit();
                $jdCurlModel->saveTableForArr();
                $this->resetZIndex();

                $this->sendFlow("black",$blackMessage);//发送流程
            }
        }catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,'Cannot update. ('.$e->getMessage().')');
        }
        return $bool;
    }

    public function downTypeList(){
        $list = array(
            0=>Yii::t('procurement','All'),
            1=>Yii::t('procurement','Have read,Drop shipping'),
            2=>Yii::t('procurement','pending approval')
        );
        return $list;
    }

    //檢查是否有未發貨的訂單
    public function validateAll($type=0){
        $list = array("'read','sent'","'read'","'sent'");
        if(!key_exists($type,$list)){
            $type = 0;
        }
        $city_allow = Yii::app()->user->city_allow();
        $sql ="a.city in ($city_allow) AND a.judge=0 AND a.status in (".$list[$type].")";
        if($this->jd_set["jd_order_type"]==1){
            $sql.=" and b.field_value='1'";
        }else{
            $sql.=" and b.field_value='0'";
        }
        if($this->getScenario()=="approved"){
            if(empty($this->checkBoxDown)||!is_array($this->checkBoxDown)){
                return false;
            }
            $idList = implode(",",$this->checkBoxDown);
            $sql.=" and a.id in($idList)";
        }
        $rows = Yii::app()->db->createCommand()->select("a.*")->from("opr_order a")
            ->leftJoin("opr_send_set_jd b","b.set_type='technician' and b.field_id='jd_order_type' and b.table_id=a.id")
            ->where($sql)->queryAll();
        if($rows){
            $this->orderList = $rows;
            return true;
        }else{
            return false;
        }
    }

    //批量下載訂單
    public function allDownload(){
        $orderList = $this->orderList;
        if(!empty($orderList)) {
            foreach ($orderList as &$order) {
                $order["lcu_name"]=OrderGoods::getNameToUsername($order['lcu']);
                $order["goodsList"] = WarehouseForm::getGoodsListToId($order['id']);
                $order["status"] = OrderList::printPurchaseStatus($order["status"]);
                $order["lcd"] = date("Y-m-d",strtotime($order["lcd"]));
            }
            return $orderList;
        }else{
            return array();
        }
    }

    protected static function getCurlDateForOrder($order,$time,$expArr=array()){
        $order["jd_order_type"] = TechnicianList::getJDOrderTypeForId($order["id"]);
        $order["jd_company_code"] = TechnicianList::getJDOrderTypeForId($order["id"],"jd_company_code");
        //$order["jd_company_code"].="-".$order["city"];
        $list = array(
            "order_id"=>$order["id"],
            "order_code"=>$order["order_code"],
            "order_user"=>$order["order_user"],
            "city"=>$order["city"],
            "remark"=>$order["remark"],
            "apply_date"=>$order["lcd"],
            "jd_order_type"=>$order["jd_order_type"],
            "jd_company_code"=>$order["jd_company_code"],
            "audit_date"=>$time,
            "luu_name"=>Yii::app()->user->user_display_name(),
            "goods_item"=>array(),
        );
        if(!empty($expArr)){
            foreach ($expArr as $key=>$item){
                $list[$key] = $item;
            }
        }
        return $list;
    }

    protected static function getCurlDateForWarehouse($warehouseRow,$num,$expArr=array()){
        $warehouseRow["jd_good_id"] = WarehouseForm::getJDGoodsInfoToGoodsId($warehouseRow["id"]);
        $warehouseRow["jd_unit_code"] = WarehouseForm::getJDGoodsInfoToGoodsId($warehouseRow["id"],"jd_unit_code");
        $arr = array();
        $list = array(
            //"jd_warehouse_no"=>$warehouseRow["jd_warehouse_no"],
            "lbs_order_store_id"=>0,
            "jd_good_id"=>$warehouseRow["jd_good_id"],
            "jd_unit_code"=>$warehouseRow["jd_unit_code"],
            "city"=>$warehouseRow["city"],
            "good_id"=>$warehouseRow["id"],
            "goods_code"=>$warehouseRow["goods_code"],
            "goods_name"=>$warehouseRow["name"],
            "inventory"=>$warehouseRow["inventory"],
            "note"=>$warehouseRow["note"],
            "remark"=>$warehouseRow["remark"],
            "confirm_num"=>"".$num
        );
        if(!empty($warehouseRow["store_list"])){
            foreach ($warehouseRow["store_list"] as $storeRow){
                $temp = $list;
                $temp["lbs_order_store_id"]=$storeRow["id"];
                $temp["confirm_num"]="".$storeRow["store_num"];
                $temp["jd_store_no"]="".$storeRow["jd_store_no"];
                if(key_exists("back_num",$storeRow)){
                    $temp["back_num"] = $storeRow["back_num"];
                }
                if(!empty($expArr)){
                    foreach ($expArr as $key=>$item){
                        $temp[$key] = $item;
                    }
                }
                $arr[]=$temp;
            }
        }
        return $arr;
    }

    //批量批准訂單
    public function allApproved(){
        $orderList = $this->orderList;
        $uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();
        $time = date_format(date_create(),"Y-m-d H:i:s");
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $jdCurlDate=array();
            $updateBool = true;
            if(!empty($orderList)){
                foreach ($orderList as $order){
                    $storeDefaultList = StoreForm::getStoreDefaultForCity($order["city"]);
                    if($storeDefaultList===false){
                        $updateBool = false;
                        $message = "城市:".$order["city"]."没有默认仓库";
                        Dialog::message(Yii::t('dialog','Validation Message'), $message);
                        break;
                    }
                    $tempCurl=self::getCurlDateForOrder($order,$time);
                    $this->lcd = $order["lcd"];
                    //記錄
                    $connection->createCommand()->insert('opr_order_status', array(
                        'order_id'=>$order["id"],
                        'status'=>"approve",
                        'r_remark'=>"",
                        'lcu'=>Yii::app()->user->user_display_name(),
                        'time'=>$time,
                    ));

                    $totalPrice=0;//訂單總價
                    //批量減少庫存
                    $rows = $connection->createCommand()->select("id,goods_id,goods_num,confirm_num,note,remark")->from("opr_order_goods")
                        ->where("order_id =:order_id",array(":order_id"=>$order["id"]))->queryAll();
                    if($rows){
                        foreach ($rows as $row){
                            $num = ($row["confirm_num"]===""||$row["confirm_num"]===null)?floatval($row["goods_num"]):floatval($row["confirm_num"]);
                            $goodsId = intval($row["goods_id"]);
                            $price = WarehouseList::getNowWarehousePrice($goodsId,$order['city'],$order['lcd']);
                            $totalPrice+=$num*$price;

                            $warehouseRow = $connection->createCommand()->select("*")->from("opr_warehouse")
                                ->where("id =:id",array(":id"=>$goodsId))->queryRow();
                            if($warehouseRow){
                                $warehouseRow["note"] = $row["note"];
                                $warehouseRow["remark"] = $row["remark"];
                                $storeList =$connection->createCommand()->select("a.id,a.store_num,b.jd_store_no")->from("opr_order_goods_store a")
                                    ->leftJoin("opr_store b","a.store_id=b.id")
                                    ->where("order_goods_id =:order_goods_id",array(":order_goods_id"=>$row["id"]))->queryAll();
                                if($storeList){
                                    $warehouseRow["store_list"]=$storeList;
                                }else{
                                    $connection->createCommand()->insert('opr_order_goods_store', array(
                                        'order_goods_id'=>$row["id"],
                                        'store_id'=>$storeDefaultList["jd_store_no"],
                                        'store_num'=>$num,
                                        'lcu'=>$uid,
                                        'lcd'=>$time,
                                    ));
                                    $id = Yii::app()->db->getLastInsertID();
                                    $warehouseRow["store_list"]=array();
                                    $warehouseRow["store_list"][]=array("id"=>$id,"store_num"=>$num,"jd_store_no"=>$storeDefaultList["jd_store_no"]);
                                }

                                $tempArr=self::getCurlDateForWarehouse($warehouseRow,$num);
                                $tempCurl["goods_item"] = array_merge($tempCurl["goods_item"],$tempArr);
                                //记录库存
                                $connection->createCommand()->insert('opr_warehouse_history',array(
                                    'apply_date'=>$time,
                                    'warehouse_id'=>$goodsId,
                                    'old_sum'=>0,
                                    'now_sum'=>0-$num,
                                    'apply_name'=>Yii::app()->user->user_display_name(),
                                    'status_type'=>2,
                                    'order_code'=>$order["order_code"],
                                    'lcu'=>$uid,
                                ));
                                /*由于金蝶要求LBS系统不需要储存库存，所以无法记录库存的变更
                                //減少庫存
                                $connection->createCommand()->update('opr_warehouse',array(
                                    'inventory'=>$warehouseRow["inventory"]-$num
                                ),"id=:id",array(":id"=>$goodsId));
                                */
                                //修改物品的實際數量
                                $connection->createCommand()->update('opr_order_goods', array(
                                    'confirm_num'=>$num,
                                    'total_price'=>$num*$price,
                                ), "id=:id",array(":id"=>$row["id"]));
                            }else{
                                $updateBool = false;
                                $message = "订单:".$order["order_code"]."的库存不足。({$warehouseRow["name"]}库存:{$warehouseRow["inventory"]})";
                                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                                break;
                            }
                        }
                    }

                    //修改物品的价格及狀態
                    $connection->createCommand()->update('opr_order', array(
                        'status'=>"approve",
                        'total_price'=>$totalPrice,
                        'audit_time'=>$time,
                        'luu'=>$uid,
                    ), "city='$city' AND judge=0 AND status in ('read','sent') and id=:id",array(":id"=>$order["id"]));

                    if($updateBool===false){
                        break;
                    }else{
                        $jdCurlDate[] = $tempCurl;
                    }
                }
            }


            if($updateBool===true&&!empty($jdCurlDate)){
                $jdCurlModel = new CurlForDelivery();
                $jdCurl = $jdCurlModel->sendJDCurlForFull($jdCurlDate);
                if($jdCurl["code"]!=200){//金蝶提示失败
                    $updateBool=false;
                    $transaction->rollback();
                    $jdCurlModel->saveTableForArr();
                    Dialog::message("金蝶系统异常", $jdCurl["message"]);
                }else{
                    $transaction->commit();
                    $jdCurlModel->saveTableForArr();
                    $this->resetZIndex();
                }
            }else{
                $transaction->rollback();
            }
            return $updateBool;
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,'Cannot update. ('.$e->getMessage().')');
        }
    }
}
