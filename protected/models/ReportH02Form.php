<?php
/* Reimbursement Form */

class ReportH02Form extends CReportForm
{
	public $staffs;
	public $staffs_desc;
	
	protected function labelsEx() {
		return array(
				'staffs'=>Yii::t('report','Staffs'),
            'start_dt'=>Yii::t('report','Start_Dt'),
            'staffs_desc'=>Yii::t('report','Staffs_Desc'),
            'five'=>Yii::t('report','Fivejieduan'),
			);
	}
	
	protected function rulesEx() {
        return array(
            array('staffs, staffs_desc','safe'),
        );
	}
	
	protected function queueItemEx() {
		return array(
				'STAFFS'=>$this->staffs,
				'STAFFSDESC'=>$this->staffs_desc,
			);
	}
	
	public function init() {
		$this->id = 'RptFive';
		$this->name = Yii::t('app','Five Steps');
		$this->format = 'EXCEL';
		$this->city = Yii::app()->user->city();
		$this->fields = 'start_dt,end_dt,staffs,staffs_desc';
		$this->start_dt = date("Y/m/d");
        $this->end_dt = date("Y/m/d");
		$this->five = "";
        $this->staffs = '';
        $this->sort = '';
        $this->gangwei = '';
		$this->staffs_desc = Yii::t('misc','All');
	}

}
