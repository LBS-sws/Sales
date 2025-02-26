<?php
class  CoefficientForm extends CFormModel
{
	public $id = 0;
	public $city;
	public $city_name;
	public $start_dt;
	public $detail = array(
				array('id'=>0,
					'hdr_id'=>0,
					'operator'=>'',
					'criterion'=>0,
                    'bonus'=>0,
                    'coefficient'=>0,
					'name'=>'',
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
			'start_dt'=>Yii::t('sales','Start Date'),
			'operator'=>Yii::t('sales','Sign'),
			'criterion'=>Yii::t('sales','Criterion'),
			'bonus'=>Yii::t('sales','Bonus'),
            'coefficient'=>Yii::t('sales','Coefficients'),
			'name'=>Yii::t('sales','Name'),
		);
	}

	public function rules()
	{
		return array(
			array('id,','safe'),
			array(' start_dt','required'),
			array('start_dt','date','allowEmpty'=>false,
				'format'=>array('MM/dd/yyyy','dd/MM/yyyy','yyyy/MM/dd',
							'MM-dd-yyyy','dd-MM-yyyy','yyyy-MM-dd',
							'M/d/yyyy','d/M/yyyy','yyyy/M/d',
							'M-d-yyyy','d-M-yyyy','yyyy-M-d',
							),
			),
			array('','validateDetailRecords'),
		);
	}

	public function validateDetailRecords($attribute, $params) {
		$rows = $this->$attribute;
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if ($row['uflag']=='Y') {
					if (!is_numeric($row['criterion']))
						$this->addError($attribute, Yii::t('service','Invalid amount').' '.$row['criterion']);
					if (!is_numeric($row['bonus']))
						$this->addError($attribute, Yii::t('service','Invalid HY PC Rate').' '.$row['bonus']);
                    if (!is_numeric($row['coefficient']))
                        $this->addError($attribute, Yii::t('service','Invalid HY PC Rate').' '.$row['coefficient']);
					if (!is_numeric($row['name']))
						$this->addError($attribute, Yii::t('service','Invalid INV Rate').' '.$row['name']);
				}
			}
		}
	}
	
	public function retrieveData($index)
	{
		$city = Yii::app()->user->city_allow();
		$sql = "select * from sal_coefficient_hdr where id=$index ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->start_dt = General::toDate($row['start_dt']);

			$sql = "select * from sal_coefficient_dtl where hdr_id=$index order by name,operator desc, criterion";
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				$this->detail = array();
				foreach ($rows as $row) {
					$temp = array();
					$temp['id'] = $row['id'];
					$temp['hdr_id'] = $row['hdr_id'];
					$temp['criterion'] = $row['criterion'];
					$temp['bonus'] = $row['bonus'];
					$temp['coefficient'] = $row['coefficient'];
                    $temp['name'] = $row['name'];
					$temp['operator'] = $row['operator'];
					$temp['uflag'] = 'N';
					$this->detail[] = $temp;
				}
			}
			return true;
		} else {
			return false;
		}
		
	}
	
	public function saveData()
	{

		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveHeader($connection);
			$this->saveDetail($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveHeader(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_coefficient_hdr where id = :id ";
				break;
			case 'new':
				$sql = "insert into sal_coefficient_hdr(
						name,start_dt,  luu, lcu
						) values (
						:name,:start_dt,  :luu, :lcu
						)";
                $name=$_POST['CoefficientForm']['detail'][0]['name'];
				break;
			case 'edit':
				$sql = "update sal_coefficient_hdr set  
                            name = :name,                     						  
							start_dt = :start_dt,
							luu = :luu 
						where id = :id
						";
                $name=$_POST['CoefficientForm']['detail'][0]['name'];
				break;
		}

//		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':start_dt')!==false) {
			$sdate = General::toMyDate($this->start_dt);
			$command->bindParam(':start_dt',$sdate,PDO::PARAM_STR);
		}
        if (strpos($sql,':name')!==false) {
            $command->bindParam(':name',$name,PDO::PARAM_STR);
        }
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	protected function saveDetail(&$connection)
	{
		$uid = Yii::app()->user->id;

		foreach ($_POST['CoefficientForm']['detail'] as $row) {
			$sql = '';
			switch ($this->scenario) {
				case 'delete':
					$sql = "delete from sal_coefficient_dtl where hdr_id = :hdr_id";
					break;
				case 'new':
					if ($row['uflag']=='Y') {
						$sql = "insert into sal_coefficient_dtl(
									hdr_id, operator, criterion, bonus, name,coefficient,
									luu, lcu
								) values (
									:hdr_id, :operator, :criterion, :bonus, :name,:coefficient,
									:luu, :lcu
								)";
					}
					break;
				case 'edit':
					switch ($row['uflag']) {
						case 'D':
							$sql = "delete from sal_coefficient_dtl where id = :id";
							break;
						case 'Y':
							$sql = ($row['id']==0)
									?
									"insert into sal_coefficient_dtl(
										hdr_id, operator, criterion, bonus, name,coefficient,
										luu, lcu
									) values (
										:hdr_id, :operator, :criterion, :bonus, :name,:coefficient,
										:luu, :lcu
									)"
									: 
									"update sal_coefficient_dtl set
										hdr_id = :hdr_id,
										operator = :operator, 
										criterion = :criterion,
										bonus = :bonus,
										coefficient=:coefficient,
										name = :name,
										luu = :luu 
									where id = :id
									";
							break;
					}
					break;
			}

			if ($sql != '') {
//                print_r('<pre>');
//                print_r($sql);exit();
				$command=$connection->createCommand($sql);
				if (strpos($sql,':id')!==false)
					$command->bindParam(':id',$row['id'],PDO::PARAM_INT);
				if (strpos($sql,':hdr_id')!==false)
					$command->bindParam(':hdr_id',$this->id,PDO::PARAM_INT);
				if (strpos($sql,':operator')!==false)
					$command->bindParam(':operator',$row['operator'],PDO::PARAM_STR);
				if (strpos($sql,':criterion')!==false) {
//					$amt = General::toMyNumber($row['criterion']);
					$command->bindParam(':criterion',$row['criterion'],PDO::PARAM_STR);
				}
				if (strpos($sql,':bonus')!==false) {
					$command->bindParam(':bonus',$row['bonus'],PDO::PARAM_STR);
				}
                if (strpos($sql,':coefficient')!==false) {
                    $coefficient = General::toMyNumber($row['coefficient']);
                    $command->bindParam(':coefficient',$coefficient,PDO::PARAM_STR);
                }
				if (strpos($sql,':name')!==false) {
					$command->bindParam(':name',$row['name'],PDO::PARAM_STR);
				}
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
