--
-- Table structure for table `comment`
--
DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` varchar(255) NOT NULL DEFAULT '',
  `eventId` varchar(255) NOT NULL DEFAULT '',
  `comment` text,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eventId` (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `event`
--
DROP TABLE IF EXISTS `event`;
CREATE TABLE `event` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `date` timestamp NULL DEFAULT NULL,
  `groupId` int(5) DEFAULT NULL,
  `place` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(48) DEFAULT NULL,
  `zipCode` int(5) DEFAULT NULL,
  `victory` tinyint(1) DEFAULT NULL,
  `scores` varchar(8) DEFAULT NULL,
  `sets` varchar(60) DEFAULT NULL,
  `stats` varchar(100) DEFAULT NULL,
  `reminder` tinyint(1) DEFAULT NULL,
  `debrief` text,
  `lat` double DEFAULT NULL,
  `long` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupId` (`groupId`, `date`),
  KEY `score` (`score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `group`
--
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `brand` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`brand`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `usergroup`
--
DROP TABLE IF EXISTS `userGroup`;
CREATE TABLE `userGroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `groupId` int(11) unsigned NOT NULL,
  `admin` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`userId`, `groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `guest`
--
DROP TABLE IF EXISTS `disponibility`;
CREATE TABLE `disponibility` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eventId` int(11) unsigned NOT NULL,
  `userId` int(11) unsigned NOT NULL,
  `response` tinyint(4) NOT NULL DEFAULT '0',
  `groupId` int(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`userId`, `eventId`),
  KEY `response` (`response`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `join`
--
DROP TABLE IF EXISTS `join`;
CREATE TABLE `join` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `response` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`userId`, `groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `user`
--
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(20) NOT NULL,
  `lastname` varchar(20) NOT NULL,
  `numero` int(2) DEFAULT NULL,
  `position` int(2) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `password` varchar(64) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `training`
--
DROP TABLE IF EXISTS `training`;
CREATE TABLE `training` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `groupId` int(5) DEFAULT NULL,
  `name` varchar(20) NOT NULL,
  `emailDay` varchar(15) DEFAULT NULL,
  `eventDay` varchar(15) DEFAULT NULL,
  `time` varchar(5) DEFAULT NULL,
  `place` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `zipCode` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emailDay` (`emailDay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `notification`
--
DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `status` tinyint(4) NULL DEFAULT NULL,
  `notification` tinyint(4) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `holiday`
--
DROP TABLE IF EXISTS `holiday`;
CREATE TABLE `holiday` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL,
  `from` timestamp NULL DEFAULT NULL,
  `to` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `stats`
--
DROP TABLE IF EXISTS `stats`;
CREATE TABLE `stats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `eventId` int(11) unsigned NOT NULL,
  `groupId` int(11) unsigned NOT NULL,
  `userId` int(11) unsigned DEFAULT NULL,
  `pointFor` tinyint(1) DEFAULT NULL,
  `scoreUs` tinyint(2) DEFAULT NULL,
  `scoreThem` tinyint(2) DEFAULT NULL,
  `set` tinyint(1) DEFAULT NULL,
  `reason` tinyint(4) DEFAULT NULL,
  `fromZone` tinyint(4) DEFAULT NULL,
  `toZone` tinyint(4) DEFAULT NULL,
  `numero` int(4) DEFAULT NULL,
  `p1` tinyint(2) DEFAULT NULL,
  `p2` tinyint(2) DEFAULT NULL,
  `p3` tinyint(2) DEFAULT NULL,
  `p4` tinyint(2) DEFAULT NULL,
  `p5` tinyint(2) DEFAULT NULL,
  `p6` tinyint(2) DEFAULT NULL,
  `libero` tinyint(2) DEFAULT NULL,
  `start` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eventId` (`eventId`, `userId`, `groupId`),
  KEY `pointFor` (`set`, `pointFor`, `reason`),
  KEY `zone` (`fromZone`, `toZone`),
  KEY `numero` (`numero`),
  KEY `position` (`p1`, `p2`, `p3`, `p4`, `p5`, `p6`, `libero`),
  KEY `point` (`start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `game`;
CREATE TABLE `game` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `eventId` int(11) unsigned NOT NULL,
  `numero` int(11) unsigned NOT NULL,
  `type` tinyint(2) DEFAULT NULL,
  `quality` tinyint(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eventId` (`eventId`, `userId`),
  KEY `type` (`type`, `quality`),
  KEY `numero` (`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;