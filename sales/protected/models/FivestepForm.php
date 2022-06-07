<?php

class FivestepForm extends CFormModel
{
	public $id;
	public $username;
	public $rec_dt;
	public $step;
	public $staff_name;
	public $staff_code;
	public $dept_name;
	public $post_name;
	public $filename;
	public $filetype;
	public $status;
	public $city;
	public $city_name;
	public $sup_score;
	public $mgr_score;
	public $dir_score;
	public $sup_remarks;
	public $mgr_remarks;
	public $dir_remarks;
	public $sup_score_dt;
	public $mgr_score_dt;
	public $dir_score_dt;
	public $sup_score_user;
	public $mgr_score_user;
	public $dir_score_user;
	public $remarks;
	public $five_type;
	
	protected $dir_score_flag = false;
	protected $mgr_score_flag = false;
	protected $sup_score_flag = false;
	
	protected $dynfields = array('sup_score', 'mgr_score', 'dir_score', 
							'sup_score_dt', 'mgr_score_dt', 'dir_score_dt', 
							'sup_score_user', 'mgr_score_user', 'dir_score_user',
							'remarks', 'sup_remarks', 'mgr_remarks', 'dir_remarks'
						);

	public function init() {
		if (php_sapi_name() != "cli") {
			$this->city = Yii::app()->user->city();
			$this->username = Yii::app()->user->id;
			$this->rec_dt = date("Y/m/d");
			$this->step = '1';
			$this->getStaffInfo();
		}
	}
	
	public function attributeLabels()
	{
		return array(
			'rec_dt'=>Yii::t('sales','Record Date'),
			'staff_code'=>Yii::t('sales','Staff Code'),
			'staff_name'=>Yii::t('sales','Staff Name'),
			'post_name'=>Yii::t('sales','Position'),
			'dept_name'=>Yii::t('sales','Department'),
			'filename'=>Yii::t('sales','File Name'),
			'remarks'=>Yii::t('sales','Remarks'),
			'city'=>Yii::t('sales','City'),
			'step'=>Yii::t('sales','5 Steps'),
			'mgr_score'=>Yii::t('sales','Manager Score').'<br>('.Yii::t('sales','Full mark: 100').') ',
			'dir_score'=>Yii::t('sales','Director Score').'<br>('.Yii::t('sales','Full mark: 100').') ',
			'sup_score'=>Yii::t('sales','Supervisor Score').'<br>('.Yii::t('sales','Full mark: 100').') ',
			'mgr_remarks'=>Yii::t('sales','Manager Comment'),
			'dir_remarks'=>Yii::t('sales','Director Comment'),
			'sup_remarks'=>Yii::t('sales','Supervisor Comment'),
			'mgr_score_dt'=>Yii::t('sales','Manager Score Date'),
			'dir_score_dt'=>Yii::t('sales','Director Score Date'),
			'sup_score_dt'=>Yii::t('sales','Supervisor Score Date'),
			'mgr_score_user'=>Yii::t('sales','Manager Score User'),
			'dir_score_user'=>Yii::t('sales','Director Score User'),
			'sup_score_user'=>Yii::t('sales','Supervisor Score User'),
            'five_type'=>Yii::t('misc','Five Type'),
		);
	}

