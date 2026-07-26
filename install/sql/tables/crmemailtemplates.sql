CREATE TABLE `crmemailtemplates` (
  `templateid` int NOT NULL AUTO_INCREMENT,
  `templatename` varchar(80) NOT NULL,
  `subject` varchar(120) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `createdat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `createdby` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`templateid`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
