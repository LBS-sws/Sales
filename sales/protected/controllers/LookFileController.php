<?php

class LookFileController extends Controller
{
    public $function_id='CT01';

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
                'actions'=>array(),
                'expression'=>array('LookFileController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('show','down'),
                'expression'=>array('LookFileController','allowReadAll'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionShow($index,$tableName='')
    {
        $this->layout = false;
        switch ($tableName){
            case "cont":
                $model = new ContHeadForm('edit');
                break;
            case "rpt":
                $model = new ClueRptForm('edit');
                break;
            case "pro":
                $model = new ContProForm('edit');
                break;
            case "clue":
                $model = new ClueForm('edit');
                break;
            default:
                throw new CHttpException(404,'The requested page does not exist.');

        }
        $model->getModelIDByFileID($index);
        if (!$model->retrieveData($model->id)) {
            throw new CHttpException(404,'The requested page does not exist.'.$model->id);
        } else {
            $lookFileModel = new LookFileForm('view');
            $lookFileModel->lookFileRow = $model->lookFileRow;
            //$lookUrl ="https://dms.lbsapps.cn:8441";
            //https://dms.lbsapps.cn:8441/upload/sal/uat/rpt_uat/10/1fff6984bfc00edd514db164fea9814f.png
            $lookUrl = Yii::app()->params['fileLookUrl'];
            $path = $lookFileModel->lookFileRow["phy_path_name"]."/".$lookFileModel->lookFileRow["phy_file_name"];
            $url = "https://files.lbsapps.cn/".$path;
            $queryArr = array("url"=>base64_encode($url));
            $fileUrl = $lookUrl."/onlinePreview?".http_build_query($queryArr);
            $this->redirect($fileUrl);
            /*
            $list=$lookFileModel->lookFile();
            $this->render($list['file'],array('list'=>$list,));
            */
        }
    }

    public function actionDown($index,$tableName='')
    {
        $this->layout = false;
        switch ($tableName){
            case "cont":
                $model = new ContHeadForm('edit');
                break;
            case "rpt":
                $model = new ClueRptForm('edit');
                break;
            case "pro":
                $model = new ContProForm('edit');
                break;
            case "clue":
                $model = new ClueForm('edit');
                break;
            default:
                throw new CHttpException(404,'The requested page does not exist.');

        }
        $model->getModelIDByFileID($index);
        if (!$model->retrieveData($model->id)) {
            throw new CHttpException(404,'The requested page does not exist.'.$model->id);
        } else {
            $fileExt = $model->lookFileRow["phy_file_name"];
            $fileExt = explode(".",$fileExt);
            $fileExt =".".end($fileExt);
            $fileName = $model->lookFileRow["file_name"];
            $fileName = strpos($fileName,$fileExt)!==false?$fileName:($fileName.$fileExt);
            $fileName= iconv('utf-8','gbk//ignore',$fileName);
            $file_path = $model->lookFileRow["phy_path_name"]."/".$model->lookFileRow["phy_file_name"];
            //$fileUrl = "https://lbs-file.lbsapps.cn/".$file_path."?attname=".$fileName;
            $fileUrl = "https://files.lbsapps.cn/".$file_path."?attname=".$fileName;
            $this->redirect($fileUrl);
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('CT01')||Yii::app()->user->validRWFunction('CT02')||Yii::app()->user->validRWFunction('CM10')||Yii::app()->user->validRWFunction('CM02');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('CM02');
    }
    public static function allowReadAll() {
        return !Yii::app()->user->isGuest;
    }
}
