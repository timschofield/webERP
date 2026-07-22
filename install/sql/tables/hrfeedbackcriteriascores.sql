CREATE TABLE `hrfeedbackcriteriascores` (
	`criteriascoreid` INT(11) NOT NULL AUTO_INCREMENT,
	`feedbackid` INT(11) NOT NULL,
	`criteriaid` INT(11) NOT NULL,
	`rating` INT(11) DEFAULT NULL,
	`score` DECIMAL(5,2) DEFAULT NULL,
	`weightedscore` DECIMAL(5,2) DEFAULT NULL,
	`comments` TEXT,
	`createdby` VARCHAR(50) DEFAULT NULL,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modifiedby` VARCHAR(50) DEFAULT NULL,
	`modifieddate` DATETIME DEFAULT NULL,
	PRIMARY KEY (`criteriascoreid`),
	UNIQUE KEY `unique_feedback_criteria` (`feedbackid`, `criteriaid`),
	KEY `idx_feedback` (`feedbackid`),
	KEY `idx_criteria` (`criteriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci