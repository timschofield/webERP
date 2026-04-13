CREATE TABLE `hrskills` (
	`skillid` INT(11) NOT NULL AUTO_INCREMENT,
	`skillcode` VARCHAR(20) NOT NULL,
	`skillname` VARCHAR(100) NOT NULL,
	`skillcategory` ENUM('Technical','Behavioral','Leadership','Core') NOT NULL,
	`description` TEXT,
	`active` TINYINT(1) DEFAULT 1,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`skillid`),
	UNIQUE KEY `skillcode` (`skillcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
