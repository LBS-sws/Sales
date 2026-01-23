<?php
/**
 * 异步数据迁移后台处理命令
 *
 * 使用方法：
 * php protected/yiic.php AsyncDataMigration process
 * php protected/yiic.php AsyncDataMigration process --daemon
 * php protected/yiic.php AsyncDataMigration status --task_id=123
 *
 * 守护进程模式（推荐）：
 * nohup php protected/yiic.php AsyncDataMigration process --daemon > /dev/null 2>&1 &
 */
class AsyncDataMigrationCommand extends CConsoleCommand
{
    /**
     * 是否守护进程模式
     */
    public $daemon = false;

    /**
     * 处理间隔（秒）
     */
    public $interval = 5;

    /**
     * 最大执行时间（秒），0表示不限制
     */
    public $maxRunTime = 3600; // 1小时

    /**
     * 处理异步任务
     */
    public function actionProcess()
    {
        // ✅ 增加内存限制和执行时间
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $startTime = time();
        $this->log("========================================");
        $this->log("异步数据迁移处理器启动");
        $this->log("守护进程模式: " . ($this->daemon ? '是' : '否'));
        $this->log("处理间隔: {$this->interval}秒");
        $this->log("========================================");

        do {
            try {
                // 获取待处理任务
                $tasks = DataMigrationTask::getPendingTasks(1);

                if (empty($tasks)) {
                    if ($this->daemon) {
                        $this->log("暂无待处理任务，等待{$this->interval}秒...");
                        sleep($this->interval);
                    } else {
                        $this->log("暂无待处理任务，退出");
                        break;
                    }
                    continue;
                }

                foreach ($tasks as $task) {
                    $this->processTask($task);
                }

                // 检查是否超过最大运行时间
                if ($this->maxRunTime > 0 && (time() - $startTime) > $this->maxRunTime) {
                    $this->log("达到最大运行时间，退出");
                    break;
                }

                if ($this->daemon) {
                    sleep($this->interval);
                }

            } catch (Exception $e) {
                $this->error("处理异常: " . $e->getMessage());
                if ($this->daemon) {
                    sleep($this->interval);
                } else {
                    break;
                }
            }
        } while ($this->daemon);

        $this->log("异步数据迁移处理器停止");
    }

