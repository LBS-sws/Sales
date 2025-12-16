<?php

/**
 * 客户等级表单模型
 * 
 * 处理客户等级的数据验证、保存、删除等操作
 * 支持生产和测试环境的数据库前缀自动区分
 * 
 * @package models
 * @author 王耽误
 */
class ClueLevelForm extends CFormModel
{
    public $id;              // 主键ID
    public $level_code;       // 等级代码（唯一）
    public $level_name;       // 等级名称
    public $level_desc;       // 等级描述
    public $sort;             // 排序
    public $status;           // 状态: 1=启用, 0=禁用

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        $rules = array(
            array('level_code, level_name', 'required'),
            array('level_code', 'length', 'min' => 1, 'max' => 50),
            array('level_name', 'length', 'min' => 1, 'max' => 255),
            array('sort', 'numerical', 'integerOnly' => true),
            array('status', 'numerical', 'integerOnly' => true),
            array('id, level_desc, scenario', 'safe'),
        );
        // 验证等级代码唯一性，编辑时排除自己的ID
        $rules[] = array('level_code', 'validateLevelCode');
        return $rules;
    }
    
    /**
     * 验证等级代码唯一性
     */
    public function validateLevelCode($attribute, $param)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select('id')
            ->from("sales{$suffix}.sal_clue_level")
            ->where('level_code=:code AND id!=:id', array(':code' => $this->level_code, ':id' => empty($this->id) ? 0 : $this->id))
            ->queryRow();
        
        if ($row) {
            $this->addError($attribute, '等级代码已存在，请使用其他代码');
        }
    }

    /**
     * 属性标签 (中文描述)
     * @return array
     */
    public function attributeLabels()
    {
        return array(
            'level_code' => '等级代码',
            'level_name' => '等级名称',
            'level_desc' => '等级描述',
            'sort' => '排序',
            'status' => '状态',
        );
    }

    /**
     * 从数据库中读取特定等级数据
     * 
     * @param int $id 等级ID
     * @return bool 是否成功
     */
    public function retrieveData($id)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select("*")
            ->from("sales{$suffix}.sal_clue_level")
            ->where("id=:id", array(":id" => $id))
            ->queryRow();

        if ($row) {
            $this->attributes = $row;
            return true;
        }
        return false;
    }

    /**
     * 保存等级数据 (新增或更新)
     * 
     * 支持生产和测试环境前缀自动区分
     * @return void
     */
    public function saveData()
    {
        $connection = Yii::app()->db;
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        
        $sql = '';
        switch ($this->getScenario()) {
            case 'new':
                $sql = "insert into sales{$suffix}.sal_clue_level(
                        level_code, level_name, level_desc, sort, status, lcu) 
                        values (:level_code, :level_name, :level_desc, :sort, :status, :lcu)";
                break;
            case 'edit':
                $sql = "update sales{$suffix}.sal_clue_level set 
                    level_code = :level_code, 
                    level_name = :level_name, 
                    level_desc = :level_desc,
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
        if (strpos($sql, ':level_code') !== false)
            $command->bindParam(':level_code', $this->level_code, PDO::PARAM_STR);
        if (strpos($sql, ':level_name') !== false)
            $command->bindParam(':level_name', $this->level_name, PDO::PARAM_STR);
        if (strpos($sql, ':level_desc') !== false)
            $command->bindParam(':level_desc', $this->level_desc, PDO::PARAM_STR);
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

    /**
     * 删除等级数据 (伪删除)
     * 
     * 支持生产和测试环境前缀自动区分
     * @return void
     */
    public function deleteData()
    {
        $suffix = Yii::app()->params['envSuffix'];
        Yii::app()->db->createCommand("update sales{$suffix}.sal_clue_level set status=0 where id=:id")
            ->bindParam(':id', $this->id, PDO::PARAM_INT)
            ->execute();
    }
}
