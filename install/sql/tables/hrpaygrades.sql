CREATE TABLE `hrpaygrades` (
	`paygradeid` INT(11) NOT NULL AUTO_INCREMENT,
	`paygradecode` VARCHAR(20) NOT NULL,
	`paygradename` VARCHAR(100) NOT NULL,
	`currencycode` VARCHAR(3) DEFAULT 'USD',
	`minsalary` DECIMAL(15,2) DEFAULT NULL,
	`midsalary` DECIMAL(15,2) DEFAULT NULL,
	`maxsalary` DECIMAL(15,2) DEFAULT NULL,
	`active` TINYINT(1) DEFAULT 1,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`paygradeid`),
	UNIQUE KEY `paygradecode` (`paygradecode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
