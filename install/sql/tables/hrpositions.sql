CREATE TABLE `hrpositions` (
	`positionid` INT(11) NOT NULL AUTO_INCREMENT,
	`positioncode` VARCHAR(20) NOT NULL,
	`positiontitle` VARCHAR(100) NOT NULL,
	`departmentid` INT(11) DEFAULT NULL,
	`reportstopositionid` INT(11) DEFAULT NULL,
	`paygradeid` INT(11) DEFAULT NULL,
	`positionstatus` ENUM('Open','Filled','Frozen','Eliminated') DEFAULT 'Open',
	`fte` DECIMAL(4,2) DEFAULT 1.00,
	`jobdescription` TEXT,
	`requirements` TEXT,
	`active` TINYINT(1) DEFAULT 1,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`positionid`),
	UNIQUE KEY `positioncode` (`positioncode`),
	KEY `idx_department` (`departmentid`),
	KEY `idx_paygrade` (`paygradeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
