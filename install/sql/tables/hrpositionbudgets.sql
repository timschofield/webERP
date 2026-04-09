CREATE TABLE `hrpositionbudgets` (
	`budgetid` INT(11) NOT NULL AUTO_INCREMENT,
	`fiscalyear` INT(11) NOT NULL,
	`departmentid` INT(11) NOT NULL,
	`positionid` INT(11) NOT NULL,
	`budgetedpositions` DECIMAL(4,2) NOT NULL,
	`budgetedsalary` DECIMAL(15,2) NOT NULL,
	`actualpositions` DECIMAL(4,2) DEFAULT NULL,
	`actualsalary` DECIMAL(15,2) DEFAULT NULL,
	`variancepositions` DECIMAL(4,2) DEFAULT NULL,
	`variancesalary` DECIMAL(15,2) DEFAULT NULL,
	`status` ENUM('Draft','Submitted','Approved','Active','Closed') DEFAULT 'Draft',
	`approvedby` INT(11) DEFAULT NULL,
	`approvaldate` DATE DEFAULT NULL,
	`notes` TEXT,
	PRIMARY KEY (`budgetid`),
	UNIQUE KEY `unique_budget` (`fiscalyear`,`departmentid`,`positionid`),
	KEY `idx_fiscalyear` (`fiscalyear`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
