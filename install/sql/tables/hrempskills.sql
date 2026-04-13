CREATE TABLE `hrempskills` (
	`empskillid` INT(11) NOT NULL AUTO_INCREMENT,
	`employeeid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`currentlevel` INT(11) NOT NULL DEFAULT 0 COMMENT '1-5 proficiency level',
	`assessmentdate` DATE NOT NULL,
	`assessedby` VARCHAR(20) NOT NULL,
	`notes` TEXT DEFAULT NULL,
	PRIMARY KEY (`empskillid`),
	UNIQUE KEY `employee_skill` (`employeeid`, `skillid`),
	KEY `employeeid` (`employeeid`),
	KEY `skillid` (`skillid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
