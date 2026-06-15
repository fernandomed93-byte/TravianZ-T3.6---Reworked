-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 21, 2011 at 02:49 AM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `%PREFIX%a2b`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%a2b` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ckey` varchar(255) DEFAULT NULL,
  `time_check` int DEFAULT '0',
  `to_vid` int DEFAULT NULL,
  `u1` int DEFAULT NULL,
  `u2` int DEFAULT NULL,
  `u3` int DEFAULT NULL,
  `u4` int DEFAULT NULL,
  `u5` int DEFAULT NULL,
  `u6` int DEFAULT NULL,
  `u7` int DEFAULT NULL,
  `u8` int DEFAULT NULL,
  `u9` int DEFAULT NULL,
  `u10` int DEFAULT NULL,
  `u11` int DEFAULT NULL,
  `type` smallint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ckey` (`ckey`(25))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%abdata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%abdata` (
  `vref` int NOT NULL,
  `a1` tinyint DEFAULT '0',
  `a2` tinyint DEFAULT '0',
  `a3` tinyint DEFAULT '0',
  `a4` tinyint DEFAULT '0',
  `a5` tinyint DEFAULT '0',
  `a6` tinyint DEFAULT '0',
  `a7` tinyint DEFAULT '0',
  `a8` tinyint DEFAULT '0',
  `b1` tinyint DEFAULT '0',
  `b2` tinyint DEFAULT '0',
  `b3` tinyint DEFAULT '0',
  `b4` tinyint DEFAULT '0',
  `b5` tinyint DEFAULT '0',
  `b6` tinyint DEFAULT '0',
  `b7` tinyint DEFAULT '0',
  `b8` tinyint DEFAULT '0',
  PRIMARY KEY (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%activate`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%activate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `tribe` tinyint(1) DEFAULT NULL,
  `access` tinyint(1) DEFAULT '1',
  `act` varchar(10) DEFAULT NULL,
  `timestamp` int DEFAULT '0',
  `location` text,
  `act2` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%active`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%active` (
  `username` varchar(100) NOT NULL,
  `timestamp` int DEFAULT NULL,
  PRIMARY KEY (`username`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `%PREFIX%active`
--


-- --------------------------------------------------------

--
-- Table structure for table `%PREFIX%admin_log`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%admin_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` text,
  `log` text,
  `time` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%ali_invite`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%ali_invite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL,
  `alliance` int DEFAULT NULL,
  `sender` int DEFAULT NULL,
  `timestamp` int DEFAULT NULL,
  `accept` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alliance-accept` (`alliance`,`accept`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%ali_log`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%ali_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aid` int DEFAULT NULL,
  `comment` text,
  `date` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%ali_permission`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%ali_permission` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL,
  `alliance` int DEFAULT NULL,
  `rank` varchar(100) DEFAULT NULL,
  `opt1` int DEFAULT '0',
  `opt2` int DEFAULT '0',
  `opt3` int DEFAULT '0',
  `opt4` int DEFAULT '0',
  `opt5` int DEFAULT '0',
  `opt6` int DEFAULT '0',
  `opt7` int DEFAULT '0',
  `opt8` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid-alliance` (`uid`,`alliance`) USING BTREE,
  KEY `alliance` (`alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%alidata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%alidata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `tag` varchar(100) DEFAULT NULL,
  `leader` int DEFAULT NULL,
  `coor` int DEFAULT NULL,
  `advisor` int DEFAULT NULL,
  `recruiter` int DEFAULT NULL,
  `notice` text,
  `desc` text,
  `max` tinyint DEFAULT NULL,
  `ap` bigint DEFAULT '0',
  `dp` bigint DEFAULT '0',
  `Rc` bigint DEFAULT '0',
  `RR` bigint DEFAULT '0',
  `Aap` bigint DEFAULT '0',
  `Adp` bigint DEFAULT '0',
  `clp` bigint DEFAULT '0',
  `oldrank` bigint DEFAULT '0',
  `forumlink` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tag` (`tag`),
  KEY `name` (`name`),
  KEY `leader` (`leader`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%allimedal`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%allimedal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `allyid` int DEFAULT NULL,
  `categorie` int DEFAULT NULL,
  `plaats` int DEFAULT NULL,
  `week` int DEFAULT NULL,
  `points` varchar(15) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `del` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `week` (`week`),
  KEY `allyid` (`allyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%artefacts`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%artefacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vref` int DEFAULT NULL,
  `owner` int DEFAULT NULL,
  `type` tinyint DEFAULT NULL,
  `size` tinyint(1) DEFAULT NULL,
  `conquered` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `desc` text,
  `effect` varchar(100) DEFAULT NULL,
  `img` varchar(20) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `kind` tinyint(1) DEFAULT '0',
  `bad_effect` tinyint(1) DEFAULT '0',
  `effect2` tinyint DEFAULT '0',
  `lastupdate` int DEFAULT '0',
  `del` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `owner-active` (`owner`,`active`),
  KEY `vref-type-kind` (`vref`,`type`,`kind`) USING BTREE,
  KEY `active-type-lastupdate` (`active`,`type`,`lastupdate`),
  KEY `size-type` (`size`,`type`),
  KEY `active-owner-conquered-del` (`active`,`owner`,`conquered`,`del`),
  KEY `idx_vref` (`vref`),
  KEY `idx_owner_size` (`owner`,`size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for `%PREFIX%artefacts_chrono`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%artefacts_chrono` (
  `id` int NOT NULL AUTO_INCREMENT,
  `artefactid` int DEFAULT NULL,
  `uid` int DEFAULT NULL,
  `vref` int DEFAULT NULL,
  `conqueredtime` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artefactid-conqueredtime` (`artefactid`,`conqueredtime`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%attacks`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%attacks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vref` int DEFAULT NULL,
  `t1` int DEFAULT NULL,
  `t2` int DEFAULT NULL,
  `t3` int DEFAULT NULL,
  `t4` int DEFAULT NULL,
  `t5` int DEFAULT NULL,
  `t6` int DEFAULT NULL,
  `t7` int DEFAULT NULL,
  `t8` int DEFAULT NULL,
  `t9` int DEFAULT NULL,
  `t10` int DEFAULT NULL,
  `t11` int DEFAULT NULL,
  `attack_type` tinyint(1) DEFAULT NULL,
  `ctar1` int DEFAULT NULL,
  `ctar2` int DEFAULT NULL,
  `spy` int DEFAULT NULL,
  `b1` tinyint(1) DEFAULT NULL,
  `b2` tinyint(1) DEFAULT NULL,
  `b3` tinyint(1) DEFAULT NULL,
  `b4` tinyint(1) DEFAULT NULL,
  `b5` tinyint(1) DEFAULT NULL,
  `b6` tinyint(1) DEFAULT NULL,
  `b7` tinyint(1) DEFAULT NULL,
  `b8` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_s1attacks_attack_type` (`attack_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%attacks_archive`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%attacks_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vref` int DEFAULT NULL,
  `t1` int DEFAULT NULL,
  `t2` int DEFAULT NULL,
  `t3` int DEFAULT NULL,
  `t4` int DEFAULT NULL,
  `t5` int DEFAULT NULL,
  `t6` int DEFAULT NULL,
  `t7` int DEFAULT NULL,
  `t8` int DEFAULT NULL,
  `t9` int DEFAULT NULL,
  `t10` int DEFAULT NULL,
  `t11` int DEFAULT NULL,
  `attack_type` tinyint(1) DEFAULT NULL,
  `ctar1` int DEFAULT NULL,
  `ctar2` int DEFAULT NULL,
  `spy` int DEFAULT NULL,
  `b1` tinyint(1) DEFAULT NULL,
  `b2` tinyint(1) DEFAULT NULL,
  `b3` tinyint(1) DEFAULT NULL,
  `b4` tinyint(1) DEFAULT NULL,
  `b5` tinyint(1) DEFAULT NULL,
  `b6` tinyint(1) DEFAULT NULL,
  `b7` tinyint(1) DEFAULT NULL,
  `b8` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_s1attacks_attack_type` (`attack_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;



--
-- Table structure for table `%PREFIX%banlist`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%banlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `reason` varchar(30) DEFAULT NULL,
  `time` int unsigned DEFAULT NULL,
  `end` int unsigned DEFAULT NULL,
  `admin` int DEFAULT NULL,
  `active` tinyint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `active-end` (`active`,`end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%bdata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%bdata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wid` int DEFAULT NULL,
  `field` tinyint DEFAULT NULL,
  `type` tinyint DEFAULT NULL,
  `loopcon` tinyint(1) DEFAULT NULL,
  `timestamp` int DEFAULT NULL,
  `master` tinyint(1) DEFAULT NULL,
  `level` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master` (`master`),
  KEY `timestamp` (`timestamp`),
  KEY `master-timestamp` (`master`,`timestamp`) USING BTREE,
  KEY `wid` (`wid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%build_log`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%build_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wid` int DEFAULT NULL,
  `log` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%chat`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%chat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `alli` varchar(255) DEFAULT NULL,
  `date` varchar(255) DEFAULT NULL,
  `msg` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%config`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%config` (
  `lastgavemedal` int DEFAULT NULL,
  `lastautomationtime` int DEFAULT '0',
  `nextStarvationUpdate` int DEFAULT '0',
  `time_offset` int DEFAULT '0',
  `enableWWstatistics` int DEFAULT NULL,
  `lastWoundedDecay` int DEFAULT '0',
  `last_rank_scan` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

INSERT INTO `%PREFIX%config` (`lastgavemedal`, `enableWWstatistics`, `last_rank_scan`) VALUES (0, 0, 0);


--
-- Table structure for table `%prefix%deleting`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%deleting` (
  `uid` int NOT NULL,
  `timestamp` int DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%demolition`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%demolition` (
  `vref` int NOT NULL,
  `buildnumber` int DEFAULT '0',
  `lvl` int DEFAULT '0',
  `timetofinish` int DEFAULT NULL,
  PRIMARY KEY (`vref`),
  KEY `timetofinish` (`timetofinish`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%diplomacy`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%diplomacy` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alli1` int DEFAULT NULL,
  `alli2` int DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `accepted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alli1` (`alli1`),
  KEY `alli2` (`alli2`),
  KEY `type-accepted` (`type`,`accepted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%enforcement`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%enforcement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `u1` int DEFAULT '0',
  `u2` int DEFAULT '0',
  `u3` int DEFAULT '0',
  `u4` int DEFAULT '0',
  `u5` int DEFAULT '0',
  `u6` int DEFAULT '0',
  `u7` int DEFAULT '0',
  `u8` int DEFAULT '0',
  `u9` int DEFAULT '0',
  `u10` int DEFAULT '0',
  `u11` int DEFAULT '0',
  `u12` int DEFAULT '0',
  `u13` int DEFAULT '0',
  `u14` int DEFAULT '0',
  `u15` int DEFAULT '0',
  `u16` int DEFAULT '0',
  `u17` int DEFAULT '0',
  `u18` int DEFAULT '0',
  `u19` int DEFAULT '0',
  `u20` int DEFAULT '0',
  `u21` int DEFAULT '0',
  `u22` int DEFAULT '0',
  `u23` int DEFAULT '0',
  `u24` int DEFAULT '0',
  `u25` int DEFAULT '0',
  `u26` int DEFAULT '0',
  `u27` int DEFAULT '0',
  `u28` int DEFAULT '0',
  `u29` int DEFAULT '0',
  `u30` int DEFAULT '0',
  `u31` int DEFAULT '0',
  `u32` int DEFAULT '0',
  `u33` int DEFAULT '0',
  `u34` int DEFAULT '0',
  `u35` int DEFAULT '0',
  `u36` int DEFAULT '0',
  `u37` int DEFAULT '0',
  `u38` int DEFAULT '0',
  `u39` int DEFAULT '0',
  `u40` int DEFAULT '0',
  `u41` int DEFAULT '0',
  `u42` int DEFAULT '0',
  `u43` int DEFAULT '0',
  `u44` int DEFAULT '0',
  `u45` int DEFAULT '0',
  `u46` int DEFAULT '0',
  `u47` int DEFAULT '0',
  `u48` int DEFAULT '0',
  `u49` int DEFAULT '0',
  `u50` int DEFAULT '0',
  `u51` int DEFAULT '0',
  `u52` int DEFAULT '0',
  `u53` int DEFAULT '0',
  `u54` int DEFAULT '0',
  `u55` int DEFAULT '0',
  `u56` int DEFAULT '0',
  `u57` int DEFAULT '0',
  `u58` int DEFAULT '0',
  `u59` int DEFAULT '0',
  `u60` int DEFAULT '0',
  `u61` int DEFAULT '0',
  `u62` int DEFAULT '0',
  `u63` int DEFAULT '0',
  `u64` int DEFAULT '0',
  `u65` int DEFAULT '0',
  `u66` int DEFAULT '0',
  `u67` int DEFAULT '0',
  `u68` int DEFAULT '0',
  `u69` int DEFAULT '0',
  `u70` int DEFAULT '0',
  `u71` int DEFAULT '0',
  `u72` int DEFAULT '0',
  `u73` int DEFAULT '0',
  `u74` int DEFAULT '0',
  `u75` int DEFAULT '0',
  `u76` int DEFAULT '0',
  `u77` int DEFAULT '0',
  `u78` int DEFAULT '0',
  `u79` int DEFAULT '0',
  `u80` int DEFAULT '0',
  `u81` int DEFAULT '0',
  `u82` int DEFAULT '0',
  `u83` int DEFAULT '0',
  `u84` int DEFAULT '0',
  `u85` int DEFAULT '0',
  `u86` int DEFAULT '0',
  `u87` int DEFAULT '0',
  `u88` int DEFAULT '0',
  `u89` int DEFAULT '0',
  `u90` int DEFAULT '0',
  `hero` tinyint(1) DEFAULT '0',
  `from` int DEFAULT '0',
  `vref` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `vref` (`vref`),
  KEY `from` (`from`,`hero`) USING BTREE,
  KEY `idx_enforcement_vref` (`vref`),
  KEY `idx_enforcement_from` (`from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%farmlist`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%farmlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wref` int DEFAULT NULL,
  `owner` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wref` (`wref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%fdata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%fdata` (
  `vref` int NOT NULL,
  `f1` tinyint DEFAULT '0',
  `f1t` tinyint DEFAULT '0',
  `f2` tinyint DEFAULT '0',
  `f2t` tinyint DEFAULT '0',
  `f3` tinyint DEFAULT '0',
  `f3t` tinyint DEFAULT '0',
  `f4` tinyint DEFAULT '0',
  `f4t` tinyint DEFAULT '0',
  `f5` tinyint DEFAULT '0',
  `f5t` tinyint DEFAULT '0',
  `f6` tinyint DEFAULT '0',
  `f6t` tinyint DEFAULT '0',
  `f7` tinyint DEFAULT '0',
  `f7t` tinyint DEFAULT '0',
  `f8` tinyint DEFAULT '0',
  `f8t` tinyint DEFAULT '0',
  `f9` tinyint DEFAULT '0',
  `f9t` tinyint DEFAULT '0',
  `f10` tinyint DEFAULT '0',
  `f10t` tinyint DEFAULT '0',
  `f11` tinyint DEFAULT '0',
  `f11t` tinyint DEFAULT '0',
  `f12` tinyint DEFAULT '0',
  `f12t` tinyint DEFAULT '0',
  `f13` tinyint DEFAULT '0',
  `f13t` tinyint DEFAULT '0',
  `f14` tinyint DEFAULT '0',
  `f14t` tinyint DEFAULT '0',
  `f15` tinyint DEFAULT '0',
  `f15t` tinyint DEFAULT '0',
  `f16` tinyint DEFAULT '0',
  `f16t` tinyint DEFAULT '0',
  `f17` tinyint DEFAULT '0',
  `f17t` tinyint DEFAULT '0',
  `f18` tinyint DEFAULT '0',
  `f18t` tinyint DEFAULT '0',
  `f19` tinyint DEFAULT '0',
  `f19t` tinyint DEFAULT '0',
  `f20` tinyint DEFAULT '0',
  `f20t` tinyint DEFAULT '0',
  `f21` tinyint DEFAULT '0',
  `f21t` tinyint DEFAULT '0',
  `f22` tinyint DEFAULT '0',
  `f22t` tinyint DEFAULT '0',
  `f23` tinyint DEFAULT '0',
  `f23t` tinyint DEFAULT '0',
  `f24` tinyint DEFAULT '0',
  `f24t` tinyint DEFAULT '0',
  `f25` tinyint DEFAULT '0',
  `f25t` tinyint DEFAULT '0',
  `f26` tinyint DEFAULT '0',
  `f26t` tinyint DEFAULT '0',
  `f27` tinyint DEFAULT '0',
  `f27t` tinyint DEFAULT '0',
  `f28` tinyint DEFAULT '0',
  `f28t` tinyint DEFAULT '0',
  `f29` tinyint DEFAULT '0',
  `f29t` tinyint DEFAULT '0',
  `f30` tinyint DEFAULT '0',
  `f30t` tinyint DEFAULT '0',
  `f31` tinyint DEFAULT '0',
  `f31t` tinyint DEFAULT '0',
  `f32` tinyint DEFAULT '0',
  `f32t` tinyint DEFAULT '0',
  `f33` tinyint DEFAULT '0',
  `f33t` tinyint DEFAULT '0',
  `f34` tinyint DEFAULT '0',
  `f34t` tinyint DEFAULT '0',
  `f35` tinyint DEFAULT '0',
  `f35t` tinyint DEFAULT '0',
  `f36` tinyint DEFAULT '0',
  `f36t` tinyint DEFAULT '0',
  `f37` tinyint DEFAULT '0',
  `f37t` tinyint DEFAULT '0',
  `f38` tinyint DEFAULT '0',
  `f38t` tinyint DEFAULT '0',
  `f39` tinyint DEFAULT '0',
  `f39t` tinyint DEFAULT '0',
  `f40` tinyint DEFAULT '0',
  `f40t` tinyint DEFAULT '0',
  `f99` tinyint DEFAULT '0',
  `f99t` tinyint DEFAULT '0',
  `wwname` varchar(100) DEFAULT 'World Wonder',
  `ww_lastupdate` int DEFAULT NULL,
  PRIMARY KEY (`vref`),
  KEY `f99` (`f99`),
  KEY `f99t` (`f99t`),
  KEY `idx_fdata_vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%forum_cat`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%forum_cat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sorting` int NOT NULL,
  `owner` varchar(255) DEFAULT NULL,
  `alliance` int NOT NULL,
  `forum_name` varchar(255) DEFAULT NULL,
  `forum_des` text,
  `forum_area` varchar(255) DEFAULT NULL,
  `display_to_alliances` text,
  `display_to_users` text,
  PRIMARY KEY (`id`),
  KEY `alliance-forum_area` (`alliance`,`forum_area`),
  KEY `display_to_alliances` (`display_to_alliances`(11)),
  KEY `display_to_users` (`display_to_users`(11)),
  KEY `sorting` (`sorting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%forum_edit`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%forum_edit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alliance` int NOT NULL,
  `result` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alliance` (`alliance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%forum_post`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%forum_post` (
  `id` int NOT NULL AUTO_INCREMENT,
  `post` longtext,
  `topic` int NOT NULL,
  `owner` int NOT NULL,
  `date` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topic-owner` (`topic`,`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%forum_survey`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%forum_survey` (
  `topic` int DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `option1` varchar(255) DEFAULT NULL,
  `option2` varchar(255) DEFAULT NULL,
  `option3` varchar(255) DEFAULT NULL,
  `option4` varchar(255) DEFAULT NULL,
  `option5` varchar(255) DEFAULT NULL,
  `option6` varchar(255) DEFAULT NULL,
  `option7` varchar(255) DEFAULT NULL,
  `option8` varchar(255) DEFAULT NULL,
  `vote1` int DEFAULT '0',
  `vote2` int DEFAULT '0',
  `vote3` int DEFAULT '0',
  `vote4` int DEFAULT '0',
  `vote5` int DEFAULT '0',
  `vote6` int DEFAULT '0',
  `vote7` int DEFAULT '0',
  `vote8` int DEFAULT '0',
  `voted` text,
  `ends` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%forum_topic`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%forum_topic` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `post` longtext,
  `date` int NOT NULL,
  `post_date` int NOT NULL,
  `cat` int NOT NULL,
  `owner` int NOT NULL,
  `alliance` int NOT NULL,
  `ends` int NOT NULL,
  `close` tinyint NOT NULL,
  `stick` tinyint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cat-stick` (`cat`,`stick`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%general`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%general` (
  `id` int NOT NULL AUTO_INCREMENT,
  `casualties` int DEFAULT NULL,
  `time` int DEFAULT NULL,
  `shown` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shown` (`shown`),
  KEY `idx_shown_time` (`shown`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%gold_fin_log`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%gold_fin_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wid` int DEFAULT NULL,
  `log` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%hero`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%hero` (
  `heroid` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL,
  `unit` smallint DEFAULT NULL,
  `name` tinytext,
  `wref` int DEFAULT NULL,
  `level` tinyint DEFAULT NULL,
  `points` int DEFAULT NULL,
  `experience` int DEFAULT NULL,
  `dead` tinyint(1) DEFAULT NULL,
  `health` float(12,9) DEFAULT NULL,
  `attack` tinyint DEFAULT NULL,
  `defence` tinyint DEFAULT NULL,
  `attackbonus` tinyint DEFAULT NULL,
  `defencebonus` tinyint DEFAULT NULL,
  `regeneration` tinyint DEFAULT NULL,
  `autoregen` int DEFAULT NULL,
  `lastupdate` int DEFAULT NULL,
  `trainingtime` int DEFAULT NULL,
  `inrevive` tinyint(1) DEFAULT NULL,
  `intraining` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`heroid`),
  KEY `uid` (`uid`,`dead`) USING BTREE,
  KEY `lastupdate` (`lastupdate`),
  KEY `inrevive` (`inrevive`),
  KEY `intraining` (`intraining`),
  KEY `idx_hero_uid_dead` (`uid`,`dead`),
  KEY `idx_hero_wref` (`wref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%illegal_log`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%illegal_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` int DEFAULT NULL,
  `log` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%links`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userid` int DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `url` varchar(150) DEFAULT NULL,
  `pos` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userid-pos` (`userid`,`pos`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Table structure for table `%prefix%login_log`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%login_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL,
  `ip` varchar(15) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%market`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%market` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vref` int DEFAULT NULL,
  `gtype` tinyint(1) DEFAULT NULL,
  `gamt` int DEFAULT NULL,
  `wtype` tinyint(1) DEFAULT NULL,
  `wamt` int DEFAULT NULL,
  `accept` tinyint(1) DEFAULT NULL,
  `maxtime` int DEFAULT NULL,
  `alliance` int DEFAULT NULL,
  `merchant` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vref-accept-merchant` (`vref`,`accept`,`merchant`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%market_log`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%market_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wid` int DEFAULT NULL,
  `log` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%mdata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%mdata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `target` int DEFAULT NULL,
  `owner` int DEFAULT NULL,
  `topic` varchar(100) DEFAULT NULL,
  `message` text,
  `viewed` tinyint(1) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT NULL,
  `send` tinyint(1) DEFAULT NULL,
  `time` int DEFAULT '0',
  `deltarget` int DEFAULT NULL,
  `delowner` int DEFAULT NULL,
  `alliance` int DEFAULT NULL,
  `player` int DEFAULT NULL,
  `coor` int DEFAULT NULL,
  `report` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `target-time` (`target`,`time`) USING BTREE,
  KEY `owner` (`owner`),
  KEY `target-viewed` (`target`,`viewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%mdata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%mdata_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `target` int DEFAULT NULL,
  `owner` int DEFAULT NULL,
  `topic` varchar(100) DEFAULT NULL,
  `message` text,
  `viewed` tinyint(1) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT NULL,
  `send` tinyint(1) DEFAULT NULL,
  `time` int DEFAULT '0',
  `deltarget` int DEFAULT NULL,
  `delowner` int DEFAULT NULL,
  `alliance` int DEFAULT NULL,
  `player` int DEFAULT NULL,
  `coor` int DEFAULT NULL,
  `report` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `target-time` (`target`,`time`) USING BTREE,
  KEY `owner` (`owner`),
  KEY `target-viewed` (`target`,`viewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%medal`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%medal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `userid` int DEFAULT NULL,
  `categorie` int DEFAULT NULL,
  `plaats` int DEFAULT NULL,
  `week` int DEFAULT NULL,
  `points` varchar(15) DEFAULT NULL,
  `img` varchar(10) DEFAULT NULL,
  `del` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `week` (`week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%movement`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%movement` (
  `moveid` int NOT NULL AUTO_INCREMENT,
  `sort_type` tinyint DEFAULT '0',
  `from` int DEFAULT '0',
  `to` int DEFAULT '0',
  `ref` int DEFAULT '0',
  `ref2` int DEFAULT '0',
  `starttime` int DEFAULT '0',
  `endtime` int DEFAULT '0',
  `proc` tinyint(1) DEFAULT '0',
  `send` tinyint(1) DEFAULT NULL,
  `wood` int DEFAULT NULL,
  `clay` int DEFAULT NULL,
  `iron` int DEFAULT NULL,
  `crop` int DEFAULT NULL,
  PRIMARY KEY (`moveid`),
  KEY `ref` (`ref`),
  KEY `from-proc-sort_type` (`from`,`proc`,`sort_type`),
  KEY `proc-sort_type-endtime` (`proc`,`sort_type`,`endtime`),
  KEY `idx_movement_from_proc_sort_endtime` (`from`,`proc`,`sort_type`,`endtime`),
  KEY `idx_movement_to_proc_sort_endtime` (`to`,`proc`,`sort_type`,`endtime`),
  KEY `idx_movement_ref` (`ref`),
  KEY `idx_movement_endtime` (`endtime`),
  KEY `idx_endtime_proc` (`endtime`,`proc`),
  KEY `idx_sort_type_ref` (`sort_type`,`ref`),
  KEY `idx_from_proc_sort` (`from`,`proc`,`sort_type`),
  KEY `idx_movement_check` (`to`,`proc`,`sort_type`,`from`),
  KEY `idx_movement_return_check` (`from`,`proc`,`sort_type`,`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%movement_archive`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%movement_archive` (
  `moveid` int NOT NULL AUTO_INCREMENT,
  `sort_type` tinyint DEFAULT '0',
  `from` int DEFAULT '0',
  `to` int DEFAULT '0',
  `ref` int DEFAULT '0',
  `ref2` int DEFAULT '0',
  `starttime` int DEFAULT '0',
  `endtime` int DEFAULT '0',
  `proc` tinyint(1) DEFAULT '0',
  `send` tinyint(1) DEFAULT NULL,
  `wood` int DEFAULT NULL,
  `clay` int DEFAULT NULL,
  `iron` int DEFAULT NULL,
  `crop` int DEFAULT NULL,
  PRIMARY KEY (`moveid`),
  KEY `ref` (`ref`),
  KEY `from-proc-sort_type` (`from`,`proc`,`sort_type`),
  KEY `proc-sort_type-endtime` (`proc`,`sort_type`,`endtime`),
  KEY `idx_movement_from_proc_sort_endtime` (`from`,`proc`,`sort_type`,`endtime`),
  KEY `idx_movement_to_proc_sort_endtime` (`to`,`proc`,`sort_type`,`endtime`),
  KEY `idx_movement_ref` (`ref`),
  KEY `idx_movement_endtime` (`endtime`),
  KEY `idx_sort_type_ref` (`sort_type`,`ref`),
  KEY `idx_from_proc_sort` (`from`,`proc`,`sort_type`),
  KEY `idx_movement_check` (`to`,`proc`,`sort_type`,`from`),
  KEY `idx_movement_return_check` (`from`,`proc`,`sort_type`,`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%ndata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%ndata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL,
  `toWref` int DEFAULT NULL,
  `ally` int DEFAULT NULL,
  `topic` text,
  `ntype` tinyint(1) DEFAULT NULL,
  `data` text,
  `time` int DEFAULT NULL,
  `viewed` tinyint(1) DEFAULT NULL,
  `archive` tinyint(1) DEFAULT '0',
  `del` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `uid-time` (`uid`,`time`) USING BTREE,
  KEY `del` (`del`),
  KEY `toWref` (`toWref`),
  KEY `uid-viewed` (`uid`,`viewed`),
  KEY `idx_report_filter` (`uid`,`del`,`archive`,`ntype`,`time`),
  KEY `idx_ally_towref_ntype_time` (`ally`,`toWref`,`ntype`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%ndata_archive`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%ndata_archive` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL,
  `toWref` int DEFAULT NULL,
  `ally` int DEFAULT NULL,
  `topic` text,
  `ntype` tinyint(1) DEFAULT NULL,
  `data` text,
  `time` int DEFAULT NULL,
  `viewed` tinyint(1) DEFAULT NULL,
  `archive` tinyint(1) DEFAULT '0',
  `del` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `uid-time` (`uid`,`time`) USING BTREE,
  KEY `del` (`del`),
  KEY `toWref` (`toWref`),
  KEY `uid-viewed` (`uid`,`viewed`),
  KEY `idx_report_filter` (`uid`,`del`,`archive`,`ntype`,`time`),
  KEY `idx_ally_towref_ntype_time` (`ally`,`toWref`,`ntype`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%odata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%odata` (
  `wref` int NOT NULL,
  `type` tinyint DEFAULT NULL,
  `conqured` int DEFAULT NULL,
  `wood` int DEFAULT NULL,
  `iron` int DEFAULT NULL,
  `clay` int DEFAULT NULL,
  `maxstore` int DEFAULT NULL,
  `crop` int DEFAULT NULL,
  `maxcrop` int DEFAULT NULL,
  `lastupdated` int DEFAULT NULL,
  `lastupdated2` int DEFAULT NULL,
  `loyalty` float(9,6) DEFAULT '100.000000',
  `owner` int DEFAULT '2',
  `name` varchar(32) DEFAULT 'Unoccupied Oasis',
  `high` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`wref`),
  KEY `lastupdated2` (`lastupdated2`) USING BTREE,
  KEY `conqured` (`conqured`),
  KEY `wood` (`wood`),
  KEY `iron` (`iron`),
  KEY `clay` (`clay`),
  KEY `crop` (`crop`),
  KEY `loyalty` (`loyalty`),
  KEY `maxcrop` (`maxcrop`),
  KEY `maxstore` (`maxstore`),
  KEY `owner` (`owner`),
  KEY `idx_odata_conqured` (`conqured`),
  KEY `idx_odata_owner` (`owner`),
  KEY `idx_odata_lastupdated2` (`lastupdated2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%online`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%online` (
  `name` varchar(32) DEFAULT NULL,
  `uid` int DEFAULT NULL,
  `time` varchar(32) DEFAULT NULL,
  `sit` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `name` (`name`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%password`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%password` (
  `uid` int NOT NULL,
  `npw` varchar(100) DEFAULT NULL,
  `cpw` varchar(100) DEFAULT NULL,
  `used` tinyint(1) DEFAULT '0',
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%prisoners`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%prisoners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wref` int DEFAULT NULL,
  `from` int DEFAULT NULL,
  `t1` int DEFAULT NULL,
  `t2` int DEFAULT NULL,
  `t3` int DEFAULT NULL,
  `t4` int DEFAULT NULL,
  `t5` int DEFAULT NULL,
  `t6` int DEFAULT NULL,
  `t7` int DEFAULT NULL,
  `t8` int DEFAULT NULL,
  `t9` int DEFAULT NULL,
  `t10` int DEFAULT NULL,
  `t11` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wref` (`wref`),
  KEY `from` (`from`,`t11`) USING BTREE,
  KEY `idx_prisoners_wref` (`wref`),
  KEY `idx_prisoners_from` (`from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%raidlist`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%raidlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lid` int DEFAULT NULL,
  `towref` int DEFAULT NULL,
  `x` int DEFAULT NULL,
  `y` int DEFAULT NULL,
  `distance` float DEFAULT '0',
  `t1` int DEFAULT NULL,
  `t2` int DEFAULT NULL,
  `t3` int DEFAULT NULL,
  `t4` int DEFAULT NULL,
  `t5` int DEFAULT NULL,
  `t6` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lid-distance` (`lid`,`distance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%research`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%research` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vref` int DEFAULT NULL,
  `tech` varchar(3) DEFAULT NULL,
  `timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vref` (`vref`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%route`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%route` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int DEFAULT NULL,
  `wid` int DEFAULT NULL,
  `from` int DEFAULT NULL,
  `wood` int DEFAULT NULL,
  `clay` int DEFAULT NULL,
  `iron` int DEFAULT NULL,
  `crop` int DEFAULT NULL,
  `start` tinyint DEFAULT NULL,
  `deliveries` tinyint(1) DEFAULT NULL,
  `merchant` int DEFAULT NULL,
  `timestamp` int DEFAULT NULL,
  `timeleft` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `wid` (`wid`),
  KEY `timestamp` (`timestamp`),
  KEY `timeleft` (`timeleft`),
  KEY `uid-timestamp` (`uid`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%send`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%send` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wood` int DEFAULT NULL,
  `clay` int DEFAULT NULL,
  `iron` int DEFAULT NULL,
  `crop` int DEFAULT NULL,
  `merchant` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%tdata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%tdata` (
  `vref` int NOT NULL,
  `t2` tinyint(1) DEFAULT '0',
  `t3` tinyint(1) DEFAULT '0',
  `t4` tinyint(1) DEFAULT '0',
  `t5` tinyint(1) DEFAULT '0',
  `t6` tinyint(1) DEFAULT '0',
  `t7` tinyint(1) DEFAULT '0',
  `t8` tinyint(1) DEFAULT '0',
  `t9` tinyint(1) DEFAULT '0',
  `t12` tinyint(1) DEFAULT '0',
  `t13` tinyint(1) DEFAULT '0',
  `t14` tinyint(1) DEFAULT '0',
  `t15` tinyint(1) DEFAULT '0',
  `t16` tinyint(1) DEFAULT '0',
  `t17` tinyint(1) DEFAULT '0',
  `t18` tinyint(1) DEFAULT '0',
  `t19` tinyint(1) DEFAULT '0',
  `t22` tinyint(1) DEFAULT '0',
  `t23` tinyint(1) DEFAULT '0',
  `t24` tinyint(1) DEFAULT '0',
  `t25` tinyint(1) DEFAULT '0',
  `t26` tinyint(1) DEFAULT '0',
  `t27` tinyint(1) DEFAULT '0',
  `t28` tinyint(1) DEFAULT '0',
  `t29` tinyint(1) DEFAULT '0',
  `t32` tinyint(1) DEFAULT '0',
  `t33` tinyint(1) DEFAULT '0',
  `t34` tinyint(1) DEFAULT '0',
  `t35` tinyint(1) DEFAULT '0',
  `t36` tinyint(1) DEFAULT '0',
  `t37` tinyint(1) DEFAULT '0',
  `t38` tinyint(1) DEFAULT '0',
  `t39` tinyint(1) DEFAULT '0',
  `t42` tinyint(1) DEFAULT '0',
  `t43` tinyint(1) DEFAULT '0',
  `t44` tinyint(1) DEFAULT '0',
  `t45` tinyint(1) DEFAULT '0',
  `t46` tinyint(1) DEFAULT '0',
  `t47` tinyint(1) DEFAULT '0',
  `t48` tinyint(1) DEFAULT '0',
  `t49` tinyint(1) DEFAULT '0',
  `t52` tinyint(1) DEFAULT '0',
  `t53` tinyint(1) DEFAULT '0',
  `t54` tinyint(1) DEFAULT '0',
  `t55` tinyint(1) DEFAULT '0',
  `t56` tinyint(1) DEFAULT '0',
  `t57` tinyint(1) DEFAULT '0',
  `t58` tinyint(1) DEFAULT '0',
  `t59` tinyint(1) DEFAULT '0',
  `t62` tinyint(1) DEFAULT '0',
  `t63` tinyint(1) DEFAULT '0',
  `t64` tinyint(1) DEFAULT '0',
  `t65` tinyint(1) DEFAULT '0',
  `t66` tinyint(1) DEFAULT '0',
  `t67` tinyint(1) DEFAULT '0',
  `t68` tinyint(1) DEFAULT '0',
  `t69` tinyint(1) DEFAULT '0',
  `t72` tinyint(1) DEFAULT '0',
  `t73` tinyint(1) DEFAULT '0',
  `t74` tinyint(1) DEFAULT '0',
  `t75` tinyint(1) DEFAULT '0',
  `t76` tinyint(1) DEFAULT '0',
  `t77` tinyint(1) DEFAULT '0',
  `t78` tinyint(1) DEFAULT '0',
  `t79` tinyint(1) DEFAULT '0',
  `t82` tinyint(1) DEFAULT '0',
  `t83` tinyint(1) DEFAULT '0',
  `t84` tinyint(1) DEFAULT '0',
  `t85` tinyint(1) DEFAULT '0',
  `t86` tinyint(1) DEFAULT '0',
  `t87` tinyint(1) DEFAULT '0',
  `t88` tinyint(1) DEFAULT '0',
  `t89` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%tech_log`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%tech_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wid` int DEFAULT NULL,
  `log` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%training`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%training` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vref` int DEFAULT NULL,
  `unit` smallint DEFAULT NULL,
  `amt` int DEFAULT NULL,
  `pop` int DEFAULT NULL,
  `timestamp` int DEFAULT NULL,
  `eachtime` int DEFAULT NULL,
  `timestamp2` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%units`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%units` (
  `vref` int NOT NULL,
  `u1` int DEFAULT '0',
  `u2` int DEFAULT '0',
  `u3` int DEFAULT '0',
  `u4` int DEFAULT '0',
  `u5` int DEFAULT '0',
  `u6` int DEFAULT '0',
  `u7` int DEFAULT '0',
  `u8` int DEFAULT '0',
  `u9` int DEFAULT '0',
  `u10` int DEFAULT '0',
  `u11` int DEFAULT '0',
  `u12` int DEFAULT '0',
  `u13` int DEFAULT '0',
  `u14` int DEFAULT '0',
  `u15` int DEFAULT '0',
  `u16` int DEFAULT '0',
  `u17` int DEFAULT '0',
  `u18` int DEFAULT '0',
  `u19` int DEFAULT '0',
  `u20` int DEFAULT '0',
  `u21` int DEFAULT '0',
  `u22` int DEFAULT '0',
  `u23` int DEFAULT '0',
  `u24` int DEFAULT '0',
  `u25` int DEFAULT '0',
  `u26` int DEFAULT '0',
  `u27` int DEFAULT '0',
  `u28` int DEFAULT '0',
  `u29` int DEFAULT '0',
  `u30` int DEFAULT '0',
  `u31` int DEFAULT '0',
  `u32` int DEFAULT '0',
  `u33` int DEFAULT '0',
  `u34` int DEFAULT '0',
  `u35` int DEFAULT '0',
  `u36` int DEFAULT '0',
  `u37` int DEFAULT '0',
  `u38` int DEFAULT '0',
  `u39` int DEFAULT '0',
  `u40` int DEFAULT '0',
  `u41` int DEFAULT '0',
  `u42` int DEFAULT '0',
  `u43` int DEFAULT '0',
  `u44` int DEFAULT '0',
  `u45` int DEFAULT '0',
  `u46` int DEFAULT '0',
  `u47` int DEFAULT '0',
  `u48` int DEFAULT '0',
  `u49` int DEFAULT '0',
  `u50` int DEFAULT '0',
  `u51` int DEFAULT '0',
  `u52` int DEFAULT '0',
  `u53` int DEFAULT '0',
  `u54` int DEFAULT '0',
  `u55` int DEFAULT '0',
  `u56` int DEFAULT '0',
  `u57` int DEFAULT '0',
  `u58` int DEFAULT '0',
  `u59` int DEFAULT '0',
  `u60` int DEFAULT '0',
  `u61` int DEFAULT '0',
  `u62` int DEFAULT '0',
  `u63` int DEFAULT '0',
  `u64` int DEFAULT '0',
  `u65` int DEFAULT '0',
  `u66` int DEFAULT '0',
  `u67` int DEFAULT '0',
  `u68` int DEFAULT '0',
  `u69` int DEFAULT '0',
  `u70` int DEFAULT '0',
  `u71` int DEFAULT '0',
  `u72` int DEFAULT '0',
  `u73` int DEFAULT '0',
  `u74` int DEFAULT '0',
  `u75` int DEFAULT '0',
  `u76` int DEFAULT '0',
  `u77` int DEFAULT '0',
  `u78` int DEFAULT '0',
  `u79` int DEFAULT '0',
  `u80` int DEFAULT '0',
  `u81` int DEFAULT '0',
  `u82` int DEFAULT '0',
  `u83` int DEFAULT '0',
  `u84` int DEFAULT '0',
  `u85` int DEFAULT '0',
  `u86` int DEFAULT '0',
  `u87` int DEFAULT '0',
  `u88` int DEFAULT '0',
  `u89` int DEFAULT '0',
  `u90` int DEFAULT '0',
  `u99` int DEFAULT '0',
  `u99o` int DEFAULT '0',
  `hero` int DEFAULT '0',
  PRIMARY KEY (`vref`),
  KEY `idx_units_vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%wounded`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%wounded` (
  `vref` int NOT NULL,
  `w1` int DEFAULT '0',
  `w2` int DEFAULT '0',
  `w3` int DEFAULT '0',
  `w4` int DEFAULT '0',
  `w5` int DEFAULT '0',
  `w6` int DEFAULT '0',
  PRIMARY KEY (`vref`),
  KEY `idx_wounded_vref` (`vref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%PREFIX%users`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tribe` tinyint(1) DEFAULT NULL,
  `access` tinyint(1) DEFAULT '1',
  `gold` int DEFAULT '0',
  `gender` tinyint(1) DEFAULT '0',
  `birthday` date DEFAULT '1970-01-01',
  `location` text,
  `desc1` text,
  `desc2` text,
  `plus` int DEFAULT '0',
  `goldclub` int DEFAULT '0',
  `b1` int DEFAULT '0',
  `b2` int DEFAULT '0',
  `b3` int DEFAULT '0',
  `b4` int DEFAULT '0',
  `sit1` int DEFAULT '0',
  `sit2` int DEFAULT '0',
  `alliance` int DEFAULT '0',
  `sessid` varchar(100) DEFAULT NULL,
  `act` varchar(10) DEFAULT NULL,
  `timestamp` int DEFAULT '0',
  `ap` int DEFAULT '0',
  `apall` int DEFAULT '0',
  `dp` int DEFAULT '0',
  `dpall` int DEFAULT '0',
  `protect` int DEFAULT NULL,
  `quest` tinyint DEFAULT NULL,
  `quest_time` int DEFAULT NULL,
  `gpack` varchar(255) DEFAULT '/gpack/travian_default/',
  `cp` float(14,5) DEFAULT '1.00000',
  `lastupdate` int DEFAULT NULL,
  `oldpop` int DEFAULT '0',
  `RR` int DEFAULT '0',
  `Rc` int DEFAULT '0',
  `ok` tinyint(1) DEFAULT '0',
  `clp` bigint DEFAULT '0',
  `oldrank` bigint DEFAULT '0',
  `regtime` int DEFAULT '0',
  `invited` int DEFAULT '0',
  `friend0` int DEFAULT '0',
  `friend1` int DEFAULT '0',
  `friend2` int DEFAULT '0',
  `friend3` int DEFAULT '0',
  `friend4` int DEFAULT '0',
  `friend5` int DEFAULT '0',
  `friend6` int DEFAULT '0',
  `friend7` int DEFAULT '0',
  `friend8` int DEFAULT '0',
  `friend9` int DEFAULT '0',
  `friend10` int DEFAULT '0',
  `friend11` int DEFAULT '0',
  `friend12` int DEFAULT '0',
  `friend13` int DEFAULT '0',
  `friend14` int DEFAULT '0',
  `friend15` int DEFAULT '0',
  `friend16` int DEFAULT '0',
  `friend17` int DEFAULT '0',
  `friend18` int DEFAULT '0',
  `friend19` int DEFAULT '0',
  `friend0wait` int DEFAULT '0',
  `friend1wait` int DEFAULT '0',
  `friend2wait` int DEFAULT '0',
  `friend3wait` int DEFAULT '0',
  `friend4wait` int DEFAULT '0',
  `friend5wait` int DEFAULT '0',
  `friend6wait` int DEFAULT '0',
  `friend7wait` int DEFAULT '0',
  `friend8wait` int DEFAULT '0',
  `friend9wait` int DEFAULT '0',
  `friend10wait` int DEFAULT '0',
  `friend11wait` int DEFAULT '0',
  `friend12wait` int DEFAULT '0',
  `friend13wait` int DEFAULT '0',
  `friend14wait` int DEFAULT '0',
  `friend15wait` int DEFAULT '0',
  `friend16wait` int DEFAULT '0',
  `friend17wait` int DEFAULT '0',
  `friend18wait` int DEFAULT '0',
  `friend19wait` int DEFAULT '0',
  `maxevasion` mediumint DEFAULT '0',
  `village_select` bigint DEFAULT NULL,
  `vac_time` varchar(255) DEFAULT '0',
  `vac_mode` int DEFAULT '0',
  `vactwoweeks` varchar(255) DEFAULT '0',
  `is_bcrypt` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  KEY `invited` (`invited`),
  KEY `lastupdate` (`lastupdate`),
  KEY `alliance` (`alliance`),
  KEY `tribe` (`tribe`),
  KEY `timestamp-tribe` (`timestamp`,`tribe`),
  KEY `access` (`access`),
  KEY `sit1` (`sit1`),
  KEY `sit2` (`sit2`),
  KEY `gold` (`gold`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

INSERT INTO `%PREFIX%users` (`id`, `username`, `password`, `email`, `tribe`, `access`, `gold`, `gender`, `birthday`, `location`, `desc1`, `desc2`, `plus`, `b1`, `b2`, `b3`, `b4`, `sit1`, `sit2`, `alliance`, `sessid`, `act`, `timestamp`, `ap`, `apall`, `dp`, `dpall`, `protect`, `quest`, `gpack`, `cp`, `lastupdate`, `RR`, `Rc`, `ok`, `is_bcrypt`) VALUES
(1, 'Support', '', 'support@travianz.game', 0, 8, 0, 0, '1970-01-01', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, '/gpack/travian_default/', 1, 0, 0, 0, 0, 1),
(2, 'Nature', '', 'nature@travianz.game', 4, 2, 0, 0, '1970-01-01', '', '[#NATURE]', '', 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, '/gpack/travian_default/', 1, 0, 0, 0, 0, 1),
(4, 'Taskmaster', '', 'taskmaster@travianz.game', 4, 8, 0, 0, '1970-01-01', '', '[#TASKMASTER]', '', 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, '/gpack/travian_default/', 1, 0, 0, 0, 0, 1),
(5, 'Multihunter', '', 'multihunter@travianz.game', 1, 9, 0, 0, '1970-01-01', '', '[#MH]', '[#MULTIHUNTER]', 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, '/gpack/travian_default/', 1, 0, 0, 0, 0, 1);

ALTER TABLE `%PREFIX%users` AUTO_INCREMENT=6;


--
-- Table structure for table `%prefix%vdata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%vdata` (
  `wref` int NOT NULL,
  `owner` int DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `capital` tinyint(1) DEFAULT NULL,
  `pop` int DEFAULT NULL,
  `cp` int DEFAULT NULL,
  `celebration` int DEFAULT '0',
  `type` int DEFAULT '0',
  `wood` float(12,2) DEFAULT NULL,
  `clay` float(12,2) DEFAULT NULL,
  `iron` float(12,2) DEFAULT NULL,
  `maxstore` int DEFAULT NULL,
  `crop` float(12,2) DEFAULT NULL,
  `maxcrop` int DEFAULT NULL,
  `lastupdate` int DEFAULT NULL,
  `lastupdate2` int DEFAULT '0',
  `loyalty` float(9,6) DEFAULT '100.000000',
  `exp1` int DEFAULT '0',
  `exp2` int DEFAULT '0',
  `exp3` int DEFAULT '0',
  `created` int DEFAULT NULL,
  `natar` tinyint(1) DEFAULT '0',
  `starv` int DEFAULT '0',
  `starvupdate` int DEFAULT '0',
  `updateStorage` int DEFAULT '0',
  `lastupdate_rank` int DEFAULT '0',
  `evasion` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`wref`),
  KEY `owner-capital-pop` (`owner`,`capital`,`pop`),
  KEY `maxstore` (`maxstore`),
  KEY `maxcrop` (`maxcrop`),
  KEY `celebration` (`celebration`),
  KEY `wood` (`wood`),
  KEY `clay` (`clay`),
  KEY `iron` (`iron`),
  KEY `crop` (`crop`),
  KEY `starv` (`starv`),
  KEY `loyalty` (`loyalty`),
  KEY `exp1` (`exp1`),
  KEY `exp2` (`exp2`),
  KEY `exp3` (`exp3`),
  KEY `idx_vdata_starv_owner` (`starv`,`owner`),
  KEY `idx_vdata_owner` (`owner`),
  KEY `idx_vdata_starvupdate` (`starvupdate`),
  KEY `idx_storage_check` (`updateStorage`, `lastupdate`, `wref`),
  KEY `idx_vdata_capital` (`capital`),
  KEY `idx_owner_pop` (`owner`,`pop`),
  KEY `idx_owner_wref` (`owner`,`wref`),
  KEY `idx_pop` (`pop`),
  KEY `idx_lastupdate_rank` (`lastupdate_rank`),
  KEY `idx_rank_owner` (`lastupdate_rank`, `owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%wdata`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%wdata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fieldtype` tinyint DEFAULT NULL,
  `oasistype` tinyint DEFAULT NULL,
  `x` int DEFAULT NULL,
  `y` int DEFAULT NULL,
  `occupied` tinyint(1) DEFAULT NULL,
  `image` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `occupied` (`occupied`),
  KEY `fieldtype` (`fieldtype`),
  KEY `x-y` (`x`,`y`),
  KEY `idx_coords_oasis` (`x`,`y`,`oasistype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Table structure for table `%prefix%ww_attacks`
--

CREATE TABLE IF NOT EXISTS `%PREFIX%ww_attacks` (
  `vid` int DEFAULT NULL,
  `attack_time` int DEFAULT NULL,
  KEY `attack_time` (`attack_time`),
  KEY `vid` (`vid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Table structure for table `%prefix%user_stats`
-- Cached aggregated stats per player for ranking pages (Player, Romans, Teutons, Gauls, Attackers, Defenders)
-- Rebuilt on-demand when stale (> 5 minutes)
--

CREATE TABLE IF NOT EXISTS `%PREFIX%user_stats` (
  `uid` int NOT NULL PRIMARY KEY,
  `totalpop` int NOT NULL DEFAULT 0,
  `totalvils` int NOT NULL DEFAULT 0,
  `apall` int NOT NULL DEFAULT 0,
  `dpall` int NOT NULL DEFAULT 0,
  `ally_id` int DEFAULT NULL,
  `ally_tag` varchar(8) DEFAULT NULL,
  `rank_pos` int NOT NULL DEFAULT 0,
  `tribe` tinyint(1) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  KEY `idx_rank_pos` (`rank_pos`),
  KEY `idx_updated_at` (`updated_at`),
  KEY `idx_pop_vils_uid` (`totalpop` DESC, `totalvils` DESC, `uid` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Table structure for table `%prefix%village_ranks`
-- Cached village ranking for Village ranking page
-- Rebuilt on-demand when stale (> 15 minutes)
--

CREATE TABLE IF NOT EXISTS `%PREFIX%village_ranks` (
  `wref` int NOT NULL PRIMARY KEY,
  `name` varchar(100) DEFAULT NULL,
  `pop` int DEFAULT NULL,
  `owner` int DEFAULT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `x` int DEFAULT NULL,
  `y` int DEFAULT NULL,
  `rank_pos` int NOT NULL DEFAULT 0,
  `updated_at` int DEFAULT NULL,
  KEY `idx_rank_pos` (`rank_pos`),
  KEY `idx_updated_at` (`updated_at`),
  KEY `idx_pop_wref` (`pop` DESC, `wref` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
