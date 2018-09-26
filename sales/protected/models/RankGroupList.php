<?php

class RankGroupList extends CListPageModel
{
    public $category =1;//積分類型： 1：德  2：智 3：體 4：群 5：美
    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'id'=>Yii::t('integral','ID'),
            'employee_id'=>Yii::t('integral','Employee Name'),
            'ranking'=>Yii::t('integral','ranking'),
            'employee_code'=>Yii::t('integral','Employee Code'),
            'employee_name'=>Yii::t('integral','Employee Name'),
            'year'=>Yii::t('integral','particular year'),
            'start_num'=>Yii::t('integral','sum credit num'),
            'end_num'=>Yii::t('integral','effect credit num'),
            'category'=>Yii::t('integral','integral type'),
            'city'=>Yii::t('integral','City'),
            'city_name'=>Yii::t('integral','City'),
        );
    }

    public function rules()
    {
        return array(
            array('attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, category','safe',),
        );
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $suffix = Yii::app()->params['envSuffix'];
        //$city_allow = Yii::app()->user->city_allow();
        $category = $this->category;
        $year = date("Y");
        if(empty($category)){
            $this->attr = array();
            return true;
        }
        $sql1 = "select a.year,a.year,d.code AS employee_code,d.name AS employee_name,d.city AS s_city,SUM(a.start_num) AS start_num,SUM(a.end_num) AS end_num 
                from gr_credit_point_ex a
                LEFT JOIN gr_credit_point e ON a.point_id = e.id
                LEFT JOIN gr_credit_type f ON e.credit_type = f.id
                LEFT JOIN hr$suffix.hr_employee d ON a.employee_id = d.id
                where f.category = '$category' and a.year = '$year' 
			";
        $sql2 = "select a.year,d.name AS employee_name,d.city AS s_city,SUM(a.start_num) AS start_num,SUM(a.end_num) AS end_num 
                from gr_credit_point_ex a
                LEFT JOIN gr_credit_point e ON a.point_id = e.id
                LEFT JOIN gr_credit_type f ON e.credit_type = f.id
                LEFT JOIN hr$suffix.hr_employee d ON a.employee_id = d.id
                where f.category = '$category' and a.year = '$year' 
			";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'employee_code':
                    $clause .= General::getSqlConditionClause('d.code',$svalue);
                    break;
                case 'employee_name':
                    $clause .= General::getSqlConditionClause('d.name',$svalue);
                    break;
                case 'city_name':
                    $clause .= ' and d.city in '.CreditRequestList::getCityCodeSqlLikeName($svalue);
                    break;
            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        } else
            $order = " order by start_num desc";


        $group = "GROUP BY a.employee_id,a.year ";

        $sql = $sql1.$clause.$group;
        $count = Yii::app()->db->createCommand($sql)->queryAll();
        if($count){
            $count = count($count);
            $this->totalRow = $count>20?20:$count;
        }else{
            $this->totalRow = 0;
        }

        $sql = $sql1.$clause.$group.$order;
        $this->pageNum = 1;
        $this->noOfItem = 20;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $list = array();
        $this->attr = array();
        if (count($records) > 0) {
            $key = 0;//名次
            $categoryList = CreditTypeForm::getCategoryAll();
            if(key_exists($category,$categoryList)){
                $category = $categoryList[$category];
            }
            foreach ($records as $k=>$record) {
                $key++;
                $this->attr[] = array(
                    'ranking'=>$key,
                    'employee_code'=>$record['employee_code'],
                    'employee_name'=>$record['employee_name'],
                    'city'=>CGeneral::getCityName($record["s_city"]),
                    'start_num'=>$record['start_num'],
                    'category'=>$category,
                );
            }
        }
        $session = Yii::app()->session;
        $session['rankGroup_op01'] = $this->getCriteria();
        return true;
    }

    //導出excel
    public function export(){
        $title = Yii::t("integral","down_city")."(".date("Y").").xls";
        $this->retrieveDataByPage(1);
        $arrHeard=array(
            Yii::t('integral','ranking'),
            Yii::t('integral','Employee Code'),
            Yii::t('integral','Employee Name'),
            Yii::t('integral','City'),
            Yii::t('integral','sum credit num')
        );
        $myExcel = new MyExcelTwo();
        $myExcel->setDataHeard($arrHeard);
        $myExcel->setDataBody($this->attr);
        $myExcel->outDownExcel($title);
    }
}