    /**
     * 处理单个任务
     */
    protected function processTask($task)
    {
        $this->log("----------------------------------------");
        $this->log("开始处理任务: {$task->task_code}");
        $this->log("迁移类型: {$task->migration_type}");
        $this->log("总城市数: {$task->total_cities}");

        // 标记任务为处理中
        $task->startProcessing();

        try {
            // 获取任务明细（待处理的城市）
            $details = Yii::app()->db->createCommand()
                ->select('*')
                ->from('sal_data_migration_task_detail')
                ->where('task_id=:task_id AND status=0', array(':task_id' => $task->id))
                ->order('id ASC')
                ->queryAll();

            if (empty($details)) {
                $this->log("任务没有待处理的城市");
                $task->complete();
                return;
            }

            $totalSuccess = 0;
            $totalErrors = 0;
            $completedCities = 0;

            foreach ($details as $detail) {
                $this->log("处理城市: {$detail['city_name']} ({$detail['city_code']})");

                // 更新明细状态为处理中
                Yii::app()->db->createCommand()->update(
                    'sal_data_migration_task_detail',
                    array(
                        'status' => 1,
                        'start_time' => date('Y-m-d H:i:s'),
                    ),
                    'id=:id',
                    array(':id' => $detail['id'])
                );

                $logId = null; // 用于保存 log_id，即使失败也要记录

                try {
                    // 处理单个城市
                    $result = $this->processSingleCity($task, $detail);
                    $logId = $result['log_id'];

                    // 更新明细状态为成功
                    Yii::app()->db->createCommand()->update(
                        'sal_data_migration_task_detail',
                        array(
                            'status' => 2,
                            'log_id' => $logId,
                            'success_count' => $result['success_count'],
                            'error_count' => $result['error_count'],
                            'end_time' => date('Y-m-d H:i:s'),
                        ),
                        'id=:id',
                        array(':id' => $detail['id'])
                    );

                    $totalSuccess += $result['success_count'];
                    $totalErrors += $result['error_count'];
                    $completedCities++;

                    $this->log("城市处理完成 - 成功: {$result['success_count']}, 失败: {$result['error_count']}");

                } catch (Exception $e) {
                    // 尝试从异常对象中提取 log_id（如果在获取数据后失败）
                    if (isset($result) && isset($result['log_id'])) {
                        $logId = $result['log_id'];
                    } elseif (property_exists($e, 'logId') && $e->logId) {
                        $logId = $e->logId;
                    }

                    // 更新明细状态为失败（保存 log_id 以便前端查看失败详情）
                    $updateData = array(
                        'status' => 3,
                        'error_message' => $e->getMessage(),
                        'end_time' => date('Y-m-d H:i:s'),
                    );

                    // 如果有 log_id，保存它（这样前端可以查看失败记录详情）
                    if ($logId) {
                        $updateData['log_id'] = $logId;
                        $updateData['success_count'] = isset($result['success_count']) ? $result['success_count'] : 0;
                        $updateData['error_count'] = isset($result['error_count']) ? $result['error_count'] : 1;
                    }

                    Yii::app()->db->createCommand()->update(
                        'sal_data_migration_task_detail',
                        $updateData,
                        'id=:id',
                        array(':id' => $detail['id'])
                    );

                    $this->error("城市处理失败: " . $e->getMessage() . ($logId ? " (已保存失败详情, log_id: {$logId})" : " (未创建日志记录)"));
                    $totalErrors++;
                    $completedCities++;
                }

                // 更新任务进度
                $task->updateProgress($completedCities, $totalSuccess, $totalErrors, $detail['city_name']);
            }

            // 检查是否所有城市都已处理完成
            $remainingCities = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('sal_data_migration_task_detail')
                ->where('task_id=:task_id AND status IN (0, 1)', array(':task_id' => $task->id))
                ->queryScalar();

            if ($remainingCities > 0) {
                // 还有待处理或处理中的城市，将任务重置为"待处理"状态，以便下次循环继续处理
                $this->log("本轮处理完成 - 成功: {$totalSuccess}, 失败: {$totalErrors}");
                $this->log("还有 {$remainingCities} 个城市待处理，将任务重置为待处理状态，下次循环继续...");

                $task->task_status = DataMigrationTask::STATUS_PENDING;
                $task->updated_at = date('Y-m-d H:i:s');
                $task->save(false);
            } else {
                // 所有城市都已处理完成
                $task->complete();
                $this->log("任务完成 - 总成功: {$totalSuccess}, 总失败: {$totalErrors}");
            }

        } catch (Exception $e) {
            $task->fail($e->getMessage());
            $this->error("任务失败: " . $e->getMessage());
        }
    }

