CREATE TABLE `departments` (
  `departmentid` int NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `authoriser` varchar(20) NOT NULL DEFAULT '',
  `departmentcode` varchar(20) DEFAULT NULL,
  `parentdepartmentid` int(11) DEFAULT NULL,
  `managerid` int(11) DEFAULT NULL,
  `locationid` varchar(5) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`departmentid`),
  KEY `idx_parent` (`parentdepartmentid`),
  KEY `idx_departmentcode` (`departmentcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4  COLLATE=utf8mb4_general_ci;
