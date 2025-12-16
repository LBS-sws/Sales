<?php

/**
 * 客户等级列表模型
 * 
 * 处理客户等级的分页查询
 * 支持生产和测试环境的数据库前缀自动区分
 * 
 * @package models
 * @author 王耽误
 */

class ClueLevelList extends CListPageModel
{
    public function attributeLabels()
    {
        return array(
            'level_code'=>'等级代码',
            'level_name'=>'等级名称',
            'level_desc'=>'等级描述',
            'sort'=>'排序',
            'status'=>'状态',
        );
    }

    public function determinePageNum($pageNum)
    {
        if ($pageNum == 0) {
            $pageNum = 1;
        }
        $this->pageNum = $pageNum;
    }

    public function retrieveDataByPage($pageNum=1)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $sql1 = "select *
                from sales{$suffix}.sal_clue_level
                where 1=1 ";
        $sql2 = "select count(id)
                from sales{$suffix}.sal_clue_level
                where 1=1 ";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\\'",trim($this->searchValue));
            switch ($this->searchField) {
                case 'level_name':
                    $clause .= " AND level_name LIKE '%{$svalue}%'";
                    break;
            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        } else {
            $order = " order by sort ASC, id DESC ";
        }

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $list = array();
        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'level_code'=>$record['level_code'],
                    'level_name'=>$record['level_name'],
                    'level_desc'=>$record['level_desc'],
                    'sort'=>$record['sort'],
                    'status'=>$record['status'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_hc22'] = $this->getCriteria();
        return true;
    }
}
