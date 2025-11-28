<?php
// For Restful API Authentication
class Authentication {
	protected $app_key = array();

	protected $svr_key = array();

	public function __construct() {
        $svr_file = Yii::app()->basePath.'/config/svrkey.php';
        $this->svr_key = require($svr_file);
	}

	public function verifyMHServer() {//门户系统的专属验证
		$headers = apache_request_headers();
		$authorization = $this->getAuthorizationString($headers);
		if (!empty($authorization) && strpos($authorization,'MHKey')!==false) {
			$key = trim(str_replace('MHKey','',$authorization));
			$ip = Yii::app()->request->userHostAddress;

            $svr_file = Yii::app()->basePath.'/config/trueIP.php';
            $trueIPList= require($svr_file);
			return isset($this->svr_key[$key])&&in_array($ip,$trueIPList);
		} else {
			return false;
		}
	}

	public function verifyUServerByCRM() {//CRM的验证
		$headers = apache_request_headers();
        $authorization = $this->getTokenString($headers);
		if (!empty($authorization)) {
			$key = trim($authorization);
            $u_key =Yii::app()->params['uCRMKey'];
            $token = md5(md5($u_key.date('Y-m-d',time()).'0000'));
			return $token==$key;
		} else {
			return false;
		}
	}
	
	protected function getAuthorizationString($headers) {
		return isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : '');
	}

	protected function getTokenString($headers) {
		return isset($headers['token']) ? $headers['token'] : (isset($headers['Token']) ? $headers['Token'] : '');
	}
	
	protected function basicAuthenticate() {
		return isset($_SERVER['PHP_AUTH_USER']) 
			&& isset($this->app_key[$_SERVER['PHP_AUTH_USER']])
			&& (($this->app_key[$_SERVER['PHP_AUTH_USER']]==$_SERVER['PHP_AUTH_PW'])
			|| ($this->app_key[$_SERVER['PHP_AUTH_USER']]."\n"==$_SERVER['PHP_AUTH_PW']));
	}
	
	protected function hashString($inVal) {
		$salt = 'lBs876234dMS';
		$now = getdate();
		$str = $inVal.$now[0].$salt;
		return md5($str);
	}
}
?>
