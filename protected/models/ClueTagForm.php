<?php

/**
 * 客户标签表单模型
 * 
 * 处理客户标签的数据验证、保存、删除等操作
 * 支持生产和测试环境的数据库前缀自动区分
 * 支持丰丰选择查询及颜色配置
 * 
 * @package models
 * @author 王耽误
 */
class ClueTagForm extends CFormModel
{
    public $id;              // 主键dID
    public $tag_code;        // 标签代码（唯一）
    public $tag_name;        // 标签名称
    public $tag_desc;        // 标签描述
    public $tag_color;       // 标签颜色 (帖子样式)
    public $sort;            // 排序
    public $status;          // 状态: 1=启用, 0=禁用

    public function rules()
    {
        $rules = array(
            array('tag_code, tag_name', 'required'),
            array('tag_code', 'length', 'min' => 1, 'max' => 50),
            array('tag_name', 'length', 'min' => 1, 'max' => 255),
            array('tag_color', 'length', 'min' => 1, 'max' => 20),
            array('sort', 'numerical', 'integerOnly' => true),
            array('status', 'numerical', 'integerOnly' => true),
            array('id, tag_desc, scenario', 'safe'),
        );
        // 验证标签代码唯一性，编辑时排除自己的ID
        $rules[] = array('tag_code', 'validateTagCode');
        return $rules;
    }
    
    /**
     * 验证标签代码唯一性
     */
    public function validateTagCode($attribute, $param)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select('id')
            ->from("sales{$suffix}.sal_clue_tag")
            ->where('tag_code=:code AND id!=:id', array(':code' => $this->tag_code, ':id' => empty($this->id) ? 0 : $this->id))
            ->queryRow();
        
        if ($row) {
            $this->addError($attribute, '标签代码已存在，请使用其他代码');
        }
    }

    public function attributeLabels()
    {
        return array(
            'tag_code' => '标签代码',
            'tag_name' => '标签名称',
            'tag_desc' => '标签描述',
            'tag_color' => '标签颜色',
            'sort' => '排序',
            'status' => '状态',
        );
    }

    public function retrieveData($id)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("*")
            ->from("sales{$suffix}.sal_clue_tag")
            ->where("id=:id", array(":id" => $id))
            ->queryRow();

        if ($row) {
            $this->attributes = $row;
            return true;
        }
        return false;
    }

    public function saveData()
    {
        $connection = Yii::app()->db;
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        
        $sql = '';
        switch ($this->getScenario()) {
            case 'new':
                $sql = "insert into sales{$suffix}.sal_clue_tag(
                        tag_code, tag_name, tag_desc, tag_color, sort, status, lcu) 
                        values (:tag_code, :tag_name, :tag_desc, :tag_color, :sort, :status, :lcu)";
                break;
            case 'edit':
                $sql = "update sales{$suffix}.sal_clue_tag set 
                    tag_code = :tag_code, 
                    tag_name = :tag_name, 
                    tag_desc = :tag_desc,
                    tag_color = :tag_color,
                    sort = :sort,
                    status = :status,
                    luu = :luu
                    where id = :id";
                break;
        }
        
        if (empty($sql)) {
            throw new CDbException('无法确定操作类型，请刷新页面后重试');
        }
        
        $command = $connection->createCommand($sql);
        if (strpos($sql, ':tag_code') !== false)
            $command->bindParam(':tag_code', $this->tag_code, PDO::PARAM_STR);
        if (strpos($sql, ':tag_name') !== false)
            $command->bindParam(':tag_name', $this->tag_name, PDO::PARAM_STR);
        if (strpos($sql, ':tag_desc') !== false)
            $command->bindParam(':tag_desc', $this->tag_desc, PDO::PARAM_STR);
        if (strpos($sql, ':tag_color') !== false) {
            $color = $this->tag_color ? $this->tag_color : '#999999';
            $command->bindParam(':tag_color', $color, PDO::PARAM_STR);
        }
        if (strpos($sql, ':sort') !== false) {
            $sort = intval($this->sort) ? intval($this->sort) : 0;
            $command->bindParam(':sort', $sort, PDO::PARAM_INT);
        }
        if (strpos($sql, ':status') !== false) {
            $status = intval($this->status) !== '' ? intval($this->status) : 1;
            $command->bindParam(':status', $status, PDO::PARAM_INT);
        }
        if (strpos($sql, ':lcu') !== false)
            $command->bindParam(':lcu', $uid, PDO::PARAM_STR);
        if (strpos($sql, ':luu') !== false)
            $command->bindParam(':luu', $uid, PDO::PARAM_STR);
        if (strpos($sql, ':id') !== false)
            $command->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        $command->execute();
        
        if ($this->getScenario() == 'new')
            $this->id = $connection->getLastInsertID();
    }

    public function deleteData()
    {
        $suffix = Yii::app()->params['envSuffix'];
        Yii::app()->db->createCommand("update sales{$suffix}.sal_clue_tag set status=0 where id=:id")
            ->bindParam(':id', $this->id, PDO::PARAM_INT)
            ->execute();
    }
}
