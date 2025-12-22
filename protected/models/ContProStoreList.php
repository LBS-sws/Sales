<?php

class ContProStoreList extends CListPageModel
{
    public $cont_id;
    public $pro_id;
    public $searchKeyword = '';
    public $storeIds = array();
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
            array('cont_id, pro_id, searchKeyword, storeIds, pageNum, noOfPages, totalRow, noOfItem, attr', 'safe'),
        );
    }

    public function retrieveDataByPage($pageNum=1)
    {
        // 如果没有门店ID列表，直接返回空
        if (empty($this->storeIds)) {
            $this->attr = array();
            $this->totalRow = 0;
            $this->pageNum = 1;
            $this->noOfPages = 1;
            return true;
        }

        $storeIds = array();
        foreach ($this->storeIds as $storeId) {
            $storeId = intval($storeId);
            if ($storeId > 0) {
                $storeIds[] = $storeId;
            }
        }
        $storeIds = array_values(array_unique($storeIds));
        if (empty($storeIds)) {
            $this->attr = array();
            $this->totalRow = 0;
            $this->pageNum = 1;
            $this->noOfPages = 1;
            return true;
        }

        $idStr = implode(',', $storeIds);

        $sql1 = "SELECT count(DISTINCT a.id)
                FROM sal_clue_store a
                LEFT JOIN sal_clue_invoice b ON a.invoice_id = b.id
                WHERE a.id IN ({$idStr})
        ";

        $sql2 = "SELECT a.*, b.invoice_header, b.tax_id, b.invoice_address
                FROM sal_clue_store a
                LEFT JOIN sal_clue_invoice b ON a.invoice_id = b.id
                WHERE a.id IN ({$idStr})
        ";

        $clause = "";

        // 如果有搜索关键词
        if (!empty($this->searchKeyword)) {
            $keyword = addslashes($this->searchKeyword);
            $clause .= " AND (a.store_name LIKE '%{$keyword}%' OR a.store_code LIKE '%{$keyword}%')";
        }
//        $clause .= " AND a.z_display = 1";

        $order = " ORDER BY FIELD(a.id, {$idStr})";

        $sql = $sql2.$clause.$order;
        $this->totalRow = Yii::app()->db->createCommand($sql1.$clause)->queryScalar();
        $this->pageNum = $pageNum;
        $this->noOfPages = empty($this->totalRow) ? 1 : max(1, ceil($this->totalRow / $this->noOfItem));

        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $record) {
                $this->attr[] = $record;
            }
        }
        return true;
    }
}