    /**
     * 处理单个城市（支持分批次执行，实时更新进度）
     */
    protected function processSingleCity($task, $detail)
    {
        $logId = null;
        $totalSuccess = 0;
        $totalErrors = 0;

        try {
            $form = new DataMigrationForm();
            $form->migration_type = $task->migration_type;
            $form->api_url = $task->api_url;
            $form->api_config = $task->api_config;

            // 构建过滤参数
            $filterParams = json_decode($task->filter_params, true);

            // ✅ 如果 filter_params 中有 type 参数，设置到 form 对象中
            if (isset($filterParams['type'])) {
                $form->type = $filterParams['type'];
            }

            // ✅ 判断是否是全量导出（city_code='all'）
            $isFullExport = ($detail['city_code'] === 'all');

            if (!$isFullExport) {
                // 单城市导出：构建单个城市的过滤参数
                $filterParams['office_code_ids'] = array($detail['city_code']);
            }

            $form->filter_params = json_encode($filterParams);

            // ✅ Step 1: 获取数据（支持分页）
            if ($isFullExport) {
                // 全量导出：分页获取
                $this->log("  └─ 全量导出模式，开始分页获取 {$detail['city_name']} 的数据...");
                $logId = $this->fetchDataWithPagination($form, $filterParams, $detail['city_name'], $detail['id']);
            } else {
                // 单城市导出：一次性获取
                $this->log("  └─ 获取 {$detail['city_name']} 的数据...");
                $fetchResult = $form->fetchPaidanData();

                if ($fetchResult['status'] != 1) {
                    throw new Exception("获取数据失败: " . $fetchResult['message']);
                }

                $logId = $fetchResult['log_id'];
            }

            $form->id = $logId;

            // 获取总记录数
            $totalRecords = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('sal_data_migration_detail')
                ->where('log_id=:log_id', array(':log_id' => $logId))
                ->queryScalar();

            $this->log("  └─ 共获取到 {$totalRecords} 条记录，开始分批导入...");

            // Step 2: 分批导入数据（每批100条）
            $batchSize = 100;
            $processedCount = 0;

            // 获取所有待处理记录的ID
            $connection = Yii::app()->db;
            $detailIds = $connection->createCommand()
                ->select('id')
                ->from('sal_data_migration_detail')
                ->where('log_id=:log_id AND status IN (:s1, :s2)', array(
                    ':log_id' => $logId,
                    ':s1' => 'P',
                    ':s2' => 'S'
                ))
                ->order('row_index ASC')
                ->queryColumn();

            if (empty($detailIds)) {
                throw new Exception("没有待处理的数据");
            }

            // 更新城市明细：记录总数
            $connection->createCommand()->update(
                'sal_data_migration_task_detail',
                array('total_records' => count($detailIds)),
                'id=:id',
                array(':id' => $detail['id'])
            );

            // 分批处理
            $batches = array_chunk($detailIds, $batchSize);
            $batchCount = count($batches);

            foreach ($batches as $batchIndex => $batchIds) {
                $batchNum = $batchIndex + 1;
                $this->log("  └─ 正在处理第 {$batchNum}/{$batchCount} 批（{$batchSize}条）...");

                // ✅ 更新导入进度信息
                $connection->createCommand()->update(
                    'sal_data_migration_task_detail',
                    array('error_message' => "正在导入数据：第 {$batchNum}/{$batchCount} 批"),
                    'id=:id',
                    array(':id' => $detail['id'])
                );

                // 调用 syncData 处理这一批
                $importResult = $form->syncData('selected', $batchIds, $batchSize);

                $batchSuccess = isset($importResult['success_count']) ? $importResult['success_count'] : 0;
                $batchErrors = isset($importResult['error_count']) ? $importResult['error_count'] : 0;

                $totalSuccess += $batchSuccess;
                $totalErrors += $batchErrors;
                $processedCount += count($batchIds);

                // 实时更新进度到数据库
                $connection->createCommand()->update(
                    'sal_data_migration_task_detail',
                    array(
                        'success_count' => $totalSuccess,
                        'error_count' => $totalErrors,
                        'processed_records' => $processedCount
                    ),
                    'id=:id',
                    array(':id' => $detail['id'])
                );

                $progress = round(($processedCount / count($detailIds)) * 100, 1);
                $this->log("     成功: {$batchSuccess}, 失败: {$batchErrors}, 进度: {$progress}% ({$processedCount}/{$totalRecords})");

                // 避免CPU占用过高，稍作延迟
                usleep(100000); // 0.1秒
            }

            $this->log("  └─ {$detail['city_name']} 导入完成！成功: {$totalSuccess}, 失败: {$totalErrors}");

            // ✅ 清除进度信息
            $connection->createCommand()->update(
                'sal_data_migration_task_detail',
                array('error_message' => ''),
                'id=:id',
                array(':id' => $detail['id'])
            );

            return array(
                'log_id' => $logId,
                'success_count' => $totalSuccess,
                'error_count' => $totalErrors,
                'status' => 1,
                'message' => '导入完成',
            );

        } catch (Exception $e) {
            $exception = new Exception($e->getMessage(), $e->getCode(), $e);
            $exception->logId = $logId;
            throw $exception;
        }
    }

