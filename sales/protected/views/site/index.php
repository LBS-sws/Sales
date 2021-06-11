<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>

<?php
// For download file use.
if (isset($url) && !empty($url)) {
	$self = Yii::app()->request->getHostInfo().Yii::app()->getBaseUrl();
	if (strpos($url,$self)!==false) {
		$js = "$(location).attr('href','$url');";
		Yii::app()->clientScript->registerScript('redirection',$js,CClientScript::POS_READY);
	}
}
?>

<?php
if (!isset($url) || empty($url)) {
	$this->widget('ext.layout.DashboardWidget',
		array('config'=>Yii::app()->basePath.'/config/dashboard.php',
		)
	);
}
?>

<?php
if (!isset($url) || empty($url)){
    $this->widget('ext.layout.AnnounceWidget');
    if (Yii::app()->params['showRankNotice']=='on')$this->widget('ext.layout.RankNoticeWidget');
	if (Yii::app()->params['showRank']=='on') $this->widget('ext.layout.RankiconWidget');


}
?>


