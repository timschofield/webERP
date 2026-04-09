CREATE TABLE `hrskilllevels` (
	`skilllevelid` INT(11) NOT NULL AUTO_INCREMENT,
	`skillid` INT(11) NOT NULL,
	`levelnumber` INT(11) NOT NULL,
	`levelname` VARCHAR(50) NOT NULL,
	`leveldescription` TEXT,
	PRIMARY KEY (`skilllevelid`),
	UNIQUE KEY `unique_competency_level` (`skillid`,`levelnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
