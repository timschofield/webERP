CREATE TABLE `hrapprovalhierarchy` (
	`hierarchyid` INT(11) NOT NULL AUTO_INCREMENT,
	`approvaltype` VARCHAR(50) NOT NULL,
	`levelnumber` INT(11) NOT NULL,
	`approverrole` VARCHAR(50) DEFAULT NULL,
	`approverid` INT(11) DEFAULT NULL,
	`amountlimit` DECIMAL(15,2) DEFAULT NULL,
	`required` TINYINT(1) DEFAULT 1,
	PRIMARY KEY (`hierarchyid`),
	UNIQUE KEY `unique_approval_level` (`approvaltype`,`levelnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
