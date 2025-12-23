<?php

class ClientReportList extends CListPageModel
{
    public $clue_id;
    public $searchKeyword = '';
    public $pageNum = 1;
    public $noOfPages = 1;
    public $totalRow = 0;
    public $noOfItem = 30;
    public $attr = array();
    
    public function retrieveDataByPage($pageNum=1)
    {
        if (empty($this->clue_id)) {
            $this->attr = array();
            $this->totalRow = 0;
            $this->pageNum = 1;
            $this->noOfPages = 1;
            return true;
        }
        
        $sql1 = "SELECT count(DISTINCT a.id)
                FROM sal_clue_rpt a
                LEFT JOIN sal_clue b ON a.clue_id = b.id
                LEFT JOIN sal_clue_service c ON a.clue_service_id = c.id
                WHERE a.clue_id = :clue_id";
        
        $sql2 = "SELECT a.*, b.clue_code, c.busine_id_text
                FROM sal_clue_rpt a
                LEFT JOIN sal_clue b ON a.clue_id = b.id
                LEFT JOIN sal_clue_service c ON a.clue_service_id = c.id
                WHERE a.clue_id = :clue_id";
        
        $clause = "";
        $params = array(':clue_id' => $this->clue_id);
        
        // 如果有搜索关键词
        if (!empty($this->searchKeyword)) {
            $keyword = addslashes($this->searchKeyword);
            $clause .= " AND (b.clue_code LIKE '%{$keyword}%' OR a.cust_name LIKE '%{$keyword}%')";
        }
        
        $order = " ORDER BY a.lcd DESC";
        
        $sql = $sql2.$clause.$order;
        $this->totalRow = Yii::app()->db->createCommand($sql1.$clause)->queryScalar($params);
        if (empty($this->totalRow)) {
            $this->totalRow = 0;
        }
        $this->pageNum = $pageNum;
        $this->noOfPages = $this->totalRow > 0 ? ceil($this->totalRow / $this->noOfItem) : 1;
        
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
