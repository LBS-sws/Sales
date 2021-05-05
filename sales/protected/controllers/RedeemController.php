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
                    $model->saveData();
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
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
    public function actionSave()
    {
        $data = $_POST['RedeemGifts'];
        if ($data['id']>0){
            $result = Yii::app()->db->createCommand()->update('sal_redeem_gifts', array('gift_name' => $data['gift_name'],'inventory' => $data['inventory'],'bonus_point' => $data['bonus_point'],'city'=>$data['city'],'city_name'=>$data['city_name']), 'id=:id', array(':id' => $data['id']));
            $id = $data['id'];
        }else{
            $result = Yii::app()->db->createCommand()->insert('sal_redeem_gifts', array('gift_name' => $data['gift_name'],'inventory' => $data['inventory'],'bonus_point' => $data['bonus_point'],'city'=>$data['city'],'city_name'=>$data['city_name']));
            $id = Yii::app()->db->getLastInsertID();
        }
        if ($result) {
            Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
            $this->redirect(Yii::app()->createUrl('redeem/edit',array('index'=>$id)));
        } else {
            Dialog::message(Yii::t('dialog','Validation Message'), Yii::t('dialog','Save no Done'));
            $this->redirect(Yii::app()->createUrl('redeem/edit',array('index'=>$id)));
        }
    }
    public function actionDelete()
    {
        $de = Yii::app()->db->createCommand()->delete('sal_redeem_gifts', 'id=:id', array(':id' => $_POST['RedeemGifts']['id']));

        if ($de) {
            Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Submission Done'));
            $this->redirect(Yii::app()->createUrl('redeem/index'));
        } else {
            Dialog::message(Yii::t('dialog','Validation Message'), Yii::t('dialog','Save no Done'));
            $this->redirect(Yii::app()->createUrl('redeem/index'));
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
    public function actionTest(){
        $start_time = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $end_time = date('Y-m-31 23:59:59', strtotime('-1 month'));
        $sql="select a.*,b.* from sal_rank a
              left join  sal_rankday b on  a.id=b.rank_id
              where a.month>= '".$start_time."' and a.month<='".$end_time."'";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();

        //用户排行榜积分累加
        for ($i=0;$i<sizeof($rows);$i++){
            if($rows[$i]['employee_id']){
                $employee_id = $rows[$i]['employee_id'];
                //计算上个赛季得分
                $now_score = $rows[$i]['now_score'];
                $add_score = 0;
                //获取所有等级
                $sql1 = 'select * from sal_level';
                $level_list = Yii::app()->db->createCommand($sql1)->queryAll();
               for ($j=0;$j<sizeof($level_list);$j++){
                   if ($level_list[$j]['start_fraction']>=$now_score && $now_score<=$level_list[$j]['end_fraction']){
                       $add_score = $level_list[$j]['reward'];
                   }
               }
               if($add_score>0){
                   $sql2 = 'select * from sal_redeem_score where employee_id ='.$employee_id;
                   $al=Yii::app()->db->createCommand($sql2)->queryRow();
                   if ($al){
                       $add_score = $add_score+$al['score'];
                       $up = Yii::app()->db->createCommand()->update('sal_redeem_score', array('score' => $add_score), 'id=:id', array(':id' => $al['id']));
                   }else{
                       $add =Yii::app()->db->createCommand()->insert('sal_redeem_score', array('score' => $add_score,'employee_id' => $employee_id));
                   }

               }

            }

        }
    }



    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('HE01');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('HE01');
    }
}
