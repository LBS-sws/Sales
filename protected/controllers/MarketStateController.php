<?php

class MarketStateController extends Controller 
{
	public $function_id='MT06';

	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
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
				'actions'=>array('new','edit','delete','save'),
				'expression'=>array('MarketStateController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','test'),
				'expression'=>array('MarketStateController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionTest()
	{
	    $jsonStr ='{"CustomerID":"JATXM154-WH","NameZH":"泰香米武汉壹方天地店","CustomerType":"125","Status":"1","NameEN":"","NameShop":"泰香米武汉壹方天地店","NameOB":"","NameBill":"上海萃泰餐厅管理有限公司","Addr":"武汉市江岸区中山大道1515号武汉天地壹方南馆2F-38号商铺","AddrBill":"","GroupID":"","AddrRemarks":"","AddrBiRemarks":"","InvRemarks":"","Area":"","City":"WH","District":"17002","Street":"","lng":"114.31618051962121","lat":"30.614857556079023","Tel":"","Email":"","Fax":"JATXM154","SalesRep":"400851","Remarks":"付款方式：半年预付，420元\/月\/3次，560\/月\/4次，每月常规服务3次\/月，根据店内要求可服务4次\/月。\n服务内容：鼠，蟑螂，果蝇。","action":"add","GroupName":"","cont_name":""} ';


        $json = json_decode($jsonStr,true);
        var_dump($json);
        echo "<br/>";
        echo "<br/>";
        $y= $this->rectify_json($jsonStr);
        var_dump($y);
        echo "<br/>";
        echo "<br/>";
        $x = preg_replace('/[\x00-\x1F\x7F]/u','',$y);
        var_dump($x);
        echo "<br/>";
        echo "<br/>";
        $rec = json_decode($x,true);
        if ($rec===NULL) {
            $x = preg_replace('/[\x00-\x1F\x80-\xFF]/','',$x);
            $rec = json_decode($x,true);
        }
        var_dump($rec);
	}

	public function rectify_json($str) {
        $rtn = '';
        $chr = $this->str_split_unicode($str);
        $len = mb_strlen($str, "UTF-8");
        for ($i=0; $i<$len; $i++) {
            $flag = $chr[$i]!='"' || mb_strpos('{[,:',$chr[$i-1])!==false || mb_strpos('}],:',$chr[$i+1])!==false;
            if ($flag) $rtn .= $chr[$i];
        }
        return $rtn;
    }

    public function str_split_unicode($str, $length = 1) {
        $tmp = preg_split('~~u', $str, -1, PREG_SPLIT_NO_EMPTY);
        if ($length > 1) {
            $chunks = array_chunk($tmp, $length);
            foreach ($chunks as $i => $chunk) {
                $chunks[$i] = join('', (array) $chunk);
            }
            $tmp = $chunks;
        }
        return $tmp;
    }

	public function actionIndex($pageNum=0)
	{
		$model = new MarketStateList;
		if (isset($_POST['MarketStateList'])) {
			$model->attributes = $_POST['MarketStateList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['marketState_c01']) && !empty($session['marketState_c01'])) {
				$criteria = $session['marketState_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}


	public function actionSave()
	{
		if (isset($_POST['MarketStateForm'])) {
			$model = new MarketStateForm($_POST['MarketStateForm']['scenario']);
			$model->attributes = $_POST['MarketStateForm'];
			if ($model->validate()) {
				$model->saveData();
//				$model->scenario = 'edit';
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('marketState/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new MarketStateForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew()
	{
		$model = new MarketStateForm('new');
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new MarketStateForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new MarketStateForm('delete');
		if (isset($_POST['MarketStateForm'])) {
			$model->attributes = $_POST['MarketStateForm'];
			if ($model->isOccupied($model->id)) {
				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
				$this->redirect(Yii::app()->createUrl('marketState/edit',array('index'=>$model->id)));
			} else {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
		$this->redirect(Yii::app()->createUrl('marketState/index'));
			}
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('MT06');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('MT06');
	}
}
