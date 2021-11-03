<?php

class RankinglistController extends Controller
{
    public $function_id='HA05';

    protected static $actions = array(
        'index'=>'HA05',
//        'overtimelist'=>'YB02',
//        'pennantexlist'=>'YB05',
//        'pennantculist'=>'YB06',
//        'leavelist'=>'YB03',
    );
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
            /*
                        array('allow', // allow authenticated user to perform 'create' and 'update' actions
                            'actions'=>array('index','new','edit','delete','save'),
                            'users'=>array('@'),
                        ),
            */
            array('allow',
                'actions'=>array('new','edit'),
                'expression'=>array('RankinglistController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','downs','download'),
                'expression'=>array('RankinglistController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionIndex($pageNum=0)
    {


       $model = new ReportRankinglistForm;
        $model->retrieveDatas($model);
//        print_r('<pre>');
//        print_r($model);
        $this->render('index',array('model'=>$model));
    }



    public function actionView()
    {
        $model=new ReportRankinglistForm($_POST['ReportRankinglistForm']);
        $start=$model['scenario']['start_dt']."-".$model['scenario']['start_dt1']."-01";
        $end=$model['scenario']['start_dt']."-".$model['scenario']['start_dt1']."-31";
        $peopel=$model->salepeople($start,$end,false);
        $list=$model->salelist($start,$end);
        $lists=$model->salelists($start,$end);
        $rank=$model->ranklist($start,$end);
        $renaudlist=$model->renaudlist($start,$end);
//        print_r('<pre>');
//        print_r($start);
        $this->render('form',array('model'=>$model,'renaudlist'=>$renaudlist,'peopel'=>$peopel,'list'=>$list,'lists'=>$lists,'rank'=>$rank));

    }

	public function actionDownload($start, $end) {
		$model = new ReportRankinglistForm();
        $people=$model->salepeople($start,$end,false);
        $list=$model->salelist($start,$end);
        $lists=$model->salelists($start,$end);
        $rank=$model->ranklist($start,$end);
        $renaudlist=$model->renaudlist($start,$end);
		$output = $model->export($start, $people, $list, $lists, $rank, $renaudlist);

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: inline;filename="rankinglist'.date('Ym',strtotime($start)).'.xlsx"');
		header('Cache-Control: max-age=0');
		echo $output;
		Yii::app()->end();
	}

//    public function actiondowns(){
//        $model = new ReportG02Form;
//        $model->scenario = $_POST['ReportG02Form'];
//        $model->retrieveData($model);
//        $model->retrieveXiaZai($model);
////        print_r("<pre>");
////        print_r($model['city'][$model['scenario']['city']]);
//    }
//	public function getMonth( $date1, $date2){
//        $time1 = strtotime($date1); // 自动为00:00:00 时分秒
//        $time2 = strtotime($date2);
//        $monarr = array();
//        while( ($time1 = strtotime('+1 month', $time1)) <= $time2){
//            $monarr[] = date('n',$time1); // 取得递增月;
//        }
////        print_r($monarr);
//
//    }

//    public function getMonthNum( $date1, $date2, $tags='-' ){
//        $date1 = explode($tags,$date1);
//        $date2 = explode($tags,$date2);
//
//        return abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
//    }



    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('HA05');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('HA05');
    }
}
