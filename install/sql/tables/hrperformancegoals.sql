CREATE TABLE `hrperformancegoals` (
  `goalid` int(11) NOT NULL AUTO_INCREMENT,
  `employeeid` int(11) NOT NULL,
  `goaldescription` text NOT NULL,
  `goalcategory` varchar(50) DEFAULT NULL,
  `targetdate` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Not Started',
  `progress` int(11) NOT NULL DEFAULT 0,
  `completiondate` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT 1.00,
  `createdby` varchar(20) NOT NULL,
  `createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedby` varchar(20) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`goalid`),
  KEY `employeeid` (`employeeid`),
  CONSTRAINT `hrperformancegoals_ibfk_1` FOREIGN KEY (`employeeid`) REFERENCES `hremployees` (`employeeid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
