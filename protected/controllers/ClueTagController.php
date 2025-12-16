<?php

/**
 * 客户标签管理控制器
 * 
 * 处理客户标签的CRUD操作
 * 支持丰丰标签定义及颜色配置
 * 权限码: HC23
 * 
 * @package controllers
 * @author 王耽误
 */
class ClueTagController extends Controller
{
    public $function_id = 'HC23';

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
                'expression' => array('ClueTagController', 'allowCRUD'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex($pageNum = 0)
    {
        $model = new ClueTagList();
        if (isset($_POST['ClueTagList'])) {
            $model->attributes = $_POST['ClueTagList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['criteria_hc23']) && !empty($session['criteria_hc23'])) {
                $criteria = $session['criteria_hc23'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index', array('model' => $model));
    }

    public function actionNew()
    {
        $model = new ClueTagForm('new');
        $this->render('form', array('model' => $model));
    }

    public function actionEdit($index)
    {
        $model = new ClueTagForm('edit');
        if ($model->retrieveData($index)) {
            $this->render('form', array('model' => $model));
        } else {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
    }

    public function actionView($index)
    {
        $model = new ClueTagForm('view');
        if ($model->retrieveData($index)) {
            $this->render('form', array('model' => $model));
        } else {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
    }

    public function actionSave()
    {
        if (Yii::app()->request->isPostRequest && isset($_POST['ClueTagForm'])) {
            $model = new ClueTagForm($_POST['ClueTagForm']['scenario']);
            $model->attributes = $_POST['ClueTagForm'];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog', 'Information'), '保存成功');
                $this->redirect(Yii::app()->createUrl('clueTag/index'));
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
        $model = new ClueTagForm('delete');
        if (Yii::app()->request->isPostRequest && isset($_POST['ClueTagForm'])) {
            $model->attributes = $_POST['ClueTagForm'];
            $model->deleteData();
            Dialog::message(Yii::t('dialog', 'Information'), '删除成功');
            $this->redirect(Yii::app()->createUrl('clueTag/index'));
        } else {
            throw new CHttpException(400, 'Bad Request');
        }
    }

    /**
     * 检查是否有HC23权限 (客户标签管理)
     * @return bool
     */
    public static function allowCRUD()
    {
        return Yii::app()->user->validRWFunction('HC23');
    }
}