    /**
     * 分页获取数据（用于全量导出）
     * @return string 返回 log_id
     */
    protected function fetchDataWithPagination($form, $filterParams, $cityName, $detailId = null)
    {
        $currentPage = 1;
        $pageSize = isset($filterParams['page_size']) ? $filterParams['page_size'] : 2000;  // 默认2000条/页
        $totalPages = 1;
        $firstLogId = null;
        $totalFetched = 0;

        do {
            // 更新当前页
            $filterParams['page'] = $currentPage;
            $form->filter_params = json_encode($filterParams);

            $this->log("  └─ 正在获取第 {$currentPage} 页数据（每页 {$pageSize} 条）...");

            // ✅ 更新任务详情的分页进度
            if ($detailId && $totalPages > 1) {
                Yii::app()->db->createCommand()->update(
                    'sal_data_migration_task_detail',
                    array('error_message' => "正在获取数据：第 {$currentPage}/{$totalPages} 页"),
                    'id=:id',
                    array(':id' => $detailId)
                );
            }

            // 获取当前页数据
            $fetchResult = $form->fetchPaidanData();

            if ($fetchResult['status'] != 1) {
                throw new Exception("第 {$currentPage} 页获取数据失败: " . $fetchResult['message']);
            }

            $logId = $fetchResult['log_id'];
            $totalCount = isset($fetchResult['total_count']) ? $fetchResult['total_count'] : 0;
            $currentCount = isset($fetchResult['count']) ? $fetchResult['count'] : 0;

            // 记录第一页的 log_id
            if ($currentPage === 1) {
                $firstLogId = $logId;
                // 计算总页数
                if ($totalCount > 0) {
                    $totalPages = ceil($totalCount / $pageSize);
                }
                $this->log("  └─ 总记录数: {$totalCount}，将分 {$totalPages} 页获取");
            } else {
                // 后续页的数据需要合并到第一页的 log_id 下
                $this->mergeLogData($firstLogId, $logId);
            }

            $totalFetched += $currentCount;
            $this->log("  └─ 第 {$currentPage}/{$totalPages} 页获取完成，本页 {$currentCount} 条，累计 {$totalFetched}/{$totalCount} 条");

            $currentPage++;

            // 避免API压力，每页间隔1秒
            if ($currentPage <= $totalPages) {
                sleep(1);
            }

        } while ($currentPage <= $totalPages);

        $this->log("  └─ 所有数据获取完成，共 {$totalFetched} 条记录");

        // ✅ 清除分页进度信息
        if ($detailId) {
            Yii::app()->db->createCommand()->update(
                'sal_data_migration_task_detail',
                array('error_message' => '数据获取完成，开始导入...'),
                'id=:id',
                array(':id' => $detailId)
            );
        }

        return $firstLogId;
    }

    /**
     * 合并日志数据（将 sourceLogId 的数据合并到 targetLogId）
     */
    protected function mergeLogData($targetLogId, $sourceLogId)
    {
        $connection = Yii::app()->db;

        // 1. 将 sourceLogId 的明细数据更新到 targetLogId
        $connection->createCommand()->update(
            'sal_data_migration_detail',
            array('log_id' => $targetLogId),
            'log_id=:source_log_id',
            array(':source_log_id' => $sourceLogId)
        );

        // 2. 删除 sourceLogId 的主记录（已经合并了）
        $connection->createCommand()->delete(
            'sal_data_migration_log',
            'id=:source_log_id',
            array(':source_log_id' => $sourceLogId)
        );
    }

    /**
     * 查看任务状态
     */
    public function actionStatus($task_id = null, $task_code = null)
    {
        if ($task_id) {
            $task = DataMigrationTask::model()->findByPk($task_id);
        } elseif ($task_code) {
            $task = DataMigrationTask::model()->find('task_code=:code', array(':code' => $task_code));
        } else {
            $this->error("请指定 --task_id 或 --task_code");
            return;
        }

        if (!$task) {
            $this->error("任务不存在");
            return;
        }

        $this->log("========================================");
        $this->log("任务编号: {$task->task_code}");
        $this->log("迁移类型: {$task->migration_type}");
        $this->log("任务状态: {$task->getStatusText()}");
        $this->log("总城市数: {$task->total_cities}");
        $this->log("已完成: {$task->completed_cities}");
        $this->log("进度: {$task->getProgress()}%");
        $this->log("总成功: {$task->success_count}");
        $this->log("总失败: {$task->error_count}");
        if ($task->current_city) {
            $this->log("当前城市: {$task->current_city}");
        }
        if ($task->start_time) {
            $this->log("开始时间: {$task->start_time}");
        }
        if ($task->end_time) {
            $this->log("结束时间: {$task->end_time}");
        }
        if ($task->error_message) {
            $this->log("错误信息: {$task->error_message}");
        }
        $this->log("========================================");
    }

    /**
     * 输出日志
     */
    protected function log($message)
    {
        echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    }

    /**
     * 输出错误
     */
    protected function error($message)
    {
        echo "[" . date('Y-m-d H:i:s') . "] [ERROR] " . $message . "\n";
    }
}

