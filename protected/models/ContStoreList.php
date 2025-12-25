<?php

class ContStoreList extends CListPageModel
{
    public $cont_id;
    public $searchKeyword = '';
    public $pageNum = 1;
    public $noOfPages = 1;
    public $totalRow = 0;
    public $noOfItem = 30;
    public $attr = array();
    
    public function attributeLabels()
    {
        return array(
            'searchKeyword' => Yii::t('misc','Search'),
        );
    }

    public function rules()
    {
        return array(
            array('cont_id, searchKeyword, pageNum, noOfPages, totalRow, noOfItem, attr', 'safe'),
        );
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $suffix = Yii::app()->params['envSuffix'];
        
        if (empty($this->cont_id)) {
            $this->attr = array();
            $this->totalRow = 0;
            $this->pageNum = 1;
            $this->noOfPages = 1;
            return true;
        }
        
        // 通过 sal_contract_sse 关联表查询合同的门店 - 使用和老逻辑相同的方式
        $sql1 = "SELECT count(DISTINCT a.id)
                FROM sal_contract_sse sse
                LEFT JOIN sal_clue_store a ON sse.clue_store_id = a.id
                WHERE sse.cont_id = :cont_id
        ";
        
        $sql2 = "SELECT a.*, sse.cont_id
                FROM sal_contract_sse sse
                LEFT JOIN sal_clue_store a ON sse.clue_store_id = a.id
                WHERE sse.cont_id = :cont_id
        ";
        
        $clause = "";
        $params = array(':cont_id' => $this->cont_id);
        
        // 如果有搜索关键词
        if (!empty($this->searchKeyword)) {
            $keyword = addslashes($this->searchKeyword);
            $clause .= " AND (a.store_name LIKE '%{$keyword}%' OR a.store_code LIKE '%{$keyword}%')";
        }
        
        $order = " ORDER BY a.lcd DESC";
        
        $sql = $sql2.$clause.$order;
        $this->totalRow = Yii::app()->db->createCommand($sql1.$clause)->queryScalar($params);
        $this->pageNum = $pageNum;
        $this->noOfPages = ceil($this->totalRow / $this->noOfItem);
        
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll(true, $params);
        
        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $record) {
                $this->attr[] = $record;
            }
        }
        return true;
    }
}
