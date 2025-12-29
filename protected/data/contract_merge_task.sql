-- 主合同合并异步任务表
CREATE TABLE IF NOT EXISTS `sal_contract_merge_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_cont_id` int(11) NOT NULL COMMENT '目标主合同ID（保留）',
  `source_cont_ids` varchar(500) NOT NULL COMMENT '源主合同ID列表（逗号分隔）',
  `clue_id` int(11) NOT NULL COMMENT '客户ID',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '状态：pending,processing,completed,failed',
  `progress` int(3) DEFAULT 0 COMMENT '进度：0-100',
  `current_step` varchar(255) DEFAULT NULL COMMENT '当前步骤',
  `logs` text COMMENT '操作日志（JSON格式）',
  `error_message` text COMMENT '错误信息',
  `created_by` varchar(50) DEFAULT NULL COMMENT '创建人',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `completed_at` datetime DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='主合同合并异步任务表';

