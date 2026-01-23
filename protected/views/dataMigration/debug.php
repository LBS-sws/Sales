<?php
/**
 * 数据迁移调试工具
 * 用于诊断失败记录重新处理的问题
 */

$this->pageTitle = '数据迁移调试工具';

// 获取参数
$logId = isset($_GET['log_id']) ? intval($_GET['log_id']) : 0;
$detailId = isset($_GET['detail_id']) ? intval($_GET['detail_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'view';

$connection = Yii::app()->db;
$result = array();

if ($action === 'test_retry' && $logId && $detailId) {
    // 测试重新处理单条记录
    try {
        // 获取记录
        $detail = $connection->createCommand()
            ->select('*')
            ->from('sal_data_migration_detail')
            ->where('id=:id AND log_id=:log_id', array(
                ':id' => $detailId,
                ':log_id' => $logId
            ))
            ->queryRow();

        if (!$detail) {
            throw new Exception('记录不存在');
        }

        // 获取日志信息
        $log = $connection->createCommand()
            ->select('*')
            ->from('sal_data_migration_log')
            ->where('id=:id', array(':id' => $logId))
            ->queryRow();

        if (!$log) {
            throw new Exception('日志不存在');
        }

        $migrationType = $log['migration_type'];
        $sourceData = json_decode($detail['source_data'], true);

        $result['step1'] = '✓ 原始数据读取成功';
        $result['source_data'] = $sourceData;

        // 预处理
        Yii::import('application.models.processors.DataMigrationVirtualContractProcessor');
        Yii::import('application.models.processors.DataMigrationContractProcessor');
        Yii::import('application.models.processors.DataMigrationClientProcessor');
        Yii::import('application.models.processors.DataMigrationStoreProcessor');

        switch ($migrationType) {
            case 'vir':
                $processed = DataMigrationVirtualContractProcessor::preprocess($sourceData, $connection, $logId);
                break;
            case 'cont':
                $processed = DataMigrationContractProcessor::preprocess($sourceData, $connection, $logId);
                break;
            case 'client':
                $processed = DataMigrationClientProcessor::preprocess($sourceData, $connection, $logId);
                break;
            case 'clientStore':
                $processed = DataMigrationStoreProcessor::preprocess($sourceData, $connection, $logId);
                break;
            default:
                throw new Exception('不支持的数据类型: ' . $migrationType);
        }

        $result['step2'] = '✓ 数据预处理成功';
        $result['processed_data'] = $processed;

        // 检查整数字段
        $intFields = array('bill_day', 'receivable_day', 'pay_type', 'pay_week', 'fee_type', 'settle_type');
        $intFieldCheck = array();
        foreach ($intFields as $field) {
            if (isset($processed[$field])) {
                $value = $processed[$field];
                $type = gettype($value);
                $intFieldCheck[$field] = array(
                    'value' => $value,
                    'type' => $type,
                    'is_empty_string' => ($value === ''),
                    'status' => ($value === '' ? '❌ 空字符串！' : '✓ OK')
                );
            }
        }
        $result['int_field_check'] = $intFieldCheck;

        $result['step3'] = '✓ 整数字段检查完成';
        $result['status'] = 'success';

    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['error'] = $e->getMessage();
        $result['file'] = $e->getFile();
        $result['line'] = $e->getLine();
        $result['trace'] = $e->getTraceAsString();
    }
}
?>

<style>
.debug-container {
    padding: 20px;
}
.debug-panel {
    background: #f9f9f9;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
}
.debug-success {
    color: #00a65a;
    font-weight: bold;
}
.debug-error {
    color: #dd4b39;
    font-weight: bold;
}
.debug-code {
    background: #282c34;
    color: #abb2bf;
    padding: 15px;
    border-radius: 5px;
    overflow-x: auto;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.5;
}
.debug-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
.debug-table th,
.debug-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.debug-table th {
    background-color: #3c8dbc;
    color: white;
}
.debug-table tr:nth-child(even) {
    background-color: #f9f9f9;
}
</style>

<section class="content-header">
    <h1>
        <strong>数据迁移调试工具</strong>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo Yii::app()->createUrl('dataMigration/index'); ?>">数据迁移</a></li>
        <li class="active">调试工具</li>
    </ol>
</section>

<section class="content">
    <div class="box box-info">
        <div class="box-body debug-container">

            <!-- 查询表单 -->
            <div class="debug-panel">
                <h4>测试失败记录重新处理</h4>
                <form method="get" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>日志ID (log_id):</label>
                                <input type="number" name="log_id" class="form-control" value="<?php echo $logId; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>记录ID (detail_id):</label>
                                <input type="number" name="detail_id" class="form-control" value="<?php echo $detailId; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" name="action" value="test_retry" class="btn btn-primary btn-block">
                                    <i class="fa fa-flask"></i> 测试重新处理
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>使用说明：</strong>
                    <ol style="margin-bottom: 0;">
                        <li>在主页面找到失败的记录，复制其 log_id 和 detail_id</li>
                        <li>输入到上面的表单中，点击"测试重新处理"</li>
                        <li>查看下方的诊断结果，找出失败原因</li>
                    </ol>
                </div>
            </div>

            <?php if (!empty($result)): ?>
            <!-- 测试结果 -->
            <div class="debug-panel">
                <h4>诊断结果</h4>

                <?php if ($result['status'] === 'success'): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> <strong>测试通过！</strong>
                        数据预处理成功，整数字段没有空字符串问题。
                    </div>

                    <?php if (isset($result['step1'])): ?>
                        <p class="debug-success">✓ <?php echo $result['step1']; ?></p>
                    <?php endif; ?>

                    <?php if (isset($result['step2'])): ?>
                        <p class="debug-success">✓ <?php echo $result['step2']; ?></p>
                    <?php endif; ?>

                    <?php if (isset($result['step3'])): ?>
                        <p class="debug-success">✓ <?php echo $result['step3']; ?></p>
                    <?php endif; ?>

                    <!-- 整数字段检查 -->
                    <?php if (isset($result['int_field_check']) && !empty($result['int_field_check'])): ?>
                        <h5 style="margin-top: 20px;">整数字段检查：</h5>
                        <table class="debug-table">
                            <thead>
                                <tr>
                                    <th>字段名</th>
                                    <th>值</th>
                                    <th>类型</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['int_field_check'] as $field => $check): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($field); ?></code></td>
                                    <td><code><?php echo htmlspecialchars(json_encode($check['value'])); ?></code></td>
                                    <td><?php echo htmlspecialchars($check['type']); ?></td>
                                    <td><?php echo $check['status']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <!-- 原始数据 -->
                    <h5 style="margin-top: 20px;">原始数据：</h5>
                    <pre class="debug-code"><?php echo htmlspecialchars(json_encode($result['source_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>

                    <!-- 处理后数据 -->
                    <h5 style="margin-top: 20px;">预处理后数据：</h5>
                    <pre class="debug-code"><?php echo htmlspecialchars(json_encode($result['processed_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>

                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fa fa-times-circle"></i> <strong>测试失败！</strong>
                    </div>

                    <p class="debug-error">错误信息：<?php echo htmlspecialchars($result['error']); ?></p>

                    <?php if (isset($result['file'])): ?>
                        <p><strong>文件：</strong> <?php echo htmlspecialchars($result['file']); ?>:<?php echo $result['line']; ?></p>
                    <?php endif; ?>

                    <?php if (isset($result['trace'])): ?>
                        <h5>错误堆栈：</h5>
                        <pre class="debug-code"><?php echo htmlspecialchars($result['trace']); ?></pre>
                    <?php endif; ?>

                    <?php if (isset($result['source_data'])): ?>
                        <h5 style="margin-top: 20px;">原始数据：</h5>
                        <pre class="debug-code"><?php echo htmlspecialchars(json_encode($result['source_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- 常见问题 -->
            <div class="debug-panel">
                <h4>常见问题及解决方案</h4>
                <table class="debug-table">
                    <thead>
                        <tr>
                            <th width="30%">错误类型</th>
                            <th>解决方案</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>Incorrect integer value: ''</code></td>
                            <td>
                                整数字段包含空字符串。<br>
                                <strong>解决</strong>：已修复代码，请清除PHP缓存并重试。
                            </td>
                        </tr>
                        <tr>
                            <td><code>Cannot add or update a child row</code></td>
                            <td>
                                外键约束失败，关联数据不存在。<br>
                                <strong>解决</strong>：先导入依赖数据（客户 → 门店 → 合约）。
                            </td>
                        </tr>
                        <tr>
                            <td><code>Duplicate entry</code></td>
                            <td>
                                数据重复。<br>
                                <strong>解决</strong>：检查原始数据中的编号是否重复。
                            </td>
                        </tr>
                        <tr>
                            <td><code>字段不能为空</code></td>
                            <td>
                                必需字段缺失。<br>
                                <strong>解决</strong>：补充原始数据中的必需字段。
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PHP信息 -->
            <div class="debug-panel">
                <h4>系统信息</h4>
                <table class="debug-table">
                    <tbody>
                        <tr>
                            <th width="200">PHP版本</th>
                            <td><?php echo phpversion(); ?></td>
                        </tr>
                        <tr>
                            <th>Yii版本</th>
                            <td><?php echo Yii::getVersion(); ?></td>
                        </tr>
                        <tr>
                            <th>OPcache状态</th>
                            <td>
                                <?php if (function_exists('opcache_get_status')): ?>
                                    <?php $opcache = opcache_get_status(); ?>
                                    <?php if ($opcache['opcache_enabled']): ?>
                                        <span class="debug-success">✓ 已启用</span>
                                        - 缓存命中率: <?php echo round($opcache['opcache_statistics']['opcache_hit_rate'], 2); ?>%
                                        <br>
                                        <a href="?action=clear_opcache" class="btn btn-warning btn-sm" style="margin-top: 5px;">
                                            <i class="fa fa-trash"></i> 清除OPcache
                                        </a>
                                    <?php else: ?>
                                        <span class="debug-error">✗ 未启用</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span>未安装 OPcache</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>当前时间</th>
                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</section>

<?php
// 清除OPcache
if (isset($_GET['action']) && $_GET['action'] === 'clear_opcache') {
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo '<script>alert("OPcache已清除！"); location.href="' . Yii::app()->createUrl('dataMigration/debug') . '";</script>';
    } else {
        echo '<script>alert("服务器未启用OPcache");</script>';
    }
}
?>

