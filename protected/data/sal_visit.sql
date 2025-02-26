/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesuat

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-01-05 11:51:47
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_visit`
-- ----------------------------
DROP TABLE IF EXISTS `sal_visit`;
CREATE TABLE `sal_visit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `visit_dt` datetime NOT NULL,
  `visit_type` int(10) unsigned NOT NULL,
  `visit_obj` varchar(100) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `cust_type` int(10) unsigned NOT NULL,
  `cust_name` varchar(255) NOT NULL,
  `cust_alt_name` varchar(255) DEFAULT NULL,
  `cust_person` varchar(255) DEFAULT NULL,
  `cust_person_role` varchar(255) DEFAULT NULL,
  `cust_tel` varchar(50) DEFAULT NULL,
  `district` int(10) unsigned NOT NULL,
  `street` varchar(255) DEFAULT NULL,
  `remarks` varchar(5000) DEFAULT NULL,
  `shift` char(1) DEFAULT NULL COMMENT 'Y为转移的',
  `status` char(1) NOT NULL DEFAULT 'N',
  `status_dt` datetime DEFAULT NULL,
  `city` char(5) NOT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visit_01` (`city`,`username`),
  KEY `idx_visit_02` (`visit_dt`,`city`,`username`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_visit
-- ----------------------------
INSERT INTO `sal_visit` VALUES ('3', 'testuser', '2018-04-20 00:00:00', '1', '[\"1\",\"10\"]', '', '4', '陳記', '', '陳生', '老闆', '212122121', '37', 'XXXX', 'TTTXXXXX', null, 'Y', null, 'SH', 'testuser', 'testuser', '2018-04-20 16:43:05', '2018-05-21 18:50:03');
INSERT INTO `sal_visit` VALUES ('4', 'VivienneChen', '2018-04-20 00:00:00', '2', '[\"3\",\"7\"]', '', '17', '我们这一家', '', '张先森', '总经理', '123456', '221', '翠景路', '', null, 'Y', null, 'ZS', 'VivienneChen', 'VivienneChen', '2018-04-20 17:01:03', '2018-04-23 11:10:44');
INSERT INTO `sal_visit` VALUES ('5', 'kittyzhou', '2018-04-20 00:00:00', '1', '[\"1\",\"10\"]', '', '19', '上井料理', '', '小瓜', '', '1313131313', '5', '大悦城', '测试', null, 'Y', null, 'CN', 'kittyzhou', 'kittyzhou', '2018-04-20 17:41:15', '2018-04-20 17:41:15');
INSERT INTO `sal_visit` VALUES ('6', 'JoeY', '2018-04-21 00:00:00', '1', '[\"2\"]', '', '2', '泰兴药业', '', '姚生', '', '1242233345', '215', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-04-21 11:54:58', '2018-04-23 10:40:59');
INSERT INTO `sal_visit` VALUES ('7', 'VivienneChen', '2018-04-24 00:00:00', '1', '[\"2\"]', '', '1', '表妹火锅', '', '你老板', '经理', '', '160', '', '', null, 'Y', null, 'NN', 'VivienneChen', 'VivienneChen', '2018-04-24 14:06:11', '2018-04-24 14:06:11');
INSERT INTO `sal_visit` VALUES ('8', 'kittyzhou', '2018-04-25 00:00:00', '1', '[\"2\",\"4\"]', '', '36', '啦啦', '', '1', '', '1236547821', '17', '', '测试', null, 'Y', null, 'CN', 'kittyzhou', 'kittyzhou', '2018-04-25 15:47:27', '2018-04-25 15:47:27');
INSERT INTO `sal_visit` VALUES ('10', 'jianjun.sh', '2018-05-21 00:00:00', '1', '[\"1\",\"10\"]', '', '25', 'Chan Tai Man Company', '', 'Chan Tai Man', '', '1263634634', '54', '', '', 'N', 'Y', null, 'SH', 'testuser', 'test', '2018-05-21 18:44:36', '2019-04-25 16:19:19');
INSERT INTO `sal_visit` VALUES ('11', 'jianjun.sh', '2018-05-23 00:00:00', '1', '[\"1\",\"10\"]', '', '25', 'Chan Tai Man Company', '', 'Chan Tai Man', '', '1263634634', '54', '', '', 'Y', 'Y', null, 'SH', 'testuser', 'testuser', '2018-05-23 12:28:43', '2019-03-13 11:42:38');
INSERT INTO `sal_visit` VALUES ('12', 'VivienneChen', '2018-05-25 00:00:00', '1', '[\"1\"]', '', '23', '一小段', '', '', '', '', '9', '', '', null, 'Y', null, 'CD', 'VivienneChen', 'VivienneChen', '2018-05-25 10:07:08', '2018-05-25 10:07:08');
INSERT INTO `sal_visit` VALUES ('13', 'testmgr', '2018-05-25 00:00:00', '1', '[\"1\",\"10\"]', '', '25', 'Chan Tai Man', '', '', '', '', '71', '', '', null, 'Y', null, 'HD', 'testmgr', 'testmgr', '2018-05-25 16:45:16', '2018-05-25 16:45:16');
INSERT INTO `sal_visit` VALUES ('14', 'JoeY', '2018-05-25 00:00:00', '2', '[\"8\",\"10\"]', '', '5', '一点点', '', '王祥生', '', '1223232313', '153', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-05-25 17:58:24', '2018-05-25 17:58:24');
INSERT INTO `sal_visit` VALUES ('15', 'JoeY', '2018-05-25 00:00:00', '4', '[\"10\"]', '', '7', '一小段', '', '', '', '', '216', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-05-25 17:59:11', '2018-05-25 17:59:11');
INSERT INTO `sal_visit` VALUES ('16', 'JoeY', '2018-05-25 00:00:00', '2', '[\"10\"]', '', '5', '一点点', '', '王祥生', '', '1223232313', '153', '', '测试', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-05-25 18:01:34', '2018-05-25 18:01:34');
INSERT INTO `sal_visit` VALUES ('17', 'JoeY', '2018-05-25 00:00:00', '3', '[\"10\"]', '', '4', '翠华', '', '张祥生', '经理', '12321321323213', '23', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-05-25 23:29:03', '2018-05-25 23:29:03');
INSERT INTO `sal_visit` VALUES ('18', 'JoeY', '2018-05-26 00:00:00', '3', '[\"10\"]', '', '15', '太兴餐厅', '', '陈胜', '总监', '132131232132132', '193', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-05-26 22:37:52', '2018-05-26 22:37:52');
INSERT INTO `sal_visit` VALUES ('19', 'JoeY', '2018-05-28 00:00:00', '3', '[\"10\"]', '', '22', '同珍酱油', '', '黄生', '经理', '134232323', '216', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-05-28 14:28:38', '2018-05-28 14:28:38');
INSERT INTO `sal_visit` VALUES ('20', 'JoeY', '2018-05-28 00:00:00', '1', '[\"11\"]', '', '41', '瑞罗空气', '', '罗生', '副总', '123232323', '224', '', '测试', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-05-28 17:39:44', '2018-05-28 17:39:44');
INSERT INTO `sal_visit` VALUES ('21', 'VivienneChen', '2019-06-02 00:00:00', '2', '[\"10\"]', '', '4', '再来一次', '', '郝经理', '', '123456', '168', '', '', null, 'Y', null, 'SZ', 'VivienneChen', 'VivienneChen', '2018-05-28 17:43:10', '2019-06-10 10:18:40');
INSERT INTO `sal_visit` VALUES ('22', 'testuser', '2019-06-01 00:00:00', '2', '[\"10\",\"11\"]', '', '19', '上井料理', null, '小瓜', '', '1313131313', '50', '大悦城', '', 'N', 'Y', null, 'SH', 'kittyzhou', 'test', '2018-05-28 18:34:14', '2019-06-05 10:05:33');
INSERT INTO `sal_visit` VALUES ('23', 'Chris', '2018-05-28 00:00:00', '2', '[\"4\"]', '', '16', '58同城', '', '春雨里', '副经理', '1021392323', '98', '', '', null, 'Y', null, 'CQ', 'Chris', 'Chris', '2018-05-28 22:55:46', '2018-05-28 22:55:46');
INSERT INTO `sal_visit` VALUES ('24', 'Chris', '2018-05-28 00:00:00', '3', '[\"10\"]', '', '17', '太兴', '', '童童总', '副经理', '123213213213', '110', '', '', null, 'Y', null, 'CQ', 'Chris', 'Chris', '2018-05-28 22:58:17', '2018-05-28 22:58:17');
INSERT INTO `sal_visit` VALUES ('25', 'HZMGR1', '2018-05-28 00:00:00', '2', '[\"10\"]', '', '4', '台山硬料', '', '写生', '经理', '1232323232', '72', '', '', null, 'Y', null, 'HZ', 'HZMGR1', 'HZMGR1', '2018-05-28 23:40:29', '2018-05-28 23:40:29');
INSERT INTO `sal_visit` VALUES ('26', 'kittyzhou', '2019-01-29 00:00:00', '2', '[\"1\",\"10\"]', '', '1', '火锅', '', '', '', '', '31', '', '', null, 'Y', null, 'BJ', 'kittyzhou', 'kittyzhou', '2018-05-29 09:54:30', '2019-07-11 17:24:17');
INSERT INTO `sal_visit` VALUES ('27', 'kittyzhou', '2019-01-29 00:00:00', '3', '[\"1\",\"10\"]', '', '6', '喵喵喵', '', '', '', '', '35', '', '', null, 'Y', null, 'BJ', 'kittyzhou', 'kittyzhou', '2018-05-29 10:12:02', '2019-07-11 17:24:07');
INSERT INTO `sal_visit` VALUES ('28', 'VivienneChen', '2018-01-29 00:00:00', '2', '[\"10\"]', '', '24', '我又來了', '', '', '', '', '172', '', '', null, 'Y', null, 'FZ', 'VivienneChen', 'VivienneChen', '2018-05-29 10:16:16', '2019-07-11 17:24:08');
INSERT INTO `sal_visit` VALUES ('29', 'VivienneChen', '2018-06-08 00:00:00', '2', '[\"10\"]', '', '6', '我又来了', '', '', '', '', '172', '', '', null, 'Y', null, 'FZ', 'VivienneChen', 'VivienneChen', '2018-06-08 17:02:23', '2018-06-08 17:02:23');
INSERT INTO `sal_visit` VALUES ('30', 'VivienneChen', '2018-06-08 00:00:00', '2', '[\"3\"]', '', '1', '真好吃', '', '', '', '', '172', '', '', null, 'Y', null, 'FZ', 'VivienneChen', 'VivienneChen', '2018-06-08 17:11:35', '2018-06-08 17:11:35');
INSERT INTO `sal_visit` VALUES ('31', 'kittyzhou', '2018-06-08 00:00:00', '1', '[\"10\"]', '', '1', '犇犇', '', '', '', '', '23', '', '', null, 'Y', null, 'BJ', 'kittyzhou', 'kittyzhou', '2018-06-08 17:21:26', '2018-06-08 17:21:26');
INSERT INTO `sal_visit` VALUES ('32', 'VivienneChen', '2018-06-08 00:00:00', '2', '[\"10\"]', '', '1', '真好吃', '', '', '', '', '172', '', '', null, 'Y', null, 'FZ', 'VivienneChen', 'VivienneChen', '2018-06-08 17:22:46', '2018-06-08 17:22:46');
INSERT INTO `sal_visit` VALUES ('33', 'JoeY', '2018-06-10 00:00:00', '1', '[\"2\"]', '', '15', '太兴餐厅', '', '陈胜', '总监', '132131232132132', '193', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-06-10 13:09:22', '2018-06-10 13:09:22');
INSERT INTO `sal_visit` VALUES ('34', 'JoeY', '2018-06-10 00:00:00', '1', '[\"10\"]', '', '15', '翠华茶餐厅', '', '康生', '', '143434434343', '121', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-06-10 13:13:21', '2018-06-10 13:13:21');
INSERT INTO `sal_visit` VALUES ('35', 'JoeY', '2018-06-10 00:00:00', '2', '[\"10\"]', '', '15', '翠华茶餐厅（上下九路分店）', '', '前生', '总监', '1343432323', '23', '', '', null, 'Y', null, 'CN', 'JoeY', 'JoeY', '2018-06-10 13:15:24', '2018-06-10 13:15:24');
INSERT INTO `sal_visit` VALUES ('36', 'HZMGR1', '2018-06-11 00:00:00', '2', '[\"11\"]', '', '5', '大大公司', '', '东升', '经理', '143343242343', '69', '', '', null, 'Y', null, 'HZ', 'HZMGR1', 'HZMGR1', '2018-06-11 21:20:01', '2018-06-11 21:20:01');
INSERT INTO `sal_visit` VALUES ('37', 'VivienneChen', '2018-06-12 00:00:00', '2', '[\"11\"]', '', '15', '我来了', '', '', '', '', '172', '', '', null, 'Y', null, 'FZ', 'VivienneChen', 'VivienneChen', '2018-06-12 14:39:53', '2018-06-12 14:39:53');
INSERT INTO `sal_visit` VALUES ('38', 'VivienneChen', '2018-06-12 00:00:00', '4', '[\"10\"]', '', '1', '真好吃', '', '', '', '', '172', '', '', null, 'Y', null, 'FZ', 'VivienneChen', 'VivienneChen', '2018-06-12 14:41:25', '2018-06-12 14:41:25');
INSERT INTO `sal_visit` VALUES ('39', 'VivienneChen', '2018-06-12 00:00:00', '2', '[\"10\"]', '', '17', '我们这一家', '', '张先森', '总经理', '123456', '172', '翠景路', '', null, 'Y', null, 'FZ', 'VivienneChen', 'VivienneChen', '2018-06-12 14:44:54', '2018-06-12 14:44:54');
INSERT INTO `sal_visit` VALUES ('40', 'VivienneChen', '2018-06-12 00:00:00', '2', '[\"11\"]', '', '24', '我又來了', '', '', '', '', '172', '', '', null, 'Y', null, 'FZ', 'VivienneChen', 'VivienneChen', '2018-06-12 14:46:34', '2018-06-12 14:46:34');
INSERT INTO `sal_visit` VALUES ('41', 'kittyzhou', '2018-06-13 00:00:00', '1', '[\"2\"]', '', '5', '汪汪汪', '', '', '', '', '93', '', '', null, 'Y', null, 'CN', 'kittyzhou', 'kittyzhou', '2018-06-13 14:30:30', '2018-06-13 14:30:30');
INSERT INTO `sal_visit` VALUES ('42', 'kittyzhou', '2018-06-13 00:00:00', '1', '[\"10\"]', '', '4', '喵喵喵喵', '', '', '', '', '19', '', '', null, 'Y', null, 'CN', 'kittyzhou', 'kittyzhou', '2018-06-13 14:32:21', '2018-06-13 14:32:21');
INSERT INTO `sal_visit` VALUES ('43', 'test', '2019-06-04 00:00:00', '1', '[\"1\",\"10\"]', '', '2', '阿萨德', null, '我', '', '11111', '52', '', '', null, 'Y', null, 'SH', 'test', 'test', '2019-03-04 10:01:24', '2019-06-06 10:51:36');
INSERT INTO `sal_visit` VALUES ('44', 'test', '2019-06-04 00:00:00', '1', '[\"10\",\"11\"]', '', '20', '111', null, '', '', '', '44', '', '', null, 'Y', null, 'SH', 'test', 'test', '2019-06-04 16:55:05', '2019-06-05 14:23:41');
INSERT INTO `sal_visit` VALUES ('45', 'test', '2019-06-13 00:00:00', '2', '[\"1\"]', '', '20', '111', null, '1', '', '', '41', '1', '1', null, 'Y', null, 'SH', 'test', 'test', '2019-06-13 16:15:33', '2019-06-13 16:15:33');
