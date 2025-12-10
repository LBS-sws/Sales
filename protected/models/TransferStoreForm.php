<?php

class TransferStoreForm extends CFormModel
{
    public $store_id;
    public $target_clue_id;
    public $transfer_reason;
    
    private $store_info;
    private $target_clue_info;

    public function rules()
    {
        return array(
            array('store_id, target_clue_id', 'required'),
            array('store_id, target_clue_id', 'numerical', 'integerOnly' => true),
            array('transfer_reason', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'store_id' => '门店',
            'target_clue_id' => '目标客户',
            'transfer_reason' => '转移原因',
        );
    }

    /**
     * 验证源门店和目标客户是否存在
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (!parent::validate($attributeNames, $clearErrors)) {
            return false;
        }

        // 验证源门店是否存在
        $storeRow = Yii::app()->db->createCommand()
            ->select("id, clue_id, store_name")
            ->from("sal_clue_store")
            ->where("id=:id", array(":id" => $this->store_id))
            ->queryRow();

        if (!$storeRow) {
            $this->addError('store_id', '源门店不存在');
            return false;
        }

        $this->store_info = $storeRow;

        // 验证目标客户是否存在
        $targetClue = Yii::app()->db->createCommand()
            ->select("id, cust_name")
            ->from("sal_clue")
            ->where("id=:id", array(":id" => $this->target_clue_id))
            ->queryRow();

        if (!$targetClue) {
            $this->addError('target_clue_id', '目标客户不存在');
            return false;
        }

        // 验证不能转移到同一个客户
        if ($storeRow['clue_id'] == $this->target_clue_id) {
            $this->addError('target_clue_id', '不能转移到同一个客户');
            return false;
        }

        $this->target_clue_info = $targetClue;
        return true;
    }

    /**
     * 执行转移操作
     */
    public function executeTransfer()
    {
        if (!$this->validate()) {
            return array('status' => false, 'message' => CHtml::errorSummary($this));
        }

        $connection = Yii::app()->db;
        $transaction = $connection->beginTransaction();
        $suffix = Yii::app()->params['envSuffix'];

        try {
            $source_clue_id = $this->store_info['clue_id'];
            $store_id = $this->store_id;
            $target_clue_id = $this->target_clue_id;

            // 1. 转移门店
            $connection->createCommand()->update('sal_clue_store', array(
                'clue_id' => $target_clue_id,
                'luu' => Yii::app()->user->id,
            ), 'id=:id', array(':id' => $store_id));

            // 2. 获取该门店关联的合约
            $contracts = $connection->createCommand()
                ->select("id")
                ->from("sales{$suffix}.sal_contract")
                ->where("clue_id=:clue_id", array(":clue_id" => $source_clue_id))
                ->queryAll();

            if (!empty($contracts)) {
                // 3. 转移合约及其关联数据
                foreach ($contracts as $contract) {
                    $contract_id = $contract['id'];

                    // 转移主合约表
                    $connection->createCommand()->update("sales{$suffix}.sal_contract", array(
                        'clue_id' => $target_clue_id,
                        'luu' => Yii::app()->user->id,
                    ), 'id=:id', array(':id' => $contract_id));

                    // 转移合约门店服务关联表
                    $connection->createCommand()->update("sales{$suffix}.sal_contract_sse", array(
                        'clue_id' => $target_clue_id,
                        'luu' => Yii::app()->user->id,
                    ), 'cont_id=:id', array(':id' => $contract_id));

                    // 转移虚拟合约
                    $connection->createCommand()->update("sales{$suffix}.sal_contract_virtual", array(
                        'clue_id' => $target_clue_id,
                        'luu' => Yii::app()->user->id,
                    ), 'cont_id=:id', array(':id' => $contract_id));

                    // 转移合约操作相关表
                    $connection->createCommand()->update("sales{$suffix}.sal_contpro", array(
                        'clue_id' => $target_clue_id,
                        'luu' => Yii::app()->user->id,
                    ), 'cont_id=:id', array(':id' => $contract_id));

                    $connection->createCommand()->update("sales{$suffix}.sal_contpro_sse", array(
                        'clue_id' => $target_clue_id,
                        'luu' => Yii::app()->user->id,
                    ), 'cont_id=:id', array(':id' => $contract_id));

                    $connection->createCommand()->update("sales{$suffix}.sal_contpro_file", array(
                        'clue_id' => $target_clue_id,
                        'luu' => Yii::app()->user->id,
                    ), 'cont_id=:id', array(':id' => $contract_id));

                    $connection->createCommand()->update("sales{$suffix}.sal_contpro_virtual", array(
                        'clue_id' => $target_clue_id,
                        'luu' => Yii::app()->user->id,
                    ), 'cont_id=:id', array(':id' => $contract_id));
                }
            }

            // 4. 记录历史
            $this->recordHistory($connection, $source_clue_id, $target_clue_id, $store_id);

            $transaction->commit();

            return array(
                'status' => true,
                'message' => '门店及其关联合约转移成功！'
            );
        } catch (Exception $e) {
            $transaction->rollBack();
            return array(
                'status' => false,
                'message' => '转移失败：' . $e->getMessage()
            );
        }
    }

    /**
     * 记录转移历史
     */
    private function recordHistory(&$connection, $source_clue_id, $target_clue_id, $store_id)
    {
        $uid = Yii::app()->user->id;
        $reason = !empty($this->transfer_reason) ? $this->transfer_reason : '系统自动转移';
        $suffix = Yii::app()->params['envSuffix'];
        // 记录源门店的历史
        $connection->createCommand()->insert("sales{$suffix}.sal_clue_history", array(
            'table_id' => $store_id,
            'table_type' => 2,
            'history_type' => 2,
            'history_html' => "<span>门店转移至客户ID: {$target_clue_id}，原因：{$reason}</span>",
            'lcu' => $uid,
        ));
    }

    /**
     * 获取门店信息
     */
    public function getStoreInfo()
    {
        return $this->store_info;
    }

    /**
     * 获取目标客户信息
     */
    public function getTargetClueInfo()
    {
        return $this->target_clue_info;
    }
}
