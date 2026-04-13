CREATE TABLE `hrperfratscales` (
	`scaleid` INT(11) NOT NULL AUTO_INCREMENT,
	`scalename` VARCHAR(50) NOT NULL,
	`ratingvalue` INT(11) NOT NULL,
	`ratinglabel` VARCHAR(50) NOT NULL,
	`ratingdescription` VARCHAR(200) DEFAULT NULL,
	`active` TINYINT(1) DEFAULT 1,
	PRIMARY KEY (`scaleid`),
	UNIQUE KEY `unique_scale_value` (`scalename`,`ratingvalue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
