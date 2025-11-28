<?php

class QiNiuFile {
	protected $objPHPExcel;
    public $bucket = "lbsgroup-kudo";
    public $key1 = "file_name1";
	public function start() {
		$phpExcelPath = Yii::getPathOfAlias('ext.qiniu');
		spl_autoload_unregister(array('YiiBase','autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'io.php');
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'rs.php');
	}
	
	public function end() {
		spl_autoload_register(array('YiiBase','autoload'));
	}

	public function uploadFile($key,$file){
        //$bucket = 'rwxf';
        //$key = 'up.php';
        $putPolicy = new Qiniu_RS_PutPolicy($this->bucket);
        //$putPolicy->CallbackUrl = 'https://10fd05306325.a.passageway.io';
        //$putPolicy->CallbackBody = 'key=$(key)&hash=$(etag)';
        $upToken = $putPolicy->Token(null);

        $putExtra = new Qiniu_PutExtra();
        list($ret, $err) = Qiniu_PutFile($upToken, $key, $file, $putExtra);
        if ($err !== null) {
            return false;
        } else {
            return true;
        }
    }

	public function uploadFileBody($key,$fileBody){
        //$bucket = 'rwxf';
        //$key = 'up.php';
        $putPolicy = new Qiniu_RS_PutPolicy($this->bucket);
        //$putPolicy->CallbackUrl = 'https://10fd05306325.a.passageway.io';
        //$putPolicy->CallbackBody = 'key=$(key)&hash=$(etag)';
        $upToken = $putPolicy->Token(null);

        list($ret, $err) = Qiniu_Put($upToken, $key,$fileBody, null);
        if ($err !== null) {
            return false;
        } else {
            return true;
        }
    }

    public function downFile($key){
        $domain = 'https://files.lbsapps.cn';
        //$baseUrl 就是您要访问资源的地址
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        return $baseUrl;
    }

    public function downPrivateFile($key){
        $domain = 'https://files.lbsapps.cn';
//$baseUrl 就是您要访问资源的地址
        $baseUrl = Qiniu_RS_MakeBaseUrl($domain, $key);
        $getPolicy = new Qiniu_RS_GetPolicy();
        $privateUrl = $getPolicy->MakeRequest($baseUrl, null);
        return $privateUrl;
    }

    public function removeFile($key1){
        $client = new Qiniu_MacHttpClient(null);
        $err = Qiniu_RS_Delete($client, $this->bucket, $key1);
    }

    public function addBucketUrl($url){
	    $this->bucket.=$url;
    }
}
?>