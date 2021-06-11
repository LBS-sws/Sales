<?php

class RgapplyController extends Controller
{
    public $function_id='HE02';

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
                'actions'=>array('new','edit','delete','save','downs','test','apply','audit'),
                'expression'=>array('RgapplyController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view'),
                'expression'=>array('RgapplyController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex($pageNum=0){
       // $this->render('index');
        if(RedeemgiftApply::validateNowUser()){
            $model = new RedeemgiftApply();
            if (isset($_POST['GiftApplyeList'])) {
                $model->attributes = $_POST['GiftApplyList'];
            } else {
                $session = Yii::app()->session;
                if (isset($session['giftRequest_01']) && !empty($session['giftRequest_01'])) {
                    $criteria = $session['giftRequest_01'];
                    $model->setCriteria($criteria);
                }
            }
            if (isset($_POST['RedeemgiftApply'])){
                $model->attributes = $_POST['RedeemgiftApply'];
            }
            $model->determinePageNum($pageNum);
            $model->retrieveDataByPage($model->pageNum);
            $list = RedeemGifts::getNowIntegral();

//            var_dump($model);die();
            $this->render('index',array('model'=>$model,'cutIntegral'=>$list));
        }else{
            throw new CHttpException(404,'您的账号未绑定员工，请与管理员联系');
        }
    }

    public function actionEdit($index)
    {
        $model = new RedeemgiftApply('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model));
        }
    }
    public function actionAudit()
    {
        if (isset($_POST['RedeemgiftApply'])) {
            $model = new RedeemgiftApply("audit");
            $model->attributes = $_POST['RedeemgiftApply'];
            if ($model->validateNowUser()) {
                $model->status = $_GET['id'];
                if ($_GET['id']==2){
                    Yii::app()->db->createCommand("update sal_redeem_score set score=score+".$_POST['RedeemgiftApply']['bonus_point']*$_POST['RedeemgiftApply']['apply_num']." where employee_id=".$_POST['RedeemgiftApply']['employee_id'])->execute();
                }
                Yii::app()->db->createCommand()->update('sal_redeem_gift_apply', array('status' => $_GET['id']), 'id=:id', array(':id' => $_POST['RedeemgiftApply']['id']));

                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('rgapply/index'));
            } else {
                $model->state = 0;
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
            }
        }
    }




    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('HE02');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('HE02');
    }
}