	public function rules() {
		return array(
			array('rec_dt, username, step,five_type','required'),
			array('filename', 'file', 'types'=>'mp4, m4a, mp3, 3gp, mov, wav', 'allowEmpty'=>false, 'on'=>'new'),
			array('sup_score, mgr_score, dir_score','numerical','allowEmpty'=>true,'integerOnly'=>true),
			array('sup_score, mgr_score, dir_score','in','range'=>range(-1,100)),
			array('id, status, city, city_name, remarks, staff_name, staff_code, dept_name, post_name, sup_score, mgr_score, dir_score,
				sup_score_dt, mgr_score_dt, dir_score_dt, sup_score_user, mgr_score_user, dir_score_user, filename, filetype, 
				sup_remarks, mgr_remarks, dir_remarks','safe'),
            array('id','validateID'),
		);
	}

    public function validateID($attribute, $params) {
	    if($this->getScenario()=="edit"){
	        if($this->isSuperRight()){//经理权限
                if(empty($this->sup_score)){
                    $this->addError($attribute, "销售经理评分不能为空");
                }
                if(empty($this->sup_remarks)){
                    $this->addError($attribute, "销售经理意见不能为空");
                }
            }
	        if($this->isManagerRight()){//总经理权限
                if(empty($this->mgr_score)){
                    $this->addError($attribute, "总经理评分不能为空");
                }
                if(empty($this->mgr_remarks)){
                    $this->addError($attribute, "总经理意见不能为空");
                }
            }
	        if($this->isDirectorRight()){//经理权限
                if(empty($this->dir_score)){
                    $this->addError($attribute, "总监评分不能为空");
                }
                if(empty($this->dir_remarks)){
                    $this->addError($attribute, "总监意见不能为空");
                }
            }
        }
    }

	public function retrieveData($index) {
		$suffix = Yii::app()->params['envSuffix'];
		$citylist = Yii::app()->user->city_allow();
		$user = Yii::app()->user->id;
		$sql = "select a.*, b.name as city_name, 
				d.field_value as mgr_score, e.field_value as dir_score, m.field_value as sup_score,
				f.field_value as mgr_score_dt, g.field_value as dir_score_dt, n.field_value as sup_score_dt,
				h.field_value as mgr_score_user, i.field_value as dir_score_user, o.field_value as sup_score_user,
				j.field_value as remarks,
				k.field_value as mgr_remarks,
				l.field_value as dir_remarks,
				p.field_value as sup_remarks
				from sal_fivestep a 
				left outer join security$suffix.sec_city b on a.city=b.code
				left outer join sal_fivestep_info d on a.id=d.five_id and d.field_id='mgr_score'
				left outer join sal_fivestep_info e on a.id=e.five_id and e.field_id='dir_score'
				left outer join sal_fivestep_info f on a.id=f.five_id and f.field_id='mgr_score_dt'
				left outer join sal_fivestep_info g on a.id=g.five_id and g.field_id='dir_score_dt'
				left outer join sal_fivestep_info h on a.id=h.five_id and h.field_id='mgr_score_user'
				left outer join sal_fivestep_info i on a.id=i.five_id and i.field_id='dir_score_user'
				left outer join sal_fivestep_info j on a.id=j.five_id and j.field_id='remarks'
				left outer join sal_fivestep_info k on a.id=k.five_id and k.field_id='mgr_remarks'
				left outer join sal_fivestep_info l on a.id=l.five_id and l.field_id='dir_remarks'
				left outer join sal_fivestep_info m on a.id=m.five_id and m.field_id='sup_score'
				left outer join sal_fivestep_info n on a.id=n.five_id and n.field_id='sup_score_dt'
				left outer join sal_fivestep_info o on a.id=o.five_id and o.field_id='sup_score_user'
				left outer join sal_fivestep_info p on a.id=p.five_id and p.field_id='sup_remarks'
				where a.id=$index
			";
		$sql .= ($this->isManagerRight() || $this->isDirectorRight() || $this->isSuperRight()) ? " and a.city in ($citylist)" : " and a.username='$user' ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->rec_dt = General::toDate($row['rec_dt']);
			$this->username = $row['username'];
			$this->getStaffInfo();
			$this->staff_name = $this->staff_code.' - '.$this->staff_name;
			$this->step = $row['step'];
			$this->status = $row['status'];
			$this->city_name = $row['city_name'];
			$this->mgr_score = $row['mgr_score'];
			$this->mgr_remarks = $row['mgr_remarks'];
			$this->mgr_score_dt = $row['mgr_score_dt'];
			$this->mgr_score_user = $row['mgr_score_user'];
			$this->dir_score = $row['dir_score'];
			$this->dir_remarks = $row['dir_remarks'];
			$this->dir_score_dt = $row['dir_score_dt'];
			$this->dir_score_user = $row['dir_score_user'];
			$this->sup_score = $row['sup_score'];
			$this->sup_remarks = $row['sup_remarks'];
			$this->sup_score_dt = $row['sup_score_dt'];
			$this->sup_score_user = $row['sup_score_user'];
			$this->remarks = $row['remarks'];
			$this->filename = $row['filename'];
			$this->filetype = $row['filetype'];
			$this->five_type = $row['five_type'];
		}
		return true;
	}
	
