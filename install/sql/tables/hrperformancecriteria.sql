CREATE TABLE `hrperformancecriteria` (
  `criteriaid` int(11) NOT NULL AUTO_INCREMENT,
  `criterianame` varchar(100) NOT NULL,
  `description` text,
  `category` varchar(50) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT 0.00,
  `displayorder` int(11) DEFAULT 0,
  `isactive` tinyint(1) NOT NULL DEFAULT 1,
  `createdby` varchar(50) DEFAULT NULL,
  `createddate` datetime DEFAULT NULL,
  `modifiedby` varchar(50) DEFAULT NULL,
  `modifieddate` datetime DEFAULT NULL,
  PRIMARY KEY (`criteriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
