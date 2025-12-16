-- =========================================
-- 客户等级表: sal_clue_level
-- 用途: 存储客户等级定义 (VIP、重点客户、普通客户等)
-- 说明: 这是一个枚举表，管理员在系统中维护等级列表
--      所有客户表(sal_clue)通过clue_level_id外键关联
-- =========================================
CREATE TABLE `sal_clue_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键，自增ID',
  `level_code` varchar(50) NOT NULL COMMENT '等级代码（唯一），用于系统内部识别，如VIP、IMPORTANT等',
  `level_name` varchar(255) NOT NULL COMMENT '等级名称，展示给用户，如"VIP客户"、"重点客户"',
  `level_desc` text COMMENT '等级描述，详细说明该等级的含义和标准',
  `sort` int(11) DEFAULT '0' COMMENT '排序号，用于列表展示顺序，数字越小越靠前',
  `status` int(11) DEFAULT '1' COMMENT '状态: 1=启用(可用), 0=禁用(不可用)',
  `lcu` varchar(30) DEFAULT NULL COMMENT '创建用户ID',
  `luu` varchar(30) DEFAULT NULL COMMENT '最后更新用户ID',
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间（自动生成）',
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间（自动更新）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `level_code` (`level_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='客户等级表：存储所有客户等级定义';

-- =========================================
-- 客户标签表: sal_clue_tag
-- 用途: 存储客户标签定义 (KA客户、战略客户、需求客户等)
-- 说明: 枚举表，一个客户可以有多个标签，支持颜色配置
-- =========================================
CREATE TABLE `sal_clue_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tag_code` varchar(50) NOT NULL COMMENT '标签代码 (unique)',
  `tag_name` varchar(255) NOT NULL COMMENT '标签名称',
  `tag_desc` text COMMENT '标签描述',
  `tag_color` varchar(20) DEFAULT '#999999' COMMENT '标签颜色 (hex color)',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `status` int(11) DEFAULT '1' COMMENT '状态',
  `lcu` varchar(30) DEFAULT NULL COMMENT '创建用户',
  `luu` varchar(30) DEFAULT NULL COMMENT '更新用户',
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag_code` (`tag_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='客户标签表';



-- =========================================
-- 在sal_clue表中添加客户等级上外键字段
-- 用途: 存储客户的等级ID（外键，关联sal_clue_level表）
-- 说明: 一个客户只能有一个等级（单选），默认为MALL
-- =========================================
ALTER TABLE `sal_clue` ADD COLUMN `clue_level_id` int(11) DEFAULT NULL COMMENT '客户等级ID (外键关联 sal_clue_level.id)';
ALTER TABLE `sal_clue` ADD COLUMN `clue_tag` varchar(255) DEFAULT NULL COMMENT '客户标签IDs (逗号分隔，如"1,2,3")';

-- =========================================
-- 在sal_clue_store表中添加门店等级和标签字段
-- 用途: 门店可以单独设置自己的等级和标签
-- 说明: 门店的等级/标签独立于客户，可以自由选择
-- =========================================
ALTER TABLE `sal_clue_store` ADD COLUMN `store_level_id` int(11) DEFAULT NULL COMMENT '门店等级ID (外键关联 sal_clue_level.id)';
ALTER TABLE `sal_clue_store` ADD COLUMN `store_tag` varchar(255) DEFAULT NULL COMMENT '门店标签IDs (逗号分隔，如"1,2,3")';


