<?php

class RedeemgiftApply extends CListPageModel
{
//    public function tableName()
//    {
//        return 'sal_redeem_gift_apply';
//    }

    public $searchTimeStart;//開始日期
    public $searchTimeEnd;//結束日期
    public $searchField;
    public $searchValue;
    public $employee_id;
    public $employee_name;
    public $id = 0;
    public $gift_id;
    public $gift_name;
    public $bonus_point;
    public $apply_num;
    public $remark;
    public $status = 0;
    public $city;
    public $city_name;
    public $apply_date;
    public $no_of_attm = array(
        'icut'=>0
    );
    /**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(	
			'id'=>Yii::t('redeem','ID'),
            'employee_id'=>Yii::t('redeem','Employee Id'),
            'employee_name'=>Yii::t('redeem','Employee Name'),
            'gift_name'=>Yii::t('redeem','Cut Name'),
            'bonus_point'=>Yii::t('redeem','Cut Integral'),
            'apply_num'=>Yii::t('redeem','Number of applications'),
            'city'=>Yii::t('redeem','City'),
            'city_name'=>Yii::t('redeem','City Name'),
            'remark'=>Yii::t('redeem','Remark'),
            'status'=>Yii::t('redeem','Status'),
            'apply_date'=>Yii::t('redeem','apply for time'),
		);
	}

    public function rules()
    {
        return array(
            array('attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, searchTimeStart, searchTimeEnd','safe',),
        );
    }
    public function getNowApply(){

    }
    public function validateNowUser($bool = false){
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $rs = Yii::app()->db->createCommand()->select("b.id,b.name")->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee b","a.employee_id = b.id")
            ->where("a.user_id ='$uid'")->queryRow();
        if($rs){
            if($bool){
                $this->employee_id = $rs["id"];
                $this->employee_name = $rs["name"];
            }
            return true; //已綁定員工
        }else{
            return false;
        }
    }
    public function saveData()
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->saveStaff($connection);
            $transaction->commit();
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,'Cannot update.'.$e->getMessage());
        }
    }
    protected function saveStaff(&$connection)
    {
        $sql = '';
        $city = Yii::app()->user->city();
        $city_allow = Yii::app()->user->city_allow();
        $uid = Yii::app()->user->id;
//        var_dump($_POST);die();
        //礼物信息
        $sqlg = "SELECT * FROM sal_redeem_gifts WHERE gift_name='".$_POST['GiftApply']['gift_name']."' and bonus_point=".$_POST['GiftApply']['bonus_point']." and city='".$city."'";
        $gift = Yii::app()->db->createCommand($sqlg)->queryRow();

        switch ($this->scenario) {
            case 'delete':
                $sql = "delete from sal_redeem_gift_apply where id = :id and city IN ($city_allow)";
                break;
            case 'apply':
                $sql = "insert into sal_redeem_gift_apply(
							employee_id, employee_name,gift_id,gift_name, bonus_point, apply_num, city, city_name, remark, status, apply_date
						) values (
							:employee_id, :employee_name,:gift_id,:gift_name, :bonus_point, :apply_num, :city, :city_name, :remark, :status,:apply_date
						)";
                break;
            case 'audit':
                $sql = "update sal_redeem_gift_apply set
							bonus_point = :bonus_point,
							apply_num = :apply_num,
							remark = :remark,
							reject_note = '',
							state = :state,
							luu = :luu,
							apply_date = :apply_date
						where id = :id
						";
                break;
        }
        $command=$connection->createCommand($sql);

        if (strpos($sql,':employee_id')!==false)
            $command->bindParam(':employee_id',$this->employee_id,PDO::PARAM_INT);
        if (strpos($sql,':employee_name')!==false)
            $command->bindParam(':employee_name',$this->employee_name,PDO::PARAM_STR);
        if (strpos($sql,':gift_id')!==false)
            $command->bindParam(':gift_id',$gift['id'],PDO::PARAM_INT);
        if (strpos($sql,':gift_name')!==false)
            $command->bindParam(':gift_name',$gift['gift_name'],PDO::PARAM_STR);
        if (strpos($sql,':bonus_point')!==false)
            $command->bindParam(':bonus_point',$gift['bonus_point'],PDO::PARAM_INT);
        if (strpos($sql,':apply_num')!==false)
            $command->bindParam(':apply_num',$_POST['GiftApply']['apply_num'],PDO::PARAM_INT);
        if (strpos($sql,':city')!==false)
            $command->bindParam(':city',$city,PDO::PARAM_STR);
        if (strpos($sql,':city_name')!==false)
            $command->bindParam(':city_name',$gift['city_name'],PDO::PARAM_STR);
        if (strpos($sql,':remark')!==false)
            $command->bindParam(':remark',$_POST['GiftApply']['remark'],PDO::PARAM_STR);
        if (strpos($sql,':status')!==false)
            $command->bindParam(':status',$this->status,PDO::PARAM_INT);
        if (strpos($sql,':apply_date')!==false)
            $command->bindParam(':apply_date',date("Y-m-d"),PDO::PARAM_STR);
        $command->execute();

        if ($this->scenario=='apply'){
            $this->id = Yii::app()->db->getLastInsertID();
            Yii::app()->db->createCommand("update sal_redeem_score set score=score-".$_POST['GiftApply']['bonus_point']*$_POST['GiftApply']['apply_num']." where employee_id=".$this->employee_id)->execute();
        }

        if ($this->scenario=='apply'||$this->scenario=='audit'){
            //扣除庫存
            Yii::app()->db->createCommand("update sal_redeem_gifts set inventory=inventory-".$_POST['GiftApply']['apply_num']." where id=".$gift['id'])->execute();
        }

       // $this->sendEmail();
        return true;
    }
	public function retrieveDataByPage($pageNum=1)
	{
        $city_allow = Yii::app()->user->city_allow();
        $sql1 = "select *
				from sal_redeem_gift_apply
				where city IN ($city_allow) 
			";
        $sql2 = "select count(id)
				from sal_redeem_gift_apply
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
                    $clause .= ' and city in '.CreditRequestList::getCityCodeSqlLikeName($svalue);
                    break;
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
            //var_dump($records);die();
            foreach ($records as $k=>$record) {
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'employee_id'=>$record['employee_id'],
                    'employee_name'=>$record['employee_name'],
                    'gift_id'=>$record['gift_id'],
                    'gift_name'=>$record['gift_name'],
                    'bonus_point'=>$record['bonus_point'],
                    'apply_num'=>$record['apply_num'],
                    'city_name'=>$record['city_name'],
                    'status'=>$record['status'],
                    'remark'=>$record['remark'],
                    'apply_date'=>$record['apply_date'],
                    'city'=>Yii::app()->user->city,
                );
            }
        }
        $session = Yii::app()->session;
        $session['gift_op01'] = $this->getCriteria();
        return true;
	}

    //根據狀態獲取顏色
    public function statusToColor($status){
        switch ($status){
            // text-danger
            case 0:
                return array(
                    "status"=>Yii::t("integral","Draft"),
                    "style"=>""
                );
            case 1:
                return array(
                    "status"=>Yii::t("integral","Sent, pending approval"),//已發送，等待審核
                    "style"=>" text-primary"
                );
            case 2:
                return array(
                    "status"=>Yii::t("integral","Rejected"),//拒絕
                    "style"=>" text-danger"
                );
            case 3:
                return array(
                    "status"=>Yii::t("integral","approve"),//批准
                    "style"=>" text-green"
                );
        }
        return array(
            "status"=>"",
            "style"=>""
        );
    }
    function retrieveData($index) {
        $city_allow = Yii::app()->user->city_allow();
        $suffix = Yii::app()->params['envSuffix'];
        $rows = Yii::app()->db->createCommand()->select("*,docman$suffix.countdoc('ICUT',id) as icutdoc")
            ->from("sal_redeem_gift_apply")->where("id=:id and city in($city_allow)",array(":id"=>$index))->queryAll();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $this->id = $row['id'];
                $this->employee_id = $row['employee_id'];
                $this->employee_name = $row['employee_name'];
                $this->gift_id = $row['gift_id'];
                $this->gift_name = $row['gift_name'];
                $this->bonus_point = $row['bonus_point'];
                $this->apply_num = $row['apply_num'];
                $this->apply_date = $row['apply_date'];
                $this->status = $row['status'];
                $this->remark = $row['remark'];
                $this->no_of_attm['icut'] = $row['icutdoc'];
                break;

            }
        }
        return true;
    }
}
