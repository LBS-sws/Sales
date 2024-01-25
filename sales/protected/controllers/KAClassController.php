<?php

class KAClassController extends Controller
{
    public $function_id='KA10';

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
                'actions'=>array('new','edit','delete','save'),
                'expression'=>array('KAClassController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view'),
                'expression'=>array('KAClassController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex($pageNum=0)
    {
        $model = new KAClassList;
        if (isset($_POST['KAClassList'])) {
            $model->attributes = $_POST['KAClassList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['kAClass_c01']) && !empty($session['kAClass_c01'])) {
                $criteria = $session['kAClass_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }


    public function actionSave()
    {
        if (isset($_POST['KAClassForm'])) {
            $model = new KAClassForm($_POST['KAClassForm']['scenario']);
            $model->attributes = $_POST['KAClassForm'];
            if ($model->validate()) {
                $model->saveData();
//				$model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('kAClass/edit',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }

    public function actionView($index)
    {
        $model = new KAClassForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionNew()
    {
        $model = new KAClassForm('new');
        $this->render('form',array('model'=>$model,));
    }

    public function actionEdit($index)
    {
        $model = new KAClassForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionDelete()
    {
        $model = new KAClassForm('delete');
        if (isset($_POST['KAClassForm'])) {
            $model->attributes = $_POST['KAClassForm'];
            if ($model->isOccupied($model->id)) {
                Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
                $this->redirect(Yii::app()->createUrl('kAClass/edit',array('index'=>$model->id)));
            } else {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('kAClass/index'));
            }
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('KA10');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('KA10');
    }
}
