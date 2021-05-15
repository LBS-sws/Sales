<?php

class RedeemController extends Controller
{
    public $function_id='HE01';

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
                'actions'=>array('new','edit','delete','save','downs','test','apply'),
                'expression'=>array('RedeemController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view'),
                'expression'=>array('RedeemController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

//    public function actionIndex($pageNum=0){
//        $this->render('index');
//        if(GiftRequestForm::validateNowUser()){
//            $model = new GiftRequestList;
//            if (isset($_POST['GiftRequestList'])) {
//                $model->attributes = $_POST['GiftRequestList'];
//            } else {
//                $session = Yii::app()->session;
//                if (isset($session['giftRequest_01']) && !empty($session['giftRequest_01'])) {
//                    $criteria = $session['giftRequest_01'];
//                    $model->setCriteria($criteria);
//                }
//            }
//            $model->determinePageNum($pageNum);
//            $model->retrieveDataByPage($model->pageNum);
//            $listArrIntegral = GiftList::getNowIntegral();
//            $this->render('index',array('model'=>$model,'cutIntegral'=>$listArrIntegral));
//        }else{
//            throw new CHttpException(404,'您的账号未绑定员工，请与管理员联系');
//        }
//    }
    public function actionIndex($pageNum=0)
    {
        $model = new RedeemGifts();
        if (isset($_POST['GiftList'])) {
            $model->attributes = $_POST['GiftList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['gift_op01']) && !empty($session['gift_op01'])) {
                $criteria = $session['gift_op01'];
                $model->setCriteria($criteria);
            }
        }
        if (isset($_POST['RedeemGifts'])){
            $model->attributes = $_POST['RedeemGifts'];
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        /*        $cutIntegral = IntegralCutView::getNowIntegral();
                $this->render('index',array('model'=>$model,'cutIntegral'=>$cutIntegral));*/
        //var_dump($model);die();
        $this->render('index',array('model'=>$model));
    }

    public function actionNew()
    {
        $model = new RedeemGifts('new');
        $this->render('form',array('model'=>$model,));
    }
    public function actionApply(){
        $model = new RedeemgiftApply("apply");
        if($model->validateNowUser(true)){
            if (isset($_POST['GiftApply'])) {

                $model->attributes = $_POST['GiftApply'];
                if ($model->validate()) {
                    //礼物信息
                    $city = Yii::app()->user->city();
                    $sqlg = "SELECT * FROM sal_redeem_gifts WHERE gift_name='".$_POST['GiftApply']['gift_name']."' and bonus_point=".$_POST['GiftApply']['bonus_point']." and city='".$city."'";
                    $gift = Yii::app()->db->createCommand($sqlg)->queryRow();
                    //判断积分和库存
                    $listArrIntegral = RedeemGifts::getNowIntegral();
                    $sy_score = $listArrIntegral['cut']-$_POST['GiftApply']['bonus_point']*$_POST['GiftApply']['apply_num'];
                    $sy_inventory =$gift['inventory']-$_POST['GiftApply']['apply_num'];
                    if ($sy_score < 0){
                        Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Score No'));
                    }else if($sy_inventory<0){
                        Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Inventory No'));
                    }else{
                        $model->saveData();
                        Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                    }

                    $this->redirect(Yii::app()->createUrl('redeem/index'));
                } else {
                    $message = CHtml::errorSummary($model);
                    Dialog::message(Yii::t('dialog','Validation Message'), $message);
                    $this->redirect(Yii::app()->createUrl('redeem/index'));
                }
            }
        }else{
            throw new CHttpException(404,'您的账号未绑定员工，请与管理员联系');
        }
    }
    public function actionEdit($index)
    {
        $model = new RedeemGifts('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            // print_r($model);exit();
            $this->render('form',array('model'=>$model));
        }
    }
    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('HE01');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('HE01');
    }
}
