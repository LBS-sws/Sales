<?php

return array(
	'drs'=>array(
		'webroot'=>'http://192.168.0.106/dr-new',
		'name'=>'Daily Report',
		'icon'=>'fa fa-pencil-square-o',
	),
	'acct'=>array(
		'webroot'=>'http://192.168.0.106/ac-new',
		'name'=>'Accounting',
		'icon'=>'fa fa-money',
	),
//	'ops'=>array(
//		'webroot'=>'http://192.168.0.106/op-new',
//		'name'=>'Operation',
//		'icon'=>'fa fa-gears',
//	),
	'hr'=>array(
		'webroot'=>'http://192.168.0.106/hr-new',
		'name'=>'Personnel',
		'icon'=>'fa fa-users',
	),
	'sal'=>array(
		'webroot'=>'http://192.168.0.106/sa-new',
		'name'=>'Sales',
		'icon'=>'fa fa-suitcase',
	),
	'nu'=>array(
		'webroot'=>'https://dms.lbsapps.cn/nu',
		'name'=>'New United',
		'icon'=>'fa fa-suitcase',
		'param'=>'/admin',
		'script'=>'goNewUnited',
	),
//	'quiz'=>array(
//		'webroot'=>'http://192.168.0.106/qz-new',
//		'name'=>'Quiz',
//		'icon'=>'fa fa-pencil',
//	),
//	'sp'=>array(
//		'webroot'=>'http://192.168.0.106/sp-new',
//		'name'=>'Academic Credit',
//		'icon'=>'fa fa-cube',
//	),
//	'onlib'=>array(
//		'webroot'=>'https://onlib.lbsapps.com/seeddms',
//		'script'=>'remoteLoginOnlib',
//		'name'=>'Online Library',
//		'icon'=>'fa fa-book',
//		'external'=>array(
//				'layout'=>'onlib',
//				'update'=>'saveOnlib',		//function defined in UserFormEx.php
//				'fields'=>'fieldsOnlib',
//			),
//	),
/*
	'apps'=>array(
		'webroot'=>'https://app.lbsgroup.com.tw/web',
		'script'=>'remoteLoginTwApp',
		'name'=>'Apps System',
		'icon'=>'fa fa-rocket',
		'external'=>true,
	),
*/
);

?>
