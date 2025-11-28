<?php
//2024年9月28日09:28:46

class CurlReceiveList extends CListPageModel
{
    public $info_type;

    public function rules()
    {
        return array(
            array('info_type,attr, pageNum, noOfItem, totalRow,city, searchField, searchValue, orderField, orderType, filter, dateRangeValue','safe',),
        );
    }

    public function getCriteria() {
        return array(
            'info_type'=>$this->info_type,
            'searchField'=>$this->searchField,
            'searchValue'=>$this->searchValue,
            'orderField'=>$this->orderField,
            'orderType'=>$this->orderType,
            'noOfItem'=>$this->noOfItem,
            'pageNum'=>$this->pageNum,
            'filter'=>$this->filter,
            'dateRangeValue'=>$this->dateRangeValue,
        );
    }
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'status_type'=>Yii::t('curl','status type'),
			'info_type'=>Yii::t('curl','info type'),
			'info_url'=>Yii::t('curl','info url'),
			'data_content'=>Yii::t('curl','data content'),
			'out_content'=>Yii::t('curl','out content'),
			'message'=>Yii::t('curl','message'),
			'lcu'=>Yii::t('curl','lcu'),
			'lcd'=>Yii::t('curl','lcd'),
			'lud'=>Yii::t('curl','lud'),
		);
	}

	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select * 
				from datasync{$suffix}.sync_mh_api_curl 
				where 1=1 
			";
		$sql2 = "select count(id)
				from  datasync{$suffix}.sync_mh_api_curl  
				where 1=1 
			";
		$clause = "";
		if(!empty($this->info_type)){
            $svalue = str_replace("'","\'",$this->info_type);
            $clause.=" and info_type='$svalue' ";
        }
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'id':
					$clause .= General::getSqlConditionClause('id',$svalue);
					break;
				case 'status_type':
					$clause .= General::getSqlConditionClause('status_type',$svalue);
					break;
				case 'info_type':
					$clause .= General::getSqlConditionClause('info_type',$svalue);
					break;
				case 'data_content':
					$clause .= General::getSqlConditionClause('data_content',$svalue);
					break;
				case 'out_content':
					$clause .= General::getSqlConditionClause('out_content',$svalue);
					break;
				case 'message':
					$clause .= General::getSqlConditionClause('message',$svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
            $order .= " order by {$this->orderField} ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
            $order .= " order by id desc ";
        }

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
						'info_type'=>self::getInfoTypeList($record['info_type'],true),
						'status_type'=>$record['status_type'],
						'data_content'=>$record['data_content'],
						'out_content'=>$record['out_content'],
						//'data_content'=>urldecode($record['data_content']),
						//'out_content'=>urldecode($record['out_content']),
						'message'=>$record['message'],
						'lcu'=>$record['lcu'],
						'lcd'=>$record['lcd'],
						'lud'=>$record['lud'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['curlReceive_c01'] = $this->getCriteria();
		return true;
	}

	//翻译curl的类型
	public static function getInfoTypeList($key="",$bool=false){
        $list = array(
            "rpt"=>"报价审批",
            "cont"=>"合同审批",
            "pro"=>"合同变更审批",
            "virPro"=>"虚拟合同变更审批",
            "call"=>"呼叫式审批",
            "setFree"=>"修改服务频次",
            "setPerPWD"=>"设置联系人密码",
        );
        if($bool){
            if(key_exists($key,$list)){
                return $list[$key];
            }else{
                return $key;
            }
        }else{
            return $list;
        }
    }

	public function sendID($index){
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("*")->from("datasync{$suffix}.sync_mh_api_curl")
            ->where("id=:id and status_type!='P'", array(':id'=>$index))->queryRow();
        if($row){
            Yii::app()->db->createCommand()->update("datasync{$suffix}.sync_mh_api_curl",array(
                "status_type"=>"P",
                "message"=>null,
                "lcu"=>$uid,
            ),"id={$index}");
            return true;
        }else{
            return false;
        }
    }

    private function sendCurl($url,$data){
        $data = json_encode($data);
        $url = Yii::app()->params['curlLink'].$url;
        $svrkey = self::generate_key();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'Content-Length:'.strlen($data),
            'Authorization: SvrKey '.$svrkey,
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $out = curl_exec($ch);
        if ($out===false) {
            echo 'Error: '.curl_error($ch);
        } else {
            var_dump($out);
        }
        curl_close($ch);
    }

    public static function generate_key(){
        $ip =Yii::app()->params['uCurlIP'];
        $interval = 600; // 10分钟的秒数
        $secret_key = 'c09c321acaf59c57e2a2a999e31b5ea8'; // 加密密钥

        //生成key
        $salt = floor(time() / $interval) * $interval; // 使用10分钟为间隔的时间戳作为盐

        $ip_split = explode('.', $ip);
        if(count($ip_split)!=4){
            return false;
        }
        $hexip = sprintf('%02x%02x%02x%02x', $ip_split[0], $ip_split[1], $ip_split[2], $ip_split[3]);
        $key = hash('sha256', $ip . $salt . $hexip);

        //加密发送时间戳
        $encryptedData = openssl_encrypt($salt, 'AES-128-ECB', $secret_key, OPENSSL_RAW_DATA);

        $encrypted = base64_encode($encryptedData);
        return $key.'.'.$encrypted;
    }

    public static function encryptSalt($password) {
        // 使用'$6$'指定SHA-256和自动盐值生成的前缀
        // 这里的12个字符是盐值的长度，可以根据需要调整
        $salt = '$6$' . substr(sha1(uniqid(mt_rand(), true)), 0, 12);
        return crypt($password, $salt);
    }

    public static function getCurlTextForID($id,$type=0){
        $type = "".$type;
        $list = array(
            0=>"data_content",//请求内容
            1=>"out_content",//响应的内容
            //2=>"cmd_content",//执行结果
        );
        $selectStr = key_exists($type,$list)?$list[$type]:$list[0];
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($selectStr)->from("datasync{$suffix}.sync_mh_api_curl")
            ->where("id=:id", array(':id'=>$id))->queryRow();
        if($row){
            $searchList = array("\\r","\\n","\\t");
            $replaceList = array("\r","\n","\t");
            return str_replace($searchList,$replaceList,$row[$selectStr]);
        }else{
            return "";
        }
    }
}
