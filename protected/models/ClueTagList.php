<?php

/**
 * 客户标签列表模型
 * 
 * 处理客户标签的分页查询
 * 支持生产和测试环境的数据库前缀自动区分
 * 
 * @package models
 * @author 王耽误
 */

class ClueTagList extends CListPageModel
{
    public function attributeLabels()
    {
        return array(
            'tag_code'=>'标签代码',
            'tag_name'=>'标签名称',
            'tag_color'=>'标签颜色',
            'tag_desc'=>'标签描述',
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
                from sales{$suffix}.sal_clue_tag
                where 1=1 ";
        $sql2 = "select count(id)
                from sales{$suffix}.sal_clue_tag
                where 1=1 ";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\\'",trim($this->searchValue));
            switch ($this->searchField) {
                case 'tag_name':
                    $clause .= " AND tag_name LIKE '%{$svalue}%'";
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
                    'tag_code'=>$record['tag_code'],
                    'tag_name'=>$record['tag_name'],
                    'tag_color'=>$record['tag_color'],
                    'tag_desc'=>$record['tag_desc'],
                    'sort'=>$record['sort'],
                    'status'=>$record['status'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_hc23'] = $this->getCriteria();
        return true;
    }
}
