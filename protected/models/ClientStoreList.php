<?php

class ClientStoreList extends CListPageModel
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
        
        $staff_id = CGetName::getEmployeeIDByMy();
        $groupIdStr = CGetName::getGroupStaffIDByStaffID($staff_id);
        $groupIdStr = implode(",",$groupIdStr);
        
        $sql1 = "SELECT count(DISTINCT a.id)
                FROM sal_clue_store a
                LEFT JOIN sal_clue c ON a.clue_id = c.id
                WHERE a.clue_id = :clue_id";
        
        $sql2 = "SELECT a.*
                FROM sal_clue_store a
                LEFT JOIN sal_clue c ON a.clue_id = c.id
                WHERE a.clue_id = :clue_id";
        
        $clause = "";
        $params = array(':clue_id' => $this->clue_id);
        
        // 权限过滤：地推类型所有人可见，其他类型只显示当前销售相关的门店
        if(ClientHeadList::isReadAll()){
            // 如果有查看全部权限，不限制
        }else{
            // 地推类型（clue_type=1）：所有人都可以看到
            // 其他类型：只显示当前销售团队创建的门店
            $clause .= " AND (c.clue_type = 1 OR a.create_staff in ({$groupIdStr}))";
        }
        
        // 如果有搜索关键词
        if (!empty($this->searchKeyword)) {
            $keyword = addslashes($this->searchKeyword);
            $clause .= " AND (a.store_name LIKE '%{$keyword}%' OR a.store_code LIKE '%{$keyword}%')";
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
