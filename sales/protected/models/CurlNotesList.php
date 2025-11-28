<?php
//2024年9月28日09:28:46

class CurlNotesList extends CListPageModel
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
				from sal_api_curl 
				where 1=1 
			";
		$sql2 = "select count(id)
				from sal_api_curl 
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
				case 'status_type':
					$clause .= General::getSqlConditionClause('status_type',$svalue);
					break;
				case 'info_type':
					$clause .= General::getSqlConditionClause('info_type',$svalue);
					break;
				case 'info_url':
					$clause .= General::getSqlConditionClause('info_url',$svalue);
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
				case 'id':
					$clause .= General::getSqlConditionClause('id',$svalue);
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
						'status_type'=>self::getCurlStatusNameToID($record['status_type']),
						'info_type'=>self::getInfoTypeList($record['info_type'],true),
						'info_url'=>$record['min_url'],
						'data_content'=>$record['data_content'],
						'out_content'=>$record['out_content'],
						'message'=>$record['message'],
						'lcu'=>$record['lcu'],
						'lcd'=>$record['lcd'],
						'lud'=>$record['lud'],
					);
			}
		}
		$session = Yii::app()->session;
		$session['opr_curlNotes_c01'] = $this->getCriteria();
		return true;
	}

    //获取员工类型翻译
    public static function getCurlStatusNameToID($id){
        $id = "".$id;
        $list = array(
            "P"=>"未进行",
            "C"=>"已完成",
            "E"=>"错误",
        );
        if(key_exists($id,$list)){
            return $list[$id];
        }else{
            return $id;
        }
    }

    //翻译curl的类型
    public static function getInfoTypeList($key="",$bool=false){
        $list = array(
            "client"=>"客户",
            "clientPerson"=>"客户联系人",
            "clientStaff"=>"客户负责人",
            "clientArea"=>"客户归属区域",
            "store"=>"门店",
            "storePerson"=>"门店联系人",
            //"cont"=>"主合约",
            "contVir"=>"虚拟合约",
            "contFile"=>"合约附件",
            "callFree"=>"呼叫式",
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

    public static function getCurlTextForID($id,$type=0){
        $type = "".$type;
        $list = array(
            0=>"data_content",//请求内容
            1=>"out_content",//响应的内容
            2=>"info_url",//
        );
        $selectStr = key_exists($type,$list)?$list[$type]:$list[0];
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select($selectStr)->from("sal_api_curl")
            ->where("id=:id", array(':id'=>$id))->queryRow();
        if($row){
            $searchList = array("\\r","\\n","\\t");
            $replaceList = array("\r","\n","\t");
            return str_replace($searchList,$replaceList,$row[$selectStr]);
        }else{
            return "";
        }
    }

    public function resetSendData($id,$bool){
        $uid = Yii::app()->user->id;
        $row = Yii::app()->db->createCommand()->select("id,status_type,data_content")->from("sal_api_curl")
            ->where("id=:id", array(':id'=>$id))->queryRow();
        if($row){
            if($bool===false){
                if($row["status_type"]=="C"){
                    Dialog::message(Yii::t('dialog','Validation Message'), "该消息已执行成功，不推荐再次发送");
                    return false;
                }
            }
            Yii::app()->db->createCommand()->update("sal_api_curl",array(
                "status_type"=>"P",
                "message"=>null,
                "lcu"=>$uid,
            ),"id=:id",array(':id'=>$row["id"]));
        }
    }

    public function EndData($id){
        $row = Yii::app()->db->createCommand()->select("*")->from("sal_api_curl")
            ->where("id=:id", array(':id'=>$id))->queryRow();
        if($row){
            switch ($row['info_type']) {
                case "client"://客户同步
                    $model = new CurlNotesByClient();
                    $model->outData = json_decode($row["out_content"],true);
                    $model->status_type=$row["status_type"];
                    $model->endData();
                    break;
                case "clientPerson"://客户联系人同步
                    $model = new CurlNotesByClient();
                    $model->outData = json_decode($row["out_content"],true);
                    $model->status_type=$row["status_type"];
                    $model->endData();
                    break;
                case "clientArea"://客户归属区域同步
                    $model = new CurlNotesByClient();
                    $model->outData = json_decode($row["out_content"],true);
                    $model->status_type=$row["status_type"];
                    $model->endData();
                    break;
                case "clientStaff"://客户负责人同步
                    $model = new CurlNotesByClient();
                    $model->outData = json_decode($row["out_content"],true);
                    $model->status_type=$row["status_type"];
                    $model->endData();
                    break;
                case "store"://门店同步
                    $model = new CurlNotesByStore();
                    $model->outData = json_decode($row["out_content"],true);
                    $model->status_type=$row["status_type"];
                    $model->endData();
                    break;
                case "storePerson"://门店负责人同步
                    $model = new CurlNotesByStore();
                    $model->outData = json_decode($row["out_content"],true);
                    $model->status_type=$row["status_type"];
                    $model->endData();
                    break;
                case "contVir"://虚拟合约同步
                    $model = new CurlNotesByVir();
                    $model->outData = json_decode($row["out_content"],true);
                    $model->status_type=$row["status_type"];
                    $model->endData();
                    break;
                default:
                    $model = new CurlNotesModel();
                    $model->status_type="E";
            }
            if($model->status_type!="C"){
                Dialog::message(Yii::t('dialog','Validation Message'), "该消息未成功，无法执行");
            }else{
                Dialog::message(Yii::t('dialog','Validation Message'), "已执行");
            }
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'), "消息不存在");
        }
    }
}
