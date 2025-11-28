<?php
//2024年9月28日09:28:46

class CurlNotesController extends Controller
{
	public $function_id='ZC01';
	
	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations 1
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', 
				'actions'=>array('resetSendData'),
				'expression'=>array('CurlNotesController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','getAjaxStr','endData'),
				'expression'=>array('CurlNotesController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionEndData($index){//跳过发送，直接执行结果
        $model = new CurlNotesList();
        $model->endData($index);
        $this->redirect(Yii::app()->createUrl('curlNotes/index'));
    }

    public function actionResetSendData($index=0,$bool=false)
    {
        $model = new CurlNotesList();
        $model->resetSendData($index,$bool);
        $this->redirect(Yii::app()->createUrl('curlNotes/index'));
    }

    public function actionGetAjaxStr()
    {
        if(Yii::app()->request->isAjaxRequest) {//是否ajax请求
            $model = new CurlNotesList();
            $id = key_exists("id",$_POST)?$_POST["id"]:0;
            $type = key_exists("type",$_POST)?$_POST["type"]:0;
            $content = $model->getCurlTextForID($id,$type);
            echo CJSON::encode(array("content"=>$content));
        }else{
            $this->redirect(Yii::app()->createUrl('curlNotes/index'));
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new CurlNotesList();
		if (isset($_POST['CurlNotesList'])) {
			$model->attributes = $_POST['CurlNotesList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['opr_curlNotes_c01']) && !empty($session['opr_curlNotes_c01'])) {
				$criteria = $session['opr_curlNotes_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('ZC01');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('ZC01');
	}
}
