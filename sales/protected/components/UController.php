<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class UController extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public function filterEnforceValidConnection($filterChain) {
		$auth = new Authentication();
		$rtn = ($auth->verifyUServerByCRM());
		if ($rtn)
			$filterChain->run();
		else {
            echo json_encode(array('code'=>400,'msg'=>"error token!"));
		    die();
		}
	}

	public function _getdata() {
		return json_decode(file_get_contents('php://input'), true);
	}

	public function _getDataTwo() {
	    $out = file_get_contents('php://input');
        $out = htmlspecialchars_decode($out);
		return json_decode($out, true);
	}
}
