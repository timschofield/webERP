INSERT INTO `config` VALUES ('InvoiceQuantityDefault','1');
-- Add field to display dashboard after login:
ALTER TABLE `www_users` ADD `showdashboard` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Display dashboard after login' AFTER `modulesallowed`;

INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('Z_UpdateItemCosts.php', '15', 'Use CSV of item codes and costs to update webERP item costs');
INSERT INTO `scripts` (`script` ,`pagesecurity` ,`description` ) VALUES ('CustomerBalancesMovement.php', '3', 'Allow customers to be listed in local currency with balances and activity over a date range');
INSERT INTO  `scripts` VALUES ('UserLocations.php',  '15',  'Location User Maintenance');
ALTER TABLE  `stockmoves` ADD  `userid` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER  `trandate`;

INSERT INTO  `securitytokens` VALUES ('16', 'QA');

INSERT INTO `scripts`  VALUES ('QATests.php', '16', 'Quality Test Maintenance');
INSERT INTO `scripts`  VALUES ('ProductSpecs.php', '16', 'Product Specification Maintenance');
INSERT INTO `scripts`  VALUES ('TestPlanResults.php', '16', 'Test Plan Results Entry');
INSERT INTO `scripts`  VALUES ('PDFProdSpec.php', '0', 'PDF OF Product Specification');
INSERT INTO `scripts`  VALUES ('PDFCOA.php', '0', 'PDF of COA');
INSERT INTO `scripts`  VALUES ('PDFTestPlan.php', '16', 'PDF of Test Plan');
INSERT INTO `scripts`  VALUES ('SelectQASamples.php', '16', 'Select  QA Samples');
INSERT INTO `scripts`  VALUES ('HistoricalTestResults.php', '16', 'Historical Test Results');


INSERT INTO `config` (`confname` ,`confvalue` ) VALUES ('QualityLogSamples', '0');
INSERT INTO `config` (`confname` ,`confvalue` ) VALUES ('QualityCOAText', '');
INSERT INTO `config` (`confname` ,`confvalue` ) VALUES ('QualityProdSpecText', '');

--
-- Table structure for table `prodspecs`
--

CREATE TABLE IF NOT EXISTS `prodspecs` (
  `keyval` varchar(25) NOT NULL,
  `testid` int(11) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '',
  `targetvalue` varchar(30) NOT NULL DEFAULT '',
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `showoncert` tinyint(11) NOT NULL DEFAULT '1',
  `showonspec` tinyint(4) NOT NULL DEFAULT '1',
  `showontestplan` tinyint(4) NOT NULL DEFAULT '1',
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`keyval`,`testid`),
  KEY `testid` (`testid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `qasamples`
--

CREATE TABLE IF NOT EXISTS `qasamples` (
  `sampleid` int(11) NOT NULL AUTO_INCREMENT,
  `prodspeckey` varchar(25) NOT NULL DEFAULT '',
  `lotkey` varchar(25) NOT NULL DEFAULT '',
  `identifier` varchar(10) NOT NULL DEFAULT '',
  `createdby` varchar(15) NOT NULL DEFAULT '',
  `sampledate` date NOT NULL DEFAULT '0000-00-00',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `cert` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sampleid`),
  KEY `prodspeckey` (`prodspeckey`,`lotkey`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Table structure for table `qatests`
--

CREATE TABLE IF NOT EXISTS `qatests` (
  `testid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `method` varchar(20) DEFAULT NULL,
  `groupby` varchar(20) DEFAULT NULL,
  `units` varchar(20) NOT NULL,
  `type` varchar(15) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '''''',
  `numericvalue` tinyint(4) NOT NULL DEFAULT '0',
  `showoncert` int(11) NOT NULL DEFAULT '1',
  `showonspec` int(11) NOT NULL DEFAULT '1',
  `showontestplan` tinyint(4) NOT NULL DEFAULT '1',
  `active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`testid`),
  KEY `name` (`name`),
  KEY `groupname` (`groupby`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

--
-- Table structure for table `sampleresults`
--

CREATE TABLE IF NOT EXISTS `sampleresults` (
  `resultid` bigint(20) NOT NULL AUTO_INCREMENT,
  `sampleid` int(11) NOT NULL,
  `testid` int(11) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL,
  `targetvalue` varchar(30) NOT NULL,
  `rangemin` float DEFAULT NULL,
  `rangemax` float DEFAULT NULL,
  `testvalue` varchar(30) NOT NULL DEFAULT '',
  `testdate` date NOT NULL DEFAULT '0000-00-00',
  `testedby` varchar(15) NOT NULL DEFAULT '',
  `comments` varchar(255) NOT NULL DEFAULT '',
  `isinspec` tinyint(4) NOT NULL DEFAULT '0',
  `showoncert` tinyint(4) NOT NULL DEFAULT '1',
  `showontestplan` tinyint(4) NOT NULL DEFAULT '1',
  `manuallyadded` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`resultid`),
  KEY `sampleid` (`sampleid`),
  KEY `testid` (`testid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=339 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `prodspecs`
--
ALTER TABLE `prodspecs`
  ADD CONSTRAINT `prodspecs_ibfk_1` FOREIGN KEY (`testid`) REFERENCES `qatests` (`testid`);

--
-- Constraints for table `qasamples`
--
ALTER TABLE `qasamples`
  ADD CONSTRAINT `qasamples_ibfk_1` FOREIGN KEY (`prodspeckey`) REFERENCES `prodspecs` (`keyval`);

--
-- Constraints for table `sampleresults`
--
ALTER TABLE `sampleresults`
  ADD CONSTRAINT `sampleresults_ibfk_1` FOREIGN KEY (`testid`) REFERENCES `qatests` (`testid`);

--
-- Modifications of stockdescriptiontranslations table for longdescription translation and translated versions control
--
ALTER TABLE `stockdescriptiontranslations` CHANGE `descriptiontranslation` `descriptiontranslation` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Item''s short description';
-- Add field for the long description translation of an item:
ALTER TABLE `stockdescriptiontranslations` ADD `longdescriptiontranslation` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Item''s long description';
-- Add a field to mark if a description needs revision:
ALTER TABLE `stockdescriptiontranslations` ADD `needsrevision` TINYINT(1) NOT NULL DEFAULT '0';
--
INSERT INTO `config` (`confname` ,`confvalue`) VALUES ('GoogleTranslatorAPIKey',  '');
-- Assign a Page Security value to each translation script:
INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES
	('AutomaticTranslationDescriptions.php', '15', 'Translates via Google Translator all empty translated descriptions'),
	('RevisionTranslations.php', '15', 'Human revision for automatic descriptions translations');

--
-- Insert default theme value for login screen
--

INSERT INTO  scripts (`script`,`pagesecurity`,`description`) VALUES ('SalesTopCustomersInquiry.php',  '2',  'Shows the top customers sales for a selected date range');


CREATE TABLE IF NOT EXISTS `supplierdiscounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `discountnarrative` varchar(20) NOT NULL,
  `discountpercent` double NOT NULL,
  `discountamount` double NOT NULL,
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierno` (`supplierno`),
  KEY `effectivefrom` (`effectivefrom`),
  KEY `effectiveto` (`effectiveto`),
  KEY `stockid` (`stockid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;



UPDATE config SET confvalue='4.12' WHERE confname='VersionNumber';
