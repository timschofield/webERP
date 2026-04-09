CREATE TABLE `hrperfgoals` (
	`goalid` INT(11) NOT NULL AUTO_INCREMENT,
	`appraisalid` INT(11) NOT NULL,
	`goaldescription` TEXT NOT NULL,
	`goalweight` DECIMAL(5,2) DEFAULT NULL,
	`targetcompletiondate` DATE DEFAULT NULL,
	`achievementrating` INT(11) DEFAULT NULL,
	`comments` TEXT,
	`goalorder` INT(11) DEFAULT NULL,
	PRIMARY KEY (`goalid`),
	KEY `idx_appraisal` (`appraisalid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
