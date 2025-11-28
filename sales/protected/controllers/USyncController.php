<?php

class USyncController extends UController
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

    //设置服务频次
	public function actionSetServiceFree() {
		//由于客户服务(一条客户服务)数据量不大，所以直接保存
        $lcd = date("Y-m-d H:i:s");
		$data = $this->_getdata();
        if(!empty($data)&&is_array($data)){
            $ip = Yii::app()->request->userHostAddress;
            $model = new ApiCurl();
            $contVirFreeModel = new ContVirFreeModel();
            $result = $contVirFreeModel->syncChangeOne($data);
            $rtn = $model->addrecordForMH($ip,"setFree", $data,$lcd,$result,"C");
            echo json_encode($rtn);
        }else{
            echo json_encode(array('code'=>400,'msg'=>"data is null"));
        }
        die();
	}

    //设置联系人密码
	public function actionSetPerson() {
		//由于客户服务(一条客户服务)数据量不大，所以直接保存
        $lcd = date("Y-m-d H:i:s");
		$data = $this->_getdata();
        if(!empty($data)&&is_array($data)){
            $ip = Yii::app()->request->userHostAddress;
            $model = new ApiCurl();
            $cluePersonModel = new CluePersonModel();
            $result = $cluePersonModel->syncChangeOne($data);
            $rtn = $model->addrecordForMH($ip,"setPerPWD", $data,$lcd,$result,"C");
            echo json_encode($rtn);
        }else{
            echo json_encode(array('code'=>400,'msg'=>"data is null"));
        }
        die();
	}

}

?>
