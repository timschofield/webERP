<?php

// =====================================================
// Human Resources Colleague Feedback
// =====================================================

CreateTable('hrcolleaguefeedback', "CREATE TABLE `hrcolleaguefeedback` (
	`feedbackid` INT(11) NOT NULL AUTO_INCREMENT,
	`fromemployeeid` INT(11) NOT NULL,
	`aboutemployeeid` INT(11) NOT NULL,
	`createdbyid` INT(11) NOT NULL,
	`feedbackperiodstart` DATE NOT NULL,
	`feedbackperiodend` DATE NOT NULL,
	`feedbacktype` ENUM('Annual','Mid-Year','Probation','90-Day','Project') NOT NULL,
	`overallrating` INT(11) DEFAULT NULL,
	`status` ENUM('Not Started','In Progress','Completed','Cancelled') DEFAULT 'Not Started',
	`duedate` DATE DEFAULT NULL,
	`completiondate` DATE DEFAULT NULL,
	`comments` TEXT,
	`createddate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modifieddate` DATETIME DEFAULT NULL,
	PRIMARY KEY (`feedbackid`),
	KEY `idx_fromemployee` (`fromemployeeid`),
	KEY `idx_aboutemployee` (`aboutemployeeid`),
	KEY `idx_createdby` (`createdbyid`),
	KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

AddConstraint('hrcolleaguefeedback', 'fk_hrcolleaguefeedback_fromemployee', 'fromemployeeid', 'hremployees', 'employeeid', 'RESTRICT');
AddConstraint('hrcolleaguefeedback', 'fk_hrcolleaguefeedback_aboutemployee', 'aboutemployeeid', 'hremployees', 'employeeid', 'RESTRICT');
AddConstraint('hrcolleaguefeedback', 'fk_hrcolleaguefeedback_createdby', 'createdbyid', 'hremployees', 'employeeid', 'RESTRICT');

CreateTable('hrfeedbackcriteria', "CREATE TABLE `hrfeedbackcriteria` (
	`criteriaid` INT(11) NOT NULL AUTO_INCREMENT,
	`criterianame` VARCHAR(100) NOT NULL,
	`description` TEXT,
	`weight` DECIMAL(5,2) DEFAULT 0.00,
	`displayorder` INT(11) DEFAULT 0,
	`isactive` TINYINT(1) NOT NULL DEFAULT 1,
	`createdby` VARCHAR(50) DEFAULT NULL,
	`createddate` DATETIME DEFAULT NULL,
	`modifiedby` VARCHAR(50) DEFAULT NULL,
	`modifieddate` DATETIME DEFAULT NULL,
	PRIMARY KEY (`criteriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

CreateTable('hrfeedbackcriteriascores', "CREATE TABLE `hrfeedbackcriteriascores` (
	`criteriascoreid` INT(11) NOT NULL AUTO_INCREMENT,
	`feedbackid` INT(11) NOT NULL,
	`criteriaid` INT(11) NOT NULL,
	`rating` INT(11) DEFAULT NULL,
	`score` DECIMAL(5,2) DEFAULT NULL,
	`weightedscore` DECIMAL(5,2) DEFAULT NULL,
	`comments` TEXT,
	`createdby` VARCHAR(50) DEFAULT NULL,
	`createddate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`modifiedby` VARCHAR(50) DEFAULT NULL,
	`modifieddate` DATETIME DEFAULT NULL,
	PRIMARY KEY (`criteriascoreid`),
	UNIQUE KEY `unique_feedback_criteria` (`feedbackid`, `criteriaid`),
	KEY `idx_feedback` (`feedbackid`),
	KEY `idx_criteria` (`criteriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Human Resources Colleague Feedback'));
}

?>
