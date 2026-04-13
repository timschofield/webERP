CREATE TABLE `hrincreaseguidelines` (
	`guidelineid` INT(11) NOT NULL AUTO_INCREMENT,
	`increasetype` VARCHAR(20) NOT NULL,
	`guidelinecode` VARCHAR(20) NOT NULL,
	`description` VARCHAR(200) DEFAULT NULL,
	`minpercentage` DECIMAL(5,2) DEFAULT NULL,
	`maxpercentage` DECIMAL(5,2) DEFAULT NULL,
	`targetpercentage` DECIMAL(5,2) DEFAULT NULL,
	`serviceyearsfrom` INT(11) DEFAULT NULL,
	`serviceyearsto` INT(11) DEFAULT NULL,
	`performanceratingfrom` INT(11) DEFAULT NULL,
	`performanceratingto` INT(11) DEFAULT NULL,
	`active` TINYINT(1) DEFAULT 1,
	PRIMARY KEY (`guidelineid`),
	UNIQUE KEY `unique_guideline` (`increasetype`,`guidelinecode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
