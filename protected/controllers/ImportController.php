<?php
class ImportController extends Controller
{
	public $function_id='XF02';

	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
		);
	}

	public function accessRules() {
		return array(
			array('allow', 
				'actions'=>array('submit','clue','index','downExcel'),
				'expression'=>array('ImportController','allowExecute'),
			),
			array('allow',
				'actions'=>array('clueSubmit'),
				'expression'=>array('ImportController','allowClue'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
    public function actionDownExcel($type,$code='') {
        $handleBool=true;
        $path = Yii::app()->basePath.'/commands/template/';
        header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"); //for pdf or excel file
        switch ($type){
			case "clueBox":
                $file = $path."clueBox.xlsx";
                $filename= iconv('utf-8','gbk//ignore',"线索池导入客户模板.xlsx");
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                break;
			case "clue":
                $file = $path."clue.xlsx";
                $filename= iconv('utf-8','gbk//ignore',"线索导入客户模板.xlsx");
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                break;
			case "clueStore":
                $file = $path."clueStore.xlsx";
                $filename= iconv('utf-8','gbk//ignore',"导入门店模板.xlsx");
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                if(!empty($code)){
                    $handleBool=false;
                    Yii::$enableIncludePath = false;
                    $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
                    spl_autoload_unregister(array('YiiBase','autoload'));
                    include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
                    $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
                    $objPHPExcel = $objReader->load($file);
                    $objPHPExcel->getActiveSheet()->setCellValue('A3', "线索编号：".$code) ;

                    //輸出excel
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                    ob_start();
                    $objWriter->save('php://output');
                    $output = ob_get_clean();
                    spl_autoload_register(array('YiiBase','autoload'));
                    echo $output;
                }
                break;
			case "clientStore":
                $file = $path."clientStore.xlsx";
                $filename= iconv('utf-8','gbk//ignore',"导入派单门店模板.xlsx");
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                break;
			case "vir":
                $file = $path."clientVir.xlsx";
                $filename= iconv('utf-8','gbk//ignore',"导入派单虚拟合约模板.xlsx");
                header('Content-Disposition: attachment; filename="'.$filename.'"');
				break;
			case "cont":
                $file = $path."clientCont.xlsx";
                $filename= iconv('utf-8','gbk//ignore',"导入派单主合约模板.xlsx");
                header('Content-Disposition: attachment; filename="'.$filename.'"');
				break;
			default:
                $file = $path."client.xlsx";
                $filename= iconv('utf-8','gbk//ignore',"导入派单客户模板.xlsx");
                header('Content-Disposition: attachment; filename="'.$filename.'"');
		}
		if($handleBool){
            $handle = fopen($file,'r');
            fpassthru($handle);
            fclose($handle);
		}
        die();
    }

	public function actionClue() {
        $session = Yii::app()->session;
        $session["menu_code"]="XF03";
        $session["active_func"]="XF03";
        $this->function_id = "XF03";
		$model = new ImportForm();
		$this->render('form_clue',array('model'=>$model,));
	}

    public function actionClueSubmit($type='') {
        $model = new ImportForm();
        if (isset($_POST['ImportForm'])) {
            $model->attributes = $_POST['ImportForm'];
            if ($model->validate()) {
                if ($file = CUploadedFile::getInstance($model,'import_file')) {
                    $model->setFilePath($file);
                    $model->saveImport();
                    Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Job submitted. Please go to Import Manager to retrieve the result.'));
                } else {
                    $message = Yii::t('import','Upload file error');
                    Dialog::message(Yii::t('dialog','Error Message'), $message);
                }
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
            }
        }
        //Yii::app()->request->urlReferrer
		if(empty($type)){
            $this->render('form_clue',array('model'=>$model,));
		}else{
			if($model->hasErrors()){
                $this->redirect(Yii::app()->request->urlReferrer);
			}else{
                $this->redirect(Yii::app()->createUrl("iqueue/index"));
			}
		}
    }

	public function actionIndex() {
		$model = new ImportForm();
		$this->render('form',array('model'=>$model,));
	}

	public function actionSubmit() {
		$model = new ImportForm();
		if (isset($_POST['ImportForm'])) {
			$model->attributes = $_POST['ImportForm'];
			if ($model->validate()) {
				if ($file = CUploadedFile::getInstance($model,'import_file')) {
					$model->setFilePath($file);
					$model->saveImport();
					Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Job submitted. Please go to Import Manager to retrieve the result.'));
				} else {
					$message = Yii::t('import','Upload file error');
					Dialog::message(Yii::t('dialog','Error Message'), $message);
				}		
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
			}
		}
		$this->render('form',array('model'=>$model,));
	}
	
	public static function allowExecute() {
		return Yii::app()->user->validFunction('XF02')||Yii::app()->user->validFunction('XF03');
	}

	public static function allowClue() {
		return Yii::app()->user->validFunction('CM01')||Yii::app()->user->validFunction('CM02')||Yii::app()->user->validFunction('CM04')||Yii::app()->user->validFunction('XF03');
	}
}
?>
