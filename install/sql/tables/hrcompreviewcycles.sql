CREATE TABLE `hrcompreviewcycles` (
	`reviewcycleid` INT(11) NOT NULL AUTO_INCREMENT,
	`cyclename` VARCHAR(100) NOT NULL,
	`fiscalyear` INT(11) NOT NULL,
	`startdate` DATE NOT NULL,
	`enddate` DATE NOT NULL,
	`budgetamount` DECIMAL(15,2) DEFAULT NULL,
	`status` ENUM('Planning','Active','Completed','Cancelled') DEFAULT 'Planning',
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`reviewcycleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
