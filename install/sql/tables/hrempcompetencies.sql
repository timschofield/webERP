CREATE TABLE `hrempcompetencies` (
	`empskillid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`currentlevel` INT(11) DEFAULT NULL,
	`assessmentdate` DATE DEFAULT NULL,
	`assessedby` INT(11) DEFAULT NULL,
	`expirydate` DATE DEFAULT NULL,
	`notes` TEXT,
	PRIMARY KEY (`empskillid`),
	KEY `idx_employee` (`employeeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
