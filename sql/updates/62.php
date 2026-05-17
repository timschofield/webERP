<?php

/*
 * sql/updates/62.php
 * Add hrperfcriteriascores table and appraisal fields for Phase 1 of Issue #918
 */

/* Create table to store per-appraisal criterion scores */
CreateTable('hrperfcriteriascores', "CREATE TABLE `hrperfcriteriascores` (
	`criteriascoreid` INT(11) NOT NULL AUTO_INCREMENT,
	`appraisalid` INT(11) NOT NULL,
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
	UNIQUE KEY `unique_appraisal_criteria` (`appraisalid`, `criteriaid`),
	KEY `idx_appraisal` (`appraisalid`),
	KEY `idx_criteria` (`criteriaid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

/* Add optional performancetemplateid to hrperfappraisals if missing */
$Result = DB_query("SHOW COLUMNS FROM `hrperfappraisals` LIKE 'performancetemplateid'");
if (DB_num_rows($Result) == 0) {
	$SQL = "ALTER TABLE `hrperfappraisals` ADD COLUMN `performancetemplateid` INT(11) DEFAULT NULL AFTER `appraisaltype`";
	$ErrMsg = __('Adding performancetemplateid to hrperfappraisals failed');
	$Result = DB_query($SQL, $ErrMsg);
}

/* Add calculatedoverallrating to hrperfappraisals if missing */
$Result = DB_query("SHOW COLUMNS FROM `hrperfappraisals` LIKE 'calculatedoverallrating'");
if (DB_num_rows($Result) == 0) {
	$SQL = "ALTER TABLE `hrperfappraisals` ADD COLUMN `calculatedoverallrating` INT(11) DEFAULT NULL AFTER `overallrating`";
	$ErrMsg = __('Adding calculatedoverallrating to hrperfappraisals failed');
	$Result = DB_query($SQL, $ErrMsg);
}

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add hrperfcriteriascores table and appraisal fields for Phase 1 of Issue #918'));
}
