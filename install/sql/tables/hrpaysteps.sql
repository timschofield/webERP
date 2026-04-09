CREATE TABLE `hrpaysteps` (
	`paystepid` INT(11) NOT NULL AUTO_INCREMENT,
	`paygradeid` INT(11) NOT NULL,
	`stepnumber` INT(11) NOT NULL,
	`stepamount` DECIMAL(15,2) NOT NULL,
	PRIMARY KEY (`paystepid`),
	UNIQUE KEY `unique_grade_step` (`paygradeid`,`stepnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