	protected function getStaffInfo() {
		$suffix = Yii::app()->params['envSuffix'];
		$user = $this->username;
		$sql = "select b.name as staff_name, b.code as staff_code, c.name as dept_name, d.name as post_name
				from hr$suffix.hr_binding a
				inner join hr$suffix.hr_employee b on a.employee_id=b.id
				left outer join hr$suffix.hr_dept c on b.department=c.id 
				left outer join hr$suffix.hr_dept d on b.position=d.id 
				where a.user_id = '$user'
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->staff_name = $row['staff_name'];
			$this->staff_code = $row['staff_code'];
			$this->dept_name = $row['dept_name'];
			$this->post_name = $row['post_name'];
		}
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveFiveStep($connection);
			if (!empty($this->mgr_score) && empty($this->mgr_score_user)) {
				$this->mgr_score_user = Yii::app()->user->id;
				$this->mgr_score_dt = date("Y/m/d H:m:s");
				$this->mgr_score_flag = true;
			}
			if (!empty($this->dir_score) && empty($this->dir_score_user)) {
				$this->dir_score_user = Yii::app()->user->id;
				$this->dir_score_dt = date("Y/m/d H:m:s");
				$this->dir_score_flag = true;
			}
			if (!empty($this->sup_score) && empty($this->sup_score_user)) {
				$this->sup_score_user = Yii::app()->user->id;
				$this->sup_score_dt = date("Y/m/d H:m:s");
				$this->sup_score_flag = true;
			}
			$this->saveFiveStepInfo($connection);
			$this->sendEmail($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update. '.$e->getMessage());
		}
	}

	protected function saveFiveStep(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_fivestep where id = :id";
				break;
			case 'new':
				$sql = "insert into sal_fivestep(
						five_type,username, rec_dt, step, filename, filetype, status, city, luu, lcu) values (
						:five_type,:username, :rec_dt, :step, :filename, :filetype, :status, :city, :luu, :lcu)";
				break;
			case 'edit':
				$sql = "update sal_fivestep set 
                    five_type=:five_type,
					username = :username, 
					rec_dt = :rec_dt,
					step = :step,
					status = :status,
					luu = :luu
					where id = :id and city=:city";
				break;
		}

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$dt = General::toMyDate($this->rec_dt);

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':username')!==false)
			$command->bindParam(':username',$this->username,PDO::PARAM_STR);
		if (strpos($sql,':rec_dt')!==false)
			$command->bindParam(':rec_dt',$dt,PDO::PARAM_STR);
		if (strpos($sql,':five_type')!==false)
			$command->bindParam(':five_type',$this->five_type,PDO::PARAM_STR);
        if (strpos($sql,':step')!==false)
            $command->bindParam(':step',$this->step,PDO::PARAM_STR);
		if (strpos($sql,':filename')!==false)
			$command->bindParam(':filename',$this->filename,PDO::PARAM_STR);
		if (strpos($sql,':filetype')!==false)
			$command->bindParam(':filetype',$this->filetype,PDO::PARAM_STR);
		if (strpos($sql,':status')!==false)
			$command->bindParam(':status',$this->status,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	protected function saveFiveStepInfo(&$connection) {
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from sal_fivestep_info where five_id = :five_id and field_id = :field_id";
				break;
			case 'new' :
			case 'edit':
				$sql = "insert into sal_fivestep_info(
						five_id, field_id, field_value, luu, lcu) values (
						:five_id, :field_id, :field_value, :luu, :lcu)
						on duplicate key update
						field_value = :field_value, luu  = :luu
						";
		}

		$uid = Yii::app()->user->id;

		foreach ($this->dynfields as $field) {
			$value = $this->$field;
			$command=$connection->createCommand($sql);
			if (strpos($sql,':five_id')!==false)
				$command->bindParam(':five_id',$this->id,PDO::PARAM_INT);
			if (strpos($sql,':field_id')!==false)
				$command->bindParam(':field_id',$field,PDO::PARAM_STR);
			if (strpos($sql,':field_value')!==false)
				$command->bindParam(':field_value',$this->$field,PDO::PARAM_STR);
			if (strpos($sql,':luu')!==false)
				$command->bindParam(':luu',$uid,PDO::PARAM_STR);
			if (strpos($sql,':lcu')!==false)
				$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
			$command->execute();
		}
	}
	
	protected function sendEmail(&$connection) {
		$flag = false;
		$from = Yii::app()->params['adminEmail'];
		$to = '';
		$cc = '';
		$subject = Yii::t('app','Sales').': ';
		$description = '';
		$message = '';
		$steps = $this->getStepList();
		$subjinfo = ' ('.Yii::t('sales','Record Date').':'.$this->rec_dt.', '.$steps[$this->step].')';
		$citylist = General::getCityList();
		$url = Yii::app()->createAbsoluteUrl('fivestep/edit', array('index'=>$this->id));
		
		if ($this->scenario=='new') {
			$flag = true;
			
			$a_mgr = $this->getUsersWithRight('CN01');
			$a_dir = $this->getUsersWithRight('CN02');
			$a_sup = $this->getUsersWithRight('CN05');
			$users = array_merge($a_mgr, $a_dir, $a_sup);
			$a_to = General::getEmailByUserIdArray($users);
			$a_to = General::dedupToEmailList($a_to);
			$a_cc = array();
			
//			$from = General::getEmailByUserId($this->username);
			$to = json_encode($a_to);
			$cc = json_encode($a_cc);
			
			$subject .= Yii::t('sales','A new 5 step record is pending for scoring').$subjinfo;
			$description .= Yii::t('sales','Pending for Scoring');
			$message = '<p>'.Yii::t('misc','City').': '.$citylist[$this->city].'<br>';
			$message .= Yii::t('sales','Staff Name').': '.$this->staff_name.'<br>';
			$message .= Yii::t('sales','Department').': '.$this->dept_name.'<br>';
			$message .= Yii::t('sales','Position').': '.$this->post_name.'<br>';
			$message .= Yii::t('sales','Record Date').': '.$this->rec_dt.'<br>';
			$message .= Yii::t('sales','5 Steps').': '.$steps[$this->step].'<br></p>';
			$message .= '<p>'.Yii::t('sales',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Sales');\">here</a> to carry out your job.").'</p>';
			$message = str_replace('{url}',$url,$message);
		} else {
			if ($this->dir_score_flag || $this->mgr_score_flag || $this->sup_score_flag) {
				$flag = true;

				$a_to = array(General::getEmailByUserId($this->username));
				$a_cc = array();
				$to = json_encode($a_to);
				$cc = json_encode($a_cc);

				if ($this->dir_score_flag && $this->mgr_score_flag && $this->sup_score_flag) {
					$subject .= Yii::t('sales','Supervisor, Manager and Director have scored a 5 step record').$subjinfo;
				} elseif ($this->dir_score_flag && $this->mgr_score_flag) {
					$subject .= Yii::t('sales','Manager and Director have scored a 5 step record').$subjinfo;
				} elseif ($this->dir_score_flag && $this->sup_score_flag) {
					$subject .= Yii::t('sales','Supervisor and Director have scored a 5 step record').$subjinfo;
				} elseif ($this->mgr_score_flag && $this->sup_score_flag) {
					$subject .= Yii::t('sales','Supervisor and Manager have scored a 5 step record').$subjinfo;
				} elseif ($this->dir_score_flag) {
					$subject .= Yii::t('sales','Director has scored a 5 step record').$subjinfo;
				} elseif ($this->mgr_score_flag) {
					$subject .= Yii::t('sales','Manager has scored a 5 step record').$subjinfo;
				} else {
					$subject .= Yii::t('sales','Supervisor has scored a 5 step record').$subjinfo;
				}
				$description .= Yii::t('sales','Record Scored');
				$message .= '<p>'.Yii::t('sales',"Please click <a href=\"{url}\" onClick=\"return popup(this,'Sales');\">here</a> to read the result.").'</p>';
				$message = str_replace('{url}',$url,$message);
			}
		}

		if ($flag) {
			$suffix = Yii::app()->params['envSuffix'];
			$suffix = $suffix=='dev' ? '_w' : $suffix;
			$sql = "insert into swoper$suffix.swo_email_queue
						(from_addr, to_addr, cc_addr, subject, description, message, status, lcu)
					values
						(:from_addr, :to_addr, :cc_addr, :subject, :description, :message, 'P', 'admin')
				";
			$command = $connection->createCommand($sql);
			if (strpos($sql,':from_addr')!==false)
				$command->bindParam(':from_addr',$from,PDO::PARAM_STR);
			if (strpos($sql,':to_addr')!==false)
				$command->bindParam(':to_addr',$to,PDO::PARAM_STR);
			if (strpos($sql,':cc_addr')!==false)
				$command->bindParam(':cc_addr',$cc,PDO::PARAM_STR);
			if (strpos($sql,':subject')!==false)
				$command->bindParam(':subject',$subject,PDO::PARAM_STR);
			if (strpos($sql,':description')!==false)
				$command->bindParam(':description',$description,PDO::PARAM_STR);
			if (strpos($sql,':message')!==false)
				$command->bindParam(':message',$message,PDO::PARAM_STR);
			$command->execute();
		}
	}

	protected function getUsersWithRight($right) {
		$rtn = array();
		$sysid = Yii::app()->params['systemId'];
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.username, b.city from security$suffix.sec_user_access a, security$suffix.sec_user b
				where (a.a_read_only like '%$right%' or a.a_read_write like '%$right%' or a.a_control like '%$right%')
				and a.username=b.username and b.status='A' and a.system_id='$sysid'
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (!empty($rows)) {
			$cityo = new City;
			$citylist = $cityo->getAncestorList($this->city).','.$this->city;
			foreach ($rows as $row) {
				if (strpos($citylist,$row['city'])!==false) $rtn[] = $row['username'];
			}
		}
		return $rtn;
	}

	public function toEmail($name){
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select * from security$suffix.sec_user where username='".$name."'";
        $rows = Yii::app()->db->createCommand($sql)->queryRow();
        $from_addr = "it@lbsgroup.com.hk";
        $subject = "五部曲提醒";
        $description = "五部曲提醒";
		$steplist = $this->getStepList();
		$typelist = $this->getFiveTypeList();

        $message = "您的五部曲评分为不合格，请重新上传<br>";
		$message .= "姓名：" . $rows['disp_name']."<br>";
		$message .= Yii::t('sales','Position')."：".$this->post_name."<br>";
		$message .= Yii::t('sales','5 Steps')."：".$steplist[$this->step]."<br>";
		$message .= Yii::t('misc','Five Type')."：".$typelist[$this->five_type]."<br>";
        $lcu = "admin";
			$suffix = $suffix=='dev' ? '_w' : $suffix;
        $aaa = Yii::app()->db->createCommand()->insert("swoper$suffix.swo_email_queue", array(
            'request_dt' => date('Y-m-d H:i:s'),
            'from_addr' => $from_addr,
            'to_addr' => $rows['email'],
            'subject' => $subject,//郵件主題
            'description' => $description,//郵件副題
            'message' => $message,//郵件內容（html）
            'status' => "P",
            'lcu' => $lcu,
            'lcd' => date('Y-m-d H:i:s'),
        ));
		
		$connection = Yii::app()->db;
		SystemNotice::addNotice($connection, array(
				'note_type'=>'notice',
				'subject'=>$subject,
				'description'=>$description,
				'message'=>$message,
				'username'=>json_encode(array($name)),
				'system_id'=>Yii::app()->user->system(),
				'form_id'=>'FiveStepForm',
				'rec_id'=>$this->id,
			)
		);

    }

	public function isReadOnly() {
		return (
					$this->scenario=='view' || 
					(!$this->isManagerRight() && !$this->isDirectorRight() && !$this->isSuperRight() && (!empty($this->sup_score_user) || !empty($this->mgr_score_user) || !empty($this->dir_score_user))) ||
					($this->isSuperRight() && !$this->isManagerRight() && !$this->isDirectorRight() && !empty($this->sup_score_user)) ||
					($this->isManagerRight() && !$this->isSuperRight() && !$this->isDirectorRight() && !empty($this->mgr_score_user)) ||
					($this->isDirectorRight() && !$this->isSuperRight() && !$this->isManagerRight() && !empty($this->dir_score_user)) ||
					($this->isSuperRight() && $this->isManagerRight() && !$this->isDirectorRight() && !empty($this->sup_score_user) && !empty($this->mgr_score_user)) ||
					($this->isSuperRight() && !$this->isManagerRight() && $this->isDirectorRight() && !empty($this->sup_score_user) && !empty($this->dir_score_user)) ||
					($this->isManagerRight() && !$this->isSuperRight() && $this->isDirectorRight() && !empty($this->mgr_score_user) && !empty($this->dir_score_user)) ||
					($this->isDirectorRight() && !$this->isSuperRight() && $this->isManagerRight() && !empty($this->dir_score_user) && !empty($this->mgr_score_user)) ||
					($this->isManagerRight() && $this->isDirectorRight() && $this->isSuperRight() && !empty($this->mgr_score_user) && !empty($this->dir_score_user) && !empty($this->sup_score_user))
				);
	}

	public function isIncompetent($model){
	    return ($model['dir_score']==-1||$model['mgr_score']==-1||$model['sup_score']==-1);
    }

	public function getMediaFile($raw=false) {
		$content = '';
		if (!empty($this->filename)) {
			$media = file_get_contents($this->filename);
			$content = $raw ? $media : "data:".$this->filetype.";base64,".base64_encode($media);
		}
		return $content;
	}
	
	public function getStepList() {
		return array('1'=>Yii::t('sales','Step 1'),
					'2'=>Yii::t('sales','Step 2'),
					'3'=>Yii::t('sales','Step 3'),
					'4'=>Yii::t('sales','Step 4'),
					'5'=>Yii::t('sales','Step 5'),
					);	
	}
	
	public function getFiveTypeList() {
		return array(
			0=>Yii::t('misc','Insecticidal'),
			1=>Yii::t('misc','Restroom'),
			2=>Yii::t('misc','Third'),
			3=>Yii::t('misc','Air Purifier'),
			4=>Yii::t('misc','Oil separator'),
		);
	}
	
	public function getStepDesc() {
		return array(
					'1'=>array(
							'desc'=>Yii::t('sales','销售讲稿演练'),
							'type'=>Yii::t('sales','视频'),
							'detail'=>Yii::t('sales','把讲稿流畅背诵于镜头前'),
						),
					'2'=>array(
							'desc'=>Yii::t('sales','应对套词技巧'),
							'type'=>Yii::t('sales','音频'),
							'detail'=>Yii::t('sales','增加销售手中应对武器'),
						),
					'3'=>array(
							'desc'=>Yii::t('sales','陪伴陌生拜访演练和总结'),
							'type'=>Yii::t('sales','拜访/音频'),
							'detail'=>Yii::t('sales','增加销售临场应对能力，总结回馈给销售，达到学习循环的效果'),
						),
					'4'=>array(
							'desc'=>Yii::t('sales','教练培训演练'),
							'type'=>Yii::t('sales','拜访/音频'),
							'detail'=>Yii::t('sales','主要是区域副总监或以上跟进地方培训者（老总/销售总监/销售经理/副经理），传授和确保承传的销售技巧原汁原味'),
						),
					'5'=>array(
							'desc'=>Yii::t('sales','教练培训总结'),
							'type'=>Yii::t('sales','面谈/音频'),
							'detail'=>Yii::t('sales','主要观察培训者跟销售的陌拜和总结是否到位和加而优化这培训过程，主导者一般是区域总监或以上岗位'),
						),
				);
	}
	
	public function isSuperRight() {
		return Yii::app()->user->validFunction('CN05');
	}
	
	public function isManagerRight() {
		return Yii::app()->user->validFunction('CN01');
	}
	
	public function isDirectorRight() {
		return Yii::app()->user->validFunction('CN02');
	}

	public function isRemoveRight() {
		return Yii::app()->user->validFunction('CN11');
	}
}
