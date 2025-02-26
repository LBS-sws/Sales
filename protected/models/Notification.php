<?php
class Notification extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'sal_push_message';
	}
	
	public function getNewMessageByTime($hours) {
		$time = date('Y-m-d H:i:s', strtotime('-'.$hours.' hours'));
		$rows = $this->findAll(array(
								"condition"=>"lcd >= '$time' and status <> 'P' and msg_type='SALORDER'",
								"order"=>"id desc",
								));
		$msgfield = Yii::app()->language=='en' ? 'message_en' : (Yii::app()->language=='zh_tw' ? 'message_tw' : 'message_cn');
		$rtn = array();
		foreach ($rows as $row) {
			$rtn[] = array('id'=>$row['id'], 'lcd'=>$row['lcd'], 'message'=>$row[$msgfield]);
		}
		return $rtn;
	}
	
	public function getNewMessageById($lastid) {
		$rows = $this->findAll(array(
								"condition"=>"id > $lastid and status <> 'P' and msg_type='SALORDER'",
								"order"=>"id desc",
								));
		$msgfield = Yii::app()->language=='en' ? 'message_en' : (Yii::app()->language=='zh_tw' ? 'message_tw' : 'message_cn');
		$rtn = array();
		foreach ($rows as $row) {
			$rtn[] = array('id'=>$row['id'], 'lcd'=>$row['lcd'], 'message'=>$row[$msgfield]);
		}
		return $rtn;
	}
}
