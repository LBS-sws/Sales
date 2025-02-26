<?php

class PointsForm extends CFormModel
{
    /* User Fields */
    public $id;
    public $description;
    public $rpt_cat;
    public $detail = array(
        array('id'=>0,
            'cust_type_name'=>'',
            'conditions'=>'',
            'fraction'=>0,
            'toplimit'=>0,
            'uflag'=>'N',
        ),
    );
    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'description'=>Yii::t('code','Description'),
            'rpt_cat'=>Yii::t('code','Category'),
            'cust_type_name'=>Yii::t('code','Cust Type Name'),
            'conditions'=>Yii::t('code','Condition'),
            'fraction'=>Yii::t('code','Fractiony'),
            'toplimit'=>Yii::t('code','Toplimit'),

        );
    }

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('description','required'),
            array('id,rpt_cat','safe'),
        );
    }

    public function retrieveData($index)
    {
        $sql = "select * from sal_points_type where id=".$index."";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0)
        {
            foreach ($rows as $row)
            {
                $this->id = $row['id'];
                $this->description = $row['description'];
                $this->rpt_cat = $row['rpt_cat'];
                break;
            }
        }
        $sql = "select * from sal_points_type_twoname where cust_type_id=$index ";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            $this->detail = array();
            foreach ($rows as $row) {
                $temp = array();
                $temp['id'] = $row['id'];
                $temp['cust_type_name'] = $row['cust_type_name'];
                 $temp['conditions'] = $row['conditions'];
                $temp['fraction'] = $row['fraction'];
                $temp['toplimit'] = $row['toplimit'];
                $temp['uflag'] = 'N';
                $this->detail[] = $temp;
            }
        }
        return true;
    }

    public function saveData()
    {
        $connection = Yii::app()->db;
        $transaction=$connection->beginTransaction();
        try {
            $this->saveUser($connection);
            $this->saveCustomertypeDtl($connection);
            $transaction->commit();
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,'Cannot update.');
        }
    }

    protected function saveUser(&$connection)
    {
        $sql = '';
        switch ($this->scenario) {
            case 'delete':
                $sql = "delete from sal_points_type where id = :id";
                break;
            case 'new':
                $sql = "insert into sal_points_type(
						description, rpt_cat, luu, lcu) values (
						:description, :rpt_cat, :luu, :lcu)";
                break;
            case 'edit':
                $sql = "update sal_points_type set 
					description = :description, 
					rpt_cat = :rpt_cat,
					luu = :luu
					where id = :id";
                break;
        }

        $uid = Yii::app()->user->id;

        $command=$connection->createCommand($sql);
        if (strpos($sql,':id')!==false)
            $command->bindParam(':id',$this->id,PDO::PARAM_INT);
        if (strpos($sql,':description')!==false)
            $command->bindParam(':description',$this->description,PDO::PARAM_STR);
        if (strpos($sql,':rpt_cat')!==false)
            $command->bindParam(':rpt_cat',$this->rpt_cat,PDO::PARAM_STR);
        if (strpos($sql,':luu')!==false)
            $command->bindParam(':luu',$uid,PDO::PARAM_STR);
        if (strpos($sql,':lcu')!==false)
            $command->bindParam(':lcu',$uid,PDO::PARAM_STR);
        $command->execute();

        if ($this->scenario=='new')
            $this->id = Yii::app()->db->getLastInsertID();
        return true;
    }

    protected function saveCustomertypeDtl(&$connection)
    {
        $uid = Yii::app()->user->id;
        foreach ($_POST['PointsForm']['detail'] as $row) {
            $sql = '';
            switch ($this->scenario) {
                case 'delete':
                    $sql = "delete from sal_points_type_twoname where cust_type_id = :cust_type_id ";
                    break;
                case 'new':
                    if ($row['uflag']=='Y') {
                        $sql = "insert into sal_points_type_twoname(
									cust_type_id, cust_type_name, fraction, toplimit, conditions,
									 luu, lcu
								) values (
									:cust_type_id, :cust_type_name, :fraction, :toplimit, :conditions,
									 :luu, :lcu
								)";
                    }
                    break;
                case 'edit':
                    switch ($row['uflag']) {
                        case 'D':
                            $sql = "delete from sal_points_type_twoname where id = :id ";
                            break;
                        case 'Y':
                            $sql = ($row['id']==0)
                                ?
                                "insert into sal_points_type_twoname(
										cust_type_id, cust_type_name, fraction, toplimit, conditions,
										 luu, lcu
									) values (
										:cust_type_id, :cust_type_name, :fraction, :toplimit,:conditions,
										:luu, :lcu
									)
									"
                                :
                                "update sal_points_type_twoname set
										cust_type_id = :cust_type_id,
										cust_type_name = :cust_type_name, 
										conditions = :conditions,
										fraction = :fraction,									
										toplimit = :toplimit,
										luu = :luu 
									where id = :id 
									";
                            break;
                    }
                    break;
            }
//            print_r('<pre>');
//            print_r($this->id);
//            print_r($row['uflag']);
//            exit();
            if ($sql != '') {
                $command=$connection->createCommand($sql);
                if (strpos($sql,':id')!==false)
                    $command->bindParam(':id',$row['id'],PDO::PARAM_INT);
                if (strpos($sql,':cust_type_id')!==false)
                    $command->bindParam(':cust_type_id',$this->id,PDO::PARAM_INT);
                if (strpos($sql,':cust_type_name')!==false)
                    $command->bindParam(':cust_type_name',$row['cust_type_name'],PDO::PARAM_STR);
                if (strpos($sql,':conditions')!==false)
                    $command->bindParam(':conditions',$row['conditions'],PDO::PARAM_INT);
                if (strpos($sql,':fraction')!==false)
                    $command->bindParam(':fraction',$row['fraction'],PDO::PARAM_INT);
                if (strpos($sql,':toplimit')!==false)
                    $command->bindParam(':toplimit',$row['toplimit'],PDO::PARAM_INT);
                if (strpos($sql,':luu')!==false)
                    $command->bindParam(':luu',$uid,PDO::PARAM_STR);
                if (strpos($sql,':lcu')!==false)
                    $command->bindParam(':lcu',$uid,PDO::PARAM_STR);
                $command->execute();
            }
        }
        return true;
    }


    public function isReadOnly() {
        return ($this->scenario=='view');
    }
}
