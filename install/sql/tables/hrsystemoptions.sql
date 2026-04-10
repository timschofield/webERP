CREATE TABLE `hrsystemoptions` (
	`optionid` INT(11) NOT NULL AUTO_INCREMENT,
	`optioncategory` VARCHAR(50) NOT NULL,
	`optionname` VARCHAR(100) NOT NULL,
	`optionvalue` TEXT,
	`optiondescription` VARCHAR(255) DEFAULT NULL,
	PRIMARY KEY (`optionid`),
	UNIQUE KEY `unique_option` (`optioncategory`,`optionname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
