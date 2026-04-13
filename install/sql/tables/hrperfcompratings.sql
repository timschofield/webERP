CREATE TABLE `hrperfcompratings` (
	`skillratingid` INT(11) NOT NULL AUTO_INCREMENT,
	`appraisalid` INT(11) NOT NULL,
	`skillid` INT(11) NOT NULL,
	`rating` INT(11) DEFAULT NULL,
	`comments` TEXT,
	PRIMARY KEY (`skillratingid`),
	KEY `idx_appraisal` (`appraisalid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
