CREATE TABLE `hrratingscales` (
	`scaleid` INT(11) NOT NULL AUTO_INCREMENT,
	`scalename` VARCHAR(100) NOT NULL,
	`description` TEXT,
	`minvalue` INT(11) DEFAULT 1,
	`maxvalue` INT(11) DEFAULT 5,
	`active` TINYINT(1) DEFAULT 1,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`scaleid`),
	UNIQUE KEY `scalename` (`scalename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
