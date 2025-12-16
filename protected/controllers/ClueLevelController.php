<?php

/**
 * 客户等级管理控制器
 * 
 * 处理客户等级的CURD操作
 * 权限码: HC22
 * 
 * @package controllers
 * @author 王耽误
 */
class ClueLevelController extends Controller
{
    public $function_id = 'HC22';

    public function filters()
    {
        return array(
            'enforceRegisteredStation',
            'enforceSessionExpiration',
            'enforceNoConcurrentLogin',
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions' => array('index', 'new', 'edit', 'view', 'save', 'delete'),
                'expression' => array('ClueLevelController', 'allowCRUD'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex($pageNum = 0)
    {
        $model = new ClueLevelList();
        if (isset($_POST['ClueLevelList'])) {
            $model->attributes = $_POST['ClueLevelList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['criteria_hc22']) && !empty($session['criteria_hc22'])) {
                $criteria = $session['criteria_hc22'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index', array('model' => $model));
    }

    public function actionNew()
    {
        $model = new ClueLevelForm('new');
        $this->render('form', array('model' => $model));
    }

    public function actionEdit($index)
    {
        $model = new ClueLevelForm('edit');
        if ($model->retrieveData($index)) {
            $this->render('form', array('model' => $model));
        } else {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
    }

    public function actionView($index)
    {
        $model = new ClueLevelForm('view');
        if ($model->retrieveData($index)) {
            $this->render('form', array('model' => $model));
        } else {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
    }

    public function actionSave()
    {
        if (Yii::app()->request->isPostRequest && isset($_POST['ClueLevelForm'])) {
            $model = new ClueLevelForm($_POST['ClueLevelForm']['scenario']);
            $model->attributes = $_POST['ClueLevelForm'];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog', 'Information'), '保存成功');
                $this->redirect(Yii::app()->createUrl('clueLevel/index'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog', 'Error'), $message);
            }
        } else {
            throw new CHttpException(400, 'Bad Request');
        }
    }

    public function actionDelete()
    {
        $model = new ClueLevelForm('delete');
        if (Yii::app()->request->isPostRequest && isset($_POST['ClueLevelForm'])) {
            $model->attributes = $_POST['ClueLevelForm'];
            $model->deleteData();
            Dialog::message(Yii::t('dialog', 'Information'), '删除成功');
            $this->redirect(Yii::app()->createUrl('clueLevel/index'));
        } else {
            throw new CHttpException(400, 'Bad Request');
        }
    }

    /**
     * 检查是否有HC22权限 (客户等级管理)
     * @return bool
     */
    public static function allowCRUD()
    {
        return Yii::app()->user->validRWFunction('HC22');
    }
}
