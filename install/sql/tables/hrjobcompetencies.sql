CREATE TABLE `hrjobcompetencies` (
	`jobskillid` INT(11) NOT NULL AUTO_INCREMENT,
	`positionid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`requiredlevel` INT(11) NOT NULL,
	`importance` ENUM('Essential','Important','Desired') DEFAULT NULL,
	PRIMARY KEY (`jobskillid`),
	UNIQUE KEY `unique_position_competency` (`positionid`,`skillid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
