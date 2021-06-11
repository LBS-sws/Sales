<?php

class RedeemScore extends CActiveRecord
{
    /* User Fields */
    public $id;
    public $employee_id;
    public $score;

    public function tableName()
    {

        return 'sal_redeem_score';
    }
    public function attributeLabels()
    {
        return array(
            'employee_id'=>Yii::t('redeem','employee_id'),
            'score'=>Yii::t('redeem','score'),
        );
    }
    public function saveData($data)
    {
        $connection = Yii::app()->db;
        //var_dump($data);die();
        $transaction=$connection->beginTransaction();
        try {

            $this->save($data);
            $transaction->commit();
        }
        catch(Exception $e) {
            $transaction->rollback();
            throw new CHttpException(404,'Cannot update.'.$e->getMessage());
        }
    }

}
