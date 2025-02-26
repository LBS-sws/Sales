/*
Navicat MySQL Data Transfer

Source Server         : ldb
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : salesdev

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-01-21 16:17:57
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `sal_fivestep`
-- ----------------------------
DROP TABLE IF EXISTS `sal_fivestep`;
CREATE TABLE `sal_fivestep` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `rec_dt` datetime NOT NULL,
  `five_type` varchar(10) NOT NULL DEFAULT '0',
  `step` varchar(10) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL,
  `filetype` varchar(50) DEFAULT NULL,
  `status` char(1) NOT NULL DEFAULT 'Y',
  `city` char(5) NOT NULL,
  `lcu` varchar(30) DEFAULT NULL,
  `luu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sal_fivestep
-- ----------------------------
INSERT INTO `sal_fivestep` VALUES ('1', 'testuser', '2018-03-23 00:00:00', '0', '1', '/docman/upload/sal/uat/1/22/d55bddf8d62910879ed9f605522149a8.mp4', 'video/mp4', '', 'DG', 'testuser', 'testuser', '2018-03-23 14:26:38', '2021-01-21 10:24:44');
INSERT INTO `sal_fivestep` VALUES ('2', 'testuser', '2018-03-23 00:00:00', '0', '2', '/docman/upload/sal/uat/3e/21/7e245fc2483742414604ce7e67c13111.mp4', 'video/mp4', '', 'DG', 'testuser', 'testuser', '2018-03-23 18:41:48', '2021-01-21 10:24:46');
INSERT INTO `sal_fivestep` VALUES ('3', 'testuser', '2018-03-24 00:00:00', '0', '1', '/docman/upload/sal/uat/3e/21/7e245fc2483742414604ce7e67c13111.mp4', 'video/mp4', '', 'DG', 'testuser', 'testuser', '2018-03-24 11:08:54', '2021-01-21 10:24:45');
INSERT INTO `sal_fivestep` VALUES ('4', 'JoeY', '2018-03-24 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/d41d8cd98f00b204e9800998ecf8427e.mp4', 'video/mp4', '', 'CN', 'JoeY', 'JoeY', '2018-03-24 11:12:00', '2021-01-21 10:24:46');
INSERT INTO `sal_fivestep` VALUES ('5', 'JoeY', '2018-03-24 00:00:00', '0', '1', '/docman/upload/sal/uat/46/0/46923c0eb8e0f229505992956a2715ec.mp4', 'video/mp4', '', 'CN', 'JoeY', 'JoeY', '2018-03-24 11:24:17', '2021-01-21 10:24:47');
INSERT INTO `sal_fivestep` VALUES ('6', 'JoeY', '2018-03-24 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/50f72a29faabbf47c5cb6e69c366aa52.mp4', 'video/mp4', '', 'CN', 'JoeY', 'JoeY', '2018-03-24 11:24:55', '2021-01-21 10:24:47');
INSERT INTO `sal_fivestep` VALUES ('7', 'testuser', '2018-03-24 00:00:00', '0', '3', '/docman/upload/sal/uat/43/3/1025bbae5c5ccf5c11f30b8aee9e63d1.mp4', 'video/mp4', '', 'DG', 'testuser', 'VivienneChen', '2018-03-24 11:30:42', '2021-01-21 10:24:48');
INSERT INTO `sal_fivestep` VALUES ('8', 'JoeY', '2018-03-24 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/e5e812969887155a11725ad4e3f758cf.mp4', 'video/mp4', '', 'CN', 'JoeY', 'JoeY', '2018-03-24 11:41:36', '2021-01-21 10:24:48');
INSERT INTO `sal_fivestep` VALUES ('9', 'JoeY', '2018-03-24 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/876b1ed8ab39fc17a87e4af93a74f375.mp4', 'video/mp4', '', 'CN', 'JoeY', 'JoeY', '2018-03-24 11:45:11', '2021-01-21 10:24:49');
INSERT INTO `sal_fivestep` VALUES ('10', 'testuser', '2018-03-27 00:00:00', '0', '1', '/docman/upload/sal/uat/1/22/d55bddf8d62910879ed9f605522149a8.mp4', 'video/mp4', '', 'DG', 'testuser', 'testuser', '2018-03-27 12:21:10', '2021-01-21 10:24:49');
INSERT INTO `sal_fivestep` VALUES ('11', 'JoeY', '2018-03-28 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/876b1ed8ab39fc17a87e4af93a74f375.mp4', 'video/mp4', '', 'CN', 'JoeY', 'JoeY', '2018-03-28 13:50:56', '2021-01-21 10:24:49');
INSERT INTO `sal_fivestep` VALUES ('12', 'VivienneChen', '2018-03-28 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/f71a59fe3c4e635beddac5209726d408.mp4', 'video/mp4', '', 'CD', 'VivienneChen', 'kittyzhou', '2018-03-28 14:47:02', '2021-01-21 10:24:51');
INSERT INTO `sal_fivestep` VALUES ('13', 'testuser', '2018-03-29 00:00:00', '0', '1', '/docman/upload/sal/uat/4/0/097f31ae0978732346f54b2c687f2da3.mp4', 'video/mp4', '', 'DG', 'testuser', 'testuser', '2018-03-29 01:46:45', '2021-01-21 10:24:51');
INSERT INTO `sal_fivestep` VALUES ('14', 'JoeY', '2018-03-29 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/901a701f92ababffb2a30fb9c57a03d9.mp4', 'video/mp4', '', 'CN', 'JoeY', 'JoeY', '2018-03-29 10:57:31', '2021-01-21 10:24:52');
INSERT INTO `sal_fivestep` VALUES ('15', 'JoeY', '2018-03-29 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/b34ea882979ee7136a9791cc17fd443a.MOV', 'video/quicktime', '', 'CN', 'JoeY', 'JoeY', '2018-03-29 11:00:34', '2021-01-21 10:24:52');
INSERT INTO `sal_fivestep` VALUES ('16', 'VivienneChen', '2018-03-29 00:00:00', '0', '1', '/docman/upload/sal/uat/9/0/a396baa9667d5ec9089c8b1f87a5e883.mp4', 'video/mp4', '', 'CD', 'VivienneChen', 'VivienneChen', '2018-03-29 11:13:11', '2021-01-21 10:24:52');
INSERT INTO `sal_fivestep` VALUES ('17', 'JoeY', '2018-03-29 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/9b7d907518d74ca3310dcb331872fb3e.mp3', 'audio/mp3', '', 'CN', 'JoeY', 'JoeY', '2018-03-29 11:20:18', '2021-01-21 10:24:53');
INSERT INTO `sal_fivestep` VALUES ('18', 'JoeY', '2018-03-29 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/199ffa30a08743190c6a4520a62043be.MOV', 'video/quicktime', '', 'CN', 'JoeY', 'JoeY', '2018-03-29 14:30:38', '2021-01-21 10:24:55');
INSERT INTO `sal_fivestep` VALUES ('19', 'JoeY', '2018-03-30 00:00:00', '0', '3', '/docman/upload/sal/uat/39/0/d3fe0ce30b51f3ba955c49b304bbd525.mp4', 'video/mp4', '', 'CN', 'JoeY', 'JoeY', '2018-03-30 16:19:41', '2021-01-21 10:24:56');
INSERT INTO `sal_fivestep` VALUES ('20', 'zhupeng.xa', '2018-03-30 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/ba3fd060cdd0b16d6caef18cf81ac712.mp4', 'video/mp4', '', 'XA', 'zhupeng.xa', 'zhupeng.xa', '2018-03-30 16:20:28', '2021-01-21 10:24:56');
INSERT INTO `sal_fivestep` VALUES ('21', 'funny.fz', '2018-04-04 00:00:00', '0', '1', '/docman/upload/sal/uat/0/0/085f748b1c3fd53f2816c68476e3c827.mp4', 'video/mp4', '', 'FZ', 'funny.fz', 'funny.fz', '2018-04-04 10:35:10', '2021-01-21 10:24:57');
INSERT INTO `sal_fivestep` VALUES ('22', 'clarkn.sh', '2018-04-04 00:00:00', '0', '1', '/docman/upload/sal/uat/64/c/d544a70dcaf813296edae3cf02b52db6.mp4', 'video/mp4', '', 'SH', 'clarkn.sh', 'lisa.sh', '2018-04-04 17:11:05', '2021-01-21 10:24:57');
INSERT INTO `sal_fivestep` VALUES ('24', 'clarkn.sh', '2018-04-04 00:00:00', '0', '1', '/docman/upload/sal/uat/5f/0/a3734db6871e012ae89f95ff5a48ded0.mp4', 'video/mp4', '', 'SH', 'clarkn.sh', 'clarkn.sh', '2018-04-04 17:13:41', '2021-01-21 10:24:58');
INSERT INTO `sal_fivestep` VALUES ('25', 'clarkn.sh', '2018-04-04 00:00:00', '0', '2', '/docman/upload/sal/uat/2d/0/f89d9a16163562d63e721c2efa923ad2.mp3', 'audio/mpeg', '', 'SH', 'clarkn.sh', 'clarkn.sh', '2018-04-04 17:34:14', '2021-01-21 10:24:58');
INSERT INTO `sal_fivestep` VALUES ('26', 'clarkn.sh', '2018-04-04 00:00:00', '0', '3', '/docman/upload/sal/uat/0/0/088a7257312b1708a4f4d47b2feb5b35.mp3', 'audio/mpeg', '', 'SH', 'clarkn.sh', 'test', '2018-04-04 17:34:47', '2021-01-21 10:27:30');
INSERT INTO `sal_fivestep` VALUES ('28', 'nellp.wx', '2018-04-04 00:00:00', '0', '1', '/docman/upload/sal/uat/55/0/ea9278064ae7970b0dae65cf66f44333.mp4', 'video/mp4', '', 'WX', 'nellp.wx', 'nellp.wx', '2018-04-04 21:09:43', '2021-01-21 10:25:00');
INSERT INTO `sal_fivestep` VALUES ('29', 'nellp.wx', '2018-04-04 00:00:00', '0', '1', '/docman/upload/sal/uat/5d/2/aa8d09676b9132b46e7ac438d8b7e0b1.mp4', 'video/mp4', '', 'WX', 'nellp.wx', 'nellp.wx', '2018-04-04 21:58:33', '2021-01-21 10:24:59');
INSERT INTO `sal_fivestep` VALUES ('30', 'nellp.wx', '2018-04-04 00:00:00', '0', '1', '/docman/upload/sal/uat/48/0/126aed89feff47fe9aa012c46f26cead.mp4', 'video/mp4', '', 'WX', 'nellp.wx', 'nellp.wx', '2018-04-04 22:08:31', '2021-01-21 10:25:01');
INSERT INTO `sal_fivestep` VALUES ('31', 'yan.fs', '2018-04-07 00:00:00', '0', '1', '/docman/upload/sal/uat/2/0/0174576d1aa328afa8e385f4f57203e1.mp4', 'video/mp4', '', 'FS', 'yan.fs', 'yan.fs', '2018-04-07 09:39:30', '2021-01-21 10:25:05');
INSERT INTO `sal_fivestep` VALUES ('32', 'songlin.cd', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/58/3/4c19a87820abbaf15212d18cb29e4f91.MP4', 'video/mp4', '', 'CD', 'songlin.cd', 'songlin.cd', '2018-04-07 10:10:52', '2018-04-07 10:10:52');
INSERT INTO `sal_fivestep` VALUES ('33', 'jinzhan.zs', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/1fad62c8bbeea6fc3f9b521a3ae70331.MOV', 'video/quicktime', '', 'ZS', 'jinzhan.zs', 'jinzhan.zs', '2018-04-07 11:49:04', '2018-04-07 11:49:04');
INSERT INTO `sal_fivestep` VALUES ('34', 'nellp.wx', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/28/0/2b5c402ed42ca865e60a7ed95248428d.mp4', 'video/mp4', '', 'WX', 'nellp.wx', 'nellp.wx', '2018-04-07 12:54:34', '2018-04-07 12:54:34');
INSERT INTO `sal_fivestep` VALUES ('35', 'nellp.wx', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/f207faecad3e8d3f5db4a9c1504bed93.mp4', 'video/mp4', '', 'WX', 'nellp.wx', 'nellp.wx', '2018-04-07 12:56:13', '2018-04-07 12:56:13');
INSERT INTO `sal_fivestep` VALUES ('36', 'nellp.wx', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/93971350091aa94f7eb3d9411d720043.mp4', 'video/mp4', '', 'WX', 'nellp.wx', 'nellp.wx', '2018-04-07 12:57:37', '2018-04-07 12:57:37');
INSERT INTO `sal_fivestep` VALUES ('37', 'gilbertc.wx', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/c89202e6af02f4e3df885a2a943f55bd.mp4', 'video/mp4', '', 'WX', 'gilbertc.wx', 'gilbertc.wx', '2018-04-07 23:04:20', '2018-04-07 23:04:20');
INSERT INTO `sal_fivestep` VALUES ('38', 'nellp.wx', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/5/0/b73cec2474413647c903582f6a3a319f.mp4', 'video/mp4', '', 'WX', 'nellp.wx', 'gilbertc.wx', '2018-04-07 23:06:04', '2018-04-07 23:18:58');
INSERT INTO `sal_fivestep` VALUES ('39', 'janine.wx', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/987bba5db6988a9c13f033f616932357.mp4', 'video/mp4', '', 'WX', 'janine.wx', 'gilbertc.wx', '2018-04-07 23:13:50', '2018-04-08 10:29:06');
INSERT INTO `sal_fivestep` VALUES ('40', 'janine.wx', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/987bba5db6988a9c13f033f616932357.mp4', 'video/mp4', '', 'WX', 'janine.wx', 'janine.wx', '2018-04-07 23:23:51', '2018-04-07 23:23:51');
INSERT INTO `sal_fivestep` VALUES ('41', 'lirong.fz', '2018-04-07 00:00:00', '', '1', '/docman/upload/sal/uat/1/0/9e4bed99d4c31569cfc046a9b33e0a07.mp4', 'video/mp4', '', 'FZ', 'lirong.fz', 'lirong.fz', '2018-04-07 23:57:03', '2018-04-07 23:57:03');
INSERT INTO `sal_fivestep` VALUES ('42', 'ping.nj', '2018-04-08 00:00:00', '', '1', '/docman/upload/sal/uat/53/3/d997f630b12805ea53979aa9152ad95a.mp4', 'video/mp4', '', 'NJ', 'ping.nj', 'ping.nj', '2018-04-08 10:33:52', '2018-04-08 10:33:52');
INSERT INTO `sal_fivestep` VALUES ('43', 'jon.wh', '2018-04-09 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/b2af633af100e446eb267495d9847f43.m4a', 'audio/mpeg', '', 'WH', 'jon.wh', 'jon.wh', '2018-04-09 14:36:37', '2018-04-09 14:36:37');
INSERT INTO `sal_fivestep` VALUES ('44', 'jon.wh', '2018-04-09 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/747f3f2de35d650881d417817a6950d1.m4a', 'audio/mpeg', '', 'WH', 'jon.wh', 'jon.wh', '2018-04-09 14:48:57', '2018-04-09 14:48:57');
INSERT INTO `sal_fivestep` VALUES ('45', 'zhupeng.xa', '2018-04-09 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/5c84e61afc563cf272219c1a1e671bd5.mp4', 'video/mp4', '', 'XA', 'zhupeng.xa', 'zhupeng.xa', '2018-04-09 15:14:37', '2018-04-09 15:14:37');
INSERT INTO `sal_fivestep` VALUES ('46', 'yukai.cq', '2018-04-09 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/7664d2ffca4d78f86a6b54c2d3912a36.MOV', 'video/quicktime', '', 'CQ', 'yukai.cq', 'yukai.cq', '2018-04-09 21:23:51', '2018-04-09 21:23:51');
INSERT INTO `sal_fivestep` VALUES ('47', 'zhengping.dg', '2018-04-10 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/4f6ee162a4b7f8665548a492607ca330.mp4', 'video/mp4', '', 'DG', 'zhengping.dg', 'zhengping.dg', '2018-04-10 10:46:44', '2018-04-10 10:46:44');
INSERT INTO `sal_fivestep` VALUES ('48', 'jiaxuan.dg', '2018-04-10 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/3bda39fd7a33b025c7be69005ba7aac5.mp4', 'video/mp4', '', 'DG', 'jiaxuan.dg', 'jiaxuan.dg', '2018-04-10 11:26:02', '2018-04-10 11:26:02');
INSERT INTO `sal_fivestep` VALUES ('49', 'zoey.hz', '2018-04-10 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/d9cee3c65c265689731c1a5ce1488bb7.m4a', 'audio/mp4', '', 'HZ', 'zoey.hz', 'zoey.hz', '2018-04-10 12:26:17', '2018-04-10 12:26:17');
INSERT INTO `sal_fivestep` VALUES ('50', 'testuser', '2018-04-20 00:00:00', '', '2', '/docman/upload/sal/uat/4/0/097f31ae0978732346f54b2c687f2da3.mp4', 'video/mp4', '', 'SH', 'testuser', 'test', '2018-04-20 16:33:19', '2021-01-20 11:25:28');
INSERT INTO `sal_fivestep` VALUES ('51', 'junjian.fs', '2018-04-21 00:00:00', '', '1', '/docman/upload/sal/uat/3/0/ec0f7028d8de92ae8bd4aef7c55e122d.mp4', 'video/mp4', '', 'FS', 'junjian.fs', 'junjian.fs', '2018-04-21 10:42:26', '2018-04-21 10:42:26');
INSERT INTO `sal_fivestep` VALUES ('53', 'VivienneChen', '2018-04-24 00:00:00', '', '1', '/docman/upload/sal/uat/4/0/35703b357624a05e130d2add241e8eb3.MP4', 'video/mp4', '', 'NN', 'VivienneChen', 'VivienneChen', '2018-04-24 14:07:35', '2018-04-24 14:07:35');
INSERT INTO `sal_fivestep` VALUES ('54', 'songlin.cd', '2018-05-10 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/a08290bcb90bc7fa86c0eaab6f0f612e.mp4', 'video/mp4', '', 'CD', 'songlin.cd', 'songlin.cd', '2018-05-10 20:58:49', '2018-05-10 20:58:49');
INSERT INTO `sal_fivestep` VALUES ('55', 'songlin.cd', '2018-05-10 00:00:00', '', '1', '/docman/upload/sal/uat/8/0/cf9d71fcafbc0408700a29d6a616a22c.mp4', 'video/mp4', '', 'CD', 'songlin.cd', 'songlin.cd', '2018-05-10 20:59:44', '2018-05-10 20:59:44');
INSERT INTO `sal_fivestep` VALUES ('56', 'songlin.cd', '2018-05-10 00:00:00', '', '2', '/docman/upload/sal/uat/e5/25/68f290509916d92d8c08607907cd6089.m4a', 'audio/mp4', '', 'CD', 'songlin.cd', 'songlin.cd', '2018-05-10 21:00:39', '2018-05-10 21:00:39');
INSERT INTO `sal_fivestep` VALUES ('59', 'VivienneChen', '2018-05-16 00:00:00', '', '1', '/docman/upload/sal/uat/12/0/017d9717bbf6ab574bc31c6e9486e2b3.MP4', 'video/mp4', '', 'CN', 'VivienneChen', 'kittyzhou', '2018-05-16 15:37:54', '2018-06-11 13:00:47');
INSERT INTO `sal_fivestep` VALUES ('61', 'VivienneChen', '2018-06-13 00:00:00', '', '1', '/docman/upload/sal/uat/62/2/35703b357624a05e130d2add241e8eb3.mp4', 'video/mp4', '', 'FZ', 'VivienneChen', 'VivienneChen', '2018-06-13 15:26:32', '2018-06-13 15:26:32');
INSERT INTO `sal_fivestep` VALUES ('62', 'kittyzhou', '2018-06-14 00:00:00', '', '1', '/docman/upload/sal/uat/0/0/0da42d2d1b1f33e4a0e987fa909a75a2.mp4', 'video/mp4', '', 'CN', 'kittyzhou', 'kittyzhou', '2018-06-14 10:06:25', '2018-06-14 10:06:25');
INSERT INTO `sal_fivestep` VALUES ('63', 'VivienneChen', '2018-07-19 00:00:00', '', '1', '/docman/upload/sal/uat/62/2/35703b357624a05e130d2add241e8eb3.mp4', 'video/mp4', '', 'FZ', 'VivienneChen', 'VivienneChen', '2018-07-19 09:27:41', '2018-07-19 09:27:41');
INSERT INTO `sal_fivestep` VALUES ('64', 'funny.fz', '2018-07-19 00:00:00', '', '1', '/docman/upload/sal/uat/62/2/35703b357624a05e130d2add241e8eb3.mp4', 'video/mp4', '', 'FZ', 'funny.fz', 'funny.fz', '2018-07-19 09:33:21', '2018-07-19 09:33:21');
