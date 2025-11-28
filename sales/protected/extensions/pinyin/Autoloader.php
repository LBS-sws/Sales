<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2025/9/15 0015
 * Time: 10:50
 */
$phpExcelPath = Yii::getPathOfAlias('ext.pinyin');
include($phpExcelPath . DIRECTORY_SEPARATOR . 'const.php');
include($phpExcelPath . DIRECTORY_SEPARATOR . 'DictLoaderInterface.php');
include($phpExcelPath . DIRECTORY_SEPARATOR . 'FileDictLoader.php');
include($phpExcelPath . DIRECTORY_SEPARATOR . 'Pinyin.php');