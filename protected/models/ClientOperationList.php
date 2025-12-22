<?php

class ClientOperationList extends CListPageModel
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
        
        $suffix = Yii::app()->params['envSuffix'];
        
        $sql1 = "SELECT count(DISTINCT a.id)
                FROM sal_clue_history a
                WHERE a.table_id = :clue_id AND a.table_type = 1";
        
        $sql2 = "SELECT a.*, b.disp_name
                FROM sal_clue_history a
                LEFT JOIN security{$suffix}.sec_user b ON a.lcu = b.username
                WHERE a.table_id = :clue_id AND a.table_type = 1";
        
        $clause = "";
        $params = array(':clue_id' => $this->clue_id);
        
        // 如果有搜索关键词（搜索操作内容）
        if (!empty($this->searchKeyword)) {
            $keyword = addslashes($this->searchKeyword);
            $clause .= " AND (a.history_html LIKE '%{$keyword}%' OR a.lcu LIKE '%{$keyword}%' OR b.disp_name LIKE '%{$keyword}%')";
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
