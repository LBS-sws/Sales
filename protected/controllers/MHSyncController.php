<?php
//门户请求的接口
class MHSyncController extends MHController
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'enforceValidConnection',
		);
	}

    //
    public function actionAuditClue(){
        $data = $this->_getdata();
        if(!empty($data)&&is_array($data)){
            $businesskey = isset($data['businesskey'])?$data['businesskey']:"";
            $typeList = explode("_",$businesskey);
            if(count($typeList)==2) {
                $model = new ApiCurl();
                $ip = Yii::app()->request->userHostAddress;
                $lcd = date("Y-m-d H:i:s");
                $typeStr = $typeList[0];
                $result=$this->saveByType($typeStr,$data);
                $rtn = $model->addrecordForMH($ip,$typeStr, $data,$lcd,$result,"C");
                echo json_encode($rtn);
            }else{
                echo json_encode(array('code'=>400,'msg'=>"businesskey error:{$businesskey}"));
            }
        }else{
            echo json_encode(array('code'=>400,'msg'=>"data is null"));
        }
    }

    protected function saveByType($type,$data){
        switch ($type) {
            case "cont":
                $model = new ClueContModel();
                $list = $model->syncChangeOne($data);
                break;
            case "rpt":
                $model = new ClueRptModel();
                $list = $model->syncChangeOne($data);
                break;
            case "pro":
                $model = new ClueProModel();
                $list = $model->syncChangeOne($data);
                break;
            case "virPro":
                $model = new ClueVirProModel();
                $list = $model->syncChangeOne($data);
                break;
            case "call":
                $model = new ClueCallModel();
                $list = $model->syncChangeOne($data);
                break;
            case "setFree":
                $model = new ContVirFreeModel();
                $list = $model->syncChangeOne($data);
                break;
            case "setPerPWD":
                $model = new CluePersonModel();
                $list = $model->syncChangeOne($data);
                break;
            default:
                $list=array("code"=>400,'msg'=>"info_type error:{$type}");
        }
        return $list;
    }
}

?>
